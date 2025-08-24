# Set the base image for subsequent instructions
FROM dunglas/frankenphp:1-php8.4-bookworm

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    cron \
    supervisor \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    libtidy-dev \
    git unzip libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd bcmath tidy mysqli pdo_mysql intl \
    && pecl install redis apcu imagick \
    && docker-php-ext-enable redis apcu imagick tidy gd bcmath mysqli pdo_mysql intl \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Remove default server definition
RUN rm -rf /var/www/html

# Install composer dependencies
COPY composer.json composer.lock* ./
RUN if [ -f composer.json ]; then \
        composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts --no-interaction; \
    fi

# Copy TorrentPier code
COPY . /var/www/

# Cleanup TorrentPier instance
RUN if [ -f _cleanup.php ]; then \
        php _cleanup.php && rm _cleanup.php; \
    fi

# Set permissions for TorrentPier directories
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 /var/www/internal_data /var/www/data /var/www/sitemap

# Setup cron
RUN echo "*/10 * * * * www-data cd /var/www && php cron.php >/proc/1/fd/1 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron

# Configuration files
COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose ports
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

# Startup supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
