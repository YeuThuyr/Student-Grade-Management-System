<?php
// Temporarily enable error reporting to diagnose the 500 error on the live server
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('BASE_PATH')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Replace backslashes with forward slashes for Windows compatibility
    $scriptDir = str_replace('\\', '/', $scriptDir);
    if (strpos($scriptDir, '/grade_management') !== false) {
        define('BASE_PATH', '/grade_management/');
    } else {
        define('BASE_PATH', '/');
    }
}
