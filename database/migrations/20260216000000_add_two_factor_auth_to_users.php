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

final class AddTwoFactorAuthToUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->table('bb_users')
            ->addColumn('totp_secret', 'string', [
                'limit' => 255,
                'default' => '',
                'null' => false,
                'after' => 'user_password'
            ])
            ->addColumn('totp_enabled', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'totp_secret'
            ])
            ->addColumn('totp_recovery_codes', 'text', [
                'null' => true,
                'after' => 'totp_enabled'
            ])
            ->addColumn('totp_enabled_at', 'integer', [
                'default' => 0,
                'null' => false,
                'after' => 'totp_recovery_codes'
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('bb_users')
            ->removeColumn('totp_secret')
            ->removeColumn('totp_enabled')
            ->removeColumn('totp_recovery_codes')
            ->removeColumn('totp_enabled_at')
            ->update();
    }
}
