<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Update asset paths from styles/images/ to assets/images/
 *
 * This migration updates all asset paths in the database to reflect
 * the new public/assets/ directory structure.
 */
final class UpdateAssetPaths extends AbstractMigration
{
    public function up(): void
    {
        // Update bb_config smilies path
        $this->execute("
            UPDATE bb_config
            SET config_value = REPLACE(config_value, 'styles/images/', 'assets/images/')
            WHERE config_name = 'smilies_path'
            AND config_value LIKE 'styles/images/%'
        ");

        // Update bb_ranks rank images
        $this->execute("
            UPDATE bb_ranks
            SET rank_image = REPLACE(rank_image, 'styles/images/', 'assets/images/')
            WHERE rank_image LIKE 'styles/images/%'
        ");
    }

    public function down(): void
    {
        // Revert bb_config smilies path
        $this->execute("
            UPDATE bb_config
            SET config_value = REPLACE(config_value, 'assets/images/', 'styles/images/')
            WHERE config_name = 'smilies_path'
            AND config_value LIKE 'assets/images/%'
        ");

        // Revert bb_ranks rank images
        $this->execute("
            UPDATE bb_ranks
            SET rank_image = REPLACE(rank_image, 'assets/images/', 'styles/images/')
            WHERE rank_image LIKE 'assets/images/%'
        ");
    }
}
