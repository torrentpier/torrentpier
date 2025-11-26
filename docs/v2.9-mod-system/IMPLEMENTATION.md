# TorrentPier v3.0.0 Mod System - Implementation Roadmap

**Timeline:** 14 weeks (3.5 months)
**Team Size:** 1-2 developers
**Risk Level:** High (breaking change)

---

## Table of Contents

1. [Phase 1: Foundation](#phase-1-foundation-3-weeks)
2. [Phase 2: Tooling](#phase-2-tooling-3-weeks)
3. [Phase 3: Admin UI](#phase-3-admin-ui-2-weeks)
4. [Phase 4: Documentation](#phase-4-documentation-2-weeks)
5. [Phase 5: Beta & Release](#phase-5-beta--release-4-weeks)
6. [Testing Strategy](#testing-strategy)
7. [Rollback Plan](#rollback-plan)
8. [Dependencies](#dependencies)

---

## Phase 1: Foundation (3 weeks)

**Goal:** Build core infrastructure for mod system.

### Week 1: Hook System

#### Tasks

**1.1 Create Hook class** (`/src/Hooks/Hook.php`)
- [ ] Implement `add_action()` method
- [ ] Implement `do_action()` method
- [ ] Implement `add_filter()` method
- [ ] Implement `apply_filter()` method
- [ ] Implement priority sorting
- [ ] Add argument count limiting
- [ ] Write unit tests

**1.2 Add hook points to core**
- [ ] `ajax.php` - before/after each handler
- [ ] `viewtopic.php` - post rendering, pagination
- [ ] `functions_post.php` - before/after create/edit
- [ ] `usercp_viewprofile.php` - profile rendering
- [ ] `template.php` - before/after template render
- [ ] Document all hook points in `/library/hooks.php`

**1.3 Create hooks documentation**
- [ ] Generate `/library/hooks.php` with all hook definitions
- [ ] Add PHPDoc comments for each hook
- [ ] Include usage examples in comments

#### Deliverables

- Working Hook system
- 50+ hook points in core
- Unit test coverage >80%
- Hook reference doc

#### Testing

```php
// Test priority ordering
Hook::add_filter('test', fn($v) => $v . 'A', 20);
Hook::add_filter('test', fn($v) => $v . 'B', 10);
$result = Hook::apply_filter('test', '');
assert($result === 'BA'); // B runs first (priority 10)

// Test argument limiting
Hook::add_filter('test2', fn($a, $b) => $a + $b, 10, 2);
$result = Hook::apply_filter('test2', 5, 10, 999); // 3rd arg ignored
assert($result === 15);
```

---

### Week 2: ModLoader & Database

#### Tasks

**2.1 Database schema**
- [x] Create Phinx migration for core mod tables
- [x] Add `bb_mods` table - stores installed mods and their status
- [x] Add `bb_mod_logs` table - logs mod operations (install, activate, etc.)
- [x] Add `bb_mod_migrations` table - **tracks migrations for ALL mods in single table**
- [x] Add indexes for performance
- [ ] Test migration rollback

**Note on mod migrations:**
Each mod stores its migrations in `mods/{mod_id}/migrations/*.sql` files.
The `bb_mod_migrations` table tracks which migrations have been executed for each mod:
- Primary key: (`mod_id`, `version`)
- This avoids creating separate `phinxlog_mod_{name}` tables for each mod
- ModMigrationManager handles running SQL files and tracking execution

**2.2 ModLoader class** (`/src/ModSystem/ModLoader.php`)
- [ ] Implement `discoverMods()` - scan `/mods/` directory
- [ ] Implement `loadActiveMods()` - query `bb_mods` table
- [ ] Implement `loadMod()` - load single mod
- [ ] Implement `validateManifest()` - JSON schema validation
- [ ] Implement `checkCompatibility()` - version checking
- [ ] Add error handling and logging
- [ ] Write unit tests

**2.3 Integrate ModLoader into bootstrap**
- [ ] Add ModLoader to `init_bb.php`
- [ ] Load mods after config loaded
- [ ] Load mods before routing
- [ ] Add performance monitoring

#### Deliverables

- Database schema migrated
- ModLoader class working
- Mods loaded on bootstrap
- Error handling for broken mods

#### Testing

```php
// Test mod discovery
$loader = new ModLoader();
$mods = $loader->discoverMods();
assert(count($mods) === 2); // karma, automod
assert(isset($mods['karma']['manifest']));

// Test version compatibility
$compatible = $loader->checkCompatibility('karma');
assert($compatible->isValid() === true);

// Test broken manifest
$loader->loadMod('/mods/broken/');
assert(Log::hasError('Invalid manifest: karma'));
```

---

### Week 3: AbstractMod & Config

#### Tasks

**3.1 AbstractMod class** (`/src/ModSystem/AbstractMod.php`)
- [ ] Implement constructor
- [ ] Implement `activate()` lifecycle hook
- [ ] Implement `deactivate()` lifecycle hook
- [ ] Implement `uninstall()` lifecycle hook
- [ ] Implement `upgrade()` lifecycle hook
- [ ] Implement `runMigrations()` helper - uses ModMigrationManager
- [ ] Implement `rollbackMigrations()` helper - for deactivation
- [ ] Implement `registerPermissions()` helper
- [ ] Implement `config()` helper
- [ ] Write documentation

**Migration system design:**
- Mods place SQL files in `mods/{mod_id}/migrations/001_create_tables.sql`
- `AbstractMod::runMigrations()` calls `ModMigrationManager::run($modId, $migrationsPath)`
- Manager reads `bb_mod_migrations` to check executed migrations
- Executes pending migrations in transaction
- Records execution in `bb_mod_migrations` table
- No Phinx for mods - simple SQL file execution with tracking

**3.2 Config isolation**
- [ ] Move user configs to `config.local.php`
- [ ] Add `config('mods.{id}')` namespace
- [ ] Implement config merge strategy
- [ ] Add validation for mod configs
- [ ] Create migration tool for existing `config.php`

**3.3 Template system updates**
- [ ] Add hook points to templates
- [ ] Implement `MOD_HOOK_*` variables
- [ ] Add template override mechanism
- [ ] Test template rendering with mods

#### Deliverables

- AbstractMod base class
- Config isolation working
- Template hook points
- Migration tool for configs

#### Testing

```php
// Test AbstractMod lifecycle
$mod = new KarmaMod($manifest, $path);
$mod->activate();
assert(DB()->table_exists(BB_KARMA));

$mod->deactivate();
assert($mod->isActive() === false);

// Test config isolation
config()->set('mods.karma.hide', -500);
$hide = $mod->config('hide', -1000);
assert($hide === -500);
```

---

## Phase 2: Tooling (3 weeks)

**Goal:** Build CLI tools and migration utilities.

### Week 4: CLI Foundation

#### Tasks

**4.1 CLI tool structure** (`/mods.php`)
- [ ] Create command-line entry point
- [ ] Implement argument parsing
- [ ] Add subcommand routing
- [ ] Implement help system
- [ ] Add colored output (ANSI)

**4.2 Basic commands**
- [ ] `php mods.php list` - show all mods
- [ ] `php mods.php show <id>` - show mod details
- [ ] `php mods.php activate <id>` - activate mod
- [ ] `php mods.php deactivate <id>` - deactivate mod
- [ ] Add `--force` and `--dry-run` flags

**4.3 ModManager class** (`/src/ModSystem/ModManager.php`)
- [ ] Implement `activate()` method
- [ ] Implement `deactivate()` method
- [ ] Implement `getInstalled()` method
- [ ] Implement `getActive()` method
- [ ] Add transaction support (rollback on error)
- [ ] Write unit tests

#### Deliverables

- Working CLI tool
- Basic mod management commands
- ModManager class

#### Example Output

```
$ php mods.php list

Installed Mods:
┌────────────┬─────────┬────────┬──────────────────────┐
│ ID         │ Version │ Status │ Description          │
├────────────┼─────────┼────────┼──────────────────────┤
│ karma      │ 1.0.0   │ ✅     │ User karma system    │
│ automod    │ 2.1.0   │ ❌     │ Auto moderation      │
│ bbcodes    │ 1.5.0   │ ⚠️      │ Needs update         │
└────────────┴─────────┴────────┴──────────────────────┘

$ php mods.php activate karma
✓ Activated karma v1.0.0
✓ Ran 3 migrations
✓ Registered 2 permissions
✓ Cleared cache
```

---

### Week 5: Migration Tool (Parser)

#### Tasks

**5.1 Instruction parser** (`/src/ModSystem/Migration/Parser.php`)
- [ ] Parse "Open file X" lines
- [ ] Parse "Find: ..." lines
- [ ] Parse "After insert: ..." lines
- [ ] Parse SQL blocks
- [ ] Parse file copies
- [ ] Extract metadata (author, description)
- [ ] Write tests for various formats

**5.2 AST builder**
- [ ] Build Abstract Syntax Tree from instructions
- [ ] Identify modification types:
  - Config additions
  - Function additions
  - Hook insertions
  - Template modifications
  - New files
- [ ] Detect conflicts (overlapping edits)

**5.3 Compatibility checker**
- [ ] Check if file still exists in v2.9
- [ ] Check if "Find" string exists
- [ ] Check if hook points available
- [ ] Generate compatibility report

#### Deliverables

- Instruction parser
- AST builder
- Compatibility checker

#### Testing

```php
$parser = new Parser();
$ast = $parser->parse('old_mods/karma.txt');

assert($ast->getFileEdits() === 8);
assert($ast->getSQLStatements() === 4);
assert($ast->getNewFiles() === 2);

$compatibility = $ast->checkCompatibility();
assert($compatibility->isFullyCompatible() === false);
assert(count($compatibility->getManualFixesNeeded()) === 2);
```

---

### Week 6: Migration Tool (Generator)

#### Tasks

**6.1 Structure generator** (`/src/ModSystem/Migration/Generator.php`)
- [ ] Generate `/mods/{id}/` directory
- [ ] Generate `manifest.json`
- [ ] Generate `Mod.php` skeleton
- [ ] Generate `hooks.php` from AST
- [ ] Generate `config.php` from config edits
- [ ] Generate `README.md` with instructions

**6.2 Hook converter**
- [ ] Convert "insert after" → `add_action()`
- [ ] Convert "wrap with if" → `add_filter()`
- [ ] Convert template edits → template hooks
- [ ] Generate migration SQL from SQL blocks

**6.3 Manual patch generator**
- [ ] Identify incompatible edits
- [ ] Generate `MANUAL_PATCHES.md`
- [ ] Include diff snippets
- [ ] Suggest workarounds

**6.4 Interactive mode**
- [ ] Prompt for mod metadata
- [ ] Show preview of generated files
- [ ] Allow editing before writing
- [ ] Confirm before creating mod

#### Deliverables

- Complete migration tool
- Interactive mode
- Manual patch documentation

#### Example

```
$ php mods.php migrate --from=old_mods/karma.txt --interactive

Analyzing instructions...
✓ Parsed 15 modifications in 8 files
✓ Found 4 SQL statements
✓ Found 2 new files

Compatibility check...
✓ 13/15 modifications compatible with hook system
⚠️ 2/15 require manual override

Generating mod structure...
✓ Created /mods/karma/
✓ Generated manifest.json
✓ Generated Mod.php
✓ Generated hooks.php
✓ Generated config.php
✓ Copied ajax/karma.php
✓ Created MANUAL_PATCHES.md

Preview hooks.php? [Y/n] y

Hook::add_filter('post.can_edit', function($can, $post, $user) {
    if ($user['readonly'] != 0) return false;
    return $can;
}, 10, 3);

Edit before saving? [y/N] n

Create mod? [Y/n] y

✓ Mod 'karma' created successfully!

Next steps:
1. Review /mods/karma/
2. Apply manual patches from MANUAL_PATCHES.md
3. Test: php mods.php activate karma
```

---

## Phase 3: Admin UI (2 weeks)

**Goal:** Build web-based mod management interface.

### Week 7: Basic UI

#### Tasks

**7.1 Admin page** (`/admin/mods.php`)
- [ ] Create page structure
- [ ] Implement authentication check (admin only)
- [ ] Add permission check (`manage_mods`)
- [ ] Create layout template

**7.2 Mod list view**
- [ ] Display installed mods table
- [ ] Show status (active/inactive/update available)
- [ ] Add activate/deactivate buttons
- [ ] Add search/filter functionality
- [ ] Implement pagination

**7.3 Mod detail view**
- [ ] Show full manifest data
- [ ] Display current config
- [ ] Show installed version vs available version
- [ ] Display dependencies
- [ ] Show changelog (if available)

**7.4 Activation/Deactivation**
- [ ] Implement AJAX activation
- [ ] Show progress indicator
- [ ] Display errors clearly
- [ ] Add confirmation dialogs
- [ ] Log all operations

#### Deliverables

- Admin page `/admin/mods.php`
- Mod list and detail views
- Activate/deactivate functionality

---

### Week 8: Advanced Features

#### Tasks

**8.1 Mod installation**
- [ ] Upload ZIP file form
- [ ] Validate ZIP structure
- [ ] Extract to `/mods/` directory
- [ ] Run installation (migrations, permissions)
- [ ] Show installation log

**8.2 Install from URL**
- [ ] URL input form
- [ ] Download with progress
- [ ] Validate before installing
- [ ] Same installation flow as ZIP

**8.3 Settings page** (if mod has config)
- [ ] Generate form from config schema
- [ ] Support input types (text, checkbox, select, array)
- [ ] Validate on save
- [ ] Clear mod cache on save

**8.4 Logs viewer**
- [ ] Display `bb_mod_logs` table
- [ ] Filter by mod, action, date
- [ ] Show error details
- [ ] Export logs as CSV

**8.5 Update checker**
- [ ] Check GitHub releases for updates
- [ ] Compare versions
- [ ] Show "update available" badge
- [ ] Link to download page

#### Deliverables

- Installation via ZIP/URL
- Settings page for mods
- Logs viewer
- Update checker

---

## Phase 4: Documentation (2 weeks)

**Goal:** Write comprehensive documentation for all audiences.

### Week 9: Developer Documentation

#### Tasks

**9.1 API Reference** (`/docs/v2.9-mod-system/API-REFERENCE.md`)
- [ ] Document Hook system API
- [ ] Document all 50+ core hooks
- [ ] Document ModLoader API
- [ ] Document AbstractMod API
- [ ] Include code examples for each

**9.2 Mod Development Guide** (`/docs/v2.9-mod-system/MOD-DEVELOPMENT.md`)
- [ ] Getting started tutorial
- [ ] Project structure explanation
- [ ] Manifest specification
- [ ] Hook usage patterns
- [ ] Template integration
- [ ] Testing guide
- [ ] Publishing guidelines

**9.3 Code examples**
- [ ] Create example mod from scratch (tutorial)
- [ ] Fully commented Karma mod
- [ ] Common patterns (AJAX, templates, config)

#### Deliverables

- API reference documentation
- Mod development guide
- Code examples

---

### Week 10: User Documentation

#### Tasks

**10.1 Migration Guide** (`/docs/v2.9-mod-system/MIGRATION-GUIDE.md`)
- [ ] Old vs new comparison
- [ ] Migration tool usage
- [ ] Step-by-step manual migration
- [ ] Common issues and solutions
- [ ] FAQ

**10.2 Admin Guide**
- [ ] How to install mods
- [ ] How to activate/deactivate
- [ ] How to configure mods
- [ ] How to troubleshoot
- [ ] Security best practices

**10.3 Update Guide**
- [ ] How to update from v2.8 to v2.9
- [ ] What breaks (old mods)
- [ ] How to migrate existing mods
- [ ] Rollback instructions

**10.4 Video tutorials** (optional)
- [ ] "Installing your first mod"
- [ ] "Migrating an old mod"
- [ ] "Creating a simple mod"

#### Deliverables

- Migration guide
- Admin guide
- Update guide
- (Optional) Video tutorials

---

## Phase 5: Beta & Release (4 weeks)

**Goal:** Test with real users and release stable version.

### Week 11-12: Closed Beta

#### Tasks

**11.1 Beta program setup**
- [ ] Select 5-10 trusted admins
- [ ] Provide beta build
- [ ] Set up feedback channel (Discord/Telegram)
- [ ] Create bug report template

**11.2 Testing priorities**
- [ ] Migrate top-10 mods manually
- [ ] Test on different PHP versions (8.2, 8.3, 8.4)
- [ ] Test on different DB engines (MySQL, MariaDB, Percona)
- [ ] Load testing with 10+ active mods
- [ ] Security audit

**11.3 Bug fixing**
- [ ] Triage reported issues
- [ ] Fix critical bugs (data loss, security)
- [ ] Fix high-priority bugs (broken functionality)
- [ ] Document known issues

**11.4 Performance optimization**
- [ ] Profile page load times
- [ ] Optimize hook execution
- [ ] Optimize mod loading
- [ ] Add caching where needed

#### Deliverables

- Beta release v3.0.0-beta1
- Bug fixes and improvements
- Performance benchmarks

---

### Week 13: Open Beta

#### Tasks

**13.1 Release beta to community**
- [ ] Announce on forum
- [ ] Create beta download page
- [ ] Provide migration guide
- [ ] Set up support thread

**13.2 Community support**
- [ ] Answer questions on forum
- [ ] Help migrate popular mods
- [ ] Create video walkthroughs
- [ ] Update documentation based on feedback

**13.3 Final testing**
- [ ] Regression testing
- [ ] User acceptance testing
- [ ] Documentation review
- [ ] Translation (Russian/English)

#### Deliverables

- Open beta release v3.0.0-beta2
- Community feedback incorporated
- Updated documentation

---

### Week 14: Stable Release

#### Tasks

**14.1 Release preparation**
- [ ] Finalize changelog
- [ ] Update version numbers
- [ ] Tag release in Git
- [ ] Build release packages (ZIP, tar.gz)
- [ ] Generate checksums (SHA256)

**14.2 Release v3.0.0**
- [ ] Publish on GitHub releases
- [ ] Update torrentpier.com
- [ ] Announce on forum (sticky thread)
- [ ] Send newsletter
- [ ] Update documentation site

**14.3 Post-release support**
- [ ] Monitor forum for issues
- [ ] Quick hotfix releases if critical bugs
- [ ] Help users migrate
- [ ] Collect feedback for v2.10.0

#### Deliverables

- **TorrentPier v3.0.0 stable**
- Release announcement
- Post-release support plan

---

## Testing Strategy

### Unit Tests

**Coverage goal:** >80%

**Test files:**
- `tests/Hooks/HookTest.php`
- `tests/ModSystem/ModLoaderTest.php`
- `tests/ModSystem/ModManagerTest.php`
- `tests/ModSystem/AbstractModTest.php`

**Tools:**
- PHPUnit 10+
- Mockery for mocks
- PHP CS Fixer for code style

**Run:**
```bash
vendor/bin/phpunit tests/
```

---

### Integration Tests

**Scenarios:**

1. **Install and activate mod**
   - Install Karma mod via CLI
   - Activate via CLI
   - Verify hooks registered
   - Verify database tables created
   - Deactivate
   - Verify hooks unregistered

2. **Mod with dependencies**
   - Install mod A (depends on mod B)
   - Try to activate A without B → should fail
   - Install and activate B
   - Activate A → should succeed

3. **Breaking change handling**
   - Install mod requiring TP >=3.0.0
   - Downgrade TP to 2.8.8 (simulated)
   - Try to activate mod → should fail with version error

4. **Config isolation**
   - Set `config('mods.karma.hide', -500)`
   - Update `config.local.php` to override
   - Verify mod sees overridden value

---

### Performance Tests

**Benchmarks:**

```php
// Baseline: no mods
$start = microtime(true);
include 'index.php';
$time_no_mods = microtime(true) - $start;

// With 10 active mods
activateMods(['karma', 'automod', 'bbcodes', ...]);
$start = microtime(true);
include 'index.php';
$time_with_mods = microtime(true) - $start;

$overhead = $time_with_mods - $time_no_mods;
assert($overhead < 0.01); // <10ms overhead
```

**Load testing:**
- Apache Bench: 1000 requests, 10 concurrent
- Monitor memory usage (should stay <20MB)
- Monitor query count (should not increase)

---

### Security Tests

**Scenarios:**

1. **Malicious manifest**
   - Invalid JSON → parser should reject
   - Missing required fields → should reject
   - Executable code in manifest → should sanitize

2. **Path traversal**
   - Mod tries to write to `../../config.php`
   - ModLoader should prevent escape from `/mods/`

3. **SQL injection in mod**
   - Mod with unescaped user input
   - Should be caught by mod author (docs emphasize this)
   - Demonstrate in example mod

4. **XSS in admin UI**
   - Mod name contains `<script>alert(1)</script>`
   - Admin UI should escape output

---

## Rollback Plan

### If critical bug found post-release

**Scenario:** v3.0.0 released, major bug discovered (e.g., data loss).

**Actions:**

1. **Immediate response** (< 1 hour)
   - Post warning on forum
   - Update download page with notice
   - Provide workaround if available

2. **Hotfix release** (< 24 hours)
   - Create `hotfix/v2.9.1` branch
   - Fix bug
   - Test thoroughly
   - Release v2.9.1
   - Announce on all channels

3. **Rollback instructions** (for affected users)
   - Restore database backup
   - Revert to v2.8.8
   - Wait for v2.9.1 stable

---

### If adoption fails

**Scenario:** Users don't migrate, stay on v2.8.x.

**Actions:**

1. **Analyze feedback** (week 1-2)
   - Why aren't users migrating?
   - Is migration tool insufficient?
   - Is documentation unclear?

2. **Improve migration experience** (week 3-4)
   - Better automation
   - More examples
   - Video tutorials
   - One-on-one support

3. **Extend v2.8.x support** (if needed)
   - Security updates for v2.8.x for 6 more months
   - Give community more time to migrate

4. **Deprecation of old system** (v3.0.0 delayed)
   - If <50% migrate by 6 months
   - Delay v3.0.0 removal of old system
   - Continue dual support in v2.10.0

---

## Dependencies

### External Dependencies

**PHP Extensions:**
- `json` (manifest parsing)
- `zip` (mod installation)
- `mbstring` (string handling)
- `curl` (update checker)

**Composer Packages:**
- None (keep dependencies minimal)

**Optional:**
- `symfony/console` (better CLI, future)
- `league/commonmark` (render README, future)

---

### Internal Dependencies

**Phase dependencies:**
- Phase 2 depends on Phase 1 (tooling needs foundation)
- Phase 3 depends on Phase 1 (UI needs ModManager)
- Phase 4 can start early (docs for completed features)
- Phase 5 depends on Phases 1-4 (all features ready)

**Critical path:**
```
Phase 1 (Foundation) → Phase 2 (Tooling) → Phase 5 (Release)
                    ↘ Phase 3 (Admin UI) ↗
Phase 4 (Docs) can run in parallel
```

**Fast-track option:**
- Skip Phase 3 (Admin UI) initially
- Release v3.0.0 with CLI only
- Add Admin UI in v2.9.1 (saves 2 weeks)

---

## Risk Management

### High-Risk Items

**1. Community resistance**
- **Mitigation:** Early communication, migration tool, support
- **Contingency:** Extend v2.8 support if needed

**2. Migration tool accuracy**
- **Mitigation:** Test on top-30 mods during development
- **Contingency:** Manual migration guide for complex cases

**3. Performance regression**
- **Mitigation:** Benchmark at each phase
- **Contingency:** Optimize before release, or delay release

**4. Security vulnerability in mod system**
- **Mitigation:** Security audit, code review
- **Contingency:** Rapid hotfix process

---

## Success Criteria

### Phase 1 (Foundation)
- ✅ Hook system working with 50+ hooks
- ✅ ModLoader loads and activates mods
- ✅ Zero core file modifications by mods
- ✅ Unit test coverage >80%

### Phase 2 (Tooling)
- ✅ CLI tool works for all mod operations
- ✅ Migration tool converts 80% of mods automatically
- ✅ ModManager handles errors gracefully

### Phase 3 (Admin UI)
- ✅ Web UI can install/activate/configure mods
- ✅ Logs are visible and filterable
- ✅ Update checker works

### Phase 4 (Documentation)
- ✅ API reference complete
- ✅ Migration guide written
- ✅ Mod development tutorial published

### Phase 5 (Release)
- ✅ Beta feedback incorporated
- ✅ Top-10 mods migrated
- ✅ v3.0.0 stable released
- ✅ <5% bug reports in first month

---

## Timeline Summary

| Phase             | Duration | End Date | Key Deliverable         |
|-------------------|----------|----------|-------------------------|
| 1. Foundation     | 3 weeks  | Week 3   | Hook system + ModLoader |
| 2. Tooling        | 3 weeks  | Week 6   | CLI + migration tool    |
| 3. Admin UI       | 2 weeks  | Week 8   | Web management          |
| 4. Documentation  | 2 weeks  | Week 10  | Complete docs           |
| 5. Beta & Release | 4 weeks  | Week 14  | v3.0.0 stable           |

**Total:** 14 weeks (3.5 months)

**Fast-track (skip Admin UI initially):** 12 weeks (3 months)

---

**End of Implementation Roadmap**
