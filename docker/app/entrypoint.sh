#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/html"
cd "$APP_DIR"

echo "==> Boot APP | $(date -Iseconds)"

mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true


if [ ! -f vendor/autoload.php ]; then
  echo "==> composer install"
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist
else
  echo "==> composer dump-autoload -o"
  COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload -o
fi


if [ ! -d node_modules ]; then
  echo "==> yarn install"
  yarn install --frozen-lockfile || yarn install
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  echo "==> php artisan key:generate"
  php artisan key:generate --force || true
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"
DB_WAIT_TIMEOUT="${DB_WAIT_TIMEOUT:-120}"

echo "==> Esperando a DB ${DB_HOST}:${DB_PORT} (${DB_WAIT_TIMEOUT}s máx)..."
ok=""
for i in $(seq 1 "${DB_WAIT_TIMEOUT}"); do
  if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1" >/dev/null 2>&1; then
    ok="yes"; break
  fi
  sleep 2
done
if [ -z "${ok}" ]; then
  echo "ERROR: DB no disponible tras ${DB_WAIT_TIMEOUT}s"; exit 1
fi
echo "==> DB OK"

if [ "${RUN_MIGRATIONS_ON_BOOT:-false}" = "true" ]; then
  echo "==> php artisan migrate"
  php artisan migrate
fi

SEED_MARKER="$APP_DIR/storage/app/.seeded"
echo "Vamos a migrar"

if [ "${RUN_SEED_ON_BOOT:-false}" = "true" ] && [ ! -f "$SEED_MARKER" ]; then
  echo "==> php artisan db:seed"
  php artisan db:seed 
  touch "$SEED_MARKER"
fi

exec "$@"
