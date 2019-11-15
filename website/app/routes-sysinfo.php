<?php
// Define System Info Routes, this gets loaded from [app.php] 
// only if the requested URL starts with [/sysinfo/].

use FastSitePHP\Net\Config;
use FastSitePHP\Web\Request;
use FastSitePHP\Web\Response;

// Provide detailed environment info from PHP
$app->get('/sysinfo/phpinfo', function() {
    // Show all info
    phpinfo();

    // The info displayed can be changed. This example shows everything but
    // Environment Variables. If you use [phpinfo()] and are saving senstive 
    // information in environment variables then you may want hide environment info.
    //
    //     http://php.net/manual/en/function.phpinfo.php
    //
    // phpinfo(INFO_ALL & ~INFO_ENVIRONMENT);
});

// Provide a Text Response with Server Info
$app->get('/sysinfo/server', function() use ($app) {
    $config = new Config();
    $req = new Request();
    $res = new Response();
    return $res
        ->contentType('text')
        ->content(implode("\n", [
            "Host: {$config->fqdn()}",
            "Server IP: {$req->serverIp()}",
            "Network IP: {$config->networkIp()}",
            str_repeat('-', 80),
            $config->networkInfo(),
        ]));
});
