FROM php:8.4-apache

# Install system packages
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libicu-dev \
    libzip-dev \
    pkg-config

# PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    intl \
    zip

# Enable Apache rewrite
RUN a2enmod rewrite

# Configure Laravel public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Allow .htaccess
RUN echo '<Directory /var/www/html/public>\n\
AllowOverride All\n\
Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
&& a2enconf laravel

# Install Node.js
RUN apt-get update && apt-get install -y curl

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs

COPY . .

# Build Vite assets
RUN npm install
RUN npm run build

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application
COPY . .

# Ensure SQLite file exists
RUN mkdir -p database && touch database/database.sqlite

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

RUN chmod 777 database/database.sqlite

RUN chmod -R 777 database

RUN touch database/database.sqlite

RUN php artisan migrate --force

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]