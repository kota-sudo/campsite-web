FROM php:8.3-cli

# 必要な拡張とツール
RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# 依存インストール
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

# アプリ全体コピー
COPY . .

# フロントエンドビルド
RUN npm run build

# ストレージリンク・権限
RUN mkdir -p /var/data storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# .env がない場合は .env.example をコピー
RUN cp -n .env.example .env || true

# 起動スクリプト
COPY docker-start.sh /docker-start.sh
RUN chmod +x /docker-start.sh

EXPOSE 8000

CMD ["/docker-start.sh"]
