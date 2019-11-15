<?php
// Test Script for manually testing both FileSystem\Search and Media\Image classes.
// This script creates thumbnails in the future this code can be used to create docs and demos.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

$full_image_dir = 'C:\Users\Public\Pictures\Sample Pictures';
$thumb_dir = 'C:\Users\Public\Pictures\Thumbnails';

$search = new \FastSitePHP\FileSystem\Search();
$files = $search
    ->dir($full_image_dir)
    ->fileTypes(array('jpg', 'jpeg', 'gif', 'png', 'webp'))
    ->fullPath(true)
    ->files();

foreach ($files as $file) {
    $img = new \FastSitePHP\Media\Image();
    $img
        ->open($file)
        ->resize(300, 300)
        ->save($thumb_dir . '/' . basename($file));
}

echo 'Finished, check files and manually delete when done';
