<?php
header('Content-Type: text/plain');

echo "=== PHP Syntax Check ===\n\n";

$files = [
    'pages/import_page.php',
    'controllers/ExcelReader.php',
    'controllers/SimpleXLSX.php',
    'controllers/main.php'
];

foreach ($files as $file) {
    echo "Checking: $file\n";
    
    if (!file_exists($file)) {
        echo "  ❌ File not found\n\n";
        continue;
    }
    
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "  ✅ No syntax errors\n";
    } else {
        echo "  ❌ Syntax error:\n";
        foreach ($output as $line) {
            echo "     $line\n";
        }
    }
    echo "\n";
}

echo "\n=== Trying to include files ===\n\n";

try {
    echo "Including ExcelReader.php...\n";
    require_once 'controllers/ExcelReader.php';
    echo "✅ ExcelReader.php loaded\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

try {
    echo "Including SimpleXLSX.php...\n";
    require_once 'controllers/SimpleXLSX.php';
    echo "✅ SimpleXLSX.php loaded\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Class Check ===\n\n";
echo "ExcelReader exists: " . (class_exists('ExcelReader') ? 'YES' : 'NO') . "\n";
echo "SimpleXLSX exists: " . (class_exists('SimpleXLSX') ? 'YES' : 'NO') . "\n";
