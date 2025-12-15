---
sidebar_position: 6
title: Search System (Manticore)
---

# Search System Migration

TorrentPier now supports **Manticore Search** as a modern replacement for the legacy Sphinx API. All new projects are recommended to use Manticore, since it is fully compatible with the SphinxQL protocol but offers much more functionality.

## Why Manticore?

- Actively maintained and developed
- Drop-in compatibility with existing SphinxQL queries
- **Real-time indexes (RT)** — instant indexing without rebuilds
- Extended search operators (filters, full-text, boolean search, JSON fields)
- Replication and sharding support for high-load environments
- Works with common Sphinx client libraries

## Sync System

TorrentPier automatically synchronizes data with Manticore through helper functions:

```php
// Topics
sync_topic_to_manticore($topic_id, $topic_title, $forum_id, 'upsert');
sync_topic_to_manticore($topic_id, null, null, 'delete');

// Posts
sync_post_to_manticore($post_id, $post_text, $topic_title, $topic_id, $forum_id, 'upsert');
sync_post_to_manticore($post_id, null, null, null, null, 'delete');

// Users
sync_user_to_manticore($user_id, $username, 'upsert');
sync_user_to_manticore($user_id, null, 'delete');
```

Each sync function supports two modes:
- `upsert` — insert or update a record in the index
- `delete` — remove a record from the index

All calls are wrapped in `try/catch` and errors are logged to `manticore_errors.log` so that search issues do not affect forum availability.

## Configuration

```php
$bb_cfg['search_engine_type'] = 'manticore';
$bb_cfg['manticore_host'] = '127.0.0.1';
$bb_cfg['manticore_port'] = 9306;
$bb_cfg['search_fallback_to_mysql'] = true;
```

## Migration Steps

1. Install and run Manticore Search
2. Enable Manticore in `config/config.php`
3. Reindex existing posts and topics:
   1. Go to the `Admin Control Panel`
   2. On the main page, in the `Update` section, click `Reindex search`
   3. This will rebuild the search index with all existing content
