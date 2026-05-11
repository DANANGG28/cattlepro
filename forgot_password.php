<?php
session_start();
require_once 'controllers/Database.php';

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
        // Logic untuk generate token dan kirim email (misal via Resend atau Fonnte)
        // Untuk demo, kita tampilkan pesan sukses saja dulu
        $success = "Tautan pemulihan telah dikirim ke email Anda. Silakan periksa folder masuk atau spam.";
    } else {
        $error = "Email tidak terdaftar dalam sistem kami.";
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#093320] min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[480px] bg-white rounded-[32px] shadow-2xl overflow-hidden p-10 relative">
        <!-- Decorative subtle glows -->
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
                        <input type="email" name="email" required placeholder="Masukkan email terdaftar" class="w-full pl-11 pr-4 py-[14px] bg-[#F8FAFC] border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all placeholder-[#94A3B8]">
                    </div>
                </div>

                <button type="submit" name="forgot_password" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 text-[15px]">
                    Kirim Instruksi Pemulihan
                </button>
            </form>
        </div>
    </div>
</body>
</html>
