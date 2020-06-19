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

namespace FastSitePHP\Security\Web;

use FastSitePHP\Application;
use FastSitePHP\Encoding\Base64Url;
use FastSitePHP\Security\Crypto\Random;

/**
 * ‘Stateless’ CSRF Tokens
 *
 * Stateless CSRF Tokens are not stored in Session but rather use a crypto
 * keyed-hash message authentication code (HMAC) to create and verify the token.
 *
 * Stateless Tokens work well if authentication happens with cookies that do
 * not use standard PHP session functions, that said this class also works well
 * with PHP Sessions and provides expiration time that can be changed per page.
 *
 * If stateless authentication always happens through Request Headers for
 * POST Requests and other transactions then CSRF attacks are prevented
 * and CSRF Tokens are not needed. CSRF Tokens are generally needed if
 * authentication happens with cookies (by default Session code always uses cookies).
 *
 * If using PHP 5.3 then using this class will polyfill functions [bin2hex()] and [hex2bin()]
 * and if using a version of PHP below 5.6 then this class will polyfill [hash_equals()].
 *
 * For another Stateless CSRF Token implementation with NodeJS see the link below.
 *
 * @link https://www.paypal-engineering.com/2016/06/01/securing-your-js-apps-w-stateless-csrf/
 */
class CsrfStateless
{
    /**
     * Return a key that can be used to generate and validate Stateless
     * CSRF Tokens. The key must be kept private and not shared with end users.
     *
     * @return string
     */
    public static function generateKey()
    {
        if (PHP_VERSION_ID < 50400) {
            require_once __DIR__ . '/../../Polyfill/hex_compat.php';
        }
        return \bin2hex(Random::bytes(32));
    }

    /**
     * Setup and validate stateless CSRF Tokens. A good place to call this
     * function is on route filters of pages that use authentication.
     *
     * This will assign the token to app property $app->locals['csrf_token']
     * which then must be included with the form or response. When using
     * [$app->render()] the value will be available as variable [$csrf_token].
     *
     * This function requires the App config value $app->config['CSRF_KEY']
     * or an Environment Variable of the same name with a key generated from
     * the function [generateKey()].
     *
     * For usage see demo code.
     *
     * @link https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)
     * @link https://en.wikipedia.org/wiki/Cross-site_request_forgery
     * @link https://en.wikipedia.org/wiki/HMAC
     * @param Application $app
     * @param string|int $user_id - A unique identifier for the user. This doesn't have to be secret and can be a simple as an numeric field in a database.
     * @param null|string|float $expire_time - An option expiration time for the token. The format is a Unix Timestamp in milliseconds or a string value that can be used by the PHP function [strtotime()], for example '+1 hour'.
     * @param string $key - Defaults to ['X-CSRF-Token'], the key must be included in either a form field or request header when the request is submitted.
     * @return void
     * @throws \Exception
     */
    public static function setup(Application $app, $user_id, $expire_time = null, $key = 'X-CSRF-Token')
    {
        if (PHP_VERSION_ID < 50400) {
            require_once __DIR__ . '/../../Polyfill/hex_compat.php';
        }

        // Validate $hmac key and decode it from hex
        $hmac_key = null;
        if (isset($app->config['CSRF_KEY'])) {
            $hmac_key = $app->config['CSRF_KEY'];
        } else {
            $hmac_key = getenv('CSRF_KEY');
        }

        if ($hmac_key === null || $hmac_key === false) {
            throw new \Exception('HMAC Key for Stateless CSRF is not defined. Use the function [CsrfStateless::generateKey()] to create a valid key and then save the result to $app->config[\'CSRF_KEY\'] or an Environment Variable before calling this function.');
        } elseif (strlen($hmac_key) !== 64 || !ctype_xdigit($hmac_key)) {
            throw new \Exception('Invalid HMAC Key for Stateless CSRF. The key must be a hexadecimal string that is 64 characters in length (32 bytes, 256 bits). Use the function [CsrfStateless::generateKey()] to create a valid key.');
        }
        $hmac_key = \hex2bin($hmac_key);

        // Validate the expire time option
        if ($expire_time !== null) {
            if (is_string($expire_time)) {
                // Parse the string date value into a Unix Timestamp (example '+1 day')
                $expire_time = strtotime($expire_time);
                if ($expire_time === false) {
                    throw new \Exception('Invalid [expire_time] parameter for CSRF. A string was passed however it could not be converted to a valid timestamp. If specified the parameter [expire_time] must be either a int representing a Unix Timestamp in Milliseconds or a valid string for the PHP function [strtotime()], examples include \'+1 day\' and \'+30 minutes\'.');
                }
                // Convert the time value from seconds to milliseconds
                $expire_time = $expire_time * 1000;
            } elseif (!is_float($expire_time)) {
                throw new \Exception(sprintf('Unexpected [expire_time] parameter for CSRF, expected [string|float|null] but was passed [%s].', gettype($expire_time)));
            }
        }

        // Validate POST, PUT, DELETE Requests, etc.
        // A Token MUST be sent with the request.
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null);
        if (!($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS')) {
            // Get Token from either Submitted Form or from Request Header
            $submitted_token = null;
            if (isset($_POST[$key])) {
                $submitted_token = $_POST[$key];
            } else {
                // Request header will come in as 'HTTP_X_CSRF_Token'
                $field = 'HTTP_' . str_replace('-', '_', strtoupper($key));
                if (isset($_SERVER[$field])) {
                    $submitted_token = $_SERVER[$field];
                }
            }

            // Was token submitted?
            if ($submitted_token === null) {
                throw new \Exception('CSRF Token not submitted');
            }

            // Get expire time if one was saved with token.
            // If using expire time the token format will be 'time.base64url(token)'.
            $csrf_expire_time = null;
            $parts = explode('.', $submitted_token);
            $count = count($parts);
            if ($count === 2) {
                $csrf_expire_time = (float)$parts[0];
                $submitted_token = $parts[1];
            } else if ($count !== 1) {
                throw new \Exception('CSRF Token is not in the expected format when using an expiration time.');
            }

            // Has the token expired?
            // [$now] the value that JavaScript Date.now() would return. This makes checking from JS easier.
            // PHP [time() * 1000] also works using second precision rather than milliseconds.
            if ($csrf_expire_time !== null) {
                $now = round(microtime(true) * 1000);
                if ($now > (float)$csrf_expire_time) {
                    throw new \Exception('Error - Your session has expired. Please logout then log back in and try again.');
                }
            }

            // Decode and then parse the token into two parts (bytes + hash)
            $submitted_token = Base64Url::decode($submitted_token);
            if ($submitted_token === false || strlen($submitted_token) !== (16 + 32)) {
                throw new \Exception('CSRF Token is not in the expected format.');
            }
            $bytes = substr($submitted_token, 0, 16);
            $submitted_hash = substr($submitted_token, 16);

            // Calculate what the hash should be based on what was submitted
            $hash_text = $bytes . (string)$csrf_expire_time . (string)$user_id;
            $calculated_hash = \hash_hmac('sha256', $hash_text, $hmac_key, true);

            // Compare what client submitted vs the calculated value.
            if (PHP_VERSION_ID < 50600) {
                require_once __DIR__ . '/../../Polyfill/hash_equals_compat.php';
            }
            if (!\hash_equals($calculated_hash, $submitted_hash)) {
                throw new \Exception('Error - Your session may have expired. Please logout then log back in and try again.');
            }
        }

        // Generate a new CSRF Token; a new token is generated on each request.
        $bytes = Random::bytes(16);
        $hash_text = $bytes . (string)$expire_time . (string)$user_id;
        $hmac = \hash_hmac('sha256', $hash_text, $hmac_key, true);
        $token = Base64Url::encode($bytes . $hmac);
        if ($expire_time !== null) {
            $token = (string)$expire_time . '.' . $token;
        }

        // Make token available in App Locals.
        // The site itself must send this with the form or page.
        $app->locals['csrf_token'] = $token;
    }
}
