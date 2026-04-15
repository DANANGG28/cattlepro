<?php
session_start();

require_once 'controllers/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CattlePro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#093320] min-h-screen flex m-0 overflow-hidden text-slate-800">

    <!-- Left Side: Branding & Features -->
    <div class="hidden lg:flex flex-col justify-center px-10 xl:px-20 w-1/2 relative overflow-hidden h-screen z-0">
        <!-- Decorative subtle glows -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-500/10 rounded-full filter blur-[100px] -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-[#2CD381]/10 rounded-full filter blur-[120px] translate-y-1/3 -translate-x-1/4"></div>

        <div class="relative z-10 w-full max-w-[480px] mx-auto xl:mr-10">
            <!-- Logo -->
            <div class="flex items-center gap-3.5 mb-12">
                <div class="bg-white shadow-xl shadow-black/10 w-[54px] h-[54px] rounded-[16px] flex items-center justify-center">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" class="text-[#093320]" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12.5 4c-3.5 0-6.5 3-6.5 6.5s3 6.5 6.5 6.5 6.5-6.5 2-9-2-4-5.5-4z"/>
                      <circle cx="10" cy="13" r="1.5" fill="currentColor"/>
                    </svg>
                </div>
                <span class="text-white text-[28px] font-extrabold tracking-tight">CattlePro</span>
            </div>
            
            <h1 class="text-[42px] xl:text-[48px] font-extrabold text-white leading-[1.15] mb-5">
                Kelola Peternakan <br>
                <span class="text-[#00D084]">Lebih Cerdas & <br>Digital.</span>
            </h1>

            <p class="text-[#89A897] text-[16px] mb-14 leading-relaxed max-w-sm">
                Pantau kesehatan ternak, analitik pertumbuhan, dan manajemen inventaris dalam satu platform terintegrasi.
            </p>

            <!-- Feature Card -->
            <div class="bg-[#0C4A30] border border-white/5 rounded-[20px] p-5 flex items-center gap-4 shadow-xl w-full max-w-[420px]">
                <div class="bg-[#00D084]/15 text-[#00D084] w-[46px] h-[46px] rounded-[14px] flex flex-shrink-0 items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-[15px] mb-0.5">Keamanan Terjamin</h3>
                    <p class="text-[#89A897] text-[13px]">Data peternakan Anda aman & terenkripsi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 bg-[#F8FAFC] flex flex-col justify-center px-8 sm:px-16 lg:px-24 py-10 lg:rounded-l-[40px] relative z-10 shadow-[-20px_0_40px_rgba(0,0,0,0.1)] h-screen overflow-y-auto">
        
        <div class="w-full max-w-[420px] mx-auto lg:ml-8 xl:ml-12 border border-transparent">
            
            <h2 class="text-[32px] font-extrabold text-[#0B1522] mb-1.5 tracking-tight">Selamat Datang</h2>
            <p class="text-[#64748B] mb-10 text-[15px]">Silakan masuk ke akun admin peternakan Anda.</p>

            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <!-- Email -->
                <div>
                    <label class="block text-[13px] font-extrabold text-[#1E293B] mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" name="email" required placeholder="admin@jayamakmur.com" class="w-full pl-11 pr-4 py-[14px] bg-white border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all shadow-sm placeholder-[#94A3B8]">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-[13px] font-extrabold text-[#1E293B]">Password</label>
                        <a href="#" class="text-[13px] font-bold text-[#00A166] hover:text-[#008A56] transition-colors">Lupa Password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#94A3B8]">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-11 py-[14px] bg-white border border-[#E2E8F0] rounded-[16px] text-[#0F172A] text-[15px] focus:ring-2 focus:ring-[#00A166] focus:border-[#00A166] outline-none transition-all shadow-sm placeholder-[#94A3B8]">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-[#64748B] transition-colors focus:outline-none">
                            <svg id="eye-icon" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Remember me -->
                <div class="flex items-center pt-2">
                    <div class="relative flex items-center">
                        <input id="remember" type="checkbox" class="w-[18px] h-[18px] border-[#CBD5E1] rounded-[6px] text-[#475569] accent-[#334155] focus:ring-[#00A166] focus:ring-2 cursor-pointer bg-white">
                        <label for="remember" class="ml-2.5 text-[14px] font-semibold text-[#475569] cursor-pointer">Ingat saya di perangkat ini</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="login" class="w-full bg-[#00A166] text-white font-bold py-[15px] rounded-[16px] hover:bg-[#008A56] active:scale-[0.98] transition-all shadow-lg shadow-[#00A166]/25 mt-4 text-[15px]">
                    Masuk Sekarang
                </button>
            </form>

            <p class="text-center text-[14px] text-[#64748B] mt-10 font-medium">
                Belum punya akun? <a href="#" class="text-[#00A166] font-bold hover:underline">Hubungi IT Support</a>
            </p>

        </div>
        
        <!-- Help Float Button -->
        <button class="fixed lg:absolute bottom-6 right-6 w-10 h-10 bg-[#1E293B] text-white flex rounded-full items-center justify-center hover:bg-black transition-colors shadow-lg z-50">
            <span class="font-bold text-[15px] pb-[1px]">?</span>
        </button>
    </div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" />';
    }
}
</script>

</body>
</html>
