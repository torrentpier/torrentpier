---
sidebar_position: 2
title: Semantic URLs
---

# Semantic URLs

TorrentPier supports SEO-friendly semantic URLs that improve search engine optimization and user experience.

## URL format

URLs follow the pattern: `/{type}/{slug}.{id}/`

| Type    | Old URL                         | New URL                      |
|---------|---------------------------------|------------------------------|
| Topic   | `/viewtopic?t=123`              | `/topic/my-topic-title.123/` |
| Forum   | `/viewforum?f=5`                | `/forum/hd-video.5/`         |
| Profile | `/profile?mode=viewprofile&u=2` | `/profile/admin.2/`          |

### Additional profile routes

| Route                         | Description        |
|-------------------------------|--------------------|
| `/profile/{slug}.{id}/email/` | Send email to user |
| `/profile/bonus/`             | User bonus page    |
| `/profile/watchlist/`         | Topic watchlist    |
| `/register/`                  | Registration page  |
| `/settings/`                  | Profile settings   |
| `/password-recovery/`         | Password recovery  |
| `/activate/{user_id}/{key}/`  | Account activation |

## Generating URLs

### PHP

Use the `url()` helper which returns a `UrlBuilder` instance:

```php
// Topic URL
url()->topic($topicId, $topicTitle);
// Result: /topic/my-topic-title.123/

// Forum URL
url()->forum($forumId, $forumName);
// Result: /forum/hd-video.5/

// Profile URL
url()->profile($userId, $username);
// Result: /profile/admin.2/

// With query parameters
url()->topic($id, $title, ['start' => 20]);
// Result: /topic/my-topic-title.123/?start=20

// With URL fragment/anchor (use '_fragment' key)
url()->topic($id, $title, ['view' => 'newest', '_fragment' => 'newest']);
// Result: /topic/my-topic-title.123/?view=newest#newest

// Static routes
url()->register();      // /register/
url()->settings();      // /settings/
url()->passwordRecovery();  // /password-recovery/
url()->profileBonus();  // /profile/bonus/
url()->profileWatchlist();  // /profile/watchlist/
url()->profileEmail($id, $username);  // /profile/username.123/email/
url()->activate($userId, $key);  // /activate/123/abc123/
```

### Twig templates

The `url` object is available globally in all templates:

```twig
{# Topic link #}
<a href="{{ url.topic(t.TOPIC_ID, t.TOPIC_TITLE) }}">{{ t.TOPIC_TITLE }}</a>

{# Forum link #}
<a href="{{ url.forum(f.FORUM_ID, f.FORUM_NAME) }}">{{ f.FORUM_NAME }}</a>

{# Profile link #}
<a href="{{ url.profile(u.USER_ID, u.USERNAME) }}">{{ u.USERNAME }}</a>

{# With query params #}
<a href="{{ url.topic(t.TOPIC_ID, t.TOPIC_TITLE, {start: 20}) }}">Page 2</a>

{# With URL fragment/anchor (use _fragment key) #}
<a href="{{ url.topic(t.TOPIC_ID, t.TOPIC_TITLE, {view: 'newest', _fragment: 'newest'}) }}">New posts</a>
{# Result: /topic/my-topic.123/?view=newest#newest #}

{# Static routes #}
<a href="{{ url.register() }}">Register</a>
<a href="{{ url.settings() }}">Settings</a>
```

## Slug generation

Slugs are generated automatically using these rules:

1. **Transliteration**: Cyrillic and other non-ASCII characters are romanized
   - `Бугония` → `bugonia`
   - `日本語` → `ri-ben-yu`

2. **Normalization**: Special characters become hyphens
   - `Hello World!` → `hello-world`
   - `test--multiple---hyphens` → `test-multiple-hyphens`

3. **Length limit**: Slugs are truncated at 50 characters (word boundary)

4. **Empty handling**: Empty titles result in `/.{id}/` which still works

```php
use TorrentPier\Helpers\Slug;

Slug::generate('Hello World!');  // 'hello-world'
Slug::generate('Бугония');       // 'bugonia'
Slug::generate('');              // ''
```

## URL fragments (anchors)

To include a URL fragment (hash anchor) in the generated URL, use the special `_fragment` key in the params array. This is preferable to concatenating the fragment manually because it keeps all URL components in one place.

```php
// PHP
url()->topic($id, $title, ['_fragment' => 'post-456']);
// Result: /topic/my-topic.123/#post-456

// Combined with query parameters
url()->topic($id, $title, ['start' => 20, '_fragment' => 'post-456']);
// Result: /topic/my-topic.123/?start=20#post-456
```

```twig
{# Twig #}
{{ url.topic(id, title, {_fragment: 'newest'}) }}

{# Combined with query params #}
{{ url.topic(id, title, {view: 'newest', _fragment: 'newest'}) }}
```

The `_fragment` key is stripped from query parameters and appended to the end of the URL after the `#` symbol. This follows standard URL structure: `path?query#fragment`.

## Redirects

### Legacy URL redirects

Old-style URLs automatically redirect (301) to semantic URLs:

```
GET /viewtopic?t=123 → 301 → /topic/my-topic.123/
GET /viewforum?f=5   → 301 → /forum/hd-video.5/
GET /profile?mode=viewprofile&u=2 → 301 → /profile/admin.2/
```

### Canonical URL enforcement

If the slug in the URL doesn't match the current title, a 301 redirect occurs:

```
GET /topic/old-title.123/ → 301 → /topic/new-title.123/
```

This happens automatically when controllers call `UrlBuilder::assertCanonical()`.

### Trailing slash enforcement

URLs without a trailing slash redirect to the canonical form:

```
GET /topic/my-topic.123 → 301 → /topic/my-topic.123/
```

### POST requests

POST requests to legacy URLs are processed normally (cannot redirect POST data), but include a `Link` header with the canonical URL:

```
Link: </topic/my-topic.123/>; rel="canonical"
```

## Route configuration

Routes are defined in `routes/web.php`:

```php
// Semantic routes (must come before legacy routes)
$router->any('/topic/{params}/', new RouteAdapter('topic'));
$router->any('/forum/{params}/', new RouteAdapter('forum'));
$router->any('/profile/{params}/', new RouteAdapter('profile'));

// Trailing slash redirects
$router->get('/topic/{params}', new TrailingSlashRedirect());

// Legacy redirects
$router->any('/viewtopic', new LegacyRedirect('topic', $fallbackController));
$router->any('/viewforum', new LegacyRedirect('forum', $fallbackController));
```

## Architecture

### Key classes

| Class                                               | Purpose                              |
|-----------------------------------------------------|--------------------------------------|
| `TorrentPier\Helpers\Slug`                          | Slug generation with transliteration |
| `TorrentPier\Router\SemanticUrl\UrlBuilder`         | URL factory (singleton)              |
| `TorrentPier\Router\SemanticUrl\RouteAdapter`       | Handles semantic URL requests        |
| `TorrentPier\Router\SemanticUrl\LegacyRedirect`     | Redirects old URLs                   |
| `TorrentPier\Http\Middleware\TrailingSlashRedirect` | Enforces trailing slash              |

### Request flow

1. Request arrives at `/topic/my-topic.123/`
2. `RouteAdapter` extracts ID (123) and slug (my-topic)
3. Sets query parameters for legacy controller compatibility
4. Stores semantic route info in request attributes (`semantic_route`, `semantic_route_type`, `semantic_route_slug`)
5. Delegates to legacy controller via `LegacyAdapter`
6. Controller calls `UrlBuilder::assertCanonical()` after loading data
7. If the slug doesn't match the current title, `RedirectException` is thrown and converted to 301 redirect

## Migration guide

### Updating templates

Replace old URL patterns with the new helper:

```diff
- <a href="{TOPIC_URL}{t.TOPIC_ID}">{t.TOPIC_TITLE}</a>
+ <a href="{{ url.topic(t.TOPIC_ID, t.TOPIC_TITLE) }}">{t.TOPIC_TITLE}</a>

- <a href="{FORUM_URL}{f.FORUM_ID}">{f.FORUM_NAME}</a>
+ <a href="{{ url.forum(f.FORUM_ID, f.FORUM_NAME) }}">{f.FORUM_NAME}</a>
```

### Adding canonical checks to controllers

After loading entity data, add:

```php
if (request()->attributes->get('semantic_route')) {
    \TorrentPier\Router\SemanticUrl\UrlBuilder::assertCanonical(
        request()->attributes->get('semantic_route_type'),
        $id,
        $title
    );
}
```

Note: `assertCanonical()` throws `RedirectException` if the slug doesn't match. This is caught by `LegacyAdapter` and converted to a 301 redirect response.

## SEO benefits

- **Readable URLs**: Users and search engines understand URL structure
- **Keywords in URLs**: Topic/forum names appear in URLs
- **Consistent URLs**: Canonical redirects prevent duplicate content
- **301 redirects**: Search engines update their indexes automatically
- **Proper trailing slash**: Consistent URL format
