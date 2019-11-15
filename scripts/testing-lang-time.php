<?php
// Test Script for manually testing [Lang\Time] class.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

use FastSitePHP\Lang\Time;

echo Time::secondsToText(129680) . "\n";
echo Time::secondsToText(10) . "\n";
echo Time::secondsToText(0) . "\n";
echo Time::secondsToText(1) . "\n";
echo Time::secondsToText(59) . "\n";
echo Time::secondsToText(60) . "\n";
echo Time::secondsToText(119) . "\n";
echo Time::secondsToText(120) . "\n";
echo Time::secondsToText(60 * 60) . "\n";
echo Time::secondsToText(60 * 60 * 24) . "\n";
echo Time::secondsToText((60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20) . "\n";
echo Time::secondsToText(31536000 + (60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20) . "\n";
echo Time::secondsToText((31536000*2) + (60 * 60 * 24 * 2) + (60 * 60 * 12) + (60 * 59) + 20) . "\n";
