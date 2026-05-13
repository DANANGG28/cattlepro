<?php
// TEMPORARY PASSWORD RESET SCRIPT
// HAPUS FILE INI SETELAH DIGUNAKAN!
require_once 'controllers/Database.php';

$db = new Database();

$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$email = 'danangeja3003@gmail.com';

$query = 'mutation ResetPassword($email: String!, $password: String!) {
    user_update(
        where: { email: { eq: $email } }
        data: { password: $password }
    ) {
        id
        email
    }
}';

$res = $db->execute($query, ['email' => $email, 'password' => $hash]);

echo '<pre>';
if (isset($res['data'])) {
    echo "✅ PASSWORD BERHASIL DIRESET!\n";
    echo "Email: $email\n";
    echo "Password baru: $new_password\n";
    echo "Hash: $hash\n";
    echo "\nSEGERA HAPUS FILE INI DARI SERVER!\n";
} else {
    echo "❌ GAGAL:\n";
    print_r($res);
}
echo '</pre>';
?>
