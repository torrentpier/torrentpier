<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Remove obsolete guest access config options
 *
 * These config options are no longer used because guest access control
 * is now handled by AuthMiddleware on routes instead of config checks.
 */
final class RemoveObsoleteGuestConfig extends AbstractMigration
{
    public function up(): void
    {
        // Remove bt_tor_browse_only_reg - tracker access now controlled by AuthMiddleware
        $this->execute("DELETE FROM bb_config WHERE config_name = 'bt_tor_browse_only_reg'");
    }

    public function down(): void
    {
        // Restore config with the default value (disabled)
        $this->execute("INSERT INTO bb_config (config_name, config_value) VALUES ('bt_tor_browse_only_reg', '0')");
    }
}
