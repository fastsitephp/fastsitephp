<?php
// Test Script for manually testing the I18N class.
// In the future this code can be used as a starting point
// when creating the full unit tests. In the meantime this
// manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

$lang_dir = __DIR__ . '/i18n';
$app->config['I18N_DIR'] = $lang_dir;
$tests = array(
    array('jp'),
    array('jp', 'en'),
    array('es', 'en'),
    array('en', 'en'),
    array('en'),
    array('zh', 'es'),
    array('zh'),

    // Security Check
    array('../index.php'),
    array(0),

    // Config errors:
    array('en', null, null),
    array('en', null, array()),
    array('en', null, __DIR__ . DIRECTORY_SEPARATOR . 'missing'),
);

// Uncomment to test an optional 404 error
// $i18n = new \FastSitePHP\Lang\I18N($app, 'en');
// $i18n->send404OnError(true)->langFile('page')->langFile('missing');

header('Content-Type: text/plain');

foreach ($tests as $test) {
    try {
        echo str_repeat('=', 100);
        echo "\n";
        echo json_encode($test);
        echo "\n";
        echo "\n";
        
        if (count($test) === 3) {
            $app->config['I18N_DIR'] = $test[2];
        }

        if (count($test) === 2) {
            $app->config['I18N_FALLBACK_LANG'] = $test[1];
        } else {
            if (isset($app->config['I18N_FALLBACK_LANG'])) {
                unset($app->config['I18N_FALLBACK_LANG']);
            }
        }
        \FastSitePHP\Lang\I18N::langFile('page', $test[0]);

        // Uncomment to Test Error on all pages
        // $i18n->langFile('page2');
        print_r(\FastSitePHP\Lang\I18N::$loaded_files);
        echo "\n";
        print_r($app->locals['i18n']);        
        echo "\n";
        echo '$app->lang: ';
        print_r($app->lang);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    echo "\n\n";
    unset($app->locals['i18n']);
    $app->lang = null;
    \FastSitePHP\Lang\I18N::$loaded_files = array();
}

// Test [I18N::textFile()]
$lang_file = $lang_dir . '/test-file-{lang}.txt';
$app->config['I18N_FALLBACK_LANG'] = 'en';
echo str_repeat('=', 100);
echo "\n";
echo \FastSitePHP\Lang\I18N::textFile($lang_file, 'es');
echo "\n";
echo \FastSitePHP\Lang\I18N::textFile($lang_file, 'zh');
echo "\n";
try {
    unset($app->config['I18N_FALLBACK_LANG']);
    echo \FastSitePHP\Lang\I18N::textFile($lang_file, 'es');
} catch (\Exception $e) {
    echo $e->getMessage();
}
echo "\n";
try {
    echo \FastSitePHP\Lang\I18N::textFile($lang_file, './en');
} catch (\Exception $e) {
    echo $e->getMessage();
}
echo "\n";
print_r(\FastSitePHP\Lang\I18N::$opened_text_files);
