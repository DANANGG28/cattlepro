<?php
require_once '../controllers/main.php';

try {
    // 1. Tambah kolom admin_id
    $sql = "ALTER TABLE sapi ADD COLUMN admin_id INT NULL AFTER tanggal_ib";
    $db->exec($sql);
    echo "Kolom admin_id berhasil ditambahkan.\n";
    
    // 2. Set admin_id default (opsional: isi data lama dengan admin pertama)
    $sql_update = "UPDATE sapi SET admin_id = (SELECT id FROM users LIMIT 1) WHERE admin_id IS NULL";
    $db->exec($sql_update);
    echo "Data lama berhasil dihubungkan ke admin default.\n";

} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "\n";
}
?>
