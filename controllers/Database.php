<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Class pendukung untuk membungkus hasil query agar kompatibel dengan PDO (fetchAll, fetch)
 */
class QueryResult {
    private $data;
    private $index = 0;

    public function __construct($data = []) {
        $this->data = is_array($data) ? $data : [];
    }

    public function fetchAll($mode = null) {
        return $this->data;
    }

    public function fetch($mode = null) {
        if ($this->index >= count($this->data)) return null;
        return $this->data[$this->index++];
    }

    public function execute() {
        return true;
    }

    public function rowCount() {
        return count($this->data);
    }
}

class Database {
    private $projectId = 'cattlepro-93c0b';
    private $location = 'asia-southeast2';
    private $service = 'cattlepro-93c0b-service';
    private $keyFile = 'cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json';

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function getAccessToken() {
        $absolutePath = dirname(__DIR__) . '/' . $this->keyFile;
        if (!file_exists($absolutePath)) return null;

        $key = json_decode(file_get_contents($absolutePath), true);
        $now = time();
        
        $header = $this->base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url_encode(json_encode([
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signature = '';
        if (!openssl_sign("$header.$payload", $signature, $key['private_key'], 'SHA256')) return null;
        $signature = $this->base64url_encode($signature);

        $jwt = "$header.$payload.$signature";

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response_raw = curl_exec($ch);
        $res = json_decode($response_raw, true);
        curl_close($ch);

        if (!isset($res['access_token'])) {
            error_log('Auth Token Error: ' . $response_raw);
        }

        return isset($res['access_token']) ? $res['access_token'] : null;
    }

    public function execute($query, $variables = []) {
        $token = $this->getAccessToken();
        if (!$token) return ['errors' => [['message' => 'Auth Error']]];

        $endpoint = "https://firebasedataconnect.googleapis.com/v1beta/projects/{$this->projectId}/locations/{$this->location}/services/{$this->service}:executeGraphql";
        $payload = json_encode(['query' => $query, 'variables' => (object)$variables]);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('CURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        $res = json_decode($response, true);
        if (isset($res['errors'])) {
            error_log('GraphQL Errors: ' . json_encode($res['errors']));
        }
        return $res;
    }

    public function prepare($sql) { return new QueryResult(); }
    public function execute_stmt() { return new QueryResult(); }
    public function getConnection() { return $this; }
}
?>
