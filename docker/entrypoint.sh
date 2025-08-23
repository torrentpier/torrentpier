#!/bin/sh

# Exit on error
set -e

echo "Starting TorrentPier Docker container..."

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until nc -z mysql 3306; do
    echo "MySQL is not ready yet, waiting..."
    sleep 2
done
echo "MySQL is ready!"

# Check if .env file exists, if not copy from .env.example
if [ ! -f "/var/www/html/.env" ] && [ -f "/var/www/html/.env.example" ]; then
    echo "Copying .env.example to .env..."
    cp /var/www/html/.env.example /var/www/html/.env

    # Update database settings in .env
    sed -i "s/DB_HOST=.*/DB_HOST=mysql/" /var/www/html/.env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-torrentpier}/" /var/www/html/.env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-root}/" /var/www/html/.env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-secret}/" /var/www/html/.env
    sed -i "s/TP_HOST=.*/TP_HOST=${TP_HOST:-localhost}/" /var/www/html/.env
    sed -i "s/TP_PORT=.*/TP_PORT=${TP_PORT:-80}/" /var/www/html/.env
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Create necessary directories if they don't exist
for dir in "internal_data" "data" "sitemap"; do
    if [ ! -d "/var/www/html/$dir" ]; then
        mkdir -p "/var/www/html/$dir"
        echo "Created directory: $dir"
    fi
done

# Set proper permissions for all directories
chown -R www-data:www-data /var/www/html/internal_data /var/www/html/data /var/www/html/sitemap 2>/dev/null || true

# Generate Caddyfile based on SSL settings
if [ "$SSL_ENABLED" = "true" ]; then
    echo "SSL is enabled, configuring HTTPS..."
    export CADDY_TLS_SETTING="tls ${SSL_EMAIL:-internal}"
else
    echo "SSL is disabled, using HTTP only..."
    export CADDY_TLS_SETTING=""
fi

# Install/update Composer dependencies if composer.json exists
if [ -f "/var/www/html/composer.json" ]; then
    echo "Installing/updating Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

echo "Starting services with supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
