<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// Check installed
if (!defined('BB_PATH') || is_file(BB_PATH . '/.env')) {
    redirect(make_url()); // Redirect to homepage
}

// Remove time limit
set_time_limit(0);

// Define installer directory
define('INSTALL_DIR', BB_PATH . '/install/');

// Write config
if (isset($submit)) {
    // Load ENV
    $envFile = \EnvEditor\EnvFile::loadFrom(BB_PATH . '/.env.example');

    // Set values
    $envFile->setValue('DB_HOST', trim($_POST['db_host']));
    $envFile->setValue('DB_PORT', trim($_POST['db_port']));
    $envFile->setValue('DB_USERNAME', trim($_POST['db_user']));
    $envFile->setValue('DB_PASSWORD', trim($_POST['db_pass']));

    // Save ENV
    $envFile->saveTo(BB_PATH . '/.env');
}
?>

<!doctype html>
<html lang="en">
<head>
    <!-- Meta info -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TorrentPier Installer (<?php echo $bb_cfg['tp_version']; ?>)</title>
    <link rel="icon" href="<?php echo make_url(hide_bb_path(BB_PATH . '/favicon.png')); ?>">
    <!-- jQuery -->
    <script src="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/jquery/jquery-3.7.0.min.js')); ?>"></script>
    <!-- jQuery Steps -->
    <script
        src="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/jquery-steps/jquery-steps.js')); ?>"></script>
    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/bootstrap/bootstrap.min.css')); ?>">
    <script
        src="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/styles.css')); ?>">
</head>
<body>
<!-- Contents -->
<!-- Scripts -->
<script src="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/script.js')); ?>"></script>
</body>
</html>
