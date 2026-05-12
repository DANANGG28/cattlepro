<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$all_activities = $sapi->getAllActivities()->fetchAll(PDO::FETCH_ASSOC);

// Map activity types to styles
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
    'reset_gagal'   => ['icon' => 'fas fa-sync',        'bg' => 'bg-red-100',     'color' => 'text-red-600',     'label' => 'Reset Siklus']
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Log Aktivitas Sistem</h2>
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Aktivitas</h2>
        <div class="flex items-center gap-4 ml-auto">
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">
        
        <!-- Main Activity Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Unified Section Header -->
            <div class="p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-r from-white to-blue-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 shadow-sm">
                        <i class="fas fa-history text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[16px] text-slate-800">Riwayat Seluruh Aktivitas</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="flex h-2 w-2 rounded-full bg-slate-500 animate-pulse"></span>
                            <span class="text-[10px] text-slate-600 uppercase font-bold tracking-widest">Audit Trail System</span>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md lg:mx-4">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="activitySearch" placeholder="Cari Aktivitas, Nama Admin, atau Deskripsi..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm">
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-4 rounded-xl text-[12px] hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-print text-gray-400"></i> Cetak Log
                    </button>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Waktu</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Jenis Aktivitas</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Admin</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="activityTableBody">
                        <?php foreach ($all_activities as $act): 
                            $config = isset($activity_config[$act['jenis_aktivitas']]) ? $activity_config[$act['jenis_aktivitas']] : array(
                                'icon' => 'fas fa-info-circle',
                                'bg' => 'bg-gray-100',
                                'color' => 'text-gray-500',
                                'label' => str_replace('_', ' ', ucwords($act['jenis_aktivitas']))
                            );
                        ?>
                        <tr class="hover:bg-gray-50/50 transition activity-row">
                            <td class="p-4 whitespace-nowrap">
                                <p class="text-slate-800 font-bold text-[13px]"><?php echo tgl_indo($act['created_at'], true); ?></p>
                                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-tighter mt-0.5"><?php echo date('H:i:s', strtotime($act['created_at'])); ?> WIB</p>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-bold <?php echo $config['bg'] . ' ' . $config['color']; ?> border border-current/10">
                                    <i class="<?php echo $config['icon']; ?> text-[10px] opacity-70"></i>
                                    <span class="search-target-type"><?php echo $config['label']; ?></span>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-[11px]">
                                        <?php echo strtoupper(substr($act['nama'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-[13px] search-target-name"><?php echo htmlspecialchars($act['nama']); ?></p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><?php echo $act['role']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-gray-600 text-[13px] leading-relaxed search-target-desc">
                                <?php echo htmlspecialchars($act['deskripsi']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-100" id="activityCardContainer">
                <?php foreach ($all_activities as $act): 
                    $config = isset($activity_config[$act['jenis_aktivitas']]) ? $activity_config[$act['jenis_aktivitas']] : array(
                        'icon' => 'fas fa-info-circle',
                        'bg' => 'bg-gray-100',
                        'color' => 'text-gray-500',
                        'label' => str_replace('_', ' ', ucwords($act['jenis_aktivitas']))
                    );
                ?>
                <div class="p-5 space-y-3 activity-card">
                    <div class="flex justify-between items-start">
                        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg text-[10px] font-bold <?php echo $config['bg'] . ' ' . $config['color']; ?> border border-current/10 search-target-type-mobile">
                            <i class="<?php echo $config['icon']; ?> text-[9px] opacity-70"></i>
                            <?php echo $config['label']; ?>
                        </span>
                        <span class="text-[10px] text-gray-400 font-bold"><?php echo tgl_indo($act['created_at'], true); ?></span>
                    </div>
                    <p class="text-sm text-slate-700 leading-relaxed search-target-desc-mobile">
                        <span class="font-bold text-slate-900 search-target-name-mobile"><?php echo htmlspecialchars($act['nama']); ?></span> 
                        <?php echo htmlspecialchars($act['deskripsi']); ?>
                    </p>
                    <div class="flex items-center gap-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                        <i class="far fa-clock"></i>
                        <?php echo date('H:i', strtotime($act['created_at'])); ?> WIB &bull; 
                        <i class="fas fa-user-shield ml-1"></i> <?php echo $act['role']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>
</div>

<script>
document.getElementById('activitySearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    // Filter Table Rows
    const rows = document.querySelectorAll('.activity-row');
    rows.forEach(row => {
        const type = row.querySelector('.search-target-type').innerText.toLowerCase();
        const name = row.querySelector('.search-target-name').innerText.toLowerCase();
        const desc = row.querySelector('.search-target-desc').innerText.toLowerCase();
        
        if (type.includes(searchTerm) || name.includes(searchTerm) || desc.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Filter Cards
    const cards = document.querySelectorAll('.activity-card');
    cards.forEach(card => {
        const type = card.querySelector('.search-target-type-mobile').innerText.toLowerCase();
        const name = card.querySelector('.search-target-name-mobile').innerText.toLowerCase();
        const desc = card.querySelector('.search-target-desc-mobile').innerText.toLowerCase();
        
        if (type.includes(searchTerm) || name.includes(searchTerm) || desc.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
