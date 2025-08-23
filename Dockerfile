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
    libtidy-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    default-mysql-client

# Install PHP extensions
RUN docker-php-ext-install \
    mysqli \
    mbstring \
    gd \
    bcmath \
    intl \
    tidy \
    xml \
    xmlwriter \
    fileinfo \
    dom \
    ctype \
    curl \
    filter \
    hash \
    zip \
    pcntl \
    opcache \

# PECL extensions
RUN pecl install redis apcu && docker-php-ext-enable redis apcu

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/* && rm -rf /tmp/pear

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Remove default server definition
RUN rm -rf /var/www/html

# Install composer dependencies
COPY composer.json composer.lock* ./

RUN if [ -f composer.json ]; then \
        composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts; \
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
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab /etc/cron.d/app-cron

# Configuration files
COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose active ports
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

# Startup supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
