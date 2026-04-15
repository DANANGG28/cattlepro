<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: sapi.php");
    exit;
}

$id_sapi = intval($_GET['id']);
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
        $_SESSION['flash_pesan'] = "Data birahi berhasil dihapus.";
    }
    header("Location: detail_sapi.php?id=" . $id_sapi);
    exit;
}

// --- PRG: Proses form POST lalu redirect ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Input Birahi
    if (isset($_POST['simpan_birahi'])) {
        $tanggal_birahi = trim($_POST['tanggal_birahi']) . ' ' . date('H:i:s');
        if(!empty($_POST['waktu_birahi'])){
            $time_part = trim($_POST['waktu_birahi']);
            if (strlen($time_part) == 5) $time_part .= ':00';
            $tanggal_birahi = trim($_POST['tanggal_birahi']) . ' ' . $time_part;
        }
        $sapi->createBirahi($id_sapi, $tanggal_birahi);
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah Birahi');
        $sapi->logActivity($_SESSION['user_id'], 'tambah_birahi', "Mencatat birahi sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Data birahi ditambahkan. Status sapi: Sudah Birahi.";
    }

    // Input Inseminasi
    if (isset($_POST['simpan_ib'])) {
        $tgl_ib = str_replace('T', ' ', trim($_POST['tanggal_ib']));
        if (strlen($tgl_ib) == 16) $tgl_ib .= ':00';
        $sapi->setTanggalIB($id_sapi, $tgl_ib);
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah IB');
        $sapi->logActivity($_SESSION['user_id'], 'inseminasi', "Melakukan IB pada sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Data Inseminasi divalidasi. Status sapi: Sudah IB.";
    }

    // Input PKB
    if (isset($_POST['simpan_pkb'])) {
        $hasil = $_POST['hasil_pkb'];
        if ($hasil == 'Bunting') {
            $sapi->updateStatusReproduksi($id_sapi, 'Bunting');
            $_SESSION['flash_pesan'] = "Selamat! Sapi dinyatakan Bunting.";
        } elseif ($hasil == 'Gagal') {
            $sapi->updateStatusReproduksi($id_sapi, 'Gagal Hamil');
            $sapi->setTanggalIB($id_sapi, null);
            $_SESSION['flash_pesan'] = "Sapi gagal hamil. Status diubah: Gagal Hamil.";
        } else {
            $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
            $sapi->setTanggalIB($id_sapi, null);
            $_SESSION['flash_pesan'] = "Sapi tidak bunting. Status kembali: Kosong.";
        }
        $sapi->logActivity($_SESSION['user_id'], 'pkb', "Pemeriksaan Kebuntingan {$data_sapi['kode_sapi']}: $hasil");
    }

    // Input Kelahiran
    if (isset($_POST['simpan_kelahiran'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->setTanggalIB($id_sapi, null);
        $sapi->logActivity($_SESSION['user_id'], 'kelahiran', "Mencatat kelahiran sapi dari indukan: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Data kelahiran dicatat. Status sapi kembali: Kosong.";
    }

    // Batal Birahi
    if (isset($_POST['batal_birahi'])) {
        $latest = $sapi->getLatestBirahi($id_sapi);
        if ($latest) {
            $sapi->deleteBirahi($latest['id']);
        }
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->logActivity($_SESSION['user_id'], 'batal_birahi', "Membatalkan laporan birahi sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Status birahi dibatalkan. Kembali ke tahap kosong.";
    }

    // Batal IB
    if (isset($_POST['batal_ib'])) {
        $sapi->setTanggalIB($id_sapi, null);
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah Birahi');
        $sapi->logActivity($_SESSION['user_id'], 'batal_ib', "Membatalkan laporan IB sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Laporan IB dibatalkan. Sapi kembali ke tahap Sudah Birahi.";
    }

    // Batal Bunting
    if (isset($_POST['batal_bunting'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Sudah IB');
        $sapi->logActivity($_SESSION['user_id'], 'batal_bunting', "Membatalkan status bunting sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Status bunting dibatalkan. Sapi kembali ke tahap Sudah IB.";
    }

    // Reset Gagal Hamil
    if (isset($_POST['reset_gagal'])) {
        $sapi->updateStatusReproduksi($id_sapi, 'Kosong');
        $sapi->logActivity($_SESSION['user_id'], 'reset_gagal', "Mereset status gagal hamil sapi: {$data_sapi['kode_sapi']}");
        $_SESSION['flash_pesan'] = "Status gagal hamil direset. Sapi kembali ke tahap Kosong.";
    }

    // PRG: Redirect setelah semua POST selesai
    header("Location: detail_sapi.php?id=" . $id_sapi);
    exit;
}

// --- Flash message dari session (setelah redirect) ---
$pesan = null;
if (isset($_SESSION['flash_pesan'])) {
    $pesan = $_SESSION['flash_pesan'];
    unset($_SESSION['flash_pesan']);
}

// Ambil histori birahi setelah semua proses POST/GET selesei
$histori_birahi = $sapi->getBirahiByIdSapi($id_sapi);
$riwayat_aktivitas = $sapi->getHistoryBySapi($id_sapi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail: <?php echo htmlspecialchars($data_sapi['kode_sapi']); ?> - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative">

<?php include 'components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 md:p-5 px-4 md:px-6 sticky top-0 z-10 flex items-center gap-3">
        <div class="flex items-center truncate">
            <a href="sapi.php" class="text-gray-500 hover:text-emerald-600 mr-3 text-lg md:text-xl flex-shrink-0"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-lg md:text-xl font-bold text-gray-800 truncate">Detail: <?php echo htmlspecialchars($data_sapi['kode_sapi']); ?></h2>
        </div>
        <div class="ml-auto flex items-center"><?php include 'components/profile_dropdown.php'; ?></div>
    </header>

    <main class="p-4 pb-36 md:p-6 md:pb-6 space-y-6 w-full">
        
        <?php if(!empty($pesan)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative flex items-center gap-2 text-sm">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $pesan; ?></span>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative flex items-center gap-2 text-sm">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

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
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($data_sapi['tanggal_lahir']); ?></p>
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
                    'Sudah IB' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                    'Bunting' => 'bg-green-50 text-green-700 border border-green-200',
                    'Gagal Hamil' => 'bg-red-50 text-red-700 border border-red-200'
                ];
                $badge_class = isset($status_colors[$status_repro]) ? $status_colors[$status_repro] : $status_colors['Kosong'];
                ?>
                <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold <?php echo $badge_class; ?>">
                    <?php echo $status_repro; ?>
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
                <?php
                $latest_birahi = $sapi->getLatestBirahi($id_sapi);
                $waktu_ib_text = '-';
                if ($latest_birahi) {
                    $waktu_birahi = strtotime($latest_birahi['tanggal_birahi']);
                    $waktu_ib = $waktu_birahi + (12 * 3600);
                    $waktu_ib_text = date('d M Y, H:i', $waktu_ib);
                }
                ?>
                <div class="p-4 rounded-xl mb-6 bg-pink-50 border border-pink-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-pink-500"></i>
                        <div class="text-sm">
                            <p class="font-bold text-pink-700 mb-1">Jadwal IB Optimal:</p>
                            <p class="text-gray-700"><?php echo $waktu_ib_text; ?></p>
                            <p class="text-xs text-pink-400 mt-1">(12 Jam setelah terdeteksi birahi)</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($status == 'Sudah IB'): ?>
                <?php
                $waktu_ib = strtotime($data_sapi['tanggal_ib']);
                $waktu_pantau = $waktu_ib + (21 * 24 * 3600);
                $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
                ?>
                <div class="p-5 rounded-xl mb-6 bg-blue-50 border border-blue-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-blue-500"></i>
                        <div class="text-sm space-y-3">
                            <div>
                                <p class="font-bold text-blue-700">Pantau Birahi Ulang (H+21):</p>
                                <p class="text-gray-700"><?php echo date('d M Y', $waktu_pantau); ?></p>
                            </div>
                            <div>
                                <p class="font-bold text-purple-700">Jadwal Cek PKB (H+60):</p>
                                <p class="text-gray-700"><?php echo date('d M Y', $waktu_pkb); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($status == 'Bunting'): ?>
                <?php
                $waktu_ib = strtotime($data_sapi['tanggal_ib']);
                $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
                ?>
                <div class="p-4 rounded-xl mb-6 bg-green-50 border border-green-100 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle mt-0.5 text-lg text-green-500"></i>
                        <div class="text-sm">
                            <p class="font-bold text-green-700 mb-1">Perkiraan Melahirkan (HPL):</p>
                            <p class="text-gray-700"><?php echo date('d M Y', $waktu_hpl); ?></p>
                            <p class="text-xs text-green-400 mt-1">(283 hari sejak Inseminasi Buatan)</p>
                        </div>
                    </div>
                </div>
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
                    <form method="POST" class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Tanggal Mulai Birahi</label>
                            <input type="date" name="tanggal_birahi" value="<?php echo date('Y-m-d'); ?>" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Jam (Opsional)</label>
                            <input type="time" name="waktu_birahi" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="simpan_birahi" class="flex items-center justify-center gap-2 bg-pink-500 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-pink-600 transition shadow-md w-full md:w-auto h-[42px]"><i class="fas fa-save"></i> Simpan Birahi</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Sudah Birahi'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-syringe text-blue-500"></i> Lapor Tindakan Inseminasi (IB)
                    </h4>
                    <form method="POST" class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Tanggal Implementasi IB</label>
                            <input type="datetime-local" name="tanggal_ib" value="<?php echo date('Y-m-d\TH:i'); ?>" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex flex-col md:flex-row items-end gap-2 w-full md:w-auto">
                            <button type="submit" name="simpan_ib" class="flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-blue-700 transition shadow-md w-full md:w-auto h-[42px] whitespace-nowrap"><i class="fas fa-save"></i> Simpan Data IB</button>
                            <button type="submit" name="batal_birahi" formnovalidate onclick="return confirm('Apakah Anda yakin ingin membatalkan status Sudah Birahi dan menghapus log birahi terakhir?');" class="flex items-center justify-center gap-2 bg-white border border-red-500 text-red-500 font-semibold py-2 px-4 rounded-lg hover:bg-red-50 transition shadow-sm w-full md:w-auto h-[42px]" title="Batalkan laporan birahi"><i class="fas fa-undo"></i> Batal</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Sudah IB'): ?>
                <!-- PKB Section -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-stethoscope text-purple-600"></i> Hasil Pemeriksaan Kebuntingan (PKB)
                    </h4>
                    <form method="POST" class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Status PKB Hasil Pemeriksaan</label>
                            <select name="hasil_pkb" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="Bunting">Bunting (Positif)</option>
                                <option value="Tidak">Tidak Bunting (Negatif)</option>
                                <option value="Gagal">Gagal Hamil</option>
                            </select>
                        </div>
                        <div class="flex flex-col md:flex-row items-end gap-2 w-full md:w-auto">
                            <button type="submit" name="simpan_pkb" class="flex items-center justify-center gap-2 bg-purple-600 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-purple-700 transition shadow-md w-full md:w-auto h-[42px] whitespace-nowrap"><i class="fas fa-save"></i> Simpan Hasil PKB</button>
                            <button type="submit" name="batal_ib" formnovalidate onclick="return confirm('Apakah Anda yakin ingin membatalkan laporan IB dan kembali ke tahap Sudah Birahi?');" class="flex items-center justify-center gap-2 bg-white border border-red-500 text-red-500 font-semibold py-2 px-4 rounded-lg hover:bg-red-50 transition shadow-sm w-full md:w-auto h-[42px]" title="Batalkan laporan IB"><i class="fas fa-undo"></i> Batal</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($status == 'Bunting'): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-baby-carriage text-green-500"></i> Pelaporan Kelahiran Sapi
                    </h4>
                    <form method="POST" class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Tanggal Indukan Melahirkan</label>
                            <input type="date" name="tanggal_kelahiran" value="<?php echo date('Y-m-d'); ?>" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="flex flex-col md:flex-row items-end gap-2 w-full md:w-auto">
                            <button type="submit" name="simpan_kelahiran" class="flex items-center justify-center gap-2 bg-green-600 text-white font-semibold py-2.5 px-6 rounded-lg hover:bg-green-700 transition shadow-md w-full md:w-auto h-[42px] whitespace-nowrap"><i class="fas fa-save"></i> Laporkan Kelahiran</button>
                            <button type="submit" name="batal_bunting" formnovalidate onclick="return confirm('Apakah Anda yakin ingin membatalkan status Bunting dan kembali ke tahap Sudah IB?');" class="flex items-center justify-center gap-2 bg-white border border-red-500 text-red-500 font-semibold py-2 px-4 rounded-lg hover:bg-red-50 transition shadow-sm w-full md:w-auto h-[42px]" title="Batalkan laporan Bunting"><i class="fas fa-undo"></i> Batal</button>
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
        <div class="bg-[#0A3622] rounded-2xl shadow-xl p-6 relative overflow-hidden">
            <!-- Glassmorphism decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/20 rounded-full filter blur-[80px] -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-500/20 rounded-full filter blur-[60px] translate-y-1/3 -translate-x-1/4"></div>
            
            <h4 class="font-bold text-white mb-6 flex items-center gap-2 relative z-10 text-lg">
                <i class="fas fa-stream text-[#00D084]"></i> Riwayat Reproduksi (Timeline)
            </h4>
            
            <div class="relative z-10 pl-3">
                <div class="absolute left-[19px] top-2 bottom-2 w-0.5 bg-white/10"></div>
                <div class="space-y-6">
                    <?php if(count($riwayat_aktivitas) > 0): ?>
                        <?php foreach($riwayat_aktivitas as $log): 
                            $jenis = $log['jenis'];
                            // Tentukan ikon dan warna berdasarkan jenis aktivitas
                            $icon = "fas fa-info";
                            $bg_color = "bg-gray-500/20";
                            $text_color = "text-gray-300";
                            
                            if (strpos($jenis, 'birahi') !== false) {
                                $icon = "fas fa-calendar-alt"; $bg_color = "bg-pink-500/20"; $text_color = "text-pink-400";
                            } elseif (strpos($jenis, 'inseminasi') !== false || strpos($jenis, 'ib') !== false) {
                                $icon = "fas fa-syringe"; $bg_color = "bg-blue-500/20"; $text_color = "text-blue-400";
                            } elseif (strpos($jenis, 'pkb') !== false) {
                                $icon = "fas fa-stethoscope"; $bg_color = "bg-purple-500/20"; $text_color = "text-purple-400";
                            } elseif (strpos($jenis, 'bunting') !== false) {
                                $icon = "fas fa-baby"; $bg_color = "bg-green-500/20"; $text_color = "text-green-400";
                            } elseif (strpos($jenis, 'kelahiran') !== false) {
                                $icon = "fas fa-baby-carriage"; $bg_color = "bg-emerald-500/20"; $text_color = "text-emerald-400";
                            } elseif (strpos($jenis, 'gagal') !== false) {
                                $icon = "fas fa-times-circle"; $bg_color = "bg-red-500/20"; $text_color = "text-red-400";
                            } else {
                                $icon = "fas fa-check"; $bg_color = "bg-emerald-500/20"; $text_color = "text-[#00D084]";
                            }
                        ?>
                        <div class="relative pl-8">
                            <span class="absolute -left-3 top-1 flex h-8 w-8 items-center justify-center rounded-full <?php echo $bg_color; ?> border border-white/10 backdrop-blur-md">
                                <i class="<?php echo $icon; ?> text-[13px] <?php echo $text_color; ?>"></i>
                            </span>
                            <div class="bg-white/5 border border-white/10 backdrop-blur-sm rounded-xl p-4 transition hover:bg-white/10">
                                <p class="text-sm text-gray-200"><?php echo htmlspecialchars($log['deskripsi']); ?></p>
                                <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                                    <i class="far fa-clock"></i>
                                    <span><?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pl-6 text-sm text-gray-400 italic">Belum ada riwayat reproduksi.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card: Arsip Histori Birahi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-history text-gray-400"></i> Arsip Histori Birahi (Khusus Diagnostik)
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="p-3 font-bold text-gray-500 text-xs uppercase tracking-wider">Tanggal Birahi Deteksi</th>
                            <th class="p-3 font-bold text-gray-500 text-xs uppercase tracking-wider">Tercatat Pada</th>
                            <th class="p-3 font-bold text-gray-500 text-xs uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if($histori_birahi->rowCount() > 0): ?>
                            <?php while($row = $histori_birahi->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 font-medium text-pink-600"><?php echo date('d M Y, H:i', strtotime($row['tanggal_birahi'])); ?></td>
                                <td class="p-3 text-gray-400"><?php echo date('d/m/y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="p-3 text-center">
                                    <a href="?id=<?php echo $id_sapi; ?>&hapus_birahi=<?php echo $row['id']; ?>" onclick="return confirm('Hapus archieve data birahi ini?')" title="Hapus" class="text-red-400 hover:text-red-600 transition">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="p-4 text-center text-gray-400 italic text-sm">Belum ada histori birahi.</td></tr>
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
