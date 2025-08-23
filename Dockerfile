FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libxslt \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    tidyhtml-dev \
    libxml2-dev \
    oniguruma-dev \
    libgcrypt \
    mysql-client \
    caddy \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    mysqli \
    mbstring \
    gd \
    bcmath \
    intl \
    tidy \
    xml \
    xmlwriter \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create necessary directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /etc/caddy \
    && mkdir -p /data/caddy \
    && mkdir -p /var/www/html/internal_data \
    && chmod -R 755 /var/www/html

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy configuration files
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod +x /usr/local/bin/entrypoint.sh

# Expose ports
EXPOSE 80 443

# Start services
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
