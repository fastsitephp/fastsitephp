<?php

// -------------------------------
// Setup
// -------------------------------

// Setup a PHP Autoloader
// This allows classes to be dynamically loaded
require '../../../autoload.php';

// Or for a minimal site only the following 2 files need to be included
// require '../vendor/fastsitephp/src/Application.php';
// require '../vendor/fastsitephp/src/Route.php';

// Create the Application Object with Error Handling and UTC for the Timezone
$app = new \FastSitePHP\Application();
$app->setup('UTC');

// -------------------------------
// Define Routes
// -------------------------------

// Send a response of 'Hello World!' for default requests
$app->get('/', function() {
    return 'Hello World!';
});

// Send a response 'Hello World!' for the URL '/hello' or in the case of the
// optional [name] variable safely escape and return a message with the name
// (example: '/hello/FastSitePHP' will output 'Hello FastSitePHP!')
$app->get('/hello/:name?', function($name = 'World') use ($app) {
    return 'Hello ' . $app->escape($name) . '!';
});

// Send a JSON Response that contains an object with basic Site info
$app->get('/site', function() use ($app) {
    return [
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    ];
});

// Send a JSON Response that contains basic Request info
$app->get('/request', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'acceptEncoding' => $req->acceptEncoding(),
        'acceptLanguage' => $req->acceptLanguage(),
        'origin' => $req->origin(),
        'userAgent' => $req->userAgent(),
        'referrer' => $req->referrer(),
        'clientIp' => $req->clientIp(),
        'protocol' => $req->protocol(),
        'host' => $req->host(),
        'port' => $req->port(),
    ];
});

// Send the contents of this file as a plain text response using
// HTTP Response Headers that allow for the end user to cache the
// page until the file is modified
$app->get('/cached-file', function() {
    $file_path = __FILE__;
    $res = new \FastSitePHP\Web\Response();
    return $res->file($file_path, 'text', 'etag:md5', 'private');
});

// Return the user's IP Address as a JSON Web Service that supports
// Cross-Origin Resource Sharing (CORS) and specifically tells the browser
// to not cache the results. In this example the Web Server is assumed to
// be behind a proxy server (for example a Load Balancer) and the IP Address
// is safely read from it. Additionally the cors() function is called from a
// filter function which only gets called if the route is matched and allows
// for correct handling of an OPTIONS request.
$app->get('/whats-my-ip', function() {
    $req = new \FastSitePHP\Web\Request();
    return [
        'ipAddress' => $req->clientIp('from proxy', 'trust local'),
    ];
})
->filter(function() use ($app) {
    $app
        ->noCache()
        ->cors('*');
});

// Define a function that returns true if the web request is coming
// from a local network (for example 127.0.0.1 or 10.0.0.1). This
// function will be used in a filter to show or hide routes.
$is_local = function() {
    // Compare Request IP using Classless Inter-Domain Routing (CIDR)
    $req = new \FastSitePHP\Web\Request();
    $private_ips = \FastSitePHP\Net\IP::privateNetworkAddresses();

    return \FastSitePHP\Net\IP::cidr(
        $private_ips,
        $req->clientIp('from proxy')
    );
};

// Provide detailed environment info from PHP for users requesting the page
// from a local network. If the request is coming from someone on the internet
// then a 404 Response 'Page not found' would be returned. Calling [phpinfo()]
// outputs an HTML response so the route does not need to return anything.
$app->get('/phpinfo', function() {
    phpinfo();
})
->filter($is_local);

// Provide a Text Response with Server Info for Local Users
$app->get('/server', function() {
    $config = new \FastSitePHP\Net\Config();
    $req = new \FastSitePHP\Web\Request();
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Host: {$config->fqdn()}",
            "Server IP: {$req->serverIp()}",
            "Network IP: {$config->networkIp()}",
            str_repeat('-', 80),
            $config->networkInfo(),
        ]));
})
->filter($is_local);

// If the requested url starts with '/examples' then load a PHP file for
// the matching routes from the current directory. This is a real file
// that provides many more examples. If you download this site, this code
// and other examples can be found in [app_data/sample-code].
$app->mount('/examples', 'home-page-en-examples.php');

// -------------------------------
// Run the application
// -------------------------------
$app->run();
