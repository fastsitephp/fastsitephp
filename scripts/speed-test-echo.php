<?php

// See comments in [speed-test.php]

// Run
// ab -n 10000 -c 10 http://localhost/fastsitephp/public/speed-test-echo.php

/*
Concurrency Level:      10
Time taken for tests:   3.713 seconds
Complete requests:      10000
Failed requests:        0
Write errors:           0
Total transferred:      2350000 bytes
HTML transferred:       110000 bytes
Requests per second:    2693.31 [#/sec] (mean)
Time per request:       3.713 [ms] (mean)
Time per request:       0.371 [ms] (mean, across all concurrent requests)
Transfer rate:          618.09 [Kbytes/sec] received
*/

echo 'Hello World';
