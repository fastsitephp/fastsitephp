<?php
// Test Script for manually testing FileSystem classes.
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

$dir = __DIR__ . '/../../../';
$search = new \FastSitePHP\FileSystem\Search();

// ------------------------------------------------------

$search = new \FastSitePHP\FileSystem\Search();
$dirs = $search
    ->dir($dir)
    ->excludeNames(array('private'))
    ->excludeRegExNames(array('/^[.]/'))
    ->dirs();

echo '#dirs() - Exclude';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->includeNames(array('html', 'vendor'))
    ->fullPath(true)
    ->dirs();

echo '#dirs() - includeNames';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->includeRegExNames(array('/^app/', '/^d/'))
    ->dirs();

echo '#dirs() - includeRegEx';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->dir(__DIR__ . '/../')
    ->recursive(true)
    ->dirs();

echo '#dirs() - recursive';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->dir(__DIR__ . '/../')
    ->recursive(true)
    ->includeRoot(false)
    ->dirs();

echo '#dirs() - recursive and includeRoot(false)';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$files = $search
    ->reset()
    ->dir(__DIR__)
    ->includeRegExNames(array('/^testing-/', '/.htm$/'))
    ->fullPath(true)
    ->files();

echo '#files() - includeRegEx';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

$files = $search
    ->reset()
    ->dir(__DIR__)
    ->includeText(array('FASTSITEPHP', 'FileEncryption'))
    ->files();

echo '#files() - includeText(case-insensitive)';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

$files = $search
    ->reset()
    ->dir(__DIR__)
    ->includeText(array('FASTSITEPHP'))
    ->caseInsensitiveText(false)
    ->files();

echo '#files() - includeText(case-sensitive)';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

$files = $search
    ->reset()
    ->dir(__DIR__)
    ->includeNames(array('phpinfo.php', basename(__FILE__)))    
    ->files();

echo '#files() - includeNames';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

$files = $search
    ->reset()
    ->dir(__DIR__ . '/../website/public')
    ->fileTypes(array('php', 'config'))
    ->files();

echo '#files() - fileTypes';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->dir(__DIR__ . '/../vendor')
    ->fileTypes(array('php'))
    ->recursive(true)
    ->files();

echo '#files() - recursive';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";

// ------------------------------------------------------

$dirs = $search
    ->reset()
    ->dir(__DIR__ . '/../vendor')
    ->fileTypes(array('php'))
    ->recursive(true)
    ->includeRegExPaths(array('/Security|Encoding/'))
    ->files();

echo '#files() - recursive2 with includeRegExPaths()';
echo "\n";
print_r($dirs);
echo "\n";
echo "\n";


// ------------------------------------------------------

$dir = __DIR__ . '/../website/public/img/icons';
$url = 'https://www.fastsitephp/img/icons';

$files = $search
    ->reset()
    ->dir($dir)
    ->fileTypes(array('svg'))
    ->excludeNames(array('Documentation.svg', 'Less-Code.svg'))
    ->urlFiles($url);

echo '#urlFiles()';
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

// [reset()] is not needed when calling [all()] because search filters are not used
list($dirs, $files) = $search->dir(__DIR__)->all();

echo '#files() - all()';
echo "\n";
print_r($dirs);
echo "\n";
print_r($files);
echo "\n";
echo "\n";

// ------------------------------------------------------

echo 'Exception 1';
echo "\n";
try {
    $search = new \FastSitePHP\FileSystem\Search();
    $files = $search->files();
} catch (\Exception $e) {
    echo $e->getMessage();
}
echo "\n";
echo "\n";

// ------------------------------------------------------

echo 'Exception 2';
echo "\n";
try {
    $files = $search
        ->dir(__DIR__ . '/Missing')
        ->files();
} catch (\Exception $e) {
    echo $e->getMessage();
}
echo "\n";
echo "\n";

// ------------------------------------------------------

echo 'Finished!' . "\n";
