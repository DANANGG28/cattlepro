<?php
// TEMP DEBUG - HAPUS SETELAH SELESAI
$keyFile = dirname(__FILE__) . '/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json';
echo "<h3>Raw file content (first 200 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr(file_get_contents($keyFile), 0, 200)) . "</pre>";
echo "<h3>File size: " . filesize($keyFile) . " bytes</h3>";
?>
