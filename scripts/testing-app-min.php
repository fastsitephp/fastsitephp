<?php
// Only two files are required to run FastSitePHP AppMin and they can
// be in the same directory as [index.php] or the main php page.
require '../src/AppMin.php';
require '../src/Route.php';

// Create the AppMin Object and optionally setup
// Error Handling and a Timezone.
$app = new FastSitePHP\AppMin();
$app->setup('UTC');

// Define the 'Hello World' default route
$app->get('/', function() {
    return 'Hello World!';
});

// Return a JSON Response by returning an Object or an Array
$app->get('/json', function() {
    return ['Hello' => 'World'];
});

// Send a Plain Text Response and Custom Header. AppMin is minimal in size so
// optional URL parameters [:name?] and Wildcard URL's [*] are not supported.
$app->get('/hello/:name', function($name) use ($app) {
    $app->headers = [
        'Content-Type' => 'text/plain',
        'X-Custom-Header' => $name,
    ];
    return 'Hello ' . $name;
});

// Detailed Error info will show for localhost otherwise the following
// must be called just like when using the standard `Application` Class:
//     $app->show_detailed_errors = true;
$app->get('/error', function() {
    return 1 / 0;
});

// Run the App
$app->run();