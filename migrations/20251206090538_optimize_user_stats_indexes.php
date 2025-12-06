<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Optimize indexes for user statistics queries
 *
 * This migration fixes performance issues with build_stats.php queries:
 * 1. Adds index on user_gender for gender count queries
 * 2. Adds a generated column user_birthday_md for birthday queries
 * 3. Adds index on (user_active, user_birthday_md) for efficient birthday lookups
 *
 * Without these indexes, the stat queries scan all users causing memory exhaustion.
 */
final class OptimizeUserStatsIndexes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_users');

        // Index on user_gender for gender count queries
        $table->addIndex(['user_gender'], ['name' => 'idx_user_gender']);

        // Generated column for birthday month-day (Phinx doesn't support generated columns natively)
        $this->execute("
            ALTER TABLE bb_users
            ADD COLUMN user_birthday_md CHAR(5)
            GENERATED ALWAYS AS (DATE_FORMAT(user_birthday, '%m-%d')) STORED
            AFTER user_birthday
        ");

        // Composite index for birthday queries
        $table->addIndex(['user_active', 'user_birthday_md'], ['name' => 'idx_user_birthday_md']);

        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('bb_users');
        $table->removeIndexByName('idx_user_birthday_md');
        $table->removeIndexByName('idx_user_gender');
        $table->update();

        $this->execute('ALTER TABLE bb_users DROP COLUMN user_birthday_md');
    }
}
