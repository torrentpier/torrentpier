---
sidebar_position: 3
title: Database Relationships
---

# Database Relationships & Entity Dependencies

## Overview

This document maps all key relationships in the TorrentPier database schema, identifying primary keys, foreign keys, and data dependencies. This analysis is crucial for designing the modern Laravel schema with proper Eloquent relationships.

## Core Entity Relationship Diagram

```text
Users ──┐
        │
        ├── Topics ──── Posts ──── Attachments ──── Files
        │    │           │             │
        │    │           └── PostText  └── AttachmentMetadata
        │    │
        │    └── Torrents ──── TrackerPeers
        │         │                │
        │         └── TorrentStats └── UserStats
        │
        ├── Groups ──── Permissions
        │
        └── Sessions
```

## Primary Entities & Their Relationships

### 1. Forum Hierarchy

```text
Categories (1:many) Forums (1:many) Topics (1:many) Posts
    │                   │               │               │
    └── forum_id        └── topic_id    └── post_id     └── PostText (1:1)
```

**Key Relationships:**
- `bb_categories.cat_id` → `bb_forums.cat_id`
- `bb_forums.forum_id` → `bb_topics.forum_id`
- `bb_topics.topic_id` → `bb_posts.topic_id`
- `bb_posts.post_id` → `bb_posts_text.post_id`

### 2. User System

```text
Users (many:many) Groups (1:many) Forums
  │                    │               │
  └── user_id          └── group_id    └── Permissions
```

**Key Relationships:**
- `bb_users.user_id` → `bb_user_group.user_id`
- `bb_groups.group_id` → `bb_user_group.group_id`
- `bb_groups.group_id` → `bb_auth_access.group_id`
- `bb_forums.forum_id` → `bb_auth_access.forum_id`

### 3. Torrent Integration (Critical Path)

```text
Topics (1:1) Torrents (1:1) AttachmentFiles
   │             │              │
   └── topic_id  └── attach_id  └── TorrentFile
                      │
                      └── TrackerPeers (1:many)
```

**Critical Relationships:**
- `bb_topics.topic_id` = `bb_bt_torrents.topic_id` **(1:1 - Core Integration)**
- `bb_bt_torrents.attach_id` = `bb_attachments_desc.attach_id` **(1:1)**
- `bb_posts.post_id` = `bb_attachments.post_id` **(1:many)**
- `bb_attachments.attach_id` = `bb_attachments_desc.attach_id` **(1:1)**

### 4. Tracker System

```text
Torrents (1:many) TrackerPeers
    │                    │
    └── topic_id        └── user_id → Users
```

**Key Relationships:**
- `bb_bt_torrents.topic_id` → `bb_bt_tracker.topic_id`
- `bb_users.user_id` → `bb_bt_tracker.user_id`
- `bb_users.user_id` → `bb_bt_users.user_id`

## Data Flow Dependencies

### Torrent Posting Flow

```text
1. User Authentication
   bb_users.user_id

2. Forum Selection
   bb_categories → bb_forums (permissions check)

3. Topic Creation
   bb_topics.topic_id (generated)

4. Post Creation
   bb_posts → bb_posts_text

5. File Upload
   bb_attachments_desc (torrent file)
   bb_attachments (link to post)

6. Torrent Registration
   bb_bt_torrents (links everything together)

7. Tracker Activation
   bb_bt_tracker (peer monitoring begins)
```

### Dependencies for Deletion

```text
Delete Torrent:
1. Remove tracker peers: bb_bt_tracker
2. Remove torrent stats: bb_bt_*stat tables
3. Remove torrent record: bb_bt_torrents
4. Remove attachment files: bb_attachments_desc, bb_attachments
5. Remove posts: bb_posts_text, bb_posts
6. Remove topic: bb_topics
```

## Orphaned Data Prevention

### Current Issues in Legacy
- No foreign key constraints allow orphaned records
- Manual cleanup required via cron jobs
- Inconsistent data possible

### Modern Solution

```sql
-- Cascade deletions where appropriate
ON DELETE CASCADE for content dependencies
ON DELETE SET NULL for optional references
ON DELETE RESTRICT for critical business data
```

## Complex Relationships

### Many-to-Many: Users ↔ Groups

```text
bb_users ←→ bb_user_group ←→ bb_groups
```

### Many-to-Many: Users ↔ Tracked Torrents

```text
bb_users ←→ bb_bt_tracker ←→ bb_bt_torrents
```

### Polymorphic: Attachments

```text
bb_attachments can belong to:
- bb_posts (forum attachments)
- bb_privmsgs (private message attachments)
```

## Denormalization in Legacy Schema

### Forum Statistics

```text
bb_forums.forum_posts     -- Cached from bb_posts count
bb_forums.forum_topics    -- Cached from bb_topics count
bb_topics.topic_replies   -- Cached from bb_posts count
bb_topics.topic_views     -- Cached/updated separately
```

### Torrent Statistics  

```text
bb_bt_torrents.complete_count    -- Cached completion count
bb_bt_torrents.seeder_last_seen  -- Cached timestamp
bb_users.user_posts              -- Cached post count
```

## Modern Laravel Relationships

### Eloquent Model Relationships

```php
// Topic Model
public function posts() { return $this->hasMany(Post::class); }
public function torrent() { return $this->hasOne(Torrent::class); }
public function forum() { return $this->belongsTo(Forum::class); }

// Torrent Model  
public function topic() { return $this->belongsTo(Topic::class); }
public function peers() { return $this->hasMany(TrackerPeer::class); }
public function attachment() { return $this->belongsTo(Attachment::class); }

// User Model
public function posts() { return $this->hasMany(Post::class, 'poster_id'); }
public function torrents() { return $this->hasMany(Torrent::class, 'poster_id'); }
public function trackerStats() { return $this->hasOne(TrackerUser::class); }
```

### Recommended Constraints

```sql
-- Strict constraints for data integrity
FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE RESTRICT

-- Indexes for performance
INDEX (topic_id, user_id) for tracker queries
INDEX (forum_id, topic_time) for forum browsing
INDEX (info_hash) for tracker announces
```

## Summary

The legacy schema has complex implicit relationships that need to be formalized with proper foreign key constraints in the modern Laravel implementation. The core `topic_id` integration between forum and tracker systems must be preserved while adding proper referential integrity.

Key improvements needed:
1. **Add foreign key constraints** for data integrity
2. **Remove buffer table dependencies** (replace with Laravel Queues)
3. **Implement proper cascading deletes** for cleanup
4. **Use Laravel conventions** for relationship naming and structure
