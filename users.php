<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Only admin can access
if (!isset($current_user['role']) || $current_user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan = '';
$error = '';

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $check->bindParam(':email', $email);
    $check->execute();

    if ($check->rowCount() > 0) {
        $error = "Email sudah terdaftar.";
    } else {
        $stmt = $db->prepare("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, :role)");
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        if ($stmt->execute()) {
            $pesan = "User berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan user.";
        }
    }
}

// Handle Delete User
if (isset($_GET['hapus_user'])) {
    $uid = $_GET['hapus_user'];
    if ($uid != $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $uid);
        if ($stmt->execute()) {
            $pesan = "User berhasil dihapus!";
        }
    } else {
        $error = "Tidak bisa menghapus akun sendiri.";
    }
}

// Get all users
$stmt = $db->prepare("SELECT * FROM users ORDER BY id ASC");
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - CattlePro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-[#F0F2F5] font-sans flex overflow-hidden w-full h-screen relative text-gray-800">

<?php include 'components/sidebar.php'; ?>

<div class="flex-1 h-screen overflow-y-auto w-full transition-all duration-300 relative flex flex-col bg-[#F0F2F5]" id="main-content">
    
    <header class="bg-white shadow-sm border-b border-gray-200 p-4 px-6 flex justify-between items-center sticky top-0 z-10 w-full">
        <h2 class="text-xl font-bold text-slate-800"><i class="fas fa-users-cog text-amber-500 mr-2"></i>Manajemen User</h2>
        <div class="flex items-center gap-4 ml-auto">
            <?php include 'components/profile_dropdown.php'; ?>
        </div>
    </header>

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

        <div class="flex justify-end">
            <button onclick="document.getElementById('modal-user').classList.remove('hidden')" class="bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold py-2.5 px-5 rounded-xl text-sm hover:from-amber-600 hover:to-orange-600 transition-all shadow-lg shadow-amber-200 flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Tambah User
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">No</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Nama</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Email</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider">Role</th>
                            <th class="p-4 font-bold text-gray-500 text-xs uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php $no = 1; foreach ($all_users as $u): ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4 text-gray-400 font-mono text-xs"><?php echo $no++; ?></td>
                            <td class="p-4 font-bold text-slate-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                        <?php echo strtoupper(substr($u['nama'], 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($u['nama']); ?>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td class="p-4"><span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo $u['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td class="p-4 text-center">
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?hapus_user=<?php echo $u['id']; ?>" onclick="return confirm('Yakin hapus user ini?')" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 inline-flex items-center justify-center hover:bg-red-100 transition" title="Hapus">
                                        <i class="fas fa-trash text-xs"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-gray-300 italic">Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah User -->
<div id="modal-user" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-user-plus text-amber-500 mr-2"></i>Tambah User Baru</h3>
            <button onclick="document.getElementById('modal-user').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Role</label>
                <select name="role" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
                    <option value="admin">Admin</option>
                    <option value="petugas">Petugas</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-user').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Batal</button>
                <button type="submit" name="tambah_user" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-sm hover:from-amber-600 hover:to-orange-600 transition shadow-lg shadow-amber-200">Simpan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
