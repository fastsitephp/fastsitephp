<?php
// ===========================================================
// Unit Testing Page
// *) This file uses only core Framework files
//	  and the Web\Response Object.
// ===========================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Web/Response.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
    require '../src/Web/Response.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Create Etag Functions
// -----------------------------------------------------------

$createEtag = function($content) {
    return md5($content);
};

$returnInt = function($content) {
    return 123;
};


// -----------------------------------------------------------
// Application Events
// -----------------------------------------------------------

// PHP Unit Testing for specific routes, this gets called after
// the response is sent to the client and for specific routes
// will send additional data to the client which is expected in
// the client test page.
$app->after(function($content) use ($app) {
    // Compare Sent Response Header Values to the Expected Values
    function compareHeaders(array $expected) {
        // Get the headers sent to the client and calculate array sizes
        $headers = headers_list();
        $count = count($headers);
        $expected_count = count($expected);

        // Check each expected header
        for ($n = 0; $n < $expected_count; $n++) {
            // Default to not being found
            $was_found = false;

            // If found in the headers sent to the client set the 
            // flag variable to true
            for ($x = 0; $x < $count; $x++) {
                if (is_array($expected[$n])) {
                    // Match to one of the strings in the array
                    $h = count($expected[$n]);
                    for ($j = 0; $j < $h; $j++) {
                        if ($headers[$x] === $expected[$n][$j]) {
                            $was_found = true;
                            break;
                        }
                    }
                    if ($was_found) {
                        break;
                    }       
                } else {
                    // Compare as string, must match exactly
                    if ($headers[$x] === $expected[$n]) {
                        $was_found = true;
                        break;
                    }
                }
            }
            
            // If not found then send an error message to the client
            if (!$was_found) {
                echo sprintf('[Cookie (%d) was not found]: (%s)', $n, $expected[$n]);
                echo '<br><br><strong>Headers: </strong>';
                var_dump($headers);
                echo '<br><br><strong>Expected: </strong>';
                var_dump($expected);
                return;
            }
        }

        // Success, output the number of matched headers
        echo sprintf('[%d Headers were found]', $expected_count);
    }

    // Handle URL's that send cookies to the client because the XMLHttpRequest cannot verify
    // the [Set-Cookie] header except when using certain conditions so this it the easiest 
    // way to verify that the cookies are sent correctly. To see how unit-tests for setcookie
    // from the PHP Source Code, refer to:
    //   https://github.com/php/php-src/blob/master/ext/standard/tests/network/setcookie.phpt
    // The source for setcookie() is the function php_setcookie() from the following:
    //   https://github.com/php/php-src/blob/master/ext/standard/head.c
    switch ($app->requestedPath()) {
        case '/cookie-1':
            // Exit in event of an error
            if (!isset($app->time)) {
                return;
            }
            // PHP 5.5 added [Max-Age]
            // PHP 7 changes case from 'httponly' to 'HttpOnly'
            if (version_compare(PHP_VERSION, '5.5.0', '<')) {
                compareHeaders(array(
                    'Set-Cookie: unit-test-route=cookie-1; expires=' . date('D, d-M-Y H:i:s', $app->time - 1) . ' GMT; path=/path; domain=domain.tld; secure; httponly',
                    'Set-Cookie: unit-test-data=abc123',
                ));
            } elseif (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                compareHeaders(array(
                    array(
                        // Some versions of PHP 7 send 'Max-Age=0' and some send 'Max-Age=-1'
                        'Set-Cookie: unit-test-route=cookie-1; expires=' . date('D, d-M-Y H:i:s', $app->time - 1) . ' GMT; Max-Age=0; path=/path; domain=domain.tld; secure; HttpOnly',
                        'Set-Cookie: unit-test-route=cookie-1; expires=' . date('D, d-M-Y H:i:s', $app->time - 1) . ' GMT; Max-Age=-1; path=/path; domain=domain.tld; secure; HttpOnly',
                    ),
                    'Set-Cookie: unit-test-data=abc123',
                ));
            } else {
                compareHeaders(array(
                    'Set-Cookie: unit-test-route=cookie-1; expires=' . date('D, d-M-Y H:i:s', $app->time - 1) . ' GMT; Max-Age=-1; path=/path; domain=domain.tld; secure; httponly',
                    'Set-Cookie: unit-test-data=abc123',
                ));
            }
            break;
        case '/cookie-2':
            if (version_compare(PHP_VERSION, '5.5.0', '<')) {
                compareHeaders(array(
                    'Set-Cookie: unit-test-route=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=/path; domain=domain.tld; secure; httponly',
                    'Set-Cookie: unit-test-data=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT',
                ));
            } elseif (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                compareHeaders(array(
                    'Set-Cookie: unit-test-route=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/path; domain=domain.tld; secure; HttpOnly',
                    'Set-Cookie: unit-test-data=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
                ));
            } else {
                compareHeaders(array(
                    'Set-Cookie: unit-test-route=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0; path=/path; domain=domain.tld; secure; httponly',
                    'Set-Cookie: unit-test-data=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
                ));
            }
            break;
    }
});

// -----------------------------------------------------------------
// Uncomment and modify the function below if needed for debugging
// -----------------------------------------------------------------

/*
// This function gets called after the response is sent 
// and logs info to a text file
$app->after(function() use ($app) {
    // Exit if not the URL that is in question
    $url = $app->requestedPath();
    if ($url !== '/cache-headers-13') {
        return;
    }

    // Start a new section
    $text = "\r\n";
    $text .= '====================================================';
    $text .= "\r\n";
    $text .= 'requestedPath(): ' . $url;
    $text .= "\r\n";
    $text .= 'statusCode(): ' . $app->statusCode();
    $text .= "\r\n";

    // Add response headers that were sent to the browser
    $headers = $app->headers('response');
    foreach ($headers as $name => $value) {
        $text .= "$name: $value\r\n";
    }
    
    // Append to a text file in the data folder
    $file = '../app_data/debug-log.txt';
    file_put_contents($file, $text . "\r\n", FILE_APPEND);
});
*/

// -----------------------------------------------------------
// Create Classes used for Testing
// -----------------------------------------------------------

// When converted to JSON using json_encode() static 
// properies are expected to not be included
class CustomClass
{
    public $Name = 'FastSitePHP_Response';
    public $CreatedFrom = 'CustomClass';
    public $IntValue = 123;
    public $BoolValue = true;
    public static $StaticInt = 123;
    public static $StaticBool = true;
}

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Check how the Response Object is defined
$app->get('/check-response-class', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content(array(
            'get_class' => get_class($res),
            'get_parent_class' => get_parent_class($res),
        ));
});

// Check Default Object Properties
$app->get('/check-response-properties', function() {
    // Define arrays of properties by type
    $null_properties = array();
    $true_properties = array();
    $false_properties = array();
    $string_properties = array();
    $array_properties = array();
    $private_properties = array(
        'status_code', 'header_fields', 'response_cookies', 'etag_type', 
        'jsonp_query_string', 'response_file', 'response_content',
        'json_options',
    );
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $res = new \FastSitePHP\Web\Response();
    $result = checkObjectProperties($res, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
    
	// Return the result as a plain text response
    return $res
        ->contentType('text')
        ->content($result);
});

// Check Class Functions, this is similar to the above function
// but instead of checking properties it checks the functions.
$app->get('/check-response-methods', function() {
    // Define arrays of function names by type
    $private_methods = array(
        'dateHeader'
    );
    $public_methods = array(
        'header', 'headers', 'statusCode', 'contentType', 'jsonpQueryString',
        'content', 'etag', 'lastModified', 'cacheControl', 'expires',
        'vary', 'noCache', 'cors', 'cookie', 'clearCookie', 'signedCookie',
        'encryptedCookie', 'cookies', 'fileTypeToMimeType', 'file', 
        'redirect', 'reset', 'send', 'json', '__construct', 'jsonOptions',
        'jwtCookie',
    ); 
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $res = new \FastSitePHP\Web\Response();
    $result = checkObjectMethods($res, $private_methods, $public_methods);

	// Return the result as a plain text response
    return $res
        ->contentType('text')
        ->content($result);
});

// Testing the content() function and sent an HTML string
$app->get('/content-html', function() {
    // Create the Response Object
    $res = new \FastSitePHP\Web\Response();

    // Make sure the default content() is null
    if ($res->content() !== null) {
        throw new \Exception('Unexpected value from $res->content(), should be null by default');
    }

    // Make sure that content() is chainable and if called 
    // without a parameter returns the content value
    $value = '<h1>Content() Test</h1>';
    if ($value !== $res->content($value)->content()) {
        throw new \Exception('Unexpected value from $res->content(), should have returned: ' . $value);
    }

    // Success
    return $res;
});

// Return HTML when setting contentType() to 'html'
$app->get('/content-type-html', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('html')
        ->content('<h1>HTML Test using Response contentType()</h1>');
});

// Return HTML using the charset 'ISO-8859-1'
$app->get('/content-type-html-charset', function() {
    // Set and get the 'Content-Type', this makes
    // sure that when setting the 'Content-Type'
    // The method is chainable and returns the 
    // $app object.
    $res = new \FastSitePHP\Web\Response();
    $content_type = $res
        ->contentType('html', 'ISO-8859-1')
        ->contentType();

    // Validate
    if ($content_type !== 'text/html; charset=ISO-8859-1') {
        return 'Unexpected Result: ' . $content_type;
    }

    // Set Content and Return Response Object
    return $res->content('<h1>HTML Test using Response contentType() with Charset</h1>');
});

// Return a JSON Response that sets content() using a JSON String
$app->get('/content-json-string', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content(json_encode(array(
            'Name' => 'FastSitePHP_Response',
            'CreatedFrom' => 'String',
        )));
});

// Return a JSON Response that sets content() using a basic PHP Array
$app->get('/content-json-array', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content(array(
            'Name' => 'FastSitePHP_Response',
            'CreatedFrom' => 'Array',
        ));
});

// Return a JSON Response that sets content() using a basic PHP stdClass Object
$app->get('/content-json-object', function() {
    // Create a Basic Object
    $object = new \stdClass;
    $object->Name = 'FastSitePHP_Response';
    $object->CreatedFrom = 'stdClass';

    // Create and Return Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content($object);
});

// Return a JSON Response that sets content() using a User Defined Class (above in this page)
$app->get('/content-json-custom', function() {
    // Create the Object
    $object = new CustomClass();

    // Create and Return Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content($object);
});

// Test [json()] Function with Array
$app->get('/content-json-func-with-array', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res->json(array(
        'test' => 123
    ));
});

// Test [json()] Function with User-Defined Class
$app->get('/content-json-func-with-obj', function() {
    $res = new \FastSitePHP\Web\Response();
    $object = new CustomClass();
    $object->Name = 'Response_JSON';
    return $res->json($object);
});

// Test [json()] Function with [\stdClass]
$app->get('/content-json-func-with-stdclass', function() {
    $object = new \stdClass;
    $object->Name = 'JSON_Response';
    $object->CreatedFrom = 'stdClass';

    $res = new \FastSitePHP\Web\Response();
    return $res->json($object);
});

// Test [json()] Function Error
$app->get('/content-json-func-error', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res->json('{}');
});

// Return Plain Text using Charset UTF-8
$app->get('/text-charset', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('text', 'UTF-8')
        ->content('Plain Text Response Using UTF-8 Encoding');
});

// Return JavaScript Content
$app->get('/javascript1', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('javascript')
        ->content('alert(\'JavaScript_Response_1\');');
});

// Return JavaScript Content with UTF-8 specified as the charset
$app->get('/javascript2', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('javascript', 'UTF-8')
        ->content('alert(\'JavaScript_Response_2\');');
});

// Return CSS Content
$app->get('/css1', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('css')
        ->content('div { border:2px solid red; }');
});

// Return CSS Content with UTF-8 specified as the charset
$app->get('/css2', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('css', 'UTF-8')
        ->content('div { border:2px solid blue; }');
});

// Return an XML Response from a String
$app->get('/xml-string', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('xml')
        ->content('<test>XML Test using Response Object</test>');
});

// Return an XML Response using XML from PHP's SimpleXML Library
$app->get('/xml-simplexml', function() {
    // Build the XML
    $xml = new SimpleXMLElement('<test>SimpleXML Test using Response Object</test>');
    
    // Return the Response Object using SimpleXML
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('xml')
        ->content($xml->asXml());
});

// Return an XML Response using XML from PHP's XMLWriter Library
$app->get('/xml-xmlwriter', function() {
    // Build the XML
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startDocument('1.0', 'UTF-8');
    $xml->setIndent(false);
    $xml->writeElement('test', 'XMLWriter Test using Response Object');
    $xml->endDocument();
    
    // Return the XML Response
    // Return the Response Object using SimpleXML
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('xml')
        ->content($xml->outputMemory());
});

// Testing Application contentType() encoding options
$app->get('/content-type-encoding', function() {
    // Create the Response Object
    $res = new \FastSitePHP\Web\Response();

    // Test each of the valid charsets with supported content types
    $types = array(
        'html' => 'text/html',
        'text' => 'text/plain',
        'css' => 'text/css',
        'javascript' => 'application/javascript',
    );
    $charsets = array('UTF-8', 'ISO-8859-1', 'GB2312', 'Shift_JIS', 'GBK');
    $test_count = 0;

    // Check each type 'html', etc
    foreach ($types as $param => $type) {
        // For each type check each charset
        foreach ($charsets as $charset) {
            // Calling the function twice in this manner confirms that it is a
            // chainable function when setting the value
            $content_type = $res
                ->contentType($param, $charset)
                ->contentType();

            if ($content_type !== $type . '; charset=' . $charset) {
                return $res
                    ->contentType('text')
                    ->content('Unexpected [' . $param . '] result with charset [' . $charset . '], resulting content-type: ' . $content_type);
            }

            // Keep count
            $test_count++;
        }
    }

    // Finished
    return $res
        ->contentType('text')
        ->content('Success, all ' . $test_count . ' tests passed');
});

$app->get('/content-type-error-invalid-html-charset', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('html', 'CHARSET');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

$app->get('/content-type-invalid-json-charset', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('json', 'UTF-8');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

$app->get('/content-type-invalid-xml-charset', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('xml', 'UTF-8');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// For details on this refer to comments from the same route in [test-app.php]
$app->get('/content-type-invalid-charset-with-full-header', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('text/html', 'UTF-8');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

$app->get('/content-type-invalid-jsonp-option-int', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('jsonp', 0);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

$app->get('/content-type-invalid-jsonp-option-empty-array', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('jsonp', array());
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

$app->get('/content-type-invalid-jsonp-option-empty-string', function() {
    try {
        $res = new \FastSitePHP\Web\Response();
        $res->contentType('jsonp', '');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// First set 'jsonp' then 'javascript' and returning array for an error.
// This test should error because 'javascript' needs to return a string type.
$app->get('/content-type-error-changing-jsonp-to-javascript', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('jsonp')
        ->contentType('javascript')
        ->content(array('test' => 'error'));
});

// Check Content Types set with [contentType()] using [fileTypeToMimeType()]
$app->get('/content-type-from-mime-type', function() {
    $type = array(
        'htm' => 'text/html',
        'md' => 'text/markdown',
        'markdown' => 'text/markdown',
        'csv' => 'text/csv',
        'jsx' => 'text/jsx',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'application/font-woff',
        'pdf' => 'application/pdf',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
        'flv' => 'video/x-flv',
        'mp3' => 'audio/mp3',
        'weba' => 'audio/weba',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/aac',
        'aac' => 'audio/aac',
    );

    $res = new \FastSitePHP\Web\Response();
    $result = array();
    foreach ($type as $key => $expected) {
        $value = $res->contentType($key)->contentType();
        if ($value !== $expected) {
            return "Error {$key} returned {$value} instead of {$expected}";
        }
        $result[] = "[{$key}={$value}]";
    }

    return $res
        ->contentType('text')
        ->content(implode('', $result));
});

$app->get('/custom-content-type', function() {
    $res = new \FastSitePHP\Web\Response();
    $value = $res->contentType('text/template')->contentType();
    return $value;
});

$app->get('/invalid-content-type', function() {
    $res = new \FastSitePHP\Web\Response();
    $res->contentType('template');
    return 'Error - this should have failed';    
});

// Return jsonp (Padded-JSON)
$app->get('/jsonp1', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('jsonp')
        ->content(array('data' => 'jsonp1'));
});

// Return jsonp (Padded-JSON) using a specified query string parameter
$app->get('/jsonp2', function() {
    // Create a basic object using stdClass
    $obj = new \stdClass;
    $obj->prop_name = 'jsonp2';

    // Return Response
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('jsonp', 'fn')
        ->content($obj);
});

// Return JSONP with a defined charset
$app->get('/jsonp3', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('application/javascript; charset=UTF-8')
        ->jsonpQueryString('callback')
        ->content(array('data' => 'jsonp3'));
});


// This function gets called several different times so invalid
// parameters can be tested
$app->get('/jsonp-callback-test', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('jsonp')
        ->content(array('data' => 'jsonp'));
});

// Testing Specific Unicode Control Characters:
//   LINE SEPARATOR (U+2028) 
//   PARAGRAPH SEPARATOR (U+2029)
// These two characters are allowed in JSON Strings but not allowed in JavaScript.
// In some Frameworks these have to be handled manually however PHP should automatically
// handle these with json_encode() which gets called when sending a 'jsonp' result.
// This unit test is created to confirm that the two characters are handled correctly.
// In Ruby on Rails and Express with Node.js these two characters are manually handled:
//   https://github.com/node-modules/jsonp-body/blob/master/index.js
//   https://github.com/rack/rack-contrib/pull/37
$app->get('/jsonp-escape-characters', function() use ($app) {
    // Create Response Object
    $res = new \FastSitePHP\Web\Response();

    // PHP 7 provides better escape syntax for Unicode characters.
    // For more on strings with PHP see:
    //   http://php.net/manual/en/language.types.string.php
    if (PHP_MAJOR_VERSION >= 7) {
        $char1 = "\u{2028}";
        $char2 = "\u{2029}";
    } else {
        $char1 = "\xe2\x80\xa8";
        $char2 = "\xe2\x80\xa9";
    }
    
    // Verify that the characters are correct by comparing to a decoded json string
    // Using json_decode() can also be used instead of the escaped strings above.
    if ($char1 !== json_decode('"\u2028"')) {
        return $res
            ->contentType('jsonp')
            ->content(array(
                'error' => 'Character 1 did not match U+2028',
            ));
    }
    if ($char2 !== json_decode('"\u2029"')) {
        return $res
            ->contentType('jsonp')
            ->content(array(
                'error' => 'Character 2 did not match U+2029',
            ));
    }

    // Return strings that include the escape characters
    return $res
        ->contentType('jsonp')
        ->content(array(
            'string1' => "Test1 $char1 Test2",  // JavaScript: "Test1 \u2028 Test2"
            'string2' => "Test1 $char2 Test2",  // JavaScript: "Test1 \u2029 Test2"
        ));
});

// Return a simple HTML page with common cache headers such as [ETag] and [Last-Modified]
$app->get('/cache-headers-1', function() {
    // Create text content and a hash from the contents
    $html = 'cache-headers-1';
    $hash = md5($html);

    // Time values for the Header
    $last_modified = strtotime('5 August 2015');
    $expires = strtotime('6 August 2015');

    // Set Response Headers and Content using Chainable Functions
    $res = new \FastSitePHP\Web\Response();
    $res
        ->statusCode(200)
        ->etag($hash)
        ->lastModified($last_modified)
        ->cacheControl('public, max-age=86400')
        ->expires($expires)
        ->content($html);
        
    // Check that the values were set as expected and can be read using both the function name
    // without any parameters and the header function.
    if ($res->statusCode() !== 200) {
        throw new \Exception('Error reading Status-Code value on server');
    }
    if ($res->header('etag') !== $res->etag() || $res->header('etag') !== 'W/"89da1dc9504f54ee76041b0f21e28b92"') {
        throw new \Exception('Error reading etag value on server');
    }
    if ($res->header('Last-Modified') !== $res->lastModified() || $res->header('Last-Modified') !== 1438732800) {
        throw new \Exception('Error reading Last-Modified value on server');
    }
    if ($res->header('Cache-Control') !== $res->cacheControl() || $res->header('Cache-Control') !== 'public, max-age=86400') {
        throw new \Exception('Error reading Cache-Control value on server');
    }
    if ($res->header('Expires') !== $res->expires() || $res->header('Expires') !== 1438819200) {
        throw new \Exception('Error reading Expires value on server');
    }
    if ($res->content() !== $html) {
	    throw new \Exception('Error reading content() value on server');
    }

    // Return the Response Object
    return $res;
});

// Return a simple HTML page with only the [Last-Modified] header
$app->get('/cache-headers-2', function() {
	$res = new \FastSitePHP\Web\Response();
	return $res
		->lastModified(strtotime('5 August 2015'))
	    ->content('cache-headers-2');
});

// Test ETag Defined as a Closure Function
$app->get('/cache-headers-3', function() use ($createEtag) {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($createEtag)
	    ->content('cache-headers-3');
});

// This should match the above test as [$app] creates the closure instead
$app->get('/cache-headers-3-v2', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->etag('hash:md5')
		->content('cache-headers-3');
});

// Testing etag() with an invalid hash
$app->get('/cache-headers-3-v2-error', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->etag('hash:error')
	    ->content('cache-headers-3');
});

// Test 'strong' ETag Defined as a Closure Function
$app->get('/cache-headers-3-strong', function() use ( $createEtag) {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($createEtag, 'strong')
		->content('cache-headers-3-strong');
});

// Test ETag that the value is not double-quoted
$app->get('/cache-headers-4', function() {
    // Create text content and a hash from the contents
    $html = 'cache-headers-4';
    $hash = md5($html);

    // Set quotes for the ETag value
    $hash = '"' . $hash . '"';

    // The first test does not specify quotes when adding
    // and ETag but they are required by HTTP Specs so the etag()
    // function adds them if needed but not if they are already 
    // there. This test confirms the case.
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($hash)
		->content($html);
});

// Test 'strong' ETags
$app->get('/cache-headers-5', function() {
    // Create text content and a hash from the contents
    $html = 'cache-headers-5';
    $hash = md5($html);

    // Set ETag and return the response
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($hash, 'strong')
		->content($html);
});

// Testing lastModified() error messages
$app->get('/cache-headers-6', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->lastModified('abc');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing lastModified() error messages
$app->get('/cache-headers-7', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->lastModified(false);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing lastModified() error messages
$app->get('/cache-headers-8', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->expires('abc');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing lastModified() error messages
$app->get('/cache-headers-9', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $object = new \stdClass;
        $res->expires($object);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing etag() error messages
$app->get('/cache-headers-10', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $object = new \stdClass;
        $res->etag($object);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing etag() error messages
$app->get('/cache-headers-11', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->etag('abc', 'type');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing etag() error messages
$app->get('/cache-headers-12', function() use ($returnInt) {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($returnInt)
		->content('cache-headers-12');
});

// Testing noCache() with an eTag
$app->get('/cache-headers-13', function() use ($createEtag) {
    // This checks that noCache() is chainable as well
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->noCache()
        ->etag($createEtag)
        ->content('cache-headers-13');
});

// Testing 'Cache-Control:no-store' with an eTag
// Unit test for the following code line from Response->send():
//   $user_can_cache = ($cache_control === null || strpos($cache_control, 'no-store') === false);
$app->get('/cache-headers-14', function() use ($createEtag) {
    //Setting cache-control as all lower-case as it gets called using 'Cache-Control'
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->header('cache-control', 'no-store')
        ->etag($createEtag)
        ->content('cache-headers-14');
});

// Testing 'expires:0' with an eTag
// Unit test for the following code line from Response->send():
//   $user_can_cache = ($user_can_cache && ($expires === null || (string)$expires !== '0'));
$app->get('/cache-headers-15', function() use ($createEtag) {
    //Setting expires as all upper-case as it gets called using 'Expires'
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->header('EXPIRES', '0')
        ->etag($createEtag)
        ->content('cache-headers-15');
});

// Testing 'Pragma:no-cache' with an eTag
// Unit test for the following code line from Response->send():
//   $user_can_cache = ($user_can_cache && ((($pragma = $this->header('Pragma')) === null || $pragma !== 'no-cache')));
$app->get('/cache-headers-16', function() use ($createEtag) {
    //Setting Pragma as all lower-case as it gets called using 'Pragma'
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->header('pragma', 'no-cache')
        ->etag($createEtag)
        ->content('cache-headers-16');
});

// Testing and Error Response with an eTag
// Unit test for the following code line from Response->send():
//   $status_code_is_200_range = ($this->status_code === null || ($this->status_code >= 200 && $this->status_code < 300));
$app->get('/cache-headers-17', function() use ($createEtag) {
    //Set Status-Code and ETag
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->statusCode(500)
        ->etag($createEtag)
        ->content('Error');
});

// Testing a Post with an eTag
// Unit test for the following code line from Response->send():
//   $method_matches = ($method === 'GET' || $method === 'HEAD');
$app->post('/cache-headers-18', function() use ($createEtag) {
    // Set ETag and return response text
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($createEtag)
		->content('cache-headers-18');
});


// Testing an invalid 'Last-Modified' header 
// This is Unit testing error logic from the sendResponse() function
$app->get('/cache-headers-19', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->header('last-modified', 'abc')
		->content('cache-headers-19');
});

// Testing the 'Expires' header with a string value of '0'
$app->get('/cache-headers-20', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->expires('0')
		->content('cache-headers-20');
});

// Testing the 'Expires' header with a string value of '-1'
$app->get('/cache-headers-21', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->expires('-1')
		->content('cache-headers-21');
});

// Test ETag with a value that has a quote only at the start of the string.
// It should have quotes added. This is testing the following line from the etag() function:
//   $has_quotes = (substr($value, 0, 1) === '"' && substr($value, -1, 1) === '"');
$app->get('/cache-headers-22', function() {
    // Create text content and a hash from the contents
    $html = 'cache-headers-22';
    $hash = md5($html);

    // Set quote for the ETag value
    $hash = '"' . $hash;

    // Set the ETag and return the contents
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($hash)
		->content($html);
});

// This is similar to the above test but testing with quote at the end rather 
// than start of the string.
$app->get('/cache-headers-23', function() {
    // Create text content and a hash from the contents
    $html = 'cache-headers-23';
    $hash = md5($html);

    // Set quote for the ETag value
    $hash = $hash . '"';

    // Set the ETag and return the contents
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->etag($hash)
		->content($html);
});

// Testing various options with cacheControl() and sending
// the result as a JSON Array
$app->get('/cache-headers-24', function() {
    // Test cases
    $test_values = array(
        // First specifiy Valid Values
        'public',
        'private',
        'private, max-age=60',
        'no-cache, no-store, must-revalidate',
        'private=Server',
        'no-cache=Server',
        'private="Server"',
        // NOTE - the following item appears invalid at first glace
        // however "public, max-age" is a quoted-string so it is 
        // parsed as a field value for the option "private" so it is valid
        'private="public, max-age", max-age=60',

        // Specify all Invalid Values below
        'unknown',
        'private, public',
        'private, max-age=abc',
        'private, s-maxage=-1',
        'public, no-store',
        'private, no-store',
        'public=Server',
        'no-store=Server',
        'no-transform=Server',
        'must-revalidate=Server',
        'proxy-revalidate=Server',
        'private="Server',
    );

    // Test each option and save results to an array of objects
    $results = array();
    $res = new \FastSitePHP\Web\Response();

    foreach ($test_values as $test_value) {
        try
        {
            $res->cacheControl($test_value);
            $current_value = $res->cacheControl();
            
            if ($test_value !== $current_value) {
                throw new \Exception(sprintf('Return value from cacheControl() did not match: [%s], [%s]', $test_value, $current_value));
            } else {
                $results[] = array(
                    'option' => $test_value,
                    'isError' => false,
                );                
            }
        } catch (\Exception $e) {
            $results[] = array(
                'option' => $test_value,
                'isError' => true,
                'errorMessage' => $e->getMessage(),
            );
        } 
    }

    // Check for the last valid value
    if ($res->cacheControl() !== 'private="public, max-age", max-age=60') {
        throw new \Exception('The last value for cacheControl() was not the expected result.');
    }

    // Clear the 'Cache-Control' header by sending a blank string
    $res->cacheControl('');

    // Check that the header is now null
    if ($res->cacheControl() !== null) {
        throw new \Exception('The value for cacheControl() should be null');
    }

    // Return as JSON
    return $res
    	->contentType('json')
		->content($results);
});

// Testing various options with vary() and sending
// the result as a JSON Array
$app->get('/cache-headers-25', function() {
    // Test cases
    $test_values = array(
        // First specifiy Valid Values
        'User-Agent',
        'USER-AGENT',
        'Accept, Accept-Charset', 
        'Accept-Encoding, Accept-Language', 
        'Origin',
        'Cookie, Referer',
        '*',

        // Specify Invalid Values below
        'User-Agent, *',
        'UserAgent',
        'Accept-Encoding, User-Agent, Accept-Language',
    );

    // Test each option and save results to an array of objects
    $results = array();
    $res = new \FastSitePHP\Web\Response();

    foreach ($test_values as $test_value) {
        try
        {
            $res->vary($test_value);
            $current_value = $res->vary();

            if ($test_value !== $current_value) {
                throw new \Exception(sprintf('Return value from vary() did not match: [%s], [%s]', $test_value, $current_value));
            } else {
                $results[] = array(
                    'option' => $test_value,
                    'isError' => false,
                );                
            }
        } catch (\Exception $e) {
            $results[] = array(
                'option' => $test_value,
                'isError' => true,
                'errorMessage' => $e->getMessage(),
            );
        } 
    }

    // Check for the last valid value
    if ($res->vary() !== '*') {
        throw new \Exception('The last value for vary() was not the expected result.');
    }

    // Clear the 'Vary' header by sending a blank string
    $res->vary('');

    // Check that the header is now null
    if ($res->vary() !== null) {
        throw new \Exception('The value for vary() should be null');
    }

    // Return as JSON
    return $res
    	->contentType('json')
		->content($results);
});

// Testing expires() date validation for values greater than one year from now
$app->get('/cache-headers-26', function() {
    // Return type is text
    $res = new \FastSitePHP\Web\Response();
    $res->contentType('text');

    // This values gets converted to a unix timestamp with strtotime()
    $future_date = '+ 1 year, 1 day';

    // Makes sure expires() throws an exception
    try {
        $res->expires($future_date);
        return 'expires() should have thrown an exception';
    } catch (\Exception $e) {
        $expected_message = 'Invalid Value for [FastSitePHP\Web\Response->expires()]. Expires date values cannot be greater than one year from the current time using this function. To set the header value to a date greater than one year from today use the header() function instead.';
        if ($e->getMessage() !== $expected_message) {
            return 'Unexpected Error Message for expires(): ' . $e->getMessage();
        }
    }

    // Make sure the value can be set with the header function
    $time = strtotime($future_date);
    $res->header('Expires', $time);

    // Make sure the value can be read
    if ($res->expires() !== $time) {
        return sprintf('expires() returned [%s] but should have returned [%s]', $app->expires(), $time);
    }

    // Overwrite with no cache headers and return success response
    return $res
    	->noCache()
		->content('Success passed all tests for the expires() max time validation');
});

// Testing header() error messages
$app->get('/header-1', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->header(123);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing header() error messages
$app->get('/header-2', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->header('');
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing header() error messages
$app->get('/header-3', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->header('X-Custom', 123);
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing header() error messages
$app->get('/header-4', function() {
    try {
	    $res = new \FastSitePHP\Web\Response();
        $res->header('X-Custom', function(){ return 'ABC'; });
        echo 'This line should never get called';
    } catch (\Exception $e) {
        return $res
        	->contentType('text')
			->content($e->getMessage());
    }
});

// Testing header() with a Response Header
$app->get('/header-5', function() {
    // Create Response Object  and specify text output
    $res = new \FastSitePHP\Web\Response();
    
    // Get all response headers
    $response_headers = $res->headers();
    if (count($response_headers) !== 0) {
        return $res
        	->contentType('text')
			->content('Error - There should not be any Response headers created before setting one');
    }

    // Set a customer Response Header, this confirms the function
    // is chainable and that it is case-insenstive as the 2nd call
    // should overwrite the first call
    $res
        ->header('X-CUSTOM-HEADER', 'Value 1')
        ->header('X-Custom-Header', 'Value 2');

    $response_headers = $res->headers();
    if (count($response_headers) !== 1) {
	    return $res
	    	->contentType('text')
			->content('Error - There should only be one Response header at this point');
    }

    // Even though value was overwritten the first header key remains
    if ($response_headers['X-CUSTOM-HEADER'] !== 'Value 2') {
	    return $res
	    	->contentType('text')        
        	->content('Error - [X-Custom-Header] should be found from the headers() function');
    }

    // Getting the header using an all lower-case key
    if ($res->header('x-custom-header') !== 'Value 2') {
	    return $res
	    	->contentType('text')
			->content('Error - [X-Custom-Header] should be found from the header() function');
    }

    // Clear the header and get the headers again, this also makes sure that clearing
    // a value returns the app object
    $response_headers = $res
        ->header('x-custom-header', '')
        ->headers();

    if (count($response_headers) !== 0) {
	    return $res
	    	->contentType('text')
			->content('Error - There should not be any Response headers after clearing the created value');
    }

    // Success for how the header() function works.
    // Add back the header with a new value and return the response.
    // The client still needs to verify the actual header.
    return $res
    	->contentType('text')    
		->header('X-Custom-Header', 'FastSitePHP')
		->content('header-5 Test');
});

// Verify that 'Content-Length' can be defined as an integer
// then remove the header. This header is not sent by any other unit test
// because it most cases the web-server would overwrite it for HTML/Text/Json/etc.
$app->get('/header-6', function() {
	$res = new \FastSitePHP\Web\Response();
    $res->header('Content-Length', 10);
    $content_length = $res->header('Content-Length');
    $res->header('Content-Length', '');
    return sprintf('Header Count: %d, Defined Length (%s): %d', Count($res->headers()), gettype($content_length), $content_length);
});

// Cookie test, the header values are checked after the response is sent
// on the $app->after() function defined at the top of this file.
$app->route('/cookie-1', function() use ($app) {
    // Get the current time and assign it as a property in the app
    $app->time = time();

    // Add Cookies
    $res = new \FastSitePHP\Web\Response();
    $res
        ->cookie('unit-test-route', 'value', $app->time, '/path', 'domain.tld', true, true)
        ->cookie('unit-test-data', 'value');

    // Check Data
    $cookies = $res->cookies();
    if (count($cookies) !== 2) {
        return 'Cookie Count did not match';
    }

    if ($cookies[0]['name'] !== 'unit-test-route'
        || $cookies[0]['value'] !== 'value'
        || $cookies[0]['expire'] !== $app->time
        || $cookies[0]['path'] !== '/path'
        || $cookies[0]['domain'] !== 'domain.tld'
        || $cookies[0]['secure'] !== true
        || $cookies[0]['httponly'] !== true
    ) {
       return 'Cookie 0 did not match';
    }

    if ($cookies[1]['name'] !== 'unit-test-data'
        || $cookies[1]['value'] !== 'value'
        || $cookies[1]['expire'] !== 0
        || $cookies[1]['path'] !== ''
        || $cookies[1]['domain'] !== ''
        || $cookies[1]['secure'] !== false
        || $cookies[1]['httponly'] !== false
    ) {
       return 'Cookie 1 did not match';
    }

    // Update Cookies
    $res
        ->cookie('unit-test-route', 'cookie-1', $app->time - 1, '/path', 'domain.tld', true, true)
        ->cookie('unit-test-data', 'abc123');

    // Check Data
    $cookies = $res->cookies();
    if (count($cookies) !== 2) {
        return 'Cookie Count did not match';
    }

    if ($cookies[0]['name'] !== 'unit-test-route'
        || $cookies[0]['value'] !== 'cookie-1'
        || $cookies[0]['expire'] !== ($app->time - 1)
        || $cookies[0]['path'] !== '/path'
        || $cookies[0]['domain'] !== 'domain.tld'
        || $cookies[0]['secure'] !== true
        || $cookies[0]['httponly'] !== true
    ) {
       return 'Cookie 0 did not match';
    }

    if ($cookies[1]['name'] !== 'unit-test-data'
        || $cookies[1]['value'] !== 'abc123'
        || $cookies[1]['expire'] !== 0
        || $cookies[1]['path'] !== ''
        || $cookies[1]['domain'] !== ''
        || $cookies[1]['secure'] !== false
        || $cookies[1]['httponly'] !== false
    ) {
       return 'Cookie 1 did not match';
    }
    
    // Success return a response
    return $res->cors($app)->content('[Cookie Test 1]');
});

// Set the cookies to deleted by passing blank or missing values.
// Just like the above test data is checked on the $app->after() function.
$app->route('/cookie-2', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
        ->cookie('unit-test-route', '', -1, '/path', 'domain.tld', true, true)
        ->cookie('unit-test-data')
        ->content('[Cookie Test 2]');
});

// Trigger an error by using an unsupported data type for the value
$app->route('/cookie-3', function() {
	$res = new \FastSitePHP\Web\Response();
    return $res
    	->cookie('unit-test', array('cookie-3'))
		->content('[Cookie Test 3]');
});

// Same error as above but with PHP error reporting turned off
// which will cause the Application to throw an Exception
$app->route('/cookie-4', function() {
    // Turn off error reporting prior to the error
    error_reporting(0);
    
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->cookie('unit-test', array('cookie-4'))
		->content('[Cookie Test 4]');
});

// Similar to the above error test but using the wrong data type for the cookie name
$app->route('/cookie-5', function() {
    // Turn off error reporting prior to the error
    error_reporting(0);
    
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->cookie(array('unit-test'))
		->content('[Cookie Test 5]');
});

// Verify all File/Mime Types plus common types that return
// 'application/octet-stream' from the function fileTypeToMimeType()
$app->get('/check-mime-types', function() {
    // Create an Array of Files Types to Test
    $file_types = 'htm,html,txt,md,markdown,csv,css,jsx,png,gif,webp,jpg,jpeg,';
    $file_types .= 'svg,ico,js,woff,json,xml,mp4,webm,ogv,flv,mp3,weba,ogg,';
    $file_types .= 'm4a,aac,data,doc,docx,xls,xlsx,zip';
    $file_types = explode(',', $file_types);

    // Build an Array of File Types with the associated Mime-Type
    $res = new \FastSitePHP\Web\Response();
    $mime_types = array();
    foreach ($file_types as $file_type) {
        $mime_types[$file_type] = $res->fileTypeToMimeType('test.' . $file_type);
    }

    // Return JSON object of all tested file types
    return $res
    	->contentType('json')
		->content($mime_types);
});

// Validate a file() response. The file() allows for basic streaming
// and provides a memory efficient method for sending files to the client
$app->get('/download-file', function() {
    // Simple Text File for Testing
    $path = __DIR__ . '/files/text-file.txt';

    // File is called several times to verify the different parameters

    // First test is with a custom 'Cache-Control' header and MD5 for the ETag
    $res = new \FastSitePHP\Web\Response();
    $cache_control = 'private, must-revalidate';
    
    // Verify that file() is chainable
    $md5 = $res
    	->file($path, 'download', 'etag:md5', $cache_control)
		->etag();

    if ($path !== $res->file()) {
        throw new \Exception('Unexpected value from $res->file()');
    }
    if ($cache_control !== $res->cacheControl()) {
        throw new \Exception('Unexpected value from $res->cacheControl()');
    }

    // Next test with SHA-1 for the Etag
    $sha1 = $res
    	->file($path, 'download', 'etag:sha1')
	    ->etag();
	    
    // Test the 'last-modified' parameter with 'text' as the content type.
    // Because this file date modified can change when the site or folder 
    // is copied the actual date value is not tested but rather matched
    // to the filemtime() function.
    $res->file($path, 'text', 'last-modified');
    $last_modified = ($res->lastModified() === filemtime($path) ? 'valid' : 'invalid');
    
    if ($res->contentType() !== 'text/plain') {
        throw new \Exception('Unexpected value from $res->contentType(): ' . $res->contentType());
    }

    // Clear file and verify
    $file = $res->file('');

    if ($file !== $res) {
        throw new \Exception('Unexpected result from $res->file(\'\')');
    }
    if ($res->file() !== null) {
        throw new \Exception('$res->file() should be null');
    }
        
    // Clear 'ETag' and 'Last-Modified' headers, defined custom headers,
    // and return the file download
    return $res
    	->etag('')
		->header('Last-Modified', '')
		->header('X-Hash-md5', $md5)
		->header('X-Hash-sha1', $sha1)
		->header('X-Last-Modified', $last_modified)
		->file($path, 'download');
});

// Use 'application/octet-stream' rather than 'download' as the [$content_type] 
// parameter. The response will be the same.
$app->get('/download-file2', function() {
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path, 'application/octet-stream');
});

// Do not specify a file type so that the file() function will use the file
// extension to determine mime-type. In this case 'text/plain'.
$app->get('/download-file3', function() {
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path);
});

// Send a file with an ETag header so that the server can also
// send a 304 'Not Modified' Response
$app->get('/download-file4', function() {
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path, 'text', 'etag:md5');
});

// Test file() with a file that doesn't exist
$app->get('/download-missing-file-error', function() {
    $path = __DIR__ . '/../../app_data/unit-testing/files/missing-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path, 'download');
});

// Test file() with an invalid [$cache_type] parameter
$app->get('/download-file-invalid-param', function() {
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path, 'download', 'cache_type_error');
});

// Test file() with a valid directory path instead of a file.
// This will trigger an error.
$app->get('/file-response-with-directory', function() {
    $path = __DIR__ . '/../../app_data/unit-testing/files/';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path);
});

// Verify a specific error condition for generating an etag with 
// a closure which is not allowed for file responses.
$app->get('/file-response-invalid-etag', function() use ($createEtag) {
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res
    	->file($path)
		->etag($createEtag);
});

// The function [file()] is designed to handle large files.
// For example streaming a video file that is several hundred 
// megabytes or gigabytes in size. However the unit tests only
// actually test small files. The code is manually tested with
// large files and for unit testing this function verifies that the
// buffer is properly set so that streaming works.
//
// This code is testing the following loop:
//    while (ob_get_level()) {
// If [ob_end_clean()] were called only once from sendResponse()
// then the result would be "[test]This is a simple text file."
// however because it is called multiple times the response is
// "This is a simple text file."
$app->get('/download-file-buffer', function() {
    ob_start();
    echo '[test]';
    echo ob_get_clean();
    ob_start();
    $path = __DIR__ . '/files/text-file.txt';
    $res = new \FastSitePHP\Web\Response();
    return $res->file($path, 'text/plain');
});

// Test the reset() function
$app->get('/reset', function() {
    // Define a function to check empty property values.
    // Default values are all null or empty arrays.
    function checkEmptyProperties($res, $first_check)
    {
        $message = ($first_check ? 'Before calling reset():' : 'After calling reset():');

        // Check Null Properties
        // NOTE - these are not "real" properties but rather method/function calls
        // that behave like a property with a getter and setter.
        // Also the sytax [$res->$prop()] is known as a Variable function in PHP:
        //   http://php.net/manual/en/functions.variable-functions.php
        $properies = array('statusCode', 'content', 'etag', 'file', 'jsonpQueryString');
        foreach ($properies as $prop) {
            if ($res->$prop() !== null) {
                throw new \Exception(sprintf('%s Unexpected value from %s->%s(), should be null by default', $message, get_class($res), $prop));
            }
        }

        // Check Empty Arrays
        $properies = array('headers', 'cookies');
        foreach ($properies as $prop) {
            if ($res->$prop() !== array()) {
                throw new \Exception(sprintf('%s Unexpected value from %s->%s(), should be an empty array by default', $message, get_class($res), $prop));
            }
        }

        // Use Reflection to check the private property [etag_type]
        $prop_info = new \ReflectionProperty($res, 'etag_type');
        $prop_info->setAccessible(true);
        if ($prop_info->getValue($res) !== null) {
            throw new \Exception(sprintf('%s Unexpected value of [%s] from %s->etag_type, should have returned: null', $message, $prop_info->getValue($res), get_class($res)));
        }
    }
    
    // Define a function to set and check property values
    function defineProperties($res)
    {
        // Define and Check Basic Properties using Methods
        $properies = array(
            'statusCode' => 500, 
            'content' => 'content check', 
            'file' => __FILE__, 
            'jsonpQueryString' => array('callback', 'jsonp'),
        );

        foreach ($properies as $name => $value) {
            if ($value !== $res->$name($value)->$name()) {
                throw new \Exception(sprintf('Unexpected value of [%s] from %s->%s(), should have returned: %s', $res->$name(), get_class($res), $name, $value));
            }
        }

        // Set values for Array Properties
        $res->etag('hash:md5');
        $res->cookie('Cookie-Name', 'Value');

        // Use Reflection to check the private property [etag_type]
        $prop_info = new \ReflectionProperty($res, 'etag_type');
        $prop_info->setAccessible(true);
        if ($prop_info->getValue($res) !== 'weak') {
            throw new \Exception(sprintf('Unexpected value of [%s] from %s->etag_type, should have returned: weak', $prop_info->getValue($res), get_class($res)));
        }

        // Check Array Counts
        $properies = array('headers' => 7, 'cookies' => 1);
        foreach ($properies as $prop => $expected_count) {
            if (count($res->$prop()) !== $expected_count) {
                throw new \Exception(sprintf('Unexpected count of [%s] from %s->%s(), should be an array with %s item(s)', count($res->$prop()), get_class($res), $prop, $expected_count));
            }
        }
    }

    // Run the Test
    $res = new \FastSitePHP\Web\Response();
    checkEmptyProperties($res, true);
    defineProperties($res);
    $res->reset();
    checkEmptyProperties($res, false);

    // Success if no error
    return $res->content('reset() function has been tested');
});

// Test an Exception that gets thrown by the send() function if both
// file() and content() are called.
$app->get('/send-error-1', function() {
    $res = new \FastSitePHP\Web\Response();
    $file_path = __FILE__;

    return $res
    	->file($file_path)
		->content('test');
});

// Test an Exception that gets thrown by the send() function if neither
// file() or content() are called.
$app->get('/send-error-2', function() {
    $res = new \FastSitePHP\Web\Response();
    return $res;
});

// Manually call the send function rather than returning the Response Object
$app->get('/manual-send', function() {
    $res = new \FastSitePHP\Web\Response();
    $res
        ->content('<h1>Calling send()</h1>')
        ->send();
});

// Dynamically define Routes for each of the Supported Redirect Status Codes
// Similar tests exist in [test-app.php] however they are for
// the redirect() method of the Application Object.
$redirect_status_codes = array(301, 302, 303, 307, 308);
foreach ($redirect_status_codes as $status_code) {
    $app->get("/redirect-$status_code", function() use ($status_code) { 
        $res = new \FastSitePHP\Web\Response();
        return $res->redirect("redirected-$status_code", $status_code); 
    });
    $app->get("/redirected-$status_code", function() use ($status_code) {
        $res = new \FastSitePHP\Web\Response();
        return $res->content("$status_code Redirect from Response");
    });
}

// Redirect with URL Parameters, the command line unit tests
// available in the folder [docs/unit-testing] confirm that 
// the ampersand [&] is correctly escaped.
$app->get("/redirect-with-params", function() { 
    $res = new \FastSitePHP\Web\Response();
    return $res->redirect('redirected-with-params?param1=abc&param2=123');
});
$app->get("/redirected-with-params", function() { 
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->contentType('json')
        ->content($_GET);
});

// Test the redirect() function for invalid function calls
// A similar test exist in [test-app.php] however it is for
// the redirect() method of the Application Object.
$app->get('/redirect-errors', function() {
    // Create the Response Object
    $res = new \FastSitePHP\Web\Response();

    // Test for exceptions, each of these tests 
    // is expected to thrown an exception.
    $test_error_count = 0;

    $error_tests = array(
        array(
            'url' => 123,
            'expected_error' => 'Invalid parameter type [$url] for [FastSitePHP\Web\Response->redirect()], expected a [string] however a [integer] was passed.',
        ),
        array(
            'url' => '',
            'expected_error' => 'Invalid parameter for [FastSitePHP\Web\Response->redirect()], [$url] cannot be an empty string.',
        ),
        array(
            'url' => "URL1 \n URL2",
            'expected_error' => 'Invalid parameter for [FastSitePHP\Web\Response->redirect()], [$url] should be in the format of a URL understood by the client and cannot contain a line break. The URL passed to this function included a line break character.',
        ),
        array(
            'url' => '404',
            'status_code' => 404,
            'expected_error' => 'Invalid [$status_code = 404] specified for [FastSitePHP\Web\Response->redirect()]. Supported Status Codes are [301, 302, 303, 307, 308].',
        ),
    );

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;
            
            // Test
            if (isset($test['status_code'])) {
                $res->redirect($test['url'], $test['status_code']);
            } else {
                $res->redirect($test['url']);
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
    echo '[redirect-errors-from-response-object]';
    ob_flush();
    
    // The final error test requires [headers_sent() === true]
    try {
        $test_error_count++;
        $res->redirect('redirected');
    } catch (\Exception $e) {
        $expected = 'Error trying to redirect from [FastSitePHP\Web\Response->redirect()] because Response Headers have already been sent to the client.';
        if ($e->getMessage() !== $expected) {
            echo sprintf('Error with Exception Test %d, The test correctly threw an exception but the message did not match the expected error message.', $test_error_count);
            echo '<br><br>';
            echo $e->getMessage();
            echo '<br><br>';
            echo json_encode($expected, JSON_PRETTY_PRINT);
            exit();
        }
    }

    // Success all Errors Tests returned the expected Exception Text
    echo '[Tested Errors: ' . $test_error_count . ']';
    exit();
});

// Testing of the cors() Function.
// This function is validating logic of the function but does 
// not send any CORS headers to the client. Testing of actual 
// CORS headers is handled in additional tests from this file.
$app->get('/cors-validation', function() use ($app) {
    // Create a new Response Object
    // and keep count of the tests
    $res = new \FastSitePHP\Web\Response();
    $test_count = 0;

    // Define a Non-CORS Header to confirm that calling 
    // cors() does not affect any other headers.
    $res->contentType('html');

    // Make sure null is returned if no CORS headers are set.
    // Set and Get the CORS Values on the same line to confirm
    // that the function is chainable.
    $cors_headers = $res->cors($app)->cors();
    if ($cors_headers !== null) {
        echo sprintf('Error on Test %d, checking for null Headers', $test_count);
        echo '<br><br>';
        var_dump($cors_headers);
        exit();
    }
    $test_count++;

    // Set Multiple CORS Headers
    $headers = array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
    );
    $app->cors($headers);
    $cors_headers = $res->cors($app)->cors();
    if ($cors_headers !== $headers) {
        echo sprintf('Error on Test %d, checking for Multiple Headers', $test_count);
        echo '<br><br>';
        var_dump($cors_headers);
        exit();
    }
    $test_count++;

    // Also verify that the values are now visible from the 
    // headers() and header() functions.
    $expected_headers = array(
        'Content-Type' => 'text/html; charset=UTF-8',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
    );
    
    if ($res->headers() !== $expected_headers) {
        echo sprintf('Error on Test %d, checking headers()', $test_count);
        echo '<br><br>';
        var_dump($res->headers());
        exit();
    }
    $test_count++;

    if ($res->header('Access-Control-Allow-Origin') !== '*') {
        echo sprintf('Error on Test %d, checking header(Access-Control-Allow-Origin)', $test_count);
        echo '<br><br>';
        var_dump($res->header('Access-Control-Allow-Origin'));
        exit();
    }
    $test_count++;

    // Set only [Access-Control-Allow-Origin], this should clear
    // [Access-Control-Allow-Headers] which was previously defined.
    $expected_headers = array('Access-Control-Allow-Origin' => '*');
    $app->cors('*');
    $cors_headers = $res->cors($app)->cors();
    if ($cors_headers !== $expected_headers) {
        echo sprintf('Error on Test %d, checking for Multiple Headers', $test_count);
        echo '<br><br>';
        var_dump($cors_headers);
        exit();
    }
    $test_count++;

    // Clear all CORS Headers
    $headers = array('Access-Control-Allow-Origin' => '*');
    $app->cors('');
    $cors_headers = $res->cors($app)->cors();
    if ($cors_headers !== null) {
        echo sprintf('Error on Test %d, checking for Cleared CORS Headers', $test_count);
        echo '<br><br>';
        var_dump($cors_headers);
        exit();
    }
    $test_count++;

    // Checkk that only 1 header remains after all tests
    $expected_headers = array(
        'Content-Type' => 'text/html; charset=UTF-8',
    );
    
    if ($res->headers() !== $expected_headers) {
        echo sprintf('Error on Test %d, checking headers() after all CORS Tests', $test_count);
        echo '<br><br>';
        var_dump($res->headers());
        exit();
    }
    $test_count++;

    // Return the Response Object with
    // the number of passed tests
    $html = $app->escape(sprintf('Success checked Response->cors() with %d passed tests', $test_count));
    return $res->content($html);
});

// Set the Header Value for [Access-Control-Allow-Origin] using [cors()].
// This is based on a route from [test-app.php] however this version
// uses the Response Object for Sending the Headers on the Page.
$app->get('/cors-1', function() use ($app) {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cors($app)
        ->content('Testing cors() [Access-Control-Allow-Origin] from Response Object with a String Value');
})
->filter(function() use ($app) {
    $app->cors('*');
});

// Set Multiple CORS Header Values using [cors()].
// For more refer to comments from route '/cors-1'.
// A similar route also exists in [test-app.php].
$app->get('/cors-2', function() use ($app) {
    $res = new \FastSitePHP\Web\Response();
    return $res
        ->cors($app)
        ->content('Testing cors() [Access-Control-Allow-Origin] from Response Object with an Array');
})
->filter(function() use ($app) {
    $app->cors(array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
    ));
});

// Test Setup with Response Class Constructor
$app->get('/headers-from-app', function() use ($app) {
    $res = new \FastSitePHP\Web\Response($app);
    return $res->content('Test with Response->__construct()');
})
->filter(function() use ($app) {
    $app
        ->cors(array(
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ))
        ->statusCode(202)
        ->noCache()
        ->header('X-Custom-Header', 'Unit-Test')
        ->header('X-API-Key', 'password123');
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
