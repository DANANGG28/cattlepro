<?php
// Manual password reset (bypass email)
require_once '../config/config.php';

$email = 'danangg8300@gmail.com'; // Change this
$new_password = 'password123'; // Change this

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, nama FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("❌ User dengan email $email tidak ditemukan!");
    }
    
    echo "User found: {$user['nama']} (ID: {$user['id']})<br>";
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, $email]);
    
    echo "✅ Password berhasil direset!<br>";
    echo "Email: $email<br>";
    echo "Password baru: $new_password<br>";
    echo "<br><a href='../index.php'>Login Sekarang</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
