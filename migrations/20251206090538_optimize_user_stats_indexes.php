<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Optimize indexes for user statistics queries
 *
 * This migration fixes performance issues with build_stats.php queries:
 * 1. Adds index on user_gender for gender count queries
 * 2. Adds functional index on birthday month-day for efficient birthday lookups
 *
 * Uses functional indexes (MySQL 8.0+ / MariaDB 10.5+) instead of generated columns
 * for better cross-database compatibility.
 */
final class OptimizeUserStatsIndexes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_users');

        // Index on user_gender for gender count queries
        $table->addIndex(['user_gender'], ['name' => 'idx_user_gender']);
        $table->update();

        // Functional index for birthday queries (month-day as integer MMDD)
        // Works in MySQL 8.0+ and MariaDB 10.5+
        $this->execute("
            ALTER TABLE bb_users
            ADD INDEX idx_user_birthday_md ((
                MONTH(user_birthday) * 100 + DAYOFMONTH(user_birthday)
            ))
        ");
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE bb_users DROP INDEX idx_user_birthday_md');

        $table = $this->table('bb_users');
        $table->removeIndexByName('idx_user_gender');
        $table->update();
    }
}
