<?php
// ==================================================================
// Unit Testing Page
// *) The route in this file generates a E_COMPILE_WARNING
//    Error once the clousure is evaluated so it can't be 
//    used in other unit testing pages as it will always 
//    generate an error. On some versions of PHP 7+ the error
//    will not be triggered.
// ==================================================================

// ------------------------------------------------------------
// Setup FastSitePHP
// ------------------------------------------------------------

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
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Error Test with Error Type E_COMPILE_WARNING
$app->get('/error-compile-warning', function() use ($app) {
    declare(test='test2');
    echo 'php' . PHP_MAJOR_VERSION;
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
