<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $handle = fopen($file, "r");
    
    $success_count = 0;
    $error_count = 0;
    $row = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        if ($row == 1) continue; // Skip header

        // Expected columns: Kode Sapi, Jenis, Tanggal Lahir (YYYY-MM-DD), Berat
        if (count($data) >= 4) {
            $sapi->kode_sapi = $data[0];
            $sapi->jenis = $data[1];
            $sapi->tanggal_lahir = $data[2];
            $sapi->berat = $data[3];
            $sapi->status_reproduksi = 'Kosong';
            $sapi->admin_id = $_SESSION['user_id'];

            if ($sapi->create()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    fclose($handle);
    
    // Catat aktivitas import ke log
    if ($success_count > 0) {
        $sapi->logActivity(
            $_SESSION['user_id'],
            'import_sapi',
            "Import data sapi dari Excel: {$success_count} data berhasil ditambahkan" . ($error_count > 0 ? ", {$error_count} gagal" : "")
        );
    }
    
    $msg = "Import selesai! $success_count data berhasil, $error_count gagal.";
    header("Location: sapi.php?pesan=" . urlencode($msg));
    exit;
} else {
    header("Location: sapi.php");
    exit;
}
?>
