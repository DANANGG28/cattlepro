#!/bin/bash
set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"
PHP_CONFIG_FILE="/var/www/html/config/firebase_env.php"

mkdir -p /var/www/html/config

# ── Prioritas 1: Base64-encoded full JSON ──────────────────────────────
if [ -n "$FIREBASE_CREDENTIALS_B64" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS_B64" | base64 -d > "$CREDENTIALS_FILE"
    echo "[CattlePro] Credentials dimuat dari FIREBASE_CREDENTIALS_B64."

# ── Prioritas 2: Raw JSON ──────────────────────────────────────────────
elif [ -n "$FIREBASE_CREDENTIALS" ]; then
    printf '%s' "$FIREBASE_CREDENTIALS" > "$CREDENTIALS_FILE"
    echo "[CattlePro] Credentials dimuat dari FIREBASE_CREDENTIALS."
fi

# ── Selalu: Tulis PHP config dari env vars individual ─────────────────
# Ini bypass masalah Apache tidak passing env vars ke PHP
cat > "$PHP_CONFIG_FILE" <<PHPEOF
<?php
// AUTO-GENERATED oleh docker-entrypoint.sh — JANGAN EDIT MANUAL
define('FB_CLIENT_EMAIL', '${FIREBASE_CLIENT_EMAIL}');
define('FB_PRIVATE_KEY',  '${FIREBASE_PRIVATE_KEY}');
define('FB_PROJECT_ID',   '${FIREBASE_PROJECT_ID:-cattlepro-93c0b}');
PHPEOF

chown -R www-data:www-data /var/www/html/config
chmod 600 "$PHP_CONFIG_FILE"
echo "[CattlePro] PHP firebase config ditulis ke $PHP_CONFIG_FILE"

exec "$@"
