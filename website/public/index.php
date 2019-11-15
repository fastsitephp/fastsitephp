<?php
// -----------------------------------------------------------
// Setup a PHP Autoloader and FastSitePHP
// -----------------------------------------------------------

// IMPORTANT - this file is slightly modified from the Starter Site
// so that it works for Framework Development directory structure.
// The actual production server uses the version from the Starter Site.

// Check if the query string [?_debug=stats] is defined
$show_debug_info = isset($_GET['_debug']) && $_GET['_debug'] === 'stats';

// If '?_debug=stats' is included in the query string then keep track of
// starting time and memory usage. The info shown does not pose a security
// risk however if you would like to prevent it from showing on your site
// simply comment out the line of code above or remove the related code. 
// For the if statement below the variable is checked the with isset()
// so that if the above line is commented out then no error will occur.
if (isset($show_debug_info) && $show_debug_info) {
    require __DIR__ . '/../../src/Utilities/debug.php';    
}

// Setup a PHP Autoloader so classes can be dynamically loaded
require __DIR__ . '/../../autoload.php';

// Vendor dependencies are not included by default. They are instead
// installed with [scripts/install.php] or Composer.
$path = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($path)) {
    require $path;
}

// Create and Setup the FastSitePHP Application Object
// If a specific timezone is desired you can obtain the 
// value to use from here:
//   http://php.net/manual/en/timezones.php
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// ------------------------------------------------------------------------------
// Include the App File for the Site.
// The file [app.php] is where routes, functions, and features unique to a site
// will be defined. Having the site's core app code outside of the web root is
// good security practice because if a site is compromised or if a server update 
// prevented php from working then it can reduce the chance of someone being able
// to view the site's server code. Additionally during development if there is a
// parsing error with the file then it will be caught and displayed on an error
// page because [$app->setup()] is called above, however if a lot of code was
// included in this file and there is a parsing error then a PHP WSOD 
// (White Screen of Death) error would occur instead.
// ------------------------------------------------------------------------------

require __DIR__ . '/../app/app.php';

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------

// Run the app to determine and show the specified URL
$app->run();

// If debug then add script time and memory info to the end of the page
if (isset($show_debug_info) && $show_debug_info) {
    $showDebugInfo();
}
