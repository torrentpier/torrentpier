<?php
# Constants declaration
define('CURRENT_VERSION', '1.2.1');

# Autoloader
function __autoload($class)
{
    require_once str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
}