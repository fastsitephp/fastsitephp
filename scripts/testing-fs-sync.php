<?php

// Test Script for manually testing [FastSitePHP\FileSystem\Sync] class.
// In the future full unit tests need to be created.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

header('Content-Type: text/plain');


$sync = new FastSitePHP\FileSystem\Sync();
$dir_from = __DIR__ . '/../../Test/src1';
$dir_to = __DIR__ . '/../../Test/src2';

$sync
    ->dirFrom($dir_from)
    ->dirTo($dir_to)
    ->excludeNames(['package-lock.json'])
    ->excludeRegExPaths(['/node_modules/'])
    // ->summaryTitle('FS Sync Results')
    // ->hashAlgo('sha384')
    // ->dryRun(true)
    ->sync()
    ->printResults();
