<?php

// This script can be modified and used to see how much memory is used
// when a class file or files are loaded. The script time will change
// every time the page is refreshed however memory usage remains constant.
//
// Something to be aware of is that PHP DocBlock Comments increase the amount of
// memory used, often by 25% or more. However even though memory is increased it 
// generally does not impact on page speed. This feature of PHP can be turned off
// by setting the following setting in the [php.ini] file in recent versions of PHP:
//     opcache.save_comments = false;

// Show Errors in case files are not loaded
error_reporting(-1);
ini_set('display_errors', 'on');

// Start Stat/Memory/Time
require __DIR__ . '/../src/Utilities/debug.php';

// Setup a PHP Autoloader
require __DIR__ . '/../autoload.php';

// Create Objects
$app = new \FastSitePHP\Application();
// $app = new \FastSitePHP\AppMin();
// $req = new \FastSitePHP\Web\Request();
// $res = new \FastSitePHP\Web\Response();
// $crypto = new \FastSitePHP\Security\Crypto\Encryption();
// $csd = new \FastSitePHP\Security\Crypto\SignedData();
// $l10n = new \FastSitePHP\Lang\L10N();
// $l10n->locale('en-US');
// $l10n->locale('fr-FR');

// Show Loaded Objects
echo '<div style="text-align:center; margin-top:20px;"><div style="display:inline-block; margin:auto; text-align:left;">';
echo '<b>Loaded Classes</b><br>';
$all_classes = array_values(get_declared_classes());
$script_classes = array();
foreach ($all_classes as $class) {
    if (strpos($class, 'FastSitePHP') === 0) {
        echo $class . '<br>';
    }
}
echo '</div></div>';

// Show Memory Info
$showDebugInfo();
