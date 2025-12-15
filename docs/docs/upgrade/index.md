---
sidebar_position: 1
title: Overview
---

# TorrentPier Upgrade Guide

This guide helps you upgrade your TorrentPier installation to the latest version, covering breaking changes, new features, and migration strategies.

## What's New in 3.0

TorrentPier 3.0 is a complete architectural overhaul, moving from legacy PHP to modern Laravel-inspired patterns.

### Key Modernizations

| Component          | Before                     | After                                   |
|--------------------|----------------------------|-----------------------------------------|
| **Architecture**   | Procedural, global state   | DI Container, Service Providers         |
| **Entry Points**   | Multiple files             | Unified `public/index.php` with routing |
| **CLI**            | Loose PHP scripts          | Modern `bull` CLI with 50+ commands     |
| **Database**       | Global `DB()` singleton    | Dependency-injected `Database`          |
| **Caching**        | Global `CACHE()` singleton | DI-enabled `CacheManager`               |
| **Templates**      | eXtreme Styles `.tpl`      | Twig `.twig` with extensions            |
| **Routing**        | File-based controllers     | PSR-7/PSR-15 middleware stack           |
| **Configuration**  | Static files               | Environment + service providers         |
| **Error Handling** | PHP's default              | Whoops + Tracy with custom handlers     |

## Migration Topics

This guide covers migration for each subsystem:

- [Database Migration System](./database-migrations) - Phinx-based schema management
- [Database Layer](./database-layer) - Nette Database integration
- [Cache System](./cache-system) - Unified caching with Nette Caching
- [Configuration](./configuration) - Modern config management
- [Search System](./search-system) - Manticore Search integration
- [Language System](./language-system) - DI container integration
- [Censor System](./censor-system) - Word censoring refactor
- [Select System](./select-system) - Namespace reorganization
- [Development System](./development-system) - Tracy debug bar
- [HTTP Request System](./http-request) - Symfony HTTP Foundation
- [HTTP Helper](./http-helper) - Class rename
- [Application Container](./application-container) - Laravel-style DI
- [Web Server Configuration](./web-server) - Nginx/Caddy setup
- [Breaking Changes](./breaking-changes) - Deprecated patterns and best practices

## Backward Compatibility

**Important**: All existing helper function calls continue to work exactly as before:

```php
// These all still work unchanged
$user = DB()->fetch_row("SELECT * FROM users WHERE id = ?", 123);
$value = CACHE('bb_cache')->get('key');
$siteName = config()->get('sitename');
global $lang; echo $lang['FORUM'];
```

This is an internal modernization that requires **zero code changes** for standard usage.

## Getting Started

1. **For New Installations**: Use the modern migration system automatically
2. **For Existing Installations**: Continue working without changes, adopt modern patterns gradually
3. **For Developers**: Leverage new DI container and service provider patterns

:::tip Recommended Reading Order
Start with [Application Container](./application-container) to understand the new architecture, then explore specific subsystems as needed.
:::

:::warning Production Upgrades
Always test upgrades in a staging environment before applying to production. Keep backups of your database and files.
:::

## Support

For additional support:
- [Official Forum](https://torrentpier.com)
- [GitHub Repository](https://github.com/torrentpier/torrentpier)
- [Documentation](https://docs.torrentpier.com)
