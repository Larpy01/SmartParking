FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev \
    gnupg

# Install Node.js (stable LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies (production)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Install JS dependencies and build assets
RUN npm install
RUN npm run build

# Clear & cache Laravel config
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan config:cache

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# IMPORTANT: Railway uses port 8080
EXPOSE 8080

# Start server using Railway's injected PORT
CMD php -S 0.0.0.0:$PORT -t public