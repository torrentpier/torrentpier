# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TorrentPier is a BitTorrent tracker engine written in PHP, designed for hosting BitTorrent communities with forum functionality. The project is in active modernization, transitioning from legacy code to modern PHP practices while maintaining backward compatibility.

## Technology Stack & Architecture

- **PHP 8.4+** with modern features
- **MySQL/MariaDB/Percona** database
- **Nette Database** with backward-compatible wrapper
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
# Automated installation (downloads Composer if needed, then runs wizard)
php install.php

# Or if Composer is already installed, use Bull CLI directly
php bull app:install

# Manual dependency installation
composer install

# Force reinstallation
php bull app:install --force
```

### Bull CLI (Command Line Interface)
```bash
# List all available commands
php bull list

# Show system information
php bull about

# Cache management
php bull cache:status
php bull cache:clear

# Cron jobs
php bull cron:list
php bull cron:run

# Create new console command
php bull make:command user:create

# Shell completion (bash/zsh/fish)
php bull completion bash > /etc/bash_completion.d/bull
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
- **Nette Database** with full old SqlDb backward compatibility
- Singleton pattern accessible via `DB()` function
- Support for multiple database connections and debug functionality
- Migration path to ORM-style Explorer queries

### Cache System (`/src/Cache/`)
- **Unified caching** using Nette Caching internally
- 100% backward compatibility with existing `CACHE()` and $datastore calls
- Supports file, SQLite, memory, and Memcached storage
- Advanced features: memoization, cache dependencies

### Configuration Management
- Environment-based config with `.env` files
- Singleton `Config` class accessible via `config()` function
- Local overrides supported via `library/config.local.php`

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

### Migration Commands (Bull CLI)
```bash
# Run all pending migrations
php bull migrate

# Check migration status
php bull migrate:status

# Rollback last migration
php bull migrate:rollback

# Mark migrations as applied without running (for existing installations)
php bull migrate --fake

# Create a new migration file
php bull make:migration add_column_to_users
```

## Legacy Compatibility Strategy

The codebase maintains 100% backward compatibility while introducing modern alternatives:

- **Database layer**: Existing old SqlDb calls work while new code can use Nette Database
- **Cache system**: All existing `CACHE()` and $datastore calls preserved while adding modern features
- **Configuration**: Legacy config access maintained alongside new singleton pattern

This approach allows gradual modernization without breaking existing functionality - critical for a mature application with existing deployments.

## Security & Performance

- **Environment-based secrets** management via `.env`
- **CDN/proxy support** (Cloudflare, Fastly)
- **Input sanitization** and CSRF protection
- **Advanced caching** with multiple storage backends
- **Rate limiting** and IP-based restrictions

## BitTorrent Tracker Features

- **BitTorrent v1 & v2** support
- **TorrServer integration** capability
- **Client ban system** for problematic torrent clients
- **Scrape support** for tracker statistics

When working with this codebase, prioritize understanding the legacy compatibility approach and modern architecture patterns. Always test both legacy and modern code paths when making changes to core systems.
