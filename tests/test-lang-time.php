<?php
// ===============================================
// Unit Testing Page for Lang Time Object
// ===============================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Lang/Time.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Lang/Time.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

$app->get('/check-lang-time-class', function() use ($app) {
    $time = new \FastSitePHP\Lang\Time();
    return array(
        'get_class' => get_class($time),
        'get_parent_class' => get_parent_class($time),
    );
});

$app->get('/check-lang-time-properties', function() use ($app) {
    // Define arrays of properties by type
    $null_properties = array();
    $true_properties = array();
    $false_properties = array();
    $string_properties = array();
    $array_properties = array();
    $private_properties = array();

    // Load the core function file and verify the object
    // using a function defined in the file.
    require('./core.php');
    $time = new \FastSitePHP\Lang\Time();
    return checkObjectProperties($time, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
});

$app->get('/check-lang-time-methods', function() use ($app) {
    // Define arrays of function names by type
    $private_methods = array('english');
    $public_methods = array('secondsToText');

    // Load the core function file and verify the object
    // using a function defined in the file.
    require('./core.php');
    $time = new \FastSitePHP\Lang\Time();
    return checkObjectMethods($time, $private_methods, $public_methods);
});

$app->get('/time-seconds-to-text', function() use ($app) {
    return array(
        \FastSitePHP\Lang\Time::secondsToText(129680),
        \FastSitePHP\Lang\Time::secondsToText(10),
        \FastSitePHP\Lang\Time::secondsToText(0),
        \FastSitePHP\Lang\Time::secondsToText(1),
        \FastSitePHP\Lang\Time::secondsToText(59),
        \FastSitePHP\Lang\Time::secondsToText(60),
        \FastSitePHP\Lang\Time::secondsToText(119),
        \FastSitePHP\Lang\Time::secondsToText(120),
        \FastSitePHP\Lang\Time::secondsToText(60 * 60),
        \FastSitePHP\Lang\Time::secondsToText(60 * 60 * 24),
        \FastSitePHP\Lang\Time::secondsToText((60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20),
        \FastSitePHP\Lang\Time::secondsToText(31536000 + (60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20),
        \FastSitePHP\Lang\Time::secondsToText((31536000*2) + (60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20),
    );
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
