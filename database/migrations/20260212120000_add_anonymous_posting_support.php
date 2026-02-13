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

final class AddAnonymousPostingSupport extends AbstractMigration
{
    public function up(): void
    {
        $this->table('bb_posts')
            ->addColumn('post_anonymous', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'attach_rg_sig',
            ])
            ->update();

        $this->table('bb_forums')
            ->addColumn('allow_anonymous', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'self_moderated',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('bb_posts')
            ->removeColumn('post_anonymous')
            ->update();

        $this->table('bb_forums')
            ->removeColumn('allow_anonymous')
            ->update();
    }
}
