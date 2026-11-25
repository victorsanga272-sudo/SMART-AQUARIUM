#!/usr/bin/env bash
set -euo pipefail

# Render injects PORT dynamically; default to 80 if unset
PORT="${PORT:-80}"

# Update Apache listening port and VirtualHost port to match Render's PORT
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:([0-9]+)>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/*.conf

# Ensure runtime directories and permissions remain intact on startup
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear any stale cached config before migrating
php artisan config:clear || true

# Storage link (ignore error if already linked)
php artisan storage:link --force || true

# Run database migrations against Render PostgreSQL
php artisan migrate --force

# Cache config, routes, and views for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in the foreground so Render keeps the container alive
exec apache2-foreground