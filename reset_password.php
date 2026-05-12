<?php
session_start();
require_once 'controllers/Database.php';
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

$error = '';
$success = '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$valid_token = false;
$token_data = null;

// === Verifikasi Token ===
function verify_token($token) {
    if (empty($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    
    $token_file = __DIR__ . '/cache/reset_tokens/' . $token . '.json';
    if (!file_exists($token_file)) return null;
    
    $data = json_decode(file_get_contents($token_file), true);
    if (!$data || $data['expiry'] < time()) {
        @unlink($token_file); // Token kedaluwarsa, hapus
        return null;
    }
    return $data;
}

// === Hapus Token setelah dipakai ===
function consume_token($token) {
    $token_file = __DIR__ . '/cache/reset_tokens/' . $token . '.json';
    if (file_exists($token_file)) @unlink($token_file);
}

$token_data = verify_token($token);
$valid_token = ($token_data !== null);

// === Handle form submit ganti password ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password']) && $valid_token) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if (strlen($new_pass) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Password dan konfirmasi tidak cocok.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $result = $user_model->updatePassword($token_data['user_id'], $hashed);
        
        if ($result) {
            consume_token($token); // Hapus token setelah sukses
            $success = "Password berhasil diperbarui! Anda akan diarahkan ke halaman login...";
        } else {
            $error = "Gagal memperbarui password. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#093320] min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[480px] bg-white rounded-[32px] shadow-2xl overflow-hidden p-10 relative">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#00D084]/10 rounded-full filter blur-[40px]"></div>

        <div class="relative z-10">
            <div class="mb-8">
                <div class="w-14 h-14 rounded-2xl bg-[#0A3622] flex items-center justify-center mb-5 shadow-lg">
                    <i class="fas fa-key text-2xl text-white"></i>
                </div>
                <h2 class="text-[28px] font-extrabold text-[#0B1522] mb-1.5 tracking-tight">Buat Password Baru</h2>
                <p class="text-[#64748B] text-[14px]">Masukkan password baru untuk akun CattlePro Anda.</p>
            </div>

            <?php if (!$valid_token && !$success): ?>
                <!-- Token Invalid / Expired -->
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">Tautan Tidak Valid</h3>
                    <p class="text-gray-500 text-sm mb-6">Tautan reset password ini sudah kedaluwarsa atau tidak valid. Silakan minta tautan baru.</p>
                    <a href="forgot_password.php" class="inline-flex items-center gap-2 bg-[#00A166] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#008A56] transition text-sm">
                        <i class="fas fa-redo"></i> Minta Tautan Baru
                    </a>
                </div>

            <?php elseif ($success): ?>
                <!-- Sukses -->
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-3xl text-emerald-500"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">Password Diperbarui!</h3>
                    <p class="text-gray-500 text-sm mb-6"><?php echo $success; ?></p>
                    <a href="index.php" id="loginRedirect" class="inline-flex items-center gap-2 bg-[#0A3622] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#093320] transition text-sm">
                        <i class="fas fa-sign-in-alt"></i> Ke Halaman Login
                    </a>
                </div>
                <script>setTimeout(function(){ window.location.href = 'index.php'; }, 3000);</script>

            <?php else: ?>
                <!-- Form Reset -->
                <?php if($error): ?>
                    <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2 border border-red-100">
                        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5" id="resetForm">
                    <div>
                        <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]"><i class="fas fa-lock"></i></span>
                            <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Minimal 6 karakter" 
                                class="w-full pl-10 pr-11 py-[14px] bg-[#F8FAFC] border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all placeholder-[#94A3B8]">
                            <button type="button" onclick="togglePass('new_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]"><i class="fas fa-lock"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Ulangi password baru" 
                                class="w-full pl-10 pr-11 py-[14px] bg-[#F8FAFC] border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all placeholder-[#94A3B8]">
                            <button type="button" onclick="togglePass('confirm_password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Strength indicator -->
                    <div id="strengthBar" class="h-1 rounded-full bg-gray-100 overflow-hidden hidden">
                        <div id="strengthFill" class="h-full rounded-full transition-all duration-300 w-0"></div>
                    </div>

                    <button type="submit" name="reset_password" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 text-[15px]">
                        <i class="fas fa-shield-alt mr-2"></i> Simpan Password Baru
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function togglePass(id, btn) {
        var el = document.getElementById(id);
        var icon = btn.querySelector('i');
        if (el.type === 'password') {
            el.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            el.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Password strength bar
    var pwInput = document.getElementById('new_password');
    if (pwInput) {
        pwInput.addEventListener('input', function() {
            var bar = document.getElementById('strengthBar');
            var fill = document.getElementById('strengthFill');
            var val = this.value;
            bar.classList.remove('hidden');
            
            var strength = 0;
            if (val.length >= 6) strength++;
            if (val.length >= 10) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            var colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-blue-400', 'bg-emerald-500'];
            var widths = ['20%', '40%', '60%', '80%', '100%'];
            
            fill.className = 'h-full rounded-full transition-all duration-300 ' + (colors[strength - 1] || 'bg-red-400');
            fill.style.width = widths[strength - 1] || '10%';
        });
    }
    </script>
</body>
</html>
