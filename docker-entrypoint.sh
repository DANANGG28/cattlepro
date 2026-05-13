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
php -r '
    $b64 = getenv("FIREBASE_CREDENTIALS_B64");
    $raw = getenv("FIREBASE_CREDENTIALS");
    
    $jsonString = "";
    if (!empty($b64)) {
        $b64 = trim($b64, " \t\n\r\0\x0B<>");
        $jsonString = base64_decode($b64);
    } elseif (!empty($raw)) {
        $jsonString = trim($raw, " \t\n\r\0\x0B<>");
    }

    $content = "<?php\n";
    if (!empty($jsonString)) {
        $json = json_decode($jsonString, true);
        
        $clientEmail = "";
        $privateKey = "";
        $projectId = "";

        if ($json && isset($json["private_key"])) {
            $clientEmail = $json["client_email"] ?? "";
            $privateKey = $json["private_key"] ?? "";
            $projectId = $json["project_id"] ?? "";
        } else {
            // BYPASS JSON DECODE FAILURES WITH REGEX
            if (preg_match("/\"client_email\"\s*:\s*\"([^\"]+)\"/", $jsonString, $matches)) {
                $clientEmail = $matches[1];
            }
            if (preg_match("/\"private_key\"\s*:\s*\"([^\"]+)\"/", $jsonString, $matches)) {
                $privateKey = str_replace("\\n", "\n", $matches[1]);
            }
            if (preg_match("/\"project_id\"\s*:\s*\"([^\"]+)\"/", $jsonString, $matches)) {
                $projectId = $matches[1];
            }
        }

        if (!empty($privateKey) && !empty($clientEmail)) {
            $content .= "define(\"FB_CLIENT_EMAIL\", \"" . $clientEmail . "\");\n";
            $content .= "define(\"FB_PRIVATE_KEY\", " . var_export($privateKey, true) . ");\n";
            $content .= "define(\"FB_PROJECT_ID\", \"" . $projectId . "\");\n";
            echo "[CattlePro] Config PHP berhasil di-generate (Regex/JSON mode)!\n";
        } else {
            $content .= "// GAGAL MENEMUKAN KREDENSIAL VIA REGEX MAUPUN JSON\n";
            $content .= "// Snippet: " . var_export(substr($jsonString, 0, 200), true) . "\n";
        }
    } else {
        $content .= "/* FIREBASE CREDENTIALS KOSONG */\n";
    }
    
    file_put_contents("'$PHP_CONFIG_FILE'", $content);
'

chown -R www-data:www-data /var/www/html/config
chmod -R 777 /var/www/html/config || true
echo "[CattlePro] Entrypoint selesai. Memulai Apache..."

exec "$@"
