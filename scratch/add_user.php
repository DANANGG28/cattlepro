<?php
require_once __DIR__ . '/../controllers/Database.php';
$db = (new Database())->getConnection();

$email = 'danangaja3003@gmail.com';
$nama = 'Danang Admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'admin')");
    $stmt->execute(['nama' => $nama, 'email' => $email, 'password' => $password]);
    echo "User $email berhasil ditambahkan.\n";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "User $email sudah ada di database.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
