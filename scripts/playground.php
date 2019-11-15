<?php
// Copy to Webroot [public] folder and then modify
// and run to test features in development or run from here.

// Testing without the Application Object
error_reporting(-1);
ini_set('display_errors', 'on');
// date_default_timezone_set('UTC');
// set_time_limit(0);

// Autoloader and Setup App
require __DIR__ . '/../../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// Data Example
$data = \FastSitePHP\Net\IP::privateNetworkAddresses();

// Examples with a Plain Text Response

header('Content-Type: text/plain');
echo json_encode($data, JSON_PRETTY_PRINT);

echo "\n";
echo "\n";
var_dump($data);

echo "\n";
echo "\n";
print_r($data);

// Run the App if Testing Routes
// $app->run();
