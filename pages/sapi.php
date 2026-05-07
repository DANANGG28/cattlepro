<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$error = '';

// Handle Tambah Sapi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_sapi'])) {
    $sapi->kode_sapi = $_POST['kode_sapi'];
    $sapi->jenis = $_POST['jenis'];
    $sapi->tanggal_lahir = $_POST['tanggal_lahir'];
    $sapi->berat = $_POST['berat'];
    $sapi->status_reproduksi = 'Kosong';
    $sapi->admin_id = $_SESSION['user_id'];

    $new_id = $sapi->create();
    if ($new_id) {
        $pesan = "Sapi berhasil ditambahkan!";
        $sapi->logActivity($_SESSION['user_id'], 'tambah_sapi', "Mendaftarkan sapi baru: {$_POST['kode_sapi']}");
    } else {
        $error = "Gagal menambahkan sapi.";
    }
}

// Handle Edit Sapi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_sapi'])) {
    $sapi->id = $_POST['id'];
    $sapi->kode_sapi = $_POST['kode_sapi'];
    $sapi->jenis = $_POST['jenis'];
    $sapi->tanggal_lahir = $_POST['tanggal_lahir'];
    $sapi->berat = $_POST['berat'];

    if ($sapi->update()) {
        $pesan = "Data sapi berhasil diperbarui!";
        $sapi->logActivity($_SESSION['user_id'], 'edit_sapi', "Mengubah data informasi sapi: {$_POST['kode_sapi']}");
    } else {
        $error = "Gagal memperbarui data sapi.";
    }
}

// Handle Hapus Sapi
if (isset($_GET['hapus'])) {
    // Info for logging before deleting
    $sapi_to_delete = $sapi->getById($_GET['hapus']);
    if ($sapi->delete($_GET['hapus'])) {
        $pesan = "Sapi berhasil dihapus!";
        if ($sapi_to_delete) {
            $sapi->logActivity($_SESSION['user_id'], 'hapus_sapi', "Menghapus sapi dari sistem: {$sapi_to_delete['kode_sapi']}");
        }
    } else {
        $error = "Gagal menghapus sapi.";
    }
}

// Handle Search
$keyword = isset($_GET['search']) ? $_GET['search'] : '';
if ($keyword) {
    $semua_sapi = $sapi->search($keyword)->fetchAll(PDO::FETCH_ASSOC);
} else {
    $semua_sapi = $sapi->readAll()->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sapi - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <!-- Topbar -->
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Data Sapi</h2>
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Sapi</h2>
        <div class="flex items-center gap-4 ml-auto">
            <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <!-- Content -->
    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">

        <?php if($pesan): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?php echo $pesan; ?>
            </div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Search & Add -->
        <!-- Main Table Section -->
        <?php 
            $status_colors = [
                'Kosong' => 'bg-gray-100 text-gray-600',
                'Sudah Birahi' => 'bg-pink-100 text-pink-700',
                'Sudah IB' => 'bg-blue-100 text-blue-700',
                'Bunting' => 'bg-emerald-100 text-emerald-700',
                'Gagal Hamil' => 'bg-red-100 text-red-700'
            ];
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Unified Section Header -->
            <div class="p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-r from-white to-blue-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shadow-sm">
                        <i class="fas fa-cow text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[16px] text-slate-800">Daftar Populasi Sapi</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] text-emerald-600 uppercase font-bold tracking-widest">Database System</span>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md lg:mx-4">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="sapiSearch" placeholder="Cari Kode atau Jenis Sapi..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all shadow-sm">
                </div>

                <div class="flex items-center gap-2">
                    <a href="import_page.php" class="flex-1 sm:flex-none bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-4 rounded-xl text-[12px] hover:bg-gray-50 transition-all shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fas fa-file-import text-blue-500"></i> Import Data
                    </a>
                    <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="flex-1 sm:flex-none bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-2.5 px-5 rounded-xl text-[12px] hover:from-emerald-600 hover:to-green-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fas fa-plus"></i> Tambah Sapi
                    </button>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Nama / ID & Jenis Sapi</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Informasi Sapi</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Status</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Admin Terakhir</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(count($semua_sapi) > 0): ?>
                            <?php foreach($semua_sapi as $s): ?>
                            <?php 
                                $status = trim($s['status_reproduksi'] ?? 'Kosong');
                                $badge = $status_colors[$status] ?? $status_colors['Kosong'];
                            ?>
                            <tr class="hover:bg-gray-50/50 transition border-b border-gray-50 last:border-0 sapi-row">
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-800 text-[16px] search-target"><?php echo htmlspecialchars($s['kode_sapi']); ?></span>
                                        <span class="text-[12px] text-gray-400 font-bold search-target-sub"><?php echo htmlspecialchars($s['jenis']); ?></span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2 text-gray-600 text-[11px]">
                                            <i class="fas fa-calendar-day text-blue-300"></i>
                                            <span><?php echo tgl_indo($s['tanggal_lahir']); ?></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-600 text-[11px]">
                                            <i class="fas fa-weight-hanging text-emerald-300"></i>
                                            <span class="font-bold"><?php echo $s['berat']; ?> <span class="text-[9px] font-normal text-gray-400 uppercase">kg</span></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-[12px] font-black shadow-sm <?php echo $badge; ?> border border-current/10">
                                        <span class="h-2 w-2 rounded-full bg-current mr-2.5 opacity-80"></span>
                                        <?php echo str_replace('Sudah IB', 'Sudah Inseminasi Buatan', $status); ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full flex items-center justify-center text-[13px] font-black text-gray-500 border border-gray-200 shadow-sm shrink-0">
                                            <?php echo isset($s['last_admin']) ? strtoupper(substr($s['last_admin'], 0, 1)) : '-'; ?>
                                        </div>
                                        <?php 
                                            $role_label = 'ADMINISTRATOR'; 
                                            $role_color = 'text-blue-500';
                                        ?>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] text-slate-700 font-bold leading-none"><?php echo isset($s['last_admin']) ? htmlspecialchars($s['last_admin']) : 'System/Import'; ?></span>
                                            <span class="text-[9px] <?php echo $role_color; ?> uppercase tracking-tighter mt-0.5 font-bold"><?php echo $role_label; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2.5">
                                        <a href="detail_sapi.php?id=<?php echo $s['id']; ?>" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition shadow-sm group" title="Kelola Reproduksi">
                                            <i class="fas fa-stethoscope text-sm group-hover:scale-110 transition"></i>
                                        </a>
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s)); ?>)" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow-sm group" title="Edit Data">
                                            <i class="fas fa-pen-to-square text-sm group-hover:scale-110 transition"></i>
                                        </button>
                                        <a href="?hapus=<?php echo $s['id']; ?>" onclick="return confirm('Yakin ingin menghapus sapi ini?')" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition shadow-sm group" title="Hapus Data">
                                            <i class="fas fa-trash-alt text-sm group-hover:scale-110 transition"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center">
                                    <p class="text-gray-400 italic">Belum ada data sapi.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4 pb-20">
            <?php if(count($semua_sapi) > 0): ?>
                <?php foreach($semua_sapi as $s): ?>
                <?php 
                    $status = trim($s['status_reproduksi'] ?? 'Kosong');
                    $badge = $status_colors[$status] ?? $status_colors['Kosong'];
                ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4 sapi-card">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 search-target-mobile"><?php echo htmlspecialchars($s['kode_sapi']); ?></h3>
                            <p class="text-xs text-gray-400 font-medium search-target-sub-mobile"><?php echo htmlspecialchars($s['jenis']); ?></p>
                        </div>
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-[12px] font-black shadow-sm <?php echo $badge; ?>">
                            <span class="h-2 w-2 rounded-full bg-current mr-2.5 opacity-80"></span>
                            <?php echo str_replace('Sudah IB', 'Sudah Inseminasi Buatan', $status); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-y border-gray-50">
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Tanggal Lahir</p>
                            <div class="flex items-center gap-2 text-gray-600 text-xs">
                                <i class="fas fa-calendar-day text-blue-300"></i>
                                <span><?php echo tgl_indo($s['tanggal_lahir']); ?></span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] uppercase text-gray-400 font-bold tracking-wider">Berat Badan</p>
                            <div class="flex items-center gap-2 text-gray-600 text-xs">
                                <i class="fas fa-weight-hanging text-emerald-300"></i>
                                <span class="font-bold"><?php echo $s['berat']; ?> kg</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-50 rounded-full flex items-center justify-center text-[11px] font-bold text-gray-400 border border-gray-100">
                                <?php echo isset($s['last_admin']) ? strtoupper(substr($s['last_admin'], 0, 1)) : '-'; ?>
                            </div>
                        <?php 
                            $role_label = 'ADMINISTRATOR'; 
                            $role_color = 'text-blue-500';
                        ?>
                        <div class="flex flex-col leading-tight">
                            <span class="text-[12px] text-slate-700 font-bold"><?php echo isset($s['last_admin']) ? htmlspecialchars($s['last_admin']) : 'System/Import'; ?></span>
                            <span class="text-[10px] <?php echo $role_color; ?> uppercase font-bold tracking-tight"><?php echo $role_label; ?></span>
                        </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a href="detail_sapi.php?id=<?php echo $s['id']; ?>" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm">
                                <i class="fas fa-stethoscope"></i>
                            </a>
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s)); ?>)" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                                <i class="fas fa-pen-to-square"></i>
                            </button>
                            <a href="?hapus=<?php echo $s['id']; ?>" onclick="return confirm('Yakin ingin menghapus sapi ini?')" class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shadow-sm">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white rounded-2xl p-10 text-center border border-dashed border-gray-200">
                    <p class="text-gray-400 text-sm">Belum ada data sapi.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Tambah Sapi -->
<div id="modal-tambah" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-plus-circle text-emerald-500 mr-2"></i>Tambah Sapi Baru</h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Kode Sapi</label>
                <input type="text" name="kode_sapi" required placeholder="Contoh: S001" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Jenis Sapi</label>
                <select name="jenis" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    <option value="Limousin">Limousin</option>
                    <option value="Simental">Simental</option>
                    <option value="Brahman">Brahman</option>
                    <option value="Angus">Angus</option>
                    <option value="PO (Peranakan Ongole)">PO (Peranakan Ongole)</option>
                    <option value="Bali">Bali</option>
                    <option value="Madura">Madura</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Berat (kg)</label>
                    <input type="number" step="0.1" name="berat" required placeholder="Contoh: 350" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-tambah').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="tambah_sapi" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-sm hover:from-emerald-600 hover:to-green-700 transition shadow-lg shadow-emerald-200">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Sapi -->
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-pen text-amber-500 mr-2"></i>Edit Data Sapi</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="edit-id">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Kode Sapi</label>
                <input type="text" name="kode_sapi" id="edit-kode" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Jenis Sapi</label>
                <select name="jenis" id="edit-jenis" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                    <option value="Limousin">Limousin</option>
                    <option value="Simental">Simental</option>
                    <option value="Brahman">Brahman</option>
                    <option value="Angus">Angus</option>
                    <option value="PO (Peranakan Ongole)">PO (Peranakan Ongole)</option>
                    <option value="Bali">Bali</option>
                    <option value="Madura">Madura</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="edit-tgl" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Berat (kg)</label>
                    <input type="number" step="0.1" name="berat" id="edit-berat" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="edit_sapi" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-sm hover:from-amber-600 hover:to-orange-600 transition shadow-lg shadow-amber-200">Update</button>
            </div>
        </form>
    </div>
</div>



<script>
function openEditModal(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-kode').value = data.kode_sapi;
    document.getElementById('edit-jenis').value = data.jenis;
    document.getElementById('edit-tgl').value = data.tanggal_lahir;
    document.getElementById('edit-berat').value = data.berat;
    document.getElementById('modal-edit').classList.remove('hidden');
}

document.getElementById('sapiSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    // Filter Table Rows
    const rows = document.querySelectorAll('.sapi-row');
    rows.forEach(row => {
        const kode = row.querySelector('.search-target').innerText.toLowerCase();
        const jenis = row.querySelector('.search-target-sub').innerText.toLowerCase();
        row.style.display = (kode.includes(searchTerm) || jenis.includes(searchTerm)) ? '' : 'none';
    });

    // Filter Cards
    const cards = document.querySelectorAll('.sapi-card');
    cards.forEach(card => {
        const kode = card.querySelector('.search-target-mobile').innerText.toLowerCase();
        const jenis = card.querySelector('.search-target-sub-mobile').innerText.toLowerCase();
        card.style.display = (kode.includes(searchTerm) || jenis.includes(searchTerm)) ? '' : 'none';
    });
});
</script>

</body>
</html>
