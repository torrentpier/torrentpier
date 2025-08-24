# Set the base image for subsequent instructions
FROM dunglas/frankenphp:1-php8.4-bookworm

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    cron \
    supervisor \
    git \
    unzip \
    default-mysql-client \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libavif-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    libtidy-dev \
    libmagickwand-dev \
 && docker-php-ext-configure gd \
      --with-freetype \
      --with-jpeg \
      --with-webp \
      --with-avif \
 && docker-php-ext-install -j"$(nproc)" gd bcmath tidy mysqli pdo_mysql intl \
 && pecl install redis apcu imagick \
 && docker-php-ext-enable gd bcmath tidy mysqli pdo_mysql intl redis apcu imagick \
 && rm -rf /var/lib/apt/lists/* /tmp/pear

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Change current user to www-data
USER www-data

# Remove default server definition
RUN rm -rf /var/www/html

# Install composer dependencies
COPY composer.json composer.lock* ./
RUN if [ -f composer.json ]; then \
        composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts --no-interaction; \
    fi

# Copy TorrentPier code
COPY . /var/www/

RUN chmod g+s /var/www/internal_data /var/www/data /var/www/sitemap

# Change current user to root
USER root

# Настройка прав для всех директорий проекта
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && find /var/www/internal_data /var/www/data /var/www/sitemap -type d -exec chmod 775 {} \; \
    && find /var/www/internal_data /var/www/data /var/www/sitemap -type f -exec chmod 664 {} \;

# Configuration files
COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup cron
RUN echo "*/10 * * * * www-data cd /var/www && /usr/local/bin/php cron.php >> /proc/1/fd/1 2>&1" \
    > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab -u www-data /etc/cron.d/app-cron

# Expose ports
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

# Startup supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
