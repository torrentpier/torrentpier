FROM dunglas/frankenphp:1-php8.4

RUN apt-get update && apt-get install -y \
    cron \
    libicu-dev \
    libtidy-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        mbstring \
        gd \
        bcmath \
        intl \
        tidy \
        xml \
        xmlwriter \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

RUN echo "*/10 * * * * php /app/cron.php >> /var/log/cron.log 2>&1" > /etc/cron.d/app-cron \
    && chmod 0644 /etc/cron.d/app-cron \
    && crontab /etc/cron.d/app-cron \
    && touch /var/log/cron.log

COPY install/Caddyfile /etc/caddy/Caddyfile

EXPOSE 80 443

CMD service cron start && frankenphp run --config /etc/caddy/Caddyfile
