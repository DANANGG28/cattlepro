<?php
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../models/Sapi.php';

$database = new Database();
$db = $database->getConnection();

$sapi = new Sapi($db);

// Helper function for Indonesian date format
function tgl_indo($tanggal, $with_time = false, $with_day = false) {
    if (!$tanggal || $tanggal == '0000-00-00' || $tanggal == '0000-00-00 00:00:00') return '-';
    
    $hari = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $time = strtotime($tanggal);
    $tgl = date('d', $time);
    $bln = $bulan[(int)date('m', $time)];
    $thn = date('Y', $time);
    
    $res = "$tgl $bln $thn";
    
    if ($with_day) {
        $d = $hari[(int)date('N', $time)];
        $res = "$d, $res";
    }
    
    if ($with_time) {
        $res .= ' ' . date('H:i', $time);
    }
    
    return $res;
}

// Get current user info for components
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
