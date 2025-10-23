<?php

/**
 * PHPUnit Bootstrap File
 *
 * This file is loaded before any tests run.
 * It sets up the test environment and includes necessary files.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load Pest helpers and mocks
require_once __DIR__ . '/Pest.php';
