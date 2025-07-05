# Use the official PHP image with FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache

# Copy PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Symlink storage directory
RUN php artisan storage:link

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Copy nginx configuration
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD service nginx start && php-fpm
