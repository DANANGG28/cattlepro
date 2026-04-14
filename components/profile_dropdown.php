<?php
// Profile Dropdown Component
?>
<div class="relative">
    <button onclick="toggleProfileDropdown()" class="flex items-center gap-3 hover:bg-gray-50 rounded-xl px-3 py-2 transition" id="profile-btn">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold text-gray-800"><?php echo isset($current_user['nama']) ? htmlspecialchars($current_user['nama']) : 'Super Admin'; ?></p>
            <p class="text-[10px] text-gray-400 uppercase font-semibold tracking-wide"><?php echo isset($current_user['role']) ? htmlspecialchars(strtoupper($current_user['role'])) : 'SUPERADMIN'; ?></p>
        </div>
        <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow">
            <?php echo isset($current_user['nama']) ? strtoupper(substr($current_user['nama'], 0, 1)) : 'S'; ?>
        </div>
    </button>
    
    <div id="profile-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
        <div class="px-4 py-2 border-b border-gray-50">
            <p class="text-sm font-bold text-gray-800"><?php echo isset($current_user['nama']) ? htmlspecialchars($current_user['nama']) : 'Super Admin'; ?></p>
            <p class="text-xs text-gray-400"><?php echo isset($current_user['email']) ? htmlspecialchars($current_user['email']) : ''; ?></p>
        </div>
        <a href="logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition">
            <i class="fas fa-sign-out-alt"></i>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</div>

<script>
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profile-dropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const btn = document.getElementById('profile-btn');
    const dropdown = document.getElementById('profile-dropdown');
    if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
