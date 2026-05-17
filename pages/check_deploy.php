<?php
header('Content-Type: text/plain');

echo "=== Deployment Check ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current File: " . __FILE__ . "\n\n";

echo "=== File Existence Check ===\n\n";

$files = [
    '../controllers/main.php',
    '../controllers/ExcelReader.php',
    '../controllers/SimpleXLSX.php',
    '../controllers/Database.php'
];

foreach ($files as $file) {
    $full_path = __DIR__ . '/' . $file;
    $exists = file_exists($full_path);
    echo "$file: " . ($exists ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
    if ($exists) {
        echo "  Size: " . filesize($full_path) . " bytes\n";
        echo "  Modified: " . date('Y-m-d H:i:s', filemtime($full_path)) . "\n";
    }
    echo "\n";
}

echo "=== Try Loading Files ===\n\n";

try {
    require_once '../controllers/main.php';
    echo "✅ main.php loaded\n";
} catch (Throwable $e) {
    echo "❌ main.php error: " . $e->getMessage() . "\n";
}

try {
    require_once '../controllers/ExcelReader.php';
    echo "✅ ExcelReader.php loaded\n";
    echo "   ExcelReader class exists: " . (class_exists('ExcelReader') ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "❌ ExcelReader.php error: " . $e->getMessage() . "\n";
}

echo "\n=== Session Check ===\n\n";
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO') . "\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session ID: " . session_id() . "\n";
    echo "User logged in: " . (isset($_SESSION['user_id']) ? 'YES (ID: ' . $_SESSION['user_id'] . ')' : 'NO') . "\n";
}
