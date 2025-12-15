---
sidebar_position: 14
title: Web Server Configuration
---

# Web Server Configuration

TorrentPier requires proper web server configuration for clean URLs and optimal performance. All `.php` file redirects and static file serving should be handled by the web server, not PHP.

## Nginx Configuration

Located at `install/nginx.conf`:

```nginx
server {
    listen 80;
    server_name example.com;
    root /path/to/torrentpier/public;
    index index.php;
    charset utf-8;

    # All requests go to front controller
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    # Block access to sensitive files
    location ~ /\.\.  { return 404; }
    location ~ /\.(ht|en) { return 404; }
    location ~ /\.git { return 404; }

    # ==========================================================
    # SEO Redirects - clean URLs
    # ==========================================================

    # Redirect /index.php to /
    location = /index.php {
        if ($args = '') {
            return 301 /;
        }
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    }

    # Redirect *.php to clean URLs (301 for SEO)
    # Example: /viewtopic.php?t=123 -> /viewtopic?t=123
    location ~ ^/(.+)\.php$ {
        return 301 /$1$is_args$args;
    }

    # ==========================================================
    # Static files
    # ==========================================================

    # Sitemap location
    location = /sitemap.xml {
        alias /path/to/torrentpier/public/storage/sitemap/sitemap.xml;
        expires 1h;
    }

    # Static file caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP processing
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Caddy/FrankenPHP Configuration (Docker)

Located at `install/docker/Caddyfile`:

```caddyfile
{$TP_HOST} {
    root * /app/public
    encode gzip zstd

    # Block sensitive files
    @blocked {
        path /..
        path /.ht* /.en* /.git* /.docker*
    }
    respond @blocked 404

    # Redirect /index.php to /
    @indexphp {
        path /index.php
        not query *
    }
    redir @indexphp / 301

    # Redirect *.php to clean URLs (301 for SEO)
    @phpfiles {
        path_regexp php ^/(.+)\.php$
    }
    redir @phpfiles /{re.php.1}{query} 301

    # Sitemap
    handle /sitemap.xml {
        rewrite * /storage/sitemap/sitemap.xml
        file_server
        header Cache-Control "public, max-age=3600"
    }

    # Static file caching
    @static {
        path *.css *.js *.png *.jpg *.jpeg *.gif *.ico *.svg *.woff *.woff2 *.ttf *.eot *.map
    }
    header @static Cache-Control "public, max-age=2592000, immutable"

    php_server
    try_files {path} {path}/ /index.php?{query}
    file_server
}
```

## Key Configuration Points

| Feature | Nginx | Caddy |
|---------|-------|-------|
| Clean URLs | `try_files $uri $uri/ /index.php?$args` | `try_files {path} {path}/ /index.php?{query}` |
| PHP redirects | `location ~ ^/(.+)\.php$ { return 301 /$1$is_args$args; }` | `redir @phpfiles /{re.php.1}{query} 301` |
| Static caching | `expires 30d` | `header Cache-Control "public, max-age=2592000"` |
| Sitemap | `alias /path/to/storage/sitemap/sitemap.xml` | `rewrite * /storage/sitemap/sitemap.xml` |

## Why Web Server Handles Redirects

- **Performance**: Redirects at web server level don't invoke PHP
- **SEO**: 301 redirects preserve search engine rankings
- **Standards**: Clean URLs (`/viewtopic` vs `/viewtopic.php`)

## Apache Configuration

Ensure `mod_rewrite` is enabled. Use the provided `.htaccess` file:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect /index.php to /
    RewriteCond %{THE_REQUEST} ^GET\ /index\.php\ HTTP
    RewriteRule ^index\.php$ / [R=301,L]

    # Redirect *.php to clean URLs
    RewriteCond %{THE_REQUEST} ^GET\ /(.+)\.php
    RewriteRule ^(.+)\.php$ /$1 [R=301,L]

    # Front controller
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

## Security Headers

Add these headers for improved security:

```nginx
# Nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

```caddyfile
# Caddy
header {
    X-Content-Type-Options "nosniff"
    X-Frame-Options "SAMEORIGIN"
    X-XSS-Protection "1; mode=block"
    Referrer-Policy "strict-origin-when-cross-origin"
}
```
