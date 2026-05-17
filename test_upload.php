<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'controllers/ExcelReader.php';

echo "<h2>Test Upload & ExcelReader</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>File Upload Info:</h3>";
    echo "<pre>";
    print_r($_FILES['test_file']);
    echo "</pre>";
    
    $file = $_FILES['test_file']['tmp_name'];
    $file_name = $_FILES['test_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    echo "<h3>Parsed Info:</h3>";
    echo "Original filename: $file_name<br>";
    echo "Extension: $file_ext<br>";
    echo "Temp path: $file<br>";
    echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "<br>";
    echo "File size: " . filesize($file) . " bytes<br>";
    
    echo "<h3>Testing ExcelReader:</h3>";
    
    try {
        echo "Creating ExcelReader...<br>";
        $reader = new ExcelReader($file, $file_name);
        echo "✅ ExcelReader created<br>";
        
        echo "Validating file...<br>";
        $errors = $reader->validate();
        if (!empty($errors)) {
            echo "❌ Validation errors:<br>";
            foreach ($errors as $error) {
                echo "- $error<br>";
            }
        } else {
            echo "✅ Validation passed<br>";
        }
        
        echo "Reading file...<br>";
        $data = $reader->read();
        echo "✅ File read successfully!<br>";
        echo "Rows found: " . count($data) . "<br>";
        
        echo "<h3>First 3 rows:</h3>";
        echo "<pre>";
        print_r(array_slice($data, 0, 3));
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>Upload Test File:</h3>
    <input type="file" name="test_file" accept=".csv,.xlsx" required>
    <button type="submit">Test Upload</button>
</form>

<style>
    body { font-family: monospace; padding: 20px; }
    h2, h3 { color: #333; }
    pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
</style>
