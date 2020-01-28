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

namespace FastSitePHP;

use \Exception;
use \ErrorException;
use FastSitePHP\Route;

/**
 * The AppMin Class contains core code from the Application Class and is much
 * smaller in size. If you have a minimal site such as a few simple web services
 * then AppMin could be used as an alternative to the Application Class.
 *
 * Due to its small size the AppMin Class may run twice as fast as the Application
 * Class on some servers, however this is typically a very small number (thousands
 * or tens of thousands of a second only). In general if using PHP 7 with common
 * production settings on a Linux Server there will be no difference between this
 * Class and the Application Class.
 *
 * If you are using this class with only a few classes you might want to consider
 * copying the files to your project and modifying this class to fit the needs
 * of your site.
 */
class AppMin
{
    /**
     * HTTP Response Status Code
     * @var int|null
     */
    public $status_code = null;

    /**
     * HTTP Response Headers
     * @var array
     */
    public $headers = array();

    /**
     * HTTP Response Headers for CORS
     * @var array|null
     */
    public $cors_headers = null;

    /**
     * If [true] then the following response headers will be sent to the client:
     *
     *     Cache-Control: no-cache, no-store, must-revalidate
     *     Pragma: no-cache
     *     Expires: -1
     *
     * @var bool
     */
    public $no_cache = false;

    /**
     * Optional Location of the template files that get rendered when using the [render()] function.
     * @var string|null
     */
    public $template_dir = null;

    /**
     * Array of template files or a single file name as a string.
     * @var string|array|null
     */
    public $error_template = null;

    /**
     * Array of not-found template files or a single file name as a string.
     * @var string|array|null
     */
    public $not_found_template = null;

    /**
     * Title for 500 Error Responses, available as [$page_title] for the error template.
     * @var string|null
     */
    public $error_page_title = 'An error has occurred';

    /**
     * Message for 500 Error Responses, available as [$message] for the error template.
     * @var string|null
     */
    public $error_page_message = 'An error has occurred while processing your request.';

    /**
     * Title for 404 'Not Found' Responses, available as [$page_title] for the template.
     * @var string|null
     */
    public $not_found_page_title = 'Page Not Found';

    /**
     * Message for 404 'Not Found' Responses, available as [$message] for the template.
     * @var string|null
     */
    public $not_found_page_message = 'The requested page could not be found.';

    /**
     * Title for 405 'Method Not Allowed' Responses, available as [$page_title] for the template.
     * @var string|null
     */
    public $method_not_allowed_title = 'Error - Method Not Allowed';

    /**
     * Message for 405 'Method Not Allowed' Responses, available as [$message] for the template.
     * @var string|null
     */
    public $method_not_allowed_message = 'A [{method}] request was submitted however this route only allows for [{allowed_methods}] methods.';

    /**
     * By default, a request for '/about/' with route '/about' will match, however if
     * [strict_url_mode] is set to true then '/about/' and '/about' would be separate URL's.
     * @var bool
     */
    public $strict_url_mode = false;

    /**
     * Routes defined from one of the url matching functions:
     * route(), get(), or post()
     * @var array
     */
    private $site_routes = array();

    /**
     * Last Handled Error from errorHandler(), used so
     * fatal errors can properly be handled on shutdown().
     * @var array|null
     */
    private $last_error = null;

    /**
     * Setup error handling and optionally set a time-zone for the application
     *
     * @param string|null $timezone
     * @return void
     * @throws \Exception
     */
    public function setup($timezone)
    {
        // Handle all errors and Exceptions
        error_reporting(-1);
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdown'));

        // If the [php.ini] setting [display_errors] is turned on then turn it off
        if (function_exists('filter_var') &&
            function_exists('ini_get') &&
            filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN) === true &&
            function_exists('ini_set')
        ) {
            ini_set('display_errors', 'off');
        }

        // Set the time-zone
        // Use [php.ini] settings if the parameter 'date.timezone' is specified
        if (isset($timezone) && $timezone !== null) {
            if ($timezone === 'date.timezone') {
                $timezone = ini_get('date.timezone');
                if ($timezone === '' || $timezone === null) {
                    throw new \Exception('The settings [date.timezone] is not setup in [php.ini], it must be defined when using calling setup([date.timezone]) or setup() must be called with a valid timezone instead.');
                }
            }
            date_default_timezone_set($timezone);
        }
    }

    /**
     * Application defined exception handler function
     *
     * @param \Exception|\Throwable $e
     * @return void
     */
    public function exceptionHandler($e)
    {
        $this->sendErrorPage(500, $e);
    }

    /**
     * Application defined error handler function
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws \ErrorException
     */
    public function errorHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            $this->last_error = array(
                'type' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line,
            );
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Application defined error handler function for fatal errors
     * @return void
     */
    public function shutdown()
    {
        $err = error_get_last();
        if ($err !== null && $err !== $this->last_error) {
            $e = new \ErrorException($err['message'], 0, $err['type'], $err['file'], $err['line']);
            $this->exceptionHandler($e);
        }
    }

    /**
     * Used internally this function handles rendering templates and sending the response
     * for pages with a status code of 500 'Internal Server Error', 404 'Not Found', or
     * 405 'Method Not Allowed'. When a 404 or 405 response is sent the parameter $e
     * is null otherwise for PHP 5 and instance of an Exception Object or for
     * PHP 7 and instance of a Throwable Object.
     *
     * @param int $response_code
     * @param null|\Exception|\Throwable $e
     * @param null|array $allowed_methods   Used for 405 'Method Not Allowed' Responses
     * @return void
     * @throws \Exception
     */
    private function sendErrorPage($response_code, $e = null, $allowed_methods = null)
    {
        if ($response_code === 404) {
            $page_title = $this->not_found_page_title;
            $message = $this->not_found_page_message;
        } elseif ($response_code === 405) {
            $page_title = $this->method_not_allowed_title;
            $message = $this->method_not_allowed_message;
            $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
            $message = str_replace('{method}', $method, $message);
            $message = str_replace('{allowed_methods}', implode(', ', $allowed_methods), $message);
        } else {
            $page_title = $this->error_page_title;
            $message = $this->error_page_message;

            if (get_class($e) === 'ErrorException') {
                $error_constants = array(
                    E_ERROR => 'E_ERROR',
                    E_WARNING => 'E_WARNING',
                    E_PARSE => 'E_PARSE',
                    E_NOTICE => 'E_NOTICE',
                    E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                    E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                    E_USER_ERROR => 'E_USER_ERROR',
                    E_USER_WARNING => 'E_USER_WARNING',
                    E_USER_NOTICE => 'E_USER_NOTICE',
                    E_STRICT => 'E_STRICT',
                    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                    E_DEPRECATED => 'E_DEPRECATED',
                    E_USER_DEPRECATED => 'E_USER_DEPRECATED',
                );
                $error_level = $e->getSeverity();
                if (isset($error_constants[$error_level])) {
                    $e->severityText = $error_constants[$error_level];
                }
            }
        }

        $response = null;
        $error_page = null;
        if (($response_code === 404 || $response_code === 405) && $this->not_found_template !== null) {
            $error_page = $this->not_found_template;
        } elseif ($this->error_template !== null) {
            $error_page = $this->error_template;
        }

        if ($error_page !== null) {
            try {
                $response = $this->render($error_page, array(
                    'page_title' => $page_title,
                    'message' => $message,
                    'e' => $e,
                ));
            } catch (\Exception $render_ex) {
                $this->error_template = null;
                $this->sendErrorPage(500, $render_ex);
            }
        }

        if ($response === null) {
            $error_page = __DIR__ . '/Templates/error.php';
            if (is_file($error_page)) {
                $response = $this->render($error_page, array(
                    'page_title' => $page_title,
                    'message' => $message,
                    'e' => $e,
                ));
            } else {
                $response = '<h1>' . $this->escape($page_title) . '</h1>';
                $response .= '<p>' . $this->escape($message) . '</p>';
            }
        }

        if (!headers_sent()) {
            header_remove();
        }

        $this->status_code = $response_code;
        $this->headers = array('Content-Type' => 'text/html; charset=UTF-8');
        $this->sendResponse($response);
        exit();
    }

    /**
     * Convert special characters to HTML entities.
     * This function is a wrapper for the php function:
     *     htmlspecialchars($text, ENT_QUOTES, 'UTF-8', true)
     *
     * Characters escaped are:
     *     " = &quot;
     *     & = &amp;
     *     ' = &#039;
     *     < = &lt;
     *     > = &gt;
     *
     * @param string $text
     * @return string
     */
    public function escape($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', true);
    }

    /**
     * Render a single template file or an array of template files
     * using variables specified in the $data parameter.
     *
     * @param array|string $templates
     * @param array|null $data
     * @return string
     */
    public function render($templates, $data = null)
    {
        // Start new output buffering
        if (ob_get_length()) {
            ob_end_clean();
        }
        ob_start();

        // Template Dir
        if (is_string($this->template_dir) && strlen($this->template_dir) > 0 && substr($this->template_dir, -1, 1) !== '/') {
            $this->template_dir .= '/';
        }

        // Extract variables to local scope for the template
        $app = $this;
        if ($data !== null) {
            extract($data);
        }

        // Process each template
        foreach ((array)$templates as $template_file) {
            include (string)$this->template_dir . $template_file;
        }
        return ob_get_clean();
    }

    /**
     * Add a route for an HTTP Request
     *
     * @param string $pattern
     * @param \Closure $callback
     * @param string|null $method (default: null)
     * @return Route
     */
    public function route($pattern, \Closure $callback, $method = null)
    {
        // Create the new Route
        $route = new Route();
        $route->pattern = $pattern;
        $route->controller = $callback;
        $route->method = $method;

        // Add to Routes Array and return the created Route Object
        $this->site_routes[] = $route;
        return $route;
    }

    /**
     * Add a route for an HTTP 'GET' Request
     *
     * @param string $pattern
     * @param \Closure $callback
     * @return Route
     */
    public function get($pattern, \Closure $callback)
    {
        return $this->route($pattern, $callback, 'GET');
    }

    /**
     * Add a route for an HTTP 'POST' Request
     *
     * @param string $pattern
     * @param \Closure $callback
     * @return Route
     */
    public function post($pattern, \Closure $callback)
    {
        return $this->route($pattern, $callback, 'POST');
    }

    /**
     * Return the Array of Defined Routes
     *
     * @return array
     */
    public function routes()
    {
        return $this->site_routes;
    }

    /**
     * Redirect the user to another page or site. This must be called
     * prior to headers and content being sent to the user. Defaults to a
     * [302 'Found'] Response.
     *
     * Example:
     *
     *     // User makes this request
     *     $app->get('/page1', function() use ($app) {
     *         $app->redirect('page2');
     *     });
     *
     *     // User will then see this URL and Response
     *     $app->get('/page2', function() {
     *         return 'page2';
     *     });
     *
     * @param string $url
     * @param int $status_code
     * @return void
     * @throws \Exception
     */
    public function redirect($url, $status_code = 302)
    {
        // Supported Status Codes
        $status_code_text = array(
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
        );

        // Validation
        if (headers_sent()) {
            throw new \Exception(sprintf('Error trying to redirect from [%s->%s()] because Response Headers have already been sent to the client.', __CLASS__, __FUNCTION__));
        } elseif (!isset($status_code_text[$status_code])) {
            throw new \Exception(sprintf('Invalid [$status_code = %s] specified for [%s->%s()]. Supported Status Codes are [%s].', $status_code, __CLASS__, __FUNCTION__, implode(', ', array_keys($status_code_text))));
        }

        // Build Response
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
        $content = null;
        if ($method !== 'HEAD') {
            $content = '<h1>' . $status_code_text[$status_code] . '</h1>';
            $content .= '<p>Redirecting to <a href="' . $this->escape($url) . '">' . $this->escape($url) . '</a></p>';
        }

        // Send Response
        header('Location: ' . $url, true, $status_code);
        if ($content !== null) {
            echo $content;
        }
        exit();
    }

    /**
     * Return the Requested Path (Page only, excluding site, base directory, query strings, etc).
     * This will return the same result regardless of the Web Server used and it will be
     * based on where the [index.php] or entry PHP file is located.
     *
     * @return string|null
     */
    public function requestedPath()
    {
        $url = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null);
        if ($url === null || $url === '') {
            if (!isset($_SERVER['REQUEST_URI'])) {
                return null;
            }
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $script_name = $_SERVER['SCRIPT_NAME'];
            if (strpos($url, $script_name) === 0) {
                $url = substr($url, strlen($script_name));
            } elseif ((string)$script_name !== '') {
                $data = explode('/', $script_name);
                $base_name = $data[count($data) - 1];
                $script_name = substr($script_name, 0, strlen($script_name) - strlen($base_name) - 1);
                $url = substr($url, strlen($script_name));
            }
        }

        if ((string)$url === '') {
            $url = '/';
        } elseif (!$this->strict_url_mode && strlen($url) > 1 && substr($url, -1) === '/') {
            $url = substr($url, 0, strlen($url) - 1);
        }

        return $url;
    }

    /**
     * Return the Site Root URL; the URL returned is the base URL for all pages.
     *
     * @return string|null
     */
    public function rootUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return null;
        }

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $script_name = $_SERVER['SCRIPT_NAME'];

        if (strpos($url, $script_name) === 0 && !($url === $script_name && $url === '/')) {
            $url = substr($url, 0, strlen($script_name)) . '/';
        } elseif ((string)$script_name !== '') {
            $data = explode('/', $script_name);
            $base_name = $data[count($data) - 1];
            $script_name = substr($script_name, 0, strlen($script_name) - strlen($base_name) - 1);
            $url = substr($url, 0, strlen($script_name)) . '/';
        }

        $is_secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
        $url = ($is_secure ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $url;
        return $url;
    }

    /**
     * Return the Site Root URL; the URL returned is the base URL for all pages.
     *
     * @return string
     */
    public function rootDir()
    {
        // Get the root URL and only get the dir if it contains the starting script
        // file name (for example '/index.php')
        $url = $this->rootUrl();

        if (strpos($url, $_SERVER['SCRIPT_NAME']) !== false) {
            $url = dirname($url) . '/';
        }

        return $url;
    }

    /**
     * Check if a the current url path matches a defined route.
     *
     * @param string $pattern
     * @param string $path
     * @return array|bool
     * @throws \Exception
     */
    public function routeMatches($pattern, $path)
    {
        $matches = true;
        $args = array();

        if (!$this->strict_url_mode) {
            $last_pattern_char = substr($pattern, -1, 1);
            if (strlen($path) > 1 && substr($path, -1, 1) === '/' && $last_pattern_char !== '/') {
                $path = substr($path, 0, -1);
            }
        }

        if ($pattern !== $path) {
            $url_parts = explode('/', $path);
            $pattern_parts = explode('/', $pattern);
            $m = count($url_parts);

            if ($m !== count($pattern_parts)) {
                $matches = false;
            } else {
                for ($n = 0; $n < $m; $n++) {
                    if (urldecode($url_parts[$n]) !== $pattern_parts[$n]) {
                        if (strlen($pattern_parts[$n]) > 1 && substr($pattern_parts[$n], 0, 1) === ':') {
                            $args[substr($pattern_parts[$n], 1)] = urldecode($url_parts[$n]);
                        } else {
                            $matches = false;
                            break;
                        }
                    }
                }
            }
        }

        return ($matches ? $args : false);
    }

    /**
     * Private function that gets called from $app->run(). This function
     * gets called only on matched routes and checks each filter in the
     * route. If a filter function returns false then the route is skipped.
     * If all filters for the route returns anything else including nothing
     * then the route is considered valid for processing.
     *
     * @param Route $route
     * @param string $method
     * @param string $url
     * @return bool
     * @throws \Exception
     */
    private function skipRoute($route, $method, $url)
    {
        $skip_route = false;
        foreach ($route->filter_callbacks as $callback) {
            if ($callback instanceof \Closure) {
                $result = call_user_func($callback);
                if ($result === false) {
                    $skip_route = true;
                    break;
                }
            } else {
                throw new \Exception(sprintf('An item from [Route->filter()] for URL [%s %s] was defined as a [%s] but it should be defined as a Closure function.', $method, $url, gettype($callback)));
            }
        }
        return $skip_route;
    }

    /**
     * This is the main function that processes the request, determines the route,
     * and sends a response. Routes, settings, validation rules, etc need to be
     * defined prior to calling this function.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        // Define Variables used through-out this function
        $response = null;
        $route_was_found = false;
        $allowed_methods = array();

        // Get the Request Method and URL
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
        $url = $this->requestedPath();

        // Handle the OPTIONS request method if sent from the client.
        if ($method === 'OPTIONS') {
            $this->sendOptionsResponse($url);
        }

        // Find the first matching Route.
        foreach ($this->site_routes as $route) {
            // First check if the requested URL matches the current route.
            $args = $this->routeMatches($route->pattern, $url);
            if ($args === false) {
                continue;
            }

            // Next check the method [GET, POST, etc]
            if (!($route->method === null
                || $route->method === $method
                || ($route->method === 'GET' && $method === 'HEAD'))) {
                    $allowed_methods[] = $route->method;
                    continue;
            }

            // The route matches so check any filter functions defined for the route.
            if (!$this->skipRoute($route, $method, $url)) {
                // Make sure that the controller is properly defined.
                if (!($route->controller instanceof \Closure)) {
                    throw new \Exception(sprintf('A [route->controller] for URL [%s %s] was defined as a [%s] but it should be defined as a Closure function. This can happen if controller is modified directly after it is defined from route(), get(), or post().', $method, $url, gettype($route->controller)));
                }

                // Call the route controller function
                $response = call_user_func_array($route->controller, $args);
                $route_was_found = true;
                if (isset($response) || ob_get_length() > 0 || headers_sent())  {
                    break;
                }
            }
        }

        // Handle the Result
        if ($response === null) {
            if (!(ob_get_length() > 0 || headers_sent()))  {
                if ($route_was_found) {
                    throw new \Exception(sprintf('Route [%s %s] was matched however the route function returned no response.', $method, $url));
                } else {
                    if (count($allowed_methods) === 0) {
                        $this->sendErrorPage(404);
                    } else {
                        if (in_array('GET', $allowed_methods)) {
                            $allowed_methods[] = 'HEAD';
                        }
                        $allowed_methods[] = 'OPTIONS';
                        $this->sendErrorPage(405, null, array_unique($allowed_methods));
                    }
                }
            }

        // String Response
        } elseif (gettype($response) === 'string') {
            $this->sendResponse($response);

        // Response Object
        } elseif (gettype($response) === 'object' && method_exists($response, 'send') && stripos(get_class($response), 'Response') !== false) {
            $response->send();

        // JSON Response
        } elseif (is_array($response) && !isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json';
            $this->sendResponse(json_encode($response));
        } elseif (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === 'application/json') {
            $this->sendResponse(json_encode($response));

        // Unknown result, raise an exception
        } else {
            $type = gettype($response);
            $type = ($type === 'object' ? $type . ':' . get_class($response) : $type);
            throw new \Exception(sprintf('Unexpected route return type of [%s]. Expected a string, mixed data for a JSON Response, or an object that includes "Response" in the name with a [send()] method.', $type));
        }
    }

    /**
     * Private function that handles the Response for an OPTIONS Request.
     * OPTIONS Requests do not send any content but rather are used to send
     * Cross-Origin Resource Sharing (CORS) Headers and an 'Allow' Header listing
     * HTTP Methods allowed for the URL. This function gets called from [run()].
     *
     * @param string $url   Requested URL
     * @return void
     */
    private function sendOptionsResponse($url)
    {
        // Define an empty array for supported request header methods
        $allowedMethods = array();

        // Build a list of supported request methods
        // by matching all routes to the current url
        foreach ($this->site_routes as $route) {
            // If the url request is for general site info then
            // get all used requested methods for all defined routes.
            // This doesn't return any actual URL info to the client.
            if ($url === '/*') {
                switch ($route->method) {
                    // If the route method is defined
                    // as null then assume the route
                    // supports HEAD/GET/POST
                    case null:
                        $allowedMethods[] = 'HEAD';
                        $allowedMethods[] = 'GET';
                        $allowedMethods[] = 'POST';
                        break;
                    // GET routes will also support HEAD requests
                    case 'GET':
                        $allowedMethods[] = 'HEAD';
                        $allowedMethods[] = 'GET';
                        break;
                    // Add the matching method
                    default:
                        $allowedMethods[] = $route->method;
                        break;
                }

                // Continue with the next route; code execution goes
                // back to the top of the current foreach loop rather
                // than continuing below
                continue;
            }

            // Does the Requested URL match the Current Route?
            $args = $this->routeMatches($route->pattern, $url);
            if ($args === false) {
                continue;
            }

            // Check any filter functions defined for the route. If a route exits
            // but the user doesn't have access because of a filter function then
            // they won't see it here so they cannot use OPTIONS to probe for possible URL's.
            if (!$this->skipRoute($route, 'OPTIONS', $url)) {
                // Same rules as used above when matching all routes
                switch ($route->method) {
                    case null:
                        $allowedMethods[] = 'HEAD';
                        $allowedMethods[] = 'GET';
                        $allowedMethods[] = 'POST';
                        break;
                    case 'GET':
                        $allowedMethods[] = 'HEAD';
                        // fall-through the case statement to add the 'GET'
                    default:
                        $allowedMethods[] = $route->method;
                        break;
                }
            }
        }

        // If no methods were found send a 404 Response 'Not found'
        if (count($allowedMethods) === 0) {
            $this->sendErrorPage(404);
        } else {
            // Always add 'OPTIONS' as it is always allowed for valid routes
            $allowedMethods[] = 'OPTIONS';
        }

        // When a URL matches multiple routes the allowed request methods will be
        // duplicated so remove all duplicates from the array and sort the result.
        $allowedMethods = array_unique($allowedMethods);
        asort($allowedMethods);
        $allowedMethods = implode(', ', $allowedMethods);

        // Check if any Cross-Origin Resource Sharing (CORS) Headers are
        // defined and if so send them with the OPTIONS request.
        // See the cors() function for resource links.
        if ($this->cors_headers !== null) {
            // Loop through and output each CORS Response Header Field
            $has_cors_allow_methods = false;
            foreach ($this->cors_headers as $name => $value) {
                // Output the Header
                header("$name: $value");

                // Keep track if the header [Access-Control-Allow-Methods] is defined
                if (strtolower($name) === 'access-control-allow-methods') {
                    $has_cors_allow_methods = true;
                }
            }

            // If there were any CORS headers and [Access-Control-Allow-Methods]
            // was not defined then output it with the values for the 'Allow' header.
            if (!$has_cors_allow_methods) {
                header('Access-Control-Allow-Methods: ' . $allowedMethods);
            }
        }
        exit();
    }

    /**
     * Private function that sends the actual HTTP Response.
     *
     * @param string $content
     * @return void
     * @throws \Exception
     */
    private function sendResponse($content)
    {
        if (!headers_sent()) {
            if ($this->status_code !== null) {
                if (function_exists('http_response_code')) {
                    http_response_code($this->status_code);
                } else {
                    $status_code_text = array(
                        200 => 'OK',
                        201 => 'Created',
                        202 => 'Accepted',
                        204 => 'No Content',
                        205 => 'Reset Content',
                        404 => 'Not Found',
                        405 => 'Method Not Allowed',
                        500 => 'Internal Server Error',
                    );
                    if (isset($status_code_text[$this->status_code])) {
                        header(sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $this->status_code, $status_code_text[$this->status_code]));
                    }
                }
            }
            if ($this->no_cache) {
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: -1');
            }
            if ($this->cors_headers !== null) {
                foreach ($this->cors_headers as $name => $value) {
                    header("$name: $value");
                }
            }
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }

        // Output Response
        // Note - To handle 304 Response Codes use the Class [\FastSitePHP\Web\Response] instead.
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
        if ($method !== 'HEAD' && $this->status_code !== 204 && $this->status_code !== 205) {
            echo $content;
        }
    }
}
