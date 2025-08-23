# Set the base image for subsequent instructions
FROM dunglas/frankenphp:1-php8.4

# Install system dependencies
RUN apt-get update && apt-get install -y --fix-missing \
    build-essential \
    cron \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install mysqli gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

# Cleanup TorrentPier instance
RUN php _cleanup.php && rm _cleanup.php

# Setup cron
RUN echo "*/10 * * * * php /app/cron.php >> /proc/1/fd/1 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab /etc/cron.d/app-cron

COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose active ports
EXPOSE 80
EXPOSE 443
EXPOSE 443/udp

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
