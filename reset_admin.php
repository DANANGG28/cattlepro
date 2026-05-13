<?php
// TEMP RESET SCRIPT - HAPUS SETELAH SELESAI!
require_once 'controllers/Database.php';

$db = new Database();

// Test koneksi dulu
$testQuery = 'query { users(limit: 1) { id email } }';
$testRes = $db->execute($testQuery);

echo "<h3>Test Firebase Connection:</h3>";
if (isset($testRes['data'])) {
    echo "✅ Firebase terhubung!<br>";
    echo "Users found: " . count($testRes['data']['users']) . "<br><br>";

    // Reset password admin
    $new_password = 'admin123';
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $email = 'danangeja3003@gmail.com';

    $mutation = 'mutation {
        user_update(
            where: { email: { eq: "' . $email . '" } }
            data: { password: "' . $hash . '" }
        ) { id email }
    }';

    $res = $db->execute($mutation);

    echo "<h3>Reset Password:</h3>";
    if (isset($res['data'])) {
        echo "✅ Password berhasil direset!<br>";
        echo "Email: $email<br>";
        echo "Password baru: <b>$new_password</b><br>";
        echo "<br><b>Sekarang login dan hapus file ini!</b>";
    } else {
        echo "❌ Reset gagal:<br><pre>" . print_r($res, true) . "</pre>";
    }
} else {
    echo "❌ Firebase gagal:<br><pre>" . print_r($testRes, true) . "</pre>";
    
    // Debug isi file config
    $configFile = dirname(__FILE__) . '/config/firebase_env.php';
    echo "<h3>Debug File Config:</h3>";
    if (file_exists($configFile)) {
        echo "✅ File config ditemukan!<br>";
        echo "<pre>" . htmlspecialchars(file_get_contents($configFile)) . "</pre>";
    } else {
        echo "❌ File config TIDAK ditemukan! Entrypoint script gagal jalan.<br>";
    }
}
?>
