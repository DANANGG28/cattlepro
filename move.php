<?php
$files = ['dashboard.php', 'sapi.php', 'detail_sapi.php', 'export_sapi.php', 'prediksi.php', 'users.php', 'logout.php'];
if (!is_dir('pages')) {
    if (!mkdir('pages')) {
        echo "Failed to create directory 'pages'.<br>";
    }
}
foreach ($files as $file) {
    if (file_exists($file)) {
        if (!rename($file, 'pages/' . $file)) {
            $err = error_get_last();
            echo "Failed to move $file: " . $err['message'] . "<br>";
        } else {
            echo "Successfully moved $file.<br>";
        }
    } else {
        echo "$file does not exist.<br>";
    }
}
?>
