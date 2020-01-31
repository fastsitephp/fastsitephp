<?php
// Test Script for manually testing the HttpClient class.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected.

// Testing without the Application Object
// error_reporting(-1);
// ini_set('display_errors', 'on');
// date_default_timezone_set('UTC');
// set_time_limit(0);

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// var_dump(openssl_get_cert_locations());
// exit();

// var_dump(curl_version());
// exit();

// $http = new \FastSitePHP\Net\HttpClient();
// var_dump($http->certPath());
// exit();

// Include Debug File for Tracking Page Speed and Memory
require __DIR__ . '/../src/Utilities/debug.php';

$headers = array(
    'X-API-Key' => 'password123', 
    'X-Custom-Header' => 'test', 
    // 'User-Agent' => 'test/123',
);

// $res = \FastSitePHP\Net\HttpClient::get('http://www.example.com/');
// echo $res->content;
// exit();

header('Content-Type: text/plain');

// \FastSitePHP\Net\HttpClient::$allow_insecure = true;
// \FastSitePHP\Net\HttpClient::$cainfo_path = __DIR__ . '/cacert.pem';

// $res = \FastSitePHP\Net\HttpClient::get('https://httpbin.org/json');
// echo gettype($res->json);
// echo gettype($res->content);

// print_r(\FastSitePHP\Net\HttpClient::get('http://httpbin.org/json'));
// print_r(\FastSitePHP\Net\HttpClient::get('https://httpbin.org/json'));
// print_r(\FastSitePHP\Net\HttpClient::get('https://httpbin.org/json', $headers));
print_r(\FastSitePHP\Net\HttpClient::get('http://www.example.com/'));

// print_r(\FastSitePHP\Net\HttpClient::get('https://httpbin.org/status/404'));
// print_r(\FastSitePHP\Net\HttpClient::get('https://httpbin.org/status/500'));


// // NOTE - to test with a video add a real URL and uncomment
// //$url = 'http://.../video.mp4';
// //$file = __DIR__ . '/' . basename($url);
// //
// $url = 'http://www.example.com/';
// $file = __DIR__ . '/example.htm';
// print_r(\FastSitePHP\Net\HttpClient::downloadFile($url, $file));

// print_r(\FastSitePHP\Net\HttpClient::postJson('https://httpbin.org/post', array(
//     'text' => 'test',
//     'num' => 123,
// ), $headers));

// print_r(\FastSitePHP\Net\HttpClient::postForm('https://httpbin.org/post', array(
//     'text' => 'test',
//     'num' => 123,
// ), $headers));

// Large data, this will trigger a 'Expect: 100-continue' from [curl]
// [curl] source: 
//   EXPECT_100_THRESHOLD 
//   https://github.com/curl/curl/blob/master/lib/http.h
//   https://github.com/curl/curl/blob/master/lib/http.c
//
// print_r(\FastSitePHP\Net\HttpClient::postForm('https://httpbin.org/post', array(
//     'text' => str_repeat('a', 1024),
// ), $headers));

// print_r(\FastSitePHP\Net\HttpClient::postForm('https://httpbin.org/post', array(
//     'text' => 'test',
//     'num' => 123,
//     // 'txtFile' => new \CURLFile(__FILE__),
//     // 'txtFile' => new \CURLFile(__FILE__, 'text/plain', 'text_file'),

//     // Sends as [data:application/octet-stream]
//     'jpgFile' => new \CURLFile('C:\Users\Public\Pictures\Thumbnails\Desert.jpg'),

//     // Sends as [data:image/jpg;base64]
//     // 'jpgFile' => new \CURLFile('C:\Users\Public\Pictures\Thumbnails\Desert.jpg', 'image/jpg'),

//     // Larger Images
//     // 'img1' => new \CURLFile('C:\Users\Public\Pictures\Sample Pictures\Desert.jpg', 'image/jpg'),
//     // 'img2' => new \CURLFile('C:\Users\Public\Pictures\Sample Pictures\Chrysanthemum.jpg', 'image/jpg'),
// ), $headers));

// $data = array('test' => '123');
// $http = new \FastSitePHP\Net\HttpClient();
// $res = $http->request('https://httpbin.org/put', array(
//     'method' => 'PUT',
//     'headers' => $headers,
//     'json' => $data,
// ));
// print_r($res);

$http = new \FastSitePHP\Net\HttpClient();
$res = $http->request('https://httpbin.org/anything', array(
    // 'method' => 'HEAD',
    // 'mode' => 'php',
    'headers' => $headers,
    'parse_json' => false,
    // 'user_agent' => 'test',
));
print_r($res);

$http = new \FastSitePHP\Net\HttpClient();
$res = $http->request('https://httpbin.org/anything', array(
    'mode' => 'php',
    'headers' => $headers,
    'parse_json' => false,
));
print_r($res);

// PUT or POST a file
// $data = array('test' => '123');
// $http = new \FastSitePHP\Net\HttpClient();
// $res = $http->request('https://httpbin.org/anything', array(
//     // 'mode' => 'php',
//     'method' => 'PUT', // 'POST'
//     'headers' => $headers,
//     'send_file' => 'C:\Users\Public\Pictures\Thumbnails\Desert.jpg',
// ));
// print_r($res);

// ----------------------------------------
// Testing different modes (curl vs php)

// $url = 'https://httpbin.org/redirect-to?url=https%3A%2F%2Fhttpbin.org%2Fjson&status_code=302';
// // $options = array(
// //     // 'mode' => 'curl',
// //     'mode' => 'php',
// // );

// $url = 'https://httpbin.org/post';
// $options = array(
//     // 'mode' => 'curl',
//     'mode' => 'php',
//     'method' => 'POST',
//     'headers' => $headers,
//     'json' => array( // or 'form'
//         'test' => 123,
//         'test2' => 'abc',
//     ),
// );

// $url = 'https://httpbin.org/status/500';
// $options = array(
//     'mode' => 'php',
// );

// $http = new \FastSitePHP\Net\HttpClient();
// print_r($http->request($url, $options));
// print_r($http->request($url, $options));
// ----------------------------------------

// Add script time and memory info to the end of the page
$showDebugInfo(true);