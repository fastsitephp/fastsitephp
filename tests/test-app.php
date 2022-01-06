<?php
// ==================================================================
// Unit Testing Page
// *) This file uses only core Framework files and no error
//    handling called from the setup() function.
// *) Common functions in the Application Class are tested
//    with this file.
// *) Because there is no error handling defined this page
//    should return 500 status code errors with a blank
//    screen for all errors unless the [php.ini] settings
//    are configured to display errors. If the settings are
//    configured that way then the user is alerted so on
//    the unit test page.
// ==================================================================

// Before calling [$app->setup()] get the value of display_errors.
// This is used in route [/check-server-config]
$display_errors = ini_get('display_errors');

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();

// Create and Setup the Application Object
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// General Classes and Functions used for Testing
// -----------------------------------------------------------

// This function is named this way on purpose with lowercase "response"
// and mixed case "Send", this is for testing that the expected code is case-insensitive
class custom_response
{
    public $content = null;

    public function Send()
    {
        echo $this->content;
    }
}

// Similar to the above class but with a different name
class CustomSend
{
    public $content = null;

    public function Send()
    {
        echo $this->content;
    }
}

// When converted to JSON using json_encode() static
// properies are expected to not be included
class CustomClass
{
    public $Name = 'FastSitePHP_App';
    public $CreatedFrom = 'CustomClass';
    public $IntValue = 123;
    public $BoolValue = true;
    public static $StaticInt = 123;
    public static $StaticBool = true;
}

class SimpleClass
{
    public static $prop = null;
    public function getValue()
    {
        return 'Test';
    }
}

function showObject(\stdClass $obj) {
    var_dump($obj);
}

// -----------------------------------------------------------
// Application Events
// -----------------------------------------------------------

$app->before(function() use ($app) {
    // Before any routes are matched check if the route should disable
    // the property [allow_options_requests] prior to matching routes.
    // By default [allow_options_requests] is set to true
    switch ($app->requestedPath()) {
        case '/toggle-options':
            // Example: [?options=true] [?options=false]
            if (!isset($_GET['options']) || filter_var($_GET['options'], FILTER_VALIDATE_BOOLEAN) === false) {
                $app->allow_options_requests = false;
            }
            break;
        case '/405-options':
            $app->allow_options_requests = false;
            break;
    }
});

// -----------------------------------------------------------
// Create Route Filter Functions
// -----------------------------------------------------------

$redirectFilter = function() use ($app) {
    $app->redirect('redirected');
};

$returnFalseFilter = function() use ($app) {
    return false;
};

$emptyFilter = function() {};

$updateAppFilter = function() use ($app) {
    $app->custom_property = 'updateAppFilter()';
};

$json_response = function() use ($app) {
    $app->header('Content-Type', 'application/json');
};

// -----------------------------------------------------------
// Define Parameter Validation
// -----------------------------------------------------------

$app->param(':product_id', 'int');

$range_param = function($value) {
    $num = (int)$value;
    if ($num >= 5 && $num <= 10) {
        return true;
    } else {
        return false;
    }
};

// Verify that the param() function is chainable
$app
    ->param(':range1', $range_param)
    ->param(':range2', $range_param, 'int')
    ->param(':range3', $range_param, function($value) {
        return (int)$value;
    });

$app->param(':float1', 'float');
$app->param(':float2', 'any', 'float');

$app->param(':bool1', 'bool');
$app->param(':bool2', 'any', 'bool');

$app->param(':regex1', '/^\d+$/');
$app->param(':regex2', '/^[a-zA-Z]*$/');

// -----------------------------------------------------------
// Check Server Configuration
// -----------------------------------------------------------

// This route checks is the first unit test to run using default FastSitePHP setup. It checks
// the web server for known errors that would cause specific Unit Tests to fail. In general
// if an error message is displayed here it’s a good idea to review your PHP setup and make
// sure that it is correct for your environment. Additionally while some server info can
// be obtained by the client if this method fails the information provided does not pose a
// security risk. The route below this route is also used by the client page for verifying
// the web server.
$app->get('/check-server-config', function() use ($app, $display_errors) {
    // Save error messages to an Array
    $errors = array();
    $error_msg = null;

    // Check if the instance of PHP running is using the configuration file [php.ini].
    // [php_ini_loaded_file] returns either the file path or false if no [php.ini] file
    // is being used. The [php.ini] file is not required for PHP to run however it should
    // be setup on all production servers and default installations of Linux and Windows.
    // The most likely cause of this error will be if running this script on a Mac where
    // the settings file has not been setup or was reset during a Mac OS Upgrade.
    //
    // If running this on a Mac and the file does not exist then run the following
    // commands from Terminal:
    //   1) cd /etc
    //   2) sudo cp php.ini.default php.ini
    //   3) sudo /usr/sbin/apachectl restart
    //
    // OR to replicate this error from a Mac (or simply delete a bad [php.ini] file)
    // run the following commands:
    //   1) cd /etc
    //   2) sudo rm php.ini
    //   3) sudo /usr/sbin/apachectl restart
    //
    // For more on the [php.ini] file including default settings see links:
    //   http://php.net/manual/en/configuration.file.php
    //   http://php.net/manual/en/ini.list.php
    //
    // To edit the [php.ini] you will likely need root or sudo access on Mac/Linux/Unix
    // and Administrator Rights on Windows. To edit in Windows first right-click on
    // notepad from the Start Menu and choose [Run as administrator] then browser for
    // the file. To get the file path simply call [php_ini_loaded_file()] from another
    // PHP File or use the function [phpinfo()].
    //
    $ini_path = php_ini_loaded_file();
    if ($ini_path === false) {
	    $error_msg = 'The [php.ini] file does not exist.';
	    if (PHP_OS === 'Darwin') {
		    $error_msg .= ' If you are seeing this while in development on Mac refer to the source code comments on how to fix this error.';
	    } else {
		    $error_msg .= ' On Production Web Servers it’s recommended for PHP to use the [php.ini] for configuration settings.';
	    }
	    $error_msg .= ' This error will likely trigger invalid configuration that will cause some unit tests to fail.';
	    $errors[] = $error_msg;
	}

    // Check if the [php.ini] setting [display_errors] is turned on. FastSitePHP
    // turns this off when [$app->setup()] is called as it converts all errors
    // to exceptions and handles them with a formatted error page. If turned this
    // will cause two unit testing routes that do not call [$app->setup()] to fail.
    if (!function_exists('filter_var')) {
	    $errors[] = 'Core PHP Function filter_var() is missing. It\'s likely that many other core functions are missing. Many tests are expected to fail. Refer to PHP Install Docs for your OS on how to install.';
    } elseif (filter_var($display_errors, FILTER_VALIDATE_BOOLEAN) === true) {
	    if ($ini_path === false) {
	    	$error_msg = 'Because [php.ini] is missing the setting [display_errors] is set to [On] which is not recommended for Production Servers.';
	    } else {
		    $error_msg = 'The [php.ini] setting [display_errors] is not set to off.';
	    }
        $error_msg .= ' This will cause unexpected output on two unit tests when [$app->setup()] is not called.';
        $errors[] = $error_msg;
    }

    // Check the [php.ini] setting [output_buffering], this error will most likely be triggered if the
    // settings file [php.ini] is not being used. If the option to "Off" or "0" then many tests will
    // fail however if it is turned on but set to a different value (example: 2048) then likely only
    // this config check will fail however because the default and recommened option for both
    // PHP Development and Production Servers is 4096 that is the value that is checked.
    if (ini_get('output_buffering') !== '4096') {
	    if ($ini_path === false) {
	    	$error_msg = 'Because [php.ini] is missing the setting [output_buffering] is not set to the default value of 4096.';
	    } else {
		    $error_msg = 'The [php.ini] setting [output_buffering] is not set to the default value of 4096.';
	    }
	    $error_msg .= ' This may cause unexpected output on several unit tests where an error occurs after output is sent to the client or when the function [$app->setup()] is not called.';
	    $errors[] = $error_msg;
    }

    // Check for IPv6 Support
    // According to documentation if [!defined('AF_INET6')] then PHP was not
    // compiled with IPv6 however on Windows this option will likely not be set so
    // checking if the function [inet_ntop()] is defined instead is a more reliable
    // way of checking IPv6 Support because [inet_ntop()] is required for IPv6.
    if (!function_exists('inet_ntop')) {
        $errors[] = 'The version of PHP used by this server does not include IPv6 support which will cause several unit tests that depend on IPv6 support to fail.';
    }

    // Specific Modules (functions and classes)
    if (!function_exists('bcpow')) {
        $errors[] = 'Missing PHP Binary Calculator Module (bc). This will cause some Unit Tests to fail because IP Addresses available will not be listed when using [Net\IP::cidr()]. This is installed by default on most PHP Installations however since it is not here the model [bcmath] would need to be installed.';
    }

    // If running PHP from a local PHP Command Line Web Server using PHP Version 5.6
    // then check for a known default bug. For help editing [php.ini] see comments
    // near the top of this function.
    //   https://bugs.php.net/bug.php?id=66763
    if (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 6 && php_sapi_name() === 'cli-server') {
        if (ini_get('always_populate_raw_post_data') !== '-1') {
            $error_msg = 'When using PHP Version 5.6 with the local PHP Web Server from the Command Line a known bug exists in the default configuration which will cause errors with routes that use the POST method.';
            if ($ini_path === false) {
                $error_msg .= ' To fix this set the setting [always_populate_raw_post_data = -1] once a [php.ini] file is setup. When editing this file you will need to make sure that the text editor as Administrator or Root privileges.';
            } else {
                $error_msg .= ' To fix this set the setting [always_populate_raw_post_data = -1] in the file [' . $ini_path . '].';
            }
            $errors[] = $error_msg;
        }
    }

    // If using PHP 5.3 then check if Magic Quotes are enabled. Magic Quotes were widely
    // used in early versions of PHP and have been used set by default by many Web Hosts
    // that use or used PHP 5.3. Using Magic quotes causes for unexpected behavior so if
    // the site is running on PHP 5.3 then check for them.
    //   https://en.wikipedia.org/wiki/Magic_quotes
    //   http://php.net/manual/en/security.magicquotes.disabling.php
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        if (filter_var(ini_get('magic_quotes_gpc'), FILTER_VALIDATE_BOOLEAN) === true) {
            $errors[] = 'The settings file [php.ini] on this server has the setting [magic_quotes_gpc] turned on. This will cause trigger some of the unit tests to fail and can cause for unexpected results when processing User Info. If you cannot upgrade to a new version of PHP you should consider disabling this setting.';
        }
        if (filter_var(ini_get('magic_quotes_runtime'), FILTER_VALIDATE_BOOLEAN) === true) {
            $errors[] = 'The settings file [php.ini] on this server has the setting [magic_quotes_runtime] turned on. This may not trigger any of the unit tests to fail however it can cause for unexpected results when reading data from files, databases, or executables. If you cannot upgrade to a new version of PHP you should consider disabling this setting.';
        }
    }

    // Are PHP Shell functions disabled? Likely from [php.ini].
    // This would result in E_WARNING "shell_exec() has been disabled for security reasons".
    // For info on this setting see:
    //   https://php.net/disable-functions
    if (!function_exists('exec')) {
        $errors[] = 'PHP function [exec] does not exist and may have been disabled in the [php.ini] file from setting [disable_functions].';
    }
    if (!function_exists('shell_exec')) {
        $errors[] = 'PHP function [shell_exec] does not exist and may have been disabled in the [php.ini] file from setting [disable_functions].';
    }

    // Check if openssl (encryption functions) are available.
    // https://secure.php.net/manual/en/book.openssl.php
    if (!extension_loaded('openssl')) {
        $errors[] = 'The common PHP extension [openssl] is not available on this server. This will prevent encryption features from the class [FastSitePHP\Security\Crypto] and related functionality such as Signed or Encrypted Cookies from working.';
    }

    // Check if multibyte string functions are available and if so that they
    // do not use any function overloading features.
    // https://secure.php.net/manual/en/book.mbstring.php
    // https://secure.php.net/manual/en/mbstring.overload.php
    if (extension_loaded('mbstring') && ini_get('mbstring.func_overload') !== '0') {
        $errors[] = 'Multibyte function overloading is being used. This is not expected to impact FastSitePHP however using this feature is highly discouraged and has been DEPRECATED as of PHP 7.2.0. Using this feature may affect your application or third-party code libraries.';
    }

    // FreeBSD check for known required modules
    if (count($errors) > 0 && PHP_OS === 'FreeBSD') {
	 	$errors[] = 'Make sure you have the required modules installed. Example of how to install for PHP 7.1: pkg install mod_php71 php71-json php71-filter php71-hash php71-ctype php71-openssl php71-mbstring php71-xml php71-bcmath php71-gd';
    }

    // Check Folder Write Permissions
    if (!is_writable(sys_get_temp_dir())) {
        $errors[] = 'The current webuser does not have permissions to write files to the temp directory. This will cause unit tests that write files to fail. This includes file encryption unit tests.';
    }

    // Result
    return array(
	    'settingsAreValid' => (count($errors) === 0 ? true : false),
	    'errors' => $errors,
    );
});

// Define a route [/check-server-options] which allow for [PUT, DELETE, and PATCH]
// Requests. This is the 2nd Unit Test called in the default FastSitePHP Installation.
// The actual routes are never called which is why they are defined as empty functions
// but rather an OPTIONS request is sent to see what methods the Web Server sends back
// in the [Allow] Response Header. Web Servers will often have modules (for example
// WebDAV in IIS) that block access to certain request methods.
$app->put('/check-server-options', function() {});
$app->delete('/check-server-options', function() {});
$app->patch('/check-server-options', function() {});

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Check how the Application Object is defined
$app->get('/check-app-class', function() use ($app) {
    // NOTE - changing this line in [Application.php]:
    //   class Application
    // to
    //   class Application extends \stdClass
    // would cause this test to fail on the client web page

    // Return type is json format
    return array(
        'get_class' => get_class($app),
        'get_parent_class' => get_parent_class($app),
    );
});

// Check Default Application Properties, new properties defined for Application
// must also be added here.
$app->get('/check-app-properties', function() use ($app) {
    // Define arrays of properties by type
    $null_properties = array(
        'template_dir', 'header_templates', 'footer_templates',
        'error_template', 'not_found_template', 
        'allow_methods_override', 'controller_root', 'lang',
        'middleware_root',
    );
    $true_properties = array('case_sensitive_urls', 'allow_options_requests');
    $false_properties = array('strict_url_mode', 'show_detailed_errors');
    $string_properties = array(
        'error_page_title' => 'An error has occurred',
        'error_page_message' => 'An error has occurred while processing your request.',
        'not_found_page_title' => '404 - Page Not Found',
        'not_found_page_message' => 'The requested page could not be found.',
        'method_not_allowed_title' => 'Error - Method Not Allowed',
        'method_not_allowed_message' => 'A [{method}] request was submitted however this route only allows for [{allowed_methods}] methods.',
    );
    $array_properties = array('locals', 'config');
    $private_properties = array(
        'site_routes', 'before_callbacks', 'not_found_callbacks', 'before_send_callbacks',
        'after_callbacks', 'error_callbacks', 'view_engine', 'params',
        'status_code', 'header_fields', 'no_cache', 'cors_headers', 'last_error',
        'lazy_load_props', 'response_cookies', 'render_callbacks',
    );
    $int_properties = array('json_options');

    // Reset prop as it's overwritten at the top of this page
    $app->show_detailed_errors = false;

    // Load the core function file and verify the object
    // using a function defined in the file.
    require('./core.php');
    $result = checkObjectProperties($app, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties, $int_properties);

	// Return the result
    return $result;
});

// Check Application Functions, this is similar to the above function
// but instead of checking properties it checks the application functions.
$app->get('/check-app-methods', function() use ($app) {
    // Define arrays of function names by type
    $private_methods = array(
        'sendErrorPage', 'checkParam', 'skipRoute', 'sendResponse', 'sendOptionsResponse',
        'callMiddleware',
    );
    $public_methods = array(
        'setup', 'exceptionHandler', 'errorHandler', 'shutdown', '__call',
        'statusCode', 'headers', 'header', 'noCache', 'cors', 'escape',
        'engine', 'render', 'before', 'notFound', 'beforeSend', 'after',
        'error', 'mount', 'route', 'get', 'post', 'put', 'delete', 'patch',
        'routes', 'redirect', 'requestedPath', 'rootUrl', 'rootDir',
        'param', 'routeMatches', 'run', 'pageNotFound', 'runAfterEvents',
        'methodExists', '__get', 'lazyLoad', 'sendPageNotFound',
        'cookie', 'clearCookie', 'cookies', 'onRender', 'errorPage',
    );

    // Load the core function file and verify the object
    // using a function defined in the file.
    require('./core.php');
    $result = checkObjectMethods($app, $private_methods, $public_methods);

	// Return the result
    return $result;
});

// Default Route returns file name
$app->get('/', function() use ($app) {
    return basename(__FILE__);
});

// Return a JSON object to the client with basic URL request info
$app->get('/get-url', function() use ($app) {
    return array(
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    );
});

// Return a JSON object with basic URL request info.
// Note - this is using an array rather than stdClass as is used
// above however the end result is the same (except for the
// property requestedPath)
$app->get('/get/url', function() use ($app) {
    return array(
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
    );
});

// Return a JSON object - similar to the route '/get-url'
// however the route path is different and the return
// type is a string using json_encode() rather than an object.
// On the client the route is actually being requested as
// "/get/url/3/" (note the extra slash at the end of the request)
$app->get('/get/url/3', function() use ($app) {
    $url_info = new \stdClass;
    $url_info->rootUrl = $app->rootUrl();
    $url_info->rootDir = $app->rootDir();
    $url_info->requestedPath = $app->requestedPath();
    return json_encode($url_info);
})
->filter($json_response);

// Return a text of the requested url to the client.
// Similar to the above URL but testing for [strict_url_mode].
$app->get('/get-url2', function() use ($app) {
    // By default [strict_url_mode] is set to false
    // so this will match both '/get-url2/' and '/get-url2'.
    // When requestedPath() is called with [strict_url_mode]
    // equal to false the last '/' character will be removed
    // however if [strict_url_mode=true] then it will be kept.
    // Normally [strict_url_mode] would be defined as true prior
    // to any routes being matched but this is for unit testing
    // so it is set here.
    if (isset($_GET['strict_url_mode']) && $_GET['strict_url_mode'] === '1') {
        $app->strict_url_mode = true;
    }
    return $app->requestedPath();
});

// Return a JSON object with basic URL request info.
// URL contains Chinese Characters.
$app->get('/test/测试', function() use ($app) {
    return array(
        'rootUrl' => $app->rootUrl(),
        'rootDir' => $app->rootDir(),
        'requestedPath' => $app->requestedPath(),
        'queryString' => $_GET,
    );
});

// Return HTML text
$app->get('/html', function() use ($app) {
    return '<h1>HTML</h1>';
});

// Return HTML text using route() rather than get();
$app->route('/html2', function() use ($app) {
    return '<h1>HTML2</h1>';
});

// Echo HTML and call exit()
$app->get('/html3', function() use ($app) {
    echo '<h1>HTML3</h1>';
    exit();
});

// Echo HTML and do not call exit()
$app->get('/html4', function() use ($app) {
    echo '<h1>HTML4</h1>';
});

// Return HTML using the charset 'ISO-8859-1'
$app->get('/html5', function() use ($app) {
    // Set Header and make sure functions are chainable
    $headers = $app
        ->header('Content-Type', 'text/html; charset=ISO-8859-1')
        ->headers();

    // Validate
    if ($headers !== array('Content-Type' => 'text/html; charset=ISO-8859-1')) {
        echo 'Unexpected Result: ';
        var_dump($headers);
        exit();
    }

    // Return the HTML
    return '<h1>HTML5</h1>';
});

// Return a JSON Response from a string
$app->get('/json-string', function() use ($app) {
    return json_encode(array(
        'Name' => 'FastSitePHP_App',
        'ReturnType' => 'String',
    ));
})
->filter($json_response);

// Return a JSON Response using a basic PHP Array
$app->get('/json-array', function() use ($app) {
    return array(
        'Name' => 'FastSitePHP_App',
        'CreatedFrom' => 'Array',
    );
})
->filter($json_response);

// Return a JSON Response using a basic PHP stdClass Object
$app->get('/json-object', function() use ($app) {
    // Create a Basic Object
    $object = new \stdClass;
    $object->Name = 'FastSitePHP_App';
    $object->CreatedFrom = 'stdClass';

    // Return Response
    return $object;
})
->filter($json_response);

// Return a JSON Response using a User Defined Class (above in this page)
$app->get('/json-custom', function() use ($app) {
    $object = new CustomClass();
    return $object;
})
->filter($json_response);

// Create and Return a Custom Response Object
$app->get('/custom-response-object', function() {
    $res = new custom_response();
    $res->content = 'Testing with a Custom Response Class: ' . get_class($res);
    return $res;
});

// Error Check - Returning an Invalid Object for the Route
$app->get('/invalid-response-object', function() {
    $res = new CustomSend();
    $res->content = 'Testing with a Custom Response Class: ' . get_class($res);
    return $res;
});

// Error Check - Returning an Invalid Variable for the Route
$app->get('/invalid-response-type', function() {
    return 123;
});

// Dynamically define Routes for each of the Supported Redirect Status Codes
// This is tested by the browser for expected Redirect behavior however the
// web browser cannot be used to test the actual response content so command line
// unit tests exist to test response content in the folder [docs/unit-testing].
$redirect_status_codes = array(301, 302, 303, 307, 308);
foreach ($redirect_status_codes as $status_code) {
    $app->get("/redirect-$status_code", function() use ($app, $status_code) { $app->redirect("redirected-$status_code", $status_code); });
    $app->get("/redirected-$status_code", function() use ($status_code) { return "$status_code Redirect"; });
}

// Redirect with URL Parameters, the command line unit tests
// available in the folder [docs/unit-testing] confirm that
// the ampersand [&] is correctly escaped.
$app->get("/redirect-with-params", function() use ($app) {
    $app->redirect('redirected-with-params?param1=abc&param2=123');
});
$app->get("/redirected-with-params", function() use ($app) {
    return $_GET;
});

// Test the redirect() function for invalid function calls
$app->get('/redirect-errors', function() use ($app) {
    // Test for exceptions, each of these tests
    // is expected to thrown an exception.
    $test_error_count = 0;

    $error_tests = array(
        array(
            'url' => 123,
            'expected_error' => 'Invalid parameter type [$url] for [FastSitePHP\Application->redirect()], expected a [string] however a [integer] was passed.',
        ),
        array(
            'url' => '',
            'expected_error' => 'Invalid parameter for [FastSitePHP\Application->redirect()], [$url] cannot be an empty string.',
        ),
        array(
            'url' => "URL1 \n URL2",
            'expected_error' => 'Invalid parameter for [FastSitePHP\Application->redirect()], [$url] should be in the format of a URL understood by the client and cannot contain a line break. The URL passed to this function included a line break character.',
        ),
        array(
            'url' => '404',
            'status_code' => 404,
            'expected_error' => 'Invalid [$status_code = 404] specified for [FastSitePHP\Application->redirect()]. Supported Status Codes are [301, 302, 303, 307, 308].',
        ),
    );

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;

            // Test
            if (isset($test['status_code'])) {
                $app->redirect($test['url'], $test['status_code']);
            } else {
                $app->redirect($test['url']);
            }

            // If the test doesn't error that there is a problem
            echo sprintf('Error with Exception Test %d, The test did not fail but should have thrown an exception.', $test_error_count);
            echo '<br><br>';
            echo '<strong>Result: </strong> ';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Test: </strong> ';
            echo json_encode($test, JSON_PRETTY_PRINT);
            exit();
        } catch (\Exception $e) {
            if ($e->getMessage() !== $test['expected_error']) {
                echo sprintf('Error with Exception Test %d, The test correctly threw an exception but the message did not match the expected error message.', $test_error_count);
                echo '<br><br>';
                echo $e->getMessage();
                echo '<br><br>';
                echo json_encode($test, JSON_PRETTY_PRINT);
                exit();
            }
        }
    }

    // Force [headers_sent()] to return true by sending a header, content,
    // and flushing the output buffer.
    header('X-Test-Redirect: Testing');
    echo '[redirect-errors]';
    ob_flush();

    // The final error test requires [headers_sent() === true]
    try {
        $test_error_count++;
        $app->redirect('redirected');
    } catch (\Exception $e) {
        $expected = 'Error trying to redirect from [FastSitePHP\Application->redirect()] because Response Headers have already been sent to the client.';
        if ($e->getMessage() !== $expected) {
            echo sprintf('Error with Exception Test %d, The test correctly threw an exception but the message did not match the expected error message.', $test_error_count);
            echo '<br><br>';
            echo $e->getMessage();
            echo '<br><br>';
            echo json_encode($expected, JSON_PRETTY_PRINT);
            exit();
        }
    }

    //SendSuccess all Errors Tests returned the expected Exception Text
    echo '[Tested Errors: ' . $test_error_count . ']';
    exit();
});

// Filter this route to redirect using the filter function.
// This function gets called from both the Web Page for Redirect Testing
// and from the Command Lines Tests as a POST Request from [docs/unit-testing].
$app->route('/redirect-filter', function() use ($app) {
    throw new \Exception('This function should never get called');
})
->filter($redirectFilter);

// Route that gets called from $redirectFilter
$app->get('/redirected', function() use ($app) {
    return 'Route was redirected';
});

// Filter Test, one of these filters returns false so this route never gets called.
// It's defined again below.
$app->get('/filter-test-1', function() use ($app) {
    throw new \Exception('This function should never get called');
})
->filter($emptyFilter)
->filter($returnFalseFilter);

// Filter Test, this route is defined twice, this should be the final output
$app->get('/filter-test-1', function() use ($app) {
    echo 'filter-test-1';
})
->filter($emptyFilter);

// Filter Test, the filter returns false so this route never gets called
// and the result should be an error sent to the client.
$app->get('/skip-route-test', function() use ($app) {
    throw new \Exception('This function should never get called');
})
->filter($returnFalseFilter);

// Route defined mulitple times - the 2nd one gets called because the first returns nothing
$app->get('/route-defined-twice-1', function() use ($app) {
});
$app->get('/route-defined-twice-1', function() use ($app) {
    echo 'route-defined-twice-1 - function 2';
});

// Route defined mulitple times - the 1st one gets called
$app->get('/route-defined-twice-2', function() use ($app) {
    echo 'route-defined-twice-2 - function 1';
});
$app->get('/route-defined-twice-2', function() use ($app) {
    echo 'route-defined-twice-2 - function 2';
});

// Filter Test where the filter is setting a custom property in the app object
$app->get('/update-app-filter', function() use ($app) {
    if (!isset($app->custom_property)) {
        throw new \Exception('app.custom_property is not defined');
    }
    return $app->custom_property;
})
->filter($updateAppFilter);

// Filter Error Test
$app->get('/invalid-filter-test', function() {
    echo 'invalid-filter-test';
})
->filter(0);

// Test a specific error where the controller is overwritten with an invalid variable type
$app->get('/invalid-controller-test', function() {
    echo 'invalid-controller-test';
})
->controller = 123;

// Parameter Test
$app->get('/hello/:name', function($name) use ($app) {
    return 'Hello ' . $app->escape($name);
});

// Parameter Test 2
$app->get('/record/:controller/:action/:id', function($controller, $action, $id) use ($app) {
    return array(
        'controller' => $controller,
        'action' => $action,
        'id' => $id,
    );
});

// Parameter Test 3
$app->get('/get-file/:file_name', function($file_name) use ($app) {
    echo $app->escape($file_name);
});

// Parameter Test 4 - Using Encoding of Spaces
// Example from JavaScript: encodeURIComponent("param test") + "/" + encodeURIComponent("page title with spaces")
$app->get('/param test/:page_title', function($page_title) use ($app) {
    echo $app->escape($page_title);
});

// Parameter Test 5
$app->get('/param-test-5/:value1/:value2', function($value1, $value2) use ($app) {
    return array(
        'value1' => $value1,
        'value2' => $value2,
    );
});

// Parameter Test 6 - similar to the above function however
// $values1 and $values2 are switched, this show that the name of the variable
// in the route path string has no impact on the callback function parameters.
$app->get('/param-test-6/:value1/:value2', function($value2, $value1) use ($app) {
    return array(
        'value1' => $value1,
        'value2' => $value2,
    );
});

// Parameter Test 7 - check that $app->param() calls work as expected
$app->get('/param-validation-test-1/:product_id/:range1/:range2/:range3', function($product_id, $range1, $range2, $range3) use ($app) {
    return array(
        'product_id' => $product_id,
        'range1' => $range1,
        'range2' => $range2,
        'range3' => $range3,
    );
});

// Parameter Test 8 - check that $app->param() calls work as expected
$app->get('/param-validation-test-2/:float1/:float1/:float2', function($float1, $float2, $float3) use ($app) {
    return array(
        'float1' => $float1,
        'float2' => $float2,
        'float3' => $float3,
    );
});

// Parameter Test 9 - check that $app->param() calls work as expected
$app->get('/param-validation-test-3/:bool1/:bool1/:bool1/:bool1/:bool1/:bool1/:bool1/:bool1', function($bool1, $bool2, $bool3, $bool4, $bool5, $bool6, $bool7, $bool8) use ($app) {
    return array(
        'bool1' => $bool1,
        'bool2' => $bool2,
        'bool3' => $bool3,
        'bool4' => $bool4,
        'bool5' => $bool5,
        'bool6' => $bool6,
        'bool7' => $bool7,
        'bool8' => $bool8,
    );
});

// Parameter Test 10 - check that only a ":" is needed to specify a variable
$app->get('/param-test-10/:/:', function($value1, $value2) use ($app) {
    return array(
        'value1' => $value1,
        'value2' => $value2,
    );
});

// Parameter Test 11 - check that only a ":" is needed to specify a variable
$app->get('/param-test-11/:year?/:month?', function($year = 2015, $month = 12) use ($app) {
    return array(
        'year' => $year,
        'month' => $month,
    );
});

// HTML Escape Test
$app->get('/escape', function() use ($app) {
    echo $app->escape('<script>&"\'') . '[' . $app->escape(null) . ']';
});

// Testing param() error messages
$app->get('/param-error-1', function() use ($app) {
    try {
        $app->param(1, 'any');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-2', function() use ($app) {
    try {
        $app->param(':', 'any');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-3', function() use ($app) {
    try {
        $app->param('name', 'any');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-4', function() use ($app) {
    try {
        $app->param(':duplicate', 'any');
        $app->param(':duplicate', 'any');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-5', function() use ($app) {
    try {
        $app->param(':error', '');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-6', function() use ($app) {
    try {
        $app->param(':error', 0);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-7', function() use ($app) {
    try {
        $app->param(':error', 'any', 'any');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing param() error messages
$app->get('/param-error-8', function() use ($app) {
    try {
        $app->param(':error', 'any', 0);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Error Test using the default error template
$app->get('/exception', function() use ($app) {
   throw new \Exception('Exception Test');
});

// Error Test using the default error template
// This will also get called with an OPTIONS request.
$app->get('/exception-in-filter', function() use ($app) {
   return "Should never get called";
})
->filter(function() {
    throw new \Exception('Exception in Filter');
});

// Error Test with Error Type E_ERROR
$app->get('/error-fatal', function() use ($app) {
    // Unhandled exception, PHP processing will stop by default
    // however because $app->setup() is called this error will be handled
    // by the shutdown() function.
    $test = new UnknownObject();
});

// Error Test with Error Type E_WARNING
$app->get('/error-warning', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        // In case this becomes a different error in future versions of PHP
        // use the following URL to find new warning errors:
        // https://github.com/php/php-src/search?l=C&p=1&q=E_WARNING
        session_destroy();
    }
    // Divide by zero error, depending upon the PHP settings processing would
    // continue however because $app->setup() is called this error will be handled
    echo 1 / 0;
});

// Error Test with Error Type E_WARNING with the setting [track_errors] turned on.
// Track errors when used in combination with [$app->setup()] allows for
// the PHP predefined variable [$php_errormsg] to be set.
// As of PHP 7.2.0 [$php_errormsg] is being DEPRECATED
$app->get('/error-track-errors', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        return 'Skipping Test, PHP version is 8 or above';
    }
    ini_set('track_errors', '1');
    error_reporting(0);
    $value = 1 / 0;
    echo '[$php_errormsg: ' . (isset($php_errormsg) ? $php_errormsg : '$php_errormsg is not set') . ']';
});

// Error Test with PHP 5.* Error Type E_PARSE or PHP 7 Throwable ParseError
$app->get('/error-parse', function() use ($app) {
    // The following file contains invalid PHP Syntax
    require('./test-app-parse-error.php');
});

// Error Test with Error Type E_NOTICE
$app->get('/error-notice', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        // Many notice and warning errors in PHP 5 and 7 have been converted to errors in PHP 8.
        // In the future as these change search through PHP C Source Code as needed to find E_NOTICE errors:
        //   https://github.com/php/php-src/search?l=C&p=1&q=E_NOTICE
        date_default_timezone_set('test');
    }
    // Try to use an undefined variable, by default PHP processing would continue
    // however because $app->setup() is called this error will be handled
    echo $undefined_variable;
});

// Error Test with PHP 5.* Error Type E_RECOVERABLE_ERROR
// or PHP 7 Throwable TypeError
$app->get('/error-recoverable', function() use ($app) {
    // Invalid call to function
    showObject("");
});

// Error Test with Error Type E_DEPRECATED
$app->get('/error-deprecated', function() use ($app) {
    // An unknown condition has been seen on two computers where this
    // specific URL where it hangs and causes PHP to crash resulting
    // in a White-Screen-of-Death page (WSOD).:
    //  - One running 32-bit Windows 7 with IIS Express and PHP 5.4
    //  - Linux / CentoOS on AWS Lightsail using PHP 5.4 
    // All other unit tests on the Windows computer worked correctly and
    // when running directly using PHP CLI from the command prompt without IIS
    // Express there was no error.
    // The cause of the error is not known but if the computer you are testing on
    // has this error then un-commenting the line below can fix this issue.
    // The actual source of the error is the following line of code from
    // the errorHandler() function:
    //   throw new \ErrorException($message, 0, $severity, $file, $line);
    // Other tested computers with the same Windows/PHP/IIS Versions did not
    // have this issue. If this error were to happen on a Production Server
    // then PHP should probably be re-installed.
    //
    // ini_set('display_errors', 'on');

    // As of PHP 5.3 function split() is deprecated and
    // because $app->setup() is called this error will be handled
    if (PHP_MAJOR_VERSION === 5) {
        $data = split(',', 'a,b,c');
        echo join(',', $data);
    } elseif (PHP_MAJOR_VERSION === 7) {
        // In PHP 7 the split() function was removed so test by calling
        // a non-static function of a class using a static function call.
        class DeprecatedTest {
            function nonStaticFunction() {
                echo 'Called DeprecatedTest->nonStaticFunction()';
            }
        }
        echo DeprecatedTest::nonStaticFunction();
    } else {
        // PHP 8
        // Additional logic will likely be needed for
        // each future Major release of PHP.
        eval('function test($a = [], $b) { }');
    }
});

// Error Test with a User Error called from trigger_error()
$app->get('/error-user-error', function() use ($app) {
    trigger_error("User Error Test", E_USER_ERROR);
});

// Error Test with a User Warning called from trigger_error()
$app->get('/error-user-warning', function() use ($app) {
    trigger_error("User Warning Test", E_USER_WARNING);
});

// Error Test with a User Notice called from trigger_error()
$app->get('/error-user-notice', function() use ($app) {
    trigger_error("User Notice Test", E_USER_NOTICE);
});

// Error Test with a User Deprecated called from trigger_error()
$app->get('/error-user-deprecated', function() use ($app) {
    trigger_error("User Deprecated Test", E_USER_DEPRECATED);
});

// Error Test with Error Type E_COMPILE_ERROR
$app->get('/error-compile-error', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        eval('class string {}');
    }
    require('missing-file.php');
});

// Error Test with Error Type E_STRICT
// NOTE - in PHP 7 all of the E_STRICT notices have been reclassified to other levels:
//   http://php.net/manual/en/migration70.incompatible.php
// In some versions of PHP 7 calling [SimpleClass::getValue()] will result
// with [E_DEPRECATED], however in some newer versions it doesn't raise and error
// or throw an Exception. In some versions of PHP 8 this generates the error
// and but not all installations so going forward this test returns a generic error.  
$app->get('/error-strict', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        throw new \Exception('Skipping E_STRICT for PHP 8+');
    }
    if (PHP_MAJOR_VERSION === 5) {
        echo SimpleClass::getValue();
    } else {
        $c = new SimpleClass();
        $c->prop = 'Prop Test';
        echo $c->prop;
    }
});

// Error Test for a PHP 7 Throwable ArithmeticError
$app->get('/error-arithmetic-error', function() use ($app) {
    if (PHP_MAJOR_VERSION === 5) {
        throw new \Exception('ArithmeticError are not in PHP 5');
    } else {
        echo 12345 << -1;
    }
});

// Error Test for a PHP 7 Throwable DivisionByZeroError.
// In PHP 5 this will trigger an E_WARNING Error.
$app->get('/error-division-by-zero-error', function() use ($app) {
    echo 12345 % 0;
});

// NOTE - PHP 7 Throwable AssertionError's are not unit tested
// because they require a setting to be defined in [php.ini]
// which should not be defined in production builds and typically
// by default. This is the only PHP 7 Throwable Error
// that is not unit tested here.
//
// Example code to trigger a Throwable AssertionError:
//   //Set [php.ini] - [zend.assertions = -1]
//   ini_set('assert.exception', 1);
//   assert(true === false);
// ----------------------------------------------------------------

// Error Test for a PHP 7.1 Throwable ArgumentCountError
// In Versions of PHP prior to 7.1 this will trigger an E_WARNING error
//
// NOTE - at the time of development (April 2017) this Error
// is currently undocumented on PHP's Website:
//   http://php.net/manual/en/class.error.php
//
// However it exists in all builds of PHP Starting with PHP 7.1.0:
//   https://github.com/php/php-src/blob/PHP-7.1.0/Zend/zend_exceptions.c
//
$app->get('/error-argument-count-error', function() use ($app) {
    // Define a function with 1 parameter
    function argument_error_test($param1) {
        echo $param1;
        return 'argument_error_test()';
    }

    // Call the function without passing any arguments
    echo argument_error_test();
});

// Error Testing using the Error Control Operator.
// In PHP Errors can be ignored for expressions by including
// the [@] character before the expression, however an error
// handler must be properly defined to handle the [@] operator.
// This unit tests verifies that the errorHandler(() function
// is properly set and that the following line in the function
// works as expected.
//   if (!(error_reporting() & $severity)) {
//
// Resource Link:
//   http://php.net/manual/en/language.operators.errorcontrol.php
$app->get('/error-control-operator', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        // PHP 8 no longer silences fatal errors so a E_NOTICE error is used
        $result = @date_default_timezone_set('test');
        echo '@date_default_timezone_set(test) = ' . ($result === false ? 'false' : $result);
    } else {
        // Pass this PHP File to the following image function which
        // will trigger and error because it is not a valid image.
        // As long as the errorHandler() function is properly defined
        // then this should return false.
        $result = @file(null);
        echo '@file(null) === ' . ($result === false ? 'false' : $result);
    }
});

// Just like the above test this test is testing the line:
//   if (!(error_reporting() & $severity)) {
// However instead of using the Error Control Operator it's
// disabling error reporting prior to the function call.
$app->get('/error-reporting-disabled', function() use ($app) {
    error_reporting(0);
    if (PHP_VERSION_ID >= 80000) {
        $result = date_default_timezone_set('test');
        echo 'date_default_timezone_set(test) = ' . ($result === false ? 'false' : $result);
    } else {
        $result = file(null);
        echo 'file(null) === ' . ($result === false ? 'false' : $result);    
    }
});

// See the above two unit tests. Without error handling defined this error (often
// caused by bad user input or data) would prevent a script from properly running
// however FastSitePHP converts errors to Exceptions so this should so the error
// page with an ErrorException.
$app->get('/error-control-operator-not-used', function() use ($app) {
    if (PHP_VERSION_ID >= 80000) {
        $result = date_default_timezone_set('test');
        echo 'date_default_timezone_set(test) = ' . $result;
    } else {
        $result = file(null);
        echo 'file(null) === ' . $result;    
    }
});

// Similar to the above three unit tests, however this test uses
// a try/catch block to handle the error.
$app->get('/error-try-catch-instead-of-control-operator', function() use ($app) {
    try {
        if (PHP_VERSION_ID >= 80000) {
            $result = date_default_timezone_set('test');
            echo 'date_default_timezone_set(test) = ' . $result;
        } else {
            $result = file(null);
            echo 'file(null) === ' . $result;
        }
    } catch (\Exception $e) {
        echo '[' . get_class($e) . ']: ' . $e->getMessage();
    }
});

// This test is designed to test the call [header_remove()] from
// [$app->sendErrorOr404()]. If [header_remove()] were not called
// then an invalid image would appear on the browser but because
// it is called before  the error page is properly displayed.
$app->get('/error-change-content-type', function() use ($app) {
    header('Content-Type: image/png');
    readfile(null);
});

// Test for an Expected Error when setting a Status Code of 304 'Not Modified'.
// 304 Response Types are only supported by the [Web\Response] Object
// and not the [Application] Object.
$app->get('/error-status-code-304', function() use ($app) {
   $app->statusCode(304);
   return 'Not Modified';
});

// Test to verify that dynamic functions can be added to the Application Object.
// This test is verifying that the PHP 'magic method' __call  is defined and working correctly.
$app->get('/dynamic-functions', function() use ($app) {
    // Add two functions to the Application Object
    $app->test = function() { return 'called from test()'; };
    $app->test2 = function($value) use ($app) {
        return sprintf('called from test2(%s)', $app->escape($value));
    };

    // The [__call] method is specifically checking for [($this->{$name} instanceof \Closure)]
    // If it were instead checking [is_callable($this->{$name})] then the following property
    // would get called as a function.
    $app->test3 = 'phpinfo';

    // Call functions to make sure they work
    $output = sprintf('[%s]', $app->test());
    $output .= sprintf('[%s]', $app->test2('abc'));
    $output .= sprintf('[%s]', $app->test2(123));
    $output .= sprintf('[%s]', $app->test2('<&>'));

    // Error Test - Function doesn't exist
    try {
        $app->test4();
        $output .= '[Error Test 1 Failed]';
    } catch (\Exception $e) {
        $output .= sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Error Test - Property called as a function
    try {
        $app->test3();
        $output .= '[Error Test 2 Failed]';
    } catch (\Exception $e) {
        $output .= sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    return $output;
});

// Test to verify [methodExists()]
$app->get('/method-exists', function() use ($app) {
    $output = array($app->methodExists('setup') === true ? 'true' : 'false');
    $output[] = $app->methodExists('test') === true ? 'true' : 'false';
    $app->test = function() { };
    $app->test2 = 'test';
    $output[] = $app->methodExists('test') === true ? 'true' : 'false';
    $output[] = $app->methodExists('test2') === true ? 'true' : 'false';
    return implode(',', $output);
});

// Test a 'POST' Route with JSON Data
// This gets tested on the client-side page with both POST and OPTIONS requests
$app->post('/post-data', function() use ($app) {
	// Get the 'Content-Type' and Input sent with the Request
	// Normally this would be done with [\FastSitePHP\Web\Request()]
	// however this file is specificly only tesitng the Application and Route Objects.
    $content_type = (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_CONTENT_TYPE']);
    $input = file_get_contents('php://input');

    // Make sure input is correct
    if ($content_type !== 'application/json; charset=UTF-8') {
        return array('error' => 'Wrong inputType(): ' . $content_type);
    } elseif ($input !== '{"site":"FastSitePHP","page":"UnitTest"}') {
        return array('error' => 'Wrong inputText(): ' . $input);
    }

    // Return input JSON text string for JSON output
    return $input;
})
->filter($json_response);

// Test a 'PUT' Route
$app->put('/put-test-1', function() use ($app) {
	// Get the 'Content-Type' and Input sent with the Request
	// Normally this would be done with [\FastSitePHP\Web\Request()]
	// however this file is specificly only tesitng the Application and Route Objects.
    $content_type = (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : $_SERVER['HTTP_CONTENT_TYPE']);
    $input = file_get_contents('php://input');

    // Make sure input is the expected format and data
    if ($content_type !== 'application/json; charset=UTF-8') {
        return array('error' => 'Wrong content_type: ' . $content_type);
    } elseif ($input !== '{"data":"PUT Test"}') {
        return array('error' => 'Wrong input: ' . $input);
    }

    // Prepare the JSON Response, 201 (Created) is a
    // commonly used response for PUT requests
	$app->statusCode(201);
   return array('result' => 'success');
});

// Test a 'PUT' Route
$app->put('/put-test-2', function() use ($app) {
    // 204 (No Content) is a commonly used response for PUT/DELETE/PATCH requests.
    // This function is returning data however the Application never sends it to
    // the client. For a real application [return '';] could be used if sending
    // a 204 response with the Application Object.
   $app->statusCode(204);
   return 'No Content';
});

// Test a 'PUT' Route
$app->put('/put-test-3', function() use ($app) {
    // 205 (Reset Content) which can be used for PUT and DELETE Requests
    // however it is not a widely used status code. Just like the above test
    // the actual content will never be sent to the client.
    $app->statusCode(205);
    return 'Reset Content';
});

// Test a 'DELETE' Route
$app->delete('/delete-test-1', function() use ($app) {
    // 202 (Accepted) is a commonly used response for DELETE requests
   $app->statusCode(202);
   return array('result' => 'success');
});

// Test a 'PATCH' Route
$app->patch('/patch-test-1', function() use ($app) {
   // 204 (No Content) is a commonly used response for PUT/DELETE/PATCH requests
   $app->statusCode(204);
   return '';
});

// Test each named routing method with the same URL.
$app->get('/method', function() { return 'get()'; });
$app->post('/method', function() { return 'post()'; });
$app->put('/method', function() { return 'put()'; });
$app->patch('/method', function() { return 'patch()'; });
$app->delete('/method', function() { return 'delete()'; });

// Test a filter than turns options on or off from a before() callback
$app->route('/toggle-options', function() {
    return 'Called [toggle-options]';
});

// Testing mount() function with the default [case_sensitive_urls = true].
// This test also verifies that the mount() function is chainable
// based on different parameters. mount() allows for either full
// file path or only the file name so some of the files are located
// in the [mount-testing] sub-directory to confirm this.
//
// Tests:
//   test-app.php/mount/test
//   test-app.php/MOUNT/TEST
//   test-app.php/mount-file-not-found/test
//   test-app.php/mount-path-not-found/test
//   test-app.php/mount2/
//   test-app.php/check-for-mount-route
$app
    ->mount('/mount/', 'never-loaded.php', function() {
        // [never-loaded.php] doesn't exist however the
        // Condition Closure returns false so there is no error.
        return false;
    })
    ->mount('/mount/', 'test-app-mount.php')
    ->mount('/mount2', __DIR__ . '/mount-testing/test-mount1.php', function() {
        return true;
    })
    ->mount('/mount-path-not-found', __DIR__ . '/file-not-found.php')
    ->mount('/mount-file-not-found/', 'file-not-found.php');

// Routes defined in '/mount/' will only be defined from mount()
// if the '/mount/' url is used so this test confirms that it
// is not being loaded. To make this test fail call
// [ require('test-app-mount.php'); ] above this function.
$app->get('/check-for-mount-route', function() use ($app) {
    $route_to_find = '/mount/test';
    $route_was_found = false;

    $routes = $app->routes();
    foreach ($routes as $route) {
        if ($route->pattern === $route_to_find) {
            $route_was_found = true;
            break;
        }
    }

    return sprintf('Route [%s] was %s', $route_to_find, ($route_was_found ? 'found' : 'not found'));
});

// Testing the routes() function
$app->get('/get-routes', function() use ($app) {
    $routes = $app->routes();

    if (!($routes[0] instanceof \FastSitePHP\Route)) {
        return 'routes()[0] did not return an array of Routes';
    }

    if ($routes[0]->method !== 'GET') {
        return 'routes()[0]->method did not match the expected value: ' . $routes[0]->method;
    }

    if ($routes[0]->pattern !== '/check-server-config') {
        return 'routes()[0]->pattern did not match the expected value: ' . $routes[0]->pattern;
    }

    // Success
    return 'Success routes() returned expected data';
});

// Testing routeMatches() function
$app->get('/route-matches', function() use ($app) {
	// Define an array of tests to run
	$tests = array(
		// Testing basic routes
		array(
			'pattern' => '/page1',
			'path' => '/page2',
			'expected' => false,
		),
		array(
			'pattern' => '/show-all',
			'path' => '/show-all',
			'expected' => array(),
		),
		array(
			'pattern' => '/record/:id',
			'path' => '/record/123',
			'expected' => array('123'),
		),
		array(
			'pattern' => '/:record/:view/:id',
			'path' => '/orders/edit/123',
			'expected' => array('orders', 'edit', '123'),
		),
		// Testing using validated parameters [ ':product_id', ':range1', ':range2', ':range3' ].
		// Parameters validation is defined above in the section [$app->param()]
		array(
			'pattern' => '/product/:product_id',
			'path' => '/product/123',
			'expected' => array(123),
		),
		array(
			'pattern' => '/:range1/:range2/:range3',
			'path' => '/5/6/7',
			'expected' => array('5', 6, 7),
		),
		array(
			'pattern' => '/:range1',
			'path' => '/11',
			'expected' => false,
		),
		array(
			'pattern' => '/:range1/:range2',
			'path' => '/5/0',
			'expected' => false,
		),
		// Testing with ending wildcard characters
		array(
			'pattern' => '/page-list/*',
			'path' => '/page-list/',
			'expected' => array(),
		),
		array(
			'pattern' => '/page-list/*',
			'path' => '/page-list/page1/page2',
			'expected' => array(),
		),
		// The next two tests confirm the routeMatches() condition:
		//   ($pattern_count !== $path_count && $pattern_parts[$pattern_count - 1] !== '*')
		array(
			'pattern' => '/section',
			'path' => '/section/title',
			'expected' => false,
		),
		array(
			'pattern' => '/section/',
			'path' => '/section/title',
			'expected' => false,
		),
		// Testing with spaces in the route name and with the variable name.
		// This confirms in the routeMatches() function where the function urldecode() is called;
		// In most servers encoded "/" and "+" characters would not come in through the URL in
		// this manner but if a server is set to allow them then routeMatches() handles them.
		// path = "/" + encodeURIComponent("page list") + "/" + encodeURIComponent("page title with spaces and / and +")
		// from JavaScript Developer Tools in a Web Browser is used to create the URL
		array(
			'pattern' => '/page list/:page_title',
			'path' => '/page%20list/page%20title%20with%20spaces%20and%20%2F%20and%20%2B',
			'expected' => array('page title with spaces and / and +'),
		),
		// Boolean validation, this value should not match
		// $app->param(':bool1', 'bool');
		array(
			'pattern' => '/:bool1',
			'path' => '/abc',
			'expected' => false,
		),
		// Boolean validation, this value should match and have a false value for the arugment
		// $app->param(':bool2', 'any', 'bool');
		array(
			'pattern' => '/:bool2',
			'path' => '/abc',
			'expected' => array(false),
		),
		// Regular expression validation - these should match
		// $app->param(':regex1', '/^\d+$/');
		// $app->param(':regex2', '/^[a-zA-Z]*$/');
		array(
			'pattern' => '/:regex1/:regex2',
			'path' => '/123/abcABC',
			'expected' => array('123', 'abcABC'),
		),
		// Regular expression validation - The next two tests should not match
		array(
			'pattern' => '/:regex1',
			'path' => '/abcABC',
			'expected' => false,
		),
		array(
			'pattern' => '/:regex2',
			'path' => '/123',
			'expected' => false,
		),
		// A user calling '/about/' for route '/about' should match by default.
		// To change this behavior see [strict_url_mode] which is tested below
		array(
			'pattern' => '/about',
			'path' => '/about/',
			'expected' => array(),
		),
		// Same as above but using [strict_url_mode = true]
		// This route should not match
		array(
			'strict_url_mode' => true,
			'pattern' => '/about',
			'path' => '/about/',
			'expected' => false,
		),
		// Testing with a request for '/about' and route '/about/' which should not match.
		// If the developer wants to handle both cases they need to define the route definition as '/about'
		array(
			'pattern' => '/about/',
			'path' => '/about',
			'expected' => false,
		),
		// Testing with multiple slashes
		array(
			'pattern' => '/:section//:page',
			'path' => '/company//about',
			'expected' => array('company', 'about'),
		),
		// Testing Optional Variables with All Variables Defined
		array(
			'pattern' => '/search-by-date/:year?/:month?/:day?',
			'path' => '/search-by-date/2015/12/31',
			'expected' => array('2015', '12', '31'),
		),
		// Testing Optional Variables with not all Variables Defined (Two Tests)
		array(
			'pattern' => '/search-by-date/:year?/:month?/:day?',
			'path' => '/search-by-date/2015/12',
			'expected' => array('2015', '12'),
		),
		array(
			'pattern' => '/search-by-date/:year?/:month?/:day?',
			'path' => '/search-by-date/2015',
			'expected' => array('2015'),
		),
		// Testing Optional Variables with all Variables Missing
		array(
			'pattern' => '/search-by-date/:year?/:month?/:day?',
			'path' => '/search-by-date/',
			'expected' => array(),
		),
		// Testing Optional Variables with minimum optional variable text of ':?'
		array(
			'pattern' => '/search-by-date/:?/:?/:?',
			'path' => '/search-by-date/2015/12/31',
			'expected' => array('2015', '12', '31'),
		),
		// Testing Mixing Required and Optional Variables
		array(
			'pattern' => '/search-by-date/:year/:month?/:day?',
			'path' => '/search-by-date/2015',
			'expected' => array('2015'),
		),
		// Testing for an case-insensitive match. By default
		// [case_sensitive_urls = true] so 'PAGE' and 'page' do not
		// match, however if the property it set to false as done
		// with the 2nd of the 2 tests then they will be matched.
		array(
			'pattern' => '/DATA/:PAGE',
			'path' => '/data/About',
			'expected' => false,
		),
		array(
			'case_sensitive_urls' => false,
			'pattern' => '/DATA/:PAGE',
			'path' => '/data/About',
			'expected' => array('About'),
		),
	);

	// Run the Tests
	$test_count = 0;
	foreach ($tests as $test) {
		// Change Application Defaults Settings if specified
		if (isset($test['strict_url_mode'])) {
			$app->strict_url_mode = true;
		}
		if (isset($test['case_sensitive_urls'])) {
			$app->case_sensitive_urls = false;
		}

	    // Run Test and keep count of how many
	    $args = $app->routeMatches($test['pattern'], $test['path']);
        $test_count++;

        // Reset Application Defaults after the test
        $app->strict_url_mode = false;
        $app->case_sensitive_urls = true;

        // Check result if it does not match output the test and end the response
	    if ($args !== $test['expected']) {
            echo sprintf('Error with Test %d', $test_count);
            echo '<br><br>';
            echo '<strong>Pattern: </strong>' . $test['pattern'];
            echo '<br><strong>Path: </strong>' . $test['path'];
            echo '<br><strong>Expected: </strong>';
            echo json_encode($test['expected'], JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Result: </strong>';
            echo json_encode($args, JSON_PRETTY_PRINT);
            exit();
	    }
    }

    // Define an array of error tests to run
    $error_tests = array(
		array(
			'pattern' => '/start/*/end/',
			'path' => '/start/data/end/',
			'expected_error' => 'The function [FastSitePHP\Application->routeMatches()] was called with a wild-card character in the middle of the route definition. A wild-card can only be used at the end. Route: [/start/*/end/]',
		),
		array(
			'pattern' => 0,
			'path' => 0,
			'expected_error' => 'Error Unexpected Parameter Type, $pattern must be a string when [FastSitePHP\Application->routeMatches()] is called.',
		),
		array(
			'pattern' => '',
			'path' => 0,
			'expected_error' => 'Error Invalid Parameter, $pattern must be 1 or more characters in length when [FastSitePHP\Application->routeMatches()] is called.',
		),
		array(
			'pattern' => '/',
			'path' => 0,
			'expected_error' => 'Error Unexpected Parameter Type, $path must be a string when [FastSitePHP\Application->routeMatches()] is called.',
		),
		array(
			'pattern' => '/',
			'path' => '',
			'expected_error' => 'Error Invalid Parameter, $path be 1 or more characters in length when [FastSitePHP\Application->routeMatches()] is called.',
		),
		array(
			'pattern' => '/search-by-date/:year?/:month?/:day',
			'path' => '/search-by-data/2015/12/31',
			'expected_error' => 'Error Invalid Route Definition, the function [FastSitePHP\Application->routeMatches()] was called with a route having a required value after an optional value. All optional variables must be defined at the end of the route definition. Route: [/search-by-date/:year?/:month?/:day]',
		),
    );

    // Test for exceptions, each of these tests
    // is expected to thrown an exception.
    $test_error_count = 0;

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;

            // Test
            $args = $app->routeMatches($test['pattern'], $test['path']);

            // If the test doesn't error that there is a problem
            echo sprintf('Error with Exception Test %d, The test did not fail but should have thrown an exception.', $test_error_count);
            echo '<br><br>';
            echo '<strong>Result: </strong> ';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Test: </strong> ';
            echo json_encode($test, JSON_PRETTY_PRINT);
            exit();
        } catch (\Exception $e) {
            if ($e->getMessage() !== $test['expected_error']) {
                echo sprintf('Error with Exception Test %d, The test correctly threw an exception but the message did not match the expected error message.', $test_error_count);
                echo '<br><br>';
                echo $e->getMessage();
                echo '<br><br>';
                echo json_encode($test, JSON_PRETTY_PRINT);
                exit();
            }
        }
    }

    // All Tests passed if code execution reaches here
    return sprintf('Success for routeMatches() function, Completed %d Unit Tests and %d Exception Tests', $test_count, $test_error_count);
});

// Test routeMatches() with an invalid regular expression validation for the parameter.
// This function is duplicated in two files and tested twice to make sure that
// the correct exception is thrown regardless of whether setup() is called or not:
//  1) test-app.php
//  2) test-app-no-setup.php
$app->get('/route-matches-param-error', function() use ($app) {
    // [error_reporting] is changed and reset by this function call.
    // Make sure it is set back to the original value.
    $current_error_level = error_reporting();

    // Define the Parameter
    $app->param(':regex_invalid', 'ABC');

    // Run Test
    try {
        $args = $app->routeMatches('/:regex_invalid', '/123');
        echo 'This line should never get called: ' . $matches;
    } catch (\Exception $e) {
        // Check original value
        if ($current_error_level !== error_reporting()) {
            return 'error_reporting was not reset';
        }
        return $e->getMessage();
    }
});

// Similar to the above Test routeMatches() is tested with invalid regular expression
// however a custom error handler function is defined that does return a value.
// Because no value is returned when set_error_handler() is called the error variable
// Just like the above test this is also tested in two separate files.
$app->get('/route-matches-custom-error', function() use ($app) {
    // Overwrite any existing Error Handler
    set_error_handler(function($severity, $message, $file, $line) {
        return;
    });

    // Define the Parameter and Run Test
    $app->param(':regex_invalid', 'ABC');
    try {
        $app->routeMatches('/:regex_invalid', '/123');
        return 'This line should never get called: routeMatches(:regex_invalid)';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Testing noCache() Function
$app->get('/no-cache', function() use ($app) {
    // Test that when setting noCache() is chainable
    // and that when calling with a null parameter the
    // current value is returned.
    $passed_tests = 0;

    // First Set
    $no_cache = $app->noCache()->noCache(null);
    if ($no_cache === true) {
        $passed_tests++;
    }

    // Clear
    $no_cache = $app->noCache(false)->noCache(null);
    if ($no_cache === false) {
        $passed_tests++;
    }

    // Return the Response with the Headers Set
    $app->noCache();
    return "noCache() Tests Passed: $passed_tests";
});

// Include 2 of the same routes when testing a 405 'Method not allowed'
// Response to confirm that methods names sent to the client are unique.
$app->post('/405', function() use ($app) {
    return '[bad-method-call]';
});
$app->post('/405', function() use ($app) {
    return '[bad-method-call]';
});

// This route results in [allow_options_requests] being set to false
// by a before() event near the top of this file.
$app->post('/405-options', function() use ($app) {
    return '[405-options]';
});

// Testing of the cors() Function.
// This function is validating logic of the function but does
// not send any CORS headers to the client. Testing of actual
// CORS headers is handled in additional tests from this file
// and also from [test-web-request.php].
$app->get('/cors-validation', function() use ($app) {
    // Keep Count of Tests
    $error_count = 0;
    $passed_count = 0;

    // Check initial header values for cors()
    if ($app->cors() !== null) {
        return 'Function cors() should have returned null';
    }
    $passed_count++;

    // Define Error Tests
    $error_tests = array(
        // Wrong Data Type
        array(
            'Access-Control-Allow-Origin' => 0,
            'expexcted_error' => 'Invalid variable type for [Access-Control-Allow-Origin] of [integer] from [FastSitePHP\Application->cors()], the value must be set and must be a string. Valid parameters for [$origin_or_headers] are [string|array|null]; refer to documentation for usage and examples.',
        ),
        // Missing http or https
        array(
            'Access-Control-Allow-Origin' => 'domain.tld',
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [domain.tld] from [FastSitePHP\Application->cors()]. When using the [cors()] function the value if not [*] must begin with either [http://] or [https://].',
        ),
        // Error multiple domains using a space ' '
        array(
            'Access-Control-Allow-Origin' => 'http://domain.tld http://domain2.tld',
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [http://domain.tld http://domain2.tld] from [FastSitePHP\Application->cors()]. When using the [cors()] function the URL value must contain only one domain and it appears multiple domains were specified.',
        ),
        // Error multiple domains using ','
        array(
            'Access-Control-Allow-Origin' => 'http://domain.tld,http://domain2.tld',
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [http://domain.tld,http://domain2.tld] from [FastSitePHP\Application->cors()]. When using the [cors()] function the URL value must contain only one domain and it appears multiple domains were specified.',
        ),
        // Error multiple domains using ';'
        array(
            'Access-Control-Allow-Origin' => 'http://domain.tld;http://domain2.tld',
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [http://domain.tld;http://domain2.tld] from [FastSitePHP\Application->cors()]. When using the [cors()] function the URL value must contain only one domain and it appears multiple domains were specified.',
        ),
        // Error full url instead of origin which is [http(s)://domain.tld]
        array(
            'Access-Control-Allow-Origin' => 'http://domain.tld/',
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [http://domain.tld/] from [FastSitePHP\Application->cors()]. When using the [cors()] function the URL value must contain only the protocol and domain rather than a full URL (e.g.: [http://domain.tld] vs [http://domain.tld/page]).',
        ),
        // Unsupported Headers
        array(
            // Using different case strings to verify the final error output
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'ACCESS-CONTROL-ALLOW-METHODS' => 'GET',
                'Access-Control-Allow' => '*',
                'ALLOW' => 'GET',
                'X-Header' => 'Test',
            ),
            'expexcted_error' => 'Unsupported headers [Access-Control-Allow], [Allow], [X-Header] were specified when the function [FastSitePHP\Application->cors()] was called. The only headers that this function supports are valid headers for Cross-Origin Resource Sharing (CORS): [Access-Control-Allow-Origin], [Access-Control-Allow-Credentials], [Access-Control-Expose-Headers], [Access-Control-Max-Age], [Access-Control-Allow-Methods], [Access-Control-Allow-Headers]',
        ),
        // Invalid Value for [Access-Control-Allow-Credentials]
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'access-control-allow-credentials' => 'false',
            ),
            'expexcted_error' => 'The only valid value for Header [Access-Control-Allow-Credentials] is [true]; if you do not need the value to be set to true then do not include it when calling [FastSitePHP\Application->cors()]. The value that causes this error was: [false].',
        ),
        // Invalid Orgin when using [Access-Control-Allow-Credentials]
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'ACCESS-CONTROL-ALLOW-CREDENTIALS' => 'true',
            ),
            'expexcted_error' => 'When using header [Access-Control-Allow-Credentials => true] specified from [FastSitePHP\Application->cors()] the server must respond using an origin rather than specifying a [*] wildcard. The requested origin can be obtained from [FastSitePHP\Web\Request->origin()]. You can then use the value from the request header to validate if it is a valid request and then send the origin back using the [Access-Control-Allow-Origin] header.',
        ),
        // Invalid Values for [Access-Control-Max-Age]
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'access-control-max-age' => 'abc',
            ),
            'expexcted_error' => 'Invalid field value data type for [Access-Control-Max-Age] of [string] from [FastSitePHP\Application->cors()]. When this header is specified the value must be an integer (number) or a string that converts to an integer or a string that converts to an integer.',
        ),
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'access-control-max-age' => '-1',
            ),
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [-1] from [FastSitePHP\Application->cors()]. The field value must be a number between 0 and 86400 (seconds in a 24 hour time frame). Different browsers handle this differently for example versions of Firefox support up to 24-hours (86400 seconds) and versions of Chrome support up to 10 minutes (600 seconds).',
        ),
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => '*',
                'access-control-max-age' => 86401,
            ),
            'expexcted_error' => 'Invalid value for [Access-Control-Allow-Origin] of [86401] from [FastSitePHP\Application->cors()]. The field value must be a number between 0 and 86400 (seconds in a 24 hour time frame). Different browsers handle this differently for example versions of Firefox support up to 24-hours (86400 seconds) and versions of Chrome support up to 10 minutes (600 seconds).',
        ),
        array(
            // Using different case strings to verify the final error output
            'origin_or_headers' => array('Access-Control-Allow-Methods' => 'GET'),
            'expexcted_error' => 'Invalid variable type for [Access-Control-Allow-Origin] of [NULL] from [FastSitePHP\Application->cors()], the value must be set and must be a string. Valid parameters for [$origin_or_headers] are [string|array|null]; refer to documentation for usage and examples.',
        ),
    );

    // Run Error Tests
    foreach ($error_tests as $test) {
        // Errors for the [Access-Control-Allow-Origin] are each tested twice,
        // once as a string/raw value and once in an Array.
        if (isset($test['Access-Control-Allow-Origin'])) {
            try {
                $app->cors($test['Access-Control-Allow-Origin']);
                return 'Error - Test Passed but Should Have Failed, Test #' . $error_count;
            } catch (\Exception $e) {
                if ($e->getMessage() !== $test['expexcted_error']) {
                    echo 'Error - Wrong Error Message, Test #.' . $error_count;
                    echo '<br><br>';
                    echo 'Error Text: ' . $e->getMessage();
                    echo '<br><br>';
                    echo 'Expected: ' . $test['expexcted_error'];
                    exit();
                }
                $error_count++;
            }
            try {
                $app->cors(array('Access-Control-Allow-Origin' => $test['Access-Control-Allow-Origin']));
                return 'Error - Test Passed but Should Have Failed, Test #' . $error_count;
            } catch (\Exception $e) {
                if ($e->getMessage() !== $test['expexcted_error']) {
                    echo 'Error - Wrong Error Message, Test #.' . $error_count;
                    echo '<br><br>';
                    echo 'Error Text: ' . $e->getMessage();
                    echo '<br><br>';
                    echo 'Expected: ' . $test['expexcted_error'];
                    exit();
                }
                $error_count++;
            }
        } else {
            try {
                $app->cors($test['origin_or_headers']);
                return 'Error - Test Passed but Should Have Failed, Test #' . $error_count;
            } catch (\Exception $e) {
                if ($e->getMessage() !== $test['expexcted_error']) {
                    echo 'Error - Wrong Error Message, Test #.' . $error_count;
                    echo '<br><br>';
                    echo 'Error Text: ' . $e->getMessage();
                    echo '<br><br>';
                    echo 'Expected: ' . $test['expexcted_error'];
                    exit();
                }
                $error_count++;
            }
        }
    }

    // Make sure no value was set
    if ($app->cors() !== null) {
        return 'Error CORS value was set';
    }
    $passed_count++;

    // Define Tests for valid function calls
    $tests = array(
        // Basic call allowing all origins and setting
        // only the [Access-Control-Allow-Origin] header
        array(
            'origin_or_headers' => '*',
            'return_value' => array(
                'Access-Control-Allow-Origin' => '*',
            ),
        ),
        // All Header Values
        array(
            'origin_or_headers' => array(
                'Access-Control-Allow-Origin' => 'http://www.fastsitephp.com',
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Expose-Headers' => 'Content-Length',
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Allow-Methods' => 'GET',
                'Access-Control-Allow-Headers' => 'Content-Type',
            ),
        ),
        // Clear previous headers
        array(
            'origin_or_headers' => '',
            'return_value' => null,
        ),
    );

    // None of these tests are expected to
    // error so no need for try/catch.
    foreach ($tests as $test) {
        // Run test and call cors() after to
        // confirm the method is chainable.
        $return_value = $app->cors($test['origin_or_headers'])->cors();
        $expected_value = (array_key_exists('return_value', $test) ? $test['return_value'] : $test['origin_or_headers']);

        // Compare and show error if invalid
        if ($return_value !== $expected_value) {
            echo sprintf('Error with CORS Test %d, Return value did not match expected result.', $passed_count);
            echo '<br><br>';
            echo json_encode($return_value, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo json_encode($expected_value, JSON_PRETTY_PRINT);
            exit();
        }
        $passed_count++;
    }

    // All Tests Passed
    return sprintf('Success checked cors() with %d passed tests and %d exception tests', $passed_count, $error_count);
});

// Set the Header Value for [Access-Control-Allow-Origin] using [cors()].
// Because the header is set from a filter it is handled on OPTIONS requests
// and doesn't not affect any other routes. Also when this route is called
// with an OPTIONS request the header [Access-Control-Allow-Methods] will
// be added (confirmed with the JavaScript code), however on the actual GET
// request/response the header is not added.
// A similar route also exists in [test-web-response.php].
$app->get('/cors-1', function() {
    return 'Testing cors() [Access-Control-Allow-Origin] with a String Value';
})
->filter(function() use ($app) {
    $app->cors('*');
});

// Set Multiple CORS Header Values using [cors()].
// For more refer to comments from route '/cors-1'.
// A similar route also exists in [test-web-response.php].
$app->get('/cors-2', function() {
    return 'Testing cors() [Access-Control-Allow-Origin, Access-Control-Allow-Headers] with an Array';
})
->filter(function() use ($app) {
    $app->cors(array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
    ));
});

// Testing cors() while using route() which verifies that the
// header 'Access-Control-Allow-Methods' ends up with the value
// 'HEAD, GET, POST, OPTION' which is also the same value as the
// 'Allow' header for a route() call.
$app->route('/cors-3', function() {
    return 'Testing cors() from route()';
})
->filter(function() use ($app) {
    $app->cors('*');
});

// Similar to the above test from '/cors-3' however this version
// overrides the default 'Access-Control-Allow-Methods' and 'Allow' Headers.
$app->route('/cors-4', function() {
    return 'Testing cors() from route() with custom allow headers';
})
->filter(function() use ($app) {
    $app->allow_methods_override = 'HEAD, GET, PUT, OPTIONS';
    $app->cors(array(
	    'Access-Control-Allow-Origin' =>  '*',
	    'Access-Control-Allow-Methods' => 'HEAD, GET, PUT, OPTIONS',
    ));
});

// header() error message
$app->get('/header-error-1', function() use ($app) {
    try {
        $app->header(123);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// header() error message
$app->get('/header-error-2', function() use ($app) {
    try {
        $app->header('');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Test functions [headers()] and [header()]
$app->get('/headers', function() use ($app) {
    // Get all response headers
    $response_headers = $app->headers();
    if (count($response_headers) !== 0) {
        return 'Error - There should not be any Response headers created before setting one';
    }

    // Set a customer Response Header, this confirms the function
    // is chainable and that it is case-insenstive as the 2nd call
    // should overwrite the first call
    $app
        ->header('X-API-KEY', '123abc')
        ->header('X-API-Key', '456xyz');

    $response_headers = $app->headers();
    if (count($response_headers) !== 1) {
	    return 'Error - There should only be one Response header at this point';
    }

    // Even though value was overwritten the first header key remains
    if ($response_headers['X-API-KEY'] !== '456xyz') {
	    return 'Error - [X-API-KEY] should be found from the headers() function';
    }

    // Getting the header using an all lower-case key
    if ($app->header('x-api-key') !== '456xyz') {
	    return 'Error - [X-Custom-Header] should be found from the header() function';
    }

    // Clear the header and get the headers again, this also makes sure that clearing
    // a value returns the app object
    $response_headers = $app
        ->header('x-api-key', '')
        ->headers();

    if (count($response_headers) !== 0) {
	    return 'Error - There should not be any Response headers after clearing the created value';
    }

    // Success for how the header() function works.
    // Add back the header with a new value and return the response.
    // The client still needs to verify the actual header.
    $app
        ->header('Content-Type', 'text/plain')
        ->header('X-API-Key', 'test123');
    return 'Testing of $app.headers() and $app.header()';
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
