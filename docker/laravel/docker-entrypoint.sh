#!/bin/sh
set -e

# Cache config at runtime so it picks up Docker Compose env vars
# (build-time caching uses .env.example defaults which are wrong).
php /var/www/html/artisan config:cache
php /var/www/html/artisan migrate --force --isolated 2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisord.conf
