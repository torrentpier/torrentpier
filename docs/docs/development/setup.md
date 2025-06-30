---
sidebar_position: 1
---

# Development Setup

:::warning Work in Progress
This documentation is currently under development and not yet complete. Some sections may be incomplete or subject to change as the project evolves.
:::

This guide will help you set up a local development environment for TorrentPier.

## Prerequisites

- Git
- PHP 8.4+
- Composer
- Node.js 18+
- MySQL/PostgreSQL
- Redis (optional)

## Quick Start

### 1. Clone and Install

```bash
# Clone the repository
git clone https://github.com/torrentpier/torrentpier.git
cd dexter

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE torrentpier"

# Run migrations
php artisan migrate

# Seed with sample data (optional)
php artisan db:seed
```

### 3. Start Development Server

```bash
# Start Laravel and Vite development servers
composer dev
```

This runs both the PHP server and Vite dev server concurrently.

## Development Tools

### Laravel Telescope

Telescope is pre-installed for debugging:

```bash
# Access at http://localhost:8000/telescope
```

### Code Quality Tools

```bash
# PHP code style
./vendor/bin/pint

# JavaScript/TypeScript linting
npm run lint

# Format code
npm run format

# Type checking
npm run types
```

### Testing

```bash
# Run all tests
composer test

# Run specific test
php artisan test --filter=ExampleTest

# Run with coverage
php artisan test --coverage
```

## IDE Setup

### VS Code

Recommended extensions:
- Laravel Extension Pack
- Inertia.js
- Tailwind CSS IntelliSense
- ESLint
- Prettier

### PHPStorm

- Install Laravel plugin
- Configure code style to use Pint
- Set up Pest integration

## Working with Artisan

### Always Use Artisan Commands

```bash
# Generate model with all resources
php artisan make:model Post --all

# Create controller
php artisan make:controller PostController --resource

# Create React page component manually in resources/js/pages/
```

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Show routes
php artisan route:list

# Show model details
php artisan model:show User

# Interactive tinker shell
php artisan tinker
```

## Frontend Development

### Creating Pages

1. Create page component in `resources/js/pages/`
2. Define route in `routes/web.php`
3. Return Inertia response from controller

Example:

```php
// routes/web.php
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// app/Http/Controllers/PostController.php
public function index()
{
    return Inertia::render('Posts/Index', [
        'posts' => Post::paginate()
    ]);
}
```

```tsx
// resources/js/pages/Posts/Index.tsx
import { Head } from '@inertiajs/react';

export default function Index({ posts }) {
    return (
        <>
            <Head title="Posts" />
            <div>
                {/* Your component */}
            </div>
        </>
    );
}
```

### Using shadcn/ui Components

```tsx
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

export default function MyComponent() {
    return (
        <Card>
            <Button>Click me</Button>
        </Card>
    );
}
```

## Database Development

### Migrations

```bash
# Create migration
php artisan make:migration create_posts_table

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh migration with seed
php artisan migrate:fresh --seed
```

### Working with Models

```php
// Use artisan to generate models
php artisan make:model Post -mfsc

// This creates:
// - Model: app/Models/Post.php
// - Migration: database/migrations/xxx_create_posts_table.php
// - Factory: database/factories/PostFactory.php
// - Seeder: database/seeders/PostSeeder.php
// - Controller: app/Http/Controllers/PostController.php
```

## Git Workflow

### Branching Strategy

```bash
# Create feature branch
git checkout -b feature/user-profile

# Create bugfix branch
git checkout -b bugfix/login-issue

# Push branch
git push -u origin feature/user-profile
```

### Commit Messages

Follow conventional commits:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Code style
- `refactor:` Code refactoring
- `test:` Tests
- `chore:` Maintenance

## Troubleshooting

### Common Issues

1. **npm install fails**
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

2. **Composer memory limit**
   ```bash
   COMPOSER_MEMORY_LIMIT=-1 composer install
   ```

3. **Permission errors**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

4. **Cache issues**
   ```bash
   php artisan optimize:clear
   ```

## Next Steps

- Review Coding Standards
- Learn about Testing
- Understand API Development
