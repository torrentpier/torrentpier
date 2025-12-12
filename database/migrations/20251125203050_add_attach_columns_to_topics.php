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

final class AddAttachColumnsToTopics extends AbstractMigration
{
    public function up(): void
    {
        $this->table('bb_topics')
            ->addColumn('attach_ext_id', 'integer', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY,
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'topic_attachment'
            ])
            ->addColumn('attach_filesize', 'biginteger', [
                'signed' => false,
                'default' => 0,
                'null' => false,
                'after' => 'attach_ext_id'
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('bb_topics')
            ->removeColumn('attach_ext_id')
            ->removeColumn('attach_filesize')
            ->update();
    }
}
