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

<!-- Custom Notifications (Replaces SweetAlert) -->
<script>
// =============================================
// CATTLEPRO GLOBAL CUSTOM UI CONFIG
// =============================================
const CP = {
    // Toast notifikasi (auto dismiss) — posisi atas tengah
    toast: function(type, msg, timer = 3000) {
        const colors = { 
            success: 'text-[#00D084] bg-[#00D084]/10 border-[#00D084]/20', 
            error: 'text-red-500 bg-red-50 border-red-200', 
            warning: 'text-amber-500 bg-amber-50 border-amber-200', 
            info: 'text-blue-500 bg-blue-50 border-blue-200' 
        };
        const icons  = { 
            success: 'fa-check-circle', 
            error: 'fa-times-circle', 
            warning: 'fa-exclamation-triangle', 
            info: 'fa-info-circle' 
        };
        
        const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
        const toastHtml = `
            <div id="${toastId}" class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl border bg-white/95 backdrop-blur-xl transform transition-all duration-300 opacity-0 -translate-y-8 pointer-events-auto min-w-[300px]">
                <div class="flex items-center justify-center w-9 h-9 rounded-full ${colors[type].split(' ')[1]} ${colors[type].split(' ')[0]}">
                    <i class="fas ${icons[type]} text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[14px] font-bold text-slate-800 tracking-wide pr-4">${msg}</p>
                </div>
                <button onclick="document.getElementById('${toastId}').remove()" class="text-gray-400 hover:text-gray-600 transition-colors ml-2">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const el = document.getElementById(toastId);
        
        // animate in
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', '-translate-y-8');
        });

        let removeTimeout = setTimeout(() => removeToast(), timer);

        el.addEventListener('mouseenter', () => clearTimeout(removeTimeout));
        el.addEventListener('mouseleave', () => removeTimeout = setTimeout(() => removeToast(), timer));

        function removeToast() {
            if(!el) return;
            el.classList.add('opacity-0', '-translate-y-8');
            setTimeout(() => el.remove(), 300);
        }
    },

    // Dialog konfirmasi
    confirm: function(msg, callback, opts = {}) {
        const confirmColor = opts.danger ? 'bg-red-500 hover:bg-red-600 focus:ring-red-500' : 'bg-[#0A3622] hover:bg-[#144834] focus:ring-[#0A3622]';
        const iconColor = opts.danger ? 'text-red-500 bg-red-50' : 'text-[#00D084] bg-[#00D084]/10';
        const defaultIcon = opts.danger ? 'fa-exclamation-triangle' : 'fa-question-circle';
        
        const modalId = 'confirm-' + Math.random().toString(36).substr(2, 9);
        const html = `
            <div id="${modalId}" class="fixed inset-0 z-[9999] flex items-center justify-center px-4">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0" id="${modalId}-backdrop"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-7 w-full max-w-[360px] transform scale-95 opacity-0 transition-all duration-300 border border-white/20" id="${modalId}-content">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full ${iconColor} flex items-center justify-center mb-5 ring-4 ring-white shadow-sm">
                            <i class="fas ${opts.icon || defaultIcon} text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-800 mb-2.5 font-sans">${opts.title || 'Konfirmasi'}</h3>
                        <p class="text-[14px] text-gray-500 mb-8 leading-relaxed font-medium">${msg}</p>
                        
                        <div class="flex flex-col-reverse sm:flex-row gap-3 w-full">
                            <button id="${modalId}-cancel" class="w-full sm:w-[45%] py-3 px-4 rounded-xl text-[13px] font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all focus:outline-none">
                                <i class="fas fa-times mr-1"></i> Batal
                            </button>
                            <button id="${modalId}-confirm" class="w-full sm:w-[55%] py-3 px-4 rounded-xl text-[13px] font-bold text-white ${confirmColor} transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-lg shadow-current/20">
                                ${opts.confirmText || '<i class="fas fa-check mr-1"></i> Ya, Lanjutkan'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', html);
        const modal = document.getElementById(modalId);
        const backdrop = document.getElementById(`${modalId}-backdrop`);
        const content = document.getElementById(`${modalId}-content`);
        
        // animate in
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            content.classList.remove('scale-95', 'opacity-0');
        });

        const close = () => {
            backdrop.classList.add('opacity-0');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.remove(), 300);
        };

        document.getElementById(`${modalId}-cancel`).onclick = close;
        document.getElementById(`${modalId}-confirm`).onclick = () => {
            close();
            callback();
        };
    },

    // Konfirmasi hapus (merah)
    confirmDelete: function(msg, callback) {
        CP.confirm(msg, callback, {
            title: 'Hapus Data?',
            icon: 'fa-trash',
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
/* Custom Alert & Modal Styles */
/* These are kept just in case but most styles are handled by Tailwind utility classes */
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
