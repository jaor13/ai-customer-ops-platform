# ─────────────────────────────────────────────
# Stage 1: Install PHP dependencies
# ─────────────────────────────────────────────
FROM composer:2 AS vendor

# phpoffice/phpword requires ext-gd at install time (platform check).
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

WORKDIR /app

COPY laravel-dashboard/composer.json laravel-dashboard/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ─────────────────────────────────────────────
# Stage 2: Production image (PHP-FPM + Nginx)
# ─────────────────────────────────────────────
FROM php:8.4-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    curl \
    nodejs \
    npm \
    # Knowledge-base OCR / PDF tooling (scanned-PDF fallback in DocumentTextExtractor).
    # Optional at runtime — gated by KB_OCR_ENABLED; remove this group to slim the image.
    ocrmypdf \
    tesseract-ocr \
    tesseract-ocr-data-eng \
    ghostscript \
    poppler-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    gd \
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

# Copy vendor from composer stage
COPY --from=vendor /app/vendor vendor

# Install Node dependencies and build assets (needs vendor/ for Ziggy)
RUN rm -f package-lock.json \
    && npm install \
    && npm run build \
    && rm -rf node_modules

# Set permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
