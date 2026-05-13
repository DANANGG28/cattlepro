<?php
session_start();
require_once 'controllers/Database.php';
require_once 'models/User.php';

// =============================================
// KONFIGURASI RESEND
// =============================================
define('RESEND_API_KEY', 're_iE6yKBbA_JCey9e38deUnYD2EP4hof1nt');
define('RESEND_FROM', 'CattlePro - Reset Password <onboarding@resend.dev>'); // Ganti ke domain Anda jika sudah diverifikasi di Resend
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$script_path = str_replace('\\', '/', $script_path);
if ($script_path === '/') {
    $script_path = '';
}
define('APP_URL', $protocol . '://' . $host . $script_path);

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

$error = '';
$success = '';

// === Fungsi Kirim Email via Resend ===
function send_reset_email($email_to, $nama, $token) {
    $reset_url = APP_URL . '/reset_password.php?token=' . $token;
    
    // CATATAN: Tanpa domain terverifikasi di Resend, email hanya bisa dikirim ke pemilik akun.
    // Setelah domain diverifikasi, ubah $actual_to kembali ke $email_to
    $actual_to = 'danangaja3003@gmail.com'; // Email pemilik akun Resend
    
    $html_body = '
    <!DOCTYPE html>
    <html>
    <body style="margin:0;padding:0;background:#f4f4f5;font-family:Plus Jakarta Sans,sans-serif;">
        <div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
            <div style="background:#0A3622;padding:32px 40px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:24px;font-weight:800;">🐄 CattlePro</h1>
                <p style="color:#86efac;margin:6px 0 0;font-size:13px;">Sistem Monitoring Ternak</p>
            </div>
            <div style="padding:40px;">
                <h2 style="color:#0f172a;font-size:20px;margin:0 0 8px;">Atur Ulang Kata Sandi</h2>
                <p style="color:#64748b;font-size:15px;margin:0 0 8px;">Halo <strong>' . htmlspecialchars($nama) . '</strong>, kami menerima permintaan untuk mengatur ulang kata sandi akun Anda (<em>' . htmlspecialchars($email_to) . '</em>).</p>
                <a href="' . $reset_url . '" style="display:inline-block;background:#00A166;color:#fff;font-weight:700;font-size:15px;padding:14px 32px;border-radius:12px;text-decoration:none;margin:16px 0 24px;">
                    🔑 Atur Ulang Kata Sandi
                </a>
                <p style="color:#94a3b8;font-size:13px;margin:0;">Tautan ini akan kedaluwarsa dalam <strong>1 jam</strong>. Jika Anda tidak meminta ini, abaikan email ini.</p>
                <hr style="border:none;border-top:1px solid #f1f5f9;margin:24px 0;">
                <p style="color:#cbd5e1;font-size:12px;margin:0;text-align:center;">© CattlePro &mdash; Sistem Manajemen Ternak</p>
            </div>
        </div>
    </body>
    </html>';
    
    $payload = json_encode([
        'from' => RESEND_FROM,
        'to' => [$actual_to],
        'subject' => 'Atur Ulang Kata Sandi - CattlePro',
        'html' => $html_body
    ]);
    
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . RESEND_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    // Log hasil ke file untuk debugging
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
    file_put_contents($log_dir . '/reset_email.log', 
        '[' . date('Y-m-d H:i:s') . '] to=' . $email_to . ' http=' . $http_code . ' err=' . ($err ?: '-') . ' res=' . $response . PHP_EOL, 
        FILE_APPEND);
    
    return $http_code >= 200 && $http_code < 300;
}

// === Fungsi Simpan Token ke File ===
function save_reset_token($user_id, $email) {
    $token = bin2hex(openssl_random_pseudo_bytes(32));
    $expiry = time() + 3600; // 1 jam
    
    $cache_dir = __DIR__ . '/cache/reset_tokens';
    if (!is_dir($cache_dir)) mkdir($cache_dir, 0755, true);
    
    // Hapus token lama untuk user ini
    foreach (glob($cache_dir . '/*.json') as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['user_id'] === $user_id) {
            unlink($file);
        }
    }
    
    $data = ['user_id' => $user_id, 'email' => $email, 'expiry' => $expiry];
    file_put_contents($cache_dir . '/' . $token . '.json', json_encode($data));
    
    return $token;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    $email = strtolower(trim($_POST['email']));
    
    $query = 'query GetUser($email: String!) {
        users(where: { email: { eq: $email } }) {
            id
            nama
            email
        }
    }';
    
    $res = $db->execute($query, ['email' => $email]);
    $user = isset($res['data']['users'][0]) ? $res['data']['users'][0] : null;

    if ($user) {
        $token = save_reset_token($user['id'], $user['email']);
        $sent = send_reset_email($user['email'], $user['nama'], $token);
        
        if ($sent) {
            $success = "Tautan pemulihan telah dikirim ke <strong>" . htmlspecialchars($email) . "</strong>. Silakan periksa kotak masuk atau folder spam Anda.";
        } else {
            // Cek log untuk detail error
            $error = "Gagal mengirim email. Pastikan konfigurasi Resend sudah benar. Lihat <code>logs/reset_email.log</code> untuk detail.";
        }
    } else {
        // Tampilkan pesan generik agar tidak expose apakah email terdaftar
        $success = "Jika email Anda terdaftar, tautan pemulihan akan segera dikirim.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#093320] min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[480px] bg-white rounded-[32px] shadow-2xl overflow-hidden p-10 relative">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#00D084]/10 rounded-full filter blur-[40px]"></div>
        
        <div class="relative z-10">
            <div class="mb-8">
                <a href="index.php" class="inline-flex items-center gap-2 text-[#64748B] hover:text-[#093320] transition-colors mb-6 font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Login
                </a>
                <h2 class="text-[32px] font-extrabold text-[#0B1522] mb-1.5 tracking-tight">Atur Ulang Kata Sandi</h2>
                <p class="text-[#64748B] text-[15px]">Masukkan email Anda untuk menerima instruksi pemulihan akun.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-start gap-2 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="bg-emerald-50 text-emerald-700 px-5 py-4 rounded-xl text-sm mb-6 flex items-start gap-3 border border-emerald-100">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span><?php echo $success; ?></span>
                </div>
            <?php else: ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" name="email" required placeholder="Masukkan email terdaftar" class="w-full pl-11 pr-4 py-[14px] bg-[#F8FAFC] border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all placeholder-[#94A3B8]">
                    </div>
                </div>

                <button type="submit" name="forgot_password" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 text-[15px]">
                    Kirim Instruksi Pemulihan
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
