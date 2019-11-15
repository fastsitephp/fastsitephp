<?php
// Test Script for manually testing the CSRF classes.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
$app->template_dir = __DIR__;
set_time_limit(0);

use \FastSitePHP\Security\Web\CsrfSession;
use \FastSitePHP\Security\Web\CsrfStateless;

// Use this to create a key
// echo FastSitePHP\Security\Web\CsrfStateless::generateKey();
// exit();

// Clear Session manually if needed
// session_start();
// session_destroy();

$csrf_session = function() use ($app) {
    CsrfSession::setup($app);
};

$csrf_stateless = function() use ($app) {
    $app->config['CSRF_KEY'] = 'f85cbf73026a4fde28771c8fa25ceecf9f2e9c33b5b2cf4c2f38185e164bf1e5';
    $user_id = 1;
    CsrfStateless::setup($app, $user_id);
};

// This CSRF token will expire after 10 seconds
$csrf_stateless_time = function() use ($app) {
    $app->config['CSRF_KEY'] = 'f85cbf73026a4fde28771c8fa25ceecf9f2e9c33b5b2cf4c2f38185e164bf1e5';
    $user_id = 1;
    CsrfStateless::setup($app, $user_id, '+10 seconds');
};

$app->get('/', function() use ($app) {
    echo $app->render('testing-csrf.htm.php');
})
->filter($csrf_stateless_time);

$app->post('/', function() use ($app) {
    var_dump($_POST);
    exit();
})
->filter($csrf_stateless_time);


$app->run();