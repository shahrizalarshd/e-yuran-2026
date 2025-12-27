# ============================================
# E-YURAN PPTT - Production Dockerfile
# Multi-stage build for optimized image
# ============================================

# Stage 1: Build assets
FROM node:20-alpine AS assets-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source files
COPY resources/ ./resources/
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Build production assets
RUN npm run build


# Stage 2: Composer dependencies
FROM composer:2.7 AS composer-builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies without dev
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev


# Stage 3: Production image
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="E-Yuran PPTT <admin@eyuran.com>"
LABEL description="E-Yuran PPTT - Sistem Yuran Taman Tropika Kajang"

# Environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    mysql-client \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    xml \
    opcache

# Configure PHP for production
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx-site.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create working directory
WORKDIR /var/www/html

# Copy application from builder stages
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=assets-builder /app/public/build ./public/build

# Copy application code
COPY --chown=www-data:www-data . .

# Remove unnecessary files
RUN rm -rf \
    .git \
    .github \
    node_modules \
    tests \
    docker \
    *.md \
    *.yml \
    *.yaml \
    .env.example \
    phpunit.xml \
    vite.config.js \
    tailwind.config.js \
    postcss.config.js \
    package*.json

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create required directories
RUN mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/storage

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Expose port
EXPOSE 80

# Start supervisor (manages nginx + php-fpm + queue worker)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

