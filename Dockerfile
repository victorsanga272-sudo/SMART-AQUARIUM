# Stage 1: Build frontend assets with Node 22
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

# Stage 2: PHP 8.4 + Apache runtime
FROM php:8.4-apache

# Install system dependencies and PHP extensions (including libonig-dev for mbstring and pdo_pgsql for Render PostgreSQL)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    pkg-config \
    zlib1g-dev \
    libonig-dev \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql intl mbstring bcmath zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Set Apache DocumentRoot to Laravel's /public folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first for Docker layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application source and compiled Vite build output
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Generate optimized autoloader and set writable permissions
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Make entrypoint executable and run it on startup
RUN chmod +x /var/www/html/docker/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]