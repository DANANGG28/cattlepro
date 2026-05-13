#!/bin/bash
set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"
PHP_CONFIG_FILE="/var/www/html/config/firebase_env.php"
ENV_DEBUG_FILE="/var/www/html/config/env_debug.txt"

mkdir -p /var/www/html/config

# Dump semua env vars dari Dokploy untuk kita debug
env > "$ENV_DEBUG_FILE"
echo "B64 Length: ${#FIREBASE_CREDENTIALS_B64}" >> "$ENV_DEBUG_FILE"

# ── Unified Config Generator ──────────────────────────────────────────────
# Gunakan PHP untuk membaca env vars, membersihkan karakter aneh (< >), dan men-decode JSON
php -r '
    $b64 = getenv("FIREBASE_CREDENTIALS_B64");
    $raw = getenv("FIREBASE_CREDENTIALS");
    
    $jsonString = "";
    if (!empty($b64)) {
        // Bersihkan < dan > lalu decode
        $b64 = trim($b64, "<>");
        $jsonString = base64_decode($b64);
    } elseif (!empty($raw)) {
        // Bersihkan < dan > dari string JSON
        $jsonString = trim($raw, "<>");
    }

    $content = "<?php\n";
    if (!empty($jsonString)) {
        $json = json_decode($jsonString, true);
        if ($json && isset($json["private_key"])) {
            $content .= "define(\"FB_CLIENT_EMAIL\", \"" . $json["client_email"] . "\");\n";
            // var_export aman untuk string multi-baris
            $content .= "define(\"FB_PRIVATE_KEY\", " . var_export($json["private_key"], true) . ");\n";
            $content .= "define(\"FB_PROJECT_ID\", \"" . $json["project_id"] . "\");\n";
            echo "[CattlePro] Config PHP berhasil di-generate dari JSON!\n";
        } else {
            $content .= "// JSON FAILED TO DECODE OR MISSING PRIVATE_KEY\n";
            echo "[CattlePro] Gagal decode JSON. Error: " . json_last_error_msg() . "\n";
        }
    } else {
        $content .= "// FIREBASE CREDENTIALS KOSONG\n";
        echo "[CattlePro] WARNING: FIREBASE_CREDENTIALS tidak ditemukan di env!\n";
    }
    
    file_put_contents("'$PHP_CONFIG_FILE'", $content);
'

chown -R www-data:www-data /var/www/html/config
chmod -R 777 /var/www/html/config || true
echo "[CattlePro] Entrypoint selesai. Memulai Apache..."

exec "$@"
