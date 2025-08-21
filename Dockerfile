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

WORKDIR /app
COPY . /app

RUN echo "*/10 * * * * php /app/cron.php >> /proc/1/fd/1 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab /etc/cron.d/app-cron

COPY install/docker/Caddyfile /etc/caddy/Caddyfile
COPY install/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80 443

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
