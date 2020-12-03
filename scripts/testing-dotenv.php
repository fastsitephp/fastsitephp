<?php
// Test Script for manually testing the [Environment\DotEnv] class.
// In the future this code (or similar) needs to be moved to a full Unit Test.
//
// Full unit tests should likely write a [.env] file to the temp directory
// and use the following file and example for testing:
//    https://github.com/motdotla/dotenv/blob/master/tests/.env
//    https://github.com/motdotla/dotenv/blob/master/tests/test-parse.js
//
// FastSitePHP's DotEnv file is based on the node project. When setting
// up full unit test add a disclaimer that the test file copyright is in
// the [Environment\DotEnv] class.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// Output as Text
header('Content-Type: text/plain');

// Before Update
echo str_repeat('=', 80) . "\n";
echo json_encode($_ENV, JSON_PRETTY_PRINT) . "\n";
echo str_repeat('-', 80) . "\n";
echo "getenv('USERNAME'): " . getenv('USERNAME') . "\n";
echo str_repeat('-', 80) . "\n";
echo "getenv('EXPAND_NEWLINES'): " . getenv('EXPAND_NEWLINES') . "\n";

// Read file
$vars = \FastSitePHP\Environment\DotEnv::load(__DIR__);

// Error Test
// $required_vars = array('ERROR_TEST_1', 'ERROR_TEST_2');
// $vars = \FastSitePHP\Environment\DotEnv::load(__DIR__, $required_vars);

// After Update
echo str_repeat('=', 80) . "\n";
echo json_encode($_ENV, JSON_PRETTY_PRINT) . "\n";
echo str_repeat('-', 80) . "\n";
echo json_encode($vars, JSON_PRETTY_PRINT) . "\n";
echo str_repeat('-', 80) . "\n";
echo "getenv('USERNAME'): " . getenv('USERNAME') . "\n";
echo str_repeat('-', 80) . "\n";
echo "getenv('EXPAND_NEWLINES'): " . getenv('EXPAND_NEWLINES') . "\n";

