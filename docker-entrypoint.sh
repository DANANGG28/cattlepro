#!/bin/bash
# ============================================================
# CattlePro Docker Entrypoint
# Inject Firebase credentials dari environment variable
# sebelum Apache dijalankan
# ============================================================

set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"

# Jika FIREBASE_CREDENTIALS env var di-set (dari Dokploy),
# tulis isinya ke file JSON yang dibutuhkan Database.php
if [ -n "$FIREBASE_CREDENTIALS" ]; then
    echo "$FIREBASE_CREDENTIALS" > "$CREDENTIALS_FILE"
    chown www-data:www-data "$CREDENTIALS_FILE"
    chmod 600 "$CREDENTIALS_FILE"
    echo "[CattlePro] Firebase credentials berhasil dimuat dari environment variable."
else
    echo "[CattlePro] WARNING: FIREBASE_CREDENTIALS tidak di-set. Firebase tidak akan bisa terhubung."
fi

# Jalankan command utama (apache2-foreground)
exec "$@"
