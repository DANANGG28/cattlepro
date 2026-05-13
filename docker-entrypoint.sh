#!/bin/bash
set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"
PHP_CONFIG_FILE="/var/www/html/config/firebase_env.php"
ENV_DEBUG_FILE="/var/www/html/config/env_debug.txt"

mkdir -p /var/www/html/config

# Dump semua env vars dari Dokploy untuk kita debug
env > "$ENV_DEBUG_FILE"
echo "B64 Length: ${#FIREBASE_CREDENTIALS_B64}" >> "$ENV_DEBUG_FILE"

# ── Prioritas 1: Base64-encoded full JSON ──────────────────────────────
if [ -n "$FIREBASE_CREDENTIALS_B64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_B64" | base64 -d > "$CREDENTIALS_FILE"
    echo "[CattlePro] Credentials dimuat dari FIREBASE_CREDENTIALS_B64."
    
    # Gunakan PHP untuk decode JSON dan buat config file dengan aman
    php -r '
        $json = json_decode(file_get_contents("'$CREDENTIALS_FILE'"), true);
        $content = "<?php\n";
        if($json && isset($json["private_key"])){
            $content .= "define(\"FB_CLIENT_EMAIL\", \"" . $json["client_email"] . "\");\n";
            $content .= "define(\"FB_PRIVATE_KEY\", " . var_export($json["private_key"], true) . ");\n";
            $content .= "define(\"FB_PROJECT_ID\", \"" . $json["project_id"] . "\");\n";
        } else {
            $content .= "// JSON FAILED TO DECODE OR MISSING PRIVATE_KEY\n";
        }
        file_put_contents("'$PHP_CONFIG_FILE'", $content);
    '
else
    echo "<?php // FIREBASE_CREDENTIALS_B64 KOSONG DARI DOKPLOY" > "$PHP_CONFIG_FILE"
fi

chown -R www-data:www-data /var/www/html/config
chmod -R 777 /var/www/html/config || true
echo "[CattlePro] Entrypoint selesai. Memulai Apache..."

exec "$@"
