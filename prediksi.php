<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$semua_sapi = $sapi->readAll()->fetchAll(PDO::FETCH_ASSOC);
$monitoring_data = [];

// Process monitoring data for all cows
foreach ($semua_sapi as $s) {
    $item = [];
    $item['id'] = $s['id'];
    $item['kode_sapi'] = $s['kode_sapi'];
    $item['jenis'] = $s['jenis'];
    $item['status_reproduksi'] = isset($s['status_reproduksi']) ? $s['status_reproduksi'] : 'Kosong';
    $item['tanggal_ib'] = isset($s['tanggal_ib']) ? $s['tanggal_ib'] : null;
    
    // Get update terakhir info
    $item['update_terakhir'] = '';
    $item['jadwal'] = '';
    $item['jadwal_detail'] = [];
    
    $status = $item['status_reproduksi'];
    
    if ($status == 'Sudah IB' && $item['tanggal_ib']) {
        $waktu_ib = strtotime($item['tanggal_ib']);
        $item['update_terakhir'] = 'IB: ' . date('d M Y, H:i', $waktu_ib);
        
        $waktu_pantau = $waktu_ib + (21 * 24 * 3600);
        $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
        
        $item['jadwal_detail'] = [
            ['label' => 'Pantau Birahi Ulang (H+21):', 'date' => date('d M Y', $waktu_pantau), 'color' => 'text-blue-700'],
            ['label' => 'Cek PKB (H+60):', 'date' => date('d M Y', $waktu_pkb), 'color' => 'text-purple-700']
        ];
    } elseif ($status == 'Sudah Birahi') {
        $latest_birahi = $sapi->getLatestBirahi($s['id']);
        if ($latest_birahi) {
            $waktu_birahi = strtotime($latest_birahi['tanggal_birahi']);
            $item['update_terakhir'] = 'Birahi: ' . date('d M Y, H:i', $waktu_birahi);
            $waktu_ib_optimal = $waktu_birahi + (12 * 3600);
            $item['jadwal_detail'] = [
                ['label' => 'Jadwal IB Optimal:', 'date' => date('d M Y, H:i', $waktu_ib_optimal), 'color' => 'text-pink-700']
            ];
        }
    } elseif ($status == 'Bunting' && $item['tanggal_ib']) {
        $waktu_ib = strtotime($item['tanggal_ib']);
        $item['update_terakhir'] = 'IB: ' . date('d M Y, H:i', $waktu_ib);
        $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
        $item['jadwal_detail'] = [
            ['label' => 'Perkiraan Melahirkan (HPL):', 'date' => date('d M Y', $waktu_hpl), 'color' => 'text-green-700']
        ];
    } elseif ($status == 'Kosong') {
        $item['update_terakhir'] = '-';
        $item['jadwal_detail'] = [
            ['label' => 'Pantau siklus birahi sapi secara rutin.', 'date' => '', 'color' => 'text-gray-500', 'icon' => 'search']
        ];
    } elseif ($status == 'Gagal Hamil') {
        $item['update_terakhir'] = '-';
        $item['jadwal_detail'] = [
            ['label' => 'Evaluasi & reset status untuk siklus baru.', 'date' => '', 'color' => 'text-red-600', 'icon' => 'exclamation']
        ];
    }
    
    $monitoring_data[] = $item;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pemeriksaan & Reproduksi - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include 'components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Monitoring Pemeriksaan & Reproduksi</h2>
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Pemeriksaan</h2>
        <div class="flex items-center gap-4 ml-auto">
            <?php include 'components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">

        <!-- Progress Reproduksi Realtime Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center gap-3">
                <i class="fas fa-stethoscope text-blue-600 text-xl"></i>
                <h3 class="font-bold text-[16px] text-slate-800">Progress Reproduksi Realtime</h3>
                <span class="text-[11px] text-blue-600 bg-blue-50 px-3 py-1 rounded-full font-bold border border-blue-200">Live Tracker</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100">
                        <tr>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Data Sapi</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider text-center">Status Terkini</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Update Terakhir</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Jadwal / Tindakan Selanjutnya</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (count($monitoring_data) > 0): ?>
                            <?php foreach ($monitoring_data as $item): ?>
                            <?php
                                $status_colors = [
                                    'Kosong' => 'bg-gray-100 text-gray-600 border border-gray-300',
                                    'Sudah Birahi' => 'bg-pink-50 text-pink-700 border border-pink-200',
                                    'Sudah IB' => 'bg-blue-50 text-blue-600 border border-blue-200',
                                    'Bunting' => 'bg-green-50 text-green-700 border border-green-200',
                                    'Gagal Hamil' => 'bg-red-50 text-red-700 border border-red-200'
                                ];
                                $badge = isset($status_colors[$item['status_reproduksi']]) ? $status_colors[$item['status_reproduksi']] : $status_colors['Kosong'];
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <!-- Data Sapi -->
                                <td class="p-4">
                                    <p class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($item['kode_sapi']); ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($item['jenis']); ?></p>
                                </td>
                                
                                <!-- Status Terkini -->
                                <td class="p-4 text-center">
                                    <span class="inline-block px-3 py-1.5 rounded-full text-[12px] font-bold <?php echo $badge; ?>">
                                        <?php echo $item['status_reproduksi']; ?>
                                    </span>
                                </td>
                                
                                <!-- Update Terakhir -->
                                <td class="p-4 text-gray-600 text-sm font-medium">
                                    <?php echo $item['update_terakhir']; ?>
                                </td>
                                
                                <!-- Jadwal / Tindakan Selanjutnya -->
                                <td class="p-4">
                                    <?php if (!empty($item['jadwal_detail'])): ?>
                                        <div class="flex items-start gap-2.5">
                                            <?php
                                            $has_schedule = false;
                                            foreach ($item['jadwal_detail'] as $jd) {
                                                if (!empty($jd['date'])) { $has_schedule = true; break; }
                                            }
                                            ?>
                                            <?php if ($has_schedule): ?>
                                                <i class="fas fa-calendar-check text-blue-600 text-[18px] mt-0.5 flex-shrink-0"></i>
                                            <?php else: ?>
                                                <i class="fas fa-search text-gray-400 text-[18px] mt-0.5 flex-shrink-0"></i>
                                            <?php endif; ?>
                                            <div class="text-sm leading-relaxed space-y-1">
                                                <?php foreach ($item['jadwal_detail'] as $jd): ?>
                                                    <?php if (!empty($jd['date'])): ?>
                                                        <p>
                                                            <span class="font-bold <?php echo $jd['color']; ?>"><?php echo $jd['label']; ?></span>
                                                            <span class="text-gray-600"><?php echo $jd['date']; ?></span>
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="<?php echo $jd['color']; ?>"><?php echo $jd['label']; ?></p>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Aksi -->
                                <td class="p-4 text-center">
                                    <a href="detail_sapi.php?id=<?php echo $item['id']; ?>" class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 inline-flex items-center justify-center hover:bg-blue-100 hover:text-blue-700 transition" title="Lihat Detail">
                                        <i class="fas fa-arrow-right text-sm"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                            <i class="fas fa-stethoscope text-gray-300 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-400 font-medium">Belum ada data sapi untuk dimonitor</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
