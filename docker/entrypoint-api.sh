#!/bin/sh
set -e
cd /var/www/html

# Dossiers Laravel inscriptibles (sessions, logs, cache)
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ ! -f .env ]; then
  cp .env.example .env
fi

echo ">> Attente de MySQL (${DB_HOST})..."
until mysqladmin ping -h"${DB_HOST}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent 2>/dev/null; do
  sleep 2
done

echo ">> Migrations et données de démo..."
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

echo ">> API Laravel sur :8000"
exec php artisan serve --host=0.0.0.0 --port=8000
