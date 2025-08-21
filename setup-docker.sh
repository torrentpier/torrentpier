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

if [ -z "$TP_HOST" ]; then
    echo ""
    echo "❌ Error: Host cannot be empty!"
    exit 1
fi

TP_HOST=$(echo "$TP_HOST" | sed -E 's|^https?://||')
TP_HOST=$(echo "$TP_HOST" | sed 's|/||g')
ESCAPED_HOST=$(printf '%s\n' "$TP_HOST" | sed 's/[&/\]/\\&/g')

if grep -q "TP_HOST=" .env; then
    sed -i "s|TP_HOST=.*|TP_HOST=$ESCAPED_HOST|" .env
    echo "✅ Updated TP_HOST to $TP_HOST"
else
    echo "TP_HOST=$TP_HOST" >> .env
    echo "✅ Added TP_HOST to .env"
fi

echo ""
echo "⚠️  SSL Notes:"
echo "   - SSL requires a real domain (not localhost/IP)"
echo "   - Caddy will automatically get Let's Encrypt certificates"
echo "   - Port 80 and 443 must be accessible from the internet"
echo ""

read -p "🔐 Do you want to enable SSL (HTTPS)? [y/N]: " ENABLE_SSL
ENABLE_SSL=$(echo "$ENABLE_SSL" | tr '[:upper:]' '[:lower:]')

if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "yes" ]; then
    SSL_ENABLED="on"
    if grep -q "TP_PORT=80" .env; then
        sed -i 's/TP_PORT=80/TP_PORT=443/' .env
    fi
else
    SSL_ENABLED="off"
fi

if grep -q "SSL_ENABLED" .env; then
    sed -i "s|SSL_ENABLED=.*|SSL_ENABLED=$SSL_ENABLED|" .env
    echo "✅ Updated SSL_ENABLED to $SSL_ENABLED"
else
    echo "" >> .env
    echo "# Docker-specific configuration" >> .env
    echo "SSL_ENABLED=$SSL_ENABLED" >> .env
    echo "SSL_PORT=443" >> .env
    echo "✅ Added Docker SSL configuration (SSL_ENABLED=$SSL_ENABLED)"
fi

echo ""
echo "🎉 Docker setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Run: docker-compose up"
if [ "$SSL_ENABLED" = "on" ]; then
    echo "   2. Open: https://$TP_HOST"
else
    echo "   2. Open: http://$TP_HOST"
fi
echo ""
