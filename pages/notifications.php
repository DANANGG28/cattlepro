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
    <title>Notifikasi - CattlePro</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'><path fill='%2300D084' d='M109.9 80.2L91.8 11.2C88.6 .9 77.9-3.7 67.9 1.4L44.4 13.5C28.2 21.8 18.2 38.6 18.2 56.8c0 14.6 7.4 28.2 19.5 36.1l32.1 21.1c-16.7 30.6-25.1 65-25.1 99.8l0 10.4c0 38 12.6 74.9 36 104.3l-1.3 5C74.6 350.3 64 369.3 64 389.9l0 69.1c0 15 9.1 28.3 23.1 33.9l32 12.8c12 4.8 25.7 1.2 33.9-8.8l21.2-25.8c29.1 23.3 65.2 36.6 102.6 37.1l.6 0c37.5-.5 73.5-13.8 102.6-37.1l21.2 25.8c8.2 10 21.9 13.6 33.9 8.8l32-12.8c14-5.6 23.1-18.9 23.1-33.9l0-69.1c0-20.6-10.6-39.6-28.1-48.9l-19.1-10.1c11.6-21.7 17.6-45.9 17.6-70.6l0-10.4c0-34.8-8.4-69.2-25.1-99.8l32.1-21.1c12.1-7.9 19.5-21.5 19.5-36.1c0-18.2-10-35-26.2-43.3l-23.5-12.1c-10-5.1-20.7-.5-23.9 9.8l-18.1 69C314.9 66 288.3 45.5 258.9 29.9l0-19.2c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 6.6c-28.5 7.6-54.3 22-75.1 41.9zM224 224a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z'/></svg>">
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
                    <?php foreach ($notifikasi as $notif): 
                        $color = isset($notif['color']) ? $notif['color'] : 'emerald';
                        $bg_class = "bg-{$color}-50 text-{$color}-600 border-{$color}-100";
                    ?>
                        <div class="p-6 hover:bg-gray-50/50 transition flex flex-col sm:flex-row gap-5 items-start group">
                            <div class="w-14 h-14 rounded-2xl <?php echo $bg_class; ?> border flex items-center justify-center shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                <i class="<?php echo $notif['icon']; ?> text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                    <span class="text-[10px] bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700 px-2 py-1 rounded-md font-extrabold uppercase tracking-widest border border-<?php echo $color; ?>-200">Sapi #<?php echo $notif['kode_sapi']; ?></span>
                                    <span class="text-[11px] text-gray-400 font-bold flex items-center gap-1"><i class="far fa-clock"></i> Baru Saja</span>
                                </div>
                                <p class="text-slate-700 text-lg leading-relaxed font-semibold"><?php echo $notif['msg']; ?></p>
                                <div class="flex items-center gap-3 mt-4">
                                    <a href="detail_sapi.php?id=<?php echo $notif['id_sapi']; ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 active:scale-[0.98]">
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
