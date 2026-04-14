<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
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

$notifikasi = [];

// Loop untuk kalkulasi stats dan generate alert notifikasi
foreach($semua_sapi as $key => $s) {
    $status = isset($s['status_reproduksi']) ? $s['status_reproduksi'] : 'Kosong';
    
    // Default sub status date format for the table
    $semua_sapi[$key]['last_update_text'] = '';
    $semua_sapi[$key]['last_update_date'] = '';

    if ($status == 'Kosong') {
        $count_kosong++;
    } elseif ($status == 'Gagal Hamil') {
        $count_gagal_hamil++;
    } elseif ($status == 'Sudah Birahi') {
        $count_birahi++;
        $latest = $sapi->getLatestBirahi($s['id']);
        if ($latest) {
             $semua_sapi[$key]['last_update_text'] = 'Birahi';
             $semua_sapi[$key]['last_update_date'] = date('d M Y', strtotime($latest['tanggal_birahi']));
             
             $waktu_birahi = strtotime($latest['tanggal_birahi']);
             $sisa_jam = round((($waktu_birahi + (12 * 3600)) - time()) / 3600);
             if ($sisa_jam > 0 && $sisa_jam <= 12) {
                  $notifikasi[] = [
                     'icon' => 'fas fa-exclamation-circle text-orange-500',
                     'bg' => 'bg-orange-50 border-orange-100/50',
                     'msg' => "Segera lakukan Inseminasi! Sapi <b>{$s['kode_sapi']}</b> sedang dalam masa birahi optimal (Sisa {$sisa_jam} Jam)."
                 ];
             }
        }
    } elseif ($status == 'Sudah IB') {
        $count_ib++;
        if ($s['tanggal_ib']) {
             $semua_sapi[$key]['last_update_text'] = 'IB';
             $semua_sapi[$key]['last_update_date'] = date('d M Y', strtotime($s['tanggal_ib']));
             
             $waktu_ib = strtotime($s['tanggal_ib']);
             $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
             $sisa_hari_pkb = round(($waktu_pkb - time()) / (24 * 3600));
             
             if ($sisa_hari_pkb <= 15 && $sisa_hari_pkb > 0) {
                 $notifikasi[] = [
                     'icon' => 'fas fa-info-circle text-blue-500',
                     'bg' => 'bg-blue-50/70 border-blue-100',
                     'msg' => "Sapi <b>{$s['kode_sapi']}</b> mendekati jadwal Cek Kehamilan (PKB) pada " . date('d M Y', $waktu_pkb) . " (H-{$sisa_hari_pkb})."
                 ];
             } elseif ($sisa_hari_pkb <= 0) {
                 $notifikasi[] = [
                     'icon' => 'fas fa-stethoscope text-blue-600',
                     'bg' => 'bg-blue-100 border-blue-200',
                     'msg' => "Sudah masuk jadwal PKB untuk sapi <b>{$s['kode_sapi']}</b>. Segera lakukan pemeriksaan!"
                 ];
             }
        }
    } elseif ($status == 'Bunting') {
        $count_bunting++;
        if ($s['tanggal_ib']) {
             $semua_sapi[$key]['last_update_text'] = 'PKB'; // Assumption, but actually based on IB
             $semua_sapi[$key]['last_update_date'] = date('d M Y', strtotime($s['tanggal_ib'])); // Simplification
             
             $waktu_ib = strtotime($s['tanggal_ib']);
             $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
             $sisa_hari_hpl = round(($waktu_hpl - time()) / (24 * 3600));
             
             if ($sisa_hari_hpl <= 30 && $sisa_hari_hpl > 0) {
                 $notifikasi[] = [
                     'icon' => 'fas fa-leaf text-emerald-500',
                     'bg' => 'bg-emerald-50/80 border-emerald-100/50',
                     'msg' => "Persiapan kelahiran! Sapi <b>{$s['kode_sapi']}</b> diestimasi melahirkan " . date('d M Y', $waktu_hpl) . " (H-{$sisa_hari_hpl})."
                 ];
             } elseif ($sisa_hari_hpl <= 0) {
                 $notifikasi[] = [
                     'icon' => 'fas fa-baby text-emerald-700',
                     'bg' => 'bg-emerald-100 border-emerald-200',
                     'msg' => "Sapi <b>{$s['kode_sapi']}</b> telah melewati HPL / Sedang proses kelahiran. Segera laporkan kelahiran."
                 ];
             }
        }
    }
}

// Slice to show only a few records (e.g. 5) in the table on dashboard
$recent_sapi_table = array_slice($semua_sapi, 0, 5);

// Get recent activities for the timeline log
$recentActivities = $sapi->getRecentActivities(8)->fetchAll(PDO::FETCH_ASSOC);
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Overview - CattlePro</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include 'components/sidebar.php'; ?>

<!-- Main Content -->
<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <!-- Topbar -->
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Dashboard Overview</h2>
        <!-- Mobile Title -->
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Overview</h2>
        
        <div class="flex items-center gap-4 ml-auto">
            <button class="relative text-gray-500 hover:text-emerald-600 transition">
                <i class="far fa-bell text-xl"></i>
                <?php if (count($notifikasi) > 0): ?>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3 items-center justify-center rounded-full bg-red-500 text-[9px] text-white">
                        <?php echo count($notifikasi); ?>
                    </span>
                <?php endif; ?>
            </button>
            <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
            <?php include 'components/profile_dropdown.php'; ?>
        </div>
    </header>

    <!-- Content Area -->
    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6">
            
            <!-- Total -->
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
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1 truncate">Gagal Hamil</h3>
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

            <!-- PKB -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 relative overflow-hidden group">
                 <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium">Pasca IB</span>
                </div>
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Menunggu PKB</h3>
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
                <h3 class="text-xs sm:text-[13px] text-gray-500 font-semibold mb-1">Sapi Bunting</h3>
                <div class="text-2xl sm:text-3xl font-bold text-slate-800"><?php echo $count_bunting; ?></div>
            </div>
        </div>

        <!-- Instruksi & Notifikasi Reproduksi -->
        <?php if (count($notifikasi) > 0): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-50 flex items-center gap-2">
                <i class="fas fa-bolt text-yellow-500 text-lg"></i>
                <h3 class="font-bold text-[15px] text-slate-800">Instruksi & Notifikasi Reproduksi</h3>
            </div>
            <div class="p-4 space-y-3 bg-gray-50/50">
                <?php foreach($notifikasi as $notif): ?>
                <div class="flex items-center gap-3 <?php echo $notif['bg']; ?> p-3.5 rounded-xl border">
                    <div class="w-6 h-6 shrink-0 flex items-center justify-center">
                        <i class="<?php echo $notif['icon']; ?> text-lg"></i>
                    </div>
                    <p class="text-[13px] sm:text-sm text-slate-700 leading-relaxed"><?php echo $notif['msg']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

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
                    <button class="text-xs text-emerald-600 font-bold hover:underline" onclick="alert('Feature coming soon')">Lihat Semua</button>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                    <div class="relative border-l-2 border-gray-100 ml-3 space-y-6 pb-4">
                        <?php foreach ($recentActivities as $act): 
                            $iconClass = "fas fa-info-circle";
                            $colorClass = "text-gray-500 bg-gray-100";
                            $iconColor = "text-gray-500";
                            $title = str_replace('_', ' ', ucwords($act['jenis_aktivitas']));
                            
                            if ($act['jenis_aktivitas'] == 'tambah_sapi') {
                                $colorClass = "bg-blue-100";
                                $iconColor = "text-blue-500";
                                $iconClass = "fas fa-plus";
                                $title = "Registrasi Sapi";
                                $actionName = "mendaftarkan";
                            } elseif ($act['jenis_aktivitas'] == 'tambah_birahi') {
                                $colorClass = "bg-pink-100";
                                $iconColor = "text-pink-500";
                                $iconClass = "fas fa-venus-mars";
                                $title = "Birahi Tercatat";
                                $actionName = "mencatat";
                            } elseif ($act['jenis_aktivitas'] == 'hitung_prediksi') {
                                $colorClass = "bg-purple-100";
                                $iconColor = "text-purple-500";
                                $iconClass = "fas fa-stethoscope";
                                $title = "Diagnosis Medis";
                                $actionName = "mengecek";
                            } else {
                                $actionName = "melakukan";
                            }
                            
                            $adminName = htmlspecialchars(explode(' ', $act['nama'])[0]);
                        ?>
                        <div class="relative pl-6 group">
                            <span class="absolute -left-[14px] top-1 flex h-7 w-7 items-center justify-center rounded-full <?php echo $colorClass; ?> ring-4 ring-white shadow-sm transition-transform group-hover:scale-110">
                                <i class="<?php echo $iconClass; ?> text-[11px] <?php echo $iconColor; ?>"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-[13px] group-hover:text-emerald-700 transition-colors"><?php echo $title; ?></h4>
                                <p class="text-[12px] text-gray-500 mt-1 leading-relaxed"><span class="font-bold text-gray-700"><?php echo $adminName; ?></span> <?php echo htmlspecialchars($act['deskripsi']); ?></p>
                                <div class="flex items-center gap-3 mt-1.5 text-[10px] text-gray-400 font-semibold tracking-wide">
                                    <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($act['created_at'])); ?></span>
                                    <span class="flex items-center gap-1 px-1.5 py-0.5 bg-gray-50 rounded text-gray-500"><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars(ucfirst($act['role'])); ?></span>
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
                            color: '#6b7280'
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

</body>
</html>
