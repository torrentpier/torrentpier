<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    if (IS_SUPER_ADMIN) {
        $module[APP_NAME]['CRON'] = basename(__FILE__) . '?mode=list';
    }
    return;
}

$mode = $_GET['mode'] ?? '';
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : '';
$submit = isset($_POST['submit']);
$jobs = isset($_POST['select']) ? implode(',', $_POST['select']) : '';
$cron_action = $_POST['cron_action'] ?? '';

if ($mode == 'run' && !$job_id) {
    define('BB_ROOT', './../');
    require BB_ROOT . 'common.php';
    $user->session_start();
    redirect('admin/' . basename(__FILE__) . '?mode=list');
} else {
    require __DIR__ . '/pagestart.php';
}

// Check for demo mode
if (IN_DEMO_MODE && ($submit || !in_array($mode, ['add', 'list']))) {
    bb_die($lang['CANT_EDIT_IN_DEMO_MODE']);
}

if (!IS_SUPER_ADMIN) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

$sql = DB()->fetch_rowset('SELECT * FROM ' . BB_CONFIG . " WHERE config_name = 'cron_check_interval'");

foreach ($sql as $row) {
    $config_name = $row['config_name'];
    $config_value = $row['config_value'];
    $default_config[$config_name] = $config_value;

    $new[$config_name] = $_POST[$config_name] ?? $default_config[$config_name];

    if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name]) {
        bb_update_config(array($config_name => $new[$config_name]));
    }
}

$template->assign_vars(array(
    'CRON_ENABLED' => TorrentPier\Helpers\CronHelper::isEnabled(),
    'CRON_CHECK_INTERVAL' => $new['cron_check_interval'],
));

switch ($mode) {
    case 'list':
        $sql = DB()->fetch_rowset('SELECT * FROM ' . BB_CRON . ' ORDER BY cron_id');

        foreach ($sql as $i => $row) {
            $template->assign_block_vars('list', array(
                'ROW_CLASS' => !($i % 2) ? 'row2' : 'row1',
                'JOB_ID' => $i + 1,
                'CRON_ID' => $row['cron_id'],
                'CRON_ACTIVE' => $row['cron_active'] ? '<img src="../styles/images/icon_run.gif" alt="' . $lang['YES'] . '" />' : '<img src="../styles/images/icon_delete.gif" alt="' . $lang['NO'] . '" />',
                'CRON_TITLE' => $row['cron_title'],
                'CRON_SCRIPT' => $row['cron_script'],
                'SCHEDULE' => $row['schedule'] ? $lang['SCHEDULE'][$row['schedule']] : '<b class="leech">' . $lang['NOSELECT'] . '</b>',
                'RUN_DAY' => $row['run_day'],
                'LAST_RUN' => $row['last_run'],
                'NEXT_RUN' => $row['next_run'],
                'RUN_COUNT' => $row['run_counter'],
            ));
        }

        $template->assign_vars(array(
            'TPL_CRON_LIST' => true,
            'S_CRON_ACTION' => 'admin_cron.php',
            'S_MODE' => 'list',
        ));

        //detect cron status
        if (is_file(CRON_RUNNING)) {
            $template->assign_vars(array(
                'CRON_RUNNING' => true,
            ));
        }
        break;

    case 'repair':
        if (is_file(CRON_RUNNING)) {
            rename(CRON_RUNNING, CRON_ALLOWED);
        }
        redirect('admin/' . basename(__FILE__) . '?mode=list');
        break;

    case 'run':
        \TorrentPier\Legacy\Admin\Cron::run_jobs($job_id);
        redirect('admin/' . basename(__FILE__) . '?mode=list');
        break;

    case 'edit':
        $sql = DB()->fetch_rowset('SELECT * FROM ' . BB_CRON . " WHERE cron_id = $job_id");

        foreach ($sql as $row) {
            $template->assign_vars(array(
                'CRON_ID' => $row['cron_id'],
                'CRON_ACTIVE' => $row['cron_active'],
                'CRON_TITLE' => $row['cron_title'],
                'CRON_SCRIPT' => $row['cron_script'],
                'SCHEDULE' => $row['schedule'] ? $lang['SCHEDULE'][$row['schedule']] : '',
                'RUN_DAY' => $row['run_day'],
                'RUN_TIME' => $row['run_time'],
                'RUN_ORDER' => $row['run_order'],
                'LAST_RUN' => $row['last_run'],
                'NEXT_RUN' => $row['next_run'],
                'RUN_INTERVAL' => $row['run_interval'],
                'LOG_ENABLED' => $row['log_enabled'],
                'LOG_FILE' => $row['log_file'],
                'LOG_SQL_QUERIES' => $row['log_sql_queries'],
                'DISABLE_BOARD' => $row['disable_board'],
                'RUN_COUNTER' => $row['run_counter'],
            ));
        }

        $run_day = array($lang['DELTA_TIME']['INTERVALS']['mday'][0] => 0);
        for ($i = 1; $i <= 28; $i++) {
            $run_day[$i] = $i;
        }

        $schedule = array($lang['SCHEDULE']['select'] => 0);
        foreach ($lang['SCHEDULE'] as $type => $key) {
            $schedule[$key] = $type;
        }

        $template->assign_vars(array(
            'TPL_CRON_EDIT' => true,
            'S_CRON_ACTION' => 'admin_cron.php',
            'S_MODE' => 'edit',
            'SCHEDULE' => build_select('schedule', $schedule, $row['schedule']),
            'RUN_DAY' => build_select('run_day', $run_day, $row['run_day']),
            'L_CRON_EDIT_HEAD' => $lang['CRON_EDIT_HEAD_EDIT'],
        ));
        break;

    case 'add':
        $run_day = array($lang['DELTA_TIME']['INTERVALS']['mday'][0] => 0);
        for ($i = 1; $i <= 28; $i++) {
            $run_day[$i] = $i;
        }

        $schedule = [];
        foreach ($lang['SCHEDULE'] as $type => $key) {
            $schedule[$key] = $type;
        }

        $template->assign_vars(array(
            'TPL_CRON_EDIT' => true,
            'S_CRON_ACTION' => 'admin_cron.php',
            'S_MODE' => 'add',
            'SCHEDULE' => build_select('schedule', $schedule, 'select', null, null),
            'RUN_DAY' => build_select('run_day', $run_day, 0, null, null),
            'CRON_ID' => '',
            'CRON_ACTIVE' => 1,
            'CRON_TITLE' => '',
            'CRON_SCRIPT' => '',
            'RUN_TIME' => '',
            'RUN_ORDER' => 255,
            'LAST_RUN' => '1900-01-01 00:00:00',
            'NEXT_RUN' => '1900-01-01 00:00:00',
            'RUN_INTERVAL' => '',
            'LOG_ENABLED' => 0,
            'LOG_FILE' => '',
            'LOG_SQL_QUERIES' => 0,
            'DISABLE_BOARD' => 0,
            'RUN_COUNTER' => 0,
        ));
        break;

    case 'delete':
        \TorrentPier\Legacy\Admin\Cron::delete_jobs($job_id);
        bb_die($lang['JOB_REMOVED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_JOBS'], '<a href="admin_cron.php?mode=list">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
        break;
}

if ($submit) {
    $mode2 = $_POST['mode'] ?? '';
    if ($mode2 == 'list') {
        if ($cron_action == 'run' && $jobs) {
            \TorrentPier\Legacy\Admin\Cron::run_jobs($jobs);
        } elseif ($cron_action == 'delete' && $jobs) {
            \TorrentPier\Legacy\Admin\Cron::delete_jobs($jobs);
        } elseif (($cron_action == 'disable' || $cron_action == 'enable') && $jobs) {
            \TorrentPier\Legacy\Admin\Cron::toggle_active($jobs, $cron_action);
        }
        redirect('admin/' . basename(__FILE__) . '?mode=list');
    } elseif (\TorrentPier\Legacy\Admin\Cron::validate_cron_post($_POST) == 1) {
        if ($mode2 == 'edit') {
            \TorrentPier\Legacy\Admin\Cron::update_cron_job($_POST);
        } elseif ($mode2 == 'add') {
            \TorrentPier\Legacy\Admin\Cron::insert_cron_job($_POST);
        } else {
            bb_die("Invalid mode: $mode2");
        }

        redirect('admin/' . basename(__FILE__) . '?mode=list');
    } else {
        bb_die(\TorrentPier\Legacy\Admin\Cron::validate_cron_post($_POST));
    }
}

print_page('admin_cron.tpl', 'admin');
