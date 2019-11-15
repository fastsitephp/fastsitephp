<?php
// ==================================================================
// Unit Testing Page
// *) Check class [\FastSitePHP\Environment\System()].
// *) These functions return system info that would change
//    on every system, so rather than check values checking
//    expected return format, data types, etc is done.
//    This verifies that the class returns info ont he test system.
// ==================================================================

// ------------------------------------------------------------
// Setup FastSitePHP
// ------------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Environment/System.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
    require '../src/Environment/System.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// ------------------------------------------------------------
// Define Routes for Unit Testing
// ------------------------------------------------------------

// Check how the Class is defined
$app->get('/check-environ-system-class', function() {
    $sys = new \FastSitePHP\Environment\System();
    return array(
        'get_class' => get_class($sys),
        'get_parent_class' => get_parent_class($sys),
    );
});

// Check Default Object Properties
$app->get('/check-environ-system-properties', function() {
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
    $net = new \FastSitePHP\Environment\System();
    return checkObjectProperties($net, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
});

// Check Functions, this is similar to the above function
// but instead of checking properties it checks the functions.
$app->get('/check-environ-system-methods', function() {
    // Define arrays of function names by type
    $private_methods = array();
    $public_methods = array(
        'osVersionInfo', 'systemInfo', 'diskSpace', 'mappedDrives'
    );
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $sys = new \FastSitePHP\Environment\System();
    return checkObjectMethods($sys, $private_methods, $public_methods);
});

// Check that System Info returns expected fields and data types
$app->get('/get-os-info', function() {
    $sys = new \FastSitePHP\Environment\System();
    $info = $sys->osVersionInfo();
    $result = '';
    foreach ($info as $key => $value) {
        $result .= '[' . $key . ':' . gettype($value) . ']';
    }
    return $result;
});

// Check that System Info returns a string.
$app->get('/get-sys-info', function() {
    $sys = new \FastSitePHP\Environment\System();
    return 'systemInfo(): ' . gettype($sys->systemInfo());
});

// Check that Disk Space returns expected fields and data types
$app->get('/get-disk-space', function() {
    $sys = new \FastSitePHP\Environment\System();
    $info = $sys->diskSpace();
    $result = '';
    foreach ($info as $key => $value) {
        $result .= '[' . $key . ':' . gettype($value) . ']';
    }
    return $result;
});

// [mappedDrives()] should return an array
$app->get('/get-mapped-drives', function() {
    $sys = new \FastSitePHP\Environment\System();
    return 'mappedDrives(): ' . gettype($sys->mappedDrives());
});

// ---------------------------------
// Run the application
// ---------------------------------
$app->run();
