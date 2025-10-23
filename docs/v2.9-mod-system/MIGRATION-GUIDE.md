# Migration Guide: Converting Legacy Mods to v2.9 Mod System

## Table of Contents

1. [Overview](#overview)
2. [What's Changing](#whats-changing)
3. [Old vs New Architecture](#old-vs-new-architecture)
4. [Automated Migration Tool](#automated-migration-tool)
5. [Manual Migration Process](#manual-migration-process)
6. [Common Conversion Patterns](#common-conversion-patterns)
7. [Testing Your Migrated Mod](#testing-your-migrated-mod)
8. [Troubleshooting](#troubleshooting)
9. [Support and Resources](#support-and-resources)

---

## Overview

TorrentPier v3.0.0 introduces a revolutionary new mod system that eliminates the need to modify core files. This guide will help you migrate your existing instruction-based mods to the new hook-based architecture.

**Benefits of migrating:**
- ✅ No more core file modifications
- ✅ Updates won't break your mods
- ✅ One-click installation and updates
- ✅ Better compatibility with other mods
- ✅ Built-in conflict detection
- ✅ Professional package management

**Migration difficulty levels:**
- **Easy (70%)**: Mods that add new functionality via hooks
- **Medium (20%)**: Mods that modify existing behavior
- **Hard (10%)**: Mods with complex core modifications

---

## What's Changing

### Before (v2.8 and earlier)
```
Installation Instructions:
1. Open library/includes/functions.php
2. Find line 1247: function get_username($user_id)
3. Add after line 1253:
   // Karma mod
   if (isset($user['karma'])) {
       $username .= " [K: {$user['karma']}]";
   }
4. Upload files to server
5. Run SQL manually
```

### After (v2.9+)
```php
// /mods/karma/Mod.php
class KarmaMod extends AbstractMod {
    public function boot() {
        Hook::add_filter('user.display_name', [$this, 'addKarmaToUsername'], 10, 2);
    }

    public function addKarmaToUsername($username, $user) {
        if (isset($user['karma'])) {
            return $username . " [K: {$user['karma']}]";
        }
        return $username;
    }
}
```

**Installation:** One click in admin panel, or `php mod.php install karma`

---

## Old vs New Architecture

### Core File Modifications → Hooks

| Old Approach | New Approach |
|-------------|--------------|
| Find line X, add code Y | Register hook and add filter/action |
| Modify `functions.php` | Create `Mod.php` with hook callbacks |
| Manual merge on updates | Automatic compatibility |
| Conflicts with other mods | Isolated execution with priorities |

### File Structure Comparison

**Old Mod Structure:**
```
karma_mod_v1.2.zip
├── INSTALL.txt
├── files_to_upload/
│   ├── ajax/karma.php
│   └── templates/karma.tpl
└── sql/install.sql
```

**New Mod Structure:**
```
mods/karma/
├── manifest.json          # Mod metadata and dependencies
├── Mod.php               # Main mod class with hooks
├── hooks.php             # Hook registrations (optional)
├── config.php            # Configuration (optional)
├── ajax/karma.php        # AJAX handlers
├── templates/            # Template overrides
│   └── post_karma.tpl
├── migrations/           # Database migrations
│   └── 001_create_tables.sql
└── lang/                 # Translations
    └── ru/main.php
```

### Database Changes: SQL Files → Migrations

**Old:**
```sql
-- Run this SQL manually
CREATE TABLE `bb_karma` (
    `user_id` INT NOT NULL,
    `karma_points` INT DEFAULT 0
);
```

**New:**
```php
// /mods/karma/migrations/001_create_tables.sql
-- Migration runs automatically on install
CREATE TABLE IF NOT EXISTS `bb_karma` (
    `user_id` INT NOT NULL PRIMARY KEY,
    `karma_points` INT DEFAULT 0,
    `last_updated` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Automated Migration Tool

TorrentPier v2.9 includes an automated migration tool that can convert 70-80% of old mods automatically.

### Usage

```bash
# Analyze your old mod
php mod.php migrate:analyze /path/to/old_mod/INSTALL.txt

# Preview migration (dry run)
php mod.php migrate:convert /path/to/old_mod/INSTALL.txt --dry-run

# Convert to new format
php mod.php migrate:convert /path/to/old_mod/INSTALL.txt --output=/path/to/new_mod

# Convert with interactive prompts for unclear sections
php mod.php migrate:convert /path/to/old_mod/INSTALL.txt --interactive
```

### What the Tool Can Detect

✅ **Automatically converted:**
- File additions (new files to upload)
- SQL schema additions (CREATE TABLE, ALTER TABLE)
- Simple function additions at end of file
- Template file additions
- Language file additions

⚠️ **Needs manual review:**
- Code modifications in middle of functions
- Complex logic changes
- UI/template modifications
- Security-sensitive operations

❌ **Cannot convert:**
- Undocumented modifications
- Binary file modifications
- External service integrations without clear instructions

### Migration Report Example

```
MIGRATION ANALYSIS REPORT
=========================
Mod: Karma System v1.2
Confidence: 75% (Medium-High)

Automatically Convertible:
✅ 3 new files detected (ajax/karma.php, templates/karma.tpl, lang/ru/main.php)
✅ 1 SQL table creation (bb_karma)
✅ 2 function additions (calculate_karma, update_karma)

Needs Manual Review:
⚠️  Modification in functions.php line 1247-1253 (function get_username)
⚠️  Modification in viewtopic.php line 823 (post display logic)

Recommended Hooks:
• user.display_name (for username modification)
• post.after_display (for karma display in posts)
• ajax.request (for AJAX handler)

Estimated manual work: 2-3 hours
```

---

## Manual Migration Process

### Step 1: Create Mod Directory Structure

```bash
cd /path/to/torrentpier
mkdir -p mods/your_mod_name/{migrations,templates,lang/ru,ajax}
cd mods/your_mod_name
```

### Step 2: Create manifest.json

```json
{
  "id": "your-mod-name",
  "name": "Your Mod Display Name",
  "description": "Brief description of what your mod does",
  "version": "2.0.0",
  "author": "Your Name",
  "author_email": "you@example.com",
  "license": "MIT",
  "requires": {
    "torrentpier": ">=3.0.0",
    "php": ">=8.2.0"
  },
  "dependencies": [],
  "hooks": [],
  "ajax_handlers": [],
  "admin_pages": [],
  "templates": []
}
```

**Version numbering:** When migrating from old mod, start with 2.0.0 to indicate major rewrite.

### Step 3: Convert Core File Modifications to Hooks

#### Example: Username Display Modification

**Old (modify functions.php):**
```php
// FIND line 1247:
function get_username($user_id) {
    // ... existing code ...

    // ADD AFTER line 1253:
    if (isset($user['karma'])) {
        $username .= " [K: {$user['karma']}]";
    }

    return $username;
}
```

**New (use hook in Mod.php):**
```php
<?php
namespace TorrentPier\Mod\Karma;

use TorrentPier\Mod\AbstractMod;
use TorrentPier\Mod\Hook;

class Mod extends AbstractMod
{
    public function boot()
    {
        // Register filter to modify username display
        Hook::add_filter('user.display_name', [$this, 'addKarmaToUsername'], 10, 2);
    }

    public function addKarmaToUsername($username, $user)
    {
        if (isset($user['karma'])) {
            return $username . " [K: {$user['karma']}]";
        }
        return $username;
    }
}
```

Then add the hook execution point in core (this is done by core team):
```php
// In library/includes/functions.php:1253
function get_username($user_id) {
    // ... existing code ...
    $username = // ... username generation ...

    // Allow mods to modify username
    $username = Hook::apply_filter('user.display_name', $username, $user);

    return $username;
}
```

#### Example: Adding AJAX Handler

**Old (upload ajax/karma.php):**
```php
<?php
// ajax/karma.php
define('IN_TORRENTPIER', true);
require_once __DIR__ . '/../common.php';

$action = request_var('action', '');
if ($action === 'vote') {
    // handle voting
}
```

**New (register AJAX handler):**
```php
// /mods/karma/Mod.php
class Mod extends AbstractMod
{
    public function boot()
    {
        // Register AJAX handler
        Hook::add_action('ajax.karma', [$this, 'handleAjaxRequest']);
    }

    public function handleAjaxRequest()
    {
        $action = request_var('action', '');

        if ($action === 'vote') {
            $this->handleVote();
        }

        ajax_response(['success' => true]);
    }

    private function handleVote()
    {
        // Voting logic
    }
}
```

Update manifest.json:
```json
{
  "ajax_handlers": [
    {
      "name": "karma",
      "method": "handleAjaxRequest",
      "permission": "user"
    }
  ]
}
```

### Step 4: Convert SQL to Migrations

**Old (install.sql):**
```sql
CREATE TABLE `bb_karma` (
    `user_id` INT NOT NULL,
    `karma_points` INT DEFAULT 0
);

CREATE TABLE `bb_karma_votes` (
    `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `voter_id` INT NOT NULL,
    `value` TINYINT NOT NULL
);
```

**New (migrations/001_create_tables.sql):**
```sql
-- Migration: 001_create_tables.sql
-- Description: Create karma tables
-- Date: 2025-01-15

CREATE TABLE IF NOT EXISTS `bb_karma` (
    `user_id` INT NOT NULL PRIMARY KEY,
    `karma_points` INT DEFAULT 0,
    `positive_votes` INT DEFAULT 0,
    `negative_votes` INT DEFAULT 0,
    `last_updated` INT NOT NULL,
    KEY `karma_points` (`karma_points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bb_karma_votes` (
    `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `voter_id` INT NOT NULL,
    `value` TINYINT NOT NULL,
    `created_at` INT NOT NULL,
    UNIQUE KEY `unique_vote` (`user_id`, `voter_id`),
    KEY `user_id` (`user_id`),
    KEY `voter_id` (`voter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 5: Move Template Files

**Old:** Upload `templates/subSilver/karma.tpl` manually

**New:** Place in `/mods/karma/templates/karma.tpl`

Templates are automatically discovered and can override core templates or provide new ones.

**Template with hooks:**
```html
<!-- /mods/karma/templates/post_karma.tpl -->
<div class="karma-display">
    <!-- BEGIN karma -->
    <span class="karma-points" data-user-id="{karma.USER_ID}">
        {karma.ICON} Karma: {karma.POINTS}
    </span>
    <!-- Hook: Allow other mods to extend karma display -->
    {KARMA_DISPLAY_EXTRA}
    <!-- END karma -->
</div>
```

Register template hook in Mod.php:
```php
public function boot()
{
    Hook::add_filter('template.karma.display_extra', [$this, 'addExtraInfo'], 10, 1);
}

public function addExtraInfo($extra_html)
{
    // Other mods can add info here
    return $extra_html;
}
```

### Step 6: Add Configuration

**Create config.php:**
```php
<?php
// /mods/karma/config.php

return [
    'karma' => [
        // Basic settings
        'enabled' => true,
        'min_posts_to_vote' => 10,
        'votes_per_day' => 5,

        // Point values
        'upvote_value' => 1,
        'downvote_value' => -1,

        // Display settings
        'show_in_profile' => true,
        'show_in_posts' => true,
        'icon_upvote' => '👍',
        'icon_downvote' => '👎',
    ]
];
```

**Access config in Mod.php:**
```php
public function canUserVote($user_id)
{
    $min_posts = $this->config('min_posts_to_vote', 10);
    $user_posts = get_user_posts_count($user_id);

    return $user_posts >= $min_posts;
}
```

### Step 7: Add Language Files

**Old:** Modify `language/lang_russian/lang_main.php` manually

**New:** Create `/mods/karma/lang/ru/main.php`

```php
<?php
// /mods/karma/lang/ru/main.php

return [
    'KARMA_SYSTEM' => 'Система кармы',
    'KARMA_POINTS' => 'Очки кармы',
    'KARMA_UPVOTE' => 'Повысить карму',
    'KARMA_DOWNVOTE' => 'Понизить карму',
    'KARMA_VOTED' => 'Вы проголосовали',
    'KARMA_ERROR_SELF' => 'Нельзя голосовать за себя',
    'KARMA_ERROR_POSTS' => 'Недостаточно сообщений для голосования',
    'KARMA_ERROR_LIMIT' => 'Превышен дневной лимит голосов',
];
```

**Use in code:**
```php
public function vote($user_id, $voter_id, $value)
{
    if ($user_id === $voter_id) {
        throw new \Exception($this->lang('KARMA_ERROR_SELF'));
    }

    // ... voting logic ...
}
```

---

## Common Conversion Patterns

### Pattern 1: Adding New Function → Static Helper Class

**Old:**
```php
// Add to functions.php
function calculate_user_karma($user_id) {
    // calculation logic
}
```

**New:**
```php
// /mods/karma/src/KarmaCalculator.php
namespace TorrentPier\Mod\Karma;

class KarmaCalculator
{
    public static function calculate($user_id)
    {
        // calculation logic
    }
}

// Usage in mod
use TorrentPier\Mod\Karma\KarmaCalculator;
$karma = KarmaCalculator::calculate($user_id);
```

### Pattern 2: Modifying Existing Function → Filter Hook

**Old:**
```php
// Modify existing function
function generate_post_html($post) {
    $html = // ... original code ...

    // ADD THIS:
    if ($post['user_karma'] > 100) {
        $html .= '<span class="high-karma">⭐</span>';
    }

    return $html;
}
```

**New:**
```php
// Core adds filter (done by core team)
function generate_post_html($post) {
    $html = // ... original code ...

    // Allow mods to modify post HTML
    $html = Hook::apply_filter('post.html', $html, $post);

    return $html;
}

// Your mod uses filter
public function boot()
{
    Hook::add_filter('post.html', [$this, 'addKarmaBadge'], 10, 2);
}

public function addKarmaBadge($html, $post)
{
    if (($post['user_karma'] ?? 0) > 100) {
        $html .= '<span class="high-karma">⭐</span>';
    }
    return $html;
}
```

### Pattern 3: Adding Page/Feature → Action Hook

**Old:**
```php
// Upload karma.php to root
define('IN_TORRENTPIER', true);
require_once 'common.php';

// Show karma page
echo "<html>...</html>";
```

**New:**
```php
// Register action in Mod.php
public function boot()
{
    Hook::add_action('page.karma', [$this, 'showKarmaPage']);
}

public function showKarmaPage()
{
    // Load template
    $this->template('karma_page', [
        'users' => $this->getTopKarmaUsers(),
    ]);
}
```

Register in manifest.json:
```json
{
  "admin_pages": [
    {
      "slug": "karma",
      "title": "Karma Leaderboard",
      "capability": "user"
    }
  ]
}
```

### Pattern 4: Database Query Modification → Query Filter

**Old:**
```php
// Modify SELECT query
$sql = "SELECT u.* FROM users u WHERE u.user_id = $user_id";

// ADD THIS:
$sql .= " LEFT JOIN bb_karma k ON u.user_id = k.user_id";
```

**New:**
```php
// Core adds filter
$sql = "SELECT u.* FROM users u WHERE u.user_id = $user_id";
$sql = Hook::apply_filter('query.user_select', $sql, ['user_id' => $user_id]);

// Your mod extends query
public function boot()
{
    Hook::add_filter('query.user_select', [$this, 'extendUserQuery'], 10, 2);
}

public function extendUserQuery($sql, $params)
{
    return $sql . " LEFT JOIN bb_karma k ON u.user_id = k.user_id";
}
```

### Pattern 5: Admin Panel Section → Admin Page

**Old:**
```php
// Modify admin/index.php
case 'karma':
    // Show karma admin page
    break;
```

**New:**
```php
// Register admin page in manifest.json
{
  "admin_pages": [
    {
      "slug": "karma-settings",
      "title": "Karma Settings",
      "capability": "admin",
      "icon": "⚙️"
    }
  ]
}

// Implement in Mod.php
public function boot()
{
    if (is_admin()) {
        Hook::add_action('admin.page.karma-settings', [$this, 'showAdminPage']);
        Hook::add_action('admin.save.karma-settings', [$this, 'saveSettings']);
    }
}

public function showAdminPage()
{
    $this->template('admin/settings', [
        'settings' => $this->getAllSettings(),
    ]);
}
```

### Pattern 6: Cron Job → Scheduled Action

**Old:**
```php
// Add to cron.php
if ($cron_type == 'hourly') {
    // ADD THIS:
    require_once 'library/karma_maintenance.php';
    update_karma_cache();
}
```

**New:**
```php
// Register cron in manifest.json
{
  "cron_jobs": [
    {
      "name": "karma_cache_update",
      "schedule": "hourly",
      "method": "updateCache"
    }
  ]
}

// Implement in Mod.php
public function updateCache()
{
    // Update karma cache
    $this->log('Karma cache updated');
}
```

---

## Testing Your Migrated Mod

### 1. Validation Test

```bash
# Validate mod structure and manifest
php mod.php validate karma

# Expected output:
✅ Manifest is valid
✅ Required files present
✅ Hooks properly registered
✅ Dependencies satisfied
```

### 2. Installation Test

```bash
# Test installation
php mod.php install karma --dry-run

# Actual installation
php mod.php install karma

# Verify
php mod.php list --active
```

### 3. Functionality Test

Create a test checklist based on your mod's features:

```markdown
## Karma Mod Test Checklist

### Basic Functionality
- [ ] Karma display shows in user profile
- [ ] Karma display shows in posts
- [ ] Upvote button works
- [ ] Downvote button works
- [ ] Vote limits enforced (5 per day)
- [ ] Can't vote for self

### Database
- [ ] Tables created correctly
- [ ] Votes are recorded
- [ ] Karma points calculated correctly
- [ ] Historical data preserved

### Compatibility
- [ ] Works with other installed mods
- [ ] No JavaScript console errors
- [ ] No PHP errors in log
- [ ] Templates display correctly

### Admin Panel
- [ ] Settings page accessible
- [ ] Configuration saves correctly
- [ ] Mod can be disabled/enabled
- [ ] Statistics display correctly
```

### 4. Compatibility Test

Test with common mods:
```bash
# Install multiple mods
php mod.php install karma
php mod.php install reputation
php mod.php install advanced-profile

# Check for conflicts
php mod.php status

# Test all features still work
```

### 5. Update Test

```bash
# Simulate mod update
php mod.php update karma --from=2.0.0 --to=2.1.0

# Verify:
# - Data preserved
# - Configuration preserved
# - New features work
# - Migrations ran successfully
```

### 6. Uninstallation Test

```bash
# Test clean removal
php mod.php uninstall karma --keep-data

# Verify:
# - Tables remain (if --keep-data)
# - Hooks unregistered
# - Files remain in /mods/
# - Site still works

# Test complete removal
php mod.php uninstall karma

# Verify:
# - Tables removed
# - All traces gone
# - Site still works
```

---

## Troubleshooting

### Problem: "Hook not found"

**Symptom:**
```
Warning: Hook 'user.display_name' does not exist
```

**Cause:** The hook isn't implemented in core yet.

**Solution:**

1. **Check hook availability:**
```bash
php mod.php hooks list | grep user.display_name
```

2. **Request hook addition:** Create issue on GitHub with hook details:
```markdown
Hook Request: user.display_name

Location: library/includes/functions.php:1253
Purpose: Allow mods to modify username display
Parameters: $username (string), $user (array)
Return: Modified username string
```

3. **Temporary workaround:** Use different hook or wait for core update

### Problem: "Manifest validation failed"

**Symptom:**
```
Error: Invalid manifest.json - missing required field 'version'
```

**Solution:** Validate JSON syntax and required fields:

```bash
# Validate JSON syntax
cat manifest.json | jq .

# Check required fields
php mod.php validate karma --verbose
```

Required fields in manifest.json:
```json
{
  "id": "karma",           // Required
  "name": "Karma System",  // Required
  "version": "2.0.0",      // Required (semver)
  "requires": {
    "torrentpier": ">=3.0.0"  // Required
  }
}
```

### Problem: "Migration failed"

**Symptom:**
```
Error: Migration 001_create_tables.sql failed: Table 'bb_karma' already exists
```

**Solutions:**

1. **Skip if already exists:**
```sql
CREATE TABLE IF NOT EXISTS `bb_karma` (
    -- columns
);
```

2. **Use migration version tracking:**
```bash
# Mark migration as completed
php mod.php migrate:mark karma 001_create_tables

# Check migration status
php mod.php migrate:status karma
```

3. **Rollback and retry:**
```bash
# Rollback last migration
php mod.php migrate:rollback karma

# Retry installation
php mod.php install karma
```

### Problem: "Config not found"

**Symptom:**
```
Error: Configuration key 'karma.min_posts_to_vote' not found
```

**Solution:** Always provide defaults:

```php
// ❌ Bad: Will throw error if not set
$min_posts = $this->config('min_posts_to_vote');

// ✅ Good: Provides default
$min_posts = $this->config('min_posts_to_vote', 10);

// ✅ Best: Check existence first
if ($this->hasConfig('min_posts_to_vote')) {
    $min_posts = $this->config('min_posts_to_vote');
} else {
    $min_posts = 10;
}
```

### Problem: "Hook priority issues"

**Symptom:** Your hook runs before/after another mod's hook, causing conflicts.

**Solution:** Adjust hook priority:

```php
// Default priority is 10
Hook::add_filter('user.display_name', [$this, 'modify'], 10, 2);

// Run before other mods
Hook::add_filter('user.display_name', [$this, 'modify'], 5, 2);

// Run after other mods
Hook::add_filter('user.display_name', [$this, 'modify'], 20, 2);
```

Priority guidelines:
- `1-5`: Critical modifications (security, validation)
- `10`: Default (most mods)
- `15-20`: UI/display modifications
- `50+`: Logging, analytics

### Problem: "Template not found"

**Symptom:**
```
Error: Template 'karma/post_display.tpl' not found
```

**Solution:** Check template paths:

```php
// ❌ Bad: Absolute path
$this->template('/mods/karma/templates/post_display.tpl');

// ✅ Good: Relative to mod directory
$this->template('post_display');

// ✅ With subdirectory
$this->template('admin/settings');
```

Template resolution order:
1. `/mods/karma/templates/admin/settings.tpl`
2. `/styles/templates/mods/karma/admin/settings.tpl` (user override)
3. Fallback to default template

### Problem: "AJAX handler not working"

**Symptom:** AJAX requests return 404 or wrong handler runs

**Solution:** Check AJAX registration:

1. **Register in manifest.json:**
```json
{
  "ajax_handlers": [
    {
      "name": "karma_vote",
      "method": "handleVote",
      "permission": "user"
    }
  ]
}
```

2. **Implement in Mod.php:**
```php
public function handleVote()
{
    $this->requirePermission('user');

    // Handle vote
    ajax_response(['success' => true]);
}
```

3. **Call from JavaScript:**
```javascript
// ✅ Correct
ajax_request('karma_vote', {action: 'upvote', user_id: 123});

// ❌ Wrong: Old style
ajax_request('karma', {action: 'vote', type: 'up'});
```

### Problem: "Mod conflicts with another mod"

**Symptom:**
```
Warning: Mod 'karma' conflicts with mod 'reputation' on hook 'user.display_name'
```

**Solution:** Coordinate with other mod or adjust implementation:

```php
// Option 1: Check if other mod is active
if ($this->isModActive('reputation')) {
    // Use different approach
    Hook::add_filter('user.display_name_extra', [$this, 'addKarma'], 10, 2);
} else {
    Hook::add_filter('user.display_name', [$this, 'addKarma'], 10, 2);
}

// Option 2: Make hooks chainable
public function addKarma($username, $user)
{
    // Append, don't replace
    $karma_text = $this->getKarmaText($user);
    return $username . ' ' . $karma_text;
}
```

### Problem: "Performance issues after migration"

**Symptom:** Site slower after enabling mod

**Solutions:**

1. **Use caching:**
```php
public function getKarmaForUser($user_id)
{
    return $this->cache("karma_user_{$user_id}", function() use ($user_id) {
        return $this->calculateKarma($user_id);
    }, 3600); // Cache for 1 hour
}
```

2. **Optimize database queries:**
```php
// ❌ Bad: N+1 query
foreach ($posts as $post) {
    $karma = $this->getKarma($post['user_id']);
}

// ✅ Good: Single query
$user_ids = array_column($posts, 'user_id');
$karmas = $this->getKarmasBulk($user_ids);
```

3. **Lazy load features:**
```php
public function boot()
{
    // Only register hooks that are actually used
    if ($this->config('show_in_posts', true)) {
        Hook::add_filter('post.display', [$this, 'addKarmaDisplay'], 10, 2);
    }
}
```

---

## Support and Resources

### Documentation
- [RFC-001-ModSystem.md](./RFC-001-ModSystem.md) - Technical specification
- [API-REFERENCE.md](./API-REFERENCE.md) - Complete API documentation
- [MOD-DEVELOPMENT.md](./MOD-DEVELOPMENT.md) - Creating new mods from scratch
- [IMPLEMENTATION.md](./IMPLEMENTATION.md) - Core implementation roadmap

### Example Mods
- [examples/karma/](./examples/karma/) - Complete migrated Karma mod
- [examples/simple-banner/](./examples/simple-banner/) - Minimal mod example
- [examples/advanced-profile/](./examples/advanced-profile/) - Complex mod example

### Getting Help

1. **Forum:** https://torrentpier.com/forum/mod-development
2. **GitHub Issues:** https://github.com/torrentpier/torrentpier/issues
3. **Discord:** #mod-development channel
4. **Wiki:** https://github.com/torrentpier/torrentpier/wiki/Mod-Development

### Migration Assistance

If you need help migrating your mod:

1. **Open migration request:** Create issue with `[Migration Help]` tag
2. **Provide mod details:**
   - Link to original mod (forum thread, GitHub)
   - Installation instructions
   - Source code (if available)
3. **Community help:** Other developers can assist with migration

### Testing Your Migration

Before publishing:
1. Test on clean TorrentPier v3.0.0 installation
2. Test with other popular mods installed
3. Test update scenario (v1.x → v2.0)
4. Get community beta testers

### Publishing Your Migrated Mod

1. **Update forum thread:** Mark as "v2.9 compatible"
2. **Submit to mod repository:** `php mod.php publish karma`
3. **Changelog:** Document changes from old version
4. **Deprecation notice:** Mark old version as deprecated

---

## Quick Reference: Common Tasks

### Convert "Find and add" instruction
```php
// Old: "Find line 123, add code after it"
// New: Use appropriate hook

Hook::add_action('after.function_name', [$this, 'myCode']);
Hook::add_filter('result.function_name', [$this, 'modifyResult'], 10, 2);
```

### Convert new file upload
```php
// Old: "Upload file.php to root"
// New: Place in mod directory, register handler

// /mods/yourmod/ajax/handler.php
// Register in manifest.json under "ajax_handlers"
```

### Convert SQL execution
```php
// Old: "Run install.sql"
// New: Create migration file

// /mods/yourmod/migrations/001_description.sql
// Runs automatically on install
```

### Convert template modification
```php
// Old: "Edit template X, add code"
// New: Create override or use template hooks

// /mods/yourmod/templates/override.tpl
// Or register template filter
Hook::add_filter('template.X', [$this, 'modify']);
```

### Convert language addition
```php
// Old: "Add to lang_main.php"
// New: Create language file

// /mods/yourmod/lang/ru/main.php
return ['KEY' => 'Value'];
```

### Convert cron job
```json
// Old: "Add to cron.php"
// New: Register in manifest.json

{
  "cron_jobs": [{
    "name": "task_name",
    "schedule": "daily",
    "method": "taskMethod"
  }]
}
```

---

## Conclusion

Migrating to v2.9's mod system requires initial effort but provides significant long-term benefits:

✅ **No more update conflicts** - Core updates won't break your mod
✅ **Easier maintenance** - Clear structure and automatic updates
✅ **Better compatibility** - Isolated execution prevents conflicts
✅ **Professional distribution** - One-click installation for users

The automated migration tool handles most conversions, with manual review for complex modifications. Most mods can be migrated in 2-6 hours depending on complexity.

**Next steps:**
1. Run automated migration tool on your mod
2. Review and test the conversion
3. Publish updated mod for v2.9+
4. Mark old version as deprecated

Good luck with your migration! 🚀
