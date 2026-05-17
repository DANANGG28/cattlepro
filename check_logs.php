<?php
// Check error logs
header('Content-Type: text/plain');

echo "=== PHP Error Log (Last 50 lines) ===\n\n";

$log_files = [
    '/var/log/apache2/error.log',
    '/var/log/php8.2-fpm.log',
    '/var/log/php8.1-fpm.log',
    '/var/log/nginx/error.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "Found: $log_file\n";
        echo str_repeat("=", 80) . "\n";
        $lines = file($log_file);
        $last_lines = array_slice($lines, -50);
        echo implode("", $last_lines);
        echo "\n\n";
    }
}

echo "=== PHP Info ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
