<?php
// ===============================================
// Unit Testing Page for Net Config Object
// ===============================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Net/Config.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
    require '../src/Net/Config.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Check how the Network Config Object is defined
$app->get('/check-net-config-class', function() use ($app) {
    $net = new \FastSitePHP\Net\Config();
    return array(
        'get_class' => get_class($net),
        'get_parent_class' => get_parent_class($net),
    );
});

// Check Default Object Properties
$app->get('/check-net-config-properties', function() use ($app) {
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
    $config = new \FastSitePHP\Net\Config();
    return checkObjectProperties($config, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
});

// Check Functions, this is similar to the above function
// but instead of checking properties it checks the functions.
$app->get('/check-net-config-methods', function() use ($app) {
    // Define arrays of function names by type
    $private_methods = array('parseIpAddr', 'parseIpConfig');
    $public_methods = array(
        'fqdn', 'networkIp', 'networkInfo', 'networkIpList', 'parseNetworkInfo',
    );

    // Load the core function file and verify the object
    // using a function defined in the file.
    require('./core.php');
    $config = new \FastSitePHP\Net\Config();
    return checkObjectMethods($config, $private_methods, $public_methods);
});

// Unit Test the for Fully-Qualified Domain Name but for security don't provide
// actual FQDN to to the client page.
$app->get('/fqdn', function() use ($app) {
    // This function makes OS Level Network calls so only call it once in this test
    $config = new \FastSitePHP\Net\Config();
    $fqdn = $config->fqdn();

    // Check that the Web Server can return a valid IP
    $response = sprintf('type: %s', gettype($fqdn));
    return $response;
});

// Unit Test the Computer/Network IP but for security don't provide
// actual IP Address Info to to the client page.
$app->get('/network-ip', function() use ($app) {
    // This function makes OS Level Network calls so only call it once in this test
    $config = new \FastSitePHP\Net\Config();
    $ip = $config->networkIp();

    // Check that the Web Server can return a valid IP
    $response = sprintf('[type:%s]', gettype($ip));
    $response .= sprintf('[is_ip:%s]', (filter_var($ip, FILTER_VALIDATE_IP) === false ? 'false' : 'true'));
    return $response;
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
