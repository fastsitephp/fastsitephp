<?php
// ===========================================================
// Unit Testing Page
// *) This file uses only core Framework files and the 
//	  Web\Request Object.
// ===========================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Web/Request.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
    require '../src/Web/Request.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Create Route Filter Functions
// -----------------------------------------------------------

// CORS function to set the header 'Access-Control-Allow-Origin' when it is added to a route
$addCorsHeaders = function() use ($app) {
    // When the route for this function gets called using a GET request on the
    // same domain origin() will return null, however when OPTIONS request is
    // submitted it will return the origin "domain". If the request were to come in from
    // a browser on a different domain then origin() would always return a value
    // because browsers submit the 'Origin' Request Header on different domains. 
    // See reference links from the cors() and origin() functions for more.
    //
    // In JavaScript using all recent browser versions the origin() is the value
    // from [window.location.origin]. With older version of IE it can be determined
    // using a Polyfill. This is handled in the client-side unit test page.
    $req = new \FastSitePHP\Web\Request();
    $origin = $req->origin();
    
    // Work around so the unit test will work as expected on all Browsers.
    // At the time of writing (early 2017), versions of Edge, IE 11, and Firefox 
    // will not submit the [Origin] header on localhost when using the same domain.
    // [origin()] reads from the Server Variable HTTP_ORIGIN.
    if ($origin === null && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        $_SERVER['HTTP_ORIGIN'] = $req->protocol() . '://' . $req->host();
        $origin = $req->origin();
    }

    // This should return '*' for a GET and the Domain on an OPTIONS request
    $app->cors($origin === null ? '*' : $origin);
};

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Check how the Request Object is defined
$app->get('/check-request-class', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    return array(
        'get_class' => get_class($req),
        'get_parent_class' => get_parent_class($req),
    );
});

// Check Default Request Object Properties
$app->get('/check-request-properties', function() use ($app) {
    // Define arrays of properties by type
    $null_properties = array();
    $true_properties = array();
    $false_properties = array();
    $string_properties = array();
    $array_properties = array();
    $private_properties = array(
        'saved_input_stream', 
    );
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $req = new \FastSitePHP\Web\Request();
    return checkObjectProperties($req, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
});

// Check Application Functions, this is similar to the above function
// but instead of checking properties it checks the application functions.
$app->get('/check-request-methods', function() use ($app) {
    // Define arrays of function names by type
    $private_methods = array(
        'proxyHeader', 'acceptHeader', 'fixIp',
    );
    $public_methods = array(
        'queryString', 'form', 'cookie', 'verifiedCookie', 'decryptedCookie',
        'value', 'header', 'headers', 'contentType', 'content', 
        'contentText', 'isXhr', 'origin', 'userAgent', 'referrer', 
        'clientIp', 'protocol', 'host', 'port', 'serverIp', 
        'isLocal', 'accept', 'acceptCharset', 'acceptEncoding', 'acceptLanguage',
        'jwtCookie', 'method', 'bearerToken',
    );
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $req = new \FastSitePHP\Web\Request();
    return checkObjectMethods($req, $private_methods, $public_methods);
});

// Return the method 'GET', 'POST', etc. This gets called several times.
$app->route('/method', function() { 
    $req = new \FastSitePHP\Web\Request();
    return $req->method();
});

// Testing reading from a JSON Post with contentText()
$app->post('/post-data-1', function() use ($app) {
    // Set response type as json
    $app->header('Content-Type', 'application/json');
    
    // Make sure input is correct
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'json') {
        return array('error' => 'Wrong contentType(): ' . $req->contentType());
    } elseif ($req->contentText() !== '{"site":"FastSitePHP","page":"UnitTest"}') {
        return array('error' => 'Wrong contentText(): ' . $req->contentText());
    }

    // Return text string for JSON output
    return $req->contentText();
});

// Testing reading from a JSON Post with content()
$app->post('/post-data-2', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    $json = $req->content();

    if ($req->contentType() !== 'json') {
        return array('error' => 'Unexpected contentType()');
    } elseif ($req->value($json, 'site') !== 'FastSitePHP') {
        return array('error' => 'Unexpected Input for site');
    } elseif ($req->value($json, 'page') !== 'UnitTest2') {
        return array('error' => 'Unexpected Input for page');
    }

    return $req->content();
});

// Testing reading from a JSON Post - using app->route() rather than app->post()
// Reading input stream with content() and returning either array or object 
// as the response.
$app->route('/post-data-3', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    return $req->content();
});

// Testing reading from a Form Post, checking contentType(), and using PHP Superglobal $_POST
$app->post('/post-data-4', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'form') {
        return array('error' => 'Unexpected contentType()');
    } elseif ($req->value($_POST, 'site') !== $req->form('site')) {
        return array('error' => '$req->form <> $req->value for [site]');
    } elseif ($req->value($_POST, 'page') !== $req->form('page')) {
        return array('error' => '$req->form <> $req->value for [page]');
    } elseif ($req->value($_POST, 'site') !== 'FastSitePHP') {
        return array('error' => 'Unexpected Input for site');
    } elseif ($req->value($_POST, 'page') !== 'UnitTest4') {
        return array('error' => 'Unexpected Input for page');
    }

    return array(
        'site' => $req->form('site'),
        'page' => $req->form('page'),
        'notSet' => $req->form('notSet'),
    );
});

// Testing reading from a Form Post with content()
$app->post('/post-data-5', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'form') {
        return array('error' => 'Unexpected contentType()');
    } elseif ($req->value($_POST, 'site') !== 'FastSitePHP') {
        return array('error' => sprintf('Unexpected Input for site: [%s]', $req->value($_POST, 'site')));
    } elseif ($req->value($_POST, 'page') !== 'UnitTest5') {
        return array('error' => sprintf('Unexpected Input for page: [%s]', $req->value($_POST, 'page')));
    }
    return $req->content();
});

// Invalid Type - Sending JSON but specifying 'application/x-www-form-urlencoded; charset=UTF-8'
// This shows what would happen, this would be an error on the developer developing the site 
// and would need to be fixed by them. Even though it's an error it might not be caught as it
// doesn't actually throw an exception.
$app->post('/post-data-6', function() use ($app) {
    // Response Type
    $app->header('Content-Type', 'application/json');

    // Make sure expected input is correct
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'form') {
        return array('error' => 'Unexpected contentType()');
    } elseif ($req->contentText() !== '{"site":"FastSitePHP","page":"UnitTest6"}') {
        return array('error' => 'Unexpected contentText()');
    }

    // Because the type form was specified but JSON was sent then
    // the JSON is actually parsed as a form variable by the PHP function parse_str()
    // however the data would not be valid.
    $data = $req->content();
    $field_name = '{"site":"FastSitePHP","page":"UnitTest6"}';

    if (!isset($data[$field_name]) || $data[$field_name] !== '') {
        return array('error' => 'Unexpected data parsing by content()');
    }

    // Return result from content() directly as JSON
    return $req->content();
});

// Invalid Type - Sending Form but specifying 'application/json; charset=utf-8'
// Because invalid data is posted and cannot be parsed as JSON this should return null
$app->post('/post-data-7', function() use ($app) {
    // Response Type
    $app->header('Content-Type', 'text/plain');
    
    // Make sure expected input is correct
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'json') {
        return 'Unexpected contentType()';
    } elseif ($req->contentText() !== 'site=FastSitePHP&page=UnitTest7') {
        return 'Unexpected contentText()';
    }

    // Because the type JSON was specified but Form Data was sent then
    // the data is not parsed and the PHP function json_decode() which
    // get called from content() returns null.
    $data = $req->content();
    return ($data === null ? '<null>' : $data);
});

// Receive an XML Post and return a Text Response
$app->post('/post-data-8', function() use ($app) {
    // Response Type
    $app->header('Content-Type', 'text/plain');
    
    // Make sure expected input is correct
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'xml') {
        return 'Unexpected contentType()';
    } elseif ($req->contentText() !== '<test><site>FastSitePHP</site><page>UnitTest8</page></test>') {
        return 'Unexpected contentText()';
    }

    // Return the xml object (this should be plain text for this test)
    return $req->content();
});

// Receive an XML Post, parse with SimpleXML, and return a XML Response
$app->post('/post-data-9', function() use ($app) {
    // Set Response Type
    $app->header('Content-Type', 'application/xml');
    
    // Make sure expected input is correct
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'xml') {
        return '<error>Unexpected contentType()</error>';
    } elseif ($req->contentText() !== '<test><site>FastSitePHP</site><page>UnitTest9</page></test>') {
        return '<error>Unexpected contentText()</error>';
    }

    // Read the XML input using PHP's SimpleXML Library
    $xml = new SimpleXMLElement($req->content());
    
    // Return the xml text
    return $xml->asXml();
});

// Receive plain text and send it back to the client as plain text
$app->post('/post-data-10', function() use ($app) {
    $app->header('Content-Type', 'text/plain');

    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'text') {
        return 'Unexpected contentType()';
    } elseif ($req->contentText() !== 'Test with plain text') {
        return 'Unexpected contentText()';
    }

    return $req->content();
});

// Client makes a request sending null which ends up being a zero-length 
// string when read by 'php//input'
$app->post('/post-data-11', function() use ($app) {
    $app->header('Content-Type', 'text/plain');

    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'text') {
        return 'Unexpected contentType()';
    } elseif ($req->contentText() !== '') {
        return 'Unexpected contentText()';
    }

    return $req->content();
});

// This test is primarily for testing 'Expect: 100-continue' Request Headers. Web browsers
// will not send the header 'Expect: 100-continue' however other libraries such as the .NET Class
// [System.Net.WebRequest] and Unix curl command will send the header. Although not commonly used the
// header has been known to cause errors with certain programs and web servers. It is not handled by 
// FastSitePHP but at a lower-level by the web server. If submitted the web server should correctly
// handle the request header 'Expect' and it should have no impact on FastSitePHP or other frameworks.
// The most recent versions of major web servers are expected to handle this correctly however older
// web servers might not handle it. Three separate tests exist to test this function which makes sure
// that the server in which FastSitePHP is installed on can handle the header. The standard unit testing
// webpage tests this function however it wouldn't send the header so in the folder
// [FastSitePHP\docs\unit-testing] exists C# Source Code for Windows and a Unix Shell Script
// for Mac/Linux/Unix that will test for the header. See documentation in the folder for more.
//
// Example of manually testing from a terminal program in Mac/Linux/Unix
// (NOTE - the url would need to be modified based on the server to test)
//
// Submit a form POST using 'application/x-www-form-urlencoded'
// curl -d "site=FastSitePHP&page=UnitTest12" http://localhost/FastSitePHP/vendor/fastsitephp/tests/test-web-request.php/post-data-12?data=Expect100
// Has Expect:100-continue: false
//
// Submit a form POST using 'multipart/form-data', this causes the 'Expect: 100-continue' header
// curl -F site=FastSitePHP -F page=UnitTest12 http://localhost/FastSitePHP/vendor/fastsitephp/tests/test-web-request.php/post-data-12?data=Expect100
// Has Expect:100-continue: true
//
// To debug from Unix Command Line
// curl -d "site=FastSitePHP&page=UnitTest12" http://localhost/FastSitePHP/vendor/fastsitephp/tests/test-web-request.php/post-data-12?data=headers
// curl -F site=FastSitePHP -F page=UnitTest12 http://localhost/FastSitePHP/vendor/fastsitephp/tests/test-web-request.php/post-data-12?data=headers
//
$app->post('/post-data-12', function() use ($app) {
    // Format an array for easy to read text output
    function formatArray($array) {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = $key . '=' . $value;
        }
        return '['. implode('] [', $result) . ']';
    }

    // Check for optional query string value
    $req = new \FastSitePHP\Web\Request();
    switch ($req->value($_GET, 'data')) {
        // Check for the Request Header 'Expect:100-continue'
        case 'Expect100':
            $result = 'Has Expect:100-continue: ' . ($req->header('Expect') === '100-continue' ? 'true' : 'false');
            break;
        // Return Request Headers
        case 'headers':
            $result = 'Headers: ' . formatArray($req->headers());
            break;
        // Return POST Data
        case 'post':
            $result = 'POST: ' . formatArray($_POST);
            break;
        // Return the Input Type
        case 'input-type':
            $result = 'Input Type: ' . $req->contentType();
            break;
        // Return Input Text
        case 'input':
            $result = 'Input: ' . $req->contentText();
            break;
        // Validate Data from the superglobal $_POST Array and return either text error or Form Data
        default:
            if (!($req->contentType() === 'form' || $req->contentType() === 'form-data')) {
                $result = 'Error - Unexpected contentType(): ' . $req->contentType();
            } elseif ($req->value($_POST, 'site') !== 'FastSitePHP') {
                $result = sprintf('Error - Unexpected Input for site: [%s]', $req->value($_POST, 'site'));
            } elseif ($req->value($_POST, 'page') !== 'UnitTest12') {
                $result = sprintf('Error - Unexpected Input for page: [%s]', $req->value($_POST, 'page'));
            } else {
                $result = sprintf('(%s): ', $req->contentType()) . formatArray($_POST);
            }
    }

    // Return the result as text, a new-line is added at the end of the result so that command line
    // (terminal) output shows each result on one line when using the *nix (Unix/Linux) command curl
    $app->header('Content-Type', 'text/plain');
    return $result . "\n";
});

// Testing reading from a Form Post that contains Quotes in one of the Form Values.
// This Test should only fail if using a version of PHP 5.3 with Magic Quotes turned on.
// For more info on this topic see comments in page [test-app.php] route '/check-server-config'.
$app->post('/post-data-13', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    if ($req->contentType() !== 'form') {
        return array('error' => 'Unexpected contentType()');
    } elseif ($req->value($_POST, 'site') !== 'FastSitePHP') {
        return array('error' => 'Unexpected Input for site');
    } elseif ($req->value($_POST, 'page') !== '\'UnitTest_With_Quotes\'') {
        return array('error' => 'Unexpected Input for page: [' . $req->value($_POST, 'page') . ']');
    }
    return $_POST;
});

// Test for QueryString
$app->get('/query-string', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    return array(
        'param1' => $req->queryString('param1'),
        'param1AsInt' => $req->queryString('param1', 'int?'),
        'missing' => $req->queryString('param2'),
    );
});

// CORS Headers are added with a filter function
$app->route('/cors-origin', function() {
    return 'Cross-Origin Resource Sharing (CORS) Test with origin()';
})->filter($addCorsHeaders);

// Verify then when origin() is called by default on a GET Request
// that it is not set and returns null.
$app->get('/check-default-origin', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();
    if ($req->origin() !== null) {
        return 'Function origin() should have returned null';
    }
    return 'Function origin() returned null';
});

// Testing Request Header HTTP_X_REQUESTED_WITH and function isXhr()
$app->get('/xhr', function() use ($app) { 
    $req = new \FastSitePHP\Web\Request();
    return array(
        'isXhr' => $req->isXhr(),
        'HTTP_X_REQUESTED_WITH' => (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '<Null>'),
        'header' => $req->header('X-Requested-With'),
    );
});

// Testing comparing data from headers() to header()
// This gets tested with both GET and POST
$app->route('/compare-headers-to-header', function() use ($app) {
    try {
        // Return type is a text string
        $app->header('Content-Type', 'text/plain');

        // Get all request headers by calling headers()
        $req = new \FastSitePHP\Web\Request();
        $request_headers = $req->headers();

        // Compare all values found with the header() function
        foreach ($request_headers as $key => $value) {
            $value2 = $req->header($key);
            if ($value !== $value2) {
                return sprintf('Request Header Field [%s] did not match between headers() and header(), headers() value = [%s], header() value = [%s]', $key, $value, $value2);
            }
        }

        // Check for POST which has an additional check
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
    
        // If running on PHP 5.3 using IIS or nginx this confirms that the following two lines work:
        //   if (isset($_SERVER['CONTENT_TYPE']) && !isset($req_headers['Content-Type'])) {
        //   if (isset($_SERVER['CONTENT_LENGTH']) && !isset($req_headers['Content-Length'])) {
        if ($method === 'POST') {
            if (!isset($request_headers['Content-Type'])) {
                return 'The headers() function did not return the [Content-Type] header: ' . json_encode($request_headers);
            } elseif (!isset($request_headers['Content-Length'])) {
                return 'The headers() function did not return the [Content-Length] header';
            } elseif ($request_headers['Content-Type'] !== 'application/x-www-form-urlencoded; charset=UTF-8') {
                return 'The headers() function returned the wrong [Content-Type] header';
            } elseif ($request_headers['Content-Length'] !== '16') {
                return 'Unexpected Post Value from the Client when testing for [Content-Length] header';
            }
        }

        // The actual headers will vary based on the client however 
        // 'User-Agent' should always exist when called from a browser
        // and headers should be case-insensitive when called from header().
        if (!isset($request_headers['User-Agent'])) {
            return 'User-Agent was not found in $request_headers';
        } elseif ($request_headers['User-Agent'] !== $req->header('USER-AGENT')) {
            return 'User-Agent was not found in the header function using [USER-AGENT]';
        }

        // Success
        return 'All Request Header Values Matched between headers() and header(), Request Type = ' . $method;
    } catch (\Exception $e) {
        // Return Error Text
        return $e->getMessage();
    }
});

// Testing the value() function, this is one of the largest test functions as unit testing 
// for this function is handled all in this one route and the client checks for the 
// expected number of successful tests.
$app->get('/value', function() use ($app) {
    
    // Simple Array
    $array = array(
        'string1' => 'abc',
        'string2' => '123',
        'string3' => ' Test with space before and after ',
        'big_int' => PHP_INT_MAX,
        // Brief overview  of how Integer overflow works in PHP. 
        // On a 32-bit builds of PHP the number 2147483647+1 = Float of 2147483648
        // but when CAST as int then -2147483648 (or 9223372036854775807/-9223372036854775808
        // in 64-bit builds of PHP). The minimum number can also be written as ~PHP_INT_MAX
        // or starting with PHP 7 PHP_INT_MIN. [~] is the Bitwise operator 'Not', to understand 
        // how a [Bitwise Not] works see: https://en.wikipedia.org/wiki/Bitwise_operation#NOT
        // For a more detailed overview on binary numbers read comments in the function [Net\IP::cidr()]
        'small_int' => (int)(PHP_INT_MAX + 1),
        'float_value' => (PHP_INT_MAX + 1),
        'bool1' => '1',
        'bool2' => 'true',
        'bool3' => 'on',
        'bool4' => 'yes',
        'bool5' => '0',
        'bool6' => 'false',
        'bool7' => 'no',
        'bool8' => 'off',
        'bool9' => true,
    );
    
    // Simple Object
    $obj = new \stdClass;
    $obj->string1 = 'abc';
    $obj->string2 = '123';
    $obj->string3 = '';
    $obj->string4 = '    ';
    $obj->int1 = 12345;
    $obj->float1 = (float)0;
    $obj->float2 = 123.456;
    $obj->valid_email = 'name@domain.tld';
    $obj->invalid_email = '@domain.tld';
    $obj->valid_http_url = 'http://www.domain.tld/';
    $obj->valid_https_url = 'https://www.domain.tld/';
    // Using the following with PHP filter_var() will return true as a valid
    // URL however with FastSitePHP->value() it will return null.
    // filter_var('mailto:name@domain.tld', FILTER_VALIDATE_URL)
    $obj->invalid_url = 'mailto:name@domain.tld';

    // Array of Arrays
    $products_array = array(
        array(
            'name' => 'Product 1',
        ),
        array(
            'name' => 'Product 2',
        ),
    );

    // Object with Properties that are also Objects
    $products_obj = new \stdClass;
    $products_obj->item1 = new \stdClass;
    $products_obj->item1->name = 'Object Product 1';
    $products_obj->item2 = new \stdClass;
    $products_obj->item2->name = 'Object Product 2';

    // JSON Data, decode a JSON String to a PHP Object
    /*
    {
      "objectType": "document",
      "title": "Unit Test",
      "description": "Test Object for FastSitePHP",
      "author": {
        "firstName": "Conrad",
        "lastName": "Sollitt"
      },
      "yearCreated": 2015,
      "tags": [
        "FastSitePHP",
        "Unit Test",
        "value() Function"
      ]
    }
    */
    $json = json_decode('{"objectType":"document","title":"Unit Test","description":"Test Object for FastSitePHP","author":{"firstName":"Conrad","lastName":"Sollitt"},"yearCreated":2015,"tags":["FastSitePHP","Unit Test","value() Function"]}');

    // Keep count of tests
    $test_count = 0;

    // --------------------------------------
    // Test Order for the $format parameter
    // --------------------------------------
    //  'value?'  and  null
    //  'string'
    //  'string?'
    //  'string with whitespace'
    //  'int'
    //  'int?'
    //  'float'
    //  'float?'
    //  'bool'
    //  'bool?'
    //  'checkbox'
    //  'email?'
    //  'url?'
    //  After, testing is done on an Array of Format Parameters using Objects and Arrays
    $tests = array(
        // ------------------------------------------------------------------------------
        // Testing the default format of 'value?' with a simple array and object.
        // When set as null this unit test function will not specify the parameter.
        // ------------------------------------------------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => null,
            'expected' => 'abc',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'value?',
            'max_length' => 2,
            'expected' => 'ab',
            'return_type' => 'string',
        ),        
        array(
            'data' => $array,
            'key' => 'string2',
            'format' => 'value?',
            'expected' => '123',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'missing',
            'format' => 'value?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $array,
            'key' => 'big_int',
            'format' => 'value?',
            'expected' => PHP_INT_MAX,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'small_int',
            'format' => 'value?',
            'expected' => ~PHP_INT_MAX, // Bitwise Not, see comments above where [small_int] is defined
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'float_value',
            'format' => 'value?',
            'expected' => (float)(PHP_INT_MAX + 1),
            'return_type' => 'double',
        ),
        array(
            'data' => $array,
            'key' => 'bool2',
            'format' => 'value?',
            'expected' => 'true',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'bool9',
            'format' => 'value?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $obj,
            'key' => 'int1',
            'format' => null,
            'expected' => 12345,
            'return_type' => 'integer',
        ),
        array(
            'data' => $obj,
            'key' => 'float1',
            'format' => 'value?',
            'expected' => 0.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'value?',
            'expected' => 123.456,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => null,
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------
        // Testing of format 'string'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'string',
            'expected' => 'abc',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'string3',
            'format' => 'string',
            'expected' => 'Test with space before and after',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'int1',
            'format' => 'string',
            'expected' => '12345',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'string',
            'expected' => '123.456',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'string',
            'expected' => '',
            'return_type' => 'string',
        ),
        // -------------------------------------
        // Testing of format 'string?'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'string?',
            'expected' => 'abc',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'string3',
            'format' => 'string?',
            'expected' => 'Test with space before and after',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'int1',
            'format' => 'string?',
            'expected' => '12345',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'string3',
            'format' => 'string?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'string4',
            'format' => 'string?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'string?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------------
        // Testing of format 'string with whitespace'
        // -------------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'string with whitespace',
            'expected' => 'abc',
            'return_type' => 'string',
        ),
        array(
            'data' => $array,
            'key' => 'string3',
            'format' => 'string with whitespace',
            'expected' => ' Test with space before and after ',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'string4',
            'format' => 'string with whitespace',
            'expected' => '    ',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'string with whitespace',
            'expected' => '',
            'return_type' => 'string',
        ),
        // -------------------------------------
        // Testing of format 'int'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'string2',
            'format' => 'int',
            'expected' => 123,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'small_int',
            'format' => 'int',
            'expected' => ~PHP_INT_MAX, // Bitwise Not, see comments above where [small_int] is defined
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'big_int',
            'format' => 'int',
            'expected' => PHP_INT_MAX,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'big_float',
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        // -------------------------------------
        // Testing of format 'int?'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'int?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $array,
            'key' => 'string2',
            'format' => 'int?',
            'expected' => 123,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'small_int',
            'format' => 'int?',
            'expected' => ~PHP_INT_MAX, // Bitwise Not, see comments above where [small_int] is defined
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'big_int',
            'format' => 'int?',
            'expected' => PHP_INT_MAX,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'big_float',
            'format' => 'int?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'int?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'int?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------
        // Testing of format 'float'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'float_value',
            'format' => 'float',
            'expected' => (float)(PHP_INT_MAX + 1),
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'int1',
            'format' => 'float',
            'expected' => 12345.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'float1',
            'format' => 'float',
            'expected' => 0.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'float',
            'expected' => 123.456,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'float',
            'expected' => 0.0,
            'return_type' => 'double',
        ),
        // -------------------------------------
        // Testing of format 'float?'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'float_value',
            'format' => 'float?',
            'expected' => (float)(PHP_INT_MAX + 1),
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'int1',
            'format' => 'float?',
            'expected' => 12345.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'float1',
            'format' => 'float?',
            'expected' => 0.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'float2',
            'format' => 'float?',
            'expected' => 123.456,
            'return_type' => 'double',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'float?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------
        // Testing of format 'bool'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'bool1',
            'format' => 'bool',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool2',
            'format' => 'bool',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool3',
            'format' => 'bool',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool4',
            'format' => 'bool',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool5',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool6',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool7',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool8',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool9',
            'format' => 'bool',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'missing',
            'format' => 'bool',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        // -------------------------------------
        // Testing of format 'bool?'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'bool1',
            'format' => 'bool?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool2',
            'format' => 'bool?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool3',
            'format' => 'bool?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool4',
            'format' => 'bool?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool5',
            'format' => 'bool?',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool6',
            'format' => 'bool?',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool7',
            'format' => 'bool?',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool8',
            'format' => 'bool?',
            'expected' => false,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'bool9',
            'format' => 'bool?',
            'expected' => true,
            'return_type' => 'boolean',
        ),
        array(
            'data' => $array,
            'key' => 'string1',
            'format' => 'bool?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $array,
            'key' => 'missing',
            'format' => 'bool?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------
        // Testing of format 'checkbox'
        // -------------------------------------
        array(
            'data' => $array,
            'key' => 'bool1',
            'format' => 'checkbox',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'bool3',
            'format' => 'checkbox',
            'expected' => 1,
            'return_type' => 'integer',
        ),
        array(
            'data' => $array,
            'key' => 'missing',
            'format' => 'checkbox',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        // -------------------------------------
        // Testing of format 'email?'
        // -------------------------------------
        array(
            'data' => $obj,
            'key' => 'valid_email',
            'format' => 'email?',
            'expected' => 'name@domain.tld',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'invalid_email',
            'format' => 'email?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'email?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // -------------------------------------
        // Testing of format 'url?'
        // -------------------------------------
        array(
            'data' => $obj,
            'key' => 'valid_http_url',
            'format' => 'url?',
            'expected' => 'http://www.domain.tld/',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'valid_https_url',
            'format' => 'url?',
            'expected' => 'https://www.domain.tld/',
            'return_type' => 'string',
        ),
        array(
            'data' => $obj,
            'key' => 'invalid_url',
            'format' => 'url?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $obj,
            'key' => 'missing',
            'format' => 'url?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // ----------------------------------------------------------------
        // Testing an Array of Format Parameters using Objects and Arrays
        // ----------------------------------------------------------------
        array(
            'data' => $products_array,
            'key' => array(0, 'name'),
            'format' => null,
            'expected' => 'Product 1',
            'return_type' => 'string',
        ),
        array(
            'data' => $products_array,
            'key' => array(1, 'name'),
            'format' => null,
            'expected' => 'Product 2',
            'return_type' => 'string',
        ),
        array(
            'data' => $products_array,
            'key' => array(2, 'name'),
            'format' => 'string',
            'expected' => '',
            'return_type' => 'string',
        ),
        array(
            'data' => $products_obj,
            'key' => array('item1', 'name'),
            'format' => null,
            'expected' => 'Object Product 1',
            'return_type' => 'string',
        ),
        array(
            'data' => $products_obj,
            'key' => array('item2', 'name'),
            'format' => null,
            'expected' => 'Object Product 2',
            'return_type' => 'string',
        ),
        array(
            'data' => $products_obj,
            'key' => array('item2', 'missing'),
            'format' => 'value?',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'data' => $products_obj,
            'key' => array('item2', 'missing'),
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $products_obj,
            'key' => array('item2', 'missing'),
            'format' => 'int',
            'expected' => 0,
            'return_type' => 'integer',
        ),
        array(
            'data' => $json,
            'key' => 'objectType',
            'format' => null,
            'expected' => 'document',
            'return_type' => 'string',
        ),
        array(
            'data' => $json,
            'key' => 'missing',
            'format' => 'float',
            'expected' => 0.0,
            'return_type' => 'double',
        ),
        array(
            'data' => $json,
            'key' => array('author', 'firstName'),
            'format' => null,
            'expected' => 'Conrad',
            'return_type' => 'string',
        ),
        array(
            'data' => $json,
            'key' => array('author', 'lastName'),
            'format' => null,
            'expected' => 'Sollitt',
            'return_type' => 'string',
        ),
        array(
            'data' => $json,
            'key' => 'yearCreated',
            'format' => null,
            'expected' => 2015,
            'return_type' => 'integer',
        ),
        array(
            'data' => $json,
            'key' => array('tags'),
            'format' => null,
            'expected' => array('FastSitePHP', 'Unit Test', 'value() Function'),
            'return_type' => 'array',
        ),
        array(
            'data' => $json,
            'key' => array('tags', 0),
            'format' => null,
            'expected' => 'FastSitePHP',
            'return_type' => 'string',
        ),
        array(
            'data' => $json->tags,
            'key' => 1,
            'format' => null,
            'expected' => 'Unit Test',
            'return_type' => 'string',
        ),
        array(
            'data' => $json->tags,
            'key' => 2,
            'format' => null,
            'expected' => 'value() Function',
            'return_type' => 'string',
        ),
    );
    
    // Run Tests calling the value() function and comparing the result to the 
    // expected value. Each of these tests should return the expected value
    // and no exceptions should be thrown.
    $req = new \FastSitePHP\Web\Request();
    foreach ($tests as $test) {
        // Call value() one of two ways leaving the $format parameter blank
        // or specifying the $format option.
        if ($test['format'] === null) {
            $value = $req->value($test['data'], $test['key']);
        } elseif (isset($test['max_length'])) {
            $value = $req->value($test['data'], $test['key'], $test['format'], $test['max_length']);
        } else {
            $value = $req->value($test['data'], $test['key'], $test['format']);
        }

        // Keep count of each test
        $test_count++;

        // First check the return type then the actual value if it matches.
        // If any test fails the function will end showing details of what test failed.
        if (gettype($value) !== $test['return_type']) {
            echo sprintf('Error with Test %d, Key [%s], Type Mismatch, Expected Type: [%s], Return Type: [%s]', $test_count, (is_array($test['key']) ? implode(', ', $test['key']) : $test['key']), $test['return_type'], gettype($value));
            echo '<br><br>';
            var_dump($test);
            echo '<br><br>';
            var_dump($value);
            exit();
        } elseif ($test['return_type'] === 'array' && count(array_diff($value, $test['expected'])) !== 0) {
            echo sprintf('Error with Test %d, Incorrect Returned Array did not match expected array, array_diff() count: %d', $test_count, count(array_diff($value, $test['expected'])));
            echo '<br><br>';
            var_dump($test);
            echo '<br><br>';
            var_dump($value);
            exit();
        } elseif ($value !== $test['expected']) {
            echo sprintf('Error with Test %d, Incorrect Return Value for Key [%s] with format [%s]', $test_count, (is_array($test['key']) ? implode(', ', $test['key']) : $test['key']), gettype($test['format']));
            echo '<br><br>';
            var_dump($test);
            echo '<br><br>';
            var_dump($value);
            exit();
        }
    }

    // ---------------------------------------------
    // Test for exceptions, each of these tests
    // is expected to thrown an exception.
    // ---------------------------------------------
    $test_error_count = 0;

    $error_tests = array(
        array(
            'data' => $obj,
            'key' => 1.1,
            'format' => null,
            'expectedError' => 'The function [FastSitePHP\Web\Request->value()] was called with an invalid parameter. The parameter $key must be defined as either an [array], [string], or [int]; it was instead defined as a [double] data type.'
        ),
        array(
            'data' => $obj,
            'key' => 'error',
            'format' => array(),
            'expectedError' => 'The function [FastSitePHP\Web\Request->value()] was called with an invalid parameter. The parameter $format must be either a valid string option or null; it was instead defined as a [array] data type.'
        ),
        array(
            'data' => $obj,
            'key' => 'error',
            'format' => 'error',
            'expectedError' => 'The function [FastSitePHP\Web\Request->value()] was called with an invalid parameter. The parameter $format must be either null or one of the valid options: [value?|string|string?|string with whitespace|int|int?|float|float?|bool|bool?|checkbox|email?|url?]; it was instead defined as [error].'
        ),
        // This test is calling a valid property but specifying that an array
        // be returned as a string which is unexpected because the value() function
        // is expecting the property/item to be a basic type when casting. A similar 
        // test is called above near the end of the [$tests] array and returning the 
        // array because the value is being returned 'as-is'.
        array(
            'data' => $json,
            'key' => array('tags'),
            'format' => 'string',
            'expectedError' => 'trim() expects parameter 1 to be string, array given'
        ),
    );

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;

            if ($test['format'] === null) {
                $value = $req->value($test['data'], $test['key']);
            } else {
                $value = $req->value($test['data'], $test['key'], $test['format']);
            }

            // If the test doesn't error that there is a problem
            echo sprintf('Error with Exception Test %d, Key [%s], The test did not fail but should have thrown an exception.', $test_error_count, (is_array($test['key']) ? implode(', ', $test['key']) : $test['key']));
            echo '<br><br>';
            var_dump($test);
            echo '<br><br>';
            var_dump($value);
            exit();
        } catch (\Exception $e) {
            if ($e->getMessage() !== $test['expectedError']) {
                echo sprintf('Error with Exception Test %d, Key [%s], The test correctly threw an exception but the message did not match the expected error message.', $test_error_count, (is_array($test['key']) ? implode(', ', $test['key']) : $test['key']));
                echo '<br><br>';
                var_dump($test);
                echo '<br><br>';
                var_dump($e);
                exit();
            }
        }
    }

    // All Tests Passed, if the code reaches here
    return sprintf('Success for value() function, Completed %d Unit Tests and %d Exception Tests', $test_count, $test_error_count);
});

// Values used in this function are related to the comments defined in the value() function
$app->get('/value2', function() use ($app) {
    // In PHP POST values can be manually set on the server
    $_POST['input1'] = 'test';
    $_POST['input2'] = '123.456';
    $_POST['checkbox1'] = 'on';

    // Create an JSON Object
    $json = json_decode('{"app":"FastSitePHP","string":"abc","number":"123","items":[{"name":"item1"},{"name":"item2"}]}');
    
    // Return a JSON object which gets created from an array
    $result = array();
    $req = new \FastSitePHP\Web\Request();

    $result['input1'] = $req->value($_POST, 'input1');
    $result['input2'] = $req->value($_POST, 'input2', 'float');
    $result['missing'] = $req->value($_POST, 'missing', 'string');
    $result['checkbox1'] = $req->value($_POST, 'checkbox1', 'checkbox');
    $result['missing-checkbox'] = $req->value($_POST, 'checkbox2', 'checkbox');
    $result['checkbox1-bool'] = $req->value($_POST, 'checkbox1', 'bool');

    $result['app'] = $req->value($json, 'app');
    $result['string-string?'] = $req->value($json, 'string', 'string?');
    $result['string-int'] = $req->value($json, 'string', 'int');
    $result['string-int?'] = $req->value($json, 'string', 'int?');
    $result['number-int'] = $req->value($json, 'number', 'int');
    $result['items-0-name'] = $req->value($json, array('items', 0, 'name'));
    $result['items-1-name'] = $req->value($json, array('items', 1, 'name'));
    $result['items-2-name'] = $req->value($json, array('items', 2, 'name'));
    
    return $result;
});

// Check the Request 'User-Agent' Header
// In JavaScript this is the value from [navigator.userAgent]
$app->get('/user-agent', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    $req = new \FastSitePHP\Web\Request();
    if ($req->userAgent() !== $req->header('User-Agent')) {
	    return 'Error, mismatch with header() function';
    }
    return $req->userAgent();
});

// Check the Request 'Referer' Header which for FastSitePHP
// uses the correct English Spelling of referrer().
// In JavaScript this is the value from [document.referrer]
$app->get('/referrer', function() use ($app) {
    $app->header('Content-Type', 'text/plain');
    $req = new \FastSitePHP\Web\Request();
    if ($req->referrer() !== $req->header('Referer')) {
	    return 'Error, mismatch with header() function';
    }
    return $req->referrer();
});

$app->get('/missing-headers', function() use ($app) {
    // Remove Specific Headers from the superglobal $_SERVER array
    unset($_SERVER['HTTP_USER_AGENT']);
    unset($_SERVER['HTTP_REFERER']);

    // Set the return type as text
    $app->header('Content-Type', 'text/plain');

    // Call functions and return result
    $req = new \FastSitePHP\Web\Request();
    $result = sprintf('[userAgent():%s]', ($req->userAgent() === null ? 'null' : $req->userAgent()));
    $result .= sprintf('[referrer():%s]', ($req->referrer() === null ? 'null' : $req->referrer()));
    return $result;
});

// Test the Request Header 'Accept' using the function [accept()]
$app->get('/accept', function() use ($app) {
    // Manually set the Server Variable Header to Test
    $_SERVER['HTTP_ACCEPT'] = 'text/html, application/xhtml+xml, application/xml;q=0.9,image/webp,*/*;q=0.8';
    $req = new \FastSitePHP\Web\Request();

    // Run Tests
    $results = array(
        'header' => $_SERVER['HTTP_ACCEPT'],
        'value' => $req->accept(),
        'search_true' => $req->accept('image/webp'),
        'search_false' => $req->accept('test/test'),
    );

    // Clear and Test
    unset($_SERVER['HTTP_ACCEPT']);
    $results['empty_value'] = $req->accept();
    $results['empty_search'] = $req->accept('image/webp');

    // Return JSON Result    
    return $results;
});

// Test the Request Header 'Accept-Charset' using the function [acceptCharset()]
$app->get('/accept-charset', function() use ($app) {
    // Manually set the Server Variable Header to Test
    $_SERVER['HTTP_ACCEPT_CHARSET'] = 'ISO-8859-1,utf-8;q=0.7,*;q=0.7';
    $req = new \FastSitePHP\Web\Request();

    // Run Tests
    $results = array(
        'header' => $_SERVER['HTTP_ACCEPT_CHARSET'],
        'value' => $req->acceptCharset(),
        'search_true' => $req->acceptCharset('ISO-8859-1'),
        'search_false' => $req->acceptCharset('Shift_JIS'),
    );

    // Clear and Test
    unset($_SERVER['HTTP_ACCEPT_CHARSET']);
    $results['empty_value'] = $req->acceptCharset();
    $results['empty_search'] = $req->acceptCharset('ISO-8859-1');

    // Return JSON Result    
    return $results;
});

// Test the Request Header 'Accept-Encoding' using the function [acceptEncoding()]
$app->get('/accept-encoding', function() use ($app) {
    // Manually set the Server Variable Header to Test
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, sdch';
    $req = new \FastSitePHP\Web\Request();

    // Run Tests
    $results = array(
        'header' => $_SERVER['HTTP_ACCEPT_ENCODING'],
        'value' => $req->acceptEncoding(),
        'search_true' => $req->acceptEncoding('deflate'),
        'search_false' => $req->acceptEncoding('test'),
    );

    // Clear and Test
    unset($_SERVER['HTTP_ACCEPT_ENCODING']);
    $results['empty_value'] = $req->acceptEncoding();
    $results['empty_search'] = $req->acceptEncoding('deflate');

    // Return JSON Result    
    return $results;
});

// Test the Request Header 'Accept-Language' using the function [acceptLanguage()]
$app->get('/accept-language', function() use ($app) {
    // Manually set the Server Variable Header to Test
    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4';
    $req = new \FastSitePHP\Web\Request();

    // Run Tests
    $results = array(
        'header' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        'value' => $req->acceptLanguage(),
        'search_true' => $req->acceptLanguage('en-US'),
        'search_false' => $req->acceptLanguage('de'),
    );

    // Clear and Test
    unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $results['empty_value'] = $req->acceptLanguage();
    $results['empty_search'] = $req->acceptLanguage('en-US');

    // Return JSON Result
    return $results;
});

// Unit Test that Client IP returns a valid IP Address. [clientIp] also 
// has options for reading proxy values which are tested from the file 
// [test-net-common.php]. The options are not tested here as they 
// require the object [FastSitePHP\Net\IP]. Testing the method without 
// loading the [Net\IP] file verifies that it will work for sites that 
// only include core framework files and don't the entire framework.
$app->get('/client-ip', function() use ($app) {
    // Create Request Object
    $req = new \FastSitePHP\Web\Request();

    // Build the Response as a text string of several tests
    $response = sprintf('[type:%s]', gettype($req->clientIp()));

    // Check that the Web Server can return a valid IP
    $response .= sprintf('[is_ip:%s]', (filter_var($req->clientIp(), FILTER_VALIDATE_IP) === false ? 'false' : 'true'));
    
    // First the existing Server Variable and test again for null
    if (isset($_SERVER['REMOTE_ADDR'])) {
        unset($_SERVER['REMOTE_ADDR']);
    }
    $response .= sprintf('[null_check:%s]', ($req->clientIp() === null ? 'true' : 'false'));
    
    // Return Text Response
    $app->header('Content-Type', 'text/plain');
    return $response;
});

// Unit Test that the Server IP is an actual IP but for security 
// don't provide actual IP Address Info to to the client page.
$app->get('/server-ip', function() use ($app) {
    // Create Request Object
    $req = new \FastSitePHP\Web\Request();

    // Build the Response as a text string of several tests
    $response = sprintf('[type:%s]', gettype($req->serverIp()));

    // Check that the Web Server can return a valid IP
    $response .= sprintf('[is_ip:%s]', (filter_var($req->serverIp(), FILTER_VALIDATE_IP) === false ? 'false' : 'true'));
    
    // Test different server variables used in the function.
    // First clear any existing values.
    if (isset($_SERVER['SERVER_ADDR'])) {
        unset($_SERVER['SERVER_ADDR']);
    }
    if (isset($_SERVER['LOCAL_ADDR'])) {
        unset($_SERVER['LOCAL_ADDR']);
    }

    // IIS Header Value
    $_SERVER['LOCAL_ADDR'] = '10.10.120.56';
    $response .= sprintf('[LOCAL_ADDR:%s]', ($_SERVER['LOCAL_ADDR'] === $req->serverIp() ? 'true' : 'false')); 

    // Apache Header Value
    // NOTE - the Apache Header is checked first in the code so the 
    // previous IIS value doesn't need to be cleared
    $_SERVER['SERVER_ADDR'] = '10.10.120.12';
    $response .= sprintf('[SERVER_ADDR:%s]', ($_SERVER['SERVER_ADDR'] === $req->serverIp() ? 'true' : 'false')); 

    // Return Text Response
    $app->header('Content-Type', 'text/plain');
    return $response;
});

// Test the isLocal() function. This unit test overwrites Server Variables
// to test different environment options.
$app->get('/is-local', function() use ($app) {
    $req = new \FastSitePHP\Web\Request();

    //localhost IPv4
    $_SERVER['SERVER_ADDR'] = '127.0.0.1';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $response = array();
    $response['test_ipv4'] = $req->isLocal();

    // localhost IPv6
    $_SERVER['SERVER_ADDR'] = '::1';
    $_SERVER['REMOTE_ADDR'] = '::1';
    $response['test_ipv6'] = $req->isLocal(); 

    // localhost mixed IPv4 and IPv6
    $_SERVER['SERVER_ADDR'] = '127.0.0.1';
    $_SERVER['REMOTE_ADDR'] = '::1';
    $response['test_mixed_local'] = $req->isLocal(); 

    // Test Server Not Local
    $_SERVER['SERVER_ADDR'] = '54.231.17.108';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $response['test_server'] = $req->isLocal();

    // Test Client Not Local
    $_SERVER['SERVER_ADDR'] = '127.0.0.1';
    $_SERVER['REMOTE_ADDR'] = '54.231.17.108';
    $response['test_client'] = $req->isLocal();

    // Test Both Not Local
    $_SERVER['SERVER_ADDR'] = '54.231.17.108';
    $_SERVER['REMOTE_ADDR'] = '54.231.17.108';
    $response['test_no_local'] = $req->isLocal();

    // Return JSON Response
    return $response;
});

// Check Properties of protocol(), host(), port() in relation to
// rootDir() for the current server. The values are not sent to the client
// as they will change based on where and how the site is hosted but
// rather they are tested to work as expected with the current site.
$app->get('/compare-rootdir-protocol-host-port', function() use ($app) {
    // Get Property Values
    $req = new \FastSitePHP\Web\Request();
    $root_dir = $app->rootDir();
    $protocol = $req->protocol();
    $host = $req->host();
    $port = $req->port();

    // Check that Port is an INT type
    if (gettype($port) !== 'integer') {
        return 'port() has the wrong type: ' . gettype($port);
    }

    // If not using standard http/https ports then
    // port number should also exist in the host
    if ($port !== 80 && $port !== 443) {
        if (stripos($host, (string)$port) === false) {
            return 'Port should be in the host value';
        }
    }

    // Site root dir and protocol type must match
    if (stripos($root_dir, $protocol . '://') !== 0) {
        return 'Protocal mismatch with rootDir() and protocol()';
    }

    // Site root dir and protocol/host must match
    if (stripos($root_dir, $protocol . '://' . $host) !== 0) {
        return 'Protocal mismatch with rootDir(), protocol(), and host()';
    }

    // Success
    return 'Test is valid when comparing protocol(), host(), and port() with rootDir()';
});


// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
