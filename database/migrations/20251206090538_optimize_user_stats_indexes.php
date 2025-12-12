<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Optimize indexes for user statistics queries
 *
 * This migration fixes performance issues with build_stats.php queries:
 * 1. Adds index on user_gender for gender count queries
 * 2. Adds STORED generated column + index for efficient birthday lookups (MMDD format)
 *
 * Why STORED instead of VIRTUAL or functional index:
 * - Functional indexes ((expr)) are MySQL 8.0+ only, MariaDB doesn't support them
 * - VIRTUAL columns can be indexed in MySQL 5.7+, but MariaDB requires PERSISTENT/STORED
 * - STORED works everywhere: MySQL 5.7+, MySQL 8.0+, MariaDB 10.2.1+
 *
 * @see https://www.percona.com/blog/virtual-columns-in-mysql-and-mariadb/
 * @see https://mariadb.com/kb/en/generated-columns/
 */
final class OptimizeUserStatsIndexes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('bb_users');

        // Index on user_gender for gender count queries
        $table->addIndex(['user_gender'], ['name' => 'idx_user_gender']);
        $table->update();

        // STORED generated column for birthday MMDD + index
        // STORED keyword works as alias for PERSISTENT in MariaDB 10.2.1+
        $this->execute("
            ALTER TABLE bb_users
            ADD COLUMN birthday_md SMALLINT UNSIGNED
                GENERATED ALWAYS AS (MONTH(user_birthday) * 100 + DAYOFMONTH(user_birthday)) STORED,
            ADD INDEX idx_user_birthday_md (birthday_md)
        ");
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE bb_users DROP INDEX idx_user_birthday_md');
        $this->execute('ALTER TABLE bb_users DROP COLUMN birthday_md');

        $table = $this->table('bb_users');
        $table->removeIndexByName('idx_user_gender');
        $table->update();
    }
}
