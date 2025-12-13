<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/*
 * ===========================================================================
 * Refactor to Modern Controller
 * ===========================================================================
 * Target: Convert to PSR-7 controller with constructor dependency injection
 *
 * Dependencies to inject:
 * - TorrentPier\Config (configuration access)
 * - TorrentPier\Database\Database (database operations)
 * - TorrentPier\Legacy\User (user session and permissions)
 * - TorrentPier\Http\Request (HTTP request handling)
 * - TorrentPier\Legacy\Ajax (AJAX response handling)
 *
 * Target namespace: TorrentPier\Http\Controllers\Api
 * Target class: AjaxController
 *
 * Key refactoring tasks:
 * 1. Split into multiple API controllers based on functionality
 * 2. Implement proper JSON responses with PSR-7
 * 3. Add proper API authentication and rate limiting
 * 4. Extract business logic into dedicated services
 * 5. Add request validation middleware
 * 6. Implement proper error handling with JSON responses
 * ===========================================================================
 */

define('IN_AJAX', true);

// Init Ajax class
ajax()->init();

// Load actions required modules
switch (ajax()->action) {
    case 'view_post':
    case 'posts':
    case 'post_mod_comment':
        require INC_DIR . '/bbcode.php';
        break;
}

ajax()->exec();

/**
 * @deprecated ajax_common
 * Dirty class removed from here since 2.2.0
 * To add new actions see at src/Ajax.php
 */
