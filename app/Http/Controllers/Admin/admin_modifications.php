<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MARKETPLACE']['MODIFICATIONS_LIST'] = basename(__FILE__);

    return;
}

$apiUrl = config()->get('mods.api_url');
$apiKey = config()->get('mods.api_key');
$per_page = max(1, (int)config()->get('mods.per_page', 20));
$row_class_1 = 'row1';
$row_class_2 = 'row2';

$url = basename(__FILE__);

// Filter keys
$cat_key = 'cat';

// Start / pagination
$start = request()->getInt('start');
if ($start < 0) {
    $start = abs($start);
}

$page = (int)floor($start / $per_page) + 1;

// Category filter
$cat_val = request()->getInt($cat_key);
if ($cat_val > 0) {
    $url = url_arg($url, $cat_key, $cat_val);
}

// Fetch resources from API (with cache)
$hasError = false;
$apiError = '';
$resources = [];
$totalResources = 0;
$categories = [];

// Use category-specific endpoint when filter is active, otherwise fetch all
$cacheKey = $cat_val > 0
    ? 'xf_resources_cat_' . $cat_val . '_page_' . $page
    : 'xf_resources_page_' . $page;

$apiEndpoint = $cat_val > 0
    ? $apiUrl . '/resource-categories/' . $cat_val . '/resources'
    : $apiUrl . '/resources/';

try {
    $data = CACHE('bb_cache')->get($cacheKey);

    if ($data === false) {
        $response = httpClient()->get($apiEndpoint, [
            'query' => ['page' => $page],
            'headers' => [
                'XF-Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ],
            'allow_redirects' => false,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 403) {
            $hasError = true;
            $apiError = sprintf(__('MODS_API_ERROR'), 'API access denied');
        } elseif ($statusCode === 429) {
            $hasError = true;
            $apiError = sprintf(__('MODS_API_ERROR'), 'Rate limited — try again later');
        } elseif ($statusCode !== 200) {
            $hasError = true;
            $apiError = sprintf(__('MODS_API_ERROR'), "HTTP {$statusCode}");
        } else {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!is_array($data) || !isset($data['resources'])) {
                $hasError = true;
                $apiError = sprintf(__('MODS_API_ERROR'), 'Invalid response format');
            } else {
                CACHE('bb_cache')->set($cacheKey, $data, (int)config()->get('mods.cache_ttl', 300));
            }
        }
    }
} catch (\TorrentPier\Http\Exception\HttpClientException $e) {
    $hasError = true;
    $apiError = __('MODS_API_UNAVAILABLE');
    bb_log("[Marketplace] API error: {$e->getMessage()}" . LOG_LF);
} catch (\Throwable $e) {
    $hasError = true;
    $apiError = __('MODS_API_UNAVAILABLE');
    bb_log("[Marketplace] Unexpected error: {$e->getMessage()}" . LOG_LF);
}

if (!$hasError && is_array($data)) {
    $resources = $data['resources'] ?? [];
    $totalResources = $data['pagination']['total'] ?? 0;
}

// Fetch categories from API (cached separately, longer TTL since they rarely change)
$catCacheKey = 'xf_resource_categories';

try {
    $categories = CACHE('bb_cache')->get($catCacheKey);
} catch (\Throwable $e) {
    $categories = false;
}

if ($categories === false) {
    $categories = [];
    try {
        $catResponse = httpClient()->get($apiUrl . '/resource-categories/', [
            'headers' => [
                'XF-Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ],
            'allow_redirects' => false,
        ]);

        if ($catResponse->getStatusCode() === 200) {
            $catData = json_decode($catResponse->getBody()->getContents(), true);
            foreach ($catData['categories'] ?? [] as $cat) {
                $count = (int)($cat['resource_count'] ?? 0);
                if ($count > 0) {
                    $categories[(int)$cat['resource_category_id']] = $cat['title'];
                }
            }
            CACHE('bb_cache')->set($catCacheKey, $categories, (int)config()->get('mods.categories_cache_ttl', 3600));
        }
    } catch (\Throwable $e) {
        // Categories are non-critical — silently fall back to empty
    }
}

// Category filter dropdown
$cat_options = [__('MODS_ALL_CATEGORIES') => 0];
foreach ($categories as $catId => $catTitle) {
    $cat_options[htmlCHR($catTitle)] = $catId;
}

// Pagination
generate_pagination($url, $totalResources, $per_page, $start);

// Assign resource rows
if ($resources) {
    foreach ($resources as $row_num => $resource) {
        $row_class = !($row_num & 1) ? $row_class_1 : $row_class_2;

        $lastUpdate = $resource['last_update'] ?? $resource['resource_date'] ?? 0;

        $categoryTitle = $resource['Category']['title']
            ?? $categories[(int)($resource['resource_category_id'] ?? 0)]
            ?? '';
        $categoryClass = match (strtolower($categoryTitle)) {
            'free' => 'free',
            'paid' => 'paid',
            default => 'other',
        };

        $avatarUrl = $resource['User']['avatar_urls']['s'] ?? '';
        if ($avatarUrl && !str_starts_with($avatarUrl, 'https://')) {
            $avatarUrl = '';
        }

        $viewUrl = $resource['view_url'] ?? '';
        if (!str_starts_with($viewUrl, 'https://')) {
            $viewUrl = '';
        }

        $username = $resource['username'] ?? '';
        $ratingAvg = round((float)($resource['rating_avg'] ?? 0), 1);

        template()->assign_block_vars('resource', [
            'ROW_CLASS' => $row_class,
            'TITLE' => htmlCHR($resource['title'] ?? ''),
            'VERSION' => htmlCHR($resource['version'] ?? ''),
            'TAG_LINE' => htmlCHR($resource['tag_line'] ?? ''),
            'USERNAME' => htmlCHR($username),
            'USERNAME_INITIAL' => htmlCHR(mb_strtoupper(mb_substr($username, 0, 1))),
            'AVATAR_URL' => htmlCHR($avatarUrl),
            'DOWNLOAD_COUNT' => (int)($resource['download_count'] ?? 0),
            'RATING_AVG' => $ratingAvg,
            'RATING_COUNT_TEXT' => (int)($resource['rating_count'] ?? 0) === 1
                ? '1 ' . __('MODS_REVIEW')
                : (int)($resource['rating_count'] ?? 0) . ' ' . __('MODS_REVIEWS'),
            'RATING_PERCENT' => min(100, (int)($ratingAvg / 5 * 100)),
            'LAST_UPDATE' => $lastUpdate ? bb_date($lastUpdate, 'd-M-y H:i') : '',
            'VIEW_URL' => htmlCHR($viewUrl),
            'CATEGORY_TITLE' => htmlCHR($categoryTitle),
            'CATEGORY_CLASS' => $categoryClass,
        ]);
    }
} else {
    template()->assign_block_vars('no_resources', []);
}

template()->assign_vars([
    'HAS_ERROR' => $hasError,
    'API_ERROR' => htmlCHR($apiError),
    'TOTAL_RESOURCES' => $totalResources,

    'SEL_CATEGORY' => build_select($cat_key, $cat_options, $cat_val ?: 0),

    'S_MODS_ACTION' => basename(__FILE__),
]);

print_page('admin_modifications.tpl', 'admin');
