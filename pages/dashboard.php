<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Data Processing for the unified dashboard
$semua_sapi = $sapi->readAll()->fetchAll(PDO::FETCH_ASSOC);

$totalSapi = count($semua_sapi);
$count_birahi = 0;
$count_ib = 0;
$count_bunting = 0;
$count_kosong = 0;
$count_gagal_hamil = 0;

$notifikasi = $sapi->getReproductionNotifications();

// Loop untuk kalkulasi stats
foreach($semua_sapi as $s) {
    $status = isset($s['status_reproduksi']) ? $s['status_reproduksi'] : 'Kosong';
    if ($status == 'Kosong') $count_kosong++;
    elseif ($status == 'Gagal Hamil') $count_gagal_hamil++;
    elseif ($status == 'Sudah Birahi') $count_birahi++;
    elseif ($status == 'Sudah IB') $count_ib++;
    elseif ($status == 'Bunting') $count_bunting++;
}

// Slice to show only a few records (e.g. 5) in the table on dashboard
$recent_sapi_table = array_slice($semua_sapi, 0, 5);

// Get recent activities for the timeline log
$recentActivities = $sapi->getRecentActivities(5)->fetchAll(PDO::FETCH_ASSOC);
// Lazy load: allActivities akan di-load saat modal dibuka (lihat inline di bawah)
$allActivities = null;

// Activity Visualization Config
$activity_config = [
    'tambah_sapi'   => ['icon' => 'fas fa-plus',        'bg' => 'bg-blue-100',    'color' => 'text-blue-600',    'label' => 'Registrasi Sapi'],
    'edit_sapi'     => ['icon' => 'fas fa-edit',        'bg' => 'bg-slate-100',   'color' => 'text-slate-600',   'label' => 'Update Data'],
    'hapus_sapi'    => ['icon' => 'fas fa-trash',       'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Hapus Sapi'],
    'import_sapi'   => ['icon' => 'fas fa-file-excel',  'bg' => 'bg-green-100',   'color' => 'text-green-700',   'label' => 'Import Excel'],
    'tambah_birahi' => ['icon' => 'fas fa-venus-mars',  'bg' => 'bg-pink-100',    'color' => 'text-pink-600',    'label' => 'Laporan Birahi'],
    'batal_birahi'  => ['icon' => 'fas fa-undo',        'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Batal Birahi'],
    'inseminasi'    => ['icon' => 'fas fa-syringe',     'bg' => 'bg-amber-100',   'color' => 'text-amber-600',   'label' => 'Inseminasi (IB)'],
    'batal_ib'      => ['icon' => 'fas fa-times',       'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Batal IB'],
    'pkb'           => ['icon' => 'fas fa-stethoscope', 'bg' => 'bg-purple-100',  'color' => 'text-purple-600',  'label' => 'Pemeriksaan (PKB)'],
    'kelahiran'     => ['icon' => 'fas fa-baby',        'bg' => 'bg-emerald-100', 'color' => 'text-emerald-600', 'label' => 'Kelahiran'],
    'batal_bunting' => ['icon' => 'fas fa-undo',        'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Batal Bunting'],
    'reset_gagal'   => ['icon' => 'fas fa-sync',        'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Reset Siklus'],
];
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CattlePro</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'><path fill='%2300D084' d='M109.9 80.2L91.8 11.2C88.6 .9 77.9-3.7 67.9 1.4L44.4 13.5C28.2 21.8 18.2 38.6 18.2 56.8c0 14.6 7.4 28.2 19.5 36.1l32.1 21.1c-16.7 30.6-25.1 65-25.1 99.8l0 10.4c0 38 12.6 74.9 36 104.3l-1.3 5C74.6 350.3 64 369.3 64 389.9l0 69.1c0 15 9.1 28.3 23.1 33.9l32 12.8c12 4.8 25.7 1.2 33.9-8.8l21.2-25.8c29.1 23.3 65.2 36.6 102.6 37.1l.6 0c37.5-.5 73.5-13.8 102.6-37.1l21.2 25.8c8.2 10 21.9 13.6 33.9 8.8l32-12.8c14-5.6 23.1-18.9 23.1-33.9l0-69.1c0-20.6-10.6-39.6-28.1-48.9l-19.1-10.1c11.6-21.7 17.6-45.9 17.6-70.6l0-10.4c0-34.8-8.4-69.2-25.1-99.8l32.1-21.1c12.1-7.9 19.5-21.5 19.5-36.1c0-18.2-10-35-26.2-43.3l-23.5-12.1c-10-5.1-20.7-.5-23.9 9.8l-18.1 69C314.9 66 288.3 45.5 258.9 29.9l0-19.2c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 6.6c-28.5 7.6-54.3 22-75.1 41.9zM224 224a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z'/></svg>">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<!-- Main Content -->
<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <!-- Topbar -->
    <?php 
    $page_title = 'Dashboard Overview';
    $page_title_mobile = 'Overview';
    include '../components/header.php'; 
    ?>
    </header>

    <!-- Content Area -->
    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            
            <!-- Total Sapi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-full">+1 Hari Ini</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Total Sapi</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $totalSapi; ?></div>
            </div>

            <!-- Gagal Hamil -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Perhatian</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1 truncate"> Sapi Gagal Hamil</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_gagal_hamil; ?></div>
            </div>

            <!-- Birahi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Fase Kritis</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1 truncate">Sapi Birahi (Butuh IB)</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_birahi; ?></div>
            </div>

            <!-- Pemeriksaan Kebuntingan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                 <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Pasca Inseminasi</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Menunggu Pemeriksaan</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_ib; ?></div>
            </div>

            <!-- Bunting -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                 <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Sehat</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Sapi Hamil</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_bunting; ?></div>
            </div>

            <!-- bUNTING 2 -->
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                 <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Sehat</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Sapi Hamil 2</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_bunting; ?></div>
            </div>
        </div>


        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <!-- Sisi Kiri (Tabel & Grafik Bar) -->
            <div class="xl:col-span-2 flex flex-col gap-6">
                
                <!-- Grafik Bar Animasi -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                    <h3 class="font-bold text-[16px] text-slate-800 mb-6 flex items-center gap-2"><i class="fas fa-chart-column text-emerald-500"></i> Distribusi Populasi Sapi Berdasarkan Status</h3>
                    <div class="relative w-full h-[250px]">
                        <canvas id="reproChart"></canvas>
                    </div>
                </div>

                <!-- Pintasan Cepat (Pengganti Tabel) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="sapi.php" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-emerald-300 hover:shadow-sm transition group flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition shadow-sm border border-white">
                            <i class="fas fa-cow text-xl"></i>
                        </div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-700 text-center">Kelola Sapi</span>
                    </a>
                    <a href="prediksi.php" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-emerald-300 hover:shadow-sm transition group flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition shadow-sm border border-white">
                            <i class="fas fa-satellite-dish text-xl"></i>
                        </div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-700 text-center">Live Tracker</span>
                    </a>
                    <a href="sapi.php" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-emerald-300 hover:shadow-sm transition group flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition shadow-sm border border-white">
                            <i class="fas fa-stethoscope text-xl"></i>
                        </div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-700 text-center">Pemeriksaan</span>
                    </a>
                    <a href="#" onclick="window.print(); return false;" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:border-emerald-300 hover:shadow-sm transition group flex flex-col items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition shadow-sm border border-white">
                            <i class="fas fa-print text-xl"></i>
                        </div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-gray-700 text-center">Cetak Laporan</span>
                    </a>
                </div>

            </div> <!-- End Sisi Kiri -->

            <!-- Sisi Kanan (Log Aktivitas) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col xl:col-span-1">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-50">
                    <h3 class="font-bold text-[16px] text-slate-800 flex items-center gap-2"><i class="fas fa-history text-slate-400"></i> Aktivitas Terbaru</h3>
                    <button onclick="document.getElementById('modal-all-activity').classList.remove('hidden')" class="text-xs text-emerald-600 font-bold hover:underline">Lihat Semua</button>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                    <div class="relative border-l-2 border-gray-100 ml-3 space-y-6 pb-4">
                        <?php foreach ($recentActivities as $act): 
                            $cfg = isset($activity_config[$act['jenis_aktivitas']]) ? $activity_config[$act['jenis_aktivitas']] : array(
                                'icon' => 'fas fa-info-circle',
                                'bg' => 'bg-gray-100',
                                'color' => 'text-gray-500',
                                'label' => str_replace('_', ' ', ucwords($act['jenis_aktivitas']))
                            );
                            $adminName = htmlspecialchars(explode(' ', $act['nama'])[0]);
                        ?>
                        <div class="relative pl-6 group">
                            <span class="absolute -left-[14px] top-1 flex h-7 w-7 items-center justify-center rounded-full <?php echo $cfg['bg']; ?> ring-4 ring-white shadow-sm transition-transform group-hover:scale-110">
                                <i class="<?php echo $cfg['icon']; ?> text-[11px] <?php echo $cfg['color']; ?>"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-[13px] group-hover:text-emerald-700 transition-colors"><?php echo $cfg['label']; ?></h4>
                                <p class="text-[12px] text-gray-500 mt-1 leading-relaxed"><span class="font-bold text-gray-700"><?php echo $adminName; ?></span> <?php echo htmlspecialchars($act['deskripsi']); ?></p>
                                <div class="flex items-center gap-3 mt-1.5 text-[10px] text-gray-400 font-semibold tracking-wide">
                                    <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?php echo tgl_indo($act['created_at'], true); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentActivities)): ?>
                            <div class="ml-6 flex flex-col items-center justify-center py-10 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-ghost text-gray-300 text-xl"></i>
                                </div>
                                <p class="text-xs text-gray-400 font-medium">Belum ada rekam log aktivitas terbaru.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<script>
    // Inisialisasi ChartJS - Doughnut Chart
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('reproChart').getContext('2d');
        const countData = [<?php echo $count_kosong; ?>, <?php echo $count_birahi; ?>, <?php echo $count_ib; ?>, <?php echo $count_bunting; ?>, <?php echo $count_gagal_hamil; ?>];
        
        // Cek jika datanya kosong semua, buat chart placeholder abu-abu
        const isAllZero = countData.every(item => item === 0);
        const displayData = isAllZero ? [1] : countData;
        const displayColors = isAllZero ? ['#e5e7eb'] : ['#ef4444', '#ec4899', '#f59e0b', '#10b981', '#dc2626'];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Kosong', 'Menunggu IB', 'Menunggu PKB', 'Bunting', 'Gagal Hamil'],
                datasets: [{
                    label: 'Jumlah Sapi',
                    data: displayData,
                    backgroundColor: displayColors,
                    borderRadius: 8,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: !isAllZero,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.y + ' Ekor';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false,
                        },
                        ticks: {
                            stepSize: Math.ceil(Math.max(...displayData) / 4) || 1,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                            color: '#9ca3af'
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' },
                            color: '#6b7280',
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
</script>

<!-- Modal: Semua Aktivitas -->
<div id="modal-all-activity" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl h-[85vh] flex flex-col overflow-hidden animate-fade-in">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-white to-blue-50/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                    <i class="fas fa-history text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Riwayat Aktivitas Lengkap</h3>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mt-0.5">Audit Trail System</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-all-activity').classList.add('hidden')" 
                    class="w-10 h-10 rounded-xl bg-gray-100 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all flex items-center justify-center text-xl">&times;</button>
        </div>

        <!-- Search Bar in Modal -->
        <div class="p-4 bg-gray-50/50 border-b border-gray-100">
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="modalActivitySearch" placeholder="Cari aktivitas, nama petugas, atau deskripsi..." 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all shadow-sm">
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
            <div class="relative border-l-2 border-gray-100 ml-3 space-y-8" id="modalActivityList">
                <?php 
                // Lazy load: hanya query saat halaman sudah di-render
                $allActivities = $sapi->getAllActivities()->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allActivities as $act): 
                    $cfg = isset($activity_config[$act['jenis_aktivitas']]) ? $activity_config[$act['jenis_aktivitas']] : array(
                        'icon' => 'fas fa-info-circle',
                        'bg' => 'bg-gray-100',
                        'color' => 'text-gray-500',
                        'label' => str_replace('_', ' ', ucwords($act['jenis_aktivitas']))
                    );
                ?>
                <div class="relative pl-8 modal-activity-item">
                    <span class="absolute -left-[18px] top-1 flex h-8 w-8 items-center justify-center rounded-xl <?php echo $cfg['bg']; ?> ring-4 ring-white shadow-sm">
                        <i class="<?php echo $cfg['icon']; ?> text-xs <?php echo $cfg['color']; ?>"></i>
                    </span>
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1.5">
                            <h4 class="font-bold text-slate-800 text-sm modal-search-type"><?php echo $cfg['label']; ?></h4>
                            <span class="text-[10px] text-gray-400 font-bold bg-gray-50 px-2 py-0.5 rounded-full uppercase tracking-tighter">
                                <?php echo tgl_indo($act['created_at'], true); ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            <span class="font-bold text-slate-700 modal-search-name"><?php echo htmlspecialchars($act['nama']); ?></span> 
                            <span class="modal-search-desc"><?php echo htmlspecialchars($act['deskripsi']); ?></span>
                        </p>
                        <div class="flex items-center gap-2 mt-2">
                             <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest border border-gray-200 text-gray-400">
                                ID #<?php echo $act['id']; ?> &bull; <?php echo $act['role']; ?>
                             </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Search Logic
document.getElementById('modalActivitySearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.modal-activity-item');
    
    items.forEach(item => {
        const type = item.querySelector('.modal-search-type').innerText.toLowerCase();
        const name = item.querySelector('.modal-search-name').innerText.toLowerCase();
        const desc = item.querySelector('.modal-search-desc').innerText.toLowerCase();
        
        if (type.includes(searchTerm) || name.includes(searchTerm) || desc.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// Close modal on backdrop click
document.getElementById('modal-all-activity').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>

</body>
</html>
