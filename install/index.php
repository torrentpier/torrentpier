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
    die('TorrentPier is already installed!');
}

// Remove time limit
set_time_limit(0);

// Define installer directory
define('INSTALL_DIR', BB_PATH . '/install/');

// Write config
if (isset($_POST['submit'])) {
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
    <title>TorrentPier Installer - (<?php echo $bb_cfg['tp_version']; ?>)</title>
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
<div class="container-fluid" id="installer">
    <div class="step-steps text-center p-3 border-bottom bg-white">
        <div class="btn-group" role="group" aria-label="Wizard controls">
            <button data-step-target="step1" type="button" class="btn btn-primary">Welcome! 🐂</button>
            <button data-step-target="step2" type="button" class="btn btn-secondary">License details 📜</button>
            <button data-step-target="step3" type="button" class="btn btn-danger">Requirements 🔧</button>
            <button data-step-target="step4" type="button" class="btn btn-danger">Database details 🗃</button>
            <button data-step-target="step5" type="button" class="btn btn-danger">Admin details 🧸</button>
            <button data-step-target="step6" type="button" class="btn btn-success">Finish! 💥</button>
        </div>
    </div>
    <div class="step-content">
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step1">
            <img src="<?php echo make_url(hide_bb_path(BB_PATH . 'favicon.png')); ?>" alt="Logo" class="img-fluid mb-3">
            <h3>Welcome to the TorrentPier Installation Wizard! ✨</h3>
        </div>
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step2">
            <h3>License details 📜</h3>
            <?php
            if (is_file(BB_PATH . '/LICENSE')) {
                echo nl2br(file_get_contents(BB_PATH . '/LICENSE'));
            } else {
                die('License file not found :(');
            }
            ?>
        </div>
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step3">
            <h3>Requirements 🔧</h3>
        </div>
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step4">
            <h3>Database details 🗃</h3>
        </div>
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step5">
            <h3>Admin details 🧸</h3>
        </div>
        <div class="step-tab-panel m-3 p-3 rounded-2" data-step="step6">
            <h3>TorrentPier successfully installed! ✅</h3>
            <h6>Now click to the <span class="text-success">Finish</span> button for redirect to homepage.</h6>
        </div>
    </div>
    <div class="step-footer text-center p-3 border-top bg-white">
        <button data-step-action="prev" type="button" class="btn btn-secondary">Previous</button>
        <button data-step-action="next" type="button" class="btn btn-primary">Next</button>
        <button onclick="window.location.replace('<?php echo FULL_URL; ?>');" data-step-action="finish" type="button"
                class="btn btn-success">Finish
        </button>
    </div>
</div>
<!-- Scripts -->
<script src="<?php echo make_url(hide_bb_path(INSTALL_DIR . 'installer/script.js')); ?>"></script>
</body>
</html>
