#!/usr/bin/env sh
set -e

PORT="${PORT:-80}"
sed -ri -e "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri -e "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

ls -la /etc/secrets/ca.pem
grep -m 1 "BEGIN CERTIFICATE" /etc/secrets/ca.pem

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php artisan config:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground