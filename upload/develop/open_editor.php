<?php

// Comment the following line to enable
die('Please REMOVE THIS FILE from your production environment!<br /><br />'. basename(__FILE__));

require('./dbg_config.php');

$interpreter = $bb_cfg['dbg']['interpreter'];
$prog_path   = $_GET['prog'];
$prog_args   = $_GET['args'];

$command = "$interpreter $prog_path $prog_args";
exec($command);
echo '<HTML><BODY onload="javascript:self.close()"></BODY></HTML>';
exit;