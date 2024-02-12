<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', './../');
define('IN_INSTALL', true);

require BB_ROOT . 'common.php';

set_time_limit(600);

$title = $step_description = $step_content = '';
$next_button = $prev_button = '';

$step = request_var('step', 'welcoming');

// Load SQL dump
$dump_path = BB_ROOT . 'install/sql/mysql.sql';

switch ($step) {
    case 'welcoming':
        $title = $step_description = 'Welcoming 👻';
        $step_content = '';
        $next_button = '<a href="index.php?step=accept_license" class="btn btn-lg btn-light fw-bold border-white bg-white">Next</a>';
        break;
    case 'accept_license':
        $title = $step_description = 'License 📃';
        $step_content = '';
        $prev_button = '<a href="index.php?step=welcoming" class="btn btn-lg btn-light fw-bold border-white bg-white">Back</a>';
        $next_button = '<a href="index.php?step=insert_dump" class="btn btn-lg btn-light fw-bold border-white bg-white">Next</a>';
        break;
    case 'insert_dump':
        // Drop tables & Insert sql dump
        $temp_line = '';
        foreach (file($dump_path) as $line) {
            if (str_starts_with($line, '--') || $line == '') {
                continue;
            }

            $temp_line .= $line;
            if (str_ends_with(trim($line), ';')) {
                DB()->query($temp_line);
                $temp_line = '';
            }
        }
        sleep(3);
        redirect('install/index.php?step=finishing');
        break;
    case 'finishing':
        $title = $step_description = 'Installed successfully! 🥳';
        $step_content = '';
        break;
    default:
        bb_simple_die('Invalid step: ' . $step);
        break;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <title><?php echo APP_NAME; ?>&nbsp;|&nbsp;<?php echo $title; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <link rel="shortcut icon" href="<?php echo BB_ROOT; ?>favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body class="d-flex h-100 text-center text-bg-dark">
<div class="container-xl d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-auto">
        <div>
            <h3 class="float-md-start mb-0"><a class="text-white text-decoration-none"
                                               href="index.php"><?php echo APP_NAME; ?> Installation
                    Wizard</a></h3>
            <h3 class="float-md-end mb-0"><?php echo $step_description; ?></h3>
        </div>
    </header>
    <main class="px-3">
        <?php echo $step_content; ?>
        <?php echo $prev_button; ?>
        <?php echo $next_button; ?>
    </main>
    <footer class="mt-auto text-white-50">
        <p>Fueled by <a class="text-white text-decoration-none" target="_blank" referrerpolicy="origin"
                        href="https://github.com/torrentpier/torrentpier">TorrentPier</a></p>
    </footer>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</html>
