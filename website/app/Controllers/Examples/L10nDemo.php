<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Data\Validator;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Lang\L10N;
use FastSitePHP\Web\Request;

class L10nDemo
{
    public function route(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('l10n-demo', $lang);

        // Default to Current Time (UTC) and Number below
        $date = time();
        $number = 123456789.1234;

        // If user submits and form is valid then use posted values
        if ($_POST) {
            $v = new Validator();
            $v->addRules([
                ['datetime-local', null, 'required type=datetime-local'],
                ['number', null, 'required type=number'],
            ]);
            list($errors, $fields) = $v->validate($_POST);
            if (!$errors) {
                $date = $_POST['datetime-local'];
                $number = $_POST['number'];
            }
        }

        // Determine number of decimal places to display (based on the number submitted)
        $pos = strpos($number, '.');
        $decimals = ($pos === false ? 0 : strlen(substr($number, $pos+1)));

        // Get all supported locales (languages)
        $l10n = new L10N();
        $all_langs = $l10n->supportedLocales();
        $records = [];
        
        // Build array of number/date/time/etc using the format of each locale
        foreach ($all_langs as $lang) {
            // Set the current language/locale
            $l10n->locale($lang);
            // Add formatted number, date, time values to the array
            $records[] = [
                $lang,
                $l10n->formatNumber($number, $decimals),
                $l10n->formatDateTime($date),
                $l10n->formatDate($date),
                $l10n->formatTime($date),
            ];
        }

        // If date is a Unix Timestamp convert to a string
        // so it can be displayed on the HTML <input>.
        if (is_int($date)) {
            $date = date('Y-m-d\TH:i:s', $date);
        }

        // Add Code Example from text file
        $file_path = $app->config['I18N_DIR'] . '/code/l10n-demo.{lang}.txt';
        $code = I18N::textFile($file_path, $app->lang);

        // Update Code Sample to show the User's Default Language
        $req = new Request();
        $user_lang = $req->acceptLanguage();
        $lang = 'en-US';
        if ($user_lang) {
            $user_lang = $user_lang[0]['value'];
            if (isset($all_langs[$user_lang])) {
                $lang = $user_lang;
            }
        }
        $code = str_replace('@lang', $lang, $code);

        // Render the View
        return $app->render('examples/l10n-demo.php', [
            'nav_active_link' => 'examples',
            'records' => $records,
            'date' => $date,
            'number' => $number,
            'code' => $code,
        ]);
    }
}