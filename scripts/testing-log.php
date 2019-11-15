<?php
// Test Script for manually testing the Logger classes.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected.

// In case autoloader is not found:
error_reporting(-1);
ini_set('display_errors', 'on');

// Autoloader
// This requires the [psr/log] dependency to be installed
require __DIR__ . '/../autoload.php';
require __DIR__ . '/../vendor/autoload.php';

// App Setup
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Create a Logger

const TEST_HTML_LOGGER = true;

if (TEST_HTML_LOGGER) {
    $is_cli = (php_sapi_name() === 'cli');
    if ($is_cli) {
        echo 'ERROR - run this from a browser';
        exit();
    }

    $replace_response = false;
    if ($replace_response) {
        $logger = new \FastSitePHP\Data\Log\HtmlLogger($app, $replace_response);
    } else {
        $logger = new \FastSitePHP\Data\Log\HtmlLogger($app);
    }
} else {
    $file = __DIR__ . '/log.txt';
    $logger = new \FastSitePHP\Data\Log\FileLogger($file);
}

class Test
{
    function __toString()
    {
        return 'Test Class';
    }
}

class Test2
{

}

// Default Format:
//     '{date} {level} - {message}{line_break}';
// Line Breaks default based on the OS:
//     "\r\n" - Windows 
//     "\n"   - Other OS's
//
// $logger->log_format = '[{level}] {message}{line_break}';
// $logger->line_break = '^^';

// Date format can be any valid value for the PHP function [date()].
// Default is [\DateTime::ISO8601].
//
// $logger->date_format = 'Y-m-d H:i:s';

$logger->info('This is a Test.');
$logger->info('User {name} created', [ 'name' => 'Admin' ]);
$logger->error('Error Test');

// Test all varaible types that are handled
$data = array(
    'obj1' => new Test(),
    'obj2' => new Test2(),
    'time' => new \DateTime(),
    'n' => null,
    'b' => true,
    'i' => 123,
    'r' => fopen(__FILE__, 'r'),
);
$logger->info('{obj1} | {obj2} | {time} | {n} | {b} | {i} | {r}', $data);

if (TEST_HTML_LOGGER) {
    $app->get('/', function() use ($logger) {
        $html = '<html><body style="background-color:green; padding:0;"><div style="padding:20px;">';
        $html .= 'Class = ' . get_class($logger);
        $html .= '<br>Psr\Log\LoggerInterface = ' . json_encode($logger instanceof Psr\Log\LoggerInterface);
        $html .= '</body></html>';
        return $html;
    });
    $app->run();
} else {
    var_dump(get_class($logger));
    var_dump($logger instanceof Psr\Log\LoggerInterface);
    echo 'Data Logged';    
}

