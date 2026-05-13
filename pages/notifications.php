<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$notifikasi = $sapi->getReproductionNotifications();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Notifikasi - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <div class="flex items-center gap-4">
             <a href="dashboard.php" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-xl font-bold text-slate-800">Semua Notifikasi</h2>
        </div>
        <div class="flex items-center gap-4 ml-auto">
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-white to-emerald-50/20">
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-3">
                    <i class="fas fa-bell text-emerald-500"></i> 
                    Daftar Notifikasi Reproduksi
                </h3>
                <p class="text-sm text-gray-500 mt-1">Daftar lengkap instruksi dan peringatan untuk manajemen reproduksi ternak Anda.</p>
            </div>

            <div class="divide-y divide-gray-50">
                <?php if (count($notifikasi) > 0): ?>
                    <?php foreach ($notifikasi as $notif): ?>
                        <div class="p-6 hover:bg-gray-50/50 transition flex flex-col sm:flex-row gap-5 items-start group">
                            <div class="w-12 h-12 rounded-2xl <?php echo str_replace('text-', 'bg-', $notif['icon']); ?>/10 flex items-center justify-center shrink-0 shadow-sm border border-current/5 group-hover:scale-110 transition-transform">
                                <i class="<?php echo $notif['icon']; ?> text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-1 rounded-md font-bold uppercase tracking-widest border border-gray-200">Sapi #<?php echo $notif['kode_sapi']; ?></span>
                                    <span class="text-[11px] text-gray-400 font-bold flex items-center gap-1"><i class="far fa-clock"></i> Baru Saja</span>
                                </div>
                                <p class="text-slate-700 text-base leading-relaxed"><?php echo $notif['msg']; ?></p>
                                <div class="flex items-center gap-3 mt-4">
                                    <a href="detail_sapi.php?id=<?php echo $notif['id_sapi']; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                                        <i class="fas fa-search"></i> Lihat Detail Sapi
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-20 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-bell-slash text-gray-200 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-bold text-slate-400">Tidak ada notifikasi</h4>
                        <p class="text-gray-400 mt-2">Seluruh ternak Anda saat ini dalam kondisi terpantau normal.</p>
                        <a href="dashboard.php" class="inline-block mt-8 text-emerald-600 font-bold hover:underline">Kembali ke Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
