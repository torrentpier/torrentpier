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

if grep -q "DB_HOST=localhost" .env; then
    sed -i 's/DB_HOST=localhost/DB_HOST=torrentpier-db/' .env
    echo "✅ Updated DB_HOST for Docker"
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
echo "   2. Open: http://localhost"
echo ""
echo "🔒 To enable HTTPS/SSL:"
echo "   1. Edit .env file and change:"
echo "      TP_HOST=localhost        →  TP_HOST=yourdomain.com"
echo "      SSL_ENABLED=off          →  SSL_ENABLED=on"
echo "   2. Make sure your domain points to this server"
echo "   3. Restart: docker-compose down && docker-compose up"
echo ""
echo "⚠️  SSL Notes:"
echo "   - SSL requires a real domain (not localhost/IP)"
echo "   - Caddy will automatically get Let's Encrypt certificates"
echo "   - Port 80 and 443 must be accessible from the internet"
echo ""
