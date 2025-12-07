FROM dunglas/frankenphp:1.9-php8.4-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions \
    apcu \
    redis \
    memcached \
    mbstring \
    mysqli \
    pdo_mysql \
    pdo_sqlite \
    gd \
    intl \
    tidy \
    bcmath \
    xml \
    xmlwriter

RUN apk add --no-cache dcron && \
    echo "*/10 * * * * cd /app/public && php cron.php >> /proc/1/fd/1 2>&1" > /etc/crontabs/root

WORKDIR /app/public
COPY . /app/public
RUN php install/release_scripts/_cleanup.php && rm -rf install/release_scripts
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && chmod +x ./install/docker/docker-entrypoint.sh && \
    cp ./install/docker/php.ini /usr/local/etc/php/php.ini

ENTRYPOINT "/app/public/install/docker/docker-entrypoint.sh"
