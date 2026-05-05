<?php
require_once 'controllers/main.php';

echo "Memulai tes pengiriman Telegram...<br>";

$pesan_tes = "<b>TES NOTIFIKASI</b>\nSistem CattlePro berhasil terhubung ke Telegram!";
$response = send_telegram($pesan_tes);

if ($response === false) {
    echo "GAGAL: Terjadi kesalahan pada koneksi cURL.";
} else {
    echo "BERHASIL!<br>";
    echo "Response dari Telegram: <pre>" . htmlspecialchars($response) . "</pre>";
}
?>
