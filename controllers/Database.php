<?php
// Produksi: tampilkan errors di log, bukan ke browser
error_reporting(E_ALL);
ini_set('display_errors', 0);

/**
 * Wrapper hasil query agar kompatibel dengan pola PDO (fetchAll, fetch, rowCount)
 */
class QueryResult {
    private $data;
    private $index = 0;

    public function __construct($data = []) {
        $this->data = is_array($data) ? $data : [];
    }

    public function fetchAll($mode = null) { return $this->data; }

    public function fetch($mode = null) {
        if ($this->index >= count($this->data)) return null;
        return $this->data[$this->index++];
    }

    public function execute()  { return true; }
    public function rowCount() { return count($this->data); }
}

class Database {
    private $projectId = 'cattlepro-93c0b';
    private $location  = 'asia-southeast2';
    private $service   = 'cattlepro-93c0b-service';
    private $keyFile   = 'cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json';

    // -------------------------------------------------------
    // Token cache — shared across all Database instances
    // dalam satu PHP process lifecycle
    // -------------------------------------------------------
    private static $cachedToken    = null;
    private static $tokenExpiresAt = 0;

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Ambil OAuth2 access token dengan 2-layer caching:
     *   Layer 1: static variable (dalam 1 request PHP, nol round-trip ulang)
     *   Layer 2: $_SESSION (antar request, token di-reuse selama 55 menit)
     *
     * Sebelumnya: setiap query = 1 extra HTTP call ke Google OAuth.
     * Setelah fix: hanya 1 call per 55 menit.
     */
    private function getAccessToken() {
        $now = time();

        // Layer 1: in-memory cache (satu request lifecycle)
        if (self::$cachedToken && $now < self::$tokenExpiresAt) {
            return self::$cachedToken;
        }

        // Layer 2: session cache (antar request, sama user)
        if (session_status() === PHP_SESSION_ACTIVE
            && !empty($_SESSION['_fb_token'])
            && $now < (int)($_SESSION['_fb_token_exp'] ?? 0)
        ) {
            self::$cachedToken    = $_SESSION['_fb_token'];
            self::$tokenExpiresAt = (int)$_SESSION['_fb_token_exp'];
            return self::$cachedToken;
        }

        // Layer 3: generate token baru dari service account
        // Prioritas A: env var individual (paling reliable, tidak perlu file)
        $envEmail      = getenv('FIREBASE_CLIENT_EMAIL');
        $envPrivateKey = getenv('FIREBASE_PRIVATE_KEY');

        if ($envEmail && $envPrivateKey) {
            // Ganti literal \n dengan newline asli (Dokploy kadang kirim sebagai string)
            $key = [
                'client_email' => $envEmail,
                'private_key'  => str_replace('\\n', "\n", $envPrivateKey),
            ];
        } else {
            // Prioritas B: baca dari file JSON
            $absolutePath = dirname(__DIR__) . '/' . $this->keyFile;
            if (!file_exists($absolutePath)) {
                error_log('[CattlePro] Key file not found: ' . $absolutePath);
                return null;
            }
            $key = json_decode(file_get_contents($absolutePath), true);
            if (!$key) {
                error_log('[CattlePro] JSON credentials corrupt! json_last_error: ' . json_last_error_msg());
                return null;
            }
        }

        $header  = $this->base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url_encode(json_encode([
            'iss'   => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]));

        $signature = '';
        if (!openssl_sign("$header.$payload", $signature, $key['private_key'], 'SHA256')) {
            error_log('[CattlePro] openssl_sign failed');
            return null;
        }
        $jwt = "$header.$payload." . $this->base64url_encode($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_ENCODING       => 'gzip, deflate',
        ]);

        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($res['access_token'])) {
            error_log('[CattlePro] Auth Token Error: ' . json_encode($res));
            return null;
        }

        $token   = $res['access_token'];
        $expires = $now + 3300; // cache 55 menit (token valid 60 menit)

        // Simpan ke kedua layer cache
        self::$cachedToken    = $token;
        self::$tokenExpiresAt = $expires;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['_fb_token']     = $token;
            $_SESSION['_fb_token_exp'] = $expires;
        }

        return $token;
    }

    /**
     * Eksekusi GraphQL query/mutation ke Firebase Data Connect.
     *
     * Optimasi cURL:
     *  - keep-alive: reuse TCP connection bila tersedia
     *  - gzip: response lebih kecil → lebih cepat di-transfer
     *  - timeout: tidak nunggu selamanya kalau Firebase lambat
     *  - TCP keepalive: jaga koneksi tetap aktif
     */
    public function execute($query, $variables = []) {
        $token = $this->getAccessToken();
        if (!$token) return ['errors' => [['message' => 'Auth Error: token null']]];

        $endpoint = "https://firebasedataconnect.googleapis.com/v1beta/projects/{$this->projectId}/locations/{$this->location}/services/{$this->service}:executeGraphql";
        $payload  = json_encode(['query' => $query, 'variables' => (object)$variables]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'Connection: keep-alive',
                'Accept-Encoding: gzip, deflate',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 5,   // max 5 detik tunggu koneksi
            CURLOPT_TIMEOUT        => 15,  // max 15 detik total request
            CURLOPT_ENCODING       => 'gzip, deflate',
            CURLOPT_TCP_KEEPALIVE  => 1,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('[CattlePro] cURL Error (' . curl_errno($ch) . '): ' . curl_error($ch));
        }
        curl_close($ch);

        $res = json_decode($response, true);
        if (isset($res['errors'])) {
            error_log('[CattlePro] GraphQL Error: ' . json_encode($res['errors']));
        }
        return $res;
    }

    // Stub methods untuk kompatibilitas dengan pola lama
    public function prepare($sql)   { return new QueryResult(); }
    public function execute_stmt()  { return new QueryResult(); }
    public function getConnection() { return $this; }
}
?>
