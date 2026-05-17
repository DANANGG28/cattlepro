<?php
// Test PhpSpreadsheet installation

echo "<h2>Testing PhpSpreadsheet Installation</h2>";

// Check if vendor autoload exists
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php exists<br>";
    require_once 'vendor/autoload.php';
} else {
    echo "❌ vendor/autoload.php NOT FOUND<br>";
    die();
}

// Check if PhpSpreadsheet class exists
if (class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
    echo "✅ PhpOffice\\PhpSpreadsheet\\IOFactory class exists<br>";
} else {
    echo "❌ PhpOffice\\PhpSpreadsheet\\IOFactory class NOT FOUND<br>";
    die();
}

// Check if ExcelReader exists
if (file_exists('controllers/ExcelReader.php')) {
    echo "✅ ExcelReader.php exists<br>";
    require_once 'controllers/ExcelReader.php';
} else {
    echo "❌ ExcelReader.php NOT FOUND<br>";
}

// Test reading a sample XLS file
echo "<br><h3>Test File Upload:</h3>";
echo "Upload a test XLS file to see if it can be read.<br>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file']['tmp_name'];
    $file_name = $_FILES['test_file']['name'];
    
    echo "<br><strong>File uploaded:</strong> $file_name<br>";
    echo "<strong>Temp path:</strong> $file<br>";
    echo "<strong>File size:</strong> " . filesize($file) . " bytes<br>";
    
    try {
        $reader = new ExcelReader($file);
        echo "✅ ExcelReader created<br>";
        
        $data = $reader->read();
        echo "✅ File read successfully!<br>";
        echo "<strong>Rows found:</strong> " . count($data) . "<br>";
        
        echo "<br><strong>First 5 rows:</strong><br>";
        echo "<pre>";
        print_r(array_slice($data, 0, 5));
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" accept=".xls,.xlsx,.csv">
    <button type="submit">Test Upload</button>
</form>
