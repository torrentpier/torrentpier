<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixLucideIconClassNames extends AbstractMigration
{
    public function up(): void
    {
        // Lucide web font uses "icon-*" class format, not "lucide-*"
        $this->execute("UPDATE bb_navigation SET icon = REPLACE(icon, 'lucide-', 'icon-') WHERE icon LIKE 'lucide-%'");
    }

    public function down(): void
    {
        $this->execute("UPDATE bb_navigation SET icon = REPLACE(icon, 'icon-', 'lucide-') WHERE icon LIKE 'icon-%'");
    }
}
