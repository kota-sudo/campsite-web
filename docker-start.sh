#!/bin/bash
set -e

# SQLite ファイルを永続ボリュームに配置
DB_PATH="/var/data/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
fi

# 環境変数を設定
export APP_KEY="${APP_KEY}"
export APP_URL="${APP_URL:-http://localhost:8000}"
export DB_DATABASE="$DB_PATH"

# キャッシュ生成
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# マイグレーション & シーダー
php artisan migrate --force
php artisan db:seed --force 2>/dev/null || true

# ストレージリンク
php artisan storage:link 2>/dev/null || true

# サーバー起動
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
