#!/bin/sh

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to be ready..."
until php -r "
\$maxAttempts = 30;
\$attempt = 0;
\$host = getenv('DB_HOST') ?: 'database';
\$port = getenv('DB_PORT') ?: 3306;
\$user = getenv('DB_USERNAME');
\$pass = getenv('DB_PASSWORD');
\$db = getenv('DB_DATABASE');

while (\$attempt < \$maxAttempts) {
    try {
        \$conn = new PDO(\"mysql:host=\$host;port=\$port\", \$user, \$pass);
        echo \"Database connection successful!\n\";
        exit(0);
    } catch (PDOException \$e) {
        \$attempt++;
        echo \"Attempt \$attempt/\$maxAttempts: Waiting for database...\n\";
        sleep(2);
    }
}
echo \"Failed to connect to database after \$maxAttempts attempts\n\";
exit(1);
"; do
  echo "Database is not ready yet. Retrying..."
  sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
cd /app/public
php vendor/bin/phinx migrate --configuration=phinx.php

if [ $? -eq 0 ]; then
    echo "Migrations completed successfully!"
else
    echo "Migration failed!"
    exit 1
fi

# Cron
crond -f -l 2 &

# FrankenPHP
exec frankenphp run --config /etc/frankenphp/Caddyfile
