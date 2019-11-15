<?php
// This is a manual test to verify what happens if the default template files
// are not found. This is not unit tested with the regular unit testing
// page because this condition would only happen if Core Framework files are missing.

// ---------------------------------------------------
// Include Core Framework Files
// ---------------------------------------------------
// For this tests the Core Framework files must
// be in the same Directory as this routing file.
// This can be tested simply by copying the files
// to the same folder as this script.
require 'Application.php';
require 'Route.php';

// -----------------------------------------
// Create and setup the Application Object
// -----------------------------------------
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Define App Events
// -------------------------------

$app->before(function() use ($app) {
    // Handle routes that never get called.
    switch ($app->requestedPath()) {
        case '/method-not-allowed-custom':
            $app->method_not_allowed_title = '405 Custom Response';
            $app->method_not_allowed_message = 'Method Not Allowed, [Request: {method}], [Allowed: {allowed_methods}]';
            break;
        case '/404-custom':
            $app->not_found_page_title = '404 Custom Response';
            $app->not_found_page_message = 'Page Not Found.';
            break;
    }
});

// -------------------------------
// Define Routes
// -------------------------------

$app->get('/', function() use ($app) {
    $html = '<ul>';
    $html .= '<li><a href="' . $app->rootUrl() . 'error">Error Test</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . 'error-custom">Error Test (Custom)</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . 'not-found">Page Not Found</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . 'not-found-custom">Page Not Found (Custom)</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . 'method-not-allowed">Method Not Allowed</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . 'method-not-allowed-custom">Method Not Custom</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . '404">Missing Page</a></li>';
    $html .= '<li><a href="' . $app->rootUrl() . '404-custom">Missing Page (Custom)</a></li>';
    $html .= '</ul>';
    return $html;
});

$app->get('/error', function() {
    throw new \Exception('Error Test');
});

$app->get('/error-custom', function() use ($app) {
    $app->error_page_title = '500 Response';
    $app->error_page_message = 'Page Error';
    throw new \Exception('Error Test');
});

$app->get('/not-found', function() use ($app) {
    return $app->pageNotFound();
});

$app->get('/not-found-custom', function() use ($app) {
    $app->not_found_page_title = '404 Custom Response';
    $app->not_found_page_message = 'Page Not Found from pageNotFound()';
    return $app->pageNotFound();
});

$app->post('/method-not-allowed', function() {
    return 'Error - this should be called with a GET for testing.';
});

$app->post('/method-not-allowed-custom', function() {
    return 'Error - this should be called with a GET for testing.';
});

// -------------------------------
// Run the application
// -------------------------------
$app->run();
