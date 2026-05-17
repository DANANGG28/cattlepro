<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple PhpSpreadsheet Test</h2>";

// Test 1: Autoload
echo "<h3>Test 1: Autoload</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php found<br>";
    require_once 'vendor/autoload.php';
    echo "✅ Autoload loaded<br>";
} else {
    echo "❌ vendor/autoload.php NOT FOUND<br>";
    die();
}

// Test 2: Class exists
echo "<h3>Test 2: Class Check</h3>";
if (class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
    echo "✅ IOFactory class exists<br>";
} else {
    echo "❌ IOFactory class NOT FOUND<br>";
    echo "Loaded classes:<br><pre>";
    print_r(get_declared_classes());
    echo "</pre>";
    die();
}

// Test 3: Try to load a file
echo "<h3>Test 3: File Loading</h3>";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file']['tmp_name'];
    $file_name = $_FILES['test_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    echo "File: $file_name<br>";
    echo "Extension: $file_ext<br>";
    echo "Size: " . filesize($file) . " bytes<br>";
    
    try {
        echo "Loading file...<br>";
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        echo "✅ File loaded!<br>";
        
        $worksheet = $spreadsheet->getActiveSheet();
        echo "✅ Worksheet obtained!<br>";
        
        $data = $worksheet->toArray();
        echo "✅ Data converted to array!<br>";
        echo "Rows: " . count($data) . "<br>";
        
        echo "<h4>First 3 rows:</h4><pre>";
        print_r(array_slice($data, 0, 3));
        echo "</pre>";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" accept=".xls,.xlsx,.csv" required>
    <button type="submit">Upload & Test</button>
</form>
