<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}
?>

<style>
    .sqlLog {
        clear: both;
        font-family: Courier, monospace;
        font-size: 12px;
        white-space: nowrap;
        background: #F5F5F5;
        border: 1px solid #BBC0C8;
        overflow: auto;
        width: 98%;
        margin: 0 auto;
        padding: 2px 4px;
    }

    .sqlLogTitle {
        font-weight: bold;
        color: #444444;
        font-size: 11px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
        padding-bottom: 2px;
    }

    .sqlLogRow {
        background-color: #F5F5F5;
        padding-bottom: 1px;
        border: solid #F5F5F5;
        border-width: 0 0 1px 0;
        cursor: pointer;
    }

    .sqlLogRow:hover {
        border-color: #8B0000;
    }

    .sqlLogWrapped {
        white-space: normal;
        overflow: visible;
    }

    .sqlExplain {
        color: #B50000;
        font-size: 13px;
        cursor: inherit !important;
    }

    .sqlHighlight {
        background: #FFE4E1;
    }
</style>

<?php
if (!empty($_COOKIE['explain'])) {
    // Get all database server instances from the new DbFactory
    $server_names = \TorrentPier\Database\DbFactory::getServerNames();
    foreach ($server_names as $srv_name) {
        try {
            $db_obj = \TorrentPier\Database\DbFactory::getInstance($srv_name);
            if (!empty($db_obj->do_explain)) {
                $db_obj->explain('display');
            }
        } catch (\Exception $e) {
            // Skip if server not available
        }
    }
}

$sql_log = !empty($_COOKIE['sql_log']) ? dev()->getSqlDebugLog() : false;

if ($sql_log) {
    echo '<div class="sqlLog" id="sqlLog">' . $sql_log . '</div><!-- / sqlLog --><br clear="all" />';
}
?>

<script type="text/javascript">
    function fixSqlLog() {
        if ($("#sqlLog").height() > 400) {
            $("#sqlLog").height(400);
        }
    }

    $(document).ready(fixSqlLog);
</script>
