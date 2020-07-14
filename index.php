<?php

// This file runs from a development environment and simply redirects
// to the [website/public] directory which would be used as the
// public root directory on a web server.

// To run from a command line or terminal program you can use the following:
//     cd {this-directory}
//     php -S localhost:3000
//
// Then open your web browser to:
//     http://localhost:3000/

// TODO - testing updates for Laravel Valet, the code block below
//  will be removed on everything fully works and all common PHP dev enviroments are confirmed.
//  This is related to issue:
//  https://github.com/fastsitephp/starter-site/issues/4
//
// header('Content-Type: text/plain');
// var_dump($_SERVER);
// exit();

if (isset($_SERVER['DOCUMENT_URI']) && strpos($_SERVER['DOCUMENT_URI'], '/laravel/valet/server.php') !== false) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/website/public';
    require __DIR__ . '/website/public/index.php';
} else {
    header('Location: website/public/');
}
