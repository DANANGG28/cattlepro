<?php
require_once 'controllers/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Matikan check foreign key supaya bisa truncate tabel yang berelasi
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Daftar tabel yang ingin dikosongkan
    $tables = ['birahi', 'log_aktivitas', 'sapi'];

    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE $table");
        echo "Tabel $table berhasil dibersihkan.<br>";
    }

    // Hidupkan kembali check foreign key
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<br><b>Database berhasil dibersihkan!</b> Semua data sapi dan aktivitas telah dihapus.";
    echo "<br><span style='color:red'>PENTING: Segera hapus file reset_db.php ini demi keamanan!</span>";

} catch (PDOException $e) {
    echo "Gagal membersihkan database: " . $e->getMessage();
}
?>
