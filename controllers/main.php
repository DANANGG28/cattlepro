<?php
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../models/Sapi.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();

$sapi = new Sapi($db);
$user_model = new User($db);

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
$notifikasi = [];
if (isset($_SESSION['user_id'])) {
    $current_user = $user_model->getById($_SESSION['user_id']);
    $notifikasi = $sapi->getReproductionNotifications();
}

// Helper function to send WhatsApp via Fonnte (dengan dukungan Schedule)
function send_wa($target, $pesan, $delay = 0) {
    $token = 'vtNwipSD1LeixshYcQ6U';
    
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
      CURLOPT_MAXREDIRS => 2,
      CURLOPT_TIMEOUT => 10,
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
    $curl_error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Log respons ke file untuk debugging
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
    $log_msg = '[' . date('Y-m-d H:i:s') . '] target=' . $target 
             . ' delay=' . $delay 
             . ' http=' . $http_code 
             . ' curl_err=' . ($curl_error ?: '-') 
             . ' response=' . $response . PHP_EOL;
    file_put_contents($log_dir . '/wa.log', $log_msg, FILE_APPEND);

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
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
