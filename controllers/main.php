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

// Helper function to send WhatsApp via Fonnte (dengan dukungan Schedule)
function send_wa($target, $pesan, $delay = 0) {
    // TODO: Ganti dengan Token Fonnte milik Anda
    $token = 'iX6uoWbbp766SGtiAQk3'; 
    
    $curl = curl_init();
    $data = array(
        'target' => $target,
        'message' => $pesan, 
        'countryCode' => '62',
    );
    
    // Jika ada delay (detik), tambahkan parameter schedule
    if ($delay > 0) {
        $data['schedule'] = time() + $delay;
    }

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 5,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
      ),
      CURLOPT_SSL_VERIFYPEER => false,
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// Helper function to send Telegram (Official API - More Stable)
function send_telegram($pesan) {
    $token = "8791329251:AAHs51S5PZrWnX9tacr1Bt3mnYEdtipePQU"; 
    $chat_id = "8586916589"; 
    
    $url = "https://api.telegram.org/bot$token/sendMessage?parse_mode=html&chat_id=$chat_id&text=" . urlencode($pesan);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Bypass SSL (Penting untuk server lokal agar tidak error)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        // Jika kamu ingin melihat errornya, bisa hilangkan komentar di bawah:
        // error_log("Telegram Error: " . $err);
        return false;
    }
    
    return $result;
}
?>
