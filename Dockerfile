# ============================================
# Stage 1: Build dependencies
# ============================================
FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

COPY . .


# ============================================
# Stage 2: Production runtime
# ============================================
FROM php:8.2-fpm-alpine AS production

WORKDIR /var/www

# Install system dependencies and build tools
RUN apk add --no-cache \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    icu \
    icu-libs \
    gcc \
    make \
    musl-dev

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    zip \
    gd \
    intl

# Copy application files
COPY --from=builder /app /var/www

# Initialize Yii2 configuration
RUN php init --env=Production --overwrite=n

# Copy .env.example to .env if not exists
RUN test -f /var/www/.env || cp /var/www/.env.example /var/www/.env

# Setup permissions - create directories first
RUN mkdir -p /var/www/runtime \
    && mkdir -p /var/www/backend/web/assets \
    && mkdir -p /var/www/backend/web/uploads \
    && mkdir -p /var/www/frontend/web/assets \
    && mkdir -p /var/www/frontend/web/uploads \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 /var/www/runtime \
    && chmod -R 775 /var/www/backend/web/assets \
    && chmod -R 775 /var/www/frontend/web/assets

EXPOSE 9000

CMD ["php-fpm"]