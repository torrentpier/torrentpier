<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use App\Models\User;

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
 * - TorrentPier\Legacy\Templates (template rendering)
 *
 * Target namespace: TorrentPier\Http\Controllers
 * Target class: MemberListController
 *
 * Key refactoring tasks:
 * 1. Extract procedural code into controller method (index)
 * 2. Replace global function calls with injected dependencies
 * 3. Implement PSR-7 request/response handling
 * 4. Extract business logic into UserService
 * 5. Add proper pagination via PaginationService
 * 6. Add sorting and filtering options
 * ===========================================================================
 */
$start = abs(request()->getInt('start'));
$mode = request()->getString('mode', 'joined');
$sort_order = (request()->getString('order', 'ASC') === 'ASC') ? 'ASC' : 'DESC';
$username = trim(request()->getString('username'));
$role = request()->getString('role', 'all');

// Memberlist sorting
$mode_types_text = [__('SORT_JOINED'), __('SORT_USERNAME'), __('SORT_LOCATION'), __('SORT_POSTS'), __('SORT_EMAIL'), __('SORT_WEBSITE'), __('SORT_TOP_TEN')];
$mode_types = ['joined', 'username', 'location', 'posts', 'email', 'website', 'topten'];

$select_sort_mode = '<select name="mode">';
for ($i = 0, $iMax = count($mode_types_text); $i < $iMax; $i++) {
    $selected = ($mode == $mode_types[$i]) ? ' selected' : '';
    $select_sort_mode .= '<option value="' . $mode_types[$i] . '"' . $selected . '>' . $mode_types_text[$i] . '</option>';
}
$select_sort_mode .= '</select>';

$select_sort_order = '<select name="order">';
if ($sort_order == 'ASC') {
    $select_sort_order .= '<option value="ASC" selected>' . __('ASC') . '</option><option value="DESC">' . __('DESC') . '</option>';
} else {
    $select_sort_order .= '<option value="ASC">' . __('ASC') . '</option><option value="DESC" selected>' . __('DESC') . '</option>';
}
$select_sort_order .= '</select>';

// Role selector
$role_select = [
    'all' => __('ALL'),
    'user' => __('AUTH_USER'),
    'admin' => __('AUTH_ADMIN'),
    'moderator' => __('MODERATOR'),
];
$select_sort_role = '<select name="role">';
foreach ($role_select as $key => $value) {
    $selected = ($role == $key) ? ' selected' : '';
    $select_sort_role .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
}
$select_sort_role .= '</select>';

// Build base query with filters
$query = User::whereNotIn('user_id', explode(',', EXCLUDED_USERS));

// Search by role
match ($role) {
    'user' => $query->where('user_level', USER),
    'admin' => $query->where('user_level', ADMIN),
    'moderator' => $query->where('user_level', MOD),
    default => null,
};

// Search by username
if (!empty($username)) {
    $query->where('username', 'LIKE', str_replace('*', '%', clean_username($username)));
}

// Generate user information
$orderColumn = match ($mode) {
    'username' => 'username',
    'location' => 'user_from',
    'posts', 'topten' => 'user_posts',
    'email' => 'user_email',
    'website' => 'user_website',
    default => 'user_regdate',
};
$limit = $mode === 'topten' ? 10 : config()->get('topics_per_page');

$result = (clone $query)
    ->select(['username', 'user_id', 'user_rank', 'user_opt', 'user_posts', 'user_regdate', 'user_from', 'user_website', 'user_email', 'avatar_ext_id'])
    ->orderBy($orderColumn, $sort_order)
    ->offset($mode === 'topten' ? 0 : $start)
    ->limit($limit)
    ->toBase()
    ->get()
    ->map(fn ($row) => (array)$row);

if ($result->isNotEmpty()) {
    foreach ($result as $i => $row) {
        $user_info = generate_user_info($row);

        $row_class = !($i % 2) ? 'row1' : 'row2';
        template()->assign_block_vars('memberrow', [
            'ROW_NUMBER' => $i + ($start + 1),
            'ROW_CLASS' => $row_class,
            'USER' => profile_url($row),
            'AVATAR' => $user_info['avatar'],
            'FROM' => $user_info['from'],
            'JOINED' => $user_info['joined'],
            'POSTS' => $user_info['posts'],
            'PM' => $user_info['pm'],
            'EMAIL' => $user_info['email'],
            'WWW' => $user_info['www'],
            'U_VIEWPROFILE' => url()->member($row['user_id'], $row['username']),
        ]);
    }
} else {
    template()->assign_block_vars('no_username', ['NO_USER_ID_SPECIFIED' => __('NO_USER_ID_SPECIFIED')]);
}

// Pagination
$paginationurl = "memberlist?mode={$mode}&amp;order={$sort_order}&amp;role={$role}";
$paginationurl .= !empty($username) ? "&amp;username={$username}" : '';

if ($mode != 'topten') {
    // Cache the count for 5 minutes to avoid expensive COUNT queries
    $cacheKey = 'memberlist_count_' . md5($role . '_' . $username);
    $total_members = CACHE('bb_cache')->get($cacheKey);

    if ($total_members === false) {
        $total_members = (clone $query)->count();
        CACHE('bb_cache')->set($cacheKey, $total_members, 300);
    }

    generate_pagination($paginationurl, $total_members, config()->get('topics_per_page'), $start);
}

// Generate output
template()->assign_vars([
    'PAGE_TITLE' => __('MEMBERLIST'),
    'S_MODE_SELECT' => $select_sort_mode,
    'S_ORDER_SELECT' => $select_sort_order,
    'S_ROLE_SELECT' => $select_sort_role,
    'S_MODE_ACTION' => FORUM_PATH . "memberlist?mode={$mode}&amp;order={$sort_order}&amp;role={$role}",
    'S_USERNAME' => $username,
]);

print_page('memberlist.tpl');
