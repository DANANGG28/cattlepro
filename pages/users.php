<?php
require_once '../controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($current_user['role']) || (strtolower($current_user['role']) !== 'admin' && strtolower($current_user['role']) !== 'superadmin')) {
    header("Location: dashboard.php");
    exit;
}

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_user'])) {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];

    if ($user_model->emailExists($email)) {
        header("Location: users.php?error=" . urlencode("Email $email sudah terdaftar."));
    } else {
        $user_model->nama     = $nama;
        $user_model->email    = $email;
        $user_model->password = $password;
        $user_model->role     = $role;
        if ($user_model->create()) {
            header("Location: users.php?pesan=" . urlencode("User $nama berhasil ditambahkan!"));
        } else {
            header("Location: users.php?error=" . urlencode("Gagal menambahkan user. Coba lagi."));
        }
    }
    exit;
}

// Handle Edit Password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_password'])) {
    $uid          = $_POST['user_id'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (strlen($new_pass) < 6) {
        header("Location: users.php?error=" . urlencode("Password minimal 6 karakter."));
    } elseif ($new_pass !== $confirm_pass) {
        header("Location: users.php?error=" . urlencode("Konfirmasi password tidak cocok."));
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        if ($user_model->updatePassword($uid, $hashed)) {
            header("Location: users.php?pesan=" . urlencode("Password berhasil diperbarui!"));
        } else {
            header("Location: users.php?error=" . urlencode("Gagal memperbarui password."));
        }
    }
    exit;
}

// Handle Delete User
if (isset($_GET['hapus_user'])) {
    $uid = $_GET['hapus_user'];
    if ($uid == $_SESSION['user_id']) {
        header("Location: users.php?error=" . urlencode("Tidak bisa menghapus akun sendiri."));
    } elseif ($user_model->delete($uid)) {
        header("Location: users.php?pesan=" . urlencode("User berhasil dihapus."));
    } else {
        header("Location: users.php?error=" . urlencode("Gagal menghapus user."));
    }
    exit;
}

// Handle Edit NIP/NIK & Nama
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_nip'])) {
    $uid       = $_POST['user_id_nip'];
    $nip       = trim($_POST['nip']);
    $nama_baru = trim($_POST['nama_baru']);

    if ($user_model->updateNip($uid, $nama_baru, $nip)) {
        if ($uid == $_SESSION['user_id']) {
            $current_user['nama'] = $nama_baru;
            $current_user['nip']  = $nip;
        }
        header("Location: users.php?pesan=" . urlencode("Nama & NIP/NIK berhasil diperbarui!"));
    } else {
        header("Location: users.php?error=" . urlencode("Gagal memperbarui NIP/NIK."));
    }
    exit;
}

// Get all users
$all_users = $user_model->readAll()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">

    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800">Manajemen User</h2>
        <div class="flex items-center gap-4 ml-auto">
            <?php include '../components/profile_dropdown.php'; ?>
        </div>
    </header>

    <main class="p-4 sm:p-6 pb-24 md:pb-6 space-y-6 w-full">




        <!-- Main Table & Cards Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Unified Section Header -->
            <div class="p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-r from-white to-blue-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shadow-sm">
                        <i class="fas fa-users-cog text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[16px] text-slate-800">Daftar Pengguna Sistem</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] text-emerald-600 uppercase font-bold tracking-widest">Access Control List</span>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md lg:mx-4">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="userSearch" placeholder="Cari Nama atau Email User..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all shadow-sm">
                </div>

                <button onclick="document.getElementById('modal-user').classList.remove('hidden')"
                    class="bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-2.5 px-5 rounded-xl text-sm hover:from-emerald-600 hover:to-green-700 transition-all shadow-lg shadow-emerald-200/50 flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i> Tambah User
                </button>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">No</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Nama</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">NIP / NIK</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Email</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em]">Role</th>
                            <th class="p-4 font-bold text-gray-400 text-[10px] uppercase tracking-[0.1em] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php $no = 1; foreach ($all_users as $u): ?>
                        <tr class="hover:bg-gray-50/50 transition user-row">
                            <td class="p-4 text-gray-400 font-mono text-xs"><?php echo $no++; ?></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm">
                                        <?php echo strtoupper(substr($u['nama'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-[14px] search-target"><?php echo htmlspecialchars($u['nama']); ?></p>
                                        <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                            <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">Akun Anda</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-[12px] font-mono"><?php echo $u['nip'] ? htmlspecialchars($u['nip']) : '<span class="text-gray-300 italic">Belum diisi</span>'; ?></td>
                            <td class="p-4 text-gray-500 text-[13px] search-target-sub"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="p-4">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold <?php echo (strtolower($u['role']) == 'admin' || strtolower($u['role']) == 'superadmin') ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <?php echo (strtolower($u['role']) == 'admin' || strtolower($u['role']) == 'superadmin') ? 'ADMINISTRATOR' : 'PETUGAS LAPANGAN'; ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditPassword('<?php echo $u['id']; ?>', '<?php echo htmlspecialchars($u['nama'], ENT_QUOTES); ?>')"
                                        class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 inline-flex items-center justify-center hover:bg-emerald-500 hover:text-white transition shadow-sm" title="Edit Password">
                                        <i class="fas fa-key text-xs"></i>
                                    </button>
                                    <button onclick="openEditNip('<?php echo $u['id']; ?>', '<?php echo htmlspecialchars($u['nama'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($u['nip'] ? $u['nip'] : '', ENT_QUOTES); ?>')"
                                        class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 inline-flex items-center justify-center hover:bg-blue-500 hover:text-white transition shadow-sm" title="Edit Nama & NIP">
                                        <i class="fas fa-id-card text-xs"></i>
                                    </button>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?hapus_user=<?php echo $u['id']; ?>" data-confirm-delete="User <?php echo htmlspecialchars($u['nama'], ENT_QUOTES); ?> akan dihapus dari sistem!"
                                            class="w-9 h-9 rounded-xl bg-red-50 text-red-600 inline-flex items-center justify-center hover:bg-red-500 hover:text-white transition shadow-sm" title="Hapus">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <div class="w-9 h-9"></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-50">
                <?php foreach ($all_users as $u): ?>
                <div class="p-5 space-y-4 user-card">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl flex items-center justify-center text-white font-bold shadow-sm">
                                <?php echo strtoupper(substr($u['nama'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 search-target-mobile"><?php echo htmlspecialchars($u['nama']); ?></h4>
                                <p class="text-xs text-gray-400 search-target-sub-mobile"><?php echo htmlspecialchars($u['email']); ?></p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold <?php echo (strtolower($u['role']) == 'admin' || strtolower($u['role']) == 'superadmin') ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                            <?php echo (strtolower($u['role']) == 'admin' || strtolower($u['role']) == 'superadmin') ? 'ADMINISTRATOR' : 'PETUGAS LAPANGAN'; ?>
                        </span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <?php if ($u['id'] == $_SESSION['user_id']): ?>
                            <div class="flex-1 bg-emerald-50 text-emerald-600 font-bold py-3 px-4 rounded-xl text-xs text-center border border-emerald-100">
                                Akun Anda
                            </div>
                        <?php endif; ?>
                        <button onclick="openEditPassword('<?php echo $u['id']; ?>', '<?php echo htmlspecialchars($u['nama'], ENT_QUOTES); ?>')"
                            class="flex-1 bg-emerald-50 text-emerald-600 font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2">
                            <i class="fas fa-key"></i> Password
                        </button>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="?hapus_user=<?php echo $u['id']; ?>" data-confirm-delete="User <?php echo htmlspecialchars($u['nama'], ENT_QUOTES); ?> akan dihapus dari sistem!"
                                class="w-11 h-11 bg-red-50 text-red-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah User -->
<div id="modal-user" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-plus text-emerald-500"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Tambah User Baru</h3>
            </div>
            <button onclick="document.getElementById('modal-user').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 flex items-center justify-center transition text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="contoh: Budi Santoso"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Email</label>
                <input type="email" name="email" required placeholder="contoh: budi@cattlepro.com"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Role</label>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
                    <option value="admin">ADMINISTRATOR</option>
                    <option value="petugas">PETUGAS LAPANGAN</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-user').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="tambah_user"
                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-sm hover:from-emerald-600 hover:to-green-700 transition shadow-lg shadow-emerald-200">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

</div>

<!-- Modal Edit NIP/NIK -->
<div id="modal-edit-nip" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-id-card text-emerald-500"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Edit Nama & NIP/NIK</h3>
                    <p class="text-xs text-gray-400" id="edit-nip-subtitle">Data ini akan muncul di tanda tangan PDF</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-edit-nip').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:bg-gray-200 flex items-center justify-center text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="user_id_nip" id="edit-nip-user-id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                <input type="text" name="nama_baru" id="edit-nip-nama" required placeholder="Nama yang akan muncul di TTD"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">NIP / NIK</label>
                <input type="text" name="nip" id="edit-nip-value" placeholder="contoh: 19850101 200604 1 001"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white font-mono">
                <p class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ingin menampilkan NIP/NIK di PDF.</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex items-start gap-2">
                <i class="fas fa-info-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-emerald-700">Nama & NIP/NIK ini akan otomatis muncul di blok tanda tangan pada setiap laporan PDF yang dicetak.</p>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit-nip').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="edit_nip"
                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-sm hover:from-emerald-600 hover:to-green-700 transition shadow-lg shadow-emerald-200">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Password -->
<div id="modal-edit-password" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-key text-emerald-500"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Edit Password</h3>
                    <p class="text-xs text-gray-400" id="edit-password-subtitle">untuk user</p>
                </div>
            </div>
            <button onclick="closeEditPassword()" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 flex items-center justify-center transition text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4" onsubmit="return validateEditPassword()">
            <input type="hidden" name="user_id" id="edit-user-id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" id="new_password" required placeholder="Minimal 6 karakter"
                        class="w-full px-4 py-2.5 pr-11 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition bg-gray-50 focus:bg-white">
                    <button type="button" onclick="toggleVis('new_password', 'eye1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-eye text-sm" id="eye1"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Ulangi password baru"
                        class="w-full px-4 py-2.5 pr-11 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-gray-50 focus:bg-white">
                    <button type="button" onclick="toggleVis('confirm_password', 'eye2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-eye text-sm" id="eye2"></i>
                    </button>
                </div>
                <p id="pass-match-msg" class="text-xs mt-1.5 hidden"></p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex items-start gap-2">
                <i class="fas fa-info-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-emerald-700">Password minimal 6 karakter. User akan perlu login ulang setelah password diubah.</p>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" onclick="closeEditPassword()"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="edit_password"
                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-sm hover:from-emerald-600 hover:to-green-700 transition shadow-lg shadow-emerald-200">
                    <i class="fas fa-save mr-1"></i> Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditNip(userId, userName, userNip) {
    document.getElementById('edit-nip-user-id').value = userId;
    document.getElementById('edit-nip-nama').value = userName;
    document.getElementById('edit-nip-value').value = userNip;
    document.getElementById('edit-nip-subtitle').textContent = 'Data tanda tangan PDF untuk: ' + userName;
    document.getElementById('modal-edit-nip').classList.remove('hidden');
}

function openEditPassword(userId, userName) {
    document.getElementById('edit-user-id').value = userId;
    document.getElementById('edit-password-subtitle').textContent = 'untuk: ' + userName;
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('pass-match-msg').classList.add('hidden');
    document.getElementById('modal-edit-password').classList.remove('hidden');
}

function closeEditPassword() {
    document.getElementById('modal-edit-password').classList.add('hidden');
}

function toggleVis(fieldId, iconId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function validateEditPassword() {
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;
    const msg = document.getElementById('pass-match-msg');

    if (np.length < 6) {
        msg.textContent = 'Password minimal 6 karakter.';
        msg.className = 'text-xs mt-1.5 text-red-500';
        msg.classList.remove('hidden');
        return false;
    }
    if (np !== cp) {
        msg.textContent = 'Password tidak cocok!';
        msg.className = 'text-xs mt-1.5 text-red-500';
        msg.classList.remove('hidden');
        return false;
    }
    return true;
}

document.getElementById('confirm_password').addEventListener('input', function() {
    const np = document.getElementById('new_password').value;
    const msg = document.getElementById('pass-match-msg');
    if (this.value === '') { msg.classList.add('hidden'); return; }
    if (this.value === np) {
        msg.textContent = 'Password cocok ✓';
        msg.className = 'text-xs mt-1.5 text-emerald-600 font-semibold';
        msg.classList.remove('hidden');
    } else {
        msg.textContent = 'Password tidak cocok';
        msg.className = 'text-xs mt-1.5 text-red-500';
        msg.classList.remove('hidden');
    }
});

// Close modal on backdrop click
document.getElementById('modal-user').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
document.getElementById('modal-edit-password').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});

document.getElementById('userSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    // Filter Table Rows
    const rows = document.querySelectorAll('.user-row');
    rows.forEach(row => {
        const nama = row.querySelector('.search-target').innerText.toLowerCase();
        const email = row.querySelector('.search-target-sub').innerText.toLowerCase();
        row.style.display = (nama.includes(searchTerm) || email.includes(searchTerm)) ? '' : 'none';
    });

    // Filter Cards
    const cards = document.querySelectorAll('.user-card');
    cards.forEach(card => {
        const nama = card.querySelector('.search-target-mobile').innerText.toLowerCase();
        const email = card.querySelector('.search-target-sub-mobile').innerText.toLowerCase();
        card.style.display = (nama.includes(searchTerm) || email.includes(searchTerm)) ? '' : 'none';
    });
});
</script>

</body>
</html>
