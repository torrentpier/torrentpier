<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveTopicAttachmentFromBbTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_topics')
            ->removeColumn('topic_attachment')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_topics')
            ->addColumn('topic_attachment', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'topic_moved_id'
            ])
            ->save();
    }
}
