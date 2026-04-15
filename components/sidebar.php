<?php
// Sidebar Component
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    #sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    
    /* Mobile Bottom Nav */
    @media (max-width: 767px) {
        #sidebar { display: none !important; }
        .mobile-bottom-nav { display: flex !important; }
    }
    @media (min-width: 768px) {
        .mobile-bottom-nav { display: none !important; }
    }
</style>

<!-- Desktop Sidebar -->
<aside id="sidebar" class="hidden md:flex flex-col w-[250px] bg-[#0A3622] h-screen shrink-0 z-20">
    
    <!-- Logo Area -->
    <div class="p-5 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#00D084] rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-cow text-[#0A3622] text-xl"></i>
            </div>
            <h1 class="text-xl font-extrabold text-white tracking-tight">CattlePro</h1>
        </div>
    </div>

    <!-- User Info -->
    <div class="px-5 mb-6 mt-2">
        <div class="bg-[#144834] rounded-2xl p-3 flex items-center gap-3 border border-[#1d5c44]">
            <div class="w-10 h-10 bg-[#00D084] rounded-full flex items-center justify-center text-[#0A3622] font-black text-lg shadow">
                <?php echo isset($current_user['nama']) ? strtoupper(substr($current_user['nama'], 0, 1)) : 'S'; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-bold text-white truncate"><?php echo isset($current_user['nama']) ? htmlspecialchars($current_user['nama']) : 'Super Admin'; ?></p>
                <p class="text-[10px] text-[#00D084] uppercase font-bold tracking-wider mt-0.5"><?php echo isset($current_user['role']) ? htmlspecialchars(strtoupper($current_user['role'])) : 'SUPERADMIN'; ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
        <p class="text-[10px] uppercase text-[#8ba99a] font-bold tracking-widest mb-3 ml-2">Menu Utama</p>
        
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'dashboard.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-chart-pie text-lg w-6 text-center"></i>
            <span class="font-bold">Dashboard</span>
        </a>

        <a href="sapi.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'sapi.php' || $current_page == 'detail_sapi.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-cow text-lg w-6 text-center"></i>
            <span class="font-bold">Data Sapi</span>
        </a>

        <a href="prediksi.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'prediksi.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-stethoscope text-lg w-6 text-center"></i>
            <span class="font-bold">Pemeriksaan</span>
        </a>

        <p class="text-[10px] uppercase text-[#8ba99a] font-bold tracking-widest mb-3 ml-2 mt-6">Sistem</p>

        <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'users.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-users-cog text-lg w-6 text-center"></i>
            <span class="font-bold">Manajemen User</span>
        </a>
    </nav>

    <!-- Footer -->
    <div class="p-5">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-[#142e23] border border-[#224536] text-[#fc8181] hover:bg-[#1d4030] transition-all duration-200 group">
            <i class="fas fa-sign-out-alt text-lg group-hover:text-[#ff9e9e]"></i>
            <span class="font-bold text-[13px]">Keluar Sistem</span>
        </a>
    </div>
</aside>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-[#0A3622] border-t border-[#144834] z-50 px-2 py-1 shadow-[0_-4px_24px_rgba(0,0,0,0.2)]" style="display:none;">
    <div class="flex justify-around items-center max-w-md mx-auto">
        <a href="dashboard.php" class="flex flex-col items-center gap-0.5 py-2 px-3 rounded-lg <?php echo ($current_page == 'dashboard.php') ? 'text-[#00D084]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-chart-pie text-lg"></i>
            <span class="text-[10px] font-bold">Home</span>
        </a>
        <a href="sapi.php" class="flex flex-col items-center gap-0.5 py-2 px-3 rounded-lg <?php echo ($current_page == 'sapi.php' || $current_page == 'detail_sapi.php') ? 'text-[#00D084]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-cow text-lg"></i>
            <span class="text-[10px] font-bold">Sapi</span>
        </a>
        <a href="prediksi.php" class="flex flex-col items-center gap-0.5 py-2 px-3 rounded-lg <?php echo ($current_page == 'prediksi.php') ? 'text-[#00D084]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-stethoscope text-lg"></i>
            <span class="text-[10px] font-bold">Periksa</span>
        </a>
        <a href="users.php" class="flex flex-col items-center gap-0.5 py-2 px-3 rounded-lg <?php echo ($current_page == 'users.php') ? 'text-[#00D084]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-users-cog text-lg"></i>
            <span class="text-[10px] font-bold">Users</span>
        </a>
    </div>
</nav>
