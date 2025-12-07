---
sidebar_position: 2
title: Database Schema Analysis
---

# Database Schema Analysis

## Overview

This document provides a comprehensive analysis of the legacy TorrentPier database schema, identifying all tables organized by functional domain, their relationships, and recommendations for modernization in the Laravel rewrite.

**Key Architecture Insight**: TorrentPier is a **forum-integrated BitTorrent tracker** where media content descriptions are stored in forum posts, and the tracker system links directly to forum topics via `topic_id`. This tight integration means that media metadata should live in the forum content, not the torrent tracking tables.

## Table Inventory by Functional Domain

### 1. Forum System (Core Content)
| Table           | Purpose                             | Replacement                |
|-----------------|-------------------------------------|----------------------------|
| `bb_categories` | Forum categories                    | Keep with improvements     |
| `bb_forums`     | Individual forums within categories | Keep with improvements     |
| `bb_topics`     | Forum topics                        | Keep with improvements     |
| `bb_posts`      | Individual posts in topics          | Keep with improvements     |
| `bb_posts_text` | Post content storage                | **Merge into posts table** |

### 2. BitTorrent Tracker System
| Table                 | Purpose                      | Replacement                                |
|-----------------------|------------------------------|--------------------------------------------|
| `bb_bt_torrents`      | Core torrent registry        | Keep with improvements                     |
| `bb_bt_tracker`       | Active peer tracking         | Keep with improvements                     |
| `bb_bt_users`         | User tracker statistics      | Keep with improvements                     |
| `bb_bt_tracker_snap`  | Tracker statistics snapshots | **Replace with Laravel Queues**            |
| `bb_bt_dlstatus_snap` | Download status snapshots    | **Replace with Laravel Queues**            |
| `bb_bt_dlstatus`      | Download status tracking     | **Merge into torrent statistics per user** |
| `bb_bt_torstat`       | Torrent statistics per user  | Keep with improvements                     |
| `bb_bt_tor_dl_stat`   | Torrent download statistics  | **Merge into torrent statistics per user** |
| `bb_bt_last_torstat`  | Last torrent statistics      | **Replace with Laravel Queues**            |
| `bb_bt_last_userstat` | Last user statistics         | **Replace with Laravel Queues**            |
| `bb_bt_torhelp`       | Torrent help system          | **Replace with Laravel Queues**            |
| `bb_bt_user_settings` | User tracker preferences     | **Merge into user settings**               |

### 3. Attachment System
| Table                   | Purpose                                       | Replacement                     |
|-------------------------|-----------------------------------------------|---------------------------------|
| `bb_attachments`        | Links attachments to posts                    | Keep with improvements          |
| `bb_attachments_desc`   | Attachment metadata                           | Keep with improvements          |
| `bb_extensions`         | File extension validation                     | **Remove**                      |
| `bb_extension_groups`   | Extension categories (Images, Archives, etc.) | **Remove**                      |
| `bb_attachments_config` | Complex attachment configuration              | **Replace with Laravel Config** |
| `bb_attach_quota`       | User/group attachment quotas                  | **Remove**                      |
| `bb_quota_limits`       | Quota limit definitions                       | **Remove**                      |

**Current Complexity**: Supports multiple file type categories with complex permissions, quotas, and download modes

**Modern Approach**: Only support `.torrent` files and basic archives (`.zip`, `.rar`, `.7z`)

### 4. User Management
| Table                 | Purpose                   | Replacement                         |
|-----------------------|---------------------------|-------------------------------------|
| `bb_users`            | User accounts             | Keep with improvements              |
| `bb_groups`           | User groups               | Keep with role-based permissions    |
| `bb_user_group`       | User groups memberships   | Keep with improvements              |
| `bb_ranks`            | User ranks/titles         | Keep with improvements              |
| `bb_auth_access`      | Group forum permissions   | **Modernize with Laravel policies** |
| `bb_auth_access_snap` | User permission snapshots | **Remove**                          |

### 5. System Management
| Table         | Purpose                   | Replacement                       |
|---------------|---------------------------|-----------------------------------|
| `bb_config`   | Application configuration | **Replace with Laravel Config**   |
| `bb_sessions` | User sessions             | **Replace with Laravel Sessions** |
| `bb_cron`     | Scheduled task management | **Replace with Laravel Queues**   |
| `bb_log`      | Action logging            | Keep with improvements            |

### 6. Messaging System
| Table              | Purpose                 | Replacement                           |
|--------------------|-------------------------|---------------------------------------|
| `bb_privmsgs`      | Private messages        | Keep with improvements                |
| `bb_privmsgs_text` | Private message content | **Merge into private messages table** |

### 7. Search & Caching
| Table               | Purpose                | Replacement                    |
|---------------------|------------------------|--------------------------------|
| `bb_posts_search`   | Search index for posts | **Replace with Laravel Scout** |
| `bb_posts_html`     | Cached HTML posts      | **Replace with Laravel Cache** |
| `bb_search_results` | Search result cache    | **Replace with Laravel Scout** |
| `bb_search_rebuild` | Search rebuild status  | **Replace with Laravel Scout** |

### 8. Content Management
| Table         | Purpose              | Replacement                     |
|---------------|----------------------|---------------------------------|
| `bb_smilies`  | Emoticons            | Keep with improvements          |
| `bb_words`    | Word censoring       | Keep with improvements          |
| `bb_banlist`  | User bans            | Keep with improvements          |
| `bb_disallow` | Disallowed usernames | **Replace with word censoring** |

### 9. Community Features
| Table             | Purpose                      | Replacement            |
|-------------------|------------------------------|------------------------|
| `bb_poll_votes`   | Poll voting options          | Keep with improvements |
| `bb_poll_users`   | Poll participation           | Keep with improvements |
| `bb_topics_watch` | Topic watching/subscriptions | Keep with improvements |
| `bb_topic_tpl`    | Topic templates              | **Remove**             |
| `bb_thx`          | Thanks/voting system         | Keep with improvements |

### 10. Buffer/Temporary Tables
| Table                   | Purpose                    | Replacement                     |
|-------------------------|----------------------------|---------------------------------|
| `buf_topic_view`        | Topic view counting buffer | **Replace with Laravel Queues** |
| `buf_last_seeder`       | Last seeder buffer         | **Replace with Laravel Queues** |
| Various snapshot tables | Statistical buffers        | **Remove**                      |

## Major Simplifications for Modern Laravel App

### 1. Attachment System Redesign

**Current**: 7 tables supporting multiple file types with complex quotas

**Modern**: 1-2 tables supporting only torrents and archives

### 2. Remove Buffer/Snapshot Tables

**Current**: 5+ buffer tables for performance optimization

**Modern**: Use Laravel Queues, events, and background jobs

### 3. Modernize Configuration

**Current**: Database-stored configuration in bb_config

**Modern**: Laravel config files and environment variables

### 4. Simplify Authentication/Authorization

**Current**: Complex custom permission system

**Modern**: Laravel Sanctum + Policies + Role-based permissions

### 5. Modern Search

**Current**: Custom search indexing and caching

**Modern**: Laravel Scout with Meilisearch

## Proposed Modern Schema Reduction

**Major Eliminations**:
- Buffer/snapshot tables
- Complex attachment system
- Custom configuration system (replaced by Laravel)
- Custom search system (replaced by Scout)
- Legacy features (topic templates, complex quotas)

## Conclusion

The legacy schema contains significant complexity that was necessary for a custom PHP application but can be greatly simplified using modern Laravel features. The core forum-tracker integration should be preserved while modernizing the implementation approach.
