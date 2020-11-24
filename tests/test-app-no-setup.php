<?php
// ==========================================================
// Unit Testing Page
// *) This file uses only core Framework files and errors 
//    are not handled because the setup() function is 
//    never called.
// *) Because there is no error handling defined this page
//    should return 500 status code errors with a blank 
//    screen for all errors unless the [php.ini] settings
//    are configured to display errors. If the settings are
//    configured that way then the user is alerted so on
//    the unit test page.
// ==========================================================

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

// Create the Application Object
$app = new \FastSitePHP\Application();

// Error Handling - Uncomment below only if needed during development
// If there is still a blank screen then use the [wsod.php] page.
// $app->setup('UTC');

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// The user should get a WSOD (White-Screen of Death) because no
// error handling is defined, this could fail if the php server 
// setting [display_errors] is turned on outside of this page.
$app->get('/exception', function() use ($app) {
   throw new \Exception('Exception Test');
});

// Error Test with Error Type E_ERROR.
// Just like the above route '/exception' this is expected
// to return a WSOD in production enviroments.
$app->get('/error-fatal', function() use ($app) {
    $test = new UnknownObject();
});

// Test routeMatches() with an invalid regular expression validation for the parameter.
// This function is duplicated in two files and tested twice to make sure that
// the correct exception is thrown regardless of whether setup() is called or not:
//  1) test-app.php
//  2) test-app-no-setup.php
$app->get('/route-matches-param-error', function() use ($app) {
    // [error_reporting] is changed and reset by this function call.
    // Make sure it is set back to the original value.
    $current_error_level = error_reporting();

    // Define the Parameter
    $app->param(':regex_invalid', 'ABC');
    
    // Run Test
    try {
        $args = $app->routeMatches('/:regex_invalid', '/123');
        echo 'This line should never get called: ' . $matches;
    } catch (\Exception $e) {
        // Check original value
        if ($current_error_level !== error_reporting()) {
            return 'error_reporting was not reset';
        }        
        return $e->getMessage();
    }
});

// Similar to the above Test routeMatches() is tested with invalid regular expression 
// however a custom error handler function is defined that does return a value.
// Because no value is returned when set_error_handler() is called the error variable
// Just like the above test this is also tested in two separate files.
$app->get('/route-matches-custom-error', function() use ($app) {
    // Overwrite any existing Error Handler
    set_error_handler(function($severity, $message, $file, $line) {
        return;
    });

    // Define the Parameter and Run Test
    $app->param(':regex_invalid', 'ABC');
    try {
        $app->routeMatches('/:regex_invalid', '/123');
        return 'This line should never get called: routeMatches(:regex_invalid)';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
