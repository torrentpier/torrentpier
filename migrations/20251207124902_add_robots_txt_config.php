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

/**
 * Add robots_txt configuration to the bb_config table
 *
 * This allows dynamic editing of robots.txt via an admin panel
 * instead of a static file in the root directory.
 */
final class AddRobotsTxtConfig extends AbstractMigration
{
    public function up(): void
    {
        $default = <<<'ROBOTS'
User-agent: *

Disallow: /bt/*
Disallow: /dl
Disallow: /group
Disallow: /login
Disallow: /memberlist
Disallow: /modcp
Disallow: /posting
Disallow: /privmsg
Disallow: /profile

Host: example.com
Sitemap: https://example.com/sitemap.xml
ROBOTS;

        $this->table('bb_config')->insert([
            'config_name' => 'robots_txt',
            'config_value' => $default,
        ])->save();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM bb_config WHERE config_name = 'robots_txt'");
    }
}
