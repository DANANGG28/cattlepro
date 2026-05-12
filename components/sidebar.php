<?php
// Sidebar Component
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Font Awesome sudah di-load di head setiap halaman -->
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

<!-- Flatpickr: Indonesian Date Picker (Global) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<!-- SweetAlert2 (Global) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// =============================================
// CATTLEPRO GLOBAL SWEETALERT2 CONFIG
// =============================================
const CP = {
    // Toast notifikasi (auto dismiss) — posisi tengah
    toast: function(type, msg, timer = 3000) {
        const colors = { success: '#00A166', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
        const icons  = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        Swal.fire({
            position: 'center',
            icon: icons[type] || 'info',
            title: msg,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            iconColor: colors[type] || '#00A166',
            customClass: { popup: 'cp-dialog' },
            didOpen: (popup) => {
                popup.addEventListener('mouseenter', Swal.stopTimer);
                popup.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    },

    // Dialog konfirmasi
    confirm: function(msg, callback, opts = {}) {
        Swal.fire({
            title: opts.title || 'Konfirmasi',
            text: msg,
            icon: opts.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: opts.confirmText || '<i class="fas fa-check mr-1"></i> Ya, Lanjutkan',
            cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
            confirmButtonColor: opts.danger ? '#ef4444' : '#00A166',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            customClass: { popup: 'cp-dialog' }
        }).then(function(result) {
            if (result.isConfirmed) callback();
        });
    },

    // Konfirmasi hapus (merah)
    confirmDelete: function(msg, callback) {
        CP.confirm(msg, callback, {
            title: 'Hapus Data?',
            icon: 'warning',
            confirmText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
            danger: true
        });
    }
};

// =============================================
// AUTO HANDLE: ?pesan= dan ?error= di URL
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('pesan')) {
        CP.toast('success', params.get('pesan'));
        // Bersihkan URL tanpa reload
        var url = new URL(window.location.href);
        url.searchParams.delete('pesan');
        window.history.replaceState({}, '', url.toString());
    }
    if (params.get('error')) {
        CP.toast('error', params.get('error'));
        var url = new URL(window.location.href);
        url.searchParams.delete('error');
        window.history.replaceState({}, '', url.toString());
    }
    if (params.get('warning')) {
        CP.toast('warning', params.get('warning'));
        var url = new URL(window.location.href);
        url.searchParams.delete('warning');
        window.history.replaceState({}, '', url.toString());
    }

    // =============================================
    // AUTO HANDLE: data-confirm-delete attribute
    // =============================================
    document.querySelectorAll('[data-confirm-delete]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            var href = el.getAttribute('href') || el.getAttribute('data-href');
            var msg = el.getAttribute('data-confirm-delete') || 'Data ini akan dihapus permanen!';
            CP.confirmDelete(msg, function() {
                window.location.href = href;
            });
        });
    });

    // AUTO HANDLE: data-confirm attribute (generic confirm)
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            var href = el.getAttribute('href') || el.getAttribute('data-href');
            var msg = el.getAttribute('data-confirm') || 'Apakah Anda yakin?';
            CP.confirm(msg, function() {
                if (href) window.location.href = href;
                else el.closest('form').submit();
            });
        });
    });

    // Flatpickr Indonesian locale
    flatpickr.localize(flatpickr.l10ns.id);
    document.querySelectorAll('input[type="date"]').forEach(function(el) {
        flatpickr(el, { locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'd F Y', defaultDate: el.value || new Date(), disableMobile: true });
    });
    document.querySelectorAll('input[type="datetime-local"]').forEach(function(el) {
        flatpickr(el, { locale: 'id', dateFormat: 'Y-m-d\\TH:i', altInput: true, altFormat: 'd F Y H:i', enableTime: true, time_24hr: true, defaultDate: el.value || new Date(), disableMobile: true });
    });
});
</script>

<style>
/* CattlePro SweetAlert2 Theme */
.cp-toast { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: 14px !important; box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important; }
.cp-toast-title { font-size: 14px !important; font-weight: 600 !important; }
.cp-dialog { font-family: 'Plus Jakarta Sans', sans-serif !important; border-radius: 20px !important; }
.swal2-confirm, .swal2-cancel { border-radius: 10px !important; font-weight: 700 !important; font-size: 14px !important; padding: 10px 20px !important; }
.swal2-title { font-size: 20px !important; font-weight: 800 !important; color: #0f172a !important; }
.swal2-html-container { font-size: 14px !important; color: #64748b !important; }
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
                <p class="text-[13px] font-bold text-white"><?php
                    $full_name = isset($current_user['nama']) ? $current_user['nama'] : 'Super Admin';
                    $first_name = explode(' ', trim($full_name))[0];
                    echo htmlspecialchars($first_name);
                ?></p>
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

        <?php if(isset($current_user['role']) && (strtolower($current_user['role']) === 'admin' || strtolower($current_user['role']) === 'superadmin')): ?>
        <p class="text-[10px] uppercase text-[#8ba99a] font-bold tracking-widest mb-3 ml-2 mt-6">Sistem</p>

        <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'users.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-users-cog text-lg w-6 text-center"></i>
            <span class="font-bold">Manajemen User</span>
        </a>

        <a href="aktivitas.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] transition-all duration-200 <?php echo ($current_page == 'aktivitas.php') ? 'bg-[#00D084] text-[#0A3622] font-bold shadow-lg shadow-[#00D084]/20' : 'text-[#a2c5b4] hover:text-white hover:bg-[#144834]'; ?>">
            <i class="fas fa-history text-lg w-6 text-center"></i>
            <span class="font-bold">Log Aktivitas</span>
        </a>
        <?php endif; ?>
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
<nav class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-[#0A3622] border-t border-[#144834] z-50 shadow-[0_-4px_24px_rgba(0,0,0,0.25)]" style="display:none;">
    <div class="flex justify-between items-center max-w-lg mx-auto px-8 py-3">
        <a href="dashboard.php" class="flex flex-col items-center gap-1.5 py-1 px-3 rounded-xl transition-all <?php echo ($current_page == 'dashboard.php') ? 'text-[#00D084] bg-[#144834]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-chart-pie text-xl"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Home</span>
        </a>
        <a href="sapi.php" class="flex flex-col items-center gap-1.5 py-1 px-3 rounded-xl transition-all <?php echo ($current_page == 'sapi.php' || $current_page == 'detail_sapi.php') ? 'text-[#00D084] bg-[#144834]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-cow text-xl"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Sapi</span>
        </a>
        <a href="prediksi.php" class="flex flex-col items-center gap-1.5 py-1 px-3 rounded-xl transition-all <?php echo ($current_page == 'prediksi.php') ? 'text-[#00D084] bg-[#144834]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-stethoscope text-xl"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Periksa</span>
        </a>
        <?php if(isset($current_user['role']) && (strtolower($current_user['role']) === 'admin' || strtolower($current_user['role']) === 'superadmin')): ?>
        <a href="users.php" class="flex flex-col items-center gap-1.5 py-1 px-3 rounded-xl transition-all <?php echo ($current_page == 'users.php') ? 'text-[#00D084] bg-[#144834]' : 'text-[#8ba99a]'; ?>">
            <i class="fas fa-users-cog text-xl"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Users</span>
        </a>
        <?php endif; ?>
    </div>
</nav>
