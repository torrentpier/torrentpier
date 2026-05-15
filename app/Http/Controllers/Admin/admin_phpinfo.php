<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    if (IS_SUPER_ADMIN) {
        $module['GENERAL']['PHP_INFO'] = basename(__FILE__);
    }

    return;
}

if (!IS_SUPER_ADMIN) {
    bb_die(__('ONLY_FOR_SUPER_ADMIN'), 403);
}

/** @noinspection ForgottenDebugOutputInspection */
phpinfo();
