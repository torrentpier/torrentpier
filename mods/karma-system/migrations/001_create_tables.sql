-- Migration: 001_create_tables.sql
-- Description: Create karma system tables
-- Date: 2025-01-15

-- Table for storing user karma points and statistics
CREATE TABLE IF NOT EXISTS `bb_karma` (
    `user_id` INT NOT NULL PRIMARY KEY,
    `karma_points` INT NOT NULL DEFAULT 0,
    `positive_votes` INT NOT NULL DEFAULT 0,
    `negative_votes` INT NOT NULL DEFAULT 0,
    `last_updated` INT NOT NULL,
    KEY `karma_points` (`karma_points`),
    KEY `last_updated` (`last_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User karma points and vote statistics';

-- Table for storing individual karma votes
CREATE TABLE IF NOT EXISTS `bb_karma_votes` (
    `vote_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'User who received the vote',
    `voter_id` INT NOT NULL COMMENT 'User who cast the vote',
    `value` TINYINT NOT NULL COMMENT '1 for upvote, -1 for downvote',
    `created_at` INT NOT NULL,
    `reason` TEXT COMMENT 'Optional reason for the vote',
    UNIQUE KEY `unique_vote` (`user_id`, `voter_id`),
    KEY `user_id` (`user_id`),
    KEY `voter_id` (`voter_id`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual karma votes cast by users';

-- Optional: Table for karma history/audit log
CREATE TABLE IF NOT EXISTS `bb_karma_history` (
    `history_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `karma_before` INT NOT NULL,
    `karma_after` INT NOT NULL,
    `change_type` VARCHAR(32) NOT NULL COMMENT 'vote_received, recalculate, manual_adjust',
    `changed_by` INT DEFAULT NULL COMMENT 'User ID who made the change (for manual adjustments)',
    `created_at` INT NOT NULL,
    `notes` TEXT,
    KEY `user_id` (`user_id`),
    KEY `created_at` (`created_at`),
    KEY `change_type` (`change_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Karma change history for auditing';
