# Conservation Plan — TorrentPier 3.0.0 (Ox)

**Status:** in progress (branch `ox`)
**Final release codename:** Ox
**Final release date:** 11-05-2026
**Project state after release:** archived — no further patches

## Project context

TorrentPier 3.0.0 (Ox) is the final release of this codebase. The project enters
conservation in May 2026. The successor is being written from scratch and may
ship under a different name; no timeline yet. The community forum at
`torrentpier.com` is being retired; a read-only archive lives at
`https://ox.torrentpier.com/`.

After 3.0.0 ships:

- No more feature work, dependency bumps, or auto-updater calls.
- GitHub Issues stay open as a low-noise channel; no contribution templates.
- Security reports go to `admin@torrentpier.com` but are acknowledged rather
  than patched.

## Canonical EOL notice (single source of truth)

Use this wording (or a context-trimmed variant) everywhere a conservation
notice appears — README banner, admin banner, welcome post, `SECURITY.md`,
CLI `Status:` line.

> **TorrentPier 3.0 (Ox) is the final release of this codebase.**
> The project entered conservation in May 2026. No further patches, security
> fixes, or feature work are planned.
> The community forum is preserved read-only at <https://ox.torrentpier.com/>.
> A new generation of the engine is being written from scratch, possibly under
> a different name. There is no timeline yet.
> If you self-host this release, you are responsible for your own security
> maintenance.

## Work areas

### 1. Auto-updater removal

- [ ] Delete `src/Updater.php` (entire file, ~255 lines)
- [ ] Delete `library/includes/datastore/build_check_updates.php` (~79 lines)
- [ ] Remove `UPDATER_URL`, `UPDATER_FILE` defines — `library/defines.php:63-64`
- [ ] Drop the `updater` block from `config/services.php` (`updater.enabled`,
      `updater.allow_pre_releases`)
- [ ] Remove `'check_updates' => 'build_check_updates.php'` —
      `src/Cache/DatastoreManager.php:54`
- [ ] Remove `'check_updates' => 'Update check cache'` —
      `app/Console/Commands/Rebuild/DatastoreCommand.php:57`
- [ ] Remove `datastore()->update('check_updates');` —
      `library/includes/cron/jobs/board_maintenance.php:55`
- [ ] Remove updater fetch + template assigns —
      `app/Http/Controllers/Admin/index.php:17-21` and `:85-95`
- [ ] Remove `<!-- BEGIN updater -->` block —
      `resources/views/admin/index.tpl:170-177` (replaced by EOL banner in §7)
- [ ] Remove `readUpdaterFile()` —
      `library/includes/functions.php:1949-1956` (verify no callers remain)
- [ ] Remove lang keys from `library/language/source/main.php`:
      `TP_VERSION` (1400), `TP_RELEASE_DATE` (1401),
      `VERSION_INFORMATION` (1733), `UPDATE_AVAILABLE` (1734)

### 2. Admin "Mods" / Marketplace removal

- [ ] Delete `app/Http/Controllers/Admin/admin_modifications.php`
- [ ] Delete `resources/views/admin/admin_modifications.tpl`
- [ ] Delete `config/mods.php`
- [ ] Remove route entry — `routes/admin.php:44`
- [ ] Remove two nav links to `config_mods` —
      `resources/views/admin/admin_board.tpl:8,155`
- [ ] Remove lang keys from `library/language/source/main.php`:
      `MODS_EXPLAIN` (1520), `MARKETPLACE` + `MODS_*` (2340-2362)
- [ ] Remove `MARKETPLACE_API_*` lines — `.env.example:75-77`

### 3. Forum + docs URL rewrites in code/config

- [ ] `composer.json:29` — `support.email`: `support@torrentpier.com` →
      `admin@torrentpier.com`
- [ ] `composer.json:31` — `support.forum`: `https://torrentpier.com` →
      `https://ox.torrentpier.com/`
- [ ] `composer.json:32` — remove `support.docs` key entirely
- [ ] `app/Console/Commands/System/AboutCommand.php:87` — `Website:` line
      → `https://ox.torrentpier.com/`
- [ ] `app/Console/Commands/System/AboutCommand.php:88` — remove the `Docs:`
      line; add `Status: Conservation — final release (May 2026)`

### 4. `/docs` Docusaurus teardown — delete the entire tree

- [ ] Delete the whole `docs/` directory (markdown, blog, sidebars,
      `docusaurus.config.ts`, `package.json`, `package-lock.json`, `static/`,
      `src/`)
- [ ] Delete `.github/workflows/docs.yml` (covered again in §6)
- [ ] Remove Docusaurus-specific lines from `.gitignore:86-90`
      (`/docs/.docusaurus`, `/docs/.cache-loader`, `/docs/build`,
      `/docs/node_modules`)
- [ ] After §3 and §5: re-grep for `docs.torrentpier.com` to confirm zero
      remaining references

### 5. README cleanup (`README.md`)

- [ ] Line 1 — replace logo URL with
      `https://files-ox.torrentpier.com/tp_ox_long.svg`; anchor target →
      `https://github.com/torrentpier` (or repo root)
- [ ] Line 11 — keep Crowdin badge as-is (user decision)
- [ ] Line 12 — **remove the nightly badge** (`nightly.link` link + shield)
- [ ] Line 23 — "official support forum" link →
      `https://ox.torrentpier.com/`; reword sentence to drop "download
      modifications" copy
- [ ] Lines 25-37 — rewrite "Current status" section: short EOL block
      (per Canonical EOL Notice). Move the 3.0 modernization bullets into the
      final CHANGELOG entry rather than the README
- [ ] Line 37 — drop both `docs.torrentpier.com` links (Upgrade Guide,
      documentation)
- [ ] Line 73 — replace the docs link in "Installation"; point to
      `install/` and `php bull app:install` directly
- [ ] Line 83 — replace the Docker docs link; point to `docker-compose.yml`
      / `Dockerfile` in repo
- [ ] Lines 54-61 — "Demo" section: **DECIDE** (see §10)
- [ ] Insert a prominent EOL banner at the very top (blockquote) using the
      Canonical EOL Notice
- [ ] Line 109 — `tests/README.md` link — verify file exists, keep

### 6. `.github` cleanup

**Workflows — keep**

- [x] `cd.yml` — release artifacts
- [x] `cs.yml` — PHP-CS-Fixer on PRs (user decision)
- [x] `tests.yml` — Pest on PHP 8.4 / 8.5 (user decision)
- [x] `sync-source-language.yml` — Crowdin source sync (user decision)

**Workflows — remove**

- [ ] `ci.yml` — nightly builds
- [ ] `claude.yml` — AI assistant trigger
- [ ] `docs.yml` — Docusaurus deploy (also in §4)
- [ ] `schedule.yml` — cron changelog

**`.github` root — keep**

- [x] `FUNDING.yml` — sponsorship config (user decision)
- [x] `SECURITY.md` — keep, rewrite in §7

**`.github` root — remove**

- [ ] `CODE_OF_CONDUCT.md`
- [ ] `CONTRIBUTING.md`
- [ ] `PULL_REQUEST_TEMPLATE.md`
- [ ] `ISSUE_TEMPLATE/` (entire directory: `bug_report.yml`,
      `feature---enhancement-request.md`)
- [ ] `dependabot.yml`

### 7. EOL notices — surface the Canonical EOL Notice everywhere

- [ ] **README banner** at the top of `README.md` (above the logo or directly
      after the badges row) — see §5
- [ ] **Admin panel banner** — replace the removed updater block
      (`resources/views/admin/index.tpl:170-177`) with a permanent EOL notice
      linking `https://ox.torrentpier.com/`
- [ ] **CLI `Status:` line** — `AboutCommand.php` — see §3
- [ ] **`.github/SECURITY.md` rewrite** — replace "Supported Versions",
      "Reporting a Vulnerability", "Alternative Reporting", and "Disclosure
      Policy" sections with EOL / thanks-but-archived language. Keep "User
      Security Best Practices". Contact stays `admin@torrentpier.com`.

### 8. Seed migration (`database/migrations/20250619000002_seed_initial_data.php`)

- [x] Welcome topic title (line 880) — `Welcome to TorrentPier Ox`
      (already updated in prior commit)
- [x] Welcome post intro (line 922) — `TorrentPier Ox` (already updated)
- [ ] Welcome post body — rewrite into EOL tone using the Canonical EOL
      Notice. Replace `[url=https://torrentpier.com/]visit our forum[/url]`
      (line 926) with `https://ox.torrentpier.com/`. Decide demo line
      (line 931) per §10.
- [x] Seed admin email `admin@torrentpier.com` (line 180) — keep
      (installer overrides it on first run)
- [x] Seed bot email `bot@torrentpier.com` (line 142) — keep
- [ ] Release note: this seed only fires on **fresh installs**; existing
      instances already ran the migration. Document this in the final
      CHANGELOG so operators aren't surprised.

### 9. `CHANGELOG.md`

- [x] Header image — `Ox.png` (already updated)
- [ ] Rename `## [nightly](https://nightly.link/...)` (line 5) →
      `## 3.0.0 - 11-05-2026`; drop the `nightly.link` URL
- [ ] Hand-curate the final release entry (we are removing `schedule.yml`,
      so no more automated regeneration)
- [ ] Add a short conservation note immediately under the `# 📖 Change Log`
      title

### 10. Decisions deferred — do not silently change

- **Demo site `https://torrentpier.duckdns.org`.** Referenced in:
  - `README.md:56-58` (Demo section)
  - `database/migrations/20250619000002_seed_initial_data.php:931` (welcome
    post)
  - `app/Http/Controllers/Admin/admin_bt_forum_cfg.php:29` (announce-URL
    placeholder)
  - `resources/views/default/posting_tpl.twig:122-123` (BBCode poster
    example)
  - `library/includes/torrent_announce_urls.php:17` (commented example)

  Will the duckdns demo stay running post-release? If no → remove README
  section + welcome-post line. The other three are config defaults the
  operator overrides — they can stay regardless.

- **Out of scope, already gitignored:** `storage/framework/templates/*`
  cached `.php` template files contain stale `torrentpier.com/threads/260`
  links. Per `.gitignore:32`, the directory is not tracked — operator
  regenerates on first request. No action needed in this repo.

- **External SVG dependency:** the new logo URL
  `https://files-ox.torrentpier.com/tp_ox_long.svg` was verified live
  (HTTP 200, 17 434 bytes) at plan-write time. If long-term durability is a
  concern, mirror the file into the repo and link relatively. Not blocking.

## Release sequence (proposed)

1. Land §1–§3 (mechanical cleanups, no user-facing surprises) — one commit
   per area.
2. Land §4 (delete `docs/`) — single commit.
3. Land §5 and §6 (README + `.github`) — separate commits.
4. Land §7 and §8 (EOL copy in user-facing surfaces) — separate commits.
5. Hand-curate `CHANGELOG.md` (§9) as the last commit before tagging.
6. Tag `v3.0.0` on `master` after merging `ox`; let `cd.yml` build the
   release artifact.
7. Post-tag housekeeping: close any open Dependabot PRs; optionally archive
   the GitHub repo.

## Working notes

- Branch: `ox` (rebased off `master`, also holds the
  `chore: bump version to 3.0.0 and codename to Ox` commit).
- Local-only until the user clears it for push. Do not push `ox` yet.
- All code, comments, commit messages, and this document stay in English per
  project convention.
- Tick the checklist boxes as items land; do not delete them — they form the
  audit trail for the final release.
