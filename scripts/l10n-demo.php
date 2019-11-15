<?php
// Test Script for manually testing L10N class.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this and 
// other files manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

$l10n = new \FastSitePHP\Lang\L10N();
$now = time();
$num = 123456.789;

// Uncomment to test errors
// $l10n->locale(0);
// $l10n->locale('en en');
// $l10n->locale('./en');

// Uncomment to test [supported*()] functions 
// header('Content-Type: text/plain');
// print_r($l10n->supportedLocales());
// print_r($l10n->supportedLanguages());
// print_r($l10n->supportedTimezones());
// exit();

printLocaleInfo($l10n, $now, $num, null);
$l10n->locale('en-US')->timezone('America/Los_Angeles');
printLocaleInfo($l10n, $now, $num, 3);
$l10n->timezone('UTC')->locale('fr-FR');
printLocaleInfo($l10n, $now, $num, 5);

$l10n = new \FastSitePHP\Lang\L10N('ar', 'Asia/Baghdad');
printLocaleInfo($l10n, $now, $num, 5);

$nums = array(
    -987654321,
    -87654321, 
    -7654321,    
    -654321,
    -54321,
    -4321,
    -321,
    -21,
    0,
    76,
    765,
    7654,
    76543,
    765432,
    7654321,
    87654321,
    987654321,
);
echo '<b>Locale: </b>' . $l10n->locale('en-IN')->locale();
echo '<br>';
echo '<div style="text-align:right; width:100px;">';
foreach ($nums as $num) {
    echo $l10n->formatNumber($num);
    echo '<br>';
}
echo '</div>';

function printLocaleInfo($l10n, $time, $number, $decimals)
{
    echo '<b>Locale: </b>' . $l10n->locale();
    echo '<br>';
    echo '<b>Timezone: </b>' . $l10n->timezone();
    echo '<br>';
    echo $l10n->formatDate($time);
    echo '<br>';
    echo $l10n->formatTime($time);
    echo '<br>';
    echo $l10n->formatDateTime($time);
    echo '<br>';
    echo $l10n->formatNumber($number, $decimals);
    echo '<br>';
    echo '<br>';
}