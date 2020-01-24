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

use \BadMethodCallException;
use \Exception;
use \ErrorException;
use \ReflectionFunction;
use \stdClass;
use FastSitePHP\Route;

/**
 * The Application Class contains the core code for FastSitePHP. It includes
 * global error handling, template rendering, request routing, application
 * events, basic response methods, sending of the response, and more.
 */
class Application
{
    // ------------------------------------------------------------------------------------------------
    //       Public Member Variables
    //
    //    ########  ########   #######  ########  ######## ########  ######## #### ########  ######
    //    ##     ## ##     ## ##     ## ##     ## ##       ##     ##    ##     ##  ##       ##    ##
    //    ##     ## ##     ## ##     ## ##     ## ##       ##     ##    ##     ##  ##       ##
    //    ########  ########  ##     ## ########  ######   ########     ##     ##  ######    ######
    //    ##        ##   ##   ##     ## ##        ##       ##   ##      ##     ##  ##             ##
    //    ##        ##    ##  ##     ## ##        ##       ##    ##     ##     ##  ##       ##    ##
    //    ##        ##     ##  #######  ##        ######## ##     ##    ##    #### ########  ######
    //
    // ------------------------------------------------------------------------------------------------

    /**
     * Location of the template files that get rendered when using the [render()] function.
     *
     * @var string|null
     */
    public $template_dir = null;

    /**
     * Header template file or or an array of file names. If defined the template or templates
     * will be rendered prior to the file or files specified in the [render()] function.
     *
     * @var string|array|null
     */
    public $header_templates = null;

    /**
     * Header template file or or an array of file names. If defined the template or templates
     * will be rendered after to the file or files specified in the [render()] function.
     *
     * @var string|array|null
     */
    public $footer_templates = null;

    /**
     * Error template file or or an array of file names. The error template will be rendered
     * when the Applications throws an uncaught Exception or triggers an unhandled error and
     * the response status code returned with an error template is 500. If not set then then
     * the default [error.php] template located under the [Templates] directory will be used.
     *
     * @var string|array|null
     */
    public $error_template = null;

    /**
     * Not Found template file or or an array of file names. The not-found template will
     * be rendered when the client requests a page that has not matched route or if they
     * request with the wrong method (example GET instead of a POST); the response status
     * codes returned with a not-found template are either [404 => 'Not Found'] or
     * [405 => 'Method Not Allowed']. If this propery is left as the default null and an
     * [error_template] is specified then the template specified in [error_template]
     * will be used. If not set then the default [error.php] template located under the
     * [Templates] directory will be used.
     *
     * @var string|array|null
     */
    public $not_found_template = null;

    /**
     * If set to [true] then full error details will be displayed on the
     * default error template. When using the default error template if
     * running directly on localhost (both client and server) then full error
     * details will automatically be displayed. These rules would only apply
     * to custom error templates if they are setup the same.
     *
     * @var bool
     */
    public $show_detailed_errors = false;

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
    public $not_found_page_title = '404 - Page Not Found';

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
     * The property [strict_url_mode] when set to the default value of false allows for requested
     * URL's to have an ending [/] character at the end of the URL and still be matched to the route.
     * For example, a request for '/about/' with route '/about' will match by default, however if
     * [strict_url_mode] is set to true then '/about/' and '/about' would be separate URL's.
     *
     * @var bool
     */
    public $strict_url_mode = false;

    /**
     * The property [case_sensitive_urls] allows for URL's to be matched using exact
     * upper/lower case letters or for URL's to be matched regardless of case. For example
     * a request for '/ABOUT' with route '/about' will not match by default, however if
     * [case_sensitive_urls] is set to false then the request would match the route.
     *
     * @var bool
     */
    public $case_sensitive_urls = true;

    /**
     * The property [allow_options_requests] which defaults to true allows for the application
     * to automatically handle HTTP OPTIONS requests. When set to false OPTIONS requests would
     * be handled as a standard request and it would be up to the application to handle.
     *
     * OPTIONS requests are most commonly used for Cross-Origin Resource Sharing (CORS) and for
     * custom Web API's. A standard web site used only by a browser and no CORS services will
     * typically not need to handle OPTIONS requests.
     *
     * @var bool
     */
    public $allow_options_requests = true;

    /**
     * String value that if defined will override the default 'Allow' Header Response Field for
     * an OPTIONS Request. For example if the default 'Allow' Header for a specific route
     * returns the value 'HEAD, GET, POST, OPTIONS' but you would prefer
     * to have it return 'HEAD, GET, PUT, OPTIONS' then the values would go here.
     *
     * @var string|null
     */
    public $allow_methods_override = null;

    /**
     * Specify a root class path for route controllers that use a string callback.
     * For example if [$app->get('/page', 'Page')] is used with this value set to
     * 'App\Controllers' then the class 'App\Controllers\Page' will be loaded for
     *  the route.
     *
     * @var string|null
     */
    public $controller_root = null;

    /**
     * Specify a root class path for middleware classes that use a string callback.
     * This applies to [Route->filter()] and [$app->mount()] callbacks. For example
     * if [$route->filter('Auth.isUser')] is used with this value set to 'App\Middleware'
     * then 'App\Middleware\Auth->isUser()' will be called on matching routes.
     *
     * @var string|null
     */
    public $middleware_root = null;

    /**
     * Specify a language for the application. This is set automatically if
     * using the class [FastSitePHP\Lang\I18N].
     *
     * @var string|null
     */
    public $lang = null;

    /**
     * Specify options for [json_encode()] when a JSON Response is returned.
     * Example:
     *     $app->json_options = (JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
     *
     * When using PHP 5.4 or above this will be set to JSON_UNESCAPED_UNICODE
     * on the response if no changes are made.
     *
     * @var int
     */
    public $json_options = 0;

    /**
     * The locals property contains an array of variables that can be set throughout the
     * application and used in the render() function from PHP Templates and custom rendering
     * engines. The locals property is a basic PHP array so adding a new variable is handled
     * simply by setting it (e.g.: [$app->locals['name'] = 'FastSitePHP';] and then the
     * variable [$name] would become available from a template called in the render() function).
     *
     * @var array
     */
    public $locals = array();

    /**
     * The config property contains an array of variables that can be set and used throughout
     * a website or application. There is no requirement to use this however using it allows
     * for site specific configuration values to be organized and easy to find. A few classes
     * included as part of the FastSitePHP Framework do use this including I18N and Crypto.
     *
     * @var array
     */
    public $config = array();

    // --------------------------------------------------------
    //       Private Member Variables
    // --------------------------------------------------------

    /**
     * Routes defined from one of the url matching functions:
     * route(), get(), post(), put(), delete(), or patch()
     *
     * @var array
     */
    private $site_routes = array();

    /**
     * Application events defined from the before() function
     *
     * @var array
     */
    private $before_callbacks = array();

    /**
     * Application events defined from the notFound() function
     *
     * @var array
     */
    private $not_found_callbacks = array();

    /**
     * Application events defined from the beforeSend() function
     *
     * @var array
     */
    private $before_send_callbacks = array();

    /**
     * Application events defined from the after() function
     *
     * @var array
     */
    private $after_callbacks = array();

    /**
     * Application events defined from the error() function
     *
     * @var array
     */
    private $error_callbacks = array();

    /**
     * Application events defined from the onRender() function
     *
     * @var array
     */
    private $render_callbacks = array();

    /**
     * Custom view engine defiend from the engine() function
     *
     * @var array|null|\Closure
     */
    private $view_engine = null;

    /**
     * Route Parameter Options
     *
     * @var array
     */
    private $params = array();

    /**
     * HTTP Response Status Code
     *
     * @var int|null
     */
    private $status_code = null;

    /**
     * HTTP Response Headers
     *
     * @var array
     */
    private $header_fields = array();

    /**
     * HTTP Response Cookies
     *
     * @var array
     */
    private $response_cookies = array();

    /**
     * If true then HTTP Response Headers used
     * to prevent caching will be sent to the client.
     *
     * @var bool
     */
    private $no_cache = false;

    /**
     * Array of HTTP Response CORS Headers
     *
     * @var array|null
     */
    private $cors_headers = null;

    /**
     * Last Handled Error from errorHandler(), used so
     * fatal errors can properly be handled on shutdown().
     *
     * @var array|null
     */
    private $last_error = null;

    /**
     * Array of properties that get defined dynamically when first called.
     *
     * @var array
     */
    private $lazy_load_props = array();

    // ------------------------------------------------------------------------
    //                  Setup and Error Handling
    //
    //     ######  ######## ######## ##     ## ########
    //    ##    ## ##          ##    ##     ## ##     ##
    //    ##       ##          ##    ##     ## ##     ##
    //     ######  ######      ##    ##     ## ########
    //          ## ##          ##    ##     ## ##
    //    ##    ## ##          ##    ##     ## ##
    //     ######  ########    ##     #######  ##
    //
    // ------------------------------------------------------------------------

    /**
     * Setup error handling and optionally set a time-zone for the application.
     *
     * Errors and Exceptions:
     * PHP has an error and exception model that is unique when compared to many other
     * programming languages. Basically PHP 5 contains both errors and exceptions and
     * they both must be handled differently by an application if all errors and exceptions
     * are to be handled.  Additionally PHP provides support for different error reporting
     * levels and using the default PHP server settings not all errors will be handled so
     * if a developer is coming from another language then it can cause a lot of confusion.
     * Examples of this include an undefined variable that would prevent a compiled program
     * from compiling will only raise an error notice warning in PHP and depending upon
     * settings the script can continue while a divide by zero error that might raise an
     * exception or cause a runtime error in another language causes an error type of a
     * warning and can allow for the script to continue. FastSitePHP helps simplify handling
     * errors and exceptions by treating all errors as exceptions that can be handled with
     * a try/catch code block, allowing for all errors and exceptions to be handled using
     * an error() callback function, and rendering all errors and exceptions to the same
     * template with a 500 'Internal Server Error' response code.
     *
     * Time-zone:
     * Setting a time-zone is required when calling PHP date/time functions. If no time-zone
     * is defined and a date/time function is called then PHP will trigger E_NOTICE or
     * E_WARNING errors. If null is passed as the parameter to this function then it will
     * not setup the time-zone otherwise the time-zone will get set and if the value is not
     * valid then an error or exception will occur and the default error template would be
     * rendered. The $timezone parameter if defined must be set to either a valid time-zone
     * for the PHP function date_default_timezone_set() or to the value 'date.timezone' which
     * use the [php.ini] configuration setting 'date.timezone' for the time-zone. By default
     * the value would be blank when PHP is installed. If PHP is installed on Windows through
     * Microsoft's Web Platform Installer then the [php.ini] setting value will likely be
     * set to the server's timezone.
     *
     * @link http://php.net/manual/en/timezones.php
     * @link http://php.net/manual/en/datetime.configuration.php
     * @link http://php.net/manual/en/function.date-default-timezone-set.php
     * @param string|null $timezone
     * @return void
     * @throws \Exception
     */
    public function setup($timezone)
    {
        // Report on all errors, this helps prevent unexpected runtime errors.
        // The parameter for error_reporting() is a bitmask value so -1 is used to
        // show all possible errors with every version of PHP. With PHP 5.4 and above
        // the constant (E_ALL) can be used instead, however with 5.3 the parameter
        // would need to be (E_ALL | E_STRICT) to report on all errors.
        error_reporting(-1);

        // This function will handle all Unhandled Exceptions allowing the application
        // to log or handling them using the error() callback function, and then show
        // an error page and error response to user or client.
        set_exception_handler(array($this, 'exceptionHandler'));

        // Handle PHP errors that are not thrown as Exceptions and throw them as ErrorExceptions
        // so they can be handled with a try/catch block or the exceptionHandler() function.
        set_error_handler(array($this, 'errorHandler'));

        // Handle Fatal Errors on Shutdown - Fatal Errors will not be caught by errorHandler()
        register_shutdown_function(array($this, 'shutdown'));

        // If the [php.ini] setting [display_errors] is turned on then turn it off.
        // For most sites this would have no impact when using FastSitePHP because
        // errors are handled in the errorHandler() function and previous error output
        // would be cleared when the error template is rendered, however if [display_errors]
        // is turned on and the setting [output_buffering] is turned off then it would
        // cause PHP to display error information in the middle of a page. The setting
        // [output_buffering] can only be defined before the PHP Script runs in [php.ini].
        // FastSitePHP is designed to work in an expected manner regardless of how output
        // buffering is set. PHP is very flexible so if [display_errors] is set to
        // '1', 'true', 'on', or 'yes' then it will be turned on. The filter_var()
        // function handles all true values. In php functions can be disabled from the
        // [php.ini] file so make sure that both [ini_get()] and [ini_set()] are enabled.
        // In FreeBSD or other systems [filter_var] will not be installed by default
        // so make sure it exits as well.
        if (function_exists('filter_var') &&
            function_exists('ini_get') &&
            filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN) === true &&
            function_exists('ini_set')
        ) {
            ini_set('display_errors', 'off');
        }

        // Set the time-zone
        // In Versions of PHP prior to 7.1 a call to this function without specifying the
        // $timezone parameter will results in and E_WARNING error but the code can still
        // execute and then $timezone will be undefined. As of PHP 7.1 a call to this
        // function without the $timezone parameter will trigger an ArgumentCountError
        // Exception and this function will not be called.
        if (isset($timezone) && $timezone !== null) {
            // Use [php.ini] settings if the parameter 'date.timezone' is specified
            if ($timezone === 'date.timezone') {
                // Get the timezone
                $timezone = ini_get('date.timezone');

                // Validate that it is defined
                if ($timezone === '' || $timezone === null) {
                    throw new \Exception('The settings [date.timezone] is not setup in [php.ini], it must be defined when using calling setup([date.timezone]) or setup() must be called with a valid timezone instead.');
                }
            }

            // Set the timezone, if the timezone value is invalid
            // then an error notice will be raised by PHP
            date_default_timezone_set($timezone);
        }
    }

    /**
     * Application defined exception handler function. This function is set as the exception
     * handler when the function [setup()] is called. Passing an exception to this function
     * will run any [error()] callback functions and send a 500 Response Code with the error
     * template to the client. FastSitePHP provides a default error template and allows for
     * customs error template to be assigned from the property [error_template]. This function
     * is public so PHP can call it but in most cases this function would not be called directly
     * from a website or application but rather it is used to handle exceptions that are thrown.
     * For PHP 5 the parameter $e needs to be an instance of an Exception Object and in PHP 7
     * an instance of a Throwable Object.
     *
     * @link http://php.net/manual/en/function.set-exception-handler.php
     * @link http://php.net/manual/en/language.exceptions.php
     * @link http://php.net/manual/en/class.throwable.php
     * @param \Exception|\Throwable $e
     * @return void
     */
    public function exceptionHandler($e)
    {
        $this->sendErrorPage(500, $e);
    }

    /**
     * Application defined error handler function. This function is set as an error handler
     * when the function [setup()] is called. For errors that are not ignored by using the
     * [@] error control operator this function will convert the error to an ErrorException
     * object and throw the exception which then gets handled by the [exceptionHandler()]
     * function. This function is public so PHP can call it but in most cases this function
     * would not be called directly from a website or application but rather it is used to
     * handle errors that are raised.
     *
     * @link http://php.net/manual/en/language.errors.php
     * @link http://php.net/manual/en/function.set-error-handler.php
     * @link http://php.net/manual/en/class.errorexception.php
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws \ErrorException
     */
    public function errorHandler($severity, $message, $file, $line)
    {
        // If error_reporting() is set to exclude certain error messages or
        // if the [@] error control operator is used then ignore the error.
        if (!(error_reporting() & $severity)) {
            // Save the the last handled error so that if error_get_last()
            // returns the error on shutdown() it can be ignored.
            $this->last_error = array(
                'type' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line,
            );

            // Return false so that the predefined php variable $php_errormsg
            // will be set. [$php_errormsg] is only set if this function
            // returns false and the setting 'track_errors' is turned on.
            return false;
        }

        // Convert the error to an ErrorException and throw it
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Application defined error handler function for fatal errors. This function is set
     * as an error handler when the function [setup()] is called. This function is executed
     * by PHP when script execution is finishing and allows for fatal errors not caught by
     * the error handler function to be handled. This function converts the errors to an
     * ErrorException object and calls the [exceptionHandler()] function which then allows
     * for the website or application to handle the error. This function is public so PHP
     * can call it but it should not be called directly.
     *
     * @return void
     * @link http://php.net/manual/en/function.register-shutdown-function.php
     */
    public function shutdown()
    {
        // If an error is set after calling error_get_last() on shutdown then
        // the error was not previously handled. Processing the request is still
        // allowed on shutdown so handle the error as an ErrorException.
        $err = error_get_last();

        // Also if the last error was already handled by errorHandler() then ignore it.
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
     * @throws \Exception
     */
    private function sendErrorPage($response_code, $e = null, $allowed_methods = null)
    {
        // Build Error Message
        if ($response_code === 404) {
            $page_title = $this->not_found_page_title;
            $message = $this->not_found_page_message;
        } elseif ($response_code === 405) {
            $page_title = $this->method_not_allowed_title;
            $message = $this->method_not_allowed_message;
            // Use a simple string replace to show the requested and allowed methods
            $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
            $message = str_replace('{method}', $method, $message);
            $message = str_replace('{allowed_methods}', implode(', ', $allowed_methods), $message);
        } else {
            $page_title = $this->error_page_title;
            $message = $this->error_page_message;

            // Call any defined error() functions with response code and the exception
            // as function parameters. If an exception is raised by an error() function
            // then clear any error() functions and call this function again to send
            // error info about the error from the error() function. Functions will be
            // cleared on error so that they do not happen more than once.
            try {
                foreach ($this->error_callbacks as $callback) {
                    call_user_func($callback, $response_code, $e, $page_title, $message);
                }
            } catch (\Exception $callback_ex) {
                $this->error_callbacks = array();
                $this->sendErrorPage(500, $callback_ex);
            }

            // If the Exception is of type ErrorException then check for known
            // error severity levels and convert the level int value to a text
            // value that can be used for more friendly error messages in the
            // error template. This list is excluding E_ALL because it is used
            // for defining error reporting rules and E_CORE_ERROR and E_CORE_WARNING
            // because CORE errors will not be caught in a PHP Script.
            if (get_class($e) === 'ErrorException') {
                // http://php.net/manual/en/errorfunc.constants.php
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

                // Define the property [severityText] if the error level can be determined.
                $error_level = $e->getSeverity();
                if (isset($error_constants[$error_level])) {
                    $e->severityText = $error_constants[$error_level];
                }
            }
        }

        // Variables related to rendering user-defined template
        $response = null;
        $error_page = null;

        // Use a custom error/not-found template if specified by the calling application
        if (($response_code === 404 || $response_code === 405) && $this->not_found_template !== null) {
            $error_page = $this->not_found_template;
        } elseif ($this->error_template !== null) {
            $error_page = $this->error_template;
        }

        if ($error_page !== null) {
            // Catch exceptions if thrown when the user-defined error or not-found
            // template is rendered. If an exception is thrown then clear the
            // user defined error template and call this function with the render
            // error so that the default error template will be displayed.
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

        // If an error or not-found page was not rendered using a user-defined template
        // than clear header/footer/template_dir and use the default error template
        if ($response === null) {
            $this->header_templates = null;
            $this->footer_templates = null;
            $this->template_dir = null;
            $this->view_engine = null;
            $is_cli = (php_sapi_name() === 'cli');
            if ($is_cli) {
                $error_page = __DIR__ . '/Templates/error-cli.php';
            } else {
                $error_page = __DIR__ . '/Templates/error.php';
            }

            // Build the error response
            if (is_file($error_page)) {
                $response = $this->render($error_page, array(
                    'page_title' => $page_title,
                    'message' => $message,
                    'e' => $e,
                ));
            } else {
                // Automatic Unit Tests do not exist for this line because it would
                // only occur if core framework files are missing. Instead a routing
                // file [docs\unit-testing\test-no-templates.php] exists to
                // manually test this.
                $response = '<h1>' . $this->escape($page_title) . '</h1>';
                $response .= '<p>' . $this->escape($message) . '</p>';
            }
        }

        // If response headers have been defined using the PHP header function
        // and not yet sent to the client then [header_remove()] will clear
        // them. This helps prevent errors such as the error page being sent
        // with as the wrong mime-type (for example as a PNG image).
        if (!headers_sent()) {
            header_remove();
        }

        // Set status code of either 404, 405, or 500; make sure the response
        // content type is HTML, send the response, and terminate script execution.
        // Response Cookies if defined are not modified.
        $this->status_code = $response_code;
        $this->header_fields = array('Content-Type' => 'text/html; charset=UTF-8');
        $this->sendResponse($response);
        exit();
    }

    // ---------------------------------------------------------------------
    //       Allow for Dynamic Functions and Lazy Loading Properties
    //
    //    ########  ##    ## ##    ##    ###    ##     ## ####  ######
    //    ##     ##  ##  ##  ###   ##   ## ##   ###   ###  ##  ##    ##
    //    ##     ##   ####   ####  ##  ##   ##  #### ####  ##  ##
    //    ##     ##    ##    ## ## ## ##     ## ## ### ##  ##  ##
    //    ##     ##    ##    ##  #### ######### ##     ##  ##  ##
    //    ##     ##    ##    ##   ### ##     ## ##     ##  ##  ##    ##
    //    ########     ##    ##    ## ##     ## ##     ## ####  ######
    //
    // ---------------------------------------------------------------------

    /**
     * Allow for methods/functions to be added dynamically to the Application Object
     * using a PHP 'magic method'. In PHP this is called method overloading however
     * the term is used differently in PHP than most programming languages. Objects
     * can always have properties dynamically added however if a function/closure is
     * dynamically added it cannot be called unless the magic method [__call()] is
     * implemented. This implementation of [__call()] allows for functions to be
     * defined in a manner similar to how a function can be added dynamically in
     * JavaScript. One difference in PHP is that a function added dynamically to
     * the Application object will not have access to the [$this] variable that
     * built-in functions have.
     *
     * JavaScript Example - This works to add a function dynamically to an object:
     *
     *     var obj = {};
     *     obj.test = function() { alert('test'); };
     *     obj.test()
     *
     * PHP Example - The property can be added but the function cannot be called
     * and the following code would trigger an error. However using the [__call()]
     * magic method if the following function were defined for a class then it
     * can be called.
     *
     *     $obj = new \stdClass;
     *     $obj->test = function() { echo 'test'; };
     *     $obj->test();
     *
     * @link http://php.net/manual/en/language.oop5.overloading.php
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        // Check if a Closure Object/Function has been assigned as a property of
        // this object instance and if so then call it otherwise throw an Exception.
        // The curly braces used below is a feature of PHP named Variable variables.
        // It is not commonly used in most PHP Scripts or well known; it allows for
        // the object property to be read dynamically by name at runtime.
        // http://docs.php.net/manual/en/language.variables.variable.php
        if (property_exists($this, $name)) {
            if ($this->{$name} instanceof \Closure) {
                return call_user_func_array($this->{$name}, $arguments);
            } else {
                throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s(), a property exists of the same name however to be called as a dynamic function from FastSitePHP it must be defined as a Closure. The current type of [%s] is [%s].', __CLASS__, $name, $name, gettype($this->{$name})));
            }
        } else {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
        }
    }

    /**
     * Return true if either a built-in or dynamic a named method exists for
     * the Application object. Typically [method_exists()] can be used however
     * this Class allows for dynamic methods to be defined.
     *
     * @link http://php.net/manual/en/function.method-exists.php
     * @param string $name
     * @return bool
     */
    public function methodExists($name)
    {
        return (
            method_exists($this, $name)
            || (property_exists($this, $name) && $this->{$name} instanceof \Closure)
        );
    }

    /**
     * PHP Magic Method which is called if a property is accessed that doesn't
     * exist on the object. It is used here to check for properties defined
     * from [lazyLoad()] and create them the first time they are used.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->lazy_load_props)) {
            $this->{$name} = call_user_func($this->lazy_load_props[$name]);
            return $this->{$name};
        }

        // Without a magic method a E_NOTICE would be triggered instead so an
        // error is triggered here as well rather than an Exception being thrown.
        trigger_error(sprintf('Undefined property %s::%s()', __CLASS__, $name), E_USER_NOTICE);
        return null;
    }

    /**
     * Define a lazy load property which will call a function the first time
     * the property is accessed. This is ideal for a site that has multiple or
     * optional database connections so they can be connected to only when used.
     *
     * @param string $name
     * @param \Closure $function
     * @return $this
     * @throws \Exception
     */
    public function lazyLoad($name, \Closure $function)
    {
        if (isset($this->lazy_load_props[$name])) {
            throw new \Exception(sprintf('Lazy Load Property is already defined [%s->%s(\'%s\')]', __CLASS__, __FUNCTION__, $name));
        }
        $this->lazy_load_props[$name] = $function;
        return $this;
    }

    // ----------------------------------------------------------------------------------
    //                          Response Functions
    //    The Application Object handles only basic Response Options and CORS; for
    //    additional features such as Caching, File Streaming, and more use the
    //    Object [\FastSitePHP\Web\Response].
    //
    //    ########  ########  ######  ########   #######  ##    ##  ######  ########
    //    ##     ## ##       ##    ## ##     ## ##     ## ###   ## ##    ## ##
    //    ##     ## ##       ##       ##     ## ##     ## ####  ## ##       ##
    //    ########  ######    ######  ########  ##     ## ## ## ##  ######  ######
    //    ##   ##   ##             ## ##        ##     ## ##  ####       ## ##
    //    ##    ##  ##       ##    ## ##        ##     ## ##   ### ##    ## ##
    //    ##     ## ########  ######  ##         #######  ##    ##  ######  ########
    //
    // ----------------------------------------------------------------------------------

    /**
     * Get or set the response status code by number (for example 200 for 'OK'
     * or 404 for 'Not Found'). By default the PHP will set a status of 200
     * so setting a status code is usually only needed for other status codes
     * other than 200. This gets sent when the response is sent to the client.
     * If this function is called without a status code passed as a parameter
     * then it will return the current status code otherwise when setting a
     * status code it will return the Application object so it can be used
     * in chainable methods.
     *
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * @param int|null $new_value (default: null)
     * @return $this|int|null
     * @throws \Exception
     */
    public function statusCode($new_value = null)
    {
        switch ($new_value)
        {
            // Make sure a status code that is supported by FastSitePHP
            // is set. Many additional status codes exist however they
            // are not commonly used by most websites or applications.
            // NOTE - if modifying this function to include additional
            // status codes then the function [sendResponse()] at the
            // bottom of this file should also be modified.
            case 200: // OK
            case 201: // Created
            case 202: // Accepted
            case 204: // No Content
            case 205: // Reset Content
            case 404: // Not Found
            case 500: // Internal Server Error
                $this->status_code = $new_value;
                return $this;
            // If only statusCode() is called without a parameter
            // then return the current value or null if not set.
            case null:
                return $this->status_code;
            // Supported by Response Object Only. Provide a helpful
            // message to the developer on how to fix this.
            case 304: // Not Modified
                throw new \Exception(sprintf('[304] is an invalid option for [%s->%s()]. Support for 304 [Not Modified] Responses are only available when calling [FastSitePHP\Web\Response()->%s] and using the Response Object as the Route\'s Return Value.', __CLASS__, __FUNCTION__, __FUNCTION__));
            default:
                throw new \Exception(sprintf('Unhandled Response Status Code for [%s->%s()]. Support for other Status Codes is available when calling [FastSitePHP\Web\Response()->%s] and using the Response Object as the Route\'s Return Value.', __CLASS__, __FUNCTION__, __FUNCTION__));
        }
    }

    /**
     * Define an HTTP Header to be sent with the Response. Additionally previously
     * defined Header fields can be read and cleared using this function. To set a
     * Header field specify both $name and $value parameters. To read the value of
     * a Header field specify only the $name parameter; if the value has been defined
     * it will be returned otherwise if it has not been defined then null will be
     * returned. To clear a Header field pass an empty string '' for the $value
     * parameter. If setting or clearing a Header field then the Application Object
     * will be returned so it can be called as a chainable method.
     *
     * The Class [\FastSitePHP\Web\Response] also has this function defined.
     * The difference is that Application version is used for basic responses
     * and headers are not validated. If a Response Object is used then headers
     * defined by the Application Object will not be sent. This function is defined
     * here so that responses can be sent without having to load and create a
     * full Response Object.
     *
     * Examples:
     *     Set the Response Header 'Content-Type' to 'text/plain'
     *     $app->header('Content-Type', 'text/plain')
     *
     *     Get the Response Header 'Content-Type' that has been set.
     *     If no value has been set then null will be returned.
     *     $value = $app->header('Content-Type')
     *
     *     Clear the Response Header 'Content-Type' that has been set
     *     $app->header('Content-Type', '')
     *
     * @param string $name
     * @param mixed $value
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function header($name, $value = null)
    {
        // Validation
        if (!is_string($name)) {
            throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The $name parameter must be defined a string but instead was defined as type [%s].', __CLASS__, __FUNCTION__, gettype($name)));
        } elseif ($name === '') {
            throw new \Exception(sprintf('The function [%s->%s()] was called with invalid parameters. The $name parameter defined as an empty string. It must instead be set to a valid header field.', __CLASS__, __FUNCTION__));
        }

        // First check for exact match, example 'Content-Type'
        $key_exists = false;
        if (isset($this->header_fields[$name])) {
            $key_exists = true;
        }

        // If not found perform a case-insensitive search of the array keys
        if (!$key_exists) {
            $name_lower_case = strtolower($name);
            foreach ($this->header_fields as $key => $data) {
                if (strtolower($key) === $name_lower_case) {
                    $name = $key;
                    $key_exists = true;
                    break;
                }
            }
        }

        // Return the header value, clear if '', or set
        if ($value === null) {
            return ($key_exists ? $this->header_fields[$name] : null);
        } elseif ($value === '') {
            unset($this->header_fields[$name]);
        } else {
            $this->header_fields[$name] = $value;
        }

        // When setting or clearing return Object Instance
        return $this;
    }

    /**
     * Return an array of Headers fields defined from the header() function
     * that will be or have been sent with the HTTP Response if a basic
     * string or array response is used. If a Response Object is used
     * then headers defined by the Application Object will not be sent.
     *
     * @return array
     */
    public function headers()
    {
        return $this->header_fields;
    }

    /**
     * Set Response Headers that tell the browser or client to not cache the response.
     *
     * This function defines the following response headers:
     *     Cache-Control: no-cache, no-store, must-revalidate
     *     Pragma: no-cache
     *     Expires: -1
     *
     * For most clients and all modern browsers 'Cache-Control' will take precedence
     * over 'Expires' when both tags exist. The 'Expires' header per HTTP Specs must
     * be defined as an HTTP-Date value, and when an invalid value such as '0' is used
     * then the client should treat the content as already expired, however in reality
     * certain older versions of Internet Explorer may end up caching the response if
     * '0' is used so '-1' is used for the 'Expires' header. At the time of writing both
     * Google and Microsoft use 'Expires: -1' for their homepages. The header 'Pragma'
     * is for old HTTP 1.0 clients that do not support either 'Cache-Control' or 'Expires'.
     *
     * This function exists in both [FastSitePHP\Application] and [FastSitePHP\Web\Response]
     * classes; calling the function from the Application object specifies the headers only
     * when a route returns a basic response and calling the function from the Response
     * object specifies the headers only when the route returns a Response object.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21
     * @link http://blogs.msdn.com/b/ieinternals/archive/2012/01/31/avoid-using-meta-to-specify-expires-or-pragma-in-html-markup.aspx
     * @param bool|mixed $no_cache    If null the current option is returned. If true then cache headers are sent with the response, and if false then they are not. Defaults to true.
     * @return $this|mixed
     */
    public function noCache($no_cache = true)
    {
        if ($no_cache === null) {
            return $this->no_cache;
        }
        $this->no_cache = $no_cache;
        return $this;
    }

    /**
     * Get or set a values for Cross-Origin Resource Sharing (CORS) Response Headers.
     * For security reasons browsers will restrict content that is from a different domain
     * when using JavaScript (for example: calling a Web Service from XMLHttpRequest).
     * CORS is a web standard that allows for restricted resources to work on domains
     * other than the domain where the resource is hosted.
     *
     * This function is flexible and allows for setting the most common header
     * 'Access-Control-Allow-Origin' as a string value or it can set multiple
     * CORS Headers by specifying an array. To clear any CORS Headers call this
     * function with an empty string and to get defined CORS Headers call this
     * function without any parameters.
     *
     * CORS Headers are sent with both the OPTIONS request method and the calling
     * method. Because OPTIONS requests are required for certain response types
     * the cors() function should often be called in a filter() function or a
     * before() event function.
     *
     * Examples:
     *     $app->cors(*);
     *
     *     $app->cors(array(
     *         'Access-Control-Allow-Origin' => '*',
     *         'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Authorization',
     *     ));
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
     * @link http://www.html5rocks.com/en/tutorials/cors/
     * @link http://www.w3.org/TR/cors/
     * @link http://www.w3.org/TR/cors/#access-control-allow-origin-response-header
     * @link https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
     * @param null|string|array $origin_or_headers
     * @return $this|null|array
     * @throws \Exception
     */
    public function cors($origin_or_headers = null)
    {
        // Return the previously set value if nothing is passed
        if ($origin_or_headers === null) {
            return $this->cors_headers;
        }

        // Clear any previously set value if an empty string
        if ($origin_or_headers === '') {
            $this->cors_headers = null;
            return $this;
        }

        // Validate the header or headers.
        // First determine [Access-Control-Allow-Origin] and check other headers.
        $origin = null;
        if (is_array($origin_or_headers)) {
            // There are 6 different CORS headers, all starting with [Access-Control-]
            $valid_headers = array(
                'Access-Control-Allow-Origin',
                'Access-Control-Allow-Credentials',
                'Access-Control-Expose-Headers',
                'Access-Control-Max-Age',
                'Access-Control-Allow-Methods',
                'Access-Control-Allow-Headers',
            );

            // Check if any other headers exist in the array, and list
            // them in the error message. This makes it easier on any
            // developer using this feature if they have an error.
            $diff = array_diff(array_map('strtolower', array_keys($origin_or_headers)), array_map('strtolower', $valid_headers));
            if (count($diff) !== 0) {
                // In PHP versions 5.3, 5.4 and less than 5.4.32, or 5.5 and less than 5.5.16
                // The function ucwords does not include the delimiter parameter function
                // so check which version can be used and handle appropriately.
                $fn = new \ReflectionFunction('ucwords');
                if (count($fn->getParameters()) === 1) {
                    $cap_case_header = function($value) {
                        return str_replace(' ', '-', ucwords(str_replace('-', ' ', $value)));
                    };
                } else {
                    $cap_case_header = function($value) { return ucwords($value, '-'); };
                }
                $diff = array_map($cap_case_header, $diff);
                throw new \Exception(sprintf('Unsupported headers [%s] were specified when the function [%s->%s()] was called. The only headers that this function supports are valid headers for Cross-Origin Resource Sharing (CORS): [%s]', implode('], [', array_values($diff)), __CLASS__, __FUNCTION__, implode('], [', $valid_headers)));
            }

            // Get the [Access-Control-Allow-Origin] Header
            foreach ($origin_or_headers as $key => $value) {
                if (strtolower($key) === 'access-control-allow-origin') {
                    $origin = $value;
                    break;
                }
            }

            // Validate Specific Headers
            // This does not validate every single header but rather validates errors that a
            // developer might not catch or be aware of. For example [Access-Control-Allow-Methods]
            // is not validated because a site might use custom or un-common methods and if not
            // defined correctly then the CORS web service would likely fail with a clear error
            // from the browser.
            foreach ($origin_or_headers as $key => $value) {
                switch (strtolower($key)) {
                    // Validation for [Access-Control-Allow-Credentials], for more info see:
                    //   https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials
                    //   https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Requests_with_credentials
                    case 'access-control-allow-credentials':
                        if ($value !== 'true' && $value !== true) {
                            throw new \Exception(sprintf('The only valid value for Header [Access-Control-Allow-Credentials] is [true]; if you do not need the value to be set to true then do not include it when calling [%s->%s()]. The value that causes this error was: [%s].', __CLASS__, __FUNCTION__, $value));
                        } else if ($origin === '*') {
                            throw new \Exception(sprintf('When using header [Access-Control-Allow-Credentials => true] specified from [%s->%s()] the server must respond using an origin rather than specifying a [*] wildcard. The requested origin can be obtained from [FastSitePHP\Web\Request->origin()]. You can then use the value from the request header to validate if it is a valid request and then send the origin back using the [Access-Control-Allow-Origin] header.', __CLASS__, __FUNCTION__));
                        }
                        break;
                    // Validation for [Access-Control-Max-Age] which is the maximum number of seconds
                    // that the results can be cached for. Valid values are (int) 0 to 86400 seconds (24 hours).
                    // This is because 86400 seconds is the max allowed by Firefox. At the time of writing
                    // Chrome allows for only 600 seconds so ideally if using this header the value should be low.
                    //   https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
                    //   https://cs.chromium.org/chromium/src/third_party/WebKit/Source/core/loader/CrossOriginPreflightResultCache.cpp
                    case 'access-control-max-age':
                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            throw new \Exception(sprintf('Invalid field value data type for [Access-Control-Max-Age] of [%s] from [%s->%s()]. When this header is specified the value must be an integer (number) or a string that converts to an integer or a string that converts to an integer.', gettype($value), __CLASS__, __FUNCTION__));
                        }
                        $int_val = (int)$value;
                        if ($int_val < 0 || $int_val > 86400) {
                            throw new \Exception(sprintf('Invalid value for [Access-Control-Allow-Origin] of [%s] from [%s->%s()]. The field value must be a number between 0 and 86400 (seconds in a 24 hour time frame). Different browsers handle this differently for example versions of Firefox support up to 24-hours (86400 seconds) and versions of Chrome support up to 10 minutes (600 seconds).', $value, __CLASS__, __FUNCTION__));
                        }
                        break;
                }
            }
        } else {
            $origin = $origin_or_headers;
        }

        // Validate Header [Access-Control-Allow-Origin] - '*' or a single http/https
        // domain origin. The actual specs allow for multiple domains but technically
        // as of late 2015 it won't work with all widely used web browsers and only single
        // domain origin is recommended. This validation doesn't validate that the origin
        // is a real domain url but rather that it looks like it likely is.
        if ($origin !== '*') {
            // Error - Wrong Type
            if (!is_string($origin)) {
                throw new \Exception(sprintf('Invalid variable type for [Access-Control-Allow-Origin] of [%s] from [%s->%s()], the value must be set and must be a string. Valid parameters for [$origin_or_headers] are [string|array|null]; refer to documentation for usage and examples.', gettype($origin), __CLASS__, __FUNCTION__));
            // Error - not starting with 'http://' or 'https://'
            } elseif (strpos($origin, 'http://') !== 0 && strpos($origin, 'https://') !== 0) {
                throw new \Exception(sprintf('Invalid value for [Access-Control-Allow-Origin] of [%s] from [%s->%s()]. When using the [cors()] function the value if not [*] must begin with either [http://] or [https://].', $origin, __CLASS__, __FUNCTION__));
            // Error - multiple domains likely specified
            } elseif(strpos($origin, ' ') !== false || strpos($origin, ',') !== false || strpos($origin, ';') !== false) {
                throw new \Exception(sprintf('Invalid value for [Access-Control-Allow-Origin] of [%s] from [%s->%s()]. When using the [cors()] function the URL value must contain only one domain and it appears multiple domains were specified.', $origin, __CLASS__, __FUNCTION__));
            // Error - likely a full url rather than {protocol}{domain}
            // Example [http://domain.tld] vs [http://domain.tld/page]
            } elseif(strpos($origin, '/', 8) !== false) {
                throw new \Exception(sprintf('Invalid value for [Access-Control-Allow-Origin] of [%s] from [%s->%s()]. When using the [cors()] function the URL value must contain only the protocol and domain rather than a full URL (e.g.: [http://domain.tld] vs [http://domain.tld/page]).', $origin, __CLASS__, __FUNCTION__));
            }
        }

        // The headers are valid so set the value and return this Application Object Instance
        if (is_string($origin_or_headers)) {
            $this->cors_headers = array('Access-Control-Allow-Origin' => $origin_or_headers);
        } else {
            $this->cors_headers = $origin_or_headers;
        }
        return $this;
    }

    /**
     * Define a cookie to be sent with the response along with the response headers.
     * Internally this calls the PHP function setcookie(). To delete a cookie use
     * the function [clearCookie()]. To read cookies use the [cookie()] function
     * of the [FastSitePHP\Web\Request] Object or use the PHP superglobal array $_COOKIE.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     * @link http://php.net/manual/en/features.cookies.php
     * @link http://php.net/manual/en/reserved.variables.cookies.php
     * @param string $name
     * @param string $value
     * @param int $expire    Defaults to 0 which makes the cookie expire at the end of the session
     * @param string $path (default: '')
     * @param string $domain (default: '')
     * @param bool $secure (default: false)
     * @param bool $httponly (default: false)
     * @return $this
     */
    public function cookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        // Check if the cookie was already defined
        $item_to_remove = null;
        for ($n = 0, $m = count($this->response_cookies); $n < $m; $n++) {
            if ($this->response_cookies[$n]['name'] === $name
                && $this->response_cookies[$n]['path'] === $path
                && $this->response_cookies[$n]['domain'] === $domain
                && $this->response_cookies[$n]['secure'] === $secure
                && $this->response_cookies[$n]['httponly'] === $httponly
            ) {
                $item_to_remove = $n;
                break;
            }
        }

        // If so remove it from the array
        if ($item_to_remove !== null) {
            array_splice($this->response_cookies, $item_to_remove, 1);
        }

        // Add the cookie to the end of the array and return the Application Object Instance.
        // Cookie validation is not handled by FastSitePHP but rather logic is in place so
        // that if there is an error when setcookie() is called on the response then the
        // error can be handled by the application.
        $this->response_cookies[] = array(
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        );
        return $this;
    }

    /**
     * Send an empty value for a named cookie and expired time to tell the browser or
     * client to clear the cookie.
     *
     * @param string $name
     * @param string $path (default: '')
     * @param string $domain (default: '')
     * @param bool $secure (default: false)
     * @param bool $httponly (default: false)
     * @return $this
     */
    public function clearCookie($name, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        return $this->cookie($name, '', -1, $path, $domain, $secure, $httponly);
    }

    /**
     * Return the Array of Cookies that will be sent with the response.
     *
     * @return array
     */
    public function cookies()
    {
        return $this->response_cookies;
    }

    // --------------------------------------------------------------------------------------
    //                 Template Rendering Functions
    //
    //    ########  ######## ##    ## ########  ######## ########  #### ##    ##  ######
    //    ##     ## ##       ###   ## ##     ## ##       ##     ##  ##  ###   ## ##    ##
    //    ##     ## ##       ####  ## ##     ## ##       ##     ##  ##  ####  ## ##
    //    ########  ######   ## ## ## ##     ## ######   ########   ##  ## ## ## ##   ####
    //    ##   ##   ##       ##  #### ##     ## ##       ##   ##    ##  ##  #### ##    ##
    //    ##    ##  ##       ##   ### ##     ## ##       ##    ##   ##  ##   ### ##    ##
    //    ##     ## ######## ##    ## ########  ######## ##     ## #### ##    ##  ######
    //
    // --------------------------------------------------------------------------------------

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
        // To see what characters are encoded by this function
        // run the commented two lines below in an empty PHP file
        //   header('Content-type: text/plain');
        //   echo json_encode(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES, 'UTF-8'), JSON_PRETTY_PRINT);
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', true);
    }

    /**
     * Define a callback closure function for rendering template files using variables
     * passed by the application. This allows for custom rendering engines to be used
     * in addition to the native PHP and Text file templates. The required callback
     * definition for defining a rendering engine is [function($file, array $data = null)];
     * the actual variable names can be different however the array typehint and
     * optional 2nd parameter must be defined. For an example of defining a custom
     * rending engine see Quick Reference Page.
     *
     * @param \Closure $callback
     * @return $this
     * @throws \Exception
     */
    public function engine(\Closure $callback)
    {
        // Validate parameters of the $callback function using Reflection.
        // The required format is:
        //   function($file, array $data = null)
        $reflection = new \ReflectionFunction($callback);
        $param_array = $reflection->getParameters();

        if (count($param_array) !== 2) {
            throw new \Exception(sprintf('Wrong number of parameters for the $callback closure definition defined from [%s->%s()]. The closure should be defined as [function($file, array $data = null)]', __CLASS__, __FUNCTION__));
        } elseif ($param_array[0]->isDefaultValueAvailable()) {
            throw new \Exception(sprintf('Invalid parameters for the $callback closure definition defined from [%s->%s()]. The first parameter was defined as an optional value. The closure should be defined as [function($file, array $data = null)]', __CLASS__, __FUNCTION__));
        } elseif (!$param_array[1]->isArray()) {
            throw new \Exception(sprintf('Invalid parameters for the $callback closure definition defined from [%s->%s()]. The second parameter was not defined with an array typehint. The closure should be defined as [function($file, array $data = null)]', __CLASS__, __FUNCTION__));
        } elseif (!($param_array[1]->isDefaultValueAvailable() && $param_array[1]->getDefaultValue() === null)) {
            throw new \Exception(sprintf('Invalid parameters for the $callback closure definition defined from [%s->%s()]. The second parameter was not defined as an optional value. The closure should be defined as [function($file, array $data = null)]', __CLASS__, __FUNCTION__));
        }

        // Set the rendering engine
        $this->view_engine = $callback;
        return $this;
    }

    /**
     * Render a single template file or an array of template files using variables specified
     * in the $data parameter and also variables defined from the [locals] property. In addition
     * templates rendered using PHP or a custom rendering engine will have the variable [$app]
     * defined as a reference to this Application Object Instance. The default format used for
     * templates are PHP templates, however including plain text files works as well. To override
     * the default view engine define one using the [engine()] function before calling [render()].
     * Template files call be specified by name as long as the property [template_dir] is
     * defined. Addition properties related to this function are [header_templates],
     * [footer_templates], [error_template], and [not_found_template].
     *
     * @param array|string $files
     * @param array|null $data
     * @return string
     * @throws \Exception
     */
    public function render($files, array $data = null)
    {
        // Return value from Custom View Engine if defined
        if ($this->view_engine !== null) {
            return call_user_func(
                $this->view_engine,
                $files,
                array_merge(array('app' => $this), $this->locals, (array)$data)
            );
        }

        // If [template_dir] is specified but does
        // not end with a '/' character then add it
        if (is_string($this->template_dir) && strlen($this->template_dir) > 0 && substr($this->template_dir, -1, 1) !== '/') {
            $this->template_dir .= '/';
        }

        // Cast template properties to an array if string and merge the arrays.
        // '$__' is used on several variables to prevent overwriting of user
        // variables passed in [$data] or from the [locals] property.
        $templates = array_merge((array)$this->header_templates, (array)$files, (array)$this->footer_templates);
        $__template_files = array();

        // Validate each template file and build a new array of full file paths
        foreach ($templates as $template_file) {
            // Cast template_dir to '' if null, this allows
            // for full file paths to be specified
            $file_path = (string)$this->template_dir . $template_file;

            // Make sure the file exists
            if (!is_file($file_path)) {
                throw new \Exception('Template file was not found: ' . $file_path);
            }

            // Add file to the new array
            $__template_files[] = $file_path;
        }

        // Make sure there is a template to render
        if (count($__template_files) === 0) {
            throw new \Exception(sprintf('The function [%s->%s()] was called without template file specified to render.', __CLASS__, __FUNCTION__));
        }

        // Call any user defined events from the [onRender()] function
        try {
            foreach ($this->render_callbacks as $callback) {
                call_user_func($callback);
            }
        } catch (\Exception $ex) {
            $this->render_callbacks = array();
            throw $ex;
        }

        // Clear Previous Output, this prevents duplicate headers on template parse errors
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Start new output buffering
        ob_start();

        // Define [$app] and extract variables from the $locals property and the
        // $data parameters to the local scope so they can be used when the templates
        $app = $this;
        extract($this->locals);
        if ($data !== null) {
            extract($data);
        }

        // Process each template as a required file
        foreach ($__template_files as $__template_file) {
            include $__template_file;
        }

        // Return the result and clear the output buffer
        return ob_get_clean();
    }

    /**
     * Render the html error template using a custom page title, message,
     * and optional exception. This function only renders the HTML and
     * does not set status code or send the response.
     *
     * @param string $page_title
     * @param string $message
     * @param null|\Exception|\Throwable $e
     * @return string
     */
    public function errorPage($page_title, $message, $e = null)
    {
        // Return user-defined returned HTML error page if one is defined
        if ($this->error_template !== null) {
            return $this->render($this->error_template, array(
                'page_title' => $page_title,
                'message' => $message,
                'e' => $e,
            ));
        }

        // Return default error template, related properties are
        // temporarily set to null during this call.
        $header_templates = $this->header_templates;
        $footer_templates = $this->footer_templates;
        $template_dir = $this->template_dir;
        $view_engine = $this->view_engine;
        $this->header_templates = null;
        $this->footer_templates = null;
        $this->template_dir = null;
        $this->view_engine = null;
        $error_page = __DIR__ . '/Templates/error.php';

        if (is_file($error_page)) {
            $html = $this->render($error_page, array(
                'page_title' => $page_title,
                'message' => $message,
                'e' => $e,
            ));
            $this->header_templates = $header_templates;
            $this->footer_templates = $footer_templates;
            $this->template_dir = $template_dir;
            $this->view_engine = $view_engine;
            return $html;
        }

        // If the default template does not exist then return basic HTML.
        return '<h1>' . $this->escape($page_title) . '</h1><p>' . $this->escape($message) . '</p>';
    }

    /**
     * Set the status code to 404 'Not found' and render and return the 404 error template.
     * This function will render template specified in the property [not_found_template]
     * if one is defined otherwise the default 404 page. Custom [page_title] and [message]
     * variables can be defined for the template from properties
     * [not_found_page_title] and [not_found_page_message].
     *
     * @return string
     */
    public function pageNotFound()
    {
        // Set Response Status to 404 'Not found'
        // and make sure the Content Type is HTML
        $this
            ->statusCode(404)
            ->header('Content-Type', 'text/html; charset=UTF-8');

        // Default text, same as the default 404 page from [sendErrorPage()].
        // The text is duplicated here to avoid it from being initialized if not needed.
        $page_title = ($this->not_found_page_title ?: 'Page Not Found');
        $message = $this->not_found_page_message; //($this->not_found_page_message ?: 'The requested page could not be found.');

        // If a custom 404 template is defined then return that
        if ($this->not_found_template !== null) {
            return $this->render($this->not_found_template, array(
                'page_title' => $page_title,
                'message' => $message,
            ));
        }

        // If an error or not-found page was not rendered using a user-defined template
        // than clear header/footer/template_dir and use the default error template
        $this->header_templates = null;
        $this->footer_templates = null;
        $this->error_template = null;
        $this->template_dir = null;
        $this->view_engine = null;
        $error_page = __DIR__ . '/Templates/error.php';

        if (is_file($error_page)) {
            return $this->render($error_page, array(
                'page_title' => $page_title,
                'message' => $message,
            ));
        }

        // If the default template does not exist then return basic HTML.
        // Automatic Unit Tests do not exist for this line because it would
        // only occur if core framework files are missing. Instead a routing
        // file [docs\unit-testing\test-no-templates.php] exists to
        // manually test this.
        return '<h1>' . $this->escape($page_title) . '</h1><p>' . $this->escape($message) . '</p>';
    }

    /**
     * Send a 404 'Not found' response to the client and end script execution.
     * This uses the same template that would be returned from calling
     * [$app->pageNotFound()].
     * 
     * @return void
     */
    public function sendPageNotFound()
    {
        $this->sendResponse($this->pageNotFound());
        exit();
    }

    // -------------------------------------------------------------
    //                   Application Events
    //
    //    ######## ##     ## ######## ##    ## ########  ######
    //    ##       ##     ## ##       ###   ##    ##    ##    ##
    //    ##       ##     ## ##       ####  ##    ##    ##
    //    ######   ##     ## ######   ## ## ##    ##     ######
    //    ##        ##   ##  ##       ##  ####    ##          ##
    //    ##         ## ##   ##       ##   ###    ##    ##    ##
    //    ########    ###    ######## ##    ##    ##     ######
    //
    // -------------------------------------------------------------

    /**
     * Add closure functions that will be called from the [run()] function prior
     * to any routes being matched. Closure functions passed to the [before()]
     * function should be defined as [function()] because no parameters are passed.
     * If multiple functions are defined then they are called in the order that
     * they were added. An example of using a [before()] function is to check
     * the session for logged in user permissions that can then be checked against
     * route filter functions to see if the user has access to the request resource.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function before(\Closure $callback)
    {
        $this->before_callbacks[] = $callback;
        return $this;
    }

    /**
     * Add closure functions that will be called from the [run()] function after
     * all routes have been checked with no routes matching the requested resource.
     * Closure functions passed to the [notFound()] function take no parameters
     * and if they return a response then it be handled as a standard route and
     * will call any defined [beforeSend()] functions afterwards. If no value is
     * returned from the function then each function is checked in order added and
     * if none of the [notFound()] functions return a response then a 404 'Not found'
     * response is sent to the client. Examples of using a [notFound()] function
     * would be to define rules dynamic routing where the application would handle
     * routes mapped to dynamic controllers or to log 404 response codes.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function notFound(\Closure $callback)
    {
        $this->not_found_callbacks[] = $callback;
        return $this;
    }

    /**
     * Add closure functions that will be called from the [run()] function after
     * a route has been matched to the requested resource. Closure functions passed
     * to the [beforeSend()] function should be defined as [function($content)]
     * and they must return a response otherwise a 404 'Not found' response will be
     * sent to the client. The [$content] parameter defined in the callback is the
     * contents of the response that will be sent to the client. If multiple
     * functions are defined then they are called in the order that they were added.
     * An example of using a [beforeSend()] function would be adding a CSRF Token
     * (Cross-Site Request Forgery) to all html forms and then also adding
     * a [before()] function that checks if the token is present on each Form POST.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function beforeSend(\Closure $callback)
    {
        $this->before_send_callbacks[] = $callback;
        return $this;
    }

    /**
     * Add closure functions that will be called from the [run()] function after
     * the response has been sent to the client. Closure functions passed to the
     * [after()] function should be defined as [function($content)]; the [$content]
     * parameter defined in the callback is the contents of the response that was
     * sent to the client. If multiple functions are defined then they are called
     * in the order that they were added. If FastSitePHP is set to handle errors
     * and exceptions from the [setup()] function then functions defined here get
     * called after the error response has been sent. The only way that [after()]
     * functions will not get called is if there script is terminated early from
     * PHP's exit() statement or if error handling is not setup and an error occurs.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function after(\Closure $callback)
    {
        $this->after_callbacks[] = $callback;
        return $this;
    }

    /**
     * Add closure functions that will be called from the private function
     * [sendErrorPage()] function if an error or an exception occurs and
     * the FastSitePHP is set to handle errors and exceptions from the [setup()]
     * function. Closure functions passed to the [error()] function should be
     * defined as [function($response_code, $e, $page_title, $message)].
     * The [error()] function allow for an application to log error responses
     * however it does not prevent the error response from rendering. To override
     * the error template and 500 status code and send a different response
     * a closure function defined here would need to handle the response
     * and call PHP's exit() statement.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function error(\Closure $callback)
    {
        $this->error_callbacks[] = $callback;
        return $this;
    }

    /**
     * Add closure functions that will be called whenever [render()] is called.
     * The events will run before templates are rendered but once template paths
     * are confirmed to be valid. This is usefull for making sure that specific
     * variables are included in [$app->locals] prior to template rendering.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function onRender(\Closure $callback)
    {
        $this->render_callbacks[] = $callback;
        return $this;
    }

    // ---------------------------------------------------------------------
    //                   Routing Functions
    //
    //    ########   #######  ##     ## ######## #### ##    ##  ######
    //    ##     ## ##     ## ##     ##    ##     ##  ###   ## ##    ##
    //    ##     ## ##     ## ##     ##    ##     ##  ####  ## ##
    //    ########  ##     ## ##     ##    ##     ##  ## ## ## ##   ####
    //    ##   ##   ##     ## ##     ##    ##     ##  ##  #### ##    ##
    //    ##    ##  ##     ## ##     ##    ##     ##  ##   ### ##    ##
    //    ##     ##  #######   #######     ##    #### ##    ##  ######
    //
    // ---------------------------------------------------------------------

    /**
     * Load a PHP file based on the requested URL and an optional condition
     * closure function. The [$url_path] parameter accepts the starting part
     * of a URL. For example [/api] will match all requested URL's starting
     * with [/api]. This allows for routes or functions related to specific
     * routes to be loaded only if they are needed. The optional condition
     * function should return true or false if defined and allows for the
     * mount path to be skipped even if the requested URL matches. An example
     * of this would be allowing for certain routes to only load on specific
     * environments such as localhost. The [$file] parameter accepts either
     * a file name which will then cause the file to be loaded in the same
     * directory of the calling file or a full file path.
     *
     * @param string $url_path
     * @param string $file
     * @param \Closure|string|null $condition
     * @return $this
     * @throws \Exception
     */
    public function mount($url_path, $file, $condition = null)
    {
        // Check if the start of the requested url matches the mount path.
        // For example '/admin/login' would load [mount('/admin/', $file)].
        // If [case_sensitive_urls = false] then '/ADMIN' would match '/admin'.
        if (strpos($url_path, ':') !== false) {
            // Handle variables in the mount path by replacing them
            // with values from the requested path. Example [mount('/:lang/documents')].
            $mount_components = explode('/', $url_path);
            $path_components = explode('/', $this->requestedPath());
            $path_count = count($path_components);
            $new_path = array();
            for ($n = 0, $m = count($mount_components); $n < $m; $n++) {
                if (strpos($mount_components[$n], ':') === 0 && $n < $path_count) {
                    $new_path[] = $path_components[$n];
                } else {
                    $new_path[] = $mount_components[$n];
                }
            }
            $url_path = implode('/', $new_path);
        }
        if ($this->case_sensitive_urls) {
            $pos = strpos($this->requestedPath(), $url_path);
        } else {
            $pos = stripos($this->requestedPath(), $url_path);
        }

        // The condition will never be evaluated and the file will never be
        // checked or loaded unless the requested url matches the mount path.
        if ($pos === 0) {
            // If a condition is defined then call the closure function
            // and if it returns false then do not load the file.
            // This is useful for allowing certain files to only be
            // loaded in a specific environment such as localhost.
            if ($condition !== null) {
                list($valid_callback, $result) = $this->callMiddleware($condition);
                if ($valid_callback) {
                    if ($result === false) {
                        return $this;
                    }
                } else {
                    throw new \Exception(sprintf('Mount condition for URL [%s] was defined as a [%s] but it should be defined as either a Closure function or a string in the format of \'Class.method\'.', $url_path, gettype($condition)));
                }
            }

            // Either a file name or full file path can be passed as the parameter
            // First check for only a file name being passed as the parameter.
            if (strpos($file, '/') === false && strpos($file, '\\') === false) {
                // Look for the file in the directory of the file that called this function
                $file_name = $file;

                // Depending upon the version of PHP call debug_backtrace using
                // different options, this is to get the minimal amount of info
                // needed and to use the least amount of memory possible.
                if (version_compare(PHP_VERSION, '5.4.0', '<')) {
                    $backtrace = debug_backtrace(false);
                } else {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
                }

                // Build a full file path of the calling file's directory and the file name
                $calling_file = $backtrace[0]['file'];
                $dir_path = dirname($calling_file);
                $file = $dir_path . '/' . $file_name;

                // See if the file exists
                if (!is_file($file)) {
                    throw new \Exception(sprintf('Error calling [FastSitePHP\mount()]: File [%s] specified for mount path [%s] was not found in the directory [%s] or permissions are set so the file is not visible to PHP.', $file_name, $url_path, $dir_path));
                }
            // For full file Paths validate that the file can be loaded
            } elseif (!is_file($file)) {
                throw new \Exception(sprintf('Error calling [FastSitePHP\mount()]: File Path [%s] specified for mount path [%s] does not exists or permissions are set so the file is not visible to PHP.', $file, $url_path));
            }

            // Make sure the $app variable will be available in the loaded file
            // and then load the the file using the include statement.
            $app = $this;
            include $file;
        }

        // Return the Application Instance Object
        return $this;
    }

    /**
     * Add a route for an HTTP Request, if the parameter $method is not
     * specified then the route will match all requests with the matching
     * url. To map to a specific HTTP method the method would be included
     * in the parameter (e.g.: 'GET') or in the case of common methods
     * the function named the same name as the route could be used instead
     * [e.g.: $app->get($path, $func)]. When the route is matched
     * [e.g.: requested url = '/about' and route = $app->route('/about', $callback)]
     * then the callback method will get called based on
     * filter logic from the $app->run() function.
     *
     * @param string $pattern
     * @param \Closure|string $callback
     * @param string|null $method (default: null)
     * @return Route
     */
    public function route($pattern, $callback, $method = null)
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
     * @param \Closure|string $callback
     * @return Route
     */
    public function get($pattern, $callback)
    {
        return $this->route($pattern, $callback, 'GET');
    }

    /**
     * Add a route for an HTTP 'POST' Request
     *
     * @param string $pattern
     * @param \Closure|string $callback
     * @return Route
     */
    public function post($pattern, $callback)
    {
        return $this->route($pattern, $callback, 'POST');
    }

    /**
     * Add a route for an HTTP 'PUT' Request
     *
     * @param string $pattern
     * @param \Closure|string $callback
     * @return Route
     */
    public function put($pattern, $callback)
    {
        return $this->route($pattern, $callback, 'PUT');
    }

    /**
     * Add a route for an HTTP 'DELETE' Request
     *
     * @param string $pattern
     * @param \Closure|string $callback
     * @return Route
     */
    public function delete($pattern, $callback)
    {
        return $this->route($pattern, $callback, 'DELETE');
    }

    /**
     * Add a route for an HTTP 'PATCH' Request
     *
     * @link http://tools.ietf.org/html/rfc5789
     * @param string $pattern
     * @param \Closure|string $callback
     * @return Route
     */
    public function patch($pattern, $callback)
    {
        return $this->route($pattern, $callback, 'PATCH');
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
     * Redirect the user to another page or site. This must be called prior to headers
     * and content being sent to the user. Calling this function ends the script execution
     * however any events defined by the [after()] function will run.
     *
     * Status Code can optionally be specified as the 2nd parameter. The default Status Code
     * used is [302 'Found'] (Temporary Redirect). If Status Code [301 'Moved Permanently']
     * is used Web Browsers will typically cache the result so careful testing and consideration
     * should be done if using a Status Code of 301. Other supported Status Codes are:
     * [303 'See Other'], [307 'Temporary Redirect'], and [308 'Permanent Redirect'].
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
     * @link http://en.wikipedia.org/wiki/URL_redirection
     * @param string $url
     * @param int $status_code
     * @throws \Exception
     */
    public function redirect($url, $status_code = 302)
    {
        // Validation
        if (headers_sent()) {
            throw new \Exception(sprintf('Error trying to redirect from [%s->%s()] because Response Headers have already been sent to the client.', __CLASS__, __FUNCTION__));
        } else if (gettype($url) !== 'string') {
            throw new \Exception(sprintf('Invalid parameter type [$url] for [%s->%s()], expected a [string] however a [%s] was passed.', __CLASS__, __FUNCTION__, gettype($url)));
        } else if ($url === '') {
            throw new \Exception(sprintf('Invalid parameter for [%s->%s()], [$url] cannot be an empty string.', __CLASS__, __FUNCTION__));
        } else if (strpos($url, "\n") !== false) {
            throw new \Exception(sprintf('Invalid parameter for [%s->%s()], [$url] should be in the format of a URL understood by the client and cannot contain a line break. The URL passed to this function included a line break character.', __CLASS__, __FUNCTION__));
        }

        // Supported Status Codes
        $status_code_text = array(
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
        );

        if (!isset($status_code_text[$status_code])) {
            throw new \Exception(sprintf('Invalid [$status_code = %s] specified for [%s->%s()]. Supported Status Codes are [%s].', $status_code, __CLASS__, __FUNCTION__, implode(', ', array_keys($status_code_text))));
        }

        // Get the Request Method
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');

        // Build the Response Body. This is not actually required and using a Web Browser
        // the end user would never see this, however RFC 2616 recommends that the body
        // of a Redirect Response should include a short note with a link to the new URI
        // except in when the request method was HEAD.
        $content = null;
        if ($method !== 'HEAD') {
            $content = '<h1>' . $status_code_text[$status_code] . '</h1>';
            $content .= '<p>Redirecting to <a href="' . $this->escape($url) . '">' . $this->escape($url) . '</a></p>';
        }

        // Send the Response
        header('Location: ' . $url, true, $status_code);
        if ($content !== null) {
            echo $content;
        }
        $this->runAfterEvents($content);
        exit();
    }

    /**
     * Return the Requested Path (Page only, excluding site, base directory, query strings, etc).
     * This will return the same result regardless of the Web Server used and it will be
     * based on where the [index.php] or entry PHP file is located.
     *
     * Request Examples:
     *     https://www.example.com/index.php/test/test?test=test
     *     https://www.example.com/index.php/test/test
     *     https://www.example.com/test/test/
     *     https://www.example.com/test/test
     *     https://www.example.com/site1/index.php/test/test
     *
     * Returns:
     *     '/test/test'
     *
     * In the above example both '/test/test/' and '/test/test' return '/test/test' when
     * using the default property [$app->strict_url_mode = false] otherwise the exact
     * URL would be returned.
     *
     * @return string|null
     */
    public function requestedPath()
    {
        // Use PATH_INFO if set, this will typically be on newer
        // versions of Apache depending on the config.
        $url = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null);

        // IIS, Nginx, and some versions of Apache will use the code block below.
        // IIS and Apache will likely use [$url === null] and Nginx will use [$url === ''].
        if ($url === null || $url === '') {
            // If running from CLI the URI will not be set
            if (!isset($_SERVER['REQUEST_URI'])) {
                return null;
            }
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $script_name = $_SERVER['SCRIPT_NAME'];

            // For cases where URL contains the PHP page [ex: /public/index.php/test]
            // and SCRIPT_NAME is similar to [/public/index.php].
            // If the base script file name [ex: index.php] is not found in the
            // script name then the request could be comming from the PHP built-in
            // Webserver using a router page so keep the existing URL.
            if (strpos($url, $script_name) === 0) {
                if (strpos($script_name, basename($_SERVER['SCRIPT_FILENAME'])) !== false) {
                    $url = substr($url, strlen($script_name));
                }
            // For cases where URL is similar to [/public/test]
            // and SCRIPT_NAME is similar to [/public/index.php].
            } elseif ((string)$script_name !== '') {
                $data = explode('/', $script_name);
                $base_name = $data[count($data) - 1];
                $script_name = substr($script_name, 0, strlen($script_name) - strlen($base_name) - 1);
                $url = substr($url, strlen($script_name));
            }
        }

        // Fix URL so Home Page is always '/' and other pages do not end with a '/' character.
        // Url could be type boolean after substr() so cast as string when checking for blank.
        if ((string)$url === '') {
            $url = '/';
        // else if the last character ends with '/' and [strict_url_mode]
        // is set to false (the default value) then truncate the last character.
        } elseif (!$this->strict_url_mode && strlen($url) > 1 && substr($url, -1) === '/') {
            $url = substr($url, 0, strlen($url) - 1);
        }
        return $url;
    }

    /**
     * Return the Site Root URL; the URL returned is the base URL for all pages.
     *
     * Examples:
     *
     *     # [index.php] specified in the URL
     *     Request: https://www.example.com/index.php/page
     *     Request: https://www.example.com/index.php/page/page2
     *     Returns: https://www.example.com/index.php/
     *
     *     # [index.php] Located in Root Folder
     *     Request: https://www.example.com/page
     *     Request: https://www.example.com/page/page2
     *     Returns: https://www.example.com/
     *
     *     # [index.php] Located under [site1]
     *     Request: https://www.example.com/site1/page
     *     Request: https://www.example.com/site1/page/page2
     *     Returns: https://www.example.com/site1/
     *
     * @return string|null
     */
    public function rootUrl()
    {
        // If running from CLI the URI will not be set
        if (!isset($_SERVER['REQUEST_URI'])) {
            return null;
        }

        // Get URL path from URL and the Script Name
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $script_name = $_SERVER['SCRIPT_NAME'];

        // For cases where URL is similar to [/web/index.php/test/?test=test]
        // and SCRIPT_NAME is similar to [/web/index.php]. If both [$url] and
        // [$script_name] equal '/' then the PHP built-in server is likely being
        // in a manner similar to "http://localhost:3000" where the URL does not end
        // with a forward slash. '/'
        if (strpos($url, $script_name) === 0 && !($url === $script_name && $url === '/')) {
            $url = substr($url, 0, strlen($script_name)) . '/';
        // For cases where URL is similar to [/web/test/?test=test]
        // and SCRIPT_NAME is similar to [/web/index.php]
        } elseif ((string)$script_name !== '') {
            $data = explode('/', $script_name);
            $base_name = $data[count($data) - 1];
            $script_name = substr($script_name, 0, strlen($script_name) - strlen($base_name) - 1);
            $url = substr($url, 0, strlen($script_name)) . '/';
        }

        // Add the (http|https) and the host
        $is_secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
        $url = ($is_secure ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $url;

        // Return the full URL
        return $url;
    }

    /**
     * Return the Site Root Directory; the Directory returned is generally the
     * base Directory for all resources (JS, CSS, IMG Files, etc).
     *
     * Request Examples:
     *     https://www.example.com/index.php/page
     *     https://www.example.com/index.php/page/page2
     *     https://www.example.com/page
     *
     * Returns:
     *     https://www.example.com/
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
     * Define validation and conversion rules for a route variable.
     * The definition of the parameter variable defined here gets
     * checked by the function [routeMatches()] when [run()] is called.
     *
     * @param string $name
     * @param string|\Closure $validation
     * @param string|\Closure|null $converter (default: null)
     * @return $this
     * @throws \Exception
     */
    public function param($name, $validation, $converter = null)
    {
        // Validate that the name is valid for the parameter
        if (!is_string($name)) {
            throw new \Exception(sprintf('Unexpected $name variable type specified for [%s->%s()]', __CLASS__, __FUNCTION__));
        } elseif (strlen($name) < 2) {
            // A parameter name must actually be specified and not just ':'
            throw new \Exception(sprintf('$name must be longer than 2 characters when [%s->%s()] is called', __CLASS__, __FUNCTION__));
        } elseif (substr($name, 0, 1) !== ':') {
            // Having the parameter name be ':name' rather than just 'name' allows for the developer to more
            // easily search through code if needed when making changes to a named parameter.
            throw new \Exception(sprintf('$name must start with [:] when [%s->%s()] is called', __CLASS__, __FUNCTION__));
        }

        // Check for duplicates as a parameter can only be defined once
        if (isset($this->params[$name])) {
            throw new \Exception(sprintf('The $name [%s] is a duplicate and was already defined when [%s->%s()] was called', $name, __CLASS__, __FUNCTION__));
        }

        // Validate that the validation option is valid for the parameter
        if (is_string($validation)) {
            if (strlen($validation) === 0) {
                // string can be 'any|int|float|bool' or a valid regular expression.
                // There is not a good method of determining if the regular expression is valid or not
                // so it's up to the developer to make sure their regular expressions are valid.
                throw new \Exception(sprintf('Error with param([%s]): $validation cannot be a zero length string', $name));
            }
        } elseif (!($validation instanceof \Closure)) {
            throw new \Exception(sprintf('Error with param([%s]): $validation must be either a closure, a string with [any|int|float|bool], or a regular expression', $name));
        }

        // Validate that if specified the converter option is valid for the parameter
        if ($converter !== null) {
            if (is_string($converter)) {
                if (!($converter === 'int' || $converter === 'float' || $converter === 'bool')) {
                    throw new \Exception(sprintf('Error with param([%s]): $converter string is not correct and must be either a closure or a string with [int|float|bool]', $name));
                }
            } elseif (!($validation instanceof \Closure)) {
                throw new \Exception(sprintf('Error with param([%s]): $converter is of the wrong type and must be either a closure or a string with [int|float|bool]', $name));
            }
        }

        // Create an object for the parameter
        $param = new \stdClass;
        $param->name = $name;
        $param->validation = $validation;
        $param->converter = $converter;

        // Add the new param object to the params array
        // and return a reference to Application object.
        $this->params[$name] = $param;
        return $this;
    }

    /**
     * Check if a url path part matches defined validation for the route
     * definition variable. This function handles both parameter validation
     * and conversion from one variable type to another. If the parameter
     * if valid then the value is returned otherwise null if the parameter
     * does not match validation. This function is called from routeMatches().
     *
     * @param string $name
     * @param string $value
     * @return mixed
     * @throws \Exception
     */
    private function checkParam($name, $value)
    {
        // If the named parameter (e.g.: ':product_id') is not defined in
        // the params array then the parameter is valid so return the value.
        if (isset($this->params[$name]) === false) {
            return $value;
        }

        // Get the parameter validation object
        $param = $this->params[$name];

        // Handle different validation types
        if ($param->validation === 'int') {
            // Check if the value is an int. The function is_int() only
            // works on actual int data types while the value for this
            // function will be a string so filter_var() is used. If the
            // value is an int then return the value cast as an int
            // otherwise return null
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                return null;
            } else {
                return (int)$value;
            }
        // Check for an return a float or null
        } elseif ($param->validation === 'float') {
            if (filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                return null;
            } else {
                return (float)$value;
            }
        // Check for and return a boolean or null
        } elseif ($param->validation === 'bool') {
            // Using strict bool validation values so the following rules apply:
            // returns true if the value is '1', 'true', 'on', or 'yes'
            // returns false if the value is '0', 'false', 'off', or 'no'
            // returns null for all other values
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        // Check if anything is allowed, this would be used in cases where anything can
        // come in and then a converter handles the result. For example if it doesn't
        // matter if the users enters 'abc' for a numeric value but the value needs to
        // be converted to 0 for invalid numbers than param(':name', 'any', 'int') could
        // be used as php casts values without error.
        } elseif ($param->validation === 'any') {
            $is_valid = true;
        // If the format validation is a function then call
        // the function and check if it returned true.
        } elseif ($param->validation instanceof \Closure) {
            $is_valid = call_user_func($param->validation, $value);
        // All other strings should be regular expressions
        } elseif (is_string($param->validation)) {
            // Check the regular expression using preg_match() which
            // returns 1 if match, 0 if no match, and false if error.
            // If there is an error checking the expression PHP will
            // raise an error that contains text without the expression
            // that caused the error, for example:
            //   Warning: preg_match(): Delimiter must not be alphanumeric or backslash in ...\FastSitePHP\Application.php on line ###
            //
            // The error text is useful but only if the developer knows what
            // pattern was used and if the site uses many regular expressions
            // they might not know. Because of this error reporting is disabled when
            // the function runs so that FastSitePHP can handle the error and throw
            // an exception with the specific pattern that caused the error.
            //
            // Additionally regular expressions can be checked using filter_var() with
            // the option FILTER_VALIDATE_REGEXP, however internally they both call the
            // same C functions and both will raise the same errors so the only way to
            // run regular expressions and handle unexpected errors is to turn error
            // handling off as is done here.

            // Run preg_match() with error reporting turned off but error tracking on.
            // After calling the function then reset the settings to their original value.
            $current_error_level = error_reporting(0);
            $is_valid = preg_match($param->validation, $value);
            error_reporting($current_error_level);

            // If preg_match() failed throw an exception with a useful message for the developer
            if ($is_valid === false) {
                $last_error = error_get_last();
                $last_error = (isset($last_error['message']) ? $last_error['message'] : null);
                $preg_match_error = (isset($last_error) ? sprintf('Error message from PHP: %s', $last_error) : 'Specific error message from [preg_match()] cannot be obtained because a function defined by this site for [set_error_handler()] did not return false.');
                throw new \Exception(sprintf('Error with param([%s]), the regular expression [%s] is not valid for the PHP function preg_match(). %s', $name, $param->validation, $preg_match_error));
            }
        } else {
            // This error should never happen because parameters are validated when param() is called.
            // It is included in case the code is modified and the validation changes in param().
            throw new \Exception(sprintf('Unexpected validation format in [%s->%s()]', __CLASS__, __FUNCTION__));
        }

        // If the parameter is not valid then return null
        if (!$is_valid) {
            return null;
        }

        // If a converter is defined then pass the value to the
        // converter function so it can be converted otherwise
        // return the string value as is.
        if ($param->converter === null) {
            return $value;
        } elseif ($param->converter === 'int') {
            return (int)$value;
        } elseif ($param->converter === 'float') {
            return (float)$value;
        } elseif ($param->converter === 'bool') {
            // returns true if the value is '1', 'true', 'on', or 'yes'
            // returns false for all other values
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true) {
                return true;
            } else {
                return false;
            }
        } elseif ($param->converter instanceof \Closure) {
            return call_user_func($param->converter, $value);
        } else {
            // This error should never happen because parameters are validated when param() is called.
            // It is included in case the code is modified and the validation changes in param().
            throw new \Exception(sprintf('Unexpected converter format in [%s->%s()]', __CLASS__, __FUNCTION__));
        }
    }

    /**
     * Check if a the current url path matches a defined route. This function returns
     * an array or parsed arguments if the route matches and boolean value of false if it
     * does not match. If the route matches and there are no defined parameters then the
     * array returned is an empty array. A wild-card character '*' can be used at the
     * very end of the route definition and then the url path must match up until the
     * wild-card character. Specific variables will be validated or converted if defined
     * in the function $app->param(). Optional variables can be defined with a '?'
     * character at the end of the variable name. A request for '/about/' with
     * route '/about' will match by default, however if [strict_url_mode] is set to true
     * then '/about/' and '/about' would be separate URL's.
     *
     * Examples:
     *     $app->routeMatches('/page1', '/page2');
     *         returns false
     *
     *     $app->routeMatches('/show-all', '/show-all');
     *         returns array()
     *
     *     # [string] data type returned
     *     $app->routeMatches('/record/:id', '/record/123');
     *         returns array('123')
     *
     *     # [int] data type based on param()
     *     $app->param(':id', 'int');
     *     $app->routeMatches('/record/:id', '/record/123');
     *         returns array(123)
     *
     *     # Only a ':' character is required to indicate a variable
     *     $app->routeMatches('/record/:', '/record/123');
     *         returns array('123')
     *
     *     $app->routeMatches('/:record/:view/:id', '/orders/edit/123');
     *         returns array('orders', 'edit', '123')
     *
     *     # Optional variables ending with '?'
     *     $app->routeMatches('/search-by-date/:year?/:month?/:day?', '/search-by-date/2015/12');
     *         returns array('2015', '12')
     *
     *     $app->routeMatches('/page-list/*', '/page-list/page1/page2')
     *         returns array()
     *
     * @param string $pattern
     * @param string $path
     * @return array|bool
     * @throws \Exception
     */
    public function routeMatches($pattern, $path)
    {
        // Quick check for exact match.
        // Validates that $pattern and $path are equal, that they are strings, and that
        // there are no defined variables or wildcard characters in the pattern.
        if ($pattern === $path && is_string($pattern) && strpos($pattern, ':') === false && strpos($pattern, '*') === false) {
            return array();
        }

        // Validation to make sure this function is called correctly
        if (!is_string($pattern)) {
            throw new \Exception(sprintf('Error Unexpected Parameter Type, $pattern must be a string when [%s->%s()] is called.', __CLASS__, __FUNCTION__));
        } elseif (strlen($pattern) === 0) {
            throw new \Exception(sprintf('Error Invalid Parameter, $pattern must be 1 or more characters in length when [%s->%s()] is called.', __CLASS__, __FUNCTION__));
        } elseif (!is_string($path)) {
            throw new \Exception(sprintf('Error Unexpected Parameter Type, $path must be a string when [%s->%s()] is called.', __CLASS__, __FUNCTION__));
        } elseif (strlen($path) === 0) {
            throw new \Exception(sprintf('Error Invalid Parameter, $path be 1 or more characters in length when [%s->%s()] is called.', __CLASS__, __FUNCTION__));
        }

        // A request for '/about/' with route '/about' will match by default,
        // however if [strict_url_mode] is set to true then '/about/' and '/about'
        // would be separate URL's.
        if (!$this->strict_url_mode) {
            $last_pattern_char = substr($pattern, -1, 1);

            // Only remove the trailing slash '/' of the path if the last character
            // of the pattern is not '/' or '*'
            if (strlen($path) > 1 && substr($path, -1, 1) === '/' && $last_pattern_char !== '/' && $last_pattern_char !== '*') {
                $path = substr($path, 0, -1);
            }
        }

        // Flag variable to indicate if the page url path matches the
        // defined route, this variable defaults to true and is set to
        // false if the path does not match the defined route.
        $matches = true;

        // Convert route pattern and url path to string arrays
        $pattern_parts = explode('/', $pattern);
        $path_parts = explode('/', $path);

        // Get the size of each array
        $pattern_count = count($pattern_parts);
        $path_count = count($path_parts);

        // Optional Variables
        // Check each pattern part for optional variables
        $optional_count = 0;
        for ($n = 0; $n < $pattern_count; $n++) {
            // The part path will start with ':' and end with '?'
            if (strlen($pattern_parts[$n]) > 1 && substr($pattern_parts[$n], 0, 1) === ':' && substr($pattern_parts[$n], -1, 1) === '?') {
                $optional_count++;
            } else {
                // Validate to make sure that no required route parts are include after an optional part
                if ($optional_count !== 0) {
                    throw new \Exception(sprintf('Error Invalid Route Definition, the function [%s->%s()] was called with a route having a required value after an optional value. All optional variables must be defined at the end of the route definition. Route: [%s]', __CLASS__, __FUNCTION__, $pattern));
                }
            }
        }

        // Are there optional variables defined for the route?
        if ($optional_count !== 0) {
            // If so then reformat the pattern to specify all variables as required
            $pattern = str_replace('?', '', $pattern);
            $pattern_parts = explode('/', $pattern);

            // Starting from the end of the route definition to the first optional variable
            // create a new URL pattern definition and check it one at a time until a match
            // is found or all possible options are checked.
            // For example
            //   '/search-by-date/:year?/:month?'
            // will make the following searches:
            //   1) '/search-by-date/:year/:month'
            //   2) '/search-by-date/:year'
            //   3) '/search-by-date/'.
            for ($n = $pattern_count; $n >= ($optional_count - 1); $n--) {
                // Start building the new route
                $current_pattern = '';

                // Skip the first item in the array $pattern_parts[0] because
                // it should be a blank string.
                for ($m = 1; $m < $n; $m++) {
                    $current_pattern .= '/' . $pattern_parts[$m];
                }

                // Make sure there is something
                if ($current_pattern === '') {
                    $current_pattern = '/';
                }

                // Check the route and if it matches return the result and exit this function
                $args = $this->routeMatches($current_pattern, $path);
                if ($args !== false) {
                    return $args;
                }
            }
            // If none of the optional routes matched then the
            // overall defined route does not match.
            return false;
        }

        // If the number of parts in the route pattern is larger than the url path
        // or if the number of parts do not match and the last pattern part is not
        // the wild-card character then the route definition and url path do not match.
        $args = array();
        if ($pattern_count > $path_count || ($pattern_count !== $path_count && $pattern_parts[$pattern_count - 1] !== '*')) {
            $matches = false;
        } else {
            // Check each path part one at time
            for ($n = 0; $n < $pattern_count; $n++) {
                // A wild-card character '*' is allowed at the end of the route
                // definition and that means that everything must match up to
                // the wild-card character and anything after can be in the url
                // and the url will still be valid for the route.
                if ($pattern_parts[$n] === '*' && $n === ($pattern_count - 1)) {
                    // Matching url path and route definition, exit loop
                    break;
                }

                // With standard server and php settings the url will already be decoded
                // so urldecode() would not be needed however it is included here just
                // in case the server is set to not decode urls. With regular server
                // settings characters will not be double-escaped so this is safe to
                // call even if the url is already decoded. This can be confirmed
                // if all unit-tests successfully run on the server in use. If a server
                // is using special configuration that breaks routing then this function
                // can be easily modified by the developers of the application.
                $path_part = urldecode($path_parts[$n]);

                // Check if the current url path part matches with the route definition
                // Example '/company/about' will match to '/company/about'.
                // Depending upon the options comparing for a case-sensitive match or
                // convert to lower-case letters and compare
                if ($this->case_sensitive_urls) {
                    $part_matches = ($path_part === $pattern_parts[$n]);
                } else {
                    $part_matches = (strtolower($path_part) === strtolower($pattern_parts[$n]));
                }

                if (!$part_matches) {
                    // If they do not match then check the first character in the pattern is
                    // a ':' character, if so then this part of the path will be a parameter
                    // variable. The name is optional so either formats ':' or ':name' will work.
                    if (strlen($pattern_parts[$n]) > 0 && substr($pattern_parts[$n], 0, 1) === ':') {
                        // This part of the route/path is a named variable such as ':id', call
                        // the private function checkParam() to see if the variable name needs to
                        // be validated or converted. checkParam() will return either a value
                        // to use for the parameter or null if the value is invalid.
                        $value = $this->checkParam($pattern_parts[$n], $path_part);
                        if ($value === null) {
                            // Parameter did not match validation so the
                            // path does not match the route, exit the loop.
                            $matches = false;
                            break;
                        }
                        // Add the variable and it's name from the route definition to the args array.
                        // For example ('/:type/:action/:id', '/order/get/123') will result in the
                        // array ('order', 'get', '123')
                        $args[] = $value;
                    } else {
                        // Check if the developer was trying to use a wild-card character
                        // in the middle of the route.
                        if ($pattern_parts[$n] === '*') {
                            throw new \Exception(sprintf('The function [%s->%s()] was called with a wild-card character in the middle of the route definition. A wild-card can only be used at the end. Route: [%s]', __CLASS__, __FUNCTION__, $pattern));
                        }
                        // Route pattern did not match the url path.
                        // For example routeMatches('/company/contact', '/company/about')
                        // would trigger this.
                        $matches = false;
                        break;
                    }
                }
            }
        }

        // Return an array of any matching arguments
        // if the route matched otherwise return false
        return ($matches ? $args : false);
    }

    /**
     * Private function that gets called from $app->run(). This function
     * gets called only on matched routes and checks each filter in the
     * route. If a filter function returns false then the route is skipped.
     * If the filter function returns a response object then it will be used
     * instead of the controller for the route, this would commonly be handled
     * for filters than handle authenication.
     *
     * @param Route $route
     * @param string $method
     * @param string $url
     * @return array [bool, Response|null]
     * @throws \Exception
     */
    private function skipRoute($route, $method, $url)
    {
        // Run route [filter()] callback functions if any exist for the route
        $skip_route = false;
        $response = null;
        foreach ($route->filter_callbacks as $callback) {
            list($valid_callback, $result) = $this->callMiddleware($callback);
            if ($valid_callback) {
                if (is_null($result)) {
                    // Filter was executed and returned nothing so it's considered valid
                    continue;
                } else if (is_bool($result)) {
                    if ($result === false) {
                        $skip_route = true;
                        break;
                    }
                } else if (is_object($result) && method_exists($result, 'send') && stripos(get_class($result), 'Response') !== false) {
                    $response = $result;
                    break;
                } else {
                    throw new \Exception(sprintf('An item from [Route->filter()] for URL [%s %s] was called and returned and invalid response with data type [%s]. Filter functions must return one of the following (null/void, bool, Response object).', $method, $url, gettype($result)));
                }
            } else {
                throw new \Exception(sprintf('An item from [Route->filter()] for URL [%s %s] was defined as a [%s] but it should be defined as either a Closure function or a string in the format of \'Class.method\'.', $method, $url, gettype($callback)));
            }
        }
        return array($skip_route, $response);
    }

    /**
     * Call a user-defined middleware function that was specified from
     * either [Route->filter()] or [$app->mount()].
     *
     * Middleware callback functions need to return false to skip the
     * route or routes, however they do not have to return anything. A bool
     * value of [false] will only be set if explicitly returned. If nothing
     * is returned then $result will equal null.
     *
     * @param \Closure|string $callback
     * @return array - list($valid_callback, $result)
     */
    private function callMiddleware($callback)
    {
        $result = null;
        $valid_callback = false;
        if ($callback instanceof \Closure) {
            // Using a closure/callback functon
            $result = call_user_func($callback);
            $valid_callback = true;
        } elseif (is_string($callback)) {
            // Using a 'class.method' string, the [Application/$this]
            // instance will be passed as the only parameter to the called
            // function however it is optional and does not have to be
            // defined in the middleware function.
            $class = $callback;
            $pos = strpos($class, '.');
            if ($pos > 0) {
                $method = substr($class, $pos +1);
                $class = substr($class, 0, $pos);
                if ($this->middleware_root !== null) {
                    $class = $this->middleware_root . '\\' . $class;
                }
                $object = array(new $class, $method);
                $result = call_user_func($object, $this);
                $valid_callback = true;
            }
        }
        return array($valid_callback, $result);
    }

    // -------------------------------------------
    //
    //    ########  ##     ## ##    ##
    //    ##     ## ##     ## ###   ##
    //    ##     ## ##     ## ####  ##
    //    ########  ##     ## ## ## ##
    //    ##   ##   ##     ## ##  ####
    //    ##    ##  ##     ## ##   ###
    //    ##     ##  #######  ##    ##
    //
    // -------------------------------------------

    /**
     * This is the main function that processes the request, determines the route,
     * and sends a response. Routes, settings, validation rules, etc need to be
     * defined prior to calling this function.
     *
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

        // Before any routes are checked call all callback
        // functions defined from the [before()] function
        foreach ($this->before_callbacks as $callback) {
            call_user_func($callback);
        }

        // Handle the OPTIONS request method if sent from the client.
        //   http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
        //   http://tools.ietf.org/html/rfc2616#section-9
        //   http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
        //
        // The OPTIONS methods shows possible request methods for a site
        // but does not allow the client to see available URL's when properly
        // implemented so it's generally not a security risk to have on a site.
        // However if you do not want to show this info for your site it can be
        // turned off by using the property [allow_options_requests].
        //
        if ($method === 'OPTIONS' && $this->allow_options_requests === true) {
            $this->sendOptionsResponse($url);
        }

        // Find the first matching Route.
        // Process all requests method types other than OPTIONS here.
        foreach ($this->site_routes as $route) {
            // First check if the requested URL matches the current route.
            // If the route is matched then the $args array will contain any
            // defined arguments in the URL pattern. For example:
            // '/record/:id', '/record/123' would result in array('123').
            // If the route does not match then it returns a boolean of false.
            // A route that matches but does not have any arguments will
            // return an empty array.
            $args = $this->routeMatches($route->pattern, $url);
            if ($args === false) {
                continue;
            }

            // Next check the method [GET, POST, etc]. If it does not match the
            // route then skip and continue to the next route in this loop.
            // Additionally routes defined from route() do not require a
            // specific method and a 'HEAD' Request is the same as a 'GET' but
            // only requesting the Response Headers. If the method is not
            // matched then the header of the current route is added to an
            // array so that a 405 'Method Not Allowed' Request can be sent
            // instead of a 404 'Not Found'.
            if (!($route->method === null
                || $route->method === $method
                || ($route->method === 'GET' && $method === 'HEAD'))) {
                    $allowed_methods[] = $route->method;
                    continue;
            }

            // The route matches so check filter functions defined for the route.
            list($skip_route, $response) = $this->skipRoute($route, $method, $url);
            if ($response !== null) {
                $route_was_found = true;
                break;
            } else if (!$skip_route) {
                // Call the route controller function. There are three supported
                // Controller options:
                //   - Closure (Callback function)
                //   - String in the format of 'Class'
                //   - String in the format of 'Class.method'
                // When using string 'Class' the method used to define the route
                // [get(), post(), route(), etc] will be called.
                if ($route->controller instanceof \Closure) {
                    $response = call_user_func_array($route->controller, $args);
                } elseif (is_string($route->controller)) {
                    $class = $route->controller;
                    $pos = strpos($class, '.');
                    if ($pos > 0) {
                        $method = substr($class, $pos +1);
                        $class = substr($class, 0, $pos);
                    } else {
                        $method = ($route->method === null ? 'route' : $route->method);
                    }
                    if ($this->controller_root !== null) {
                        $class = $this->controller_root . '\\' . $class;
                    }
                    $controller = array(new $class, $method);
                    array_unshift($args, $this);
                    $response = call_user_func_array($controller, $args);
                } else {
                    $error = 'A [route->controller] for URL [%s %s] was defined as a [%s] but it should be defined as either a Closure function or a string in the format of \'Class\' or \'Class.method\'';
                    $error = sprintf($error, $method, $url, gettype($route->controller));
                    throw new \Exception($error);
                }
                $route_was_found = true;

                // If a matching route returned a response, has sent contents
                // to the output buffer, or has submitted content then exit
                // the foreach loop. Otherwise keep looking for matching routes.
                // If the [php.ini] setting [output_buffering] is turned off
                // then [ob_get_length()] will return false and a response can
                // be determined by checking if headers are sent. If [output_buffering]
                // is turned on as would be expected in most  environments then
                // and if content was submitted [ob_get_length()] will return
                // a nubmer greather than zero.
                if (isset($response) || ob_get_length() > 0 || headers_sent())  {
                    break;
                }
            }
        }

        // If no route was matched after checking all routes then call all
        // functions defined with the [notFound()] function untill a response
        // is returned or content is sent. [notFound()] callback functions
        // can return a response.
        if (!(isset($response) || ob_get_length() > 0 || headers_sent())) {
            foreach ($this->not_found_callbacks as $callback) {
                $response = call_user_func($callback);
                if (isset($response) || ob_get_length() > 0 || headers_sent())  {
                    break;
                }
            }
        }

        // If there is a response call any functions defined from beforeSend().
        // If a function is defined in beforeSend() it must return a response.
        // [beforeSend()] functions allow for content in the response to be modified.
        if (isset($response)) {
            foreach ($this->before_send_callbacks as $callback) {
                $response = call_user_func($callback, $response);
            }
        }

        // Handle the Result
        // If null then the route has already output a response or no route was found
        if ($response === null) {
            // Check if output has already been sent by a route or function.
            // If a response has already been sent then do nothing as the
            // route is handling all output.
            if (!(ob_get_length() > 0 || headers_sent()))  {
                // If no output has been sent and a route was found then raise an exception
                // with a message regarding the route. If no routes were matched then send a
                // 404 'Not found' Response to the Client. If routes were matched but the
                // the method did not match then return a 405 'Method Not Allowed' Response.
                if ($route_was_found) {
                    throw new \Exception(sprintf('Route [%s %s] was matched however the route\'s function or a beforeSend() callback function returned no response.', $method, $url));
                } else {
                    if (count($allowed_methods) === 0) {
                        $this->sendErrorPage(404);
                    } else {
                        if (in_array('GET', $allowed_methods)) {
                            $allowed_methods[] = 'HEAD';
                        }
                        if ($this->allow_options_requests === true) {
                            $allowed_methods[] = 'OPTIONS';
                        }
                        $this->sendErrorPage(405, null, array_unique($allowed_methods));
                    }
                }
            }
            // The route handled the response so still run any after() functions that were defined.
            $this->runAfterEvents(null);

        // If a string was returned by the route then output the response
        } elseif (gettype($response) === 'string') {
            $this->sendResponse($response);

        // If an object class contains name "Response" was returned and it contains a send() method
        // then allow it to handle the response and after call any defined after() functions.
        } elseif (gettype($response) === 'object' && method_exists($response, 'send') && stripos(get_class($response), 'Response') !== false) {
            $response->send();
            $this->runAfterEvents($response);

        // If a PHP array is returned from the route then send it as JSON
        } elseif (is_array($response) && $this->header('Content-Type') === null) {
            $this->header_fields['Content-Type'] = 'application/json';
            if ($this->json_options === 0 && PHP_VERSION_ID >= 50400) {
                $this->json_options = JSON_UNESCAPED_UNICODE;
            }
            $this->sendResponse(json_encode($response, $this->json_options));

        // If the response type is specified as JSON then convert
        // the response to a JSON string and output the result.
        } elseif ($this->header('Content-Type') === 'application/json') {
            if ($this->json_options === 0 && PHP_VERSION_ID >= 50400) {
                $this->json_options = JSON_UNESCAPED_UNICODE;
            }
            $this->sendResponse(json_encode($response, $this->json_options));

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
     * @param string $url
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
            // If a filter function returns a response it will be ignored here.
            list($skip_route, $response) = $this->skipRoute($route, 'OPTIONS', $url);
            if (!$skip_route) {
                // Same rules as used above when matching all routes
                switch ($route->method) {
                    case null:
                        $allowedMethods[] = 'HEAD';
                        $allowedMethods[] = 'GET';
                        $allowedMethods[] = 'POST';
                        break;
                    case 'GET':
                        $allowedMethods[] = 'HEAD';
                        // Fall-through the case statement to add the 'GET'
                    default:
                        $allowedMethods[] = $route->method;
                        break;
                }
            }
        }

        // If no methods were found for the route with the specified
        // URL then send a 404 Response 'Not found'
        if (count($allowedMethods) === 0) {
            $this->sendErrorPage(404);
        } else {
            // Always add 'OPTIONS' as it is always allowed for valid routes
            // if [allow_options_requests] and [allow_options_requests = true]
            // will be true when when this function is called.
            $allowedMethods[] = 'OPTIONS';
        }

        // When a URL matches multiple routes the allowed request methods will be
        // duplicated so remove all duplicates from the array and sort the result.
        $allowedMethods = array_unique($allowedMethods);
        asort($allowedMethods);
        $allowedMethods = implode(', ', $allowedMethods);

        // Add the 'Allow' response header with all matched or specified request methods
        if ($this->allow_methods_override !== null) {
            $allowedMethods = $this->allow_methods_override;
        }
        header('Allow: ' . $allowedMethods);

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

        // Run any functions defined from after()
        $this->runAfterEvents(null);

        // Terminate script execution as a response body will
        // not be sent and no route controllers will be called.
        // Options is meant to only send the 'Allow' header or
        // Cross-Origin Resource Sharing headers if needed.
        exit();
    }

    /**
     * Private function that sends the actual HTTP Response. This function gets called
     * from [$app->run()] for basic responses where the route does not return a Response
     * Object and this function gets called from [$app->exceptionHandler()] when no route
     * exists or there was an error. A basic response is one where content is sent with
     * only a few common headers if any; options for caching, cookies, and custom headers
     * are not handled with this function and instead a [\FastSitePHP\Web\Response] object
     * or custom Response Class would need to be used instead.
     *
     * @param string $content
     * @return void
     * @throws \Exception
     */
    private function sendResponse($content)
    {
        // Some of the code here is duplicated from [\FastSitePHP\Web\Response->send()],
        // however this function contains fewer features. This is by design so that a site with
        // minimal needs does not need to use or load the Response Object. In fact FastSitePHP
        // can be used by including only 2 files on a server. For full comments on the related
        // code see the Response Object [send()] function.

        // Send Response Headers unless they have already been sent.
        if (!headers_sent()) {
            // First send the response status code if one is set
            if ($this->status_code !== null) {
                // PHP 5.4+
                if (function_exists('http_response_code')) {
                    http_response_code($this->status_code);
                } else {
                    // PHP 5.3, only status code supported by this object are included.
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
                    // Add status to the header response, for example 'HTTP/1.1 200 OK'
                    if (isset($status_code_text[$this->status_code])) {
                        header(sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $this->status_code, $status_code_text[$this->status_code]));
                    }
                }
            }

            // If noCache() was set then specify headers to instruct
            // the browser or client to not cache the content.
            if ($this->no_cache) {
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: -1');
            }

            // Send any defined CORS Headers
            if ($this->cors_headers !== null) {
                foreach ($this->cors_headers as $name => $value) {
                    header("$name: $value");
                }
            }

            // Send additional headers after the status code
            foreach ($this->header_fields as $name => $value) {
                header("$name: $value");
            }

            // Cookies are sent along with the response headers and like other response
            // headers can only be sent if content is not already sent to the client.
            foreach ($this->response_cookies as $cookie) {
                // setcookie() will return false when php error handling is turned off,
                // otherwise invalid calls to setcookie() will trigger E_WARNING errors.
                $success = setcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
                if (!$success) {
                    throw new \Exception(sprintf('Error: setcookie() returned false for cookie named [%s]', (is_string($cookie['name']) ? $cookie['name'] : 'Name was not a string, gettype=' . gettype($cookie['name']))));
                }
            }
        }

        // Output the response
        // If the Request is using the HEAD method or if the Response Status Code
        // is [204 => 'No Content'] or [205 => 'Reset Content'] then do not send
        // the content as those response types must not include content.
        // To handle 304 Response Codes use the Class [\FastSitePHP\Web\Response] instead.
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
        if ($method !== 'HEAD' && $this->status_code !== 204 && $this->status_code !== 205) {
            echo $content;
        }

        // Run any functions defined from after()
        $this->runAfterEvents($content);
    }

    /**
     * Call all closure functions defined from [after()]. This function is
     * public however it would normally not be called. It is used by
     * FastSitePHP internally after sending the response.
     *
     * @param mixed $content
     * @return void
     */
    public function runAfterEvents($content)
    {
        // [$content] is the parameter for the defined function which can be
        // used for logging or other needs. Once this function is called the
        // response will have already been sent so it does not get modified
        // unless an after() function sends additional content.
        try {
            foreach ($this->after_callbacks as $callback) {
                call_user_func($callback, $content);
            }
        } catch (\Exception $callback_error) {
            // If an exception is raised by an after() function then clear
            // any after callbacks and send the error. This function gets
            // called from sendErrorPage() so functions will be cleared
            // on error so that they do not happen more than once.
            $this->after_callbacks = array();
            $this->sendErrorPage(500, $callback_error);
        }
    }
}
