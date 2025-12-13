<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Feed\Exception\FeedGenerationException;
use TorrentPier\Feed\FeedGenerator;
use TorrentPier\Feed\Provider\ForumFeedProvider;
use TorrentPier\Feed\Provider\UserFeedProvider;
use TorrentPier\Http\Response;

// Init userdata
user()->session_start(['req_login' => true]);

// Get request parameters
$type = request()->query->get('type', '');
$id = request()->getInt('id');

// Validate type and ID
if (!in_array($type, ['f', 'u'], true) || $id < 0) {
    bb_simple_die(__('ATOM_ERROR'));
}

try {
    $generator = app(FeedGenerator::class);
    $atomContent = '';

    if ($type === 'f') {
        // Forum feed
        $provider = new ForumFeedProvider($id);
        $atomContent = $generator->generate($provider);
    } elseif ($type === 'u') {
        // User feed
        if ($id < 1) {
            bb_simple_die(__('ATOM_ERROR'));
        }

        $username = get_username($id);
        if (!$username) {
            bb_simple_die(__('NO_USER_ID_SPECIFIED'));
        }

        $provider = new UserFeedProvider($id, $username);
        $atomContent = $generator->generate($provider);
    }

    // Output Atom feed with proper headers
    $cacheTtl = config()->get('atom.cache_ttl') ?? 600;
    Response::atom($atomContent, $cacheTtl)->send();
    exit;
} catch (FeedGenerationException $e) {
    // Show detailed error only to super admins
    if (IS_SUPER_ADMIN) {
        bb_simple_die('Feed generation error: ' . $e->getMessage());
    }
    bb_simple_die(__('ATOM_ERROR'));
} catch (Throwable $e) {
    // Catch any other unexpected errors
    if (IS_SUPER_ADMIN) {
        bb_simple_die('Unexpected error: ' . $e->getMessage());
    }
    bb_simple_die(__('ATOM_ERROR'));
}
