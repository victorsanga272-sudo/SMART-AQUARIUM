# Stage 1: Build frontend assets (Vite / Tailwind)
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.* ./
COPY public/ public/
RUN npm run build


# Stage 2: PHP + Apache runtime
FROM php:8.3-apache

# Install system dependencies and PHP extensions once
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        intl \
        mbstring \
        bcmath \
        zip \
        opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache DocumentRoot to /var/www/html/public and allow .htaccess overrides
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

WORKDIR /var/www/html
# Copy application source and built Vite assets
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Finalize optimized autoloader
RUN composer dump-autoload --optimize

# Set writable permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Add startup script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]