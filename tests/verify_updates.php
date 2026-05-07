<?php
/**
 * CattlePro Terminology & UI Test Suite
 * Memastikan semua update istilah formal dan warna sudah diterapkan.
 */

$base_path = dirname(__DIR__) . '/';
$files_to_check = [
    'pages/detail_sapi.php',
    'pages/dashboard.php',
    'pages/sapi.php',
    'pages/export_sapi.php'
];

$requirements = [
    'terms' => [
        'Inseminasi Buatan',
        'Pemeriksaan Kebuntingan',
        'Hari Perkiraan Lahir'
    ],
    'colors' => [
        'bg-blue-100',
        'text-blue-700'
    ]
];

echo "\033[1;34mCattlePro Update Verification Tool\033[0m\n";
echo "====================================\n\n";

$errors = 0;

foreach ($files_to_check as $file) {
    $path = $base_path . $file;
    if (!file_exists($path)) {
        echo "\033[1;33m[SKIP]\033[0m $file (File tidak ditemukan)\n";
        continue;
    }

    $content = file_get_contents($path);
    echo "\033[1;32m[CHECK]\033[0m $file\n";

    // 1. Cek Istilah Formal
    foreach ($requirements['terms'] as $term) {
        if (strpos($content, $term) !== false) {
            echo "  \033[0;32m✓\033[0m Terdeteksi: $term\n";
        } else {
            echo "  \033[0;31m✗\033[0m MISSING: $term\n";
            $errors++;
        }
    }

    // 2. Cek Warna Biru untuk status (khusus di sapi.php dan detail_sapi.php)
    if (strpos($file, 'sapi.php') !== false) {
        foreach ($requirements['colors'] as $color) {
            if (strpos($content, $color) !== false) {
                echo "  \033[0;32m✓\033[0m Warna: $color ditemukan\n";
            } else {
                echo "  \033[0;31m✗\033[0m Warna: $color tidak ditemukan!\n";
                $errors++;
            }
        }
    }
    echo "\n";
}

if ($errors === 0) {
    echo "\033[1;32mPENGUJIAN BERHASIL!\033[0m Semua istilah formal dan warna sudah sesuai standar dosen.\n";
    exit(0);
} else {
    echo "\033[1;31mPENGUJIAN GAGAL!\033[0m Ditemukan $errors kesalahan yang belum diperbaiki.\n";
    exit(1);
}
