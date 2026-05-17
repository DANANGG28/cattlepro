<?php
require_once '../controllers/main.php';
require_once '../controllers/ExcelReader.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_import'])) {
    $file = $_FILES['file_import']['tmp_name'];
    $file_name = $_FILES['file_import']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi format file
    $allowed_extensions = ['csv', 'xlsx', 'xls'];
    if (!in_array($file_ext, $allowed_extensions)) {
        $error = 'Format file tidak valid! Gunakan file dengan format CSV, XLSX, atau XLS.';
    } else {
        try {
            // Gunakan ExcelReader untuk membaca file
            $reader = new ExcelReader($file);
            $data_array = $reader->read();
            
            if (empty($data_array)) {
                $error = 'File kosong atau tidak dapat dibaca.';
            } else {
                $success_count = 0;
                $error_count = 0;
                $row_index = 0;

                foreach ($data_array as $data) {
                    $row_index++;

                    // Skip header row
                    if ($row_index == 1) continue;

                    // Skip empty rows or rows with too few columns
                    if (count($data) < 4 || empty(trim($data[0]))) continue;

                    // Skip guide/hint rows
                    if (
                        stripos(trim($data[0]), 'wajib') !== false ||
                        trim($data[2]) === 'YYYY-MM-DD'
                    ) continue;

                    $sapi->kode_sapi    = substr(trim($data[0]), 0, 50);
                    $sapi->jenis        = substr(trim($data[1]), 0, 50);
                    $sapi->tanggal_lahir = trim($data[2]);
                    $sapi->berat        = trim($data[3]);

                    $status_val      = isset($data[4]) ? trim($data[4]) : 'Kosong';
                    $tanggal_status  = isset($data[5]) ? trim($data[5]) : '';

                    $valid_statuses = ['Kosong', 'Sudah Birahi', 'Sudah IB', 'Bunting'];
                    if (!in_array($status_val, $valid_statuses)) {
                        $status_val = 'Kosong';
                    }

                    $sapi->status_reproduksi = $status_val;
                    $sapi->admin_id = (int)$_SESSION['user_id'];

                    try {
                        $new_id = $sapi->create();
                        if ($new_id) {
                            $success_count++;
                            if ($status_val === 'Sudah Birahi' && !empty($tanggal_status)) {
                                $sapi->createBirahi($new_id, $tanggal_status);
                            } elseif (in_array($status_val, ['Sudah IB', 'Bunting']) && !empty($tanggal_status)) {
                                $sapi->setTanggalIB($new_id, $tanggal_status);
                            }
                        } else {
                            $error_count++;
                        }
                    } catch (PDOException $e) {
                        $error_count++;
                    }
                }

                if ($success_count > 0 || $error_count > 0) {
                    $pesan = "Import selesai! $success_count data berhasil dimasukkan" . ($error_count > 0 ? ", $error_count baris gagal/dilewati." : ".");
                } else {
                    $error = "Tidak ada data valid yang bisa di-import. Pastikan file sesuai template.";
                }
            }
        } catch (Exception $e) {
            $error = 'Gagal memproses file: ' . $e->getMessage();
        }
    }
}

// Kode lama yang tidak digunakan lagi
if (false) {
    if ($file_ext !== 'csv') {
        $error = 'Format file tidak valid! Pastikan Anda melakukan "Save As" ke format CSV di Excel sebelum upload.';
    } else {
        $handle = fopen($file, 'r');
        if (!$handle) {
            $error = 'Gagal membuka file. Coba lagi.';
        } else {
            $success_count = 0;
            $error_count = 0;
            $row_index = 0;

            while (($data = fgetcsv($handle, 2000, ',')) !== FALSE) {
                $row_index++;

                // Skip header row
                if ($row_index == 1) continue;

                // Skip empty rows or rows with too few columns
                if (count($data) < 4 || empty(trim($data[0]))) continue;

                // Skip guide/hint rows
                if (
                    stripos(trim($data[0]), 'wajib') !== false ||
                    trim($data[2]) === 'YYYY-MM-DD'
                ) continue;

                $sapi->kode_sapi    = substr(trim($data[0]), 0, 50); // Mencegah data kepanjangan
                $sapi->jenis        = substr(trim($data[1]), 0, 50);
                $sapi->tanggal_lahir = trim($data[2]);
                $sapi->berat        = trim($data[3]);

                $status_val      = isset($data[4]) ? trim($data[4]) : 'Kosong';
                $tanggal_status  = isset($data[5]) ? trim($data[5]) : '';

                $valid_statuses = ['Kosong', 'Sudah Birahi', 'Sudah IB', 'Bunting'];
                if (!in_array($status_val, $valid_statuses)) {
                    $status_val = 'Kosong';
                }

                $sapi->status_reproduksi = $status_val;
                $sapi->admin_id = (int)$_SESSION['user_id'];

                try {
                    $new_id = $sapi->create();
                    if ($new_id) {
                        $success_count++;
                        if ($status_val === 'Sudah Birahi' && !empty($tanggal_status)) {
                            $sapi->createBirahi($new_id, $tanggal_status);
                        } elseif (in_array($status_val, ['Sudah IB', 'Bunting']) && !empty($tanggal_status)) {
                            $sapi->setTanggalIB($new_id, $tanggal_status);
                        }
                    } else {
                        $error_count++;
                    }
                } catch (PDOException $e) {
                    $error_count++;
                    // Optional: log error message $e->getMessage()
                }
            }

            fclose($handle);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data Sapi - CattlePro</title>
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
                            <span>Simpan file dan upload langsung. Mendukung format <span class="font-bold">CSV, XLSX, atau XLS</span>.</span>
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
                    <p class="text-sm text-gray-400 mb-8 max-w-sm">Upload file yang sudah diisi data sapi sesuai template. Mendukung format <strong class="text-gray-500">CSV, XLSX, dan XLS</strong>.</p>
                    
                    <form action="" method="POST" enctype="multipart/form-data" class="w-full max-w-md space-y-6" id="uploadForm">
                        <div class="relative group">
                            <input type="file" name="file_import" id="file_import" accept=".csv,.xlsx,.xls" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div id="drop-zone" class="border-2 border-dashed border-gray-200 group-hover:border-blue-400 rounded-2xl p-10 transition-all bg-gray-50 group-hover:bg-blue-50/30 flex flex-col items-center">
                                <i class="fas fa-cloud-upload-alt text-gray-300 group-hover:text-blue-400 text-4xl mb-4" id="upload-icon"></i>
                                <p class="text-sm font-bold text-gray-500 group-hover:text-blue-600" id="file-name">Klik untuk pilih file</p>
                                <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold">Format: CSV, XLSX, XLS</p>
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
// Validasi file extension
const allowedExtensions = ['csv', 'xlsx', 'xls'];
const maxFileSize = 5 * 1024 * 1024; // 5MB

document.getElementById('file_import').onchange = function() {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const fileName = file.name;
        const fileSize = file.size;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        
        // Reset styling
        const dropZone = document.getElementById('drop-zone');
        const fileNameDisplay = document.getElementById('file-name');
        const uploadIcon = document.getElementById('upload-icon');
        
        // Validasi extension
        if (!allowedExtensions.includes(fileExtension)) {
            dropZone.classList.remove('border-gray-200', 'border-emerald-300', 'bg-emerald-50/30');
            dropZone.classList.add('border-red-300', 'bg-red-50/30');
            
            fileNameDisplay.textContent = 'Format file tidak didukung!';
            fileNameDisplay.classList.remove('text-gray-500', 'text-blue-600', 'text-emerald-600');
            fileNameDisplay.classList.add('text-red-600');
            
            uploadIcon.classList.remove('text-gray-300', 'text-emerald-500', 'fa-cloud-upload-alt', 'fa-check-circle');
            uploadIcon.classList.add('text-red-500', 'fa-exclamation-circle');
            
            // Disable submit button
            document.querySelector('button[type="submit"]').disabled = true;
            document.querySelector('button[type="submit"]').classList.add('opacity-50', 'cursor-not-allowed');
            
            // Clear file input
            this.value = '';
            return false;
        }
        
        // Validasi ukuran file
        if (fileSize > maxFileSize) {
            dropZone.classList.remove('border-gray-200', 'border-emerald-300', 'bg-emerald-50/30');
            dropZone.classList.add('border-red-300', 'bg-red-50/30');
            
            fileNameDisplay.textContent = 'File terlalu besar! Maksimal 5MB';
            fileNameDisplay.classList.remove('text-gray-500', 'text-blue-600', 'text-emerald-600');
            fileNameDisplay.classList.add('text-red-600');
            
            uploadIcon.classList.remove('text-gray-300', 'text-emerald-500', 'fa-cloud-upload-alt', 'fa-check-circle');
            uploadIcon.classList.add('text-red-500', 'fa-exclamation-circle');
            
            // Disable submit button
            document.querySelector('button[type="submit"]').disabled = true;
            document.querySelector('button[type="submit"]').classList.add('opacity-50', 'cursor-not-allowed');
            
            // Clear file input
            this.value = '';
            return false;
        }
        
        // File valid
        const fileSizeKB = (fileSize / 1024).toFixed(2);
        fileNameDisplay.textContent = fileName + ' (' + fileSizeKB + ' KB)';
        fileNameDisplay.classList.remove('text-gray-500', 'text-red-600');
        fileNameDisplay.classList.add('text-emerald-600');
        
        uploadIcon.classList.remove('text-gray-300', 'text-red-500', 'fa-cloud-upload-alt', 'fa-exclamation-circle');
        uploadIcon.classList.add('text-emerald-500', 'fa-check-circle');
        
        dropZone.classList.remove('border-gray-200', 'border-red-300', 'bg-red-50/30');
        dropZone.classList.add('border-emerald-300', 'bg-emerald-50/30');
        
        // Enable submit button
        document.querySelector('button[type="submit"]').disabled = false;
        document.querySelector('button[type="submit"]').classList.remove('opacity-50', 'cursor-not-allowed');
    }
};

// Validasi sebelum submit
document.getElementById('uploadForm').onsubmit = function(e) {
    const fileInput = document.getElementById('file_import');
    
    if (!fileInput.files || !fileInput.files[0]) {
        e.preventDefault();
        alert('Silakan pilih file terlebih dahulu!');
        return false;
    }
    
    const file = fileInput.files[0];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        e.preventDefault();
        alert('Format file tidak didukung! Gunakan CSV, XLSX, atau XLS.');
        return false;
    }
    
    if (file.size > maxFileSize) {
        e.preventDefault();
        alert('Ukuran file terlalu besar! Maksimal 5MB.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    submitBtn.disabled = true;
    
    return true;
};
</script>

</body>
</html>
