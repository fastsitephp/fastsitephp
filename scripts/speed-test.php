<?php

// Copy this file to root folder and run with Apache HTTP server benchmarking tool:
//     sudo apt-get install apache2-utils
//     https://httpd.apache.org/docs/2.4/programs/ab.html

// Run
// ab -n 10000 -c 10 http://localhost/FastSitePHP/vendor/fastsitephp/scripts/speed-test.php/FastSitePHP
// ab -n 10000 -c 10 http://localhost/FastSitePHP/vendor/fastsitephp/scripts/speed-test.php

/*
// Example Results from an older Mac:

speed-test.php
Concurrency Level:      10
Time taken for tests:   13.910 seconds
Complete requests:      10000
Failed requests:        0
Total transferred:      2130000 bytes
HTML transferred:       110000 bytes
Requests per second:    718.90 [#/sec] (mean)
Time per request:       13.910 [ms] (mean)
Time per request:       1.391 [ms] (mean, across all concurrent requests)
Transfer rate:          149.54 [Kbytes/sec] received


speed-test.php/FastSitePHP
Concurrency Level:      10
Time taken for tests:   15.351 seconds
Complete requests:      10000
Failed requests:        0
Total transferred:      2190000 bytes
HTML transferred:       170000 bytes
Requests per second:    651.42 [#/sec] (mean)
Time per request:       15.351 [ms] (mean)
Time per request:       1.535 [ms] (mean, across all concurrent requests)
Transfer rate:          139.32 [Kbytes/sec] received
*/

require __DIR__ . '/../src/Application.php';
require __DIR__ . '/../src/Route.php';

$app = new \FastSitePHP\Application();
$app->setup('UTC');

$app->get('/', function() use ($app) {
    return 'Hello World';
});

$app->get('/:name', function($name) use ($app) {
	return 'Hello ' . $app->escape($name);
});

$app->run();
