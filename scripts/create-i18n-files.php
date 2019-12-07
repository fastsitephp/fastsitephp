<?php
// This script is used to generate i18n translation files needed for adding a new language.
//
// Run from the command line locally:
//     php create-i18n-files.php
//
// Or run with a Code Editor if supported (example for Visual Studio Code):
//     https://www.fastsitephp.com/en/documents/edit-with-vs-code
//
// This script requires a local setup of 3 repositories and is safe to run multiple times
// as it will not overwite existing files.
//
// See Online docs for full details:
//     https://github.com/fastsitephp/fastsitephp/blob/master/docs/i18n-translations.md

// **** MODIFY here to add a new Language ****
const LANG_COPY_FROM = 'en';
const LANG_COPY_TO = 'es';

// In case autoloader is not found:
error_reporting(-1);
ini_set('display_errors', 'on');

// PHP Autoloader (dynamically load classes)
include __DIR__ . '/../autoload.php';

// -----------------------------------------------------------------
// Validate Folders and Downloaded Repositories
// -----------------------------------------------------------------

$root_dir = realpath(__DIR__ . '/../../');
if (basename($root_dir) !== 'fastsitephp') {
    echo 'Error - Unexpected file structure, this repository should be under a root [dataformsjs] dir.' . PHP_EOL;
    exit();
}

// Check for [https://github.com/fastsitephp/starter-site]
$starter_dir = $root_dir . '/starter-site/app_data/i18n';
if (!is_dir($starter_dir)) {
    echo 'Error - Missing [starter-site] repository.' . PHP_EOL;
    echo $starter_dir . PHP_EOL;
    exit();
}

// Readme file
if (LANG_COPY_FROM === 'en') {
    $readme_from = $root_dir . '/fastsitephp/README.md';
} else {
    $readme_from = $root_dir . '/fastsitephp/docs/i18n-readme/README.' . LANG_COPY_FROM . '.md';
}
$readme_to = $root_dir . '/fastsitephp/docs/i18n-readme/README.' . LANG_COPY_TO . '.md';
if (!is_file($readme_from)) {
    echo 'Error - Missing [fastsitephp] readme file to copy from.' . PHP_EOL;
    echo $readme_from . PHP_EOL;
    exit();
}

// Check for [https://github.com/fastsitephp/playground]
$playground_dir_from = $root_dir . '/playground/app_data/template/' . LANG_COPY_FROM;
$playground_dir_to = $root_dir . '/playground/app_data/template/' . LANG_COPY_TO;
if (!is_dir($playground_dir_from)) {
    echo 'Error - Missing [playground] template' . PHP_EOL;
    echo $playground_dir_from . PHP_EOL;
    exit();
}

// -----------------------------------------------------------------
// Copy Files
// -----------------------------------------------------------------

$checked_files = [];
$copied_files = [];

// JSON Files
$search = new \FastSitePHP\FileSystem\Search();
$files = $search
    ->dir($starter_dir)
    ->fileTypes(['json'])
    ->includeRegExNames(['/.' . LANG_COPY_FROM . '.json$/'])
    ->excludeNames(['translators-needed.en.json'])
    ->fullPath(true)
    ->files();

$web_files = $search
    ->dir($root_dir . '/fastsitephp/website/app_data/i18n')
    ->files();

$all_files = array_merge($files, $web_files);
foreach ($all_files as $file) {
    $find = '.' . LANG_COPY_FROM . '.json';
    $replace = '.' . LANG_COPY_TO . '.json';
    $dest = str_replace($find, $replace, $file);
    $checked_files[] = $dest;
    if (!is_file($dest)) {
        copy($file, $dest);
        $copied_files[] = $dest; 
    }
}

// Readme File
$checked_files[] = $readme_from;
if (!is_file($readme_to)) {
    copy($readme_from, $readme_to);
    $copied_files[] = $readme_to;
}

// Text files
$web_files = $search
    ->reset()
    ->dir($root_dir . '/fastsitephp/website/app_data/i18n/code')
    ->files();

foreach ($files as $file) {
    $find = '.' . LANG_COPY_FROM . '.txt';
    $replace = '.' . LANG_COPY_TO . '.txt';
    $dest = str_replace($find, $replace, $file);
    $checked_files[] = $dest;
    if (!is_file($dest)) {
        copy($file, $dest);
        $copied_files[] = $dest; 
    }
}

// Home Page Code
$home_from = $root_dir . '/fastsitephp/website/app_data/sample-code/home-page-' . LANG_COPY_FROM . '.php';
$home_to = $root_dir . '/fastsitephp/website/app_data/sample-code/home-page-' . LANG_COPY_TO . '.php';
$checked_files[] = $home_from;
if (!is_file($home_to)) {
    copy($home_from, $home_to);
    $copied_files[] = $home_to;
}

// Playground Template
if (!is_dir($playground_dir_to)) {
    mkdir($playground_dir_to);
}
if (!is_dir($playground_dir_to . '/app')) {
    mkdir($playground_dir_to . '/app');
}

$files = $search
    ->reset()
    ->dir($playground_dir_from)
    ->fileTypes(['htaccess', 'php', 'htm', 'css', 'js', 'jsx', 'svg'])
    ->fullPath(true)
    ->files();

$app_files = $search
    ->dir($playground_dir_from . '/app')
    ->fullPath(true)
    ->files();

$all_files = array_merge($files, $app_files);
foreach ($all_files as $file) {
    $find = DIRECTORY_SEPARATOR . LANG_COPY_FROM . DIRECTORY_SEPARATOR;
    $replace = DIRECTORY_SEPARATOR . LANG_COPY_TO . DIRECTORY_SEPARATOR;
    $dest = str_replace($find, $replace, $file);
    $checked_files[] = $dest;
    if (!is_file($dest)) {
        copy($file, $dest);
        $copied_files[] = $dest; 
    }
}
    
// Show Results
echo 'Files Checked: ' . count($checked_files) . PHP_EOL;
echo 'Files Copied:' . count($copied_files) . PHP_EOL;
if ($copied_files) {
    echo 'New Files:' . PHP_EOL;
    foreach ($copied_files as $file) {
        echo $file . PHP_EOL;
    }    
}
