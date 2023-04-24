<?php

// Test Script for manually testing Security\FileSystem class.
// In the future this code can be used as a starting point
// when creating the full unit tests. In the meantime this
// manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

header('Content-Type: text/plain');

$tests = array(
    array(
        'dir' => __DIR__,
        'file' => basename(__FILE__),
    ),
    array(
        'dir' => __DIR__ . '/',
        'file' => basename(__FILE__),
    ),
    array(
        'dir' => __DIR__ . (PHP_OS === 'WINNT' ? '\\' : '/'),
        'file' => basename(__FILE__),
    ),
    array(
        'dir' => __DIR__,
        'file' => '../' . basename(__DIR__) . '/' . basename(__FILE__),
    ),
    array(
        'dir' => __DIR__,
        'file' => '../../index.php',
    ),
);

header('Content-Type: text/plain');

foreach ($tests as $test) {
    $dir = $test['dir'];
    $file = $test['file'];
    echo str_repeat('-', 80);
    echo "\n";
    echo 'dir = ' . $dir;
    echo "\n";
    echo 'file = ' . $file;
    echo "\n";
    echo 'is_file(): ' . json_encode(is_file($dir . DIRECTORY_SEPARATOR . $file));
    echo "\n";
    echo 'dirContainsFile(): ' . json_encode(\FastSitePHP\FileSystem\Security::dirContainsFile($dir, $file));
    echo "\n";
    echo "\n";
}

try {
    echo str_repeat('-', 80);
    echo "\n";
    echo 'Error Test:';
    echo "\n";
    \FastSitePHP\FileSystem\Security::dirContainsFile('\invalid-dir', 'index.php');
    echo "*** ERROR - No Exception thrown";
} catch (\Exception $e) {
    echo $e->getMessage();
}
echo "\n";
echo "\n";

// Test [dirContainsPath()] and [dirContainsDir()]
echo str_repeat('-', 80);
echo "\n";
echo "dirContainsPath():\n";
echo "i18n/_.en.json\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n/_.en.json'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n/_.en.json', 'file'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n/_.en.json', 'dir'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n/_.en.json', 'all'));
var_dump(is_file(__DIR__ . '/i18n/_.en.json'));
echo "../index.php\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, '../index.php'));
var_dump(is_file(__DIR__ . '/../index.php'));
echo "i18n\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n', 'file'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n', 'dir'));
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, 'i18n', 'all'));
var_dump(is_dir(__DIR__ . '/i18n'));
echo "../src\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, '../src'));
var_dump(is_dir(__DIR__ . '/../src'));
echo "{empty string}\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsPath(__DIR__, '', 'dir'));

echo "\n";
echo "dirContainsDir():\n";
echo "i18n\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsDir(__DIR__, 'i18n'));
var_dump(is_dir(__DIR__ . '/i18n'));
echo "../src\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsDir(__DIR__, '../src'));
var_dump(is_dir(__DIR__ . '/../src'));
echo "{empty string}\n";
var_dump(\FastSitePHP\FileSystem\Security::dirContainsDir(__DIR__, ''));

// Test [fileIsValidImage()]
$path_error1 = __DIR__ . '/files/invalid1.svg';
$path_error2 = __DIR__ . '/files/invalid2.svg';
$path_error3 = __DIR__ . '/files/empty.svg';
$path_valid = __DIR__ . '/../website/public/img/icons/Clipboard.svg';

// Change paths as needed to test with local files
$jpg_valid = 'C:\Users\Public\Pictures\Desert.jpg';
$png_error = 'C:\Users\Public\Pictures\Desert.jpg.png'; // JPG renamed as PNG

$files = array($path_error1, $path_error2, $path_error3, $path_valid, $jpg_valid, $png_error);
foreach ($files as $file) {
    echo str_repeat('-', 80);
    echo "\n";
    echo $file;
    echo "\n";
    echo 'File Exits: ' . json_encode(is_file($file));
    echo "\n";    
    echo 'fileIsValidImage: ' . json_encode(\FastSitePHP\FileSystem\Security::fileIsValidImage($file));
    echo "\n";
    echo "\n";
}
