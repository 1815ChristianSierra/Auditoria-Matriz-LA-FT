FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.3-apache-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libxml2-dev libzip-dev unzip \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip opcache dom \
    && (a2dismod mpm_event 2>/dev/null || true) \
    && (a2dismod mpm_worker 2>/dev/null || true) \
    && a2enmod mpm_prefork rewrite remoteip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
COPY --from=assets /app/public/build ./public/build
COPY railway/apache.conf /etc/apache2/sites-available/000-default.conf
CMD ["sh", "railway/start.sh"]
