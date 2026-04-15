<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pesan = '';
$error = '';

// Handle Tambah Sapi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_sapi'])) {
    $sapi->kode_sapi = $_POST['kode_sapi'];
    $sapi->jenis = $_POST['jenis'];
    $sapi->tanggal_lahir = $_POST['tanggal_lahir'];
    $sapi->berat = $_POST['berat'];
    $sapi->status_reproduksi = 'Kosong';

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
    } else {
        $error = "Gagal memperbarui data sapi.";
    }
}

// Handle Hapus Sapi
if (isset($_GET['hapus'])) {
    if ($sapi->delete($_GET['hapus'])) {
        $pesan = "Sapi berhasil dihapus!";
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

<?php include 'components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <!-- Topbar -->
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800 hidden sm:block">Data Sapi</h2>
        <h2 class="text-lg font-bold text-slate-800 sm:hidden">Sapi</h2>
        <div class="flex items-center gap-4 ml-auto">
            <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>
            <?php include 'components/profile_dropdown.php'; ?>
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
        <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
            <form method="GET" class="relative w-full sm:w-80">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Cari kode atau jenis sapi..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-white">
            </form>
            <div class="flex items-center gap-2">
                <a href="export_sapi.php" target="_blank" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-4 rounded-xl text-sm hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-file-export text-emerald-500"></i> Export Semua
                </a>
                <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')" class="bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-2.5 px-5 rounded-xl text-sm hover:from-emerald-600 hover:to-green-700 transition-all shadow-lg shadow-emerald-200 flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-plus"></i> Tambah Sapi
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">No</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Kode Sapi</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Jenis</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Tgl Lahir</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Berat (kg)</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Status</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Admin Terakhir</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(count($semua_sapi) > 0): ?>
                            <?php $no = 1; foreach($semua_sapi as $s): ?>
                            <?php 
                                $status = isset($s['status_reproduksi']) ? $s['status_reproduksi'] : 'Kosong';
                                $status_colors = [
                                    'Kosong' => 'bg-gray-100 text-gray-600',
                                    'Sudah Birahi' => 'bg-pink-100 text-pink-700',
                                    'Sudah IB' => 'bg-amber-100 text-amber-700',
                                    'Bunting' => 'bg-emerald-100 text-emerald-700',
                                    'Gagal Hamil' => 'bg-red-100 text-red-700'
                                ];
                                $badge = isset($status_colors[$status]) ? $status_colors[$status] : $status_colors['Kosong'];
                            ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-4 text-gray-400 font-mono text-xs"><?php echo $no++; ?></td>
                                <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($s['kode_sapi']); ?></td>
                                <td class="p-4 text-gray-600"><?php echo htmlspecialchars($s['jenis']); ?></td>
                                <td class="p-4 text-gray-500"><?php echo date('d M Y', strtotime($s['tanggal_lahir'])); ?></td>
                                <td class="p-4 text-gray-600 font-medium"><?php echo $s['berat']; ?></td>
                                <td class="p-4">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo $badge; ?>"><?php echo $status; ?></span>
                                </td>
                                <td class="p-4 text-gray-500 text-xs">
                                    <?php echo isset($s['last_admin']) ? htmlspecialchars($s['last_admin']) : '-'; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="detail_sapi.php?id=<?php echo $s['id']; ?>" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-100 transition" title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="export_sapi.php?id=<?php echo $s['id']; ?>" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Export / Cetak">
                                            <i class="fas fa-print text-xs"></i>
                                        </a>
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s)); ?>)" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center hover:bg-amber-100 transition" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <a href="?hapus=<?php echo $s['id']; ?>" onclick="return confirm('Yakin ingin menghapus sapi ini?')" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition" title="Hapus">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-10 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                            <i class="fas fa-cow text-gray-300 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-400 font-medium mb-1">Belum ada data sapi</p>
                                        <p class="text-xs text-gray-300">Klik "Tambah Sapi" untuk menambahkan data pertama.</p>
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
</script>

</body>
</html>
