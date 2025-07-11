---
sidebar_position: 2
title: Docker Development
---

# Docker Development

Laravel Sail provides a Docker-powered local development environment for TorrentPier. This guide covers everything you need to know about using Sail for development.

## Prerequisites

Before getting started with Sail, ensure you have:

- Docker Desktop installed and running
- Git for cloning the repository
- A terminal/command line interface

:::tip
For Windows users, we recommend using WSL2 (Windows Subsystem for Linux) for the best experience.
:::

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/torrentpier/torrentpier.git
cd torrentpier
```

### 2. Copy Environment File

```bash
cp .env.sail.example .env
```

### 3. Install Dependencies

If you don't have PHP installed locally, you can use a temporary container:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Start Sail

```bash
./vendor/bin/sail up -d
```

### 5. Generate Application Key

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Run Migrations

```bash
./vendor/bin/sail artisan migrate
```

### 7. Install Frontend Dependencies

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Your application will be available at:
- Application: http://localhost
- Mailpit (Email testing): http://localhost:8025
- Meilisearch: http://localhost:7700

## Sail Configuration

### Environment Variables

The `.env.sail.example` file contains pre-configured settings for Docker services:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=torrentpier
DB_USERNAME=sail
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey

# Mailpit
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# MinIO
AWS_ACCESS_KEY_ID=sail
AWS_SECRET_ACCESS_KEY=password
AWS_BUCKET=local
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Services

TorrentPier's Sail configuration includes:

- **PostgreSQL 17** - Primary database
- **Redis** - Caching and queues
- **Meilisearch** - Full-text search engine
- **Mailpit** - Email testing interface
- **MinIO** - S3-compatible object storage for file uploads

### Customizing Services

To add additional services:

```bash
./vendor/bin/sail artisan sail:add
```

Available services include:
- MySQL
- MariaDB
- MongoDB
- Valkey
- Memcached
- Typesense
- RabbitMQ
- Selenium
- Soketi

## Common Commands

### Starting and Stopping

```bash
# Start all services
./vendor/bin/sail up -d

# Stop all services
./vendor/bin/sail stop

# Stop and remove containers
./vendor/bin/sail down

# Remove containers and volumes (full reset)
./vendor/bin/sail down -v
```

### Artisan Commands

```bash
# Run any Artisan command
./vendor/bin/sail artisan [command]

# Examples:
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan scout:import
```

### Composer Commands

```bash
# Install dependencies
./vendor/bin/sail composer install

# Update dependencies
./vendor/bin/sail composer update

# Add a package
./vendor/bin/sail composer require package/name
```

### NPM Commands

```bash
# Install dependencies
./vendor/bin/sail npm install

# Run development server
./vendor/bin/sail npm run dev

# Build for production
./vendor/bin/sail npm run build
```

### Testing

```bash
# Run all tests
./vendor/bin/sail test

# Run specific test
./vendor/bin/sail test tests/Feature/ExampleTest.php

# Run tests with coverage
./vendor/bin/sail test --coverage
```

### Database Operations

```bash
# Access PostgreSQL CLI
./vendor/bin/sail psql

# Export database
./vendor/bin/sail exec pgsql pg_dump -U sail torrentpier > backup.sql

# Import database
./vendor/bin/sail exec pgsql psql -U sail torrentpier < backup.sql
```

### Shell Access

```bash
# Access application container
./vendor/bin/sail shell

# Access as root
./vendor/bin/sail root-shell

# Access specific service
./vendor/bin/sail exec redis redis-cli
```

## Shell Alias

For convenience, add this alias to your shell configuration:

```bash
# ~/.bashrc or ~/.zshrc
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

Now you can use:

```bash
sail up
sail artisan migrate
sail npm run dev
```

## Debugging with Xdebug

### Enable Xdebug

Set in your `.env` file:

```env
SAIL_XDEBUG_MODE=debug,develop
```

### Configure Your IDE

#### PHPStorm
1. Go to Settings → PHP → Servers
2. Add a new server:
   - Name: `localhost`
   - Host: `localhost`
   - Port: `80`
   - Debugger: `Xdebug`
   - Use path mappings: Yes
   - Project files: `/var/www/html`

#### VS Code
Install the PHP Debug extension and add to `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Sail Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

## Performance Optimization

### Windows (WSL2)

For better performance on Windows:

1. Clone the project inside WSL2:

```bash
cd ~/projects
git clone https://github.com/torrentpier/torrentpier.git
```

2. Configure Vite for HMR:

```js
   // vite.config.js
   export default defineConfig({
       server: {
           hmr: {
               host: 'localhost',
           },
       },
   });
```

### macOS

Enable VirtioFS in Docker Desktop:
1. Go to Docker Desktop → Settings → General
2. Enable "Use Virtualization framework"
3. Go to Settings → Resources → File sharing
4. Enable "VirtioFS"

## Production Builds

### Building for Production

```bash
# Build production Docker image
docker build -f Dockerfile.production -t torrentpier:latest .

# Using docker-compose
docker compose -f docker-compose.production.yml build
```

### Running Production Containers

```bash
# Start production stack
docker compose -f docker-compose.production.yml up -d

# View logs
docker compose -f docker-compose.production.yml logs -f

# Stop production stack
docker compose -f docker-compose.production.yml down
```

## Troubleshooting

### Port Conflicts

If you get port conflicts, customize ports in `.env`:

```env
APP_PORT=8080
FORWARD_DB_PORT=5433
FORWARD_REDIS_PORT=6380
FORWARD_MEILISEARCH_PORT=7701
FORWARD_MAILPIT_PORT=1026
FORWARD_MAILPIT_DASHBOARD_PORT=8026
```

### Permission Issues

Fix permission issues:

```bash
# Set correct ownership
./vendor/bin/sail exec laravel.test chown -R sail:sail storage bootstrap/cache

# Or from host
sudo chown -R $(id -u):$(id -g) storage bootstrap/cache
```

### Container Won't Start

1. Check logs:

```bash
./vendor/bin/sail logs laravel.test
```

2. Rebuild containers:

```bash
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

3. Reset everything:

```bash
./vendor/bin/sail down -v
rm -rf vendor node_modules
# Then start from Quick Start
```

### Database Connection Issues

If you can't connect to the database:

1. Wait for PostgreSQL to be ready:

```bash
./vendor/bin/sail exec pgsql pg_isready -h localhost -U sail
```

2. Check PostgreSQL logs:

```bash
./vendor/bin/sail logs pgsql
```

3. Verify credentials match `.env` file

## Advanced Usage

### Custom PHP Extensions

To add PHP extensions, first publish Sail's Dockerfiles:

```bash
./vendor/bin/sail artisan sail:publish
```

Then modify `docker/8.4/Dockerfile`:

```dockerfile
RUN apt-get update && apt-get install -y \
    php8.4-gmp \
    php8.4-imagick
```

Finally rebuild:

```bash
./vendor/bin/sail build --no-cache
```

### Running Multiple Projects

Use different APP_PORT values:

```bash
# Project 1 (.env)
APP_PORT=8001

# Project 2 (.env)
APP_PORT=8002
```

### Using Different PHP Versions

1. Publish Sail's Dockerfiles:

```bash
./vendor/bin/sail artisan sail:publish
```

2. Modify `docker-compose.yml`:

```yaml
services:
    laravel.test:
        build:
           context: ./docker/8.3
```

3. Rebuild:

```bash
./vendor/bin/sail build --no-cache
```

## GitHub Codespaces / Devcontainers

For GitHub Codespaces support:

```bash
./vendor/bin/sail artisan sail:install --devcontainer
```

This creates `.devcontainer/devcontainer.json` for use with:
- GitHub Codespaces
- VS Code Remote Containers
- JetBrains Gateway

## Additional Resources

- [Official Laravel Sail Documentation](https://laravel.com/docs/sail)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

## Getting Help

If you encounter issues:

1. Check the [troubleshooting](#troubleshooting) section
2. Search existing [GitHub Issues](https://github.com/torrentpier/torrentpier/issues)
3. Ask on our [support forum](https://torrentpier.com)
4. Create a new issue with:
   - Your OS and Docker version
   - Steps to reproduce
   - Error messages/logs
