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
    nginx \
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

# Download and install s6-overlay v3.2.2.0
RUN curl -L -o /tmp/s6-overlay-noarch.tar.xz https://github.com/just-containers/s6-overlay/releases/download/v3.2.2.0/s6-overlay-noarch.tar.xz \
    && curl -L -o /tmp/s6-overlay-x86_64.tar.xz https://github.com/just-containers/s6-overlay/releases/download/v3.2.2.0/s6-overlay-x86_64.tar.xz \
    && tar -C / -Jxf /tmp/s6-overlay-noarch.tar.xz \
    && tar -C / -Jxf /tmp/s6-overlay-x86_64.tar.xz \
    && rm /tmp/s6-overlay-*.tar.xz

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

# Configure Nginx
RUN echo 'server {' > /etc/nginx/http.d/default.conf \
    && echo '    listen 80;' >> /etc/nginx/http.d/default.conf \
    && echo '    server_name _;' >> /etc/nginx/http.d/default.conf \
    && echo '    root /var/www/frontend/web;' >> /etc/nginx/http.d/default.conf \
    && echo '    index index.php index.html;' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location / {' >> /etc/nginx/http.d/default.conf \
    && echo '        try_files $uri $uri/ /index.php?$args;' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location ~ \.php$' >> /etc/nginx/http.d/default.conf \
    && echo '    {' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_index index.php;' >> /etc/nginx/http.d/default.conf \
    && echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/http.d/default.conf \
    && echo '        include fastcgi_params;' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location /backend {' >> /etc/nginx/http.d/default.conf \
    && echo '        alias /var/www/backend/web;' >> /etc/nginx/http.d/default.conf \
    && echo '        try_files $uri $uri/ /backend/index.php?$args;' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '        location ~ \.php$' >> /etc/nginx/http.d/default.conf \
    && echo '        {' >> /etc/nginx/http.d/default.conf \
    && echo '            fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/http.d/default.conf \
    && echo '            fastcgi_index index.php;' >> /etc/nginx/http.d/default.conf \
    && echo '            fastcgi_param SCRIPT_FILENAME $request_filename;' >> /etc/nginx/http.d/default.conf \
    && echo '            include fastcgi_params;' >> /etc/nginx/http.d/default.conf \
    && echo '        }' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf \
    && echo '' >> /etc/nginx/http.d/default.conf \
    && echo '    location ~ /\. {' >> /etc/nginx/http.d/default.conf \
    && echo '        deny all;' >> /etc/nginx/http.d/default.conf \
    && echo '    }' >> /etc/nginx/http.d/default.conf

# Create s6 service directories
RUN mkdir -p /etc/s6/services/php-fpm /etc/s6/services/nginx

# Create php-fpm service
RUN echo '#!/command/execlineb -P' > /etc/s6/services/php-fpm/run \
    && echo 'php-fpm' >> /etc/s6/services/php-fpm/run \
    && chmod +x /etc/s6/services/php-fpm/run

# Create nginx service
RUN echo '#!/command/execlineb -P' > /etc/s6/services/nginx/run \
    && echo 'nginx' >> /etc/s6/services/nginx/run \
    && chmod +x /etc/s6/services/nginx/run

EXPOSE 80

ENTRYPOINT ["/init"]