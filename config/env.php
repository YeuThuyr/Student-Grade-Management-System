<?php
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
