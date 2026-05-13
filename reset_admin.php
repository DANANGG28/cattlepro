<?php
// TEMPORARY DEBUG SCRIPT - HAPUS SETELAH SELESAI!
$keyFile = dirname(__FILE__) . '/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json';

echo "<h3>Step 1: Cek file credentials</h3>";
echo "Path: $keyFile<br>";
echo "File exists: " . (file_exists($keyFile) ? '✅ YES' : '❌ NO') . "<br>";

if (!file_exists($keyFile)) {
    echo "<br><b>File tidak ada! Cek env var FIREBASE_CREDENTIALS_B64 di Dokploy.</b>";
    die();
}

echo "File size: " . filesize($keyFile) . " bytes<br>";

echo "<h3>Step 2: Cek isi JSON</h3>";
$content = file_get_contents($keyFile);
$key = json_decode($content, true);
echo "JSON valid: " . ($key ? '✅ YES' : '❌ NO - ' . json_last_error_msg()) . "<br>";

if (!$key) die("JSON invalid!");

echo "project_id: " . ($key['project_id'] ?? 'MISSING') . "<br>";
echo "client_email: " . ($key['client_email'] ?? 'MISSING') . "<br>";
echo "private_key exists: " . (!empty($key['private_key']) ? '✅ YES' : '❌ NO') . "<br>";
echo "private_key length: " . strlen($key['private_key'] ?? '') . " chars<br>";
echo "private_key starts with: " . substr($key['private_key'] ?? '', 0, 30) . "...<br>";

echo "<h3>Step 3: Cek OpenSSL sign</h3>";
$now = time();
$header  = rtrim(strtr(base64_encode(json_encode(['alg'=>'RS256','typ'=>'JWT'])),'+/','-_'),'=');
$payload = rtrim(strtr(base64_encode(json_encode([
    'iss'   => $key['client_email'],
    'scope' => 'https://www.googleapis.com/auth/cloud-platform',
    'aud'   => 'https://oauth2.googleapis.com/token',
    'exp'   => $now + 3600,
    'iat'   => $now
])),'+/','-_'),'=');

$signature = '';
$result = openssl_sign("$header.$payload", $signature, $key['private_key'], 'SHA256');
echo "openssl_sign result: " . ($result ? '✅ SUCCESS' : '❌ FAILED') . "<br>";
if (!$result) {
    echo "OpenSSL error: " . openssl_error_string() . "<br>";
    die();
}

echo "<h3>Step 4: Cek OAuth request ke Google</h3>";
$jwt = "$header.$payload." . rtrim(strtr(base64_encode($signature),'+/','-_'),'=');

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
]);

$raw = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

echo "cURL error: " . ($curlErr ?: 'none') . "<br>";
$res = json_decode($raw, true);
echo "OAuth response: <pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT)) . "</pre>";

if (isset($res['access_token'])) {
    echo "<b>✅ TOKEN BERHASIL! Firebase siap digunakan.</b>";
} else {
    echo "<b>❌ Token gagal didapat dari Google.</b>";
}
?>
