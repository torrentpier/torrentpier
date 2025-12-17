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

final class RenamePostsTextToPostTexts extends AbstractMigration
{
    /**
     * Rename bb_posts_text to bb_post_texts for Eloquent naming convention
     *
     * Eloquent expects: PostText model → post_texts table (+ prefix → bb_post_texts)
     */
    public function change(): void
    {
        $this->table('bb_posts_text')
            ->rename('bb_post_texts')
            ->update();
    }
}
