FROM php:8.2-cli

WORKDIR /app

# System packages and PHP extensions for Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install backend PHP dependencies first for layer caching
COPY backend/composer.json backend/composer.lock /app/backend/
WORKDIR /app/backend
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy backend source
COPY backend /app/backend

# Prepare writable directories
RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
