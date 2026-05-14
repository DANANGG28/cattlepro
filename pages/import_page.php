<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import'])) {
    $file = $_FILES['file_import']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['file_import']['name'], PATHINFO_EXTENSION));
    
    if ($file_ext !== 'csv') {
        $error = 'Format file tidak valid! Pastikan Anda melakukan "Save As" ke format CSV di Excel sebelum upload.';
    } else {
        $handle = fopen($file, 'r');
        if (!$handle) {
            $error = 'Gagal membuka file. Coba lagi.';
        } else {
            $success_count = 0;
            $error_count   = 0;
            $skip_count    = 0;
            $row_index     = 0;
            $error_rows    = [];
            $skip_rows     = [];

            // Baca baris pertama untuk deteksi delimiter (koma vs titik koma)
            $first_line = fgets($handle);
            rewind($handle);
            $delimiter = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';

            while (($data = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
                $row_index++;
                if ($row_index == 1) continue; // Skip header

                // Skip baris kosong atau kurang kolom
                if (count($data) < 4 || empty(trim($data[0]))) continue;

                // Skip baris panduan
                if (stripos(trim($data[0]), 'wajib') !== false || trim($data[2]) === 'YYYY-MM-DD') continue;

                // Validasi & bersihkan format tanggal lahir
                $raw_tgl = trim($data[2]);
                // Coba konversi format d/m/Y atau d-m-Y ke Y-m-d
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw_tgl, $m)) {
                    $raw_tgl = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                }

                $sapi->kode_sapi         = substr(trim($data[0]), 0, 50);
                $sapi->jenis             = substr(trim($data[1]), 0, 50);
                $sapi->tanggal_lahir     = $raw_tgl;
                $sapi->berat             = (int)trim($data[3]);
                $status_val              = isset($data[4]) ? trim($data[4]) : 'Kosong';
                $raw_status_tgl          = isset($data[5]) ? trim($data[5]) : '';

                // Konversi tanggal_status dari d/m/Y ke Y-m-d juga
                if (!empty($raw_status_tgl) && preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw_status_tgl, $ms)) {
                    $tanggal_status = $ms[3] . '-' . str_pad($ms[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($ms[1], 2, '0', STR_PAD_LEFT);
                } else {
                    $tanggal_status = $raw_status_tgl;
                }

                $valid_statuses = ['Kosong', 'Sudah Birahi', 'Sudah IB', 'Bunting'];
                if (!in_array($status_val, $valid_statuses)) $status_val = 'Kosong';

                $sapi->status_reproduksi = $status_val;
                $sapi->admin_id          = $_SESSION['user_id'];

                // === VALIDASI DUPLIKAT: skip jika kode_sapi sudah ada ===
                if ($sapi->kodeSapiExists($sapi->kode_sapi)) {
                    $skip_count++;
                    $skip_rows[] = htmlspecialchars($data[0]);
                    continue;
                }

                // Jeda antar request untuk menghindari rate limit Firebase
                if ($row_index > 2) usleep(500000); // 500ms

                $new_id = $sapi->create();
                if ($new_id) {
                    $success_count++;

                    // Jeda sebelum panggilan secondary
                    usleep(400000); // 400ms

                    $secondary_ok = true;
                    if ($status_val === 'Sudah Birahi' && !empty($tanggal_status)) {
                        $secondary_ok = $sapi->createBirahi($new_id, $tanggal_status);
                        // Retry sekali jika gagal
                        if (!$secondary_ok) { usleep(600000); $sapi->createBirahi($new_id, $tanggal_status); }
                    } elseif (in_array($status_val, ['Sudah IB', 'Bunting']) && !empty($tanggal_status)) {
                        // Pass $status_val agar Bunting tidak dioverride ke Sudah IB
                        $secondary_ok = $sapi->setTanggalIB($new_id, $tanggal_status, $status_val);
                        // Retry sekali jika gagal
                        if (!$secondary_ok) { usleep(600000); $sapi->setTanggalIB($new_id, $tanggal_status, $status_val); }
                    }
                } else {
                    $error_count++;
                    $error_rows[] = "Baris {$row_index}: " . htmlspecialchars($data[0]);
                }
            }

            fclose($handle);

            if ($success_count > 0 || $skip_count > 0) {
                if ($success_count > 0) {
                    $sapi->logActivity(
                        $_SESSION['user_id'],
                        'import_sapi',
                        "Import data sapi dari Excel: {$success_count} data berhasil ditambahkan" . ($error_count > 0 ? ", {$error_count} gagal" : "") . ($skip_count > 0 ? ", {$skip_count} dilewati (duplikat)" : "")
                    );
                }
                $pesan = "✅ Import selesai! <strong>{$success_count} data berhasil</strong> dimasukkan.";
                if ($skip_count > 0) {
                    $pesan .= " <span class='text-yellow-600'>⚠️ {$skip_count} dilewati (kode sudah ada): " . implode(', ', $skip_rows) . "</span>";
                }
                if ($error_count > 0) {
                    $pesan .= " <span class='text-red-600'>❌ {$error_count} baris gagal.</span>";
                }
            } elseif ($error_count > 0) {
                $error = "❌ Semua {$error_count} baris gagal dimasukkan. Periksa format tanggal (YYYY-MM-DD) dan pastikan semua kolom wajib diisi.";
            } else {
                $error = "Tidak ada data valid yang bisa di-import. Pastikan file CSV sesuai template.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - CattlePro</title>
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
        <div class="flex items-center gap-3">
            <a href="sapi.php" class="text-gray-400 hover:text-gray-600 transition text-xl">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-xl font-bold text-slate-800">Import Data Sapi</h2>
        </div>
        <div class="flex items-center gap-4 ml-auto">
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full max-w-4xl mx-auto">

        <?php if($pesan): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl text-sm flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <div>
                    <p class="font-bold">Berhasil!</p>
                    <p><?php echo $pesan; ?></p>
                </div>
                <a href="sapi.php" class="ml-auto bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-emerald-600 transition">Lihat Data</a>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                <div>
                    <p class="font-bold">Gagal!</p>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Panduan -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-book text-blue-500"></i> Panduan Import
                    </h3>
                    <ul class="space-y-4 text-xs text-gray-600 leading-relaxed">
                        <li class="flex gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 font-bold">1</span>
                            <span>Download template CSV yang telah disediakan.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 font-bold">2</span>
                            <span>Buka di Excel, isi data sapi. Kolom <span class="font-bold">Status</span> pilih salah satu: <span class="font-bold italic">Kosong, Sudah Birahi, Sudah IB, Bunting</span>.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 font-bold">3</span>
                            <span>Jika ada Status, isi <span class="font-bold">Tanggal Status</span> dengan format <span class="font-bold">YYYY-MM-DD</span>.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 font-bold">4</span>
                            <span>Di Excel, klik <span class="font-bold">File → Save As → pilih format CSV</span>, lalu upload file tersebut di sini.</span>
                        </li>
                    </ul>

                    <div class="mt-5 bg-amber-50 border border-amber-100 rounded-xl p-3">
                        <p class="text-[10px] text-amber-700 font-bold flex items-center gap-1">
                            <i class="fas fa-lightbulb"></i> Tips
                        </p>
                        <p class="text-[10px] text-amber-600 mt-1 leading-relaxed">Baris panduan (baris ke-2) di template akan otomatis dilewati saat import.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="download_template.php" class="w-full bg-blue-50 text-blue-600 border border-blue-100 font-bold py-3 px-4 rounded-xl text-xs hover:bg-blue-100 transition flex items-center justify-center gap-2">
                            <i class="fas fa-file-excel"></i> Download Template Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-blue-50 rounded-3xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-excel text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Upload File Data Sapi</h3>
                    <p class="text-sm text-gray-400 mb-8 max-w-sm">Upload file CSV yang sudah diisi data sapi sesuai template. Pastikan file disimpan sebagai <strong class="text-gray-500">.csv</strong> dari Excel.</p>
                    
                    <form action="" method="POST" enctype="multipart/form-data" class="w-full max-w-md space-y-6">
                        <div class="relative group">
                            <input type="file" name="file_import" id="file_import" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div id="drop-zone" class="border-2 border-dashed border-gray-200 group-hover:border-blue-400 rounded-2xl p-10 transition-all bg-gray-50 group-hover:bg-blue-50/30 flex flex-col items-center">
                                <i class="fas fa-cloud-upload-alt text-gray-300 group-hover:text-blue-400 text-4xl mb-4" id="upload-icon"></i>
                                <p class="text-sm font-bold text-gray-500 group-hover:text-blue-600" id="file-name">Klik untuk pilih file</p>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Format: .CSV</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold py-4 rounded-2xl hover:from-blue-600 hover:to-indigo-700 transition shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                            <i class="fas fa-rocket"></i> Mulai Proses Import
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </main>
</div>

<script>
document.getElementById('file_import').onchange = function() {
    if (this.files && this.files[0]) {
        document.getElementById('file-name').textContent = this.files[0].name;
        document.getElementById('file-name').classList.remove('text-gray-500');
        document.getElementById('file-name').classList.add('text-blue-600');
        document.getElementById('upload-icon').classList.remove('text-gray-300', 'fa-cloud-upload-alt');
        document.getElementById('upload-icon').classList.add('text-emerald-500', 'fa-check-circle');
        document.getElementById('drop-zone').classList.remove('border-gray-200');
        document.getElementById('drop-zone').classList.add('border-emerald-300', 'bg-emerald-50/30');
    }
};
</script>

</body>
</html>
