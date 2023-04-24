# FastSitePHP Change Log

FastSitePHP uses [Semantic Versioning](https://docs.npmjs.com/about-semantic-versioning). This change log includes Framework release history and new website features or major changes.

## 1.5.1 (April 23, 2023)

* Updates to handle Deprecation Notices in PHP 8.1 and 8.2
  * `FastSitePHP\Lang\Time::secondsToText()`
  * `FastSitePHP\Lang\L10N->formatDateTime()`
  * `FastSitePHP\Lang\L10N->formatNumber()`
  * `FastSitePHP\Data\Validator->checkType()`

## 1.5.0 (April 23, 2023)

* **Added Support for PHP 8.2**
* Dynamic Properties have been Deprecated by default as of PHP 8.2 so both `Application` and `AppMin` classes now extend from stdClass to support Dynamic Properties. FastSitePHP is designed for flexibility so Dynamic Properties are an important feature.
* This logic in custom Error Templates `(isset($e->severityText) ? ' (' . $e->severityText . ')' : '')` needs to be replaced with `(isset($severityText) ? ' (' . $severityText . ')' : '')`
* For PHP 8.2+ the Utf8 Class now uses `iconv('windows-1252', 'UTF-8', $data)` instead of `utf8_encode($data)` while older versions of PHP still use `utf8_encode()`.
* Update `cacert.pem` to the latest version which is used by `\FastSitePHP\Net\HttpClient` on Windows and Mac Computers.
* Added `AppMin->show_detailed_errors` boolean property based on the standard `Application` Class.  * Minor Unit Testing updates to handle changed error messages in PHP.

## 1.4.6 (February 9, 2022)

* Updated `FastSitePHP\Data\Database` to use PDO data types rather than dynamic typing for parametrized queries
  * Added new function `Database->getBindType($value)` for this feature
  * This can be turned off using `$db->use_bind_value = false;` where `$db` is an instance of the `Database` class
  * This was added because MySQL (but not other databases) would have issues for certain queries where INT type was specifically required:
  ```php
  $sql = 'SELECT * FROM test ORDER BY id DESC LIMIT ?';
  $records = $db->query($sql, [$limit]);
  ```
  * https://www.php.net/manual/en/pdo.constants.php
* Fixed minor typos in docs

## 1.4.5 (January 5, 2022)

* Added Support for PHP 8.1
* Updated `Request->headers()` for nginx when it includes `Content-Length` or `Content-Type` with a blank value.
* Updated Server Setup Script `create-fast-site.sh` so that it shows the IPv4 Address rather than IPv6 and updated version of PHP installed from `8.0` to `8.1`.

## Web Server Updates (December 30, 2021)

* Both the main site and the playground site (along with several other open source projects) where migrated to a new server (1 server instead of 5).
  * FastSitePHP requires very little resources or memory so it didn't make sense to run 5 servers.
  * See the new setup doc at: https://github.com/fastsitephp/fastsitephp/blob/master/docs/server-setup/server-setup.sh

## 1.4.4 (December 3, 2020)

* Update `cacert.pem` to the latest version which is used by `\FastSitePHP\Net\HttpClient` on Windows and Mac Computers.
* Confirmed that FastSitePHP works with the final release of `PHP 8.0.0`. Previously it was tested and updated for Alpha builds. No Framework changes had to be made with this release, however some unit tests had to be updated.

## 1.4.3 (November 24, 2020)

* Fix for `Application->requestedPath()` so that it strips the Query String if using the PHP Built-in Server with all resources using a fallback `index.php` or other PHP file. This was not a common scenario for most PHP development and didn't affect any production servers.

## 1.4.2 (September 3, 2020)

* No changes however `export-ignore` was not working properly from `.gitattributes` for the previous release

## 1.4.1 (September 3, 2020)

* Minor update for `\FastSitePHP\Application->requestedPath()` to return the requested path of valid static files when using the PHP Built-In Server for local development
  * This was found to affect very specific setups, for example a root level `index.php` routing file with a `public` sub-directory and `node_modules` at the root level.

## 1.4.0 (August 6, 2020)

* Added Support so Cookies can use the `SameSite = 'Strict|Lax|None'` Attribute
  * Requires `PHP 7.3` or Higher
  * Affects the following functions which make a call to `setcookie()` using the new `$options` array parameter:
    * `\FastSitePHP\Application->cookie($name, $value, array $options)`
    * `\FastSitePHP\Application->clearCookie($name, array $options)`
    * `\FastSitePHP\Web\Response->cookie($name, $value, array $options)`
    * `\FastSitePHP\Web\Response->clearCookie($name, array $options)`
    * `\FastSitePHP\Web\Response->signedCookie($name, $value, $expire_time, array $options)`
    * `\FastSitePHP\Web\Response->jwtCookie($name, $value, $expire_time, array $options)`
    * `\FastSitePHP\Web\Response->encryptedCookie($name, $value, array $options)`

## 1.3.1 (July 15, 2020)

* Updates for `\FastSitePHP\Net\HttpClient`
  * Fix so that `$response->json` is populated when the a case-insensitve header is used `content-type`, `CONTENT-TYPE`, etc is used starting with the value `application/json`. Previously a case-senstive header was required `Content-Type`.
  * Updated the bundled `cacert.pem` from version `2019-10-16` to `2020-06-24`.
* Updated `scripts/install.php` to also download and use latest version of `cacert.pem`.

## 1.3.0 (July 14, 2020)

* Added support for PHP 8 Alpha 2
  * Overall most features worked out of the box with PHP 8 however a few minor changes were needed for full support and a number of unit tests had to be updated
  * Both 64-Bit and 32-Bit releases have been tested and passed all Unit Tests
  * Updates for PHP 8 do not affect previous versions of PHP so FastSitePHP now works with `PHP 5.3` to `PHP 8.0 (Alpha 2)` and all versions in-between.
  * Additional changes may be needed because PHP 8 is not yet finalized
* Added features for classes in the `\FastSitePHP\FileSystem` namespace:
  * Added new function `all()` to `\FastSitePHP\FileSystem\Search`
  * Added an optional `$type` parameter to `\FastSitePHP\FileSystem\Security::dirContainsPath($dir, $path, $type = 'file')` so that `dirContainsPath()` can be used with both files and directories. Previously this function only worked with files. Options for `$type` are [`file`, `dir`, `all`].
  * Added validation to check for empty strings for the `$dir_name` parameter in `\FastSitePHP\FileSystem\Security::dirContainsDir($root_dir, $dir_name)`. Previously the function would have returned `true`; now it returns `false` which is the intended behavior.
  * Example usage:
  ~~~php
  // Security check against Path Traversal Attacks
  if (!Security::dirContainsPath($root_dir, $user_dir, 'dir')) {
      return $app->pageNotFound();
  }

  // Get list of all directories and files directly under the user dir
  $search = Search();
  $full_path = $root_dir . $user_dir;
  list($dirs, $files) = $search->dir($full_path)->all();
  ~~~
* Updates to simplify the rules for linting using `phpstan` https://github.com/phpstan/phpstan
  * Thanks **Ondřej Mirtes** https://github.com/ondrejmirtes (Author of phpstan) and **Viktor Szépe** https://github.com/szepeviktor for helping out.
  * Updates are in pull requests:
    * https://github.com/fastsitephp/fastsitephp/pull/20
    * https://github.com/fastsitephp/fastsitephp/pull/21
  * The updates were minimal and required no changes to code logic and unit tests
  * PHPStan version 0.12.29 has been added to [composer.json](composer.json)
* Updated all Framework code under `src` to validate with https://github.com/FriendsOfPHP/PHP-CS-Fixer
  * This had no impact on code logic or any actual source code rather it updated some minor formatting issues such as replacing `function __construct` with `public function __construct` or `else if` with `elseif`.
  * To use download `php-cs-fixer-v2.phar` then run: `php php-cs-fixer-v2.phar fix src`
* Added support for the Mac-only development environment Laravel Valet
  * Related to issue https://github.com/fastsitephp/starter-site/issues/4
  * **Thanks Valentin Ursuleac** https://github.com/ursuleacv for finding and opening this issue!
  * This has no effect on the main FastSitePHP framework, rather the default development `index.php` file and website `app.php` required some updates in order to run correctly if downloading the main repository.

## Bash Encryption Script (June 28, 2020)

* The Bash Script `encrypt.sh` has been updated to allow for a default file extension `.enc` and to not require the output file parameter `-o` along with a few other minor improvements such as status output during encryption and description.
  * The bash script is not included with the FastSitePHP Framework, however it is included with the main repository and was created for compatibility with the PHP class `FastSitePHP\Security\Crypto\FileEncryption`
  * https://www.fastsitephp.com/en/documents/file-encryption-bash

## Website (March 27, 2020)

* **Thanks Li Jun Hui** for helping with Chinese translations! https://github.com/lijunhuippl

## 1.2.2 (February 26, 2020)

* Update `I18N::langFile()` for a minor edge case bug related to 404 redirect on missing language. It was found to affect local development with the PHP built-in server when a single `index.php` file is used for routing.

## 1.2.1 (January 27, 2020)

* Improved Framework support with FreeBSD
  * `FileEncryption` Class now has improved support for large file encryption (2+ GB) on a basic FreeBSD Server Setup
  * Additional documentation on FreeBSD Server Setup: https://www.fastsitephp.com/en/documents/install-php-on-linux
* Fixed a bug with `FastSitePHP\Encoding\Json::encode()` that prevented it from working when using PHP `5.3`. This did not affect any other version of PHP.

* **Thanks Nicolas CARPi for opening the issue related to the following items** https://github.com/NicolasCARPi
* Adding support for PHP linting with https://github.com/phpstan/phpstan
  * For info on how to run `phpstan` see comments in file: https://github.com/fastsitephp/fastsitephp/blob/master/phpstan.neon
* Updated `README.md` file with warning about using older versions of PHP. Currently FastSitePHP supports older versions of PHP that are widely used by not considered secure.
* Updated `README.md` with a brief description of how Unit Testing works.

## 1.2.0 (January 10, 2020)

* The core `Application` object now handles route `filter` functions that return a `Response` object instead of a `bool`. This allows for easier unit testing of custom middleware. See code example below.
* Added function `Request->bearerToken()`
* Added function `I18N::hasLang($lang)`
* Updated function `I18N::getUserDefaultLang()` to validate the language from malicious user attempts to attack a site from the 'Accept-Language' request header. This is simply an additional safety check as the key validation is handled by `Security::dirContainsFile` in the function.

~~~php
// Example route
$app->get('/:lang/auth-demo', 'AuthDemo')->filter('Auth.hasAccess');

// Prior to this change an Auth Middleware Object would have likely called [exit()]
class Auth
{
    public function hasAccess(Application $app)
    {
        $res = new Response($app)
        $res
            ->statusCode(401)
            ->header('WWW-Authenticate', 'Bearer')
            ->json(['success' => false, 'authRequired' => true])
            ->send();
        exit();
    }
}

// Now the Middleware Object can return a Response Object.
// This allows for easier CLI testing of an Apps Middleware.
class Auth
{
    public function hasAccess(Application $app)
    {
        return (new Response($app))
            ->statusCode(401)
            ->header('WWW-Authenticate', 'Bearer')
            ->json(['success' => false, 'authRequired' => true]);
    }
}
~~~

## 1.1.3 (December 24, 2019)

* Updated `Application->rootUrl()` and `AppMin->rootUrl()` for edge case error when using built-in PHP Server
  * Error did not affect Apache, nginx, IIS, or most PHP built-in server setups
  * When PHP built-in server with fallback 'php -S localhost:3000 website/public/index.php' and code similar to the example below the a site would redirect with 2 forward slashes (example: `http://localhost:3000//en/`).
  * The previous work-around was to use `$app->redirect('/' . I18N::getUserDefaultLang() . '/');`
  * The below code now works correctly in all tested environments

~~~php
$app->get('/', function() use ($app) {
    $app->redirect($app->rootUrl() . I18N::getUserDefaultLang() . '/');
});
~~~

## Website (December 24, 2019)

* Spanish `es` translations complete for all JSON files on the main site
  * https://fastsitephp.com/es/
  * **Thanks Tibaldo Pirela Reyes** for helping with translations! https://github.com/tpirelar

## 1.1.2 (December 16, 2019)

* Updates for easier nginx support using a basic nginx install
  * Change affected `Application->requestedPath()` and `AppMin->requestedPath()` so handle empty string "" for PATH_INFO

## Website (December 12, 2019)

* Created a script that allows easy web server setup with Apache, PHP, and the FastSitePHP Starter Site

~~~bash
wget https://www.fastsitephp.com/downloads/create-fast-site.sh
sudo bash create-fast-site.sh
~~~

## 1.1.1 (December 12, 2019)

* Brazilian Portuguese `pt-BR` language support added for `L10N` - formatting dates, times, and numbers
  * https://www.fastsitephp.com/en/api/Lang_L10N
  * Previously `pt-BR` would have fallen back to `pt`
  * **Thanks Marcelo dos Santos Mafra** for finding and providing this! https://github.com/msmafra
* Class `Lang\L10N`
  * https://www.fastsitephp.com/en/api/Lang_L10N
  * Fixed bug with `Lang\L10N` class so that a 404 page is correctly sent by default
  * **Thanks eGirlAsm** for finding the bug! https://github.com/eGirlAsm
  * Added link updates for unicode-cldr in the header docs
* Changed default 404 page title message from 'Page Not Found' to '404 - Page Not Found' for clarity - Property [$app->not_found_page_title]
  * https://www.fastsitephp.com/en/api/Application

## 1.1.0 (December 10, 2019)

* New Class `FastSitePHP\FileSystem\Sync`
* Class `FastSitePHP\Lang\I18N`
  * Added new static function: `I18N::getUserDefaultLang()`
  * Fixed edge case error when multiple calls are made to `I18N::langFile()` and a file is missing after the first call.

## 1.0.0 (November 14, 2019)

* Initial public release
