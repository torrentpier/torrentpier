---
sidebar_position: 1
title: Overview
---

# Architecture overview

TorrentPier follows a modular architecture combining legacy compatibility with modern PHP practices.

## Tech stack

### Backend
- **Language**: PHP 8.4+
- **Database**: Nette Database (with legacy SqlDb compatibility)
- **Templates**: Twig (with legacy syntax support)
- **Cache**: Nette Caching (file, SQLite, Memcached)
- **Routing**: League/Route
- **Migrations**: Phinx

### Infrastructure
- **Web server**: Apache / Nginx
- **Database**: MySQL 8.0+ / MariaDB / Percona
- **Cache**: File-based or Memcached

## Directory structure

```
torrentpier/
├── app/                    # Application code
│   ├── Http/
│   │   └── Controllers/   # Web controllers
│   └── Console/
│       └── Commands/      # CLI commands
├── src/                    # Core library classes (PSR-4)
│   ├── Cache/             # Caching system
│   ├── Database/          # Database layer
│   ├── Helpers/           # Utility classes (Slug, etc.)
│   ├── Router/            # Routing and URL handling
│   ├── Template/          # Twig integration
│   └── Tracker/           # BitTorrent tracker
├── library/               # Legacy application logic
│   └── includes/          # Legacy includes
├── config/                # Configuration files
│   ├── config.php         # Main configuration
│   └── config.local.php   # Local overrides (gitignored)
├── database/              # Database files
│   └── migrations/        # Phinx migrations
├── routes/                # Route definitions
│   └── web.php            # Web routes
├── resources/
│   └── views/             # Twig templates
├── public/                # Web root
│   ├── admin/             # Admin panel
│   └── bt/                # Tracker endpoints
├── storage/               # Runtime data
│   ├── app/
│   │   ├── public/        # Web-accessible (avatars, sitemap)
│   │   └── private/       # Protected files (uploads)
│   ├── logs/              # Application logs
│   └── framework/         # Cache, templates, triggers
└── install/               # Installation scripts
```

## Key components

### Database layer

The database system uses Nette Database internally while maintaining full backward compatibility:

```php
// Modern approach
$result = DB()->query('SELECT * FROM users WHERE id = ?', $id);

// Legacy approach (still works)
$result = DB()->sql_query($sql);
```

### Template engine

Twig templates with automatic legacy syntax conversion:

```twig
{# Modern Twig syntax #}
{% if user.is_admin %}
    {{ user.name }}
{% endif %}
```

```html
<!-- Legacy syntax (auto-converted) -->
<!-- IF IS_ADMIN -->
    {USERNAME}
<!-- ENDIF -->
```

### Cache system

Unified caching with multiple backends:

```php
// Using cache
$data = CACHE('my_key', function() {
    return expensive_operation();
}, 3600);
```

## BitTorrent tracker

### Endpoints
- `bt/announce.php` — peer announcements
- `bt/scrape.php` — torrent statistics

### Features
- BitTorrent v1 & v2 protocol support
- Compact peer responses
- IPv4 and IPv6 support
- Anti-cheat protection
- Ratio enforcement

## Security

- Input sanitization
- CSRF protection
- SQL injection prevention (prepared statements)
- XSS protection
- Rate limiting
- IP-based restrictions

## Performance

### Caching strategy
- Database query caching
- Template compilation caching
- Configuration caching

### Database optimization
- Indexed columns for tracker queries
- Optimized peer management
- Connection pooling support

## Configuration

Main configuration in `config/config.php` with environment overrides via `.env`:

```php
// Access configuration
$value = config('site.name');
```

## Extending TorrentPier

### Adding routes

Routes are defined using League/Route in `routes/web.php`. TorrentPier supports SEO-friendly semantic URLs:

```php
// Generate URLs in PHP
url()->topic($id, $title);     // /topic/my-topic.123/
url()->forum($id, $name);      // /forum/hd-video.5/
url()->profile($id, $username); // /profile/admin.2/
```

```twig
{# Generate URLs in templates #}
{{ url.topic(t.TOPIC_ID, t.TOPIC_TITLE) }}
```

See [Semantic URLs](./semantic-urls.md) for detailed documentation.

### Creating templates

Place Twig templates in `resources/views/{style_name}/` (e.g., `resources/views/default/`, `resources/views/momo/`).

### Database changes

Use Phinx migrations in the `database/migrations/` directory.
