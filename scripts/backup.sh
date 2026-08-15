#!/bin/bash
# ============================================================
# PMS database backup script (Phase 7)
#
# Usage (manual):
#   bash scripts/backup.sh
#
# Usage (scheduled, Linux/macOS cron — runs daily at 2 AM):
#   crontab -e
#   0 2 * * * /bin/bash /path/to/pms/scripts/backup.sh >> /path/to/pms/storage/logs/backup.log 2>&1
#
# On Windows/XAMPP, use Task Scheduler to run this via Git Bash / WSL,
# or replace with a .bat file calling the same mysqldump command:
#   "C:\xampp\mysql\bin\mysqldump.exe" -u root pms_db > backup.sql
#
# Keeps the last 14 daily backups and deletes older ones automatically.
# ============================================================

set -e

DB_NAME="pms_db"
DB_USER="root"
DB_PASS=""                     # leave empty for default XAMPP root
BACKUP_DIR="$(dirname "$0")/../storage/backups"
KEEP_DAYS=14

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
OUT_FILE="$BACKUP_DIR/pms_db_${TIMESTAMP}.sql.gz"

if [ -z "$DB_PASS" ]; then
    mysqldump -u "$DB_USER" "$DB_NAME" | gzip > "$OUT_FILE"
else
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$OUT_FILE"
fi

echo "Backup written to $OUT_FILE"

# Prune backups older than KEEP_DAYS
find "$BACKUP_DIR" -name "pms_db_*.sql.gz" -mtime +"$KEEP_DAYS" -delete

echo "Pruned backups older than $KEEP_DAYS days."
