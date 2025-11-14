# RFC-001: TorrentPier Mod System

**Status:** Draft
**Version:** 3.0.0
**Author:** TorrentPier Development Team
**Created:** 2025-01-15
**Last Updated:** 2025-01-15

---

## Executive Summary

This RFC proposes a comprehensive mod system for TorrentPier v3.0.0 that fundamentally changes how modifications are distributed, installed, and maintained. The current approach—manual file patching based on text instructions—is fragile, error-prone, and prevents users from safely updating their installations.

The proposed system introduces:
- **Hook-based architecture** for code extension without file modification
- **Isolated mod packages** with standardized structure and manifests
- **Zero-touch updates** where core and mods remain independent
- **Web-based management** for non-technical administrators
- **Automated migration tools** to convert legacy mods

This is a **breaking change** that establishes a stable foundation for future development.

---

## Problem Statement

### Current State

TorrentPier mods exist as installation instructions:

```
Open ajax.php
Find: case 'editor':
After insert:
    if($userdata['readonly'] != '0') {
        $this->ajax_die($lang['EDIT_OWN_POSTS']);
    }
```

**Critical Issues:**

1. **Update Incompatibility**
   Updates overwrite modified files. Users lose all customizations.

2. **Fragile Installation**
   Instructions break when core structure changes. "Find line X" fails.

3. **No Version Control**
   Impossible to track what was modified, by whom, or why.

4. **Conflict Resolution**
   Multiple mods modifying the same file cause unpredictable behavior.

5. **No Rollback**
   Uninstalling requires manually finding and removing all changes.

6. **Technical Barrier**
   Requires manual file editing, SSH access, understanding PHP.

### Impact

- **Users avoid updates** to preserve mods (security risk)
- **Developers can't refactor** without breaking mods
- **Support burden** from installation failures
- **Limited mod ecosystem** due to complexity

---

## Goals & Non-Goals

### Goals

✅ **Zero-touch updates** - Core updates never break mods
✅ **Isolated mods** - Mods don't modify core files
✅ **Easy management** - Install/activate/deactivate via web UI
✅ **Version compatibility** - Explicit requirements declaration
✅ **Migration path** - Tool to convert legacy mods
✅ **Developer-friendly** - Clear API, good documentation

### Non-Goals

❌ **Backward compatibility** - This is a breaking change
❌ **Auto-conversion** - Migration requires review
❌ **Marketplace** - Distribution mechanism out of scope (future work)
❌ **Sandboxing** - Mods run with full system access

---

## Architecture Design

### Component Overview

```
┌─────────────────────────────────────────────────┐
│                  TorrentPier Core               │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌──────────────┐        ┌──────────────┐      │
│  │ Hook System  │◄───────┤  ModLoader   │      │
│  └──────────────┘        └──────────────┘      │
│         │                        │              │
│         │                        │              │
│         ▼                        ▼              │
│  ┌──────────────┐        ┌──────────────┐      │
│  │   Actions    │        │   Filters    │      │
│  │   Registry   │        │   Registry   │      │
│  └──────────────┘        └──────────────┘      │
│                                                 │
├─────────────────────────────────────────────────┤
│                    Mods Layer                   │
├─────────────────────────────────────────────────┤
│                                                 │
│  /mods/karma/          /mods/automod/           │
│  ├── manifest.json     ├── manifest.json        │
│  ├── Mod.php           ├── Mod.php              │
│  ├── hooks.php         ├── hooks.php            │
│  └── config.php        └── config.php           │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Core Components

#### 1. Hook System

**Location:** `/src/Hooks/Hook.php`

**Purpose:** Event-driven extension points throughout core code.

**Types:**

- **Actions** - Execute side effects (logging, notifications)
- **Filters** - Transform data (validation, formatting)

**API:**

```php
// Add action hook
Hook::add_action(string $name, callable $callback, int $priority = 10, int $args = 1)

// Execute action hooks
Hook::do_action(string $name, ...$args)

// Add filter hook
Hook::add_filter(string $name, callable $callback, int $priority = 10, int $args = 1)

// Apply filter hooks
Hook::apply_filter(string $name, $value, ...$args)
```

**Example:**

```php
// Core code (ajax/post.php)
$can_edit = Hook::apply_filter('post.can_edit', true, $post_id, $userdata);
if (!$can_edit) {
    $this->ajax_die($lang['EDIT_OWN_POSTS']);
}

// Mod code (mods/karma/hooks.php)
Hook::add_filter('post.can_edit', function($can_edit, $post_id, $userdata) {
    if ($userdata['readonly'] != 0) {
        return false;
    }
    return $can_edit;
}, 10, 3);
```

**Priority System:**

- Lower number = higher priority
- Default = 10
- Range: 1 (first) to 999 (last)
- Same priority = execution order undefined

#### 2. ModLoader

**Location:** `/src/ModSystem/ModLoader.php`

**Purpose:** Discover, validate, and load active mods.

**Responsibilities:**

1. Scan `/mods/` for `manifest.json` files
2. Parse and validate manifests
3. Check version compatibility
4. Load active mods only (from database)
5. Register hooks from `hooks.php`
6. Merge configs from `config.php`
7. Initialize `Mod.php` classes

**Load Sequence:**

```
1. Application bootstrap (init_bb.php)
   ↓
2. ModLoader instantiation
   ↓
3. discoverMods() - scan filesystem
   ↓
4. loadActiveMods() - query database
   ↓
5. For each active mod:
   a. Validate version requirements
   b. Load config.php → config('mods.{id}')
   c. Load hooks.php → register hooks
   d. Load Mod.php → instantiate class
   ↓
6. Hooks registered, ready for execution
```

**Error Handling:**

- Invalid manifest → skip mod, log warning
- Version mismatch → deactivate mod, notify admin
- Fatal error in mod → catch exception, disable mod

#### 3. AbstractMod

**Location:** `/src/ModSystem/AbstractMod.php`

**Purpose:** Base class for mod implementations.

**Interface:**

```php
abstract class AbstractMod
{
    protected array $manifest;
    protected string $path;
    protected array $config;

    public function __construct(array $manifest, string $path)
    {
        $this->manifest = $manifest;
        $this->path = $path;
        $this->config = config("mods.{$manifest['id']}", []);
    }

    // Lifecycle hooks (optional overrides)
    public function activate(): void {}
    public function deactivate(): void {}
    public function uninstall(): void {}
    public function upgrade(string $from_version): void {}

    // Helper methods
    protected function runMigrations(): void {}
    protected function registerPermissions(array $perms): void {}
    protected function config(string $key, $default = null) {}
    protected function trans(string $key): string {}
}
```

#### 4. ModManager

**Location:** `/src/ModSystem/ModManager.php`

**Purpose:** Administrative operations (activate, deactivate, install, uninstall).

**Operations:**

```php
class ModManager
{
    // Lifecycle management
    public function activate(string $mod_id): Result
    public function deactivate(string $mod_id): Result
    public function install(string $zip_path): Result
    public function uninstall(string $mod_id, bool $remove_data = false): Result

    // Querying
    public function getInstalled(): array
    public function getActive(): array
    public function getAvailable(): array
    public function getMod(string $mod_id): ?Mod

    // Validation
    public function checkCompatibility(string $mod_id): ValidationResult
    public function checkDependencies(string $mod_id): array
}
```

---

## Database Schema

### Table: `bb_mods`

```sql
CREATE TABLE `bb_mods` (
    `mod_id` VARCHAR(64) NOT NULL PRIMARY KEY COMMENT 'Unique mod identifier from manifest',
    `mod_name` VARCHAR(255) NOT NULL COMMENT 'Human-readable name',
    `version` VARCHAR(32) NOT NULL COMMENT 'Installed version (semver)',
    `active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = active, 0 = inactive',
    `path` VARCHAR(255) NOT NULL COMMENT 'Relative path to mod directory',
    `manifest` TEXT NOT NULL COMMENT 'JSON manifest content',
    `config` TEXT NULL COMMENT 'JSON mod configuration overrides',
    `installed_at` INT(11) NOT NULL COMMENT 'Unix timestamp',
    `activated_at` INT(11) NULL COMMENT 'Unix timestamp',
    `updated_at` INT(11) NULL COMMENT 'Unix timestamp',

    INDEX `idx_active` (`active`),
    INDEX `idx_installed_at` (`installed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Installed mods registry';
```

### Table: `bb_mod_logs`

```sql
CREATE TABLE `bb_mod_logs` (
    `log_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `mod_id` VARCHAR(64) NOT NULL,
    `action` ENUM('install', 'activate', 'deactivate', 'update', 'uninstall', 'error') NOT NULL,
    `message` TEXT NOT NULL,
    `context` TEXT NULL COMMENT 'JSON context data',
    `user_id` MEDIUMINT(8) NULL COMMENT 'Admin who performed action',
    `created_at` INT(11) NOT NULL,

    INDEX `idx_mod_id` (`mod_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`mod_id`) REFERENCES `bb_mods`(`mod_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Mod operation logs';
```

### Cache Keys

```
mods:active_list        → ['karma', 'automod', ...]
mods:{id}:manifest      → manifest.json parsed
mods:{id}:config        → config.php merged with overrides
mods:hooks:registered   → list of all registered hooks
```

**Invalidation:**
- On activate/deactivate: clear `mods:active_list`
- On config save: clear `mods:{id}:config`
- On install/uninstall: clear all `mods:*`

---

## Manifest Specification

### Schema (JSON Schema Draft-07)

```json
{
    "$schema": "http://json-schema.org/draft-07/schema#",
    "type": "object",
    "required": ["id", "name", "version", "requires"],
    "properties": {
        "id": {
            "type": "string",
            "pattern": "^[a-z][a-z0-9_-]*$",
            "description": "Unique identifier (lowercase, alphanumeric, hyphen, underscore)"
        },
        "name": {
            "type": "string",
            "minLength": 3,
            "maxLength": 100
        },
        "version": {
            "type": "string",
            "pattern": "^\\d+\\.\\d+\\.\\d+(-[a-z0-9.]+)?$",
            "description": "Semantic versioning (e.g., 1.2.3 or 1.0.0-beta)"
        },
        "author": {
            "type": "string"
        },
        "homepage": {
            "type": "string",
            "format": "uri"
        },
        "description": {
            "type": "object",
            "properties": {
                "en": { "type": "string" },
                "ru": { "type": "string" }
            }
        },
        "requires": {
            "type": "object",
            "required": ["torrentpier"],
            "properties": {
                "torrentpier": {
                    "type": "string",
                    "description": "Version constraint (e.g., >=3.0.0, ^2.9, ~2.9.1)"
                },
                "php": {
                    "type": "string"
                }
            }
        },
        "dependencies": {
            "type": "array",
            "items": {
                "type": "string",
                "description": "Other mod IDs required"
            }
        },
        "autoload": {
            "type": "object",
            "properties": {
                "Mod.php": { "type": "boolean" },
                "hooks.php": { "type": "boolean" },
                "config.php": { "type": "boolean" }
            }
        },
        "ajax_handlers": {
            "type": "array",
            "items": { "type": "string" }
        },
        "cron_jobs": {
            "type": "array",
            "items": { "type": "string" }
        },
        "permissions": {
            "type": "array",
            "items": { "type": "string" }
        }
    }
}
```

### Example

```json
{
    "id": "karma",
    "name": "User & Post Karma System",
    "version": "1.0.0",
    "author": "Zenden",
    "homepage": "https://torrentpier.com/resources/karma",
    "description": {
        "ru": "Система кармы для пользователей и постов",
        "en": "Karma system for users and posts"
    },
    "requires": {
        "torrentpier": ">=3.0.0",
        "php": ">=8.2"
    },
    "dependencies": [],
    "autoload": {
        "Mod.php": true,
        "hooks.php": true,
        "config.php": true
    },
    "ajax_handlers": ["karma"],
    "cron_jobs": ["karma_cleanup"],
    "permissions": ["manage_karma", "view_karma"]
}
```

---

## Security Considerations

### Threat Model

**Assumptions:**

- Mods are installed by administrators with server access
- No sandboxing—mods have full PHP execution
- No code signing—validation is filesystem-based

**Threats:**

1. **Malicious Mod**
   Mod contains backdoor, data exfiltration, or malware.

2. **Vulnerable Mod**
   Mod has security bugs (SQL injection, XSS, file upload).

3. **Dependency Attack**
   Mod depends on compromised external mod.

4. **Supply Chain**
   Attacker compromises mod distribution (if marketplace exists).

### Mitigations

#### 1. Manual Review (Required)

**No auto-installation from untrusted sources.**

- Admins download ZIP manually
- Review manifest and code before install
- Warn in docs: "Mods have full system access"

#### 2. Version Pinning

```json
"requires": {
    "torrentpier": ">=3.0.0,<3.0.0"
}
```

Breaking changes force re-validation.

#### 3. Permission System

```json
"permissions": ["manage_karma", "delete_users"]
```

Admin UI shows what mod can do.

#### 4. Logging

All mod operations logged:
- Installation source
- Activation user
- Configuration changes
- Errors and exceptions

#### 5. Deactivation on Error

If mod throws fatal error:
```
1. Catch exception
2. Deactivate mod automatically
3. Log error details
4. Notify admin
5. Continue application execution
```

#### 6. File Integrity

Store SHA256 of manifest on install:
```sql
manifest_hash CHAR(64) NOT NULL
```

On load, verify hash matches. If mismatch → mod tampered.

### Best Practices for Mod Authors

1. **Input Validation**
   Never trust user input. Use `request_var()`, `clean_text_match()`.

2. **SQL Injection Prevention**
   Always escape with `DB()->escape()` or use prepared statements.

3. **XSS Prevention**
   Escape output with `htmlspecialchars()`.

4. **CSRF Protection**
   Validate `$_SESSION['sid']` on state-changing operations.

5. **File Upload Safety**
   Validate file types, sizes, and sanitize filenames.

6. **Dependency Audit**
   Review all dependencies for known vulnerabilities.

---

## Performance Impact

### Benchmarks (Estimated)

**Without Mod System:**
- Page load: 50ms
- Memory: 12 MB
- Queries: 15

**With Mod System (0 active mods):**
- Page load: 52ms (+2ms overhead)
- Memory: 12.5 MB (+0.5 MB)
- Queries: 16 (+1 for mod list)

**With Mod System (10 active mods):**
- Page load: 58ms (+8ms)
- Memory: 15 MB (+3 MB)
- Queries: 16 (cached mod list)

### Optimization Strategies

#### 1. Lazy Loading

Don't load mods until needed:
```php
// Instead of loading all mods on bootstrap
ModLoader::loadAll();

// Load on-demand
if (Hook::has_listeners('post.can_edit')) {
    ModLoader::loadModsForHook('post.can_edit');
}
```

#### 2. Compiled Hook Map

Generate static hook map:
```php
// /internal_data/cache/hooks_map.php
return [
    'post.can_edit' => ['karma', 'automod'],
    'user.after_login' => ['login_logger'],
];
```

Regenerate on mod activation.

#### 3. OpCache

Ensure all mod files in OpCache:
```ini
opcache.enable=1
opcache.max_accelerated_files=10000
```

#### 4. Database Index

Index on `bb_mods.active`:
```sql
INDEX `idx_active` (`active`)
```

Query only active mods.

#### 5. Cache Layer

Cache parsed manifests:
```php
$manifest = CACHE('mods')->get("manifest:{$mod_id}");
if (!$manifest) {
    $manifest = json_decode(file_get_contents(...));
    CACHE('mods')->set("manifest:{$mod_id}", $manifest, 3600);
}
```

---

## Migration Strategy

### Phase 1: Parallel System (v3.0.0)

**Both old and new mods work.**

- New mod system added
- Old-style mods still function (with deprecation warnings)
- Migration tool available
- Documentation for both

**Admin sees:**
```
⚠️ Warning: You have 3 old-style mods installed:
   - karma (old patching method)
   - automod (old patching method)
   - bbcodes (old patching method)

These will stop working in v3.0.0.
[Migrate Now] [Learn More]
```

### Phase 2: Deprecation (v2.10.0, +6 months)

**Old-style mods deprecated.**

- Loud warnings on every page
- Email notifications to admins
- Migration tool improved based on feedback

### Phase 3: Removal (v3.0.0, +12 months)

**Old-style mods removed.**

- Code cleanup
- Only new mod system supported
- Breaking change documented

### Migration Tool

**CLI:**
```bash
php mods.php migrate --from=old_mods/karma.txt --interactive
```

**Process:**

1. Parse instruction file
2. Analyze compatibility with hook system
3. Generate new mod structure
4. Flag manual overrides needed
5. Create `MANUAL_PATCHES.md` for unsupported cases
6. Test installation

**Success Rate (Estimated):**
- 70% fully automated
- 20% require minor manual fixes
- 10% require significant refactoring

---

## Backwards Compatibility

### Breaking Changes

**This is a breaking change by design.**

Justification:
- Current system unmaintainable
- Users already avoid updates (de facto broken)
- One-time pain for long-term stability

### Compatibility Layer (v2.9.x only)

**Old mod detection:**
```php
// Check for manually patched files
$baseline = json_decode(file_get_contents('.tp-baseline.json'), true);
$modified = [];

foreach ($baseline['files'] as $file => $hash) {
    if (sha256_file($file) !== $hash) {
        $modified[] = $file;
    }
}

if (!empty($modified)) {
    admin_notice("Old-style mods detected. Migrate to v2.9 mod system.");
}
```

**Fallback:**
- If new mod fails, fall back to old code path
- Log warning for admin review

---

## Open Questions

### 1. Marketplace

**Question:** Should we build an official mod marketplace?

**Pros:**
- Centralized discovery
- Version management
- One-click install

**Cons:**
- Maintenance burden
- Moderation required
- Security liability

**Decision:** Out of scope for v3.0.0. Revisit in v2.10.0.

### 2. Code Signing

**Question:** Should mods be cryptographically signed?

**Pros:**
- Verify author authenticity
- Prevent tampering

**Cons:**
- Complex infrastructure (key management)
- Doesn't prevent malicious code from legitimate author

**Decision:** Not in v3.0.0. Consider for a marketplace (future).

### 3. Sandboxing

**Question:** Should mods run in a restricted environment?

**Pros:**
- Limit damage from malicious code
- Enforce permissions

**Cons:**
- Extremely complex (PHP not designed for sandboxing)
- Performance overhead
- Breaks many legitimate use cases

**Decision:** Not feasible. Trust-based model.

### 4. Automatic Updates

**Question:** Should mods auto-update like WordPress plugins?

**Pros:**
- Security patches deployed quickly
- Users always on latest version

**Cons:**
- Updates can break functionality
- Requires server-side infrastructure

**Decision:** Manual updates only in v3.0.0. Auto-update opt-in in the future.

---

## Success Metrics

### Technical Metrics

- ✅ 95% of top-30 mods successfully migrated
- ✅ <10ms overhead for mod system
- ✅ Zero core file modifications by mods
- ✅ <5% performance degradation with 10 mods

### User Metrics

- ✅ 80% of users complete update within 3 months
- ✅ 50% of mod authors migrate their mods
- ✅ Support requests for "update broke mods" drop by 90%

### Adoption Metrics

- ✅ 20 new mods published in first 6 months
- ✅ Average mod rating >4.0/5.0
- ✅ Active forum threads showing community engagement

---

## References

### Similar Systems

- **WordPress Plugins** - Hook-based, similar activation mechanism
- **Laravel Packages** - Composer-style, service provider pattern
- **Symfony Bundles** - Auto-configuration, dependency injection
- **vBulletin Products** - XML-based, database-driven

### Standards

- **Semantic Versioning 2.0.0** - https://semver.org/
- **JSON Schema Draft-07** - https://json-schema.org/
- **PSR-4 Autoloading** - https://www.php-fig.org/psr/psr-4/

---

## Appendix

### Terminology

- **Mod** - Third-party extension package
- **Hook** - Extension point in core code
- **Action** - Hook that executes side effects
- **Filter** - Hook that transforms data
- **Manifest** - Mod metadata (manifest.json)
- **Migration** - Converting old-style mod to new format

### Acronyms

- **RFC** - Request for Comments
- **API** - Application Programming Interface
- **CLI** - Command-Line Interface
- **UI** - User Interface
- **DB** - Database
- **SQL** - Structured Query Language
- **JSON** - JavaScript Object Notation
- **SHA** - Secure Hash Algorithm
- **XSS** - Cross-Site Scripting
- **CSRF** - Cross-Site Request Forgery

---

**End of RFC-001**
