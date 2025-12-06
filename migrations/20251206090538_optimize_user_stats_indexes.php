<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Optimize indexes for user statistics queries
 *
 * This migration fixes performance issues with build_stats.php queries:
 * 1. Adds index on user_gender for gender count queries
 * 2. Adds generated column user_birthday_md for birthday queries
 * 3. Adds index on (user_active, user_birthday_md) for efficient birthday lookups
 *
 * Without these indexes, the stats queries scan 600k+ users causing memory exhaustion.
 */
final class OptimizeUserStatsIndexes extends AbstractMigration
{
    public function up(): void
    {
        // 1. Add index on user_gender for gender count queries
        // Current query: COUNT(*) WHERE user_gender = X
        // Without index: full table scan of 600k+ rows
        $this->execute('CREATE INDEX idx_user_gender ON bb_users (user_gender)');

        // 2. Add generated column for birthday month-day
        // This allows indexing the MM-DD portion of the birthday
        // The column is STORED (not VIRTUAL) so it can be indexed
        $this->execute("
            ALTER TABLE bb_users
            ADD COLUMN user_birthday_md CHAR(5)
            GENERATED ALWAYS AS (DATE_FORMAT(user_birthday, '%m-%d')) STORED
            AFTER user_birthday
        ");

        // 3. Add index for birthday queries
        // Current query: WHERE user_active = 1 AND DATE_FORMAT(user_birthday, '%m-%d') = 'XX-XX'
        // New query can use: WHERE user_active = 1 AND user_birthday_md = 'XX-XX'
        $this->execute('CREATE INDEX idx_user_birthday_md ON bb_users (user_active, user_birthday_md)');
    }

    public function down(): void
    {
        $this->execute('DROP INDEX idx_user_birthday_md ON bb_users');
        $this->execute('ALTER TABLE bb_users DROP COLUMN user_birthday_md');
        $this->execute('DROP INDEX idx_user_gender ON bb_users');
    }
}
