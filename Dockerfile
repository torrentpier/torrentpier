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
    echo "*/10 * * * * cd /app && php bull cron:run >> /proc/1/fd/1 2>&1" > /etc/crontabs/root

WORKDIR /app
COPY . /app
RUN rm -rf .git .github .gitattributes .gitignore \
    .editorconfig .idea .vscode \
    .php-cs-fixer.php .styleci.yml phpunit.xml phpstan.neon \
    CHANGELOG.md CLAUDE.md README.md UPGRADE_GUIDE.md CONTRIBUTING.md \
    tests install/release_scripts
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && \
    chmod +x ./bull ./install/docker/docker-entrypoint.sh && \
    ln -s /app/bull /usr/local/bin/bull && \
    cp ./install/docker/php.ini /usr/local/etc/php/php.ini

ENTRYPOINT ["/app/install/docker/docker-entrypoint.sh"]
