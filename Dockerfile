FROM dunglas/frankenphp:1-php8.4

RUN apt-get update && apt-get install -y \
    cron \
    supervisor \
    libicu-dev \
    libtidy-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        mbstring \
        gd \
        bcmath \
        intl \
        tidy \
        xml \
        xmlwriter \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

COPY . /app

RUN php _cleanup.php && rm _cleanup.php

RUN chown -R www-data:www-data /app \
    && find /app -type d -exec chmod 755 {} \; \
    && find /app -type f -exec chmod 644 {} \;

RUN echo "*/10 * * * * www-data php /app/cron.php >> /proc/1/fd/1 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron

COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80 443

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
