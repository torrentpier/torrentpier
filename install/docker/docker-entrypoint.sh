#!/bin/sh
set -e

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

# Create database if it doesn't exist
echo "Ensuring database exists..."
php -r "
\$host = getenv('DB_HOST') ?: 'database';
\$port = getenv('DB_PORT') ?: 3306;
\$user = getenv('DB_USERNAME');
\$pass = getenv('DB_PASSWORD');
\$db = getenv('DB_DATABASE');

// Validate database name (same whitelist as InstallCommand.php)
// Must start with letter or underscore, then alphanumeric and underscores
if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', \$db)) {
    echo \"ERROR: Invalid database name '\$db'. Must start with a letter or underscore, followed by alphanumeric characters and underscores.\n\";
    exit(1);
}

try {
    \$conn = new PDO(\"mysql:host=\$host;port=\$port\", \$user, \$pass);
    \$conn->exec(\"CREATE DATABASE IF NOT EXISTS \`\$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\");
    echo \"Database '\$db' is ready!\n\";
} catch (PDOException \$e) {
    echo \"Failed to create database: \" . \$e->getMessage() . \"\n\";
    exit(1);
}
"

# Run migrations if available
if [ -d "/app/database/migrations" ]; then
    echo "Running database migrations..."
    cd /app
    
    # Run migrations, but don't exit on failure (set -e is active)
    if php bull migrate; then
        echo "Migrations completed successfully!"
    else
        echo "Warning: Migration failed, but continuing..."
    fi
else
    echo "No migrations directory found, skipping migrations..."
fi

# Start cron in background
echo "Starting cron daemon..."
crond -f -l 2 &

# Start FrankenPHP
echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/frankenphp/Caddyfile
