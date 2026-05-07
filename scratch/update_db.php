<?php
require_once __DIR__ . '/../controllers/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $sql = "ALTER TABLE users 
            ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL,
            ADD COLUMN reset_token_expires_at DATETIME DEFAULT NULL";
    $db->exec($sql);
    echo "Table 'users' updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
