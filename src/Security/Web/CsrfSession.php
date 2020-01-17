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
 * Session-Based CSRF Tokens
 */
class CsrfSession
{
    /**
     * Setup and validate session-based CSRF Tokens. A good place to call this
     * function is on route filters of pages that use authentication.
     *
     * This will assign the token to app property $app->locals['csrf_token'] 
     * which then must be included with the form or response. When using 
     * [$app->render()] the value will be available as variable [$csrf_token].
     * 
     * For usage see demo code.
     * 
     * @link https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)
     * @link https://en.wikipedia.org/wiki/Cross-site_request_forgery
     * @param Application @app
     * @param string $key - Defaults to ['X-CSRF-Token'], the key must be included in either a form field or request header when the request is submitted.
     * @throws \Exception
     */
    public static function setup(Application $app, $key = 'X-CSRF-Token')
    {
        // Start Session if not already started
        $started = session_start();
        if (!$started) {
            throw new \Exception('Unable to use CSRF because a session could not be started.');
        }

        // Get existing user token
        $user_token = (isset($_SESSION[$key]) ? $_SESSION[$key] : null);
        
        // Validate POST, PUT, DELETE Requests, etc. 
        // A Token MUST be sent with the request.
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null);
        if (!($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS')) {
            // Make sure Token exists
            if ($user_token === null) {
                throw new \Exception('Error (CSRF not defined) - Your session may have expired. Please logout then log back in and try again.');
            }

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

            // Compare what client submitted vs what is in session.
            // If using a version of PHP below 5.6 then define the [hash_equals()] function.
            if (PHP_VERSION_ID < 50600) {
                require_once __DIR__ . '/../../Polyfill/hash_equals_compat.php';
            }
            if (!\hash_equals($submitted_token, $user_token)) {
                throw new \Exception('Error (Invalid CSRF) - Your session may have expired. Please logout then log back in and try again.');
            }
        }

        // Generate new token if not defined and save to session.
        // Tokens will be created only once per user session.
        if ($user_token === null) {
            $user_token = Base64Url::encode(Random::bytes(32));
            $_SESSION[$key] = $user_token;
        }

        // Make token available in App Locals.
        // The site itself must send this with the form or page.
        $app->locals['csrf_token'] = $user_token;
    }
}