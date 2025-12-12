<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove the legacy xs_use_cache config option
 *
 * This config was used by the old XS template system.
 * Now Twig cache is controlled via twig.cache_enabled in config.php
 */
final class RemoveXsUseCacheConfig extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("DELETE FROM bb_config WHERE config_name = 'xs_use_cache'");
    }

    public function down(): void
    {
        $this->execute("INSERT INTO bb_config (config_name, config_value) VALUES ('xs_use_cache', '1')");
    }
}
