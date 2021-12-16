<?php

/**
 * Alias for PHP constant <code>DIRECTORY_SEPARATOR</code>
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Load helper files
 */

$files = scandir(__DIR__);
$loaderFile = basename(__FILE__, '.php');
foreach ($files as $file) {
    $dots = explode('.', $file);
    end($dots);
    $extension = array_pop($dots);

    if ($file === '.' || $file === '..' || $file === $loaderFile || strcasecmp($extension, 'php') !== 0) {
        continue;
    }
    /** @noinspection PhpIncludeInspection */
    require_once(__DIR__ . DS . $file);
}

/**
 * Don't declare functions here, keep it clean.
 * Create other php files in the same directory as this file to hold your helper functions.
 * Don't know where to place it? Try sundry.php
 *
 */
