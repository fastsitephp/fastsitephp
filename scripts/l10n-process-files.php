<?php
// This script creates the [Lang\Locales\*.json] files based on data downloaded from:
// https://github.com/unicode-cldr/cldr-dates-modern/
// https://github.com/unicode-cldr/cldr-numbers-modern/
// https://github.com/unicode-cldr/cldr-localenames-modern/
// http://cldr.unicode.org/    (Main Site)
//
// Each language has it's own file like this:
// https://github.com/unicode-cldr/cldr-dates-modern/blob/master/main/en-US-POSIX/ca-gregorian.json
//
// There is also an online viewer of detailed info which is helpful to work with:
// http://demo.icu-project.org/icu-bin/locexp?_=en_US
//
// The results of this file are compared using [l10n-date-time-compare.php]
// and [l10n-number-compare.php]
//
// Unicode CLDR patters are described here:
// http://cldr.unicode.org/translation/date-time-patterns
// http://cldr.unicode.org/translation/date-time
//
// PHP Date patterns are here:
// http://php.net/manual/en/function.date.php

// Location of Downloaded Files (Change as needed)
//
// $dir_dates = '/Users/conrad/Downloads/cldr-dates-modern-master';
// $dir_num = '/Users/conrad/Downloads/cldr-numbers-modern-master';
// $lang_file = '/Users/conrad/Downloads/cldr-localenames-modern-master/main/en-US-POSIX/languages.json';
//
$dir_dates = 'C:\Users\Administrator\Downloads\cldr-dates-modern-master';
$dir_num = 'C:\Users\Administrator\Downloads\cldr-numbers-modern-master';
$lang_file = 'C:\Users\Administrator\Downloads\cldr-localenames-modern-master\main\en-US-POSIX\languages.json';

// Location to Save
$save_folder = __DIR__ . '/../../vendor/fastsitephp/Lang/';

// Optionally save all settings in a [L10N.json] file.
// This file is not used but makes for easy searching from a Code Editor.
$save_all = false;

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// Create Objects
$search = new \FastSitePHP\FileSystem\Search();
$request = new \FastSitePHP\Web\Request();

// Search for Language Files
$files = $search
    ->dir($dir_dates)
    ->includeNames(array('ca-gregorian.json'))
    ->excludeRegExPaths(array('/root/'))
    ->recursive(true)
    ->files();

// print_r($files);
// exit();

// Language Descriptions
$all_langs = json_decode(file_get_contents($lang_file), true);
$all_langs = $all_langs['main']['en-US-POSIX']['localeDisplayNames']['languages'];
$included_langs = [];

// Data to Save
$all_data = [];
$echo_file_info = false;

// Process Files
foreach ($files as $file) {
    // Print File
    if ($echo_file_info) {
        echo $file;
        echo "\n";    
    }

    // Print file and get lang from path:
    //   /cldr-dates-modern-master/main/en/ca-generic.json
    // becomes:
    //   en
    $path = explode(DIRECTORY_SEPARATOR, $file);
    $lang = $path[count($path)-2];

    // Add to Language list if no locale is specified
    if (strpos($lang, '-') === false) {
        $included_langs[$lang] = $all_langs[$lang];
    }

    // Read file as JSON and get date formats if they exist.
    // Example Key:
    //   main.en-US-POSIX.dates.calendars.gregorian.dateFormats.short
    //
    // The differences is reading 'medium' vs 'short' formats is primarly based
    // on what a web browser would show for the same language.
    $json = json_decode(file_get_contents($file));
    $medium_dates = array('zh-Hans-SG', 'zh-Hans-HK', 'zh-Hans-MO', 'zh-Hant-MO');
    $type =  (in_array($lang, $medium_dates) ? 'medium' : 'short');
    $date = $request->value($json, ['main', $lang, 'dates', 'calendars', 'gregorian', 'dateFormats', $type]);
    $time = $request->value($json, ['main', $lang, 'dates', 'calendars', 'gregorian', 'timeFormats', 'medium']);
    $am = $request->value($json, ['main', $lang, 'dates', 'calendars', 'gregorian', 'dayPeriods', 'format', 'wide', 'am']);
    $pm = $request->value($json, ['main', $lang, 'dates', 'calendars', 'gregorian', 'dayPeriods', 'format', 'wide', 'pm']);
    $type = (strpos($lang, 'fr') === 0 ? 'medium' : 'short');
    $date_time = $request->value($json, ['main', $lang, 'dates', 'calendars', 'gregorian', 'dateTimeFormats', $type]);
    if ($date === null || $time === null || $date_time === null) {
        echo "Error with File: {$file}";
        exit();
    }

    // Read the Number File
    $path = "{$dir_num}/main/{$lang}/numbers.json";
    if (!is_file($path)) {
        echo 'Missing Number File: ' . $path;
        exit();
    }
    $json = json_decode(file_get_contents($path));
    $sys = $request->value($json, ['main', $lang, 'numbers', 'defaultNumberingSystem']);
    $decimal = $request->value($json, ['main', $lang, 'numbers', 'symbols-numberSystem-' . $sys, 'decimal']);
    $group = $request->value($json, ['main', $lang, 'numbers', 'symbols-numberSystem-' . $sys, 'group']);

    // Print Formats
    if ($echo_file_info) {
        echo "\t" . $date;
        echo "\t" . $time;
        echo "\t" . $date_time;
        echo "\t" . $decimal;
        echo "\t" . $group;
        echo "\n";
    }

    // Currently the following 3 locales are handled with Gregorian dates because 
    // supporting them takes a lot more work so it can be done in the future.
    // Web Browsers convert date/time to the local Calendar's for these 3 locales.
    //
    // [ar-SA] would need to use Hijri Calendar conversion functions
    // [fa] and [fa-AF] would need to use Jalaali Calendar conversion functions
    // 
    // https://en.wikipedia.org/wiki/Date_format_by_country
    //   Iran:
    //       Short format: yyyy/mm/dd in Persian Calendar system ("yy/m/d" is a common alternative).
    //       Gregorian dates follow the same rules in Persian literature but tend to be written in 
    //       the dd/mm/yyyy format in official English documents.
    //   Saudi Arabia:
    //       (dd/mm/yyyy in Islamic and Gregorian calendar systems, except for major companies, 
    //       which conventionally use the American mm/dd/yyyy format.
    //
    // Using Latin digits and format [dd/mm/yyyy] for these languages
    $langs = ['ar-SA', 'fa', 'fa-AF'];
    if (in_array($lang, $langs, true)) {
        $date = 'dd/MM/y';
        $am = 'AM';
        $pm = 'PM';
    }

    // Convert CLDR format to PHP Date format
    $lang = str_replace('-POSIX', '', $lang);
    $date = convert_cldr_to_php($lang, $date);
    $time = convert_cldr_to_php($lang, $time);
    $date_time = str_replace('{0}', $time, $date_time);
    $date_time = str_replace('{1}', $date, $date_time);
    $date_time = str_replace(':s', '', $date_time);
    $date_time = str_replace('.s', '', $date_time);
    $date_time = str_replace("'", '', $date_time);

    // Custom Rules after conversion
    if ($lang === 'fr-CA') {
        $date_time = str_replace(" \\m\\i\\n s \\s", '', $date_time);
    }

    // Add to object
    $fields = array(
        'date' => $date,
        'time' => $time,
        'dateTime' => $date_time,
    );
    if ($am !== 'AM' || $pm !== 'PM') {
        $fields['am'] = $am;
        $fields['pm'] = $pm;
    }
    // Uncomment next line for development if desired
    // $fields['numberingSystem'] = $sys;
    $fields['decimal'] = $decimal;
    $fields['group'] = $group;
    $all_data[$lang] = $fields;

    // Save file for the language
    $path = "{$save_folder}/Locales/{$lang}.json";
    $contents = json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($path, $contents);
}

echo '-------------------------';
echo "\n";
echo 'Files Created: ' . count($files);
echo "\n";

// Save Language File
$path = $save_folder . 'Languages.json';
$contents = json_encode($included_langs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($path, $contents);
echo 'Created Languages File: ' . $path;
echo "\n";

// Optionally save All Data file
if ($save_all) {
    $path = $save_folder . 'L10N.json';
    $contents = json_encode($all_data, JSON_PRETTY_PRINT);
    file_put_contents($path, $contents);
    echo 'Created All Data File: ' . $path;
    echo "\n";
}

// Convert from this format:
//   http://cldr.unicode.org/translation/date-time
// To this format:
//   http://php.net/manual/en/function.date.php
function convert_cldr_to_php($lang, $value) {
    // Manually Handle cetain items in [fr-CA]
    if ($lang === 'fr-CA') {
        $value = str_replace("'h'", 'T1', $value);
        $value = str_replace("'min'", 'T2', $value);
        $value = str_replace("'s'", 'T3', $value);
    }

    // Make sure no lang has single-digit minutes or seconds
    if (strpos($value, 'm') !== false && strpos($value, 'mm') === false) {
        echo 'Found invalid minute, check format';
        echo "\n";
        echo $value;
        exit();
    }
    $value = str_replace('MM', 'm', $value);
    $value = str_replace('M', 'n', $value);
    if (strpos($value, 'dd') !== false) {
        $value = str_replace('dd', 'd', $value);
    } else {
        $value = str_replace('d', 'j', $value);
    }
    if (strpos($value, 'yy') !== false) {
        $value = str_replace('yy', 'Y', $value);
    } else {
        // Always Use 4-Digit Years instead of 2-Digit
        // Browers handle date this way as well
        $value = str_replace('y', 'Y', $value);
    }
    if (strpos($value, 'hh') !== false) {
        $value = str_replace('hh', 'h', $value);
    } else {
        $value = str_replace('h', 'g', $value);
    }
    if (strpos($value, 'HH') !== false) {
        $value = str_replace('HH', 'H', $value);
    } else {
        $value = str_replace('H', 'G', $value);
    }
    // Make sure these two don't exit as they can't be handled in PHP
    //   K for 12-hour cycle using 0 through 11
    //   k for 24-hour cycle using 1 through 24
    if (strpos($value, 'K') !== false || strpos($value, 'k') !== false) {
        echo 'Found invalid K option, check format';
        exit();
    }
    if (strpos($value, 's') !== false && strpos($value, 'ss') === false) {
        echo 'Found invalid seconds, check format';
        exit();
    }
    $value = str_replace('mm', 'i', $value);
    $value = str_replace('ss', 's', $value);
    $value = str_replace('a', 'A', $value);
    // Make sure these two don't exit as they can't be handled in PHP
    //   b indicates am/noon/pm/midnight
    //   B indicates use of day period ranges such as “in the morning” or “in the evening”
    if (strpos($value, 'B') !== false || strpos($value, 'b') !== false) {
        if ($lang === 'my' && strpos($value, 'B') !== false) {
            $value = trim(str_replace('B', '', $value));
        } else {
            echo 'Found invalid B option, check format';
            exit();    
        }
    }

    if ($lang === 'fr-CA') {
        $value = str_replace('T1', '\\h', $value);
        $value = str_replace('T2', '\\m\\i\\n', $value);
        $value = str_replace('T3', '\\s', $value);
    }
    $value = str_replace("'", '', $value);

    return $value;
}