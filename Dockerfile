# Set the base image for subsequent instructions
FROM dunglas/frankenphp:1-php8.4-bookworm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    cron \
    curl \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    default-mysql-client

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    fileinfo \
    mbstring \
    mysqli \
    xml \
    zip \
    intl \
    pcntl \
    opcache

# PECL extensions
RUN pecl install redis xdebug apcu xhprof \
    && docker-php-ext-enable redis xdebug apcu xhprof

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration files
COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www

# Remove default server definition
RUN rm -rf /var/www/html

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Change current user to www
USER www-data

# Install composer dependencies
RUN if [ -f composer.json ]; then \
        composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts; \
    fi

# Cleanup TorrentPier instance
RUN php _cleanup.php && rm _cleanup.php

# Set permissions for TorrentPier directories
RUN if [ -d "/var/www/internal_data" ]; then \
        chown -R www-data:www-data /var/www/internal_data; \
        chmod -R 775 /var/www/internal_data; \
    fi \
    && if [ -d "/var/www/data" ]; then \
        chown -R www-data:www-data /var/www/data; \
        chmod -R 775 /var/www/data; \
    fi \
    && if [ -d "/var/www/sitemap" ]; then \
        chown -R www-data:www-data /var/www/sitemap; \
        chmod -R 775 /var/www/sitemap; \
    fi

# Setup cron
RUN echo "*/10 * * * * php /app/cron.php >> /proc/1/fd/1 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab /etc/cron.d/app-cron

# Expose active ports
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
