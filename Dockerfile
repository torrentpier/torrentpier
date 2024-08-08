FROM php:8.1-apache
LABEL maintainer="TorrentPier <admin@torrentpier.com>"

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install GD2
RUN apt-get update && apt-get install -y --no-install-recommends --allow-downgrades \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libz-dev \
    zlib1g-dev \
    libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
    rm -rf /var/lib/apt/lists/*

# Install mysqli
RUN docker-php-ext-install mysqli

# Install mbstring
RUN docker-php-ext-install mbstring

# Install zip & unzip
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip && \
    docker-php-ext-install zip && \
    rm -rf /var/lib/apt/lists/*

# Install intl
RUN apt-get update && apt-get install -y --no-install-recommends \
    g++ \
    libicu-dev \
    zlib1g-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    rm -rf /var/lib/apt/lists/*

# Custom php.ini settings
COPY docker/php/php.ini ${PHP_INI_DIR}/php.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

# Set the work directory to /var/www/html so all subsequent commands in this file start from that directory.
# Also set this work directory so that it uses this directory everytime we use docker exec.
WORKDIR /var/www/html

# Install the composer dependencies (no autoloader yet as that invalidates the docker cache)
COPY composer.json ./
COPY composer.lock ./
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress && \
    composer clear-cache

# Bundle source code into container. Important here is that copying is done based on the rules defined in the .dockerignore file.
COPY . /var/www/html

# Dump the autoloader
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Give apache write access to host
RUN chown -R www-data:www-data /var/www/html

# This specifies on which port the application will run. This is pure communicative and makes this 12 factor app compliant
# (see https://12factor.net/port-binding).
EXPOSE 80 443
