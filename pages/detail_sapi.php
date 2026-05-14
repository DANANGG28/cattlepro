<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: sapi.php");
    exit;
}

$id_sapi = $_GET['id'];
$data_sapi = $sapi->getById($id_sapi);

if (!$data_sapi) {
    header("Location: sapi.php");
    exit;
}

// --- Handle hapus_birahi via GET (redirect after) ---
if (isset($_GET['hapus_birahi'])) {
    if ($sapi->deleteBirahi($_GET['hapus_birahi'])) {
        if ((isset($data_sapi['status_reproduksi']) ? $data_sapi['status_reproduksi'] : 'Kosong') == 'Sudah Birahi') {
            $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        }
        header("Location: detail_sapi.php?id={$id_sapi}&pesan=" . urlencode("Data arsip birahi berhasil dihapus."));
    } else {
        header("Location: detail_sapi.php?id={$id_sapi}&error=" . urlencode("Gagal menghapus arsip birahi."));
    }
    exit;
}

// --- PRG: Proses form POST lalu redirect ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Input Birahi
    if (isset($_POST['simpan_birahi'])) {
        // Firebase schema: tanggalBirahi is type Date, so ONLY send YYYY-MM-DD
        $tanggal_birahi = trim($_POST['tanggal_birahi']);
        // Strip any time component if accidentally included
        if (strlen($tanggal_birahi) > 10) {
            $tanggal_birahi = substr($tanggal_birahi, 0, 10);
        }
        $sapi->createBirahi($id_sapi, $tanggal_birahi);
        // createBirahi already calls updateStatusReproduksi internally
        $sapi->logActivity($_SESSION['user_id'], 'tambah_birahi', "Mencatat birahi sapi: {$data_sapi['kode_sapi']}");
        
        // ---- Trigger WA Notification (Fonnte) ----
        $waktu_birahi = strtotime($tanggal_birahi);
        $waktu_ib = $waktu_birahi + (12 * 3600); // IB Optimal 12 jam setelah birahi
        $waktu_ib_text = tgl_indo(date('Y-m-d H:i:s', $waktu_ib), true);
        
        $kode_sapi = $data_sapi['kode_sapi'];
        $nomor_tujuan = '085176984188';
        
        $pesan_wa = "✅ *Pencatatan Birahi Berhasil!*\n";
        $pesan_wa .= "Sapi *{$kode_sapi}* telah tercatat.\n\n";
        $pesan_wa .= "🗓 *INSTRUKSI JADWAL INSEMINASI BUATAN:*\n";
        $pesan_wa .= "👉 *" . tgl_indo(date('Y-m-d H:i:s', $waktu_ib), true, true) . "*\n";
        
        // Kirim konfirmasi sekarang
        send_wa($nomor_tujuan, $pesan_wa);
        
        // KIRIM PENGINGAT OTOMATIS (12 Jam kemudian)
        $pesan_reminder = "📢 *PENGINGAT INSEMINASI BUATAN*\n";
        $pesan_reminder .= "Hari ini adalah waktu optimal untuk melakukan Inseminasi Buatan pada sapi *{$kode_sapi}*. Segera hubungi petugas!";
        send_wa($nomor_tujuan, $pesan_reminder, (12 * 3600));

        // ---- Backup Telegram Notification ----
        $pesan_tele = "✅ <b>Pencatatan Birahi Berhasil!</b>\n";
        $pesan_tele .= "Sapi <b>{$kode_sapi}</b> telah masuk dalam sistem pengawasan.\n\n";
        $pesan_tele .= "🗓 <b>INSTRUKSI JADWAL INSEMINASI BUATAN:</b>\n";
        $pesan_tele .= "Mohon lakukan Inseminasi Buatan pada:\n";
        $pesan_tele .= "👉 <b>" . tgl_indo(date('Y-m-d H:i:s', $waktu_ib), true, true) . "</b>\n";
        $pesan_tele .= "<i>(Waktu terbaik adalah 12 jam setelah gejala birahi pertama kali muncul)</i>";
        
        send_telegram($pesan_tele);
        // ------------------------------------------

        $_SESSION['flash_pesan'] = "Data birahi ditambahkan. Status sapi: Sudah Birahi.";
        $flash_msg = "Data birahi ditambahkan. Status sapi: Sudah Birahi.";
    }

    // Input Inseminasi
    if (isset($_POST['simpan_ib'])) {
        $tgl_ib = str_replace('T', ' ', trim($_POST['tanggal_ib']));
        if (strlen($tgl_ib) == 16) $tgl_ib .= ':00';
        $tgl_ib_iso = date('c', strtotime($tgl_ib));
        $sapi->setTanggalIB($id_sapi, $tgl_ib_iso);
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah IB');
        $sapi->logActivity($_SESSION['user_id'], 'inseminasi', "Melakukan IB pada sapi: {$data_sapi['kode_sapi']}");
        
        // ---- Trigger WA & Tele: Inseminasi ----
        $nomor_tujuan = '085176984188';
        
        $waktu_ib_ts = strtotime($tgl_ib);
        $tgl_pantau = date('Y-m-d', $waktu_ib_ts + (21 * 24 * 3600));
        $tgl_pkb = date('Y-m-d', $waktu_ib_ts + (60 * 24 * 3600));

        $pesan_wa = "💉 *Laporan Inseminasi Buatan Tersimpan!*\n";
        $pesan_wa .= "Sapi *{$kode_sapi}* selesai dilakukan Inseminasi.\n\n";
        $pesan_wa .= "🗓 *JADWAL MONITORING:*\n";
        $pesan_wa .= "1. Pantau Birahi (H+21): *" . tgl_indo($tgl_pantau, false, true) . "*\n";
        $pesan_wa .= "2. Cek Kebuntingan (H+60): *" . tgl_indo($tgl_pkb, false, true) . "*";

        // Kirim konfirmasi sekarang
        send_wa($nomor_tujuan, $pesan_wa);
        
        // KIRIM PENGINGAT OTOMATIS (H+21 dan H+60)
        send_wa($nomor_tujuan, "📢 *PENGINGAT PANTAU BIRAHI*\nSudah 21 hari sejak Inseminasi. Mohon pantau apakah sapi *{$kode_sapi}* birahi lagi.", (21 * 24 * 3600));
        send_wa($nomor_tujuan, "📢 *PENGINGAT PEMERIKSAAN KEBUNTINGAN*\nSudah 60 hari sejak Inseminasi. Mohon segera jadwalkan pemeriksaan kebuntingan untuk sapi *{$kode_sapi}*.", (60 * 24 * 3600));

        $pesan_tele = "💉 <b>Laporan Inseminasi Buatan Telah Tersimpan!</b>\n";
        $pesan_tele .= "Sapi <b>{$kode_sapi}</b> baru saja dilakukan Inseminasi Buatan.\n\n";
        $pesan_tele .= "🗓 <b>JADWAL MONITORING:</b>\n";
        $pesan_tele .= "1. <b>Pantau Birahi Ulang (H+21)</b>:\nPastikan sapi tidak birahi lagi pada <b>" . tgl_indo($tgl_pantau, false, true) . "</b>\n";
        $pesan_tele .= "2. <b>Jadwal Pemeriksaan Kebuntingan (H+60)</b>:\nSegera panggil petugas pada <b>" . tgl_indo($tgl_pkb, false, true) . "</b> untuk cek kebuntingan.";

        send_telegram($pesan_tele);
        // ------------------------------------------

        $flash_msg = "Data Inseminasi divalidasi. Status sapi: Sudah IB.";
    }

    // Input PKB
    if (isset($_POST['simpan_pkb'])) {
        $hasil = $_POST['hasil_pkb'];
        $kode_sapi = $data_sapi['kode_sapi'];
        $nomor_tujuan = '085176984188';

        if ($hasil == 'Bunting') {
            $sapi->updateStatusReproduksi($id_sapi, 'Bunting');
            $flash_msg = "Selamat! Sapi dinyatakan Bunting.";
            
            // Jadwal HPL (283 hari dari IB)
            $waktu_ib = strtotime(isset($data_sapi['tanggal_ib']) ? $data_sapi['tanggal_ib'] : '');
            $waktu_hpl = tgl_indo(date('Y-m-d', $waktu_ib + (283 * 24 * 3600)));

            $pesan = "🎉 *Sapi Positif Bunting!*\n";
            $pesan .= "Sapi *{$kode_sapi}* dinyatakan Hamil setelah pemeriksaan PKB.\n\n";
            $pesan .= "🗓 *ESTIMASI KELAHIRAN (HPL):*\n";
            $pesan .= "👉 *{$waktu_hpl}*\n";
            $pesan .= "Siapkan kandang pedet dan nutrisi indukan.";

        } elseif ($hasil == 'Gagal') {
            $sapi->updateStatusReproduksi($id_sapi, 'Gagal Hamil');
            $sapi->setTanggalIB($id_sapi, null);
            $flash_msg = "Sapi gagal hamil. Status diubah: Gagal Hamil.";
            
            $pesan = "⚠️ *Hasil PKB: Gagal Hamil*\n";
            $pesan .= "Sapi *{$kode_sapi}* dinyatakan tidak hamil/gagal. Silakan evaluasi kondisi kesehatan sapi.";
        } else {
            $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
            $sapi->setTanggalIB($id_sapi, null);
            $flash_msg = "Sapi tidak bunting. Status kembali: Kosong.";
            
            $pesan = "ℹ️ *Hasil PKB: Tidak Bunting*\n";
            $pesan .= "Sapi *{$kode_sapi}* kembali ke status Kosong.";
        }
        
        $sapi->logActivity($_SESSION['user_id'], 'pkb', "Pemeriksaan Kebuntingan {$data_sapi['kode_sapi']}: $hasil");
        
        $nomor_tujuan = '085176984188';
        
        // Kirim konfirmasi sekarang (WhatsApp)
        $pesan_wa = str_replace(['<b>', '</b>'], ['*', '*'], $pesan_tele);
        send_wa($nomor_tujuan, $pesan_wa);
        
        // Jika Bunting, Kirim Pengingat Kelahiran (H-7 dan hari-H HPL)
        if ($hasil == 'Bunting') {
            $waktu_ib_val = strtotime(isset($data_sapi['tanggal_ib']) ? $data_sapi['tanggal_ib'] : '');
            $delay_hpl = ($waktu_ib_val > 0) ? ($waktu_ib_val + (283 * 24 * 3600) - time()) : 0;
            if ($delay_hpl > 0) {
                send_wa($nomor_tujuan, "📢 *PENGINGAT HARI PERKIRAAN LAHIR*\nSapi *{$kode_sapi}* diprediksi akan melahirkan hari ini. Mohon pantau kondisi indukan!", $delay_hpl);
            }
        }

        send_telegram($pesan_tele);
    }

    // Input Kelahiran
    if (isset($_POST['simpan_kelahiran'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->setTanggalIB($id_sapi, null);
        $sapi->logActivity($_SESSION['user_id'], 'kelahiran', "Mencatat kelahiran sapi dari indukan: {$data_sapi['kode_sapi']}");
        
        // ---- Trigger WA & Tele: Kelahiran ----
        $kode_sapi = $data_sapi['kode_sapi'];
        $nomor_tujuan = '085176984188';
        
        $pesan_tele = "🍼 <b>Kelahiran Tercatat!</b>\n";
        $pesan_tele .= "Indukan <b>{$kode_sapi}</b> telah melahirkan.\n\n";
        $pesan_tele .= "Status kembali ke <b>Kosong</b>.";

        // Kirim WhatsApp (Konfirmasi Kelahiran)
        $pesan_wa = str_replace(['<b>', '</b>'], ['*', '*'], $pesan_tele);
        send_wa($nomor_tujuan, $pesan_wa);

        send_telegram($pesan_tele);
        // ------------------------------------------

        $flash_msg = "Data kelahiran dicatat. Status sapi kembali: Kosong.";
    }

    // Batal Birahi
    if (isset($_POST['batal_birahi'])) {
        $latest = $sapi->getLatestBirahi($id_sapi);
        if ($latest) {
            $sapi->deleteBirahi($latest['id']);
        }
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->logActivity($_SESSION['user_id'], 'batal_birahi', "Membatalkan laporan birahi sapi: {$data_sapi['kode_sapi']}");
        $flash_msg = "Status birahi dibatalkan. Kembali ke tahap kosong.";
    }

    // Batal IB
    if (isset($_POST['batal_ib'])) {
        $sapi->setTanggalIB($id_sapi, null);
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah Birahi');
        $sapi->logActivity($_SESSION['user_id'], 'batal_ib', "Membatalkan laporan Inseminasi Buatan sapi: {$data_sapi['kode_sapi']}");
        $flash_msg = "Laporan Inseminasi Buatan dibatalkan. Sapi kembali ke tahap Sudah Birahi.";
    }

    // Batal Bunting
    if (isset($_POST['batal_bunting'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah IB');
        $sapi->logActivity($_SESSION['user_id'], 'batal_bunting', "Membatalkan status bunting sapi: {$data_sapi['kode_sapi']}");
        $flash_msg = "Status bunting dibatalkan. Sapi kembali ke tahap Sudah IB.";
    }

    // Reset Gagal Hamil
    if (isset($_POST['reset_gagal'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->logActivity($_SESSION['user_id'], 'reset_gagal', "Mereset status gagal hamil sapi: {$data_sapi['kode_sapi']}");
        $flash_msg = "Status gagal hamil direset. Sapi kembali ke tahap Kosong.";
    }

    // PRG: Redirect setelah semua POST selesai
    $msg_param = !empty($flash_msg) ? '&pesan=' . urlencode($flash_msg) : '';
    header("Location: detail_sapi.php?id={$id_sapi}" . $msg_param);
    exit;
}

// (flash message sekarang ditangani via ?pesan= URL param oleh sidebar.php)

// Ambil histori birahi setelah semua proses POST/GET selesei
$histori_birahi = $sapi->getBirahiByIdSapi($id_sapi);
$riwayat_aktivitas = $sapi->getHistoryBySapi($id_sapi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sapi - CattlePro</title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'><path fill='%2300D084' d='M109.9 80.2L91.8 11.2C88.6 .9 77.9-3.7 67.9 1.4L44.4 13.5C28.2 21.8 18.2 38.6 18.2 56.8c0 14.6 7.4 28.2 19.5 36.1l32.1 21.1c-16.7 30.6-25.1 65-25.1 99.8l0 10.4c0 38 12.6 74.9 36 104.3l-1.3 5C74.6 350.3 64 369.3 64 389.9l0 69.1c0 15 9.1 28.3 23.1 33.9l32 12.8c12 4.8 25.7 1.2 33.9-8.8l21.2-25.8c29.1 23.3 65.2 36.6 102.6 37.1l.6 0c37.5-.5 73.5-13.8 102.6-37.1l21.2 25.8c8.2 10 21.9 13.6 33.9 8.8l32-12.8c14-5.6 23.1-18.9 23.1-33.9l0-69.1c0-20.6-10.6-39.6-28.1-48.9l-19.1-10.1c11.6-21.7 17.6-45.9 17.6-70.6l0-10.4c0-34.8-8.4-69.2-25.1-99.8l32.1-21.1c12.1-7.9 19.5-21.5 19.5-36.1c0-18.2-10-35-26.2-43.3l-23.5-12.1c-10-5.1-20.7-.5-23.9 9.8l-18.1 69C314.9 66 288.3 45.5 258.9 29.9l0-19.2c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 6.6c-28.5 7.6-54.3 22-75.1 41.9zM224 224a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z'/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 md:p-5 px-4 md:px-6 sticky top-0 z-10 flex items-center gap-3">
        <div class="flex items-center truncate">
            <a href="sapi.php" class="text-gray-500 hover:text-emerald-600 mr-3 text-lg md:text-xl flex-shrink-0"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-lg md:text-xl font-bold text-gray-800 truncate">Detail: <?php echo htmlspecialchars($data_sapi['kode_sapi']); ?></h2>
        </div>
        <div class="ml-auto flex items-center"><?php include '../components/profile_dropdown.php'; ?></div>
    </header>

    <main class="p-4 pb-36 md:p-6 md:pb-6 space-y-6 w-full">
        


        <!-- Card: Informasi Utama -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-lg mb-5 text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-500"></i> Informasi Utama
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-4 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-1">Kode Sapi</p>
                    <p class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($data_sapi['kode_sapi']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-1">Jenis</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($data_sapi['jenis']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-1">Tanggal Lahir</p>
                    <p class="font-semibold text-gray-800"><?php echo tgl_indo($data_sapi['tanggal_lahir']); ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-1">Berat</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($data_sapi['berat']); ?> kg</p>
                </div>
            </div>
            
            <!-- Status Reproduksi -->
            <div class="mt-5 pt-4 border-t border-gray-100">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-wider mb-2">Status Reproduksi</p>
                <?php 
                $status_repro = isset($data_sapi['status_reproduksi']) ? $data_sapi['status_reproduksi'] : 'Kosong';
                $status_colors = [
                    'Kosong' => 'bg-gray-100 text-gray-700 border border-gray-300',
                    'Sudah Birahi' => 'bg-pink-50 text-pink-700 border border-pink-200',
                    'Sudah IB' => 'bg-blue-100 text-blue-700 border border-blue-200',
                    'Bunting' => 'bg-green-50 text-green-700 border border-green-200',
                    'Gagal Hamil' => 'bg-red-50 text-red-700 border border-red-200'
                ];
                $badge_class = isset($status_colors[$status_repro]) ? $status_colors[$status_repro] : $status_colors['Kosong'];
                ?>
                <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold <?php echo $badge_class; ?>">
                    <?php echo str_replace(['Sudah IB', 'PKB', 'HPL'], ['Sudah Inseminasi Buatan', 'Pemeriksaan Kebuntingan', 'Hari Perkiraan Lahir'], $status_repro); ?>
                </span>
            </div>
        </div>

        <!-- Card: Instruksi & Aksi Reproduksi (Smart Cycle) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden relative">
            <div class="absolute top-0 right-0 p-4 opacity-[0.03]">
                <i class="fas fa-sync-alt text-8xl text-pink-500" style="animation: spin 15s linear infinite;"></i>
            </div>
            <h3 class="font-bold text-lg mb-5 text-gray-800 flex items-center gap-2">
                <span class="text-pink-500">❤️</span> Instruksi & Aksi Reproduksi (Smart Cycle)
            </h3>
            
            <?php
            $status = isset($data_sapi['status_reproduksi']) ? $data_sapi['status_reproduksi'] : 'Kosong';
            ?>
            
            <!-- Instruction Box -->
            <?php if ($status == 'Kosong'): ?>
                <div class="p-4 rounded-xl mb-6 bg-gray-50 border border-gray-200 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-gray-400"></i>
                        <div class="text-sm text-gray-600">Sapi saat ini kosong. Lakukan pengawasan birahi secara rutin.</div>
                    </div>
                </div>
            <?php elseif ($status == 'Sudah Birahi'): ?>
                <!-- Card: Jadwal Inseminasi Buatan Optimal -->
                <?php 
                $latest_birahi = $sapi->getLatestBirahi($id_sapi);
                $jadwal_ib = '-';
                $instruksi = 'Belum ada data birahi aktif untuk menghitung jadwal.';
                $status_color = 'text-pink-300';

                if ($latest_birahi) {
                    $waktu_birahi = strtotime($latest_birahi['tanggalBirahi']);
                    $waktu_ib_awal = $waktu_birahi + (12 * 3600); // 12 Jam
                    $waktu_ib_akhir = $waktu_birahi + (18 * 3600); // 18 Jam
                    
                    $jadwal_ib = tgl_indo(date('Y-m-d', $waktu_ib_awal)) . ' (' . date('H:i', $waktu_ib_awal) . ' - ' . date('H:i', $waktu_ib_akhir) . ')';
                    $instruksi = 'Lakukan Inseminasi Buatan dalam rentang waktu di atas untuk peluang keberhasilan tertinggi.';
                    $status_color = 'text-pink-600';
                    
                    // Check if expired
                    if (time() > $waktu_ib_akhir) {
                        $instruksi = 'Waktu optimal telah terlewati. Segera lakukan pengecekan atau tunggu siklus berikutnya.';
                        $status_color = 'text-red-500';
                    }
                }
                ?>
                <div class="bg-pink-50 border-l-4 border-pink-500 p-5 rounded-2xl mb-8">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center text-pink-600 mt-1 shadow-sm">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h5 class="font-bold text-pink-800 text-sm uppercase tracking-wider mb-1">Jadwal Inseminasi Buatan Optimal:</h5>
                            <p class="font-extrabold text-2xl <?php echo $status_color; ?> mb-1">
                                <?php echo $jadwal_ib; ?>
                            </p>
                            <p class="text-xs text-pink-500 font-medium opacity-80 flex items-center gap-1">
                                <i class="fas fa-info-circle text-[10px]"></i> <?php echo $instruksi; ?>
                            </p>
                        </div>
                        <?php if ($latest_birahi): ?>
                        <div class="hidden md:block">
                            <span class="px-3 py-1 rounded-full bg-pink-200 text-pink-700 text-[10px] font-bold uppercase">Estimasi 12-18 Jam</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($status == 'Sudah IB'): ?>
                <?php
                $waktu_ib = strtotime(isset($data_sapi['tanggalIb']) ? $data_sapi['tanggalIb'] : '');
                $waktu_pantau = $waktu_ib + (21 * 24 * 3600);
                $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
                ?>
                <div class="p-5 rounded-xl mb-6 bg-blue-50 border border-blue-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-blue-500"></i>
                        <div class="text-sm space-y-3">
                            <div>
                                <p class="font-bold text-blue-700">Pantau Birahi Ulang (H+21):</p>
                                <p class="text-gray-700"><?php echo tgl_indo(date('Y-m-d', $waktu_pantau)); ?></p>
                            </div>
                            <div>
                                <p class="font-bold text-purple-700">Jadwal Pemeriksaan Kebuntingan (H+60):</p>
                                <p class="text-gray-700"><?php echo tgl_indo(date('Y-m-d', $waktu_pkb)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($status == 'Bunting'): ?>
                <?php
                $tgl_ib_raw = isset($data_sapi['tanggalIb']) ? $data_sapi['tanggalIb'] : '';
                $waktu_ib = strtotime($tgl_ib_raw);
                if ($waktu_ib > 0):
                    $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
                ?>
                <div class="p-4 rounded-xl mb-6 bg-green-50 border border-green-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-green-500"></i>
                        <div class="text-sm">
                            <p class="font-bold text-green-700 mb-1">Hari Perkiraan Lahir:</p>
                            <p class="text-gray-700"><?php echo tgl_indo(date('Y-m-d', $waktu_hpl)); ?></p>
                            <p class="text-xs text-green-400 mt-1">(283 hari sejak Inseminasi Buatan: <?php echo tgl_indo(date('Y-m-d', $waktu_ib)); ?>)</p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 rounded-xl mb-6 bg-yellow-50 border border-yellow-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle mt-0.5 text-lg text-yellow-600"></i>
                        <div class="text-sm">
                            <p class="font-bold text-yellow-700 mb-1">Data IB Tidak Ditemukan</p>
                            <p class="text-gray-600">Sapi berstatus Bunting namun tanggal Inseminasi Buatan tidak tercatat. Mohon periksa kembali data riwayat.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php elseif ($status == 'Gagal Hamil'): ?>
                <div class="p-4 rounded-xl mb-6 bg-red-50 border border-red-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle mt-0.5 text-lg text-red-500"></i>
                        <div class="text-sm">
                            <p class="font-bold text-red-700 mb-1">Sapi Gagal Hamil</p>
                            <p class="text-gray-600">Perlu evaluasi kesehatan dan manajemen reproduksi ulang. Reset status untuk mengulang siklus.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Conditional Input Form Section -->
            <?php if ($status == 'Kosong'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-pink-500"></i> Form Lapor Birahi
                    </h4>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Mulai Birahi</label>
                            <input type="date" name="tanggal_birahi" value="<?php echo date('Y-m-d'); ?>" required class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-pink-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Jam (Opsional)</label>
                            <input type="time" name="waktu_birahi" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-pink-500 outline-none transition">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="simpan_birahi" class="w-full bg-pink-500 text-white font-bold py-2.5 px-6 rounded-xl hover:bg-pink-600 transition shadow-lg shadow-pink-100 flex items-center justify-center gap-2 h-[42px]"><i class="fas fa-save"></i> Simpan Birahi</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Sudah Birahi'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-syringe text-blue-500"></i> Lapor Tindakan Inseminasi Buatan
                    </h4>
                    <form id="form-batal-birahi" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Implementasi Inseminasi Buatan</label>
                            <input type="datetime-local" name="tanggal_ib" value="<?php echo date('Y-m-d\TH:i'); ?>" required class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="flex gap-2 w-full">
                            <button type="submit" name="simpan_ib" class="flex-1 bg-blue-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-100 flex items-center justify-center gap-2 h-[42px] text-sm"><i class="fas fa-save"></i> Simpan Data IB</button>
                            <button type="button" id="btn-batal-birahi"
                                onclick="CP.confirm('Batalkan status Sudah Birahi dan hapus log birahi terakhir?', function(){ var f=document.getElementById('form-batal-birahi'); var i=document.createElement('input'); i.type='hidden'; i.name='batal_birahi'; i.value='1'; f.appendChild(i); f.submit(); }, {title:'Batalkan Birahi?', icon:'question', danger:true, confirmText:'<i class=\'fas fa-undo mr-1\'></i> Ya, Batalkan'})"
                                class="px-4 bg-white border border-red-500 text-red-500 font-bold rounded-xl hover:bg-red-50 transition h-[42px] flex items-center justify-center" title="Batalkan laporan birahi"><i class="fas fa-undo"></i></button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Sudah IB'): ?>
                <!-- PKB Section -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-stethoscope text-purple-600"></i> Hasil Pemeriksaan Kebuntingan
                    </h4>
                    <form id="form-batal-ib" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Status Hasil Pemeriksaan Kebuntingan</label>
                            <select name="hasil_pkb" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 outline-none transition appearance-none bg-white">
                                <option value="Bunting">Bunting (Positif)</option>
                                <option value="Tidak">Tidak Bunting (Negatif)</option>
                                <option value="Gagal">Gagal Hamil</option>
                            </select>
                        </div>
                        <div class="flex gap-2 w-full">
                            <button type="submit" name="simpan_pkb" class="flex-1 bg-purple-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-purple-700 transition shadow-lg shadow-purple-100 flex items-center justify-center gap-2 h-[42px] text-sm"><i class="fas fa-save"></i> Simpan Hasil PKB</button>
                            <button type="button"
                                onclick="CP.confirm('Batalkan laporan Inseminasi Buatan dan kembali ke tahap Sudah Birahi?', function(){ var f=document.getElementById('form-batal-ib'); var i=document.createElement('input'); i.type='hidden'; i.name='batal_ib'; i.value='1'; f.appendChild(i); f.submit(); }, {title:'Batalkan IB?', icon:'question', danger:true, confirmText:'<i class=\'fas fa-undo mr-1\'></i> Ya, Batalkan'})"
                                class="px-4 bg-white border border-red-500 text-red-500 font-bold rounded-xl hover:bg-red-50 transition h-[42px] flex items-center justify-center" title="Batalkan laporan IB"><i class="fas fa-undo"></i></button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Bunting'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-baby-carriage text-green-500"></i> Pelaporan Kelahiran Sapi
                    </h4>
                    <form id="form-batal-bunting" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Indukan Melahirkan</label>
                            <input type="date" name="tanggal_kelahiran" value="<?php echo date('Y-m-d'); ?>" required class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none transition">
                        </div>
                        <div class="flex gap-2 w-full">
                            <button type="submit" name="simpan_kelahiran" class="flex-1 bg-green-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-100 flex items-center justify-center gap-2 h-[42px] text-sm"><i class="fas fa-save"></i> Laporkan Kelahiran</button>
                            <button type="button"
                                onclick="CP.confirm('Batalkan status Bunting dan kembali ke tahap Sudah Inseminasi Buatan?', function(){ var f=document.getElementById('form-batal-bunting'); var i=document.createElement('input'); i.type='hidden'; i.name='batal_bunting'; i.value='1'; f.appendChild(i); f.submit(); }, {title:'Batalkan Bunting?', icon:'question', danger:true, confirmText:'<i class=\'fas fa-undo mr-1\'></i> Ya, Batalkan'})"
                                class="px-4 bg-white border border-red-500 text-red-500 font-bold rounded-xl hover:bg-red-50 transition h-[42px] flex items-center justify-center" title="Batalkan status Bunting"><i class="fas fa-undo"></i></button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Gagal Hamil'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-red-500"></i> Evaluasi Gagal Hamil
                    </h4>
                    <form method="POST" class="flex flex-col gap-3">
                        <p class="text-sm text-gray-600">Sapi terdeteksi gagal hamil. Silakan lakukan evaluasi medis atau perbaiki manajemen reproduksi/pakan, lalu reset status sapi untuk memulai siklus baru.</p>
                        <div class="flex flex-col md:flex-row items-end gap-2 w-full">
                            <button type="submit" name="reset_gagal" class="flex items-center justify-center gap-2 bg-red-600 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-red-700 transition shadow-md w-full md:w-auto h-[42px] whitespace-nowrap"><i class="fas fa-sync"></i> Reset Status ke Kosong</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card: Riwayat Reproduksi (Vertical Timeline) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-bold text-gray-800 mb-6 flex items-center gap-2 text-lg">
                <i class="fas fa-history text-emerald-500"></i> Riwayat Aktivitas Sapi
            </h4>
            
            <div class="relative pl-3">
                <div class="absolute left-[19px] top-2 bottom-2 w-0.5 bg-gray-100"></div>
                <div class="space-y-6">
                    <?php if(count($riwayat_aktivitas) > 0): ?>
                        <?php 
                        $activity_config = [
                            'tambah_sapi' => ['icon' => 'fas fa-plus', 'bg' => 'bg-blue-50', 'color' => 'text-blue-500', 'label' => 'Registrasi Sapi'],
                            'edit_sapi' => ['icon' => 'fas fa-edit', 'bg' => 'bg-gray-50', 'color' => 'text-gray-500', 'label' => 'Update Data'],
                            'hapus_sapi' => ['icon' => 'fas fa-trash', 'bg' => 'bg-red-50', 'color' => 'text-red-500', 'label' => 'Hapus Sapi'],
                            'tambah_birahi' => ['icon' => 'fas fa-venus-mars', 'bg' => 'bg-pink-50', 'color' => 'text-pink-500', 'label' => 'Laporan Birahi'],
                            'batal_birahi' => ['icon' => 'fas fa-undo', 'bg' => 'bg-red-50', 'color' => 'text-red-500', 'label' => 'Batal Birahi'],
                            'inseminasi' => ['icon' => 'fas fa-syringe', 'bg' => 'bg-amber-50', 'color' => 'text-amber-500', 'label' => 'Inseminasi (IB)'],
                            'batal_ib' => ['icon' => 'fas fa-times', 'bg' => 'bg-red-50', 'color' => 'text-red-500', 'label' => 'Batal IB'],
                            'pkb' => ['icon' => 'fas fa-stethoscope', 'bg' => 'bg-purple-50', 'color' => 'text-purple-500', 'label' => 'Pemeriksaan (PKB)'],
                            'kelahiran' => ['icon' => 'fas fa-baby', 'bg' => 'bg-emerald-50', 'color' => 'text-emerald-500', 'label' => 'Kelahiran'],
                            'batal_bunting' => ['icon' => 'fas fa-undo', 'bg' => 'bg-red-50', 'color' => 'text-red-500', 'label' => 'Batal Bunting'],
                            'reset_gagal' => ['icon' => 'fas fa-sync', 'bg' => 'bg-red-50', 'color' => 'text-red-500', 'label' => 'Reset Siklus']
                        ];
                        
                        foreach($riwayat_aktivitas as $log): 
                            $jenis = $log['jenis'];
                            $cfg = isset($activity_config[$jenis]) ? $activity_config[$jenis] : array(
                                'icon' => 'fas fa-info-circle',
                                'bg' => 'bg-gray-50',
                                'color' => 'text-gray-400',
                                'label' => str_replace('_', ' ', ucwords($jenis))
                            );
                        ?>
                        <div class="relative pl-8">
                            <span class="absolute -left-3 top-1 flex h-8 w-8 items-center justify-center rounded-xl <?php echo $cfg['bg']; ?> border border-gray-100 shadow-sm">
                                <i class="<?php echo $cfg['icon']; ?> text-[13px] <?php echo $cfg['color']; ?>"></i>
                            </span>
                            <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-4 transition hover:bg-gray-50">
                                <div class="flex justify-between items-start mb-1">
                                    <h5 class="font-bold text-gray-800 text-sm"><?php echo $cfg['label']; ?></h5>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($log['deskripsi']); ?></p>
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center text-[8px] text-white font-bold">
                                        <?php echo strtoupper(substr($log['nama'], 0, 1)); ?>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-tight"><?php echo htmlspecialchars($log['nama']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pl-6 text-sm text-gray-400 italic">Belum ada riwayat aktivitas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card: Arsip Histori Birahi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-bold text-gray-800 mb-5 flex items-center gap-2 text-lg">
                <i class="fas fa-venus-mars text-pink-500"></i> Histori Birahi (Arsip)
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-y border-gray-100">
                            <th class="p-4 font-bold text-gray-400 text-xs uppercase tracking-widest">Waktu Deteksi Birahi</th>
                            <th class="p-4 font-bold text-gray-400 text-xs uppercase tracking-widest">Data Dicatat Pada</th>
                            <th class="p-4 font-bold text-gray-400 text-xs uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if($histori_birahi->rowCount() > 0): ?>
                            <?php while($row = $histori_birahi->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-50/30 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center text-pink-500">
                                            <i class="fas fa-venus-mars text-xs"></i>
                                        </div>
                                        <span class="font-bold text-gray-700"><?php echo tgl_indo($row['tanggal_birahi']); ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-400 font-medium"><?php echo tgl_indo($row['created_at'], true); ?></td>
                                <td class="p-4 text-center">
                                    <a href="?id=<?php echo $id_sapi; ?>&hapus_birahi=<?php echo $row['id']; ?>" data-confirm-delete="Hapus arsip birahi tanggal <?php echo tgl_indo($row['tanggal_birahi']); ?>?" class="w-8 h-8 rounded-lg bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition flex items-center justify-center inline-flex">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="p-10 text-center text-gray-400 italic text-sm">Belum ada histori birahi yang tercatat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

</body>
</html>
