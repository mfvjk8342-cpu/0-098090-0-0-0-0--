FROM php:8.2-cli

WORKDIR /app

# System deps + PHP extensions commonly needed by Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP deps first (better layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy app source
COPY . .

# Run Laravel package discovery after application files are available
RUN php artisan package:discover --ansi

# Runtime Laravel prep (safe if directories already exist)
RUN mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

CMD php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
