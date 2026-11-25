#!/usr/bin/env sh
set -e

# Bind Apache to Render's dynamic $PORT (defaulting to 80 locally)
PORT="${PORT:-80}"
sed -ri -e "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri -e "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure storage symlink exists (safe if already linked)
php artisan storage:link --force || true

# Run database migrations safely in production
# (Comment out if you prefer running migrations manually or via a separate job)
php artisan migrate --force || true

# Cache config, routes, and views using runtime environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in foreground
exec apache2-foreground