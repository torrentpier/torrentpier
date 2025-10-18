<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveQuotaConfigsFromAttachmentsConfig extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("DELETE FROM bb_attachments_config WHERE config_name IN ('attachment_quota', 'default_upload_quota', 'default_pm_quota')");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->table('bb_attachments_config')->insert([
            ['config_name' => 'attachment_quota', 'config_value' => '52428800'],
            ['config_name' => 'default_upload_quota', 'config_value' => '0'],
            ['config_name' => 'default_pm_quota', 'config_value' => '0'],
        ])->saveData();
    }
}
