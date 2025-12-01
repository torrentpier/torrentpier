<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Terms of Service page controller
 *
 * Execution context (via front controller + LegacyAdapter):
 * - common.php already loaded by front controller
 * - BB_SCRIPT defined by LegacyAdapter
 * - session_start() called by LegacyAdapter
 * - Global variables ($user, $datastore, etc.) available
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Load BBCode parser for term content
require INC_DIR . '/bbcode.php';

// Check if terms are enabled
if (!config()->get('terms') && !IS_ADMIN) {
    redirect('index.php');
}

// Render the terms page
print_page('terms.twig', variables: [
    'PAGE_TITLE' => __('TERMS'),
    'TERMS_EDIT' => bbcode2html(sprintf(__('TERMS_EMPTY_TEXT'), make_url('admin/admin_terms.php'))),
    'TERMS_HTML' => bbcode2html(config()->get('terms')),
]);
