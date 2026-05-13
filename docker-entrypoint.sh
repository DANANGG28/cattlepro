#!/bin/bash
set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"
PHP_CONFIG_FILE="/var/www/html/config/firebase_env.php"

mkdir -p /var/www/html/config

# ── Prioritas 1: Base64-encoded full JSON ──────────────────────────────
if [ -n "$FIREBASE_CREDENTIALS_B64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_B64" | base64 -d > "$CREDENTIALS_FILE"
    echo "[CattlePro] Credentials dimuat dari FIREBASE_CREDENTIALS_B64."
    
    # Gunakan PHP untuk decode JSON dan buat config file dengan aman
    php -r '
        $json = json_decode(file_get_contents("'$CREDENTIALS_FILE'"), true);
        if($json && isset($json["private_key"])){
            $content = "<?php\n";
            $content .= "define(\"FB_CLIENT_EMAIL\", \"" . $json["client_email"] . "\");\n";
            // var_export handle multiline string private key dengan aman
            $content .= "define(\"FB_PRIVATE_KEY\", " . var_export($json["private_key"], true) . ");\n";
            $content .= "define(\"FB_PROJECT_ID\", \"" . $json["project_id"] . "\");\n";
            file_put_contents("'$PHP_CONFIG_FILE'", $content);
        }
    '
fi

chown -R www-data:www-data /var/www/html/config
chmod 600 "$PHP_CONFIG_FILE" || true
echo "[CattlePro] Entrypoint selesai. Memulai Apache..."

exec "$@"
