<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemovePostAttachmentFromBbPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->table('bb_posts')
            ->removeColumn('post_attachment')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_posts')
            ->addColumn('post_attachment', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'post_edit_count'
            ])
            ->save();
    }
}
