#!/bin/bash

echo "🐳 Setting up TorrentPier for Docker..."
echo ""

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "✅ Created .env from .env.example"
    else
        echo "❌ Error: .env.example not found!"
        exit 1
    fi
else
    echo "ℹ️  .env file already exists, updating for Docker..."
fi

cp .env .env.backup
echo "💾 Backup created: .env.backup"

if grep -q "APP_CRON_ENABLED=true" .env; then
    sed -i 's/APP_CRON_ENABLED=true/APP_CRON_ENABLED=false/' .env
fi

if grep -q "DB_HOST=localhost" .env; then
    sed -i 's/DB_HOST=localhost/DB_HOST=torrentpier-db/' .env
    echo "✅ Updated DB_HOST for Docker"
fi

if grep -q "DB_USERNAME=root" .env; then
    sed -i 's/DB_USERNAME=root/DB_USERNAME=torrentpier_user/' .env
    echo "✅ Updated DB_USERNAME to torrentpier_user"
fi

echo ""
read -s -p "🔐 Enter database password for 'torrentpier_user': " DB_PASSWORD
echo ""

if [ -z "$DB_PASSWORD" ]; then
    echo "❌ Error: Database password cannot be empty!"
    exit 1
fi

ESCAPED_PASSWORD=$(printf '%s\n' "$DB_PASSWORD" | sed 's/[&/\]/\\&/g')

if grep -q "DB_PASSWORD=" .env; then
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$ESCAPED_PASSWORD|" .env
    echo "✅ Updated DB_PASSWORD in .env"
else
    echo "DB_PASSWORD=$DB_PASSWORD" >> .env
    echo "✅ Added DB_PASSWORD to .env"
fi

echo ""
read -p "🌐 Enter your host (IP or domain, e.g. 192.168.1.100 or example.com): " TP_HOST
echo ""

if [ -z "$TP_HOST" ]; then
    echo "❌ Error: Host cannot be empty!"
    exit 1
fi

ESCAPED_HOST=$(printf '%s\n' "$TP_HOST" | sed 's/[&/\]/\\&/g')

if grep -q "TP_HOST=" .env; then
    sed -i "s|TP_HOST=.*|TP_HOST=$ESCAPED_HOST|" .env
    echo "✅ Updated TP_HOST to $TP_HOST"
else
    echo "TP_HOST=$TP_HOST" >> .env
    echo "✅ Added TP_HOST to .env"
fi

if ! grep -q "SSL_ENABLED" .env; then
    echo "" >> .env
    echo "# Docker-specific configuration" >> .env
    echo "SSL_ENABLED=off" >> .env
    echo "SSL_PORT=443" >> .env
    echo "✅ Added Docker SSL configuration"
fi

echo ""
echo "🎉 Docker setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Run: docker-compose up"
echo "   2. Open: http://$TP_HOST"
echo ""
echo "🔒 To enable HTTPS/SSL:"
echo "   1. Edit .env file and change:"
echo "      SSL_ENABLED=off    →  SSL_ENABLED=on"
echo "   2. Make sure your domain points to this server"
echo "   3. Restart: docker-compose down && docker-compose up"
echo ""
echo "⚠️  SSL Notes:"
echo "   - SSL requires a real domain (not localhost/IP)"
echo "   - Caddy will automatically get Let's Encrypt certificates"
echo "   - Port 80 and 443 must be accessible from the internet"
echo ""
