<?php
// This gets loaded from files [test-app.php] and [test-app-url-case.php]

// If this file is accessed directly then output the file name and exit
if (!isset($app)) {
    echo basename(__FILE__);
    exit();
}

// Add route, this file gets loaded from mount()
$app->get('/mount/test', function() use ($app) {
    return $app->requestedPath();
});