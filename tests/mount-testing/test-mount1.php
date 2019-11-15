<?php
// This gets loaded from the file [test-app.php]

// If this file is accessed directly then output the file name and exit
if (!isset($app)) {
    echo basename(__FILE__);
    exit();
}

// This file gets loaded from mount() using the full file path.
// Call mount() to load the file in the current directory.
$app->mount('/mount2', 'test-mount2.php');
