<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

require INC_DIR . '/bbcode.php';

if (!config()->get('terms') && !IS_ADMIN) {
    redirect('index.php');
}

print_page('terms.twig', variables: [
    'PAGE_TITLE' => __('TERMS'),
    'TERMS_EDIT' => bbcode2html(sprintf(__('TERMS_EMPTY_TEXT'), make_url('admin/admin_terms.php'))),
    'TERMS_HTML' => bbcode2html(config()->get('terms')),
]);
