<?php
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../models/Sapi.php';

$database = new Database();
$db = $database->getConnection();

$sapi = new Sapi($db);

// Get current user info for components
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
