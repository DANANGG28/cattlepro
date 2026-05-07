<?php
require_once __DIR__ . '/../controllers/ResendHelper.php';

$resend = new ResendHelper();
$to = 'danangaja3003@gmail.com';
$link = 'http://localhost/cattlepro/pages/reset-password.php?token=test_token_123';

echo "Sedang mengirim email test ke $to...\n";

if ($resend->sendPasswordReset($to, $link)) {
    echo "BERHASIL! Email test terkirim. Silakan cek inbox/spam email Anda.\n";
} else {
    echo "GAGAL! Terjadi kesalahan saat mengirim email. Pastikan API Key benar dan internet aktif.\n";
}
?>
