# ─────────────────────────────────────────────
# Stage 1: Build front-end assets (Vue 3 + Vite)
# ─────────────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY laravel-dashboard/package.json laravel-dashboard/package-lock.json ./
RUN npm ci

COPY laravel-dashboard/ .
RUN npm run build

# ─────────────────────────────────────────────
# Stage 2: Install PHP dependencies
# ─────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY laravel-dashboard/composer.json laravel-dashboard/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ─────────────────────────────────────────────
# Stage 3: Production image (PHP-FPM + Nginx)
# ─────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    opcache \
    pcntl \
    && rm -rf /var/cache/apk/*

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/laravel/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/laravel/nginx-site.conf /etc/nginx/http.d/default.conf
COPY docker/laravel/supervisord.conf /etc/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY laravel-dashboard/ .

# Copy built assets from frontend stage
COPY --from=frontend /app/public/build public/build

# Copy vendor from composer stage
COPY --from=vendor /app/vendor vendor

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
