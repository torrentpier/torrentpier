<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTrackerStatusToBbTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_topics')
            ->addColumn('tracker_status', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'topic_moved_id'
            ])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_topics')
            ->removeColumn('tracker_status')
            ->save();
    }
}
