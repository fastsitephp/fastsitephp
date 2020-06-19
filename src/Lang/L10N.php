<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Lang;

use FastSitePHP\FileSystem\Search;
use FastSitePHP\FileSystem\Security;

/**
 * Localization (l10n) API
 *
 * Contains functions for formatting dates, times, and numbers.
 *
 * [l10n] is spelled "Localisation" in British English. [l10n] is an
 * acronym/numeronym that represents ("l" + 10 characters + "n"). The difference is
 * US English uses "z" while British English uses an "s" in the spelling of the word.
 *
 * @link http://cldr.unicode.org/
 * @link https://en.wikipedia.org/wiki/Languages_used_on_the_Internet
 * @link https://en.wikipedia.org/wiki/List_of_countries_by_number_of_Internet_users
 * @link http://php.net/manual/en/function.date.php
 * @link https://github.com/unicode-cldr/cldr-dates-modern/
 * @link https://github.com/unicode-cldr/cldr-numbers-modern/
 * @link https://github.com/unicode-cldr/cldr-localenames-modern/
 */
class L10N
{
    // Default format if locale is not set
    private $locale = null;
    private $timezone = null;
    private $format_date = 'Y-m-d'; // Short Date
    private $format_time = 'H:i:s'; // Medium Time (with Seconds)
    private $format_date_time = 'Y-m-d H:i'; // Date-Time = Short Date and Short Time (no Seconds)
    private $decimal_point = '.';
    private $digit_group = ',';
    private $am = null;
    private $pm = null;
    private $digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    /**
     * Class Constructor
     * Settings can optionally be set when the class is first created.
     *
     * Example:
     *   $l10n = new \FastSitePHP\Lang\L10N('en-US', 'America/Los_Angeles');
     *
     * @param string|null $locale
     * @param string|null $timezone
     */
    function __construct($locale = null, $timezone = null)
    {
        if ($locale !== null) {
            $this->locale($locale);
        }
        if ($timezone !== null) {
            $this->timezone($timezone);
        }
    }

    /**
     * Return a list of supported locales. Each locale has a JSON file in the [Locales] sub-folder.
     *
     * Territories (Countries) are not always included when the language has a primary Country.
     * For example French ‘fr’ and German ‘de’ do not have locales for ‘fr-FR’ and ‘de-DE’
     * however setting [locale()] with either value will match to the correct language.
     *
     * @return array
     */
    function supportedLocales()
    {
        $search = new Search();

        $langs = $search
            ->dir(__DIR__ . '/Locales')
            ->fileTypes(array('json'))
            ->hideExtensions(true)
            ->files();

        sort($langs);
        return $langs;
    }

    /**
     * Return an associative array of Supported Languages. The key will contain the
     * language abbreviation and the value will contain the language in English.
     *
     * @return array
     */
    function supportedLanguages()
    {
        $all_langs = json_decode(file_get_contents(__DIR__ . '/Languages.json'), true);
        $locales = $this->supportedLocales();
        $langs = array();
        foreach ($all_langs as $abbr => $lang) {
            if (in_array($abbr, $locales, true)) {
                $langs[$abbr] = $lang;
            }
        }
        return $langs;
    }

    /**
     * Get or set the locale to be used when formatting dates, times, and numbers.
     * When setting the locale this function is chainable and returns the
     * L10N object instance.
     *
     * Examples:
     *   Get:
     *     $current_locale = $l10n->locale();
     *   Set:
     *     $l10n->locale('zh');
     *     $l10n->locale('fr-FR');
     *     $l10n->locale('en-US');
     *
     * @param string|null $locale
     * @return $this|null|string
     * @throws \Exception
     */
    public function locale($locale = null)
    {
        // Return current language
        if ($locale === null) {
            return $this->locale;
        }

        // [$locale] can come from User Input and is used to read files
        // so it's important to be validated to prevent Path Traversal Attacks.
        // First check if it is a string and does not contain a space.
        // Later [FastSitePHP\FileSystem\Security::dirContainsFile()]
        // is used to check additional characters and the file name.
        if (is_string($locale) === false || strpos($locale, ' ') !== false) {
            if (is_string($locale)) {
                $error = 'Unsupported Language format for [%s->%s()]. Use format of [lang] = [en] or [lang-country] = [en-US].';
                $error = sprintf($error, __CLASS__, __FUNCTION__);
                throw new \Exception($error);
            } else {
                // This one is likely a developer error
                $error = 'Invalid parameter for [$locale], expected a [string] but recevied a [%s].';
                $error = sprintf($error, gettype($locale));
                throw new \Exception($error);
            }
        }

        // Locale definitions are defined in JSON files which are created
        // from the script [docs/scripts/l10n-process-files.php].

        // First search for an exact file name (example: 'en-US') and
        // if not found then search for a language file (example: 'fr').
        $dir = __DIR__ . '/Locales/';
        $file = $locale . '.json';
        if (!Security::dirContainsFile($dir, $file)) {
            $lang = explode('-', $locale);
            if (count($lang) === 0) {
                throw new \Exception('Unsupported Locale: ' . $locale);
            }
            $file = $lang[0] . '.json';
            if (!Security::dirContainsFile($dir, $file)) {
                throw new \Exception('Unsupported Language: ' . $lang[0]);
            }
            $locale = $lang[0];
        }
        $file_path = $dir . $file;

        // Set format from the JSON file
        $json = json_decode(file_get_contents($file_path), true);
        $this->format_date = $json['date'];
        $this->format_time = $json['time'];
        $this->format_date_time = $json['dateTime'];
        $this->am = (isset($json['am']) ? $json['am'] : null);
        $this->pm = (isset($json['pm']) ? $json['pm'] : null);
        $this->decimal_point = $json['decimal'];
        $this->digit_group = $json['group'];

        // Set lang and return this
        $this->locale = $locale;
        return $this;
    }

    /**
     * Return an array of timezones that can be set from the function [timezone()].
     * This function simply returns the results of the native PHP function
     * [\DateTimeZone::listIdentifiers()].
     *
     * @return array
     */
    function supportedTimezones()
    {
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * Get or set the timezone to be used when formatting dates and times.
     * When setting the timezone this function is chainable and returns this
     * L10N object instance.
     *
     * Examples:
     *   $time = '2030-01-01 00:00:00';
     *   $l10n->timezone($timezone)->formatDateTime($time);
     *
     *   time = $timezone:
     *   '2030-01-01 00:00' = 'UTC'
     *   '2030-01-01 09:00' = 'Asia/Tokyo'
     *   '2029-12-31 16:00' = 'America/Los_Angeles'
     *
     * @link http://php.net/manual/en/timezones.php
     * @param string|null $timezone
     * @return $this|null|string
     * @throws \Exception
     */
    public function timezone($timezone = null)
    {
        // Get
        if ($timezone === null) {
            return $this->timezone;
        }

        // Set with Validation
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            if (is_string($timezone)) {
                $error = 'Timezone [%s] is not supported. To see a list of valid timezones call [%s->supportedTimezones()] or the PHP function [\DateTimeZone::listIdentifiers()].';
                $error = sprintf($error, $timezone, __CLASS__);
            } else {
                $error = 'Expected a [string] when setting the timezone but received a [%s].';
                $error = sprintf($error, gettype($timezone));
            }
            throw new \Exception($error);
        }
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Format a date and time string based on the selected locale.
     * Seconds are not included in the result.
     *
     * Example:
     *   $l10n->timezone('UTC')->formatDateTime('2030-02-01 13:00:30');
     *
     * Returns:
     *   'ko-KR' : 2030. 2. 1. 오후 1:00
     *   'bn-BD' : ১/২/২০৩০ ১:০০ PM
     *   'en-US' : 2/1/2030, 1:00 PM
     *   'de-CH' : 01.02.2030, 13:00
     *
     * To return formatted current date and time:
     *   $l10n->formatDateTime(time());
     *
     * If an invalid time is passed and timezone is set then this function
     * will return [null]; otherwise if timezone is not set then the initial
     * value for Unix Timestamp (00:00:00 on 1 January 1970) will be returned.
     *
     * When using Language 'fa' (Farsi/Persian) or locale 'ar-SA' (Arabic - Saudi Arabia)
     * dates are currently returned with Latin Digits using the Gregorian Calendar
     * instead of Jalaali Calendar for 'fa' and Hijri Calendar for 'ar-SA'.
     * If you need either of these calendars on a web page an option is to use
     * the browser built-in object [Intl.DateTimeFormat] from JavaScript.
     *
     * @param int|string $date_time - Unix Timestamp (int) or string in format of 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD'
     * @return string|null
     */
    public function formatDateTime($date_time)
    {
        return $this->getFormattedDate($date_time, $this->format_date_time);
    }

    /**
     * Format a date string (excluding time) based on the selected locale.
     *
     * Example:
     *   $l10n->timezone('UTC')->formatDate('2030-02-01 13:00:30');
     *
     * Returns:
     *   'ko-KR' : 2030. 2. 1.
     *   'bn-BD' : ১/২/২০৩০
     *   'en-US' : 2/1/2030
     *   'de-CH' : 01.02.2030
     *
     * To return formatted current date:
     *   $l10n->formatDate(time());
     *
     * See additional notes in [formatDateTime()].
     *
     * @param int|string $date - Unix Timestamp (int) or string in format of 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD'
     * @return string|null
     */
    public function formatDate($date)
    {
        return $this->getFormattedDate($date, $this->format_date);
    }

    /**
     * Format a time string (excluding date) based on the selected locale.
     * Hours, minutes, and seconds are included in the result.
     *
     * Example:
     *   $l10n->timezone('UTC')->formatTime('2030-02-01 13:00:30');
     *
     * Returns:
     *   'ko-KR' : 오후 1:00:30
     *   'bn-BD' : ১:০০:৩০ PM
     *   'en-US' : 1:00:30 PM
     *   'de-CH' : 13:00:30
     *
     * To return formatted current time:
     *   $l10n->formatTime(time());
     *
     * See additional notes in [formatDateTime()].
     *
     * @param int|string $time - Unix Timestamp (int) or string in format of 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD'
     * @return string|null
     */
    public function formatTime($time)
    {
        return $this->getFormattedDate($time, $this->format_time);
    }

    /**
     * Format a number based on the selected locale.
     * Defaults to zero decimal places.
     *
     * Example:
     *   $l10n->formatNumber(1234567890.12345, 5)
     *
     * Returns:
     *   'en-US' :  1,234,567,890.12345
     *   'en-IN' : 1,23,45,67,890.12345
     *   'fr'    :  1 234 567 890,12345
     *   'ar'    :  ١٬٢٣٤٬٥٦٧٬٨٩٠٫١٢٣٤٥
     *
     * @param string|int|float $number
     * @param int $decimals
     * @return string
     */
    public function formatNumber($number, $decimals = 0)
    {
        // Format based on selected locale
        $number = number_format($number, $decimals, $this->decimal_point, $this->digit_group);

        // Handle Custom Digit Grouping
        // [en-IN] digit grouping looks like this '1,23,45,67,890.12345'
        if ($this->locale === 'en-IN') {
            $groups = explode(',', $number);
            if (count($groups) > 1) {
                $is_negative = (strpos($number, '-') === 0);
                $number = str_replace(',', '', ltrim($number, '-'));

                $groups = explode('.', $number);
                $whole_number = strrev($groups[0]);

                $number = '';
                for ($n = 0, $m = strlen($whole_number); $n < $m; $n++) {
                    if ($n === 3 || ($n > 3 && ($n-1) % 2 === 0)) {
                        $number .= ',';
                    }
                    $number .= $whole_number[$n];
                }

                $number = ($is_negative ? '-' : '') . ltrim(strrev($number), ',');
                if (count($groups) === 2) {
                    $number .= '.' . $groups[1];
                }
            }
            return $number;
        }

        // Translate Latin Digits to Locale if needed
        return $this->translateDigits($number);
    }

    /**
     * Called by other functions for formatting as Date, Date/Time, Time.
     * See comments in the public functions that call this.
     *
     * @param mixed $date_time_value
     * @param mixed $format
     * @return string|null
     */
    private function getFormattedDate($date_time_value, $format)
    {
        // Use PHP DateTime object when using Timezone
        if ($this->timezone !== null) {
            // Create DateTime object. [is_int()] will return true in cases where this function is
            // called with a number of seconds, for example using the time() function. Otherwise
            // this function expects the date in a format of [YYYY-MM-DD HH:MM:SS] or [YYYY-MM-DD].
            if (is_int($date_time_value) === true) {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $date_time_value));
            } else {
                // Get format to read from, then parse
                $date_format = (strpos($date_time_value, ':') !== false ? 'Y-m-d H:i:s' : 'Y-m-d');
                $date = \DateTime::createFromFormat($date_format, $date_time_value);
            }

            // If error return null otherwise set timezone and return formatted date
            $date_errors = \DateTime::getLastErrors();
            if ($date === false || ($date_errors['warning_count'] + $date_errors['error_count'] > 0)) {
                return null;
            } else {
                $date->setTimezone(new \DateTimeZone($this->timezone));
                $date_value = $date->format($format);
            }
        } else {
            // Use Standard PHP date function when timezone is not specified
            if (is_int($date_time_value) === true) {
                $date_value = date($format, $date_time_value);
            } else {
                $date_value = date($format, strtotime($date_time_value));
            }
        }

        // Handle translations for AM/PM
        if ($this->am !== null && $this->pm !== null) {
            $date_value = str_replace('AM', $this->am, $date_value);
            $date_value = str_replace('PM', $this->pm, $date_value);
        }

        // To correctly format locales for 'fa' use of Jalaali Calendar is needed
        // and 'ar-SA' requires Hijri Calendar conversion. This is not yet supported.
        // For more see related notes in file [l10n-process-files.php].
        if (strpos($this->locale, 'fa') === 0 || $this->locale === 'ar-SA') {
            return $date_value;
        }

        return $this->translateDigits($date_value);
    }

    /**
     * Handle languages that use different digits
     *
     * @param string $value
     * @return string
     */
    private function translateDigits($value)
    {
        if (strpos($this->locale, 'ar') === 0) {
            $skip_list = array('ar-DZ', 'ar-EH', 'ar-LY', 'ar-MA', 'ar-TN');
            if (!in_array($this->locale, $skip_list)) {
                // NOTE - this specific array will likely appear in reverse order
                // on your code editor or browser:
                $replace = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
                $value = str_replace($this->digits, $replace, $value);
            }
        } elseif (strpos($this->locale, 'bn') === 0) {
            $replace = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
            $value = str_replace($this->digits, $replace, $value);
        } elseif (strpos($this->locale, 'fa') === 0) {
            $replace = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
            $value = str_replace($this->digits, $replace, $value);
        } elseif (strpos($this->locale, 'mr') === 0) {
            $replace = array('०', '१', '२', '३', '४', '५', '६', '७', '८', '९');
            $value = str_replace($this->digits, $replace, $value);
        }
        return $value;
    }
}
