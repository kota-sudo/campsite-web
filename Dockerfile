# Laravel + Vite（Render 等での利用想定）
# PHP 8.3 / composer install / npm install / npm run build / 起動時に config・route・view キャッシュ → artisan serve

FROM php:8.3-cli-bookworm

ARG DEBIAN_FRONTEND=noninteractive

# OSパッケージ + PHP拡張 + Node.js
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    ca-certificates \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        intl \
        gd \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Node.js 22（Vite 8 系の要件に合わせる）
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer（アプリ本体の COPY 前に依存だけ解決）
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# npm
COPY package.json package-lock.json ./
RUN npm install --ignore-scripts

# アプリケーション一式
COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi --no-interaction || true

# Vite 本番ビルド（public/build を生成）
RUN npm run build

# Laravel が書き込むディレクトリ + 実行ユーザー用に権限調整
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# 起動スクリプト: SQLite 作成 → migrate → config / route / view キャッシュ → serve
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV PORT=10000

EXPOSE 10000

USER www-data

ENTRYPOINT ["/entrypoint.sh"]
