#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Source unique Docker : backend/.env.docker
if [ ! -f .env.docker ]; then
  echo "ERREUR: fichier .env.docker introuvable dans /var/www/html" >&2
  exit 1
fi

echo ">> Configuration depuis .env.docker"
cp -f .env.docker .env

# Surcharge optionnelle (docker-compose environment / production)
set_env() {
  key="$1"
  val="$2"
  if [ -z "$val" ]; then
    return 0
  fi
  if grep -q "^${key}=" .env 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${val}|" .env
  else
    echo "${key}=${val}" >> .env
  fi
}

set_env APP_NAME "${APP_NAME}"
set_env APP_ENV "${APP_ENV}"
set_env APP_KEY "${APP_KEY}"
set_env APP_DEBUG "${APP_DEBUG}"
set_env APP_URL "${APP_URL}"
set_env DB_CONNECTION "${DB_CONNECTION}"
set_env DB_HOST "${DB_HOST}"
set_env DB_PORT "${DB_PORT}"
set_env DB_DATABASE "${DB_DATABASE}"
set_env DB_USERNAME "${DB_USERNAME}"
set_env DB_PASSWORD "${DB_PASSWORD}"
set_env FRONTEND_URL "${FRONTEND_URL}"
set_env SANCTUM_STATEFUL_DOMAINS "${SANCTUM_STATEFUL_DOMAINS}"
set_env LOG_CHANNEL "${LOG_CHANNEL}"

php artisan config:clear --no-interaction 2>/dev/null || true

DB_HOST_VAL="${DB_HOST:-db}"
DB_USER_VAL="${DB_USERNAME:-mivoorh}"
DB_PASS_VAL="${DB_PASSWORD:-mivoorh}"

echo ">> Attente de MySQL (${DB_HOST_VAL})..."
until mysqladmin ping -h"${DB_HOST_VAL}" -u"${DB_USER_VAL}" -p"${DB_PASS_VAL}" --silent 2>/dev/null; do
  sleep 2
done

echo ">> Migrations et données de démo..."
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

echo ">> API Laravel sur :8000 (DB_HOST=${DB_HOST_VAL})"
exec php artisan serve --host=0.0.0.0 --port=8000
