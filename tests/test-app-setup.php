<?php
// ============================================================
// Unit Testing Page
// *) This file is for testing different options with the
//    setup() function. In other unit testing files setup()
//    is called only once at the top of the file or not called
//    at all. Additionally this file is different as it is 
//    setting the timezone prior to calling any routes.
// ============================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
}

// Create and Setup the Application Object
$app = new \FastSitePHP\Application();
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Change Timezone using PHP Functions
// -----------------------------------------------------------

// First set the timezone to New York before any Unit Tests are called.
// Unit Tests will then set the timezone to a different value during testing.

$result = date_default_timezone_set('America/New_York');

if ($result === false || date_default_timezone_get() !== 'America/New_York') {
    die('Timezone could not be set prior to unit testing');
}

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

$app->get('/setup-not-called', function() use ($app) {
    // Don't call setup() and return the current timezone in a plain text document
    $app->header('Content-Type', 'text/plain');
    return date_default_timezone_get();
});

$app->get('/setup-utc', function() use ($app) {
    // Setup the app using UTC Timezone
    $app->setup('UTC');

    // Return the current timezone in a plain text document
    $app->header('Content-Type', 'text/plain');
    return date_default_timezone_get();
});

$app->get('/setup-phpini', function() use ($app) {
    // Set INI Settings to Los Angeles
    ini_set('date.timezone', 'America/Los_Angeles');

    // Setup the app using ini settings
    $app->setup('date.timezone');

    // Return the current timezone in a plain text document
    $app->header('Content-Type', 'text/plain');
    return date_default_timezone_get();
});

$app->get('/setup-phpini-error', function() use ($app) {
    // Make sure there are no ini settings, without using the
    // error control operator [@] this would trigger an error warning
    error_reporting(0);
    ini_set('date.timezone', '');

    // Setup the app using ini settings, this should throw an Exception
    $app->setup('date.timezone');

    // Code execution should never reach here but instead
    // the default error template should be shown.
    return '[Should not show on unit test]';
});

$app->get('/setup-timezone-error', function() use ($app) {
    // Setup the app using an invalid timezone
    $app->setup('abc123');

    // Code execution should never reach here but instead
    // the default error template should be shown.
    return '[Should not show on unit test]';
});

$app->get('/setup-null', function() use ($app) {
    // Setup the app without specifying a timezone. Because timezone was
    // defined prior to this call the original value will be used.
    $app->setup(null);

    // Return the current timezone in a plain text document
    $app->header('Content-Type', 'text/plain');
    return date_default_timezone_get();
});

$app->get('/setup-missing', function() use ($app) {
    // Verify that the setup() function cannot be called with a missing parameter.
    // In Versions of PHP prior to 7.1 this call will trigger an E_WARNING error
    // but still call this function. As of PHP 7.1 it will instead not call the function
    // and throw an ArgumentCountError Exception.
    try {
        $app->setup();
    } catch (\Throwable $e) {
        // Make sure that setup() is called for PHP 7.1+ with this specific error
        $app->setup('UTC');
        throw $e;
    }

    // Code execution should never reach here but instead
    // the default error template should be shown.
    return '[Should not show on unit test]';
});


$app->get('/setup-multiple', function() use ($app) {
    // Call the setup() method multiple times, this is allowed as 
    // PHP replaces previously defined error handlers.
    $app->setup('UTC');
    $app->setup('America/Los_Angeles');
    
    // Return the current timezone in a plain text document
    $app->header('Content-Type', 'text/plain');
    return date_default_timezone_get();
});

$app->get('/setup-settings', function() use ($app) {
    // Manualy set several setting prior to calling setup().
    // These settings should be overwritten by setup().
    error_reporting(0);
    ini_set('display_errors', 'on');    
    $app->setup('UTC');

    // Read and return the settings as a JSON Object
    return array(
        'error_reporting' => error_reporting(),
        'display_errors' => ini_get('display_errors'),
    );
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
