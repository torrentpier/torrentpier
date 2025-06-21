# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TorrentPier is a BitTorrent tracker engine written in PHP, designed for hosting BitTorrent communities with forum functionality. The project is undergoing a major 3.0 rewrite, transitioning from legacy code to modern PHP practices. **Backward compatibility is not supported in 3.0** - legacy APIs will break and are not maintained as the focus is on moving forward with clean, modern architecture.

## Technology Stack & Architecture

- **PHP 8.3+** with modern features
- **MySQL/MariaDB/Percona** database
- **Nette Database** with temporary backward-compatible wrapper
- **Composer** for dependency management
- **Custom BitTorrent tracker** implementation

## Key Directory Structure

- `/src/` - Modern PHP classes (PSR-4 autoloaded as `TorrentPier\`)
- `/library/` - Core application logic and legacy code
- `/admin/` - Administrative interface
- `/bt/` - BitTorrent tracker functionality (announce.php, scrape.php)
- `/styles/` - Templates, CSS, JS, images
- `/internal_data/` - Cache, logs, compiled templates
- `/install/` - Installation scripts and configuration examples
- `/migrations/` - Database migration files (Phinx)

## Entry Points & Key Files

- `index.php` - Main forum homepage
- `tracker.php` - Torrent search/browse interface
- `bt/announce.php` - BitTorrent announce endpoint
- `bt/scrape.php` - BitTorrent scrape endpoint
- `admin/index.php` - Administrative panel
- `cron.php` - Background task runner (CLI only)
- `install.php` - Installation script (CLI only)

## Development Commands

### Installation & Setup
```bash
# Automated installation (CLI)
php install.php

# Install dependencies
composer install

# Update dependencies
composer update
```

### Maintenance & Operations
```bash
# Run background maintenance tasks
php cron.php
```

### Code Quality
The project uses **StyleCI** with PSR-2 preset for code style enforcement. StyleCI configuration is in `.styleci.yml` targeting `src/` directory.

## Modern Architecture Components

### Database Layer (`/src/Database/`)
- **Nette Database** replacing legacy SqlDb system
- Modern singleton pattern accessible via `DB()` function
- Support for multiple database connections and debug functionality
- **Breaking changes expected** during 3.0 migration to ORM-style queries

### Cache System (`/src/Cache/`)
- **Unified caching** using Nette Caching internally
- Replaces existing `CACHE()` and $datastore systems
- Supports file, SQLite, memory, and Memcached storage
- **API changes planned** for improved developer experience

### Configuration Management
- Environment-based config with `.env` files
- Modern singleton `Config` class accessible via `config()` function
- **Legacy config access will be removed** in favor of new patterns

## Configuration Files
- `.env` - Environment variables (copy from `.env.example`)
- `library/config.php` - Main application configuration
- `library/config.local.php` - Local configuration overrides
- `composer.json` - Dependencies and PSR-4 autoloading

## Development Workflow

### CI/CD Pipeline
- **GitHub Actions** for automated testing and deployment
- **StyleCI** for code style enforcement
- **Dependabot** for dependency updates
- **FTP deployment** to demo environment

### Installation Methods
1. **Automated**: `php install.php` (recommended)
2. **Composer**: `composer create-project torrentpier/torrentpier`
3. **Manual**: Git clone + `composer install` + database setup

## Database & Schema

- **Database migrations** managed via Phinx in `/migrations/` directory
- Initial schema: `20250619000001_initial_schema.php`
- Initial seed data: `20250619000002_seed_initial_data.php`
- UTF-8 (utf8mb4) character set required
- Multiple database alias support for different components

### Migration Commands
```bash
# Run all pending migrations
php vendor/bin/phinx migrate --configuration=phinx.php

# Check migration status
php vendor/bin/phinx status --configuration=phinx.php

# Mark migrations as applied (for existing installations)
php vendor/bin/phinx migrate --fake --configuration=phinx.php
```

## TorrentPier 3.0 Modernization Strategy

The TorrentPier 3.0 release represents a major architectural shift focused on:

- **Modern PHP practices**: PSR standards, namespaces, autoloading
- **Clean architecture**: Separation of concerns, dependency injection
- **Performance improvements**: Optimized database queries, efficient caching
- **Developer experience**: Better debugging, testing, and maintenance
- **Breaking changes**: Legacy code removal and API modernization

**Important**: TorrentPier 3.0 will introduce breaking changes to achieve these modernization goals. Existing deployments should remain on 2.x versions until they're ready to migrate to the new architecture.

## Migration Path for 3.0

- **Database layer**: Legacy SqlDb calls will be removed, migrate to new Database class
- **Cache system**: Replace existing CACHE() and $datastore calls with new unified API
- **Configuration**: Update legacy global $bb_cfg access to use config() singleton
- **Templates**: Legacy template syntax may be deprecated in favor of modern Twig features
- **Language system**: Update global $lang usage to new Language singleton methods

When working with this codebase, prioritize modern architecture patterns and clean code practices. Focus on the new systems in `/src/` directory rather than maintaining legacy compatibility.
