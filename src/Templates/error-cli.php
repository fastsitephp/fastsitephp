<?php

/**
 * @var string $page_title
 * @var string $message
 * @var \ErrorException $e
 */

echo str_repeat('=', 80) . PHP_EOL;
echo 'ERROR' . PHP_EOL;
echo str_repeat('=', 80) . PHP_EOL;
echo 'Type: ' . get_class($e) . PHP_EOL;
echo 'Code: ' . $e->getCode() . PHP_EOL;
if (get_class($e) === 'ErrorException') {
    echo 'Severity: ' . $e->getSeverity() . (isset($e->severityText) ? ' (' . $e->severityText . ')' : '') . PHP_EOL;
}
echo 'Message: ' . $e->getMessage() . PHP_EOL;
echo 'File: ' . $e->getFile() . PHP_EOL;
echo 'Line: ' . $e->getLine() . PHP_EOL;
echo 'Time: ' . date(DATE_RFC2822) . PHP_EOL;

echo str_repeat('=', 80) . PHP_EOL;
echo 'Stack Trace' . PHP_EOL;
$index = 0;
foreach ($e->getTrace() as $trace) {
    $index++;
    echo str_repeat('-', 80) . PHP_EOL;
    echo 'Index: ' . $index . PHP_EOL;
    echo 'Function: ' . $trace['function'] . PHP_EOL;
    echo 'File: ' . (isset($trace['file']) ? $trace['file'] : '') . PHP_EOL;
    echo 'Line: ' . (isset($trace['line']) ? $trace['line'] : 0) . PHP_EOL;
}
echo str_repeat('=', 80) . PHP_EOL;
