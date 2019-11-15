<?php
// ============================================================
// Unit Testing Page
// *) This file uses only core Framework files.
// *) This file is to test [$app->case_sensitive_urls = false]
//	  The default for the value is true.
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
$app->setup('UTC');
$app->show_detailed_errors = true;

// By default '/URL' and '/url' will not match
// but setting this property to false allows URL's
// to be matched as case-insensitive strings.
$app->case_sensitive_urls = false;

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Testing for [$app->case_sensitive_urls = false]
//   html/test-02.php/get-url    =   /get-url
//   html/test-02.php/GET-URL    =   /GET-URL
// Return a text string with the current URL
$app->get('/get-url', function() use ($app) {
    return $app->requestedPath();
});

// Testing mount() path routes with [case_sensitive_urls = false].
// If the url beings with '/mount/' then the following file will loaded.
// Just like [test-app.js] the client side version testing
// this file will call both '/mount/test' and 'MOUNT/TEST'. Because
// of the property [case_sensitive_urls] 'MOUNT/TEST' will work here
// and fail in [test-app.js].
$app->mount('/mount/', 'test-app-mount.php');

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
