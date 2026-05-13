<?php
// Pastikan variabel $notifikasi sudah tersedia jika ingin menampilkan badge di sini.
// Biasanya dipanggil di controller/main page sebelum include header.
$notifikasi_count = isset($notifikasi) ? count($notifikasi) : 0;
?>

<header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
    <h2 class="text-xl font-bold text-slate-800 hidden sm:block"><?php echo isset($page_title) ? $page_title : 'Dashboard Overview'; ?></h2>
    <!-- Mobile Title -->
    <h2 class="text-lg font-bold text-slate-800 sm:hidden"><?php echo isset($page_title_mobile) ? $page_title_mobile : 'Overview'; ?></h2>
    
    <div class="flex items-center gap-4 ml-auto">
        <div class="relative" id="notifContainer">
            <button id="notifButton" class="relative text-gray-500 hover:text-emerald-600 transition p-2 rounded-full hover:bg-gray-100">
                <i class="far fa-bell text-xl"></i>
                <?php if ($notifikasi_count > 0): ?>
                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white">
                        <?php echo $notifikasi_count; ?>
                    </span>
                <?php endif; ?>
            </button>
            
            <!-- Dropdown Notifikasi -->
            <div id="notifDropdown" class="hidden absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden transform origin-top-right transition-all scale-95 opacity-0">
                <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-slate-800">Notifikasi Reproduksi</h3>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-bold uppercase tracking-wider"><?php echo $notifikasi_count; ?> Baru</span>
                </div>
                
                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                        <?php if ($notifikasi_count > 0): ?>
                            <div class="divide-y divide-gray-50">
                                <?php foreach($notifikasi as $notif): 
                                    $color = isset($notif['color']) ? $notif['color'] : 'emerald';
                                    $bg_class = "bg-{$color}-50 text-{$color}-600 border-{$color}-100";
                                ?>
                                    <div class="p-4 hover:bg-gray-50 transition flex gap-4 items-start group">
                                        <div class="w-10 h-10 rounded-xl <?php echo $bg_class; ?> border flex items-center justify-center shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="<?php echo $notif['icon']; ?> text-[16px]"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-<?php echo $color; ?>-600">Sapi #<?php echo $notif['kode_sapi']; ?></span>
                                                <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Baru Saja</span>
                                            </div>
                                            <p class="text-[13px] text-slate-700 leading-relaxed font-medium"><?php echo $notif['msg']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                    <?php else: ?>
                        <div class="p-10 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-bell-slash text-gray-300 text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-400">Tidak ada notifikasi baru.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-3 border-t border-gray-50 bg-gray-50/30 text-center">
                    <a href="notifications.php" class="text-xs text-emerald-600 font-bold hover:underline">Lihat Semua Notifikasi</a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('notifButton');
                const dropdown = document.getElementById('notifDropdown');
                
                if (btn && dropdown) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (dropdown.classList.contains('hidden')) {
                            dropdown.classList.remove('hidden');
                            setTimeout(() => {
                                dropdown.classList.remove('scale-95', 'opacity-0');
                                dropdown.classList.add('scale-100', 'opacity-100');
                            }, 10);
                        } else {
                            dropdown.classList.remove('scale-100', 'opacity-100');
                            dropdown.classList.add('scale-95', 'opacity-0');
                            setTimeout(() => dropdown.classList.add('hidden'), 200);
                        }
                    });
                    
                    document.addEventListener('click', function(e) {
                        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                            dropdown.classList.remove('scale-100', 'opacity-100');
                            dropdown.classList.add('scale-95', 'opacity-0');
                            setTimeout(() => dropdown.classList.add('hidden'), 200);
                        }
                    });
                }
            });
        </script>

        <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
        <?php include '../components/profile_dropdown.php'; ?>
    </div>
</header>
