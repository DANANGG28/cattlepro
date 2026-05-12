<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
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
        $item['update_terakhir'] = 'IB: ' . tgl_indo(date('Y-m-d H:i:s', $waktu_ib), true);
        
        $waktu_pantau = $waktu_ib + (21 * 24 * 3600);
        $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
        
        $item['jadwal_detail'] = [
            ['label' => 'Pantau Birahi Ulang (H+21):', 'date' => tgl_indo(date('Y-m-d', $waktu_pantau)), 'color' => 'text-blue-700'],
            ['label' => 'Cek PKB (H+60):', 'date' => tgl_indo(date('Y-m-d', $waktu_pkb)), 'color' => 'text-purple-700']
        ];
    } elseif ($status == 'Sudah Birahi') {
        $latest_birahi = $sapi->getLatestBirahi($s['id']);
        if ($latest_birahi && isset($latest_birahi['tanggalBirahi'])) {
            $waktu_birahi = strtotime($latest_birahi['tanggalBirahi']);
            $item['update_terakhir'] = 'Birahi: ' . tgl_indo(date('Y-m-d', $waktu_birahi));
            $waktu_ib_awal = $waktu_birahi + (12 * 3600);
            $waktu_ib_akhir = $waktu_birahi + (18 * 3600);
            $item['jadwal_detail'] = [
                ['label' => 'Jadwal IB Optimal:', 'date' => tgl_indo(date('Y-m-d', $waktu_ib_awal)) . ' (' . date('H:i', $waktu_ib_awal) . ' - ' . date('H:i', $waktu_ib_akhir) . ')', 'color' => 'text-pink-700']
            ];
        }
    } elseif ($status == 'Bunting' && $item['tanggal_ib']) {
        $waktu_ib = strtotime($item['tanggal_ib']);
        $item['update_terakhir'] = 'IB: ' . tgl_indo(date('Y-m-d H:i:s', $waktu_ib), true);
        $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
        $item['jadwal_detail'] = [
            ['label' => 'Perkiraan Melahirkan (HPL):', 'date' => tgl_indo(date('Y-m-d', $waktu_hpl)), 'color' => 'text-green-700']
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Monitoring Pemeriksaan & Reproduksi</h2>
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Pemeriksaan</h2>
        <div class="flex items-center gap-4 ml-auto">
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">

        <!-- Progress Reproduksi Realtime Table & Cards -->
        <?php
            $status_colors = [
                'Kosong' => 'bg-gray-100 text-gray-600 border border-gray-300',
                'Sudah Birahi' => 'bg-pink-50 text-pink-700 border border-pink-200',
                'Sudah IB' => 'bg-blue-50 text-blue-600 border border-blue-200',
                'Bunting' => 'bg-green-50 text-green-700 border border-green-200',
                'Gagal Hamil' => 'bg-red-50 text-red-700 border border-red-200'
            ];
        ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-white to-blue-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 shadow-sm">
                        <i class="fas fa-stethoscope text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[16px] text-slate-800">Progress Reproduksi Realtime</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span class="text-[10px] text-blue-600 uppercase font-bold tracking-widest">Live Monitoring System</span>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md sm:mx-4">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="pemeriksaanSearch" placeholder="Cari Kode Sapi atau Jenis..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm">
                </div>

                <div class="flex items-center gap-2">
                    <a href="export_sapi.php" target="_blank" class="hidden sm:flex bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl text-[12px] hover:bg-gray-50 transition-all shadow-sm items-center gap-2 group">
                        <i class="fas fa-file-pdf text-red-500 group-hover:scale-110 transition-transform"></i> Export Laporan
                    </a>
                    <a href="export_sapi.php" target="_blank" class="sm:hidden w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-red-500 shadow-sm">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Data Sapi</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em] text-center">Status Terkini</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Update Terakhir</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Jadwal / Tindakan Selanjutnya</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="tableBody">
                        <?php if (count($monitoring_data) > 0): ?>
                            <?php foreach ($monitoring_data as $item): ?>
                            <?php $badge = isset($status_colors[$item['status_reproduksi']]) ? $status_colors[$item['status_reproduksi']] : $status_colors['Kosong']; ?>
                            <tr class="hover:bg-gray-50/50 transition pemeriksaan-row">
                                <td class="p-4">
                                    <p class="font-bold text-slate-800 text-base search-target"><?php echo htmlspecialchars($item['kode_sapi']); ?></p>
                                    <p class="text-xs text-gray-400 mt-0.5 search-target-sub"><?php echo htmlspecialchars($item['jenis']); ?></p>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm <?php echo $badge; ?> border border-current/10">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-50"></span>
                                        <?php echo $item['status_reproduksi']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600 text-sm font-medium">
                                    <?php echo $item['update_terakhir']; ?>
                                </td>
                                <td class="p-4">
                                    <?php if (!empty($item['jadwal_detail'])): ?>
                                        <div class="flex items-start gap-3">
                                            <?php
                                            $icon = 'fa-calendar-check text-blue-600';
                                            $bg_icon = 'bg-blue-50';
                                            
                                            if ($item['status_reproduksi'] == 'Sudah Birahi') {
                                                $icon = 'fa-clock text-pink-600';
                                                $bg_icon = 'bg-pink-50';
                                            } elseif ($item['status_reproduksi'] == 'Bunting') {
                                                $icon = 'fa-calendar-check text-green-600';
                                                $bg_icon = 'bg-green-50';
                                            } elseif ($item['status_reproduksi'] == 'Kosong') {
                                                $icon = 'fa-search text-gray-400';
                                                $bg_icon = 'bg-gray-50';
                                            }
                                            ?>
                                            <div class="w-9 h-9 rounded-xl <?php echo $bg_icon; ?> flex items-center justify-center flex-shrink-0 shadow-sm border border-current/5">
                                                <i class="fas <?php echo $icon; ?> text-sm"></i>
                                            </div>
                                            <div class="text-sm space-y-1">
                                                <?php foreach ($item['jadwal_detail'] as $jd): ?>
                                                    <p class="leading-tight">
                                                        <span class="font-bold <?php echo $jd['color']; ?> text-[13px]"><?php echo $jd['label']; ?></span>
                                                        <span class="text-slate-600 font-medium ml-1"><?php echo $jd['date']; ?></span>
                                                    </p>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="detail_sapi.php?id=<?php echo $item['id']; ?>" class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 inline-flex items-center justify-center hover:bg-emerald-100 hover:text-emerald-700 transition" title="Lihat Detail">
                                            <i class="fas fa-stethoscope text-sm"></i>
                                        </a>
                                        <a href="export_sapi.php?id=<?php echo $item['id']; ?>" target="_blank" class="w-9 h-9 rounded-full bg-red-50 text-red-600 inline-flex items-center justify-center hover:bg-red-100 transition" title="Export PDF">
                                            <i class="fas fa-file-pdf text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-10 text-center text-gray-400">Belum ada data sapi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100" id="cardContainer">
                <?php if (count($monitoring_data) > 0): ?>
                    <?php foreach ($monitoring_data as $item): ?>
                    <?php $badge = isset($status_colors[$item['status_reproduksi']]) ? $status_colors[$item['status_reproduksi']] : $status_colors['Kosong']; ?>
                    <div class="p-5 space-y-4 pemeriksaan-card">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 search-target-mobile"><?php echo htmlspecialchars($item['kode_sapi']); ?></h3>
                                <p class="text-xs text-gray-400 search-target-sub-mobile"><?php echo htmlspecialchars($item['jenis']); ?></p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm <?php echo $badge; ?> border border-current/10">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-50"></span>
                                <?php echo $item['status_reproduksi']; ?>
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider mb-2">Update Terakhir</p>
                            <p class="text-sm font-semibold text-gray-700"><?php echo $item['update_terakhir']; ?></p>
                        </div>

                        <div class="space-y-3">
                            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Jadwal / Tindakan Selanjutnya</p>
                            <?php if (!empty($item['jadwal_detail'])): ?>
                                <?php foreach ($item['jadwal_detail'] as $jd): ?>
                                <div class="flex items-start gap-3 p-3 bg-white border border-gray-100 rounded-xl shadow-sm">
                                    <?php if (!empty($jd['date'])): ?>
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                            <i class="fas fa-calendar-alt text-xs"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center shrink-0">
                                            <i class="fas <?php echo (isset($jd['icon']) ? $jd['icon'] : 'search') === 'exclamation' ? 'fa-exclamation-triangle' : 'fa-search'; ?> text-xs"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-[11px] font-bold <?php echo $jd['color']; ?> leading-tight"><?php echo $jd['label']; ?></p>
                                        <?php if (!empty($jd['date'])): ?>
                                            <p class="text-xs font-bold text-slate-700 mt-1"><?php echo $jd['date']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <a href="detail_sapi.php?id=<?php echo $item['id']; ?>" class="flex-1 bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl text-xs text-center flex items-center justify-center gap-2 shadow-lg shadow-emerald-200">
                                <i class="fas fa-stethoscope"></i> Detail Sapi
                            </a>
                            <a href="export_sapi.php?id=<?php echo $item['id']; ?>" target="_blank" class="flex-1 bg-red-50 text-red-600 font-bold py-3 px-4 rounded-xl text-xs text-center flex items-center justify-center gap-2 border border-red-100">
                                <i class="fas fa-file-pdf"></i> Export Laporan
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-10 text-center text-gray-400">Belum ada data.</div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script>
document.getElementById('pemeriksaanSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    // Filter Desktop Table
    const tableRows = document.querySelectorAll('.pemeriksaan-row');
    tableRows.forEach(row => {
        const kode = row.querySelector('.search-target').innerText.toLowerCase();
        const jenis = row.querySelector('.search-target-sub').innerText.toLowerCase();
        if (kode.includes(searchTerm) || jenis.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Filter Mobile Cards
    const cards = document.querySelectorAll('.pemeriksaan-card');
    cards.forEach(card => {
        const kode = card.querySelector('.search-target-mobile').innerText.toLowerCase();
        const jenis = card.querySelector('.search-target-sub-mobile').innerText.toLowerCase();
        if (kode.includes(searchTerm) || jenis.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
