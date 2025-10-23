-- Rollback: 001_create_tables.rollback.sql
-- Description: Rollback karma system tables
-- Date: 2025-01-15

-- Drop tables in reverse order (to avoid FK issues if they exist later)
DROP TABLE IF EXISTS `bb_karma_history`;
DROP TABLE IF EXISTS `bb_karma_votes`;
DROP TABLE IF EXISTS `bb_karma`;
