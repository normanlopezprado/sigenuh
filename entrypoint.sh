#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/html"
VENDOR_DIR="$APP_DIR/vendor"
NODE_MODULES_DIR="$APP_DIR/node_modules"
DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-3306}"

# === NUEVO: control de seeding ===
SEED_ON_BOOT="${SEED_ON_BOOT:-false}"
SEED_MARKER="$APP_DIR/storage/app/.seeded"

cd "$APP_DIR"

# Ajustar permisos para Laravel
if [ -d storage ] && [ -d bootstrap/cache ]; then
  chown -R www-data:www-data storage bootstrap/cache || true
  chmod -R ug+rw storage bootstrap/cache || true
fi

# Instalar dependencias de PHP si no existen
if [ ! -f "$VENDOR_DIR/autoload.php" ]; then
  composer install --no-interaction --prefer-dist
fi

# Instalar dependencias de Node si no existen
if [ ! -d "$NODE_MODULES_DIR" ] || [ -z "$(ls -A "$NODE_MODULES_DIR" 2>/dev/null)" ]; then
  if [ -f yarn.lock ]; then
    yarn install --frozen-lockfile
  else
    yarn install
  fi
fi

# Generar clave de la aplicación si es necesario
if [ -f artisan ]; then
  CURRENT_KEY=$(php -r "require 'vendor/autoload.php'; require 'bootstrap/app.php'; echo env('APP_KEY');" 2>/dev/null || true)
  if [ -z "$CURRENT_KEY" ]; then
    php artisan key:generate --force
  fi
fi

# Esperar a que la base de datos esté disponible si se configuró
if [ -n "$DB_HOST" ]; then
  until mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" --silent; do
    sleep 2
  done

  if [ -f artisan ]; then
    php artisan migrate --force --no-interaction || true

    if [ "$SEED_ON_BOOT" = "true" ]; then
      mkdir -p storage/app
      if [ ! -f "$SEED_MARKER" ]; then
        echo "==> Ejecutando seeders (db:seed)"
        if php artisan db:seed --force --no-interaction; then
          touch "$SEED_MARKER"
          echo "==> Seeders completados (marcador creado en storage/app/.seeded)"
        else
          echo "==> ADVERTENCIA: falló db:seed; no se crea el marcador"
        fi
      else
        echo "==> Seeders ya ejecutados anteriormente (se detectó $SEED_MARKER)"
      fi
    fi
    
  fi
fi

# Compilar assets si no existe la carpeta de build
if [ -f package.json ] && [ ! -d public/build ]; then
  yarn build || true
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
