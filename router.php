<?php
/**
 * Routing script for PHP built-in server.
 * This script handles the Yii2 Practical-A template structure.
 */

$uri = $_SERVER['REQUEST_URI'];
$root = __DIR__;

// Parse the URI to remove query string for file_exists check
$path = parse_url($uri, PHP_URL_PATH);

// 1. If the request is for a physical file that IS NOT a .php file, serve it directly.
if ($path !== '/' && file_exists($root . $path) && !is_dir($root . $path)) {
    if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
        return false;
    }
}

// 2. Handle Backend requests
if (strpos($path, '/backend') === 0) {
    // If it's a physical file in the backend folder (non-php)
    if (file_exists($root . $path) && !is_dir($root . $path)) {
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
            return false;
        }
    }
    
    // Otherwise, route to backend/index.php
    $_SERVER['SCRIPT_NAME'] = '/backend/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $root . '/backend/index.php';
    require $root . '/backend/index.php';
    return true;
}

// 3. Fallback: route everything else to frontend index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
require $root . '/index.php';
