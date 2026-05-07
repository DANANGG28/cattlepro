<?php
session_start();
require_once '../controllers/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);

    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':id', $user['id']);
        $stmt->execute();

        // Send Email via Resend
        require_once '../controllers/ResendHelper.php';
        $resend = new ResendHelper();
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/cattlepro/pages/reset-password.php?token=" . $token;
        
        $emailSent = $resend->sendPasswordReset($email, $resetLink);

        if ($emailSent) {
            $success = "Link reset password telah dikirim ke email Anda.";
        } else {
            $error = "Gagal mengirim email. Silakan coba lagi nanti.";
        }
    } else {
        // We don't want to reveal if an email exists, but for this admin system, it's usually fine.
        $error = "Email tidak terdaftar dalam sistem.";
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

            <h2 class="text-3xl font-extrabold text-[#0B1522] mb-2 tracking-tight">Lupa Password?</h2>
            <p class="text-[#64748B] mb-10 text-[15px]">Masukkan email Anda untuk menerima instruksi reset password.</p>

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
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" name="email" required placeholder="admin@jayamakmur.com" class="w-full pl-11 pr-4 py-[14px] bg-white border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all shadow-sm placeholder-[#94A3B8]">
                    </div>
                </div>

                <button type="submit" name="forgot_password" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 text-[15px]">
                    Kirim Link Reset
                </button>
            </form>

            <div class="mt-10 text-center">
                <a href="../index.php" class="text-[14px] font-bold text-[#64748B] hover:text-[#093320] transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
