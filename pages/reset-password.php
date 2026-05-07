<?php
session_start();
require_once '../controllers/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';
$validToken = false;
$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expires_at > NOW() LIMIT 1");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $validToken = true;
    } else {
        $error = "Token tidak valid atau telah kadaluarsa.";
    }
} else {
    $error = "Token tidak ditemukan.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password']) && $validToken) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        if (strlen($password) >= 6) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id");
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user['id']);
            
            if ($stmt->execute()) {
                $success = "Password berhasil diperbarui. Silakan login kembali.";
                $validToken = false; // Hide form after success
            } else {
                $error = "Gagal memperbarui password. Silakan coba lagi.";
            }
        } else {
            $error = "Password minimal 6 karakter.";
        }
    } else {
        $error = "Konfirmasi password tidak cocok.";
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#093320] min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-[480px] bg-[#F8FAFC] rounded-[40px] p-8 sm:p-12 shadow-2xl relative overflow-hidden">
        <!-- Decorative subtle glows -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full filter blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-10">
                <div class="bg-[#093320] w-[42px] h-[42px] rounded-[12px] flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-cow text-white text-xl"></i>
                </div>
                <span class="text-[#093320] text-xl font-extrabold tracking-tight">CattlePro</span>
            </div>

            <h2 class="text-3xl font-extrabold text-[#0B1522] mb-2 tracking-tight">Setel Ulang Password</h2>
            <p class="text-[#64748B] mb-10 text-[15px]">Masukkan password baru Anda di bawah ini.</p>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="bg-emerald-50 text-emerald-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2 border border-emerald-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <?php echo $success; ?>
                </div>
                <div class="mt-6">
                    <a href="../index.php" class="w-full block text-center bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] transition-all shadow-lg shadow-[#00A166]/25 text-[15px]">
                        Login Sekarang
                    </a>
                </div>
            <?php endif; ?>

            <?php if($validToken): ?>
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Password Baru</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-[14px] bg-white border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all shadow-sm placeholder-[#94A3B8]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </span>
                            <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-[14px] bg-white border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all shadow-sm placeholder-[#94A3B8]">
                        </div>
                    </div>

                    <button type="submit" name="reset_password" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 text-[15px] mt-2">
                        Simpan Password Baru
                    </button>
                </form>
            <?php endif; ?>

            <?php if(!$validToken && !$success): ?>
                <div class="mt-10 text-center">
                    <a href="forgot-password.php" class="text-[14px] font-bold text-[#00A166] hover:underline">Minta Link Baru</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
