FROM dunglas/frankenphp:1.9-php8.4-alpine

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions \
    mbstring \
    mysqli \
	pdo_mysql \
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
RUN composer install --no-dev --optimize-autoloader && chmod +x ./install/docker/docker-entrypoint.sh

ENTRYPOINT "/app/public/install/docker/docker-entrypoint.sh"