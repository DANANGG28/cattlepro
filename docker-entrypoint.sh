#!/bin/bash
set -e

CREDENTIALS_FILE="/var/www/html/cattlepro-93c0b-firebase-adminsdk-fbsvc-cf6c373fee.json"

# Prioritas 1: Base64-encoded credentials (lebih aman untuk env var)
if [ -n "$FIREBASE_CREDENTIALS_B64" ]; then
    echo "$FIREBASE_CREDENTIALS_B64" | base64 -d > "$CREDENTIALS_FILE"
    chown www-data:www-data "$CREDENTIALS_FILE"
    chmod 600 "$CREDENTIALS_FILE"
    echo "[CattlePro] Firebase credentials dimuat dari FIREBASE_CREDENTIALS_B64."

# Prioritas 2: Raw JSON (fallback)
elif [ -n "$FIREBASE_CREDENTIALS" ]; then
    echo "$FIREBASE_CREDENTIALS" > "$CREDENTIALS_FILE"
    chown www-data:www-data "$CREDENTIALS_FILE"
    chmod 600 "$CREDENTIALS_FILE"
    echo "[CattlePro] Firebase credentials dimuat dari FIREBASE_CREDENTIALS."

else
    echo "[CattlePro] WARNING: Tidak ada Firebase credentials. Set FIREBASE_CREDENTIALS_B64 di Dokploy."
fi

exec "$@"
