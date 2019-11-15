<?php

// Testing file that gets called from [performance-testing.htm]
//
// This script can be used to see the performance of generating an empty response
// from a web browser for the current server. With the default setup 
// [Application vs AppMin] on some servers the performance will be twice as fast
// for [AppMin vs Application] however with PHP 7 and OPcache turned on
// performance is roughly the same.
//
// This script (and the HTML page) can be easily modified to test different features.
//
// For Command Line Testing see also:
//     Apache HTTP server benchmarking tool:
// sudo apt-get install apache2-utils
// https://httpd.apache.org/docs/2.4/programs/ab.html

// Show Errors in case files are not loaded
error_reporting(-1);
ini_set('display_errors', 'on');

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include Debug File for Tracking Page Speed and Memory
// and Setup PHP Autoloader
require __DIR__ . '/../src/Utilities/debug.php';
require __DIR__ . '/../autoload.php';

// If this and [performance-testing.htm] are copied to the web roote
// folder then comment the above lines and use these:
//
// require '../vendor/fastsitephp/src/Utilities/debug.php';
// require '../vendor/autoload.php';

// Create the Application Object
switch ($_GET['app']) {
    case 'Application':
        $app = new \FastSitePHP\Application();
        break;
    case 'AppMin':
        $app = new \FastSitePHP\AppMin();
        break;
    default:
        echo 'Invalid app GET Query String Parameter';
        exit();
}

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Return an empty string
$app->get('/', function() use ($app) {
    return '';
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();

// Add script time and memory info to the end of the page
$showDebugInfo();
