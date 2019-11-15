<?php

// If this file is accessed directly then output the file name and exit
if (!isset($app)) {
    echo basename(__FILE__);
    exit();
}

// Add route, this file gets loaded from mount()
$app->get('/mount2', function() use ($app) {
    return $app->requestedPath();
});
