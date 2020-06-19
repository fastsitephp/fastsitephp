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

use FastSitePHP\Application;
use FastSitePHP\FileSystem\Security;
use FastSitePHP\Web\Request;

/**
 * Internationalization (I18N) API
 *
 * This class provides an easy to use API for sites and apps that need to
 * support multiple languages.
 *
 * [i18n] is spelled "Internationalisation" in British English. [i18n] is an
 * acronym/numeronym that represents ("i" + 18 characters + "n"). The difference is
 * US English uses "z" while British English uses an "s" in the spelling of the word.
 *
 * Using this class requires a global [\FastSitePHP\Application] object to be
 * assigned to the variable [$app].
 *
 * @link https://en.wikipedia.org/wiki/Internationalization_and_localization
 * @link https://www.w3.org/International/questions/qa-i18n
 */
class I18N
{
    /**
     * Array of file paths loaded when calling [langFile()] in the
     * order that they were loaded. This property is primarily used
     * for Unit Testing however it can also be useful to help so
     * unexpected translations in case multiple files are loaded.
     *
     * @var array
     */
    public static $loaded_files = array();

    /**
     * Array of file paths opened when calling [textFile()] in the
     * order that they were loaded. Just like [$loaded_files] this
     * property is primarily used for Unit Testing.
     *
     * @var array
     */
    public static $opened_text_files = array();

    /**
     * The default behavior if using a fallback language and the language
     * is not matched is to send a 404 response when calling [langFile()].
     *
     * If this is set to [true] then the user will be redirected to the same page
     * using the fallback language. For this feature to work the requested URL
     * must have the language parameter after the host
     * (example: "https://www.example.com/{lang}/{pages}").
     *
     * @var bool
     */
    public static $redirect_on_missing_lang = false;

    /**
     * Private variables used for logic
     */
    private static $lang_matched = false;

    /**
     * This function read JSON files from a directory specified in the config setting
     * ($app->config['I18N_DIR']) and then loaded translations are set to the app
     * property ($app->locals['i18n']) so that they can be used with template rendering
     * and from the calling page. When the language is verified as valid it is set
     * to the app property ($app->lang).
     *
     * All JSON files need to be in the same directory and have a format of
     * [{name}.{lang}.json]. An optional main file named [_.{lang}.json] if
     * found will first be loaded when this function is called.
     *
     * A fallback language can be specified so that missing translations default
     * to another language. This allows partially translated sites to use this API.
     * Fallback language is set as config setting ($app->config['I18N_FALLBACK_LANG']).
     *
     * If a fallback language is defined and the language specified is not matched
     * and the requested url has a format of [https://www.example.com/{lang}/{pages}]
     * then this function will redirect to the fallback language and end PHP processing.
     *
     * The file specified as a parameter to this function (or optional fallback)
     * is required to exist; if not an exception is thrown. This paramater is not
     * intended to be a based on user input however the generated file name is
     * validated for security in case an app sets the value based on user input.
     *
     * Example Files:
     *     _.en.json
     *     _.es.json
     *     header.en.json
     *     header.es.json
     *     about.en.json
     *
     * Example Code:
     *     // Assuming the files above exist they would be loaded
     *     // in the order shown above based on this code.
     *     $app->config['I18N_DIR'] = __DIR__ . '/i18n';
     *     $app->config['I18N_FALLBACK_LANG'] = 'en';
     *     I18N::langFile('header', 'es');
     *     I18N::langFile('about', 'es');
     *
     *     // Typical usage is allow for an app to load a language
     *     // file based on the Requested URL:
     *     $app->get('/:lang/about', function($lang) {
     *         I18N::langFile('about', $lang);
     *
     * @param string $file_name - Name of the file, example 'about' which returns 'about.fr.json' if language [fr] is selected
     * @param string $lang - User's Selected Language
     * @return void
     * @throws \Exception
     */
    public static function langFile($file_name, $lang)
    {
        // Use the Global Application Object
        global $app;

        // Get fallback language if defined and Validate
        $dir = self::validateDir($app);
        self::validateLang($lang);
        $fallback_lang = (isset($app->config['I18N_FALLBACK_LANG']) ? $app->config['I18N_FALLBACK_LANG'] : null);
        $main_file_loaded = false;
        $path = null;

        // Each time this function is called translations are saved to
        // the locals variable [i18n] so get the existing values.
        $i18n = (isset($app->locals['i18n']) ? (array)$app->locals['i18n'] : array());

        // The first time this function is called load the optional main
        // language file that is shared by all pages (format: '_.{lang}.json').
        if (count($i18n) === 0) {
            // First load fallback language
            if ($fallback_lang !== null && $lang !== $fallback_lang) {
                $name = '_.' . $fallback_lang . '.json';
                $path = $dir . $name;
                if (Security::dirContainsFile($dir, $name)) {
                    $json = file_get_contents($path);
                    $i18n = json_decode($json, true);
                    self::$loaded_files[] = $path;
                    $main_file_loaded = true;
                }
            }

            // Then load the main language which will overwrite any of
            // the same values that exist in the fallback language.
            $name = '_.' . $lang . '.json';
            $path = $dir . $name;
            if (Security::dirContainsFile($dir, $name)) {
                $json = file_get_contents($path);
                $i18n = array_merge($i18n, json_decode($json, true));
                self::$loaded_files[] = $path;
                self::$lang_matched = true;
                $main_file_loaded = true;
            }
        }

        // After the main file load the file specified as the parameter of this function.
        $fallback_path = null;
        $was_found = false;

        if ($file_name === '_') {
            $was_found = $main_file_loaded;
        } else {
            // First load fallback language
            if ($fallback_lang !== null && $lang !== $fallback_lang) {
                $name = $file_name . '.' . $fallback_lang . '.json';
                if (Security::dirContainsFile($dir, $name)) {
                    $fallback_path = $dir . $name;
                    $json = file_get_contents($fallback_path);
                    $i18n = array_merge($i18n, json_decode($json, true));
                    self::$loaded_files[] = $fallback_path;
                    $was_found = true;
                }
            }

            // Then load the main language
            $name = $file_name . '.' . $lang . '.json';
            $path = $dir . $name;
            if (Security::dirContainsFile($dir, $name)) {
                $json = file_get_contents($path);
                $json = json_decode($json, true);
                if ($json === null) {
                    throw new \Exception('Invalid JSON File: ' . $path);
                }
                $i18n = array_merge($i18n, $json);
                self::$loaded_files[] = $path;
                $was_found = true;
                self::$lang_matched = true;
            }
        }

        // Language not matched, send a 404 error (default) or optionally redirect to fallback language
        // Fallback redirect requires the site to be setup like this: "https://www.example.com/{lang}/{pages}".
        if (!self::$lang_matched) {
            // Send a 404 Response and terminate the response (default)
            if (!self::$redirect_on_missing_lang) {
                $app->sendPageNotFound();
            // Or Redirect (optional)
            } else if ($fallback_lang !== null && strtolower($fallback_lang) !== strtolower($lang)) {
                // Build URL with the Fallback Language
                $url = $app->requestedPath();
                if (strpos($url, '/' . $lang . '/') === 0) {
                    $url = $app->rootDir() . $fallback_lang . '/' . substr($url, strlen($lang) + 2);
                } elseif ($url === '/' . $lang) {
                    $url = $app->rootDir() . $fallback_lang;
                }
                if ($url !== '' && $url !== null) {
                    $app->redirect($url);
                }
            }
        }

        // The specified file is required (either fallback or main langauge).
        // If not found then the developer of the site did not call this
        // function correctly or fallback language is not set.
        if (!$was_found) {
            $error = 'Missing language file [%s] for the page [%s]. File path: [%s]';
            $error = sprintf($error, $lang, $file_name, $path);
            if ($fallback_path !== null) {
                $error .= sprintf(' and [%s]', $fallback_path);
            }
            throw new \Exception($error);
        }

        // Assign selected language and all values back to the app
        $app->lang = $lang;
        $app->locals['i18n'] = $i18n;
    }

    /**
     * Return the contents of a file based on the User's selected language.
     *
     * Just like the function [langFile()] ths function uses a fallback language from
     * the Application config settings to handle partially translated sites.
     *
     * [$file_path] is a full file path requires the text '{lang}' anywhere in the file
     * path. The '{lang}' value gets replaced wiht the user's selected language. This
     * paramater is intended to be hard-coded by the app and users should not
     * have the ability to input their own file paths as it would be a security risk.
     *
     * The file is required to exist (either selected language or fallback language)
     * otherwise an exception is thrown.
     *
     * Example Code:
     *     // Config Option
     *     $app->config['I18N_FALLBACK_LANG'] = 'en';
     *
     *     // Typical usage is allow for an app to load file content
     *     // based on the the User's Selected Language:
     *     $app->get('/:lang/sample-code', function($lang) {
     *         $file_path = __DIR__ . '/../app_data/files/sample-code-{lang}.txt'
     *         return I18N::textFile($file_path, $lang);
     *
     * @param string $file_path - Full file path containing '{lang}' in the path
     * @param string $lang - User's Selected language
     * @return string
     * @throws \Exception
     */
    public static function textFile($file_path, $lang)
    {
        // Use the Global Application Object
        global $app;

        // Get Fallback Language if one is defined
        self::validateLang($lang);
        $fallback_lang = (isset($app->config['I18N_FALLBACK_LANG']) ? $app->config['I18N_FALLBACK_LANG'] : null);

        // The file path is required to have '{lang}' in it since it
        // gets replaced with the language.
        if (strpos($file_path, '{lang}') === false) {
            if (is_string($file_path)) {
                $error = 'Invalid Parameter to [%s::%s()]. [$file_path] must be a [string] but was instead a [%s].';
                $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($file_path));
            } else {
                $error = 'Invalid Parameter to [%s::%s()]. [$file_path] must contain the text \'{lang}\'. Value passed to function [%s].';
                $error = sprintf($error, __CLASS__, __FUNCTION__, $file_path);
            }
            throw new \Exception($error);
        }

        // Build File List
        // First look for the specified language and if no file exists
        // check for one using the fallback language.
        $lang_file = str_replace('{lang}', $lang, $file_path);
        $lang_files = array($lang_file);
        if ($fallback_lang !== null && $lang !== $fallback_lang) {
            $lang_files[] = str_replace('{lang}', $fallback_lang, $file_path);
        }

        // Open and return text contents of the first matching file
        foreach ($lang_files as $lang_file) {
            if (is_file($lang_file)) {
                self::$opened_text_files[] = $lang_file;
                return file_get_contents($lang_file);
            }
        }

        // A file must exist when calling this function otherwise it's considered a developer error.
        if (count($lang_files) === 2) {
            $error = 'Language text file not found at the following locations: [%s] and [%s]';
            $error = sprintf($error, $lang_files[0], $lang_files[1]);
        } else {
            $error = 'Language text file not found at: [%s]';
            $error = sprintf($error, $lang_files[0]);
        }
        throw new \Exception($error);
    }

    /**
     * Return the default language for the user based on the 'Accept-Language'
     * request header and available languages for the site.
     *
     * This is useful to provide custom content for the user or to redirect
     * to the user's language when they access the default URL.
     *
     * Requires config values:
     *     $app->config['I18N_DIR']
     *     $app->config['I18N_FALLBACK_LANG']
     *
     * Example usage:
     *     $app->redirect($app->rootUrl() . I18N::getUserDefaultLang() . '/');
     *
     * @return string
     */
    public static function getUserDefaultLang()
    {
        // Use the Global Application Object
        global $app;

        // Fallback language is required
        if (!isset($app->config['I18N_FALLBACK_LANG'])) {
            throw new \Exception('The app config value [I18N_FALLBACK_LANG] needs to be set before calling this function.');
        }

        // Get and Validate the i18n dir
        $dir = self::validateDir($app);

        // Read the 'Accept-Language' as an array and check each langauge.
        // validateLang() is also called in case a malicious user attempts to
        // attack a site from the 'Accept-Language' request header.
        $req = new Request();
        $langs = $req->acceptLanguage();
        $matched_lang = null;
        foreach ($langs as $lang) {
            $iso = $lang['value'];
            self::validateLang($iso);
            $name = '_.' . $iso . '.json';
            if (Security::dirContainsFile($dir, $name)) {
                $matched_lang = $lang['value'];
                break;
            }
        }

        // Return matched language or fallback
        return ($matched_lang ? $matched_lang : $app->config['I18N_FALLBACK_LANG']);
    }

    /**
     * Return true if the language is supported by the site. For a language to be
     * supported it must include a '_.{lang}.json' file in the [I18N_DIR] directory.
     *
     * Requires config value:
     *     $app->config['I18N_DIR']
     *
     * @param string $lang
     * @return bool
     */
    public static function hasLang($lang)
    {
        // Use the Global Application Object
        global $app;

        // Get and Validate the i18n dir
        $dir = self::validateDir($app);

        // Return true only if the file exists
        self::validateLang($lang);
        $name = '_.' . $lang . '.json';
        return Security::dirContainsFile($dir, $name);
    }

    /**
     * Static function that can be called for each request to make sure
     * that a language file is always loaded for template rendering when
     * [$app->render()] is called.
     *
     * This is useful if your site uses PHP or other templates for rendering
     * and expects the [i18n] default file to always be available. For example
     * an unexpected error or call to [$app->pageNotFound()] can trigger a
     * template to be rendered.
     *
     * @param \FastSitePHP\Application $app
     * @return void
     * @throws \Exception
     */
    public static function setup(Application $app)
    {
        // Fallback language is required
        if (!isset($app->config['I18N_FALLBACK_LANG'])) {
            throw new \Exception('The app config value [I18N_FALLBACK_LANG] needs to be set before calling this function.');
        }

        // Check only during template rendering
        $app->onRender(function() {
            global $app;
            if (!isset($app->locals['i18n'])) {
                I18N::langFile('_', $app->config['I18N_FALLBACK_LANG']);
            }
        });
    }

    /**
     * Validate Language which will likely come from User Input. It is used
     * to read files so it's important to be validated to prevent
     * Path Traversal or other Attacks.
     *
     * This is a simple initial check looking for characters that can be
     * used in an Attack. Additionally [Security::dirContainsFile()] is
     * used in other parts of this code.
     *
     * @param string $lang
     * @return void
     * @throws \Exception
     */
    private static function validateLang($lang)
    {
        if (is_string($lang) === false
            || strpos($lang, ' ') !== false
            || strpos($lang, '\\') !== false
            || strpos($lang, '/') !== false
            || strpos($lang, '.') !== false
            || strpos($lang, chr(0)) !== false // NULL Character
        ) {
            if (is_string($lang)) {
                $error = 'Unsupported Language format for [%s]. Use format of [lang = \'en\'] or [lang-country = \'en-US\'].';
                $error = sprintf($error, __CLASS__);
                throw new \Exception($error);
            } else {
                // This one is likely a developer error
                $error = 'Invalid parameter for [$lang], expected a [string] but recevied a [%s].';
                $error = sprintf($error, gettype($lang));
                throw new \Exception($error);
            }
        }
    }

    /**
     * Validate that config value [I18N_DIR] is correctly defined
     *
     * @param \FastSitePHP\Application $app
     * @return string
     * @throws \Exception
     */
    private static function validateDir($app)
    {
        if (!isset($app->config['I18N_DIR'])) {
            $error = 'Using [%s::langFile()] requires the Application config value [I18N_DIR] to first be defined. Refer to documentation on how to use this class.';
            $error = sprintf($error, __CLASS__);
            throw new \Exception($error);
        }
        $dir = $app->config['I18N_DIR'];
        if (!is_string($dir)) {
            $error = 'Application config value [I18N_DIR] must be a string but was instead a [%s].';
            $error = sprintf($error, gettype($dir));
            throw new \Exception($error);
        } elseif (!is_dir($dir)) {
            $error = 'I18N Language File directory [%s] was not found. Either it doesn’t exist or the web server does not have permissions to view the folder.';
            $error = sprintf($error, $dir);
            throw new \Exception($error);
        }

        // Return the normalized dir path
        return realpath((string)$app->config['I18N_DIR']) . DIRECTORY_SEPARATOR;
    }
}
