#!/bin/bash
set -e

echo "🐳 Setting up TorrentPier for Docker..."
echo ""

if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker is not installed!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: docker-compose is not installed!"
    exit 1
fi

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

# Docker-specific configurations
echo ""
echo "🔧 Configuring for Docker environment..."

if grep -q "^APP_CRON_ENABLED=true" .env; then
   sed -i 's/^APP_CRON_ENABLED=true/APP_CRON_ENABLED=false/' .env
   echo "✅ Disabled APP_CRON_ENABLED (Docker will handle cron)"
elif ! grep -q "^APP_CRON_ENABLED=" .env; then
   echo "" >> .env
   echo "APP_CRON_ENABLED=false" >> .env
   echo "✅ Added APP_CRON_ENABLED=false"
fi

if grep -q "^DB_HOST=" .env; then
   sed -i 's/^DB_HOST=.*/DB_HOST=torrentpier-db/' .env
   echo "✅ Updated DB_HOST for Docker"
else
   echo "DB_HOST=torrentpier-db" >> .env
   echo "✅ Added DB_HOST for Docker"
fi

if grep -q "^DB_USERNAME=" .env; then
   sed -i 's/^DB_USERNAME=.*/DB_USERNAME=torrentpier_user/' .env
   echo "✅ Updated DB_USERNAME to torrentpier_user"
else
   echo "DB_USERNAME=torrentpier_user" >> .env
   echo "✅ Added DB_USERNAME=torrentpier_user"
fi

echo ""
read -s -p "🔐 Enter database password for 'torrentpier_user': " DB_PASSWORD
echo ""

if [ -z "$DB_PASSWORD" ]; then
   echo "❌ Error: Database password cannot be empty!"
   exit 1
fi

# More robust password escaping for sed
ESCAPED_PASSWORD=$(printf '%s\n' "$DB_PASSWORD" | sed 's/[[\.*^$()+?{|]/\\&/g')

if grep -q "^DB_PASSWORD=" .env; then
   sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$ESCAPED_PASSWORD/" .env
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

TP_HOST=$(echo "$TP_HOST" | sed -E 's|^https?://||' | sed 's|/.*||')
if [[ ! "$TP_HOST" =~ ^[a-zA-Z0-9.-]+$ ]]; then
   echo "❌ Error: Invalid host format! Use only letters, numbers, dots, and hyphens."
   exit 1
fi

ESCAPED_HOST=$(printf '%s\n' "$TP_HOST" | sed 's/[[\.*^$()+?{|]/\\&/g')

if grep -q "^TP_HOST=" .env; then
   sed -i "s/^TP_HOST=.*/TP_HOST=$ESCAPED_HOST/" .env
else
   echo "TP_HOST=$TP_HOST" >> .env
fi
echo "✅ Set TP_HOST to $TP_HOST"

echo ""
echo "⚠️  SSL Notes:"
echo "   - SSL requires a real domain (not localhost/IP)"
echo "   - Caddy will automatically get Let's Encrypt certificates"
echo "   - Port 80 and 443 must be accessible from the internet"
echo ""

if [[ "$TP_HOST" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]] || [[ "$TP_HOST" == "localhost" ]]; then
    echo "⚠️  IP address or localhost detected - SSL will be disabled"
    ENABLE_SSL="no"
else
    read -p "🔐 Enable SSL (HTTPS) for $TP_HOST? [y/N]: " ENABLE_SSL
fi

ENABLE_SSL=$(echo "$ENABLE_SSL" | tr '[:upper:]' '[:lower:]')

if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "yes" ]; then
   SSL_ENABLED="on"
   TP_PORT="443"
   echo "✅ SSL enabled - site will be available at https://$TP_HOST"
else
   SSL_ENABLED="off"
   TP_PORT="80"
   echo "✅ HTTP mode - site will be available at http://$TP_HOST"
fi

if grep -q "^SSL_ENABLED=" .env; then
   sed -i "s/^SSL_ENABLED=.*/SSL_ENABLED=$SSL_ENABLED/" .env
else
   echo "" >> .env
   echo "# Docker SSL Configuration" >> .env
   echo "SSL_ENABLED=$SSL_ENABLED" >> .env
fi

if grep -q "^TP_PORT=" .env; then
   sed -i "s/^TP_PORT=.*/TP_PORT=$TP_PORT/" .env
else
   echo "TP_PORT=$TP_PORT" >> .env
fi

echo "✅ SSL configuration complete"

echo ""
echo "📋 Configuration Summary:"
echo "   Host: $TP_HOST"
echo "   SSL: $SSL_ENABLED"
echo "   Port: $TP_PORT"
if [ "$SSL_ENABLED" = "on" ]; then
   echo "   Docker Ports: 80, 443, 443/udp"
else
   echo "   Docker Ports: 80 only"
fi
echo "   Cron: Handled by Docker"
echo ""

echo "🎉 Docker setup complete!"
echo ""
echo "📋 Next steps:"
echo "   1. Run: docker-compose up -d"
echo "   2. Wait for database initialization..."
if [ "$SSL_ENABLED" = "on" ]; then
   echo "   3. Open: https://$TP_HOST"
   echo "   4. First HTTPS access may take a moment for SSL certificate"
else
   echo "   3. Open: http://$TP_HOST"
fi
echo ""
echo "🔍 Useful commands:"
echo "   - View logs: docker-compose logs -f"
echo "   - Stop: docker-compose down"
echo "   - Restart: docker-compose restart"
echo "   - Update: docker-compose pull && docker-compose up -d"
echo ""
