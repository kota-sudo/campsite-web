#!/bin/sh
set -e
cd /var/www/html

# 本番で @vite が Vite dev サーバー（127.0.0.1:5173）を指さないようにする
rm -f public/hot

if [ ! -f .env ]; then
    cp .env.example .env
fi

# SQLite: ファイルが無いとセッション等で QueryException になる
mkdir -p database
touch database/database.sqlite
if [ -n "${DB_DATABASE:-}" ] && [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    case "$DB_DATABASE" in
        /*)
            mkdir -p "$(dirname "$DB_DATABASE")"
            touch "$DB_DATABASE"
            ;;
    esac
fi

php artisan migrate --force --no-interaction

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
