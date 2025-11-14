# Mod Development Guide for TorrentPier v2.9

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Creating Your First Mod](#creating-your-first-mod)
4. [Understanding Hooks](#understanding-hooks)
5. [Working with Database](#working-with-database)
6. [Templates and UI](#templates-and-ui)
7. [AJAX and JavaScript](#ajax-and-javascript)
8. [Configuration and Settings](#configuration-and-settings)
9. [Localization](#localization)
10. [Testing Your Mod](#testing-your-mod)
11. [Advanced Features](#advanced-features)
12. [Best Practices](#best-practices)
13. [Publishing Your Mod](#publishing-your-mod)

---

## Introduction

Welcome to mod development for TorrentPier v2.9! This guide will teach you how to create mods using the new hook-based architecture.

### What You'll Learn

- ✅ Creating isolated, maintainable mods
- ✅ Using hooks to extend TorrentPier functionality
- ✅ Building admin interfaces
- ✅ Working with database and templates
- ✅ Following best practices and security guidelines

### Prerequisites

- PHP 8.2+ knowledge
- Basic understanding of TorrentPier architecture
- Familiarity with Git (recommended)
- Development environment with TorrentPier v2.9+ installed

### Philosophy

**The golden rule:** Never modify core files. Use hooks, events, and filters to extend functionality.

```
❌ Bad: Editing /library/includes/functions.php
✅ Good: Creating /mods/yourmod/Mod.php with hooks
```

---

## Getting Started

### Development Environment Setup

1. **Install TorrentPier v2.9+**
```bash
composer create-project torrentpier/torrentpier mysite
cd mysite
php install.php
```

2. **Enable debug mode**
```bash
# Edit .env
APP_DEBUG=true
APP_ENV=development
LOG_LEVEL=debug
```

3. **Set up mod development directory**
```bash
mkdir -p mods/mymod
cd mods/mymod
```

### Required Tools

```bash
# Install PHP CodeSniffer (optional, for code quality)
composer global require squizlabs/php_codesniffer

# Install PHPUnit (optional, for testing)
composer require --dev phpunit/phpunit
```

### Mod Directory Structure

```
mods/
└── mymod/
    ├── manifest.json         # Mod metadata (required)
    ├── Mod.php              # Main mod class (required)
    ├── README.md            # Documentation
    ├── LICENSE              # License file
    ├── config.php           # Default configuration
    ├── hooks.php            # Hook registrations (optional)
    ├── src/                 # Additional PHP classes
    │   ├── Models/
    │   ├── Controllers/
    │   └── Services/
    ├── migrations/          # Database migrations
    │   └── 001_create_tables.sql
    ├── templates/           # Template files
    │   ├── admin/
    │   └── public/
    ├── assets/              # CSS, JS, images
    │   ├── css/
    │   ├── js/
    │   └── img/
    ├── ajax/                # AJAX handlers
    │   └── handler.php
    ├── lang/                # Translations
    │   ├── en/
    │   └── ru/
    └── tests/               # Unit tests (optional)
        └── ModTest.php
```

---

## Creating Your First Mod

Let's create a simple "Welcome Message" mod that displays a custom message on the homepage.

### Step 1: Create Manifest

Create `/mods/welcome-message/manifest.json`:

```json
{
  "id": "welcome-message",
  "name": "Welcome Message",
  "description": "Displays a customizable welcome message on the homepage",
  "version": "1.0.0",
  "author": "Your Name",
  "author_email": "you@example.com",
  "author_url": "https://yoursite.com",
  "license": "MIT",
  "requires": {
    "torrentpier": ">=3.0.0",
    "php": ">=8.2.0"
  },
  "dependencies": [],
  "hooks": [
    {
      "name": "template.index_body.before",
      "description": "Adds welcome message before main content"
    }
  ],
  "config": {
    "message": "Welcome to our tracker!",
    "show_to_guests": true,
    "show_to_users": false
  }
}
```

**Manifest fields explained:**
- `id`: Unique identifier (alphanumeric + hyphens)
- `name`: Display name shown in admin panel
- `version`: Semantic version (major.minor.patch)
- `requires`: Minimum version requirements
- `hooks`: List of hooks your mod uses (for documentation)
- `config`: Default configuration values

### Step 2: Create Main Mod Class

Create `/mods/welcome-message/Mod.php`:

```php
<?php
/**
 * Welcome Message Mod
 *
 * @package TorrentPier\Mod\WelcomeMessage
 * @author Your Name
 * @license MIT
 */

namespace TorrentPier\Mod\WelcomeMessage;

use TorrentPier\Mod\AbstractMod;
use TorrentPier\Mod\Hook;

/**
 * Main mod class
 *
 * This class extends AbstractMod and implements the boot() method
 * where all hook registrations and initialization happens.
 */
class Mod extends AbstractMod
{
    /**
     * Boot the mod
     *
     * This method is called when the mod is loaded.
     * Register all hooks and initialize mod functionality here.
     *
     * @return void
     */
    public function boot()
    {
        // Register a filter to add welcome message to homepage
        Hook::add_filter('template.index_body.before', [$this, 'addWelcomeMessage'], 10, 1);
    }

    /**
     * Add welcome message to the page
     *
     * @param string $content Existing content
     * @return string Modified content with welcome message
     */
    public function addWelcomeMessage($content)
    {
        global $userdata;

        // Check if we should show message
        $is_guest = !$userdata['session_logged_in'];

        if ($is_guest && !$this->config('show_to_guests', true)) {
            return $content;
        }

        if (!$is_guest && !$this->config('show_to_users', false)) {
            return $content;
        }

        // Get message from config
        $message = $this->config('message', 'Welcome to our tracker!');

        // Build HTML
        $welcome_html = sprintf(
            '<div class="welcome-message">%s</div>',
            htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        );

        // Return content with welcome message prepended
        return $welcome_html . $content;
    }
}
```

**Key concepts:**
- **Namespace**: `TorrentPier\Mod\YourModName`
- **Extends AbstractMod**: Provides helper methods
- **boot() method**: Entry point for mod initialization
- **Hook registration**: `Hook::add_filter()` to modify content
- **Config access**: `$this->config('key', 'default')`

### Step 3: Test Your Mod

```bash
# Validate mod structure
php mod.php validate welcome-message

# Install mod
php mod.php install welcome-message

# Check if active
php mod.php list --active
```

Visit your site's homepage - you should see the welcome message!

### Step 4: Add Styling (Optional)

Create `/mods/welcome-message/assets/css/style.css`:

```css
.welcome-message {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 18px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.welcome-message:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}
```

Register CSS in Mod.php:

```php
public function boot()
{
    Hook::add_filter('template.index_body.before', [$this, 'addWelcomeMessage'], 10, 1);

    // Add CSS to page head
    Hook::add_action('template.head', [$this, 'addStyles']);
}

public function addStyles()
{
    echo '<link rel="stylesheet" href="' . $this->asset('css/style.css') . '">';
}
```

**Congratulations!** You've created your first mod. 🎉

---

## Understanding Hooks

Hooks are the core of TorrentPier's extensibility. There are two types: **Actions** and **Filters**.

### Actions vs Filters

**Actions** execute code at specific points:
```php
// Action: Do something when event occurs
Hook::add_action('user.after_login', function($user_id) {
    // Log user login
    log_event("User $user_id logged in");
});

// Trigger action
Hook::do_action('user.after_login', $user_id);
```

**Filters** modify data:
```php
// Filter: Modify username display
Hook::add_filter('user.display_name', function($username, $user) {
    return strtoupper($username); // Convert to uppercase
}, 10, 2);

// Apply filter
$username = Hook::apply_filter('user.display_name', $username, $user);
```

### Hook Anatomy

```php
Hook::add_filter(
    'hook_name',           // Hook identifier
    [$this, 'callback'],   // Callback function
    10,                    // Priority (lower = earlier, default 10)
    2                      // Number of arguments callback accepts
);
```

**Priority matters:**
- `1-5`: Critical early execution (validation, security)
- `10`: Default (most mods)
- `15-20`: Display modifications
- `50+`: Logging, analytics

### Finding Available Hooks

```bash
# List all available hooks
php mod.php hooks list

# Search for specific hooks
php mod.php hooks search user

# Get hook details
php mod.php hooks info user.after_login
```

**Documentation:** See [API-REFERENCE.md](./API-REFERENCE.md) for complete hook list.

### Creating Custom Hooks in Your Mod

Your mod can provide hooks for other mods to use:

```php
class Mod extends AbstractMod
{
    public function processData($data)
    {
        // Allow other mods to modify data before processing
        $data = Hook::apply_filter('welcome_message.before_process', $data);

        // Process data
        $result = $this->doProcessing($data);

        // Allow other mods to react to processing
        Hook::do_action('welcome_message.after_process', $result, $data);

        return $result;
    }
}
```

Other mods can then hook into your mod:

```php
// Another mod hooking into welcome-message mod
Hook::add_filter('welcome_message.before_process', function($data) {
    $data['extra_field'] = 'value';
    return $data;
});
```

---

## Working with Database

### Creating Tables with Migrations

**How migrations work:**
- All mod migrations are tracked in a single `bb_mod_migrations` table
- Each mod has migrations in `mods/{mod_id}/migrations/` directory
- Migrations are simple SQL files executed in order
- Execution is tracked by (`mod_id`, `version`) composite key

Create `/mods/welcome-message/migrations/001_create_welcome_logs.sql`:

```sql
-- Migration: 001_create_welcome_logs.sql
-- Description: Create table for logging welcome message views
-- Date: 2025-01-15

CREATE TABLE IF NOT EXISTS `bb_welcome_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) NOT NULL,
    `viewed_at` INT NOT NULL,
    KEY `user_id` (`user_id`),
    KEY `viewed_at` (`viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Logs when users view welcome message';
```

**Migration naming:** `XXX_description.sql` where XXX is sequential number (001, 002, etc.)

**Migrations run automatically on mod activation:**
```bash
php mod.php activate welcome-message
# ✓ Running migrations...
# ✓ Executed 001_create_welcome_logs.sql
# ✓ Mod activated successfully
```

**Migration tracking in database:**
```sql
-- bb_mod_migrations table structure:
-- mod_id | version | migration_name              | start_time | end_time
-- --------|---------|-----------------------------|--------------------|----------
-- welcome | 001     | 001_create_welcome_logs.sql | 2025-01-15 10:00:00| 2025-01-15 10:00:01
```

**Rollback on deactivation (optional):**
```bash
php mod.php deactivate welcome-message --rollback-migrations
# ⚠️  This will DROP tables created by migrations!
```

### Querying Database

TorrentPier uses Nette Database. Access via `DB()` function:

```php
public function logView($user_id, $ip_address)
{
    // Insert record
    DB()->query("
        INSERT INTO bb_welcome_logs (user_id, ip_address, viewed_at)
        VALUES (?, ?, ?)
    ", $user_id, $ip_address, time());
}

public function getViewCount($user_id)
{
    // Fetch single value
    return DB()->fetchField("
        SELECT COUNT(*) FROM bb_welcome_logs WHERE user_id = ?
    ", $user_id);
}

public function getRecentViews($limit = 10)
{
    // Fetch multiple rows
    return DB()->fetch_rowset("
        SELECT * FROM bb_welcome_logs
        ORDER BY viewed_at DESC
        LIMIT ?
    ", $limit);
}
```

### Using Database Helper Methods

AbstractMod provides convenient database methods:

```php
// Get database instance
$db = $this->db();

// Simple select
$user = $this->db()->fetch_row("
    SELECT * FROM bb_users WHERE user_id = ?
", $user_id);

// Insert and get ID
$insert_id = $this->db()->insert('bb_welcome_logs', [
    'user_id' => $user_id,
    'ip_address' => $ip,
    'viewed_at' => time()
]);

// Update records
$affected = $this->db()->update('bb_welcome_logs',
    ['viewed_at' => time()],         // Set
    ['user_id' => $user_id]          // Where
);

// Delete records
$deleted = $this->db()->delete('bb_welcome_logs',
    ['user_id' => $user_id]
);
```

### Caching Database Results

Always cache expensive queries:

```php
public function getPopularMessages()
{
    // Cache for 1 hour
    return $this->cache('popular_messages', function() {
        return DB()->fetch_rowset("
            SELECT message, COUNT(*) as views
            FROM bb_welcome_logs
            GROUP BY message
            ORDER BY views DESC
            LIMIT 10
        ");
    }, 3600);
}

// Clear cache when data changes
public function updateMessage($message)
{
    $this->config()->set('message', $message);
    $this->cache()->delete('popular_messages');
}
```

---

## Templates and UI

### Creating Templates

Create `/mods/welcome-message/templates/admin_settings.tpl`:

```html
<!-- BEGIN admin_settings -->
<div class="mod-settings">
    <h2>{L_WELCOME_MESSAGE_SETTINGS}</h2>

    <form method="post" action="{S_ACTION}">
        <div class="form-group">
            <label for="message">{L_MESSAGE}</label>
            <textarea id="message" name="message" rows="3">{MESSAGE}</textarea>
            <small>{L_MESSAGE_HELP}</small>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="show_to_guests" {SHOW_TO_GUESTS_CHECKED}>
                {L_SHOW_TO_GUESTS}
            </label>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="show_to_users" {SHOW_TO_USERS_CHECKED}>
                {L_SHOW_TO_USERS}
            </label>
        </div>

        <button type="submit" class="btn btn-primary">{L_SAVE}</button>
    </form>
</div>
<!-- END admin_settings -->
```

### Rendering Templates

```php
public function showAdminPage()
{
    // Prepare template variables
    $template_vars = [
        'L_WELCOME_MESSAGE_SETTINGS' => $this->lang('SETTINGS_TITLE'),
        'L_MESSAGE' => $this->lang('MESSAGE_LABEL'),
        'L_MESSAGE_HELP' => $this->lang('MESSAGE_HELP'),
        'L_SHOW_TO_GUESTS' => $this->lang('SHOW_TO_GUESTS'),
        'L_SHOW_TO_USERS' => $this->lang('SHOW_TO_USERS'),
        'L_SAVE' => $this->lang('SAVE_BUTTON'),
        'S_ACTION' => '/admin/mod-settings?mod=welcome-message',
        'MESSAGE' => $this->config('message', ''),
        'SHOW_TO_GUESTS_CHECKED' => $this->config('show_to_guests', true) ? 'checked' : '',
        'SHOW_TO_USERS_CHECKED' => $this->config('show_to_users', false) ? 'checked' : '',
    ];

    // Render template
    $this->template('admin_settings', $template_vars);
}
```

### Template Overrides

Users can override your templates:

**Your template:** `/mods/welcome-message/templates/message.tpl`

**User override:** `/styles/templates/mods/welcome-message/message.tpl`

The system automatically uses user override if it exists.

### Inline Templates

For simple HTML, use inline templates:

```php
public function displayMessage()
{
    $html = <<<HTML
    <div class="alert alert-info">
        <strong>{$this->lang('NOTICE')}</strong>
        {$this->config('message')}
    </div>
    HTML;

    echo $html;
}
```

---

## AJAX and JavaScript

### Creating AJAX Handler

1. **Register in manifest.json:**
```json
{
  "ajax_handlers": [
    {
      "name": "welcome_dismiss",
      "method": "handleDismiss",
      "permission": "user"
    }
  ]
}
```

2. **Implement in Mod.php:**
```php
public function boot()
{
    Hook::add_action('ajax.welcome_dismiss', [$this, 'handleDismiss']);
}

public function handleDismiss()
{
    // Verify user is logged in
    $this->requirePermission('user');

    // Get user ID
    global $userdata;
    $user_id = $userdata['user_id'];

    // Save dismissal to database
    DB()->query("
        INSERT INTO bb_welcome_dismissed (user_id, dismissed_at)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE dismissed_at = ?
    ", $user_id, time(), time());

    // Return JSON response
    ajax_response([
        'success' => true,
        'message' => $this->lang('DISMISSED_SUCCESS')
    ]);
}
```

3. **Create JavaScript file** `/mods/welcome-message/assets/js/script.js`:
```javascript
(function() {
    'use strict';

    // Dismiss button click handler
    document.addEventListener('DOMContentLoaded', function() {
        const dismissBtn = document.querySelector('.welcome-message .dismiss-btn');

        if (dismissBtn) {
            dismissBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dismissWelcomeMessage();
            });
        }
    });

    function dismissWelcomeMessage() {
        // Call AJAX handler
        ajax_request('welcome_dismiss', {}, function(response) {
            if (response.success) {
                // Hide message with animation
                const welcomeEl = document.querySelector('.welcome-message');
                welcomeEl.style.transition = 'opacity 0.3s ease';
                welcomeEl.style.opacity = '0';

                setTimeout(function() {
                    welcomeEl.remove();
                }, 300);
            } else {
                alert('Error: ' + (response.message || 'Unknown error'));
            }
        });
    }
})();
```

4. **Include JavaScript in page:**
```php
public function boot()
{
    Hook::add_action('template.head', [$this, 'addScripts']);
}

public function addScripts()
{
    echo '<script src="' . $this->asset('js/script.js') . '"></script>';
}
```

### AJAX Security

Always validate and sanitize:

```php
public function handleAjaxRequest()
{
    // 1. Check permission
    $this->requirePermission('user');

    // 2. Validate CSRF token (if applicable)
    if (!$this->verifyCsrfToken(request_var('token', ''))) {
        ajax_error('Invalid CSRF token', 403);
    }

    // 3. Sanitize input
    $message_id = (int) request_var('message_id', 0);
    if ($message_id <= 0) {
        ajax_error('Invalid message ID', 400);
    }

    // 4. Check ownership/permissions
    if (!$this->userCanEdit($message_id)) {
        ajax_error('Permission denied', 403);
    }

    // 5. Process request
    // ...
}
```

---

## Configuration and Settings

### Default Configuration

Define defaults in `/mods/welcome-message/config.php`:

```php
<?php

return [
    'welcome_message' => [
        // Display settings
        'message' => 'Welcome to our BitTorrent tracker!',
        'show_to_guests' => true,
        'show_to_users' => false,
        'dismissible' => true,

        // Style settings
        'background_color' => '#667eea',
        'text_color' => '#ffffff',
        'border_radius' => 8,

        // Behavior settings
        'auto_hide_after' => 0,  // seconds, 0 = never
        'cookie_lifetime' => 86400,  // 24 hours

        // Advanced
        'cache_duration' => 3600,
        'log_views' => false,
    ]
];
```

### Accessing Configuration

```php
// Get single value with default
$message = $this->config('message', 'Default message');

// Get nested value
$color = $this->config('background_color', '#667eea');

// Get all config
$all_config = $this->config();

// Check if config exists
if ($this->hasConfig('log_views')) {
    // ...
}
```

### Saving Configuration

```php
public function saveSettings($settings)
{
    // Validate settings
    $message = trim($settings['message'] ?? '');
    if (empty($message)) {
        throw new \Exception($this->lang('ERROR_EMPTY_MESSAGE'));
    }

    // Save each setting
    $this->config()->set('message', $message);
    $this->config()->set('show_to_guests', (bool) ($settings['show_to_guests'] ?? false));
    $this->config()->set('show_to_users', (bool) ($settings['show_to_users'] ?? false));

    // Clear cache after config change
    $this->cache()->delete('welcome_message_html');

    return true;
}
```

### Admin Settings Page

```php
public function boot()
{
    if (is_admin()) {
        Hook::add_action('admin.page.welcome-message', [$this, 'showAdminPage']);
        Hook::add_action('admin.save.welcome-message', [$this, 'saveAdminPage']);
    }
}

public function showAdminPage()
{
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->saveSettings($_POST);
        $this->flash('success', $this->lang('SETTINGS_SAVED'));
    }

    // Display form
    $this->template('admin_settings', [
        'MESSAGE' => $this->config('message'),
        'SHOW_TO_GUESTS' => $this->config('show_to_guests', true),
        'SHOW_TO_USERS' => $this->config('show_to_users', false),
    ]);
}
```

Register admin page in manifest.json:
```json
{
  "admin_pages": [
    {
      "slug": "welcome-message",
      "title": "Welcome Message Settings",
      "capability": "admin",
      "icon": "💬"
    }
  ]
}
```

---

## Localization

### Creating Language Files

Create `/mods/welcome-message/lang/en/main.php`:

```php
<?php

return [
    // General
    'MOD_NAME' => 'Welcome Message',
    'MOD_DESCRIPTION' => 'Displays customizable welcome message',

    // Settings
    'SETTINGS_TITLE' => 'Welcome Message Settings',
    'MESSAGE_LABEL' => 'Welcome Message',
    'MESSAGE_HELP' => 'The message to display on the homepage',
    'SHOW_TO_GUESTS' => 'Show to guests',
    'SHOW_TO_USERS' => 'Show to logged in users',
    'SAVE_BUTTON' => 'Save Settings',

    // Messages
    'SETTINGS_SAVED' => 'Settings saved successfully',
    'ERROR_EMPTY_MESSAGE' => 'Message cannot be empty',
    'DISMISSED_SUCCESS' => 'Welcome message dismissed',

    // Permissions
    'PERMISSION_DENIED' => 'You do not have permission to perform this action',
];
```

Create `/mods/welcome-message/lang/ru/main.php`:

```php
<?php

return [
    // General
    'MOD_NAME' => 'Приветственное сообщение',
    'MOD_DESCRIPTION' => 'Отображает настраиваемое приветственное сообщение',

    // Settings
    'SETTINGS_TITLE' => 'Настройки приветственного сообщения',
    'MESSAGE_LABEL' => 'Приветственное сообщение',
    'MESSAGE_HELP' => 'Сообщение для отображения на главной странице',
    'SHOW_TO_GUESTS' => 'Показывать гостям',
    'SHOW_TO_USERS' => 'Показывать авторизованным пользователям',
    'SAVE_BUTTON' => 'Сохранить настройки',

    // Messages
    'SETTINGS_SAVED' => 'Настройки успешно сохранены',
    'ERROR_EMPTY_MESSAGE' => 'Сообщение не может быть пустым',
    'DISMISSED_SUCCESS' => 'Приветственное сообщение скрыто',

    // Permissions
    'PERMISSION_DENIED' => 'У вас нет прав для выполнения этого действия',
];
```

### Using Translations

```php
// Get translation
$title = $this->lang('SETTINGS_TITLE');

// With sprintf formatting
$message = $this->lang('VIEWS_COUNT', $count); // "Viewed %d times"
// Result: "Viewed 42 times"

// Fallback if key doesn't exist
$text = $this->lang('SOME_KEY', 'Default value');

// Check if translation exists
if ($this->hasLang('SOME_KEY')) {
    // ...
}
```

### Language Selection

The system automatically loads the correct language based on user preference:

1. User's selected language
2. Board default language
3. English (fallback)

```php
// Get current language code
$lang_code = $this->getCurrentLanguage(); // e.g., 'ru', 'en'

// Load specific language file
$this->loadLanguage('admin', 'ru');
```

---

## Testing Your Mod

### Unit Testing

Create `/mods/welcome-message/tests/ModTest.php`:

```php
<?php

namespace TorrentPier\Mod\WelcomeMessage\Tests;

use PHPUnit\Framework\TestCase;
use TorrentPier\Mod\WelcomeMessage\Mod;

class ModTest extends TestCase
{
    private $mod;

    protected function setUp(): void
    {
        $this->mod = new Mod();
    }

    public function testAddWelcomeMessageForGuests()
    {
        // Set config
        $this->mod->config()->set('message', 'Test message');
        $this->mod->config()->set('show_to_guests', true);

        // Test
        $content = '';
        $result = $this->mod->addWelcomeMessage($content);

        $this->assertStringContainsString('Test message', $result);
        $this->assertStringContainsString('welcome-message', $result);
    }

    public function testConfigDefaults()
    {
        $message = $this->mod->config('message');
        $this->assertNotEmpty($message);

        $show_guests = $this->mod->config('show_to_guests', true);
        $this->assertTrue($show_guests);
    }
}
```

Run tests:
```bash
cd mods/welcome-message
vendor/bin/phpunit tests/
```

### Manual Testing Checklist

```markdown
## Welcome Message Mod - Test Checklist

### Installation
- [ ] `php mod.php validate welcome-message` passes
- [ ] `php mod.php install welcome-message` succeeds
- [ ] Database tables created correctly
- [ ] No PHP errors in log

### Functionality
- [ ] Message displays on homepage
- [ ] Message respects guest/user visibility settings
- [ ] Dismiss button works (if dismissible)
- [ ] Message doesn't show after dismissal
- [ ] Custom message from settings displays correctly

### Admin Panel
- [ ] Settings page loads
- [ ] Can save settings
- [ ] Settings persist after save
- [ ] Validation works (empty message rejected)

### Compatibility
- [ ] Works with other mods installed
- [ ] Doesn't break site functionality
- [ ] No JavaScript console errors
- [ ] Responsive design on mobile

### Performance
- [ ] Page load time acceptable (<100ms impact)
- [ ] Database queries optimized (use caching)
- [ ] No N+1 query problems

### Security
- [ ] XSS protection (message HTML-escaped)
- [ ] CSRF protection on forms
- [ ] Permission checks work
- [ ] SQL injection protected (prepared statements)

### Uninstallation
- [ ] `php mod.php uninstall welcome-message` works
- [ ] Tables removed (or kept with --keep-data)
- [ ] Site still works after uninstall
```

---

## Advanced Features

### Cron Jobs / Scheduled Tasks

Register in manifest.json:
```json
{
  "cron_jobs": [
    {
      "name": "cleanup_old_logs",
      "schedule": "daily",
      "method": "cleanupOldLogs",
      "description": "Remove logs older than 30 days"
    }
  ]
}
```

Implement in Mod.php:
```php
public function cleanupOldLogs()
{
    $cutoff = time() - (30 * 86400); // 30 days ago

    $deleted = DB()->query("
        DELETE FROM bb_welcome_logs
        WHERE viewed_at < ?
    ", $cutoff);

    $this->log("Cleaned up {$deleted} old log entries");
}
```

Schedule options: `hourly`, `daily`, `weekly`, `monthly`

### Event Listeners

Create complex workflows with event listeners:

```php
public function boot()
{
    // Listen to multiple related events
    Hook::add_action('user.after_register', [$this, 'onUserRegister']);
    Hook::add_action('user.after_login', [$this, 'onUserLogin']);
    Hook::add_action('user.before_logout', [$this, 'onUserLogout']);
}

public function onUserRegister($user_id)
{
    // Show special welcome to new users
    $this->flash('info', $this->lang('WELCOME_NEW_USER'));
}

public function onUserLogin($user_id)
{
    // Track login
    $this->logView($user_id, get_ip_address());
}

public function onUserLogout($user_id)
{
    // Clear user-specific cache
    $this->cache()->delete("user_{$user_id}_welcome");
}
```

### Permissions and Capabilities

```php
public function boot()
{
    // Define custom capability
    $this->addCapability('manage_welcome_message', [
        'admin' => true,
        'moderator' => true,
        'user' => false
    ]);
}

public function showAdminPage()
{
    // Check permission
    if (!$this->can('manage_welcome_message')) {
        $this->abort(403, $this->lang('PERMISSION_DENIED'));
    }

    // Display page
    // ...
}

// Or use helper method
public function saveSettings()
{
    $this->requireCapability('manage_welcome_message');
    // ...
}
```

### Inter-Mod Communication

Mods can communicate via hooks:

**Mod A provides data:**
```php
// In ModA
Hook::do_action('moda.data_ready', $data);
```

**Mod B consumes data:**
```php
// In ModB
public function boot()
{
    Hook::add_action('moda.data_ready', [$this, 'handleDataFromModA']);
}

public function handleDataFromModA($data)
{
    // Process data from Mod A
}
```

Check if another mod is active:
```php
if ($this->isModActive('karma-system')) {
    // Integrate with Karma System
    $karma = ModManager::getModInstance('karma-system')->getKarma($user_id);
}
```

### Custom Database Models

Create `/mods/welcome-message/src/Models/WelcomeLog.php`:

```php
<?php

namespace TorrentPier\Mod\WelcomeMessage\Models;

class WelcomeLog
{
    public static function create($user_id, $ip_address)
    {
        return DB()->insert('bb_welcome_logs', [
            'user_id' => $user_id,
            'ip_address' => $ip_address,
            'viewed_at' => time()
        ]);
    }

    public static function findByUser($user_id)
    {
        return DB()->fetch_rowset("
            SELECT * FROM bb_welcome_logs
            WHERE user_id = ?
            ORDER BY viewed_at DESC
        ", $user_id);
    }

    public static function getViewCount($user_id = null)
    {
        $sql = "SELECT COUNT(*) FROM bb_welcome_logs";
        $params = [];

        if ($user_id !== null) {
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;
        }

        return DB()->fetchField($sql, ...$params);
    }

    public static function deleteOlderThan($days)
    {
        $cutoff = time() - ($days * 86400);

        return DB()->query("
            DELETE FROM bb_welcome_logs
            WHERE viewed_at < ?
        ", $cutoff);
    }
}
```

Use in Mod.php:
```php
use TorrentPier\Mod\WelcomeMessage\Models\WelcomeLog;

public function logView($user_id)
{
    WelcomeLog::create($user_id, get_ip_address());
}

public function getUserViews($user_id)
{
    return WelcomeLog::findByUser($user_id);
}
```

---

## Best Practices

### Code Quality

1. **Follow PSR-12 coding standards:**
```php
// ✅ Good
class Mod extends AbstractMod
{
    public function boot()
    {
        Hook::add_filter('template.index', [$this, 'modify'], 10, 1);
    }

    public function modify($content)
    {
        return $content . $this->getExtraContent();
    }
}

// ❌ Bad
class Mod extends AbstractMod {
  function boot() {
    Hook::add_filter('template.index',array($this,'modify'),10,1);
  }
  function modify($content){return $content.$this->getExtraContent();}
}
```

2. **Use type hints:**
```php
// ✅ Good
public function processUser(int $user_id): array
{
    // ...
}

// ❌ Bad
public function processUser($user_id)
{
    // ...
}
```

3. **Document your code:**
```php
/**
 * Calculate user karma based on various factors
 *
 * @param int $user_id User ID
 * @param bool $include_pending Include pending karma
 * @return int Total karma points
 * @throws \Exception If user not found
 */
public function calculateKarma(int $user_id, bool $include_pending = false): int
{
    // ...
}
```

### Security

1. **Always sanitize user input:**
```php
// ✅ Good
$message = request_var('message', '', true); // true = sanitize
$message_id = (int) request_var('id', 0);

// ❌ Bad
$message = $_POST['message'];
$message_id = $_GET['id'];
```

2. **Use prepared statements:**
```php
// ✅ Good
DB()->query("SELECT * FROM users WHERE user_id = ?", $user_id);

// ❌ Bad
DB()->query("SELECT * FROM users WHERE user_id = $user_id");
```

3. **Escape output:**
```php
// ✅ Good
echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// ❌ Bad
echo $message;
```

4. **Check permissions:**
```php
// ✅ Good
public function deleteMessage($id)
{
    $this->requirePermission('admin');
    // ...
}

// ❌ Bad
public function deleteMessage($id)
{
    // No permission check
    // ...
}
```

### Performance

1. **Cache expensive operations:**
```php
// ✅ Good
public function getStats()
{
    return $this->cache('stats', function() {
        return $this->calculateStats();
    }, 3600);
}

// ❌ Bad
public function getStats()
{
    return $this->calculateStats(); // Runs every time
}
```

2. **Avoid N+1 queries:**
```php
// ✅ Good
$user_ids = array_column($posts, 'user_id');
$karmas = $this->getKarmasBulk($user_ids);

foreach ($posts as $post) {
    $post['karma'] = $karmas[$post['user_id']] ?? 0;
}

// ❌ Bad
foreach ($posts as $post) {
    $post['karma'] = $this->getKarma($post['user_id']); // Query per post!
}
```

3. **Lazy load when possible:**
```php
// ✅ Good
public function boot()
{
    // Only register heavy hooks if feature is enabled
    if ($this->config('advanced_features', false)) {
        Hook::add_filter('complex.processing', [$this, 'heavyOperation']);
    }
}
```

### Maintainability

1. **Keep functions small and focused:**
```php
// ✅ Good
public function displayMessage()
{
    if (!$this->shouldShow()) {
        return '';
    }

    $message = $this->getMessage();
    return $this->renderTemplate($message);
}

// ❌ Bad
public function displayMessage()
{
    // 200 lines of mixed logic
}
```

2. **Use constants for magic values:**
```php
// ✅ Good
class Mod extends AbstractMod
{
    private const MAX_MESSAGE_LENGTH = 500;
    private const CACHE_TTL = 3600;

    public function validateMessage($message)
    {
        return strlen($message) <= self::MAX_MESSAGE_LENGTH;
    }
}

// ❌ Bad
public function validateMessage($message)
{
    return strlen($message) <= 500; // What is 500?
}
```

3. **Separate concerns:**
```php
// ✅ Good: Separate files
// /src/Services/MessageValidator.php
// /src/Services/MessageRenderer.php
// /src/Models/Message.php

// ❌ Bad: Everything in Mod.php
```

### Compatibility

1. **Declare dependencies:**
```json
{
  "requires": {
    "torrentpier": ">=3.0.0",
    "php": ">=8.2.0"
  },
  "dependencies": ["karma-system"]
}
```

2. **Check for conflicts:**
```php
public function boot()
{
    if ($this->isModActive('conflicting-mod')) {
        $this->log('Warning: May conflict with conflicting-mod');
        // Adjust behavior or disable features
    }
}
```

3. **Provide migration path:**
```php
public function onInstall()
{
    // Check for old version data
    if ($this->hasOldData()) {
        $this->migrateFromOldVersion();
    }
}
```

---

## Publishing Your Mod

### Pre-Publishing Checklist

```markdown
## Pre-Publishing Checklist

### Code Quality
- [ ] Code follows PSR-12 standards
- [ ] All functions documented with PHPDoc
- [ ] No debug code (var_dump, print_r, etc.)
- [ ] Error handling implemented
- [ ] Input validation on all user input

### Testing
- [ ] All features tested manually
- [ ] Works on clean TorrentPier install
- [ ] Tested with other popular mods
- [ ] No PHP errors or warnings
- [ ] No JavaScript console errors

### Documentation
- [ ] README.md with installation instructions
- [ ] CHANGELOG.md with version history
- [ ] LICENSE file included
- [ ] Screenshots/demo (if applicable)
- [ ] User documentation complete

### Security
- [ ] No SQL injection vulnerabilities
- [ ] XSS protection on all output
- [ ] CSRF protection on forms
- [ ] Permission checks on sensitive operations
- [ ] No hardcoded credentials

### Performance
- [ ] Database queries optimized
- [ ] Expensive operations cached
- [ ] No memory leaks
- [ ] Assets minified (CSS/JS)

### Compatibility
- [ ] Works on PHP 8.2+
- [ ] Works on TorrentPier 2.9+
- [ ] Dependencies declared
- [ ] Migration path from old version (if applicable)
```

### Creating README.md

```markdown
# Welcome Message Mod for TorrentPier

Display a customizable welcome message on your TorrentPier homepage.

![Screenshot](screenshot.png)

## Features

- ✅ Customizable message text
- ✅ Show to guests and/or logged-in users
- ✅ Dismissible (optional)
- ✅ Fully translatable
- ✅ Lightweight and fast

## Requirements

- TorrentPier >= 3.0.0
- PHP >= 8.2.0

## Installation

### Via Web Interface (Recommended)

1. Download the latest release
2. Go to Admin Panel → Mods
3. Click "Upload Mod"
4. Select the downloaded .zip file
5. Click "Install"

### Via Command Line

bash
php mod.php install welcome-message


## Configuration

1. Go to Admin Panel → Mods → Welcome Message
2. Customize your message
3. Choose visibility options
4. Click "Save Settings"

## Usage

Once installed and configured, the welcome message will automatically appear on your homepage.

### For Developers

Hook into welcome message events:

php
// Modify message before display
Hook::add_filter('welcome_message.content', function($message) {
    return strtoupper($message);
});


## Changelog

### Version 1.0.0 (2025-01-15)
- Initial release
- Basic message display
- Guest/user visibility options
- Admin settings page

## Support

- **Forum:** https://torrentpier.com/forum/mods
- **Issues:** https://github.com/yourname/welcome-message/issues
- **Email:** you@example.com

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Credits

Created by Your Name
```

### Versioning

Follow **Semantic Versioning** (semver.org):

- **Major (X.0.0)**: Breaking changes
- **Minor (1.X.0)**: New features, backward compatible
- **Patch (1.0.X)**: Bug fixes

Examples:
- `1.0.0` → `1.0.1`: Bug fix
- `1.0.1` → `1.1.0`: Added new feature
- `1.5.0` → `2.0.0`: Changed API (breaking)

### Packaging

```bash
# Create release archive
cd /path/to/torrentpier/mods
zip -r welcome-message-v1.0.0.zip welcome-message/ \
    -x "*/tests/*" "*/node_modules/*" "*/.git/*"
```

### Publishing to Repository

```bash
# Publish to TorrentPier mod repository
php mod.php publish welcome-message

# You'll need to provide:
# - Description
# - Category (appearance, features, admin, etc.)
# - Screenshots
# - Demo URL (optional)
```

### GitHub Release

1. Tag version: `git tag v1.0.0`
2. Push tag: `git push origin v1.0.0`
3. Create release on GitHub with changelog
4. Attach .zip file

---

## Next Steps

**Congratulations!** You now know how to create professional mods for TorrentPier v2.9.

### Further Reading

- [API-REFERENCE.md](./API-REFERENCE.md) - Complete API documentation
- [MIGRATION-GUIDE.md](./MIGRATION-GUIDE.md) - Migrating old mods
- [RFC-001-ModSystem.md](./RFC-001-ModSystem.md) - Technical architecture
- [examples/](./examples/) - Example mod implementations

### Example Mods to Study

- **examples/karma/** - Complete karma system with all features
- **examples/simple-banner/** - Minimal mod (good starting point)
- **examples/advanced-profile/** - Complex mod with database, templates, AJAX

### Community Resources

- **Forum:** https://torrentpier.com/forum/mod-development
- **Discord:** #mod-development channel
- **GitHub:** https://github.com/torrentpier/torrentpier
- **Wiki:** https://github.com/torrentpier/torrentpier/wiki

### Getting Help

If you get stuck:
1. Check the documentation
2. Search the forum
3. Ask in Discord #mod-development
4. Create GitHub issue with `[Question]` tag

Happy modding! 🚀
