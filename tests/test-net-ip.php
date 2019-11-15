<?php
// ===========================================================
// Unit Testing Page
// *) This file uses only core Framework files,
//	  Web\Request, and Net\IP Objects.
// *) The Web\Request Object is included because it 
//	  uses the Net\IP Object for Proxy Functions
//	  which get tested from this file. 
// ===========================================================

// -----------------------------------------------------------
// Setup FastSitePHP
// -----------------------------------------------------------

// Include only the needed Files and run under 
// the web root folder or [fastsitephp/tests]
if (is_dir('../../vendor/fastsitephp')) {
    require '../../vendor/fastsitephp/src/Application.php';
    require '../../vendor/fastsitephp/src/Route.php';
    require '../../vendor/fastsitephp/src/Net/IP.php';
    require '../../vendor/fastsitephp/src/Web/Request.php';
} else {
    require '../src/Application.php';
    require '../src/Route.php';    
    require '../src/Net/IP.php';
    require '../src/Web/Request.php';
}

// Create the Application Object
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;

// -----------------------------------------------------------
// Functions shared between multiple Unit Tests
// -----------------------------------------------------------

/**
 * This function is for testing [$req->clientIp()]. IPv4 testing is separate 
 * from IPv6 Testing in case the server does not support IPv6. The tests are 
 * similar so core testing features of the function are shared here.
 * [$req->clientIp()] comes from the [Web\Request] Object however it's 
 * dependant on the [Net\IP] object which is why it's tested here.
 *
 * @param string $ip_type 'IPv4' or 'IPv6'
 * @param array $tests
 * @param array $error_tests
 */
function testClientIp($ip_type, array $tests, array $error_tests) {
    // Server Variables are always cleared on each test from this 
    // nested function if not defined for the test.
    function resetServerVars($test) {
        // REMOTE_ADD = IP Address of the TCP Connection (end-user or proxy server)
        // HTTP_X_FORWARDED_FOR = Non-standard but commonly used Request Header [X-Forwarded-For]
        // HTTP_CLIENT_IP = Non-standard Request Header [X-Client-Ip], less common than [X-Forwarded-For]
        // HTTP_FORWARDED = Standards based Request Header [Forwarded] but not commonly used as of 2016
        // HTTP_X_REMOTE_IP = Represents a custom header defined but an application
        $server_vars = array('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED', 'HTTP_X_REMOTE_IP');
        foreach ($server_vars as $server_var) {
            if (isset($test[$server_var])) {
                $_SERVER[$server_var] = $test[$server_var];
            } elseif (isset($_SERVER[$server_var])) {
                unset($_SERVER[$server_var]);
            }
        }
    }

    // Create Request Object
    $req = new \FastSitePHP\Web\Request();

    // ----------------------------------------------------------
    // Run Tests from the array $tests
    // ----------------------------------------------------------
    $test_count = 0;

    // Run Tests calling the clientIp() function and comparing the result to the 
    // expected value. Each of these tests should return the expected value
    // and no exceptions should be thrown.
    foreach ($tests as $test) {
        // Reset Server Variables
        resetServerVars($test);

        // Call clientIp() with different parameters defined 
        // depending upon how each test is defined.
        // *) If 'option' is not defined then the $option parameter will not be included 
        //    and left as default when the function is called. 
        // *) If 'trusted_proxies' is not defined then it will also not be passed to the
        //    function resulting in the default 'trust local' which applies only if $option is specified.
        // *) The value 'trust local' tells the clientIp() function to trust all Private Network CIDR Notation
        //    strings that are returned from the function [IP::privateNetworkAddresses()].
        if (array_key_exists('trusted_proxies', $test)) {
            $value = $req->clientIp($test['option'], $test['trusted_proxies']);
        }elseif (array_key_exists('option', $test)) {
            $value = $req->clientIp($test['option']);
        } else {
            $value = $req->clientIp();
        }

        // Keep count of each test
        $test_count++;

        // First check the return type then the actual value if it matches.
        // If any test fails the function will end showing details of what test failed.
        if (gettype($value) !== $test['return_type']) {
            echo sprintf('Error with Test %d, Type Mismatch, Expected Type: [%s], Return Type: [%s]', $test_count, $test['return_type'], gettype($value));
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        } elseif ($value !== $test['expected']) {
            echo sprintf('Error with Test %d, Incorrect Return Value', $test_count);
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        }
    }

    // ---------------------------------------------
    // Test for exceptions, each of these tests 
    // is expected to thrown an exception.
    // ---------------------------------------------
    $test_error_count = 0;

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;

            // Reset Server Variables
            resetServerVars($test);

            // Call clientIp() with different parameters defined 
            // depending upon how each test is defined
            if (array_key_exists('trusted_proxies', $test)) {
                $value = $req->clientIp($test['option'], $test['trusted_proxies']);
            }elseif (array_key_exists('option', $test)) {
                $value = $req->clientIp($test['option']);
            } else {
                $value = $req->clientIp();
            }
            
            // If the test doesn't error that there is a problem
            echo sprintf('Error with Exception Test %d, The test did not fail but should have thrown an exception.', $test_error_count);
            echo '<br><br>';
            echo '<strong>Value:</strong> ' . $value;
            echo '<br><br>';
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

    // All Tests Passed, if code execution reaches here
    return sprintf('Success for clientIp() function with %s Addresses, Completed %d Unit Tests and %d Exception Tests', $ip_type, $test_count, $test_error_count);
}

// -----------------------------------------------------------
// Define Routes for Unit Testing
// -----------------------------------------------------------

// Check how the Network IP Object is defined
$app->get('/check-net-ip-class', function() {
    $ip = new \FastSitePHP\Net\IP();
    return array(
        'get_class' => get_class($ip),
        'get_parent_class' => get_parent_class($ip),
    );
});

// Check Default Object Properties
$app->get('/check-net-ip-properties', function() {
    // Define arrays of properties by type
    $null_properties = array();
    $true_properties = array();
    $false_properties = array();
    $string_properties = array();
    $array_properties = array();
    $private_properties = array();
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $ip = new \FastSitePHP\Net\IP();
    return checkObjectProperties($ip, $null_properties, $true_properties, $false_properties, $string_properties, $array_properties, $private_properties);
});

// Check Functions, this is similar to the above function
// but instead of checking properties it checks the functions.
$app->get('/check-net-ip-methods', function() {
    // Define arrays of function names by type
    $private_methods = array();
    $public_methods = array(
        'cidr', 'privateNetworkAddresses'
    );
    
    // Load the core function file and verify the object 
    // using a function defined in the file.
    require('./core.php');
    $ip = new \FastSitePHP\Net\IP();
    return checkObjectMethods($ip, $private_methods, $public_methods);
});

// Test the cidr() function for general errors (parameter type, etc)
$app->get('/cidr-general-errors', function() {
    // Add tested errors to an array
    $errors = array();

    // Invalid CIDR Data Type
    try {
        \FastSitePHP\Net\IP::cidr(0);
        $errors['test' . count($errors)] = 'Test passed but should have failed';
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid IP Data Type
    try {
        \FastSitePHP\Net\IP::cidr('', 0);
        $errors['test' . count($errors)] = 'Test passed but should have failed';
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid CIDR String without an IP
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid CIDR String with an IP
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('', '');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Return errors as an Object in JSON Format
    return $errors;
});

// Test the cidr() function to obtain networking info from an IPv4 CIDR Notation String.
// IPv4 testing is separate from IPv6 Testing in case the server does not support IPv6.
$app->get('/cidr-ipv4', function() {
    return array(
        'item1' => \FastSitePHP\Net\IP::cidr('10.63.5.183/24'),
        'item2' => \FastSitePHP\Net\IP::cidr('54.231.17.108/17'),
    );
});

// Test the cidr() function to compare IP Addresses to an IPv4 CIDR Value
$app->get('/cidr-ipv4-compare', function() {
    // Build an array of options to test
    $tests = array(
        // Comparing CIDR String to IP Address
        0 => array('10.63.5.183/24', '10.63.5.120'), // returns true
        1 => array('10.63.5.183/24', '10.63.4.183'), // returns false
        // IP Address Only and no CIDR String
        2 => array('10.10.120.12', '10.10.120.12'), // returns true
        3 => array('10.10.120.12', '10.10.120.13'), // returns false
        // Comparing CIDR String to IP Address
        4 => array('54.231.0.0/17',   '54.231.17.108'), // returns true
        5 => array('54.231.128.0/19', '54.231.17.108'), // returns false
        // Invalid IP Address, returns false
        6 => array('10.0.0.0/8', 'abc'),
        // Compare to an Array of CIDR Strings
        7 => array(array('10.0.0.0/8', '54.231.0.0/17'), '54.231.17.109'),  // returns true
        8 => array(array('10.0.0.0/8', '54.231.0.0/17'), '169.254.1.1'),    // returns false
        9 => array(array('172.16.0.0/12', '169.254.0.0/16'), '169.254.1.1'),    // returns true
        10 => array(array('127.0.0.0/8'), '127.0.0.1'), // returns true
        // Private Network with Port Number, returns true
        11 => array('10.0.0.0/8', '10.10.120.13:8080'),
        // IPv4 Local/Private Network Addresses, these will all return true.
        // These values can be obtained from the function [\FastSitePHP\Net\IP::privateNetworkAddresses()].
        12 => array('127.0.0.0/8', '127.0.0.1'), // localhost
        13 => array('127.0.0.0/8', '127.0.0.2'), // localhost
        14 => array('10.0.0.0/8', '10.0.0.1'), // Private Network, RFC1918 24-bit block
        15 => array('172.16.0.0/12', '172.16.0.1'), // Private Network, RFC1918 20-bit block
        16 => array('192.168.0.0/16', '192.168.0.1'), // Private Network, RFC1918 16-bit block
        17 => array('169.254.0.0/16', '169.254.1.1'), // local-link
    );

    // Run each test and add to an array, in JavaScript this will
    // get converted to an object with child objects for each property
    $results = array();
    foreach ($tests as $key => $value) {
        $results['item' . str_pad($key, 2, '0', STR_PAD_LEFT)] = array(
            'cidr' => $value[0],
            'ip_to_compare' => $value[1],
            'result' => \FastSitePHP\Net\IP::cidr($value[0], $value[1]),
        );
    }
    
    // Return the result in JSON Format
    return $results;
});

// Test the cidr() function for errors related to IPv4 Addresses
$app->get('/cidr-ipv4-errors', function() {
    // Add tested errors to an array
    $errors = array();
    
    // Invalid CIDR String
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('abc.abc.abc.abc/24');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid CIDR String with an IP
    try {
        \FastSitePHP\Net\IP::cidr('abc.abc.abc.abc/24', '127.0.0.1');
        $errors['test' . count($errors)] = 'Test passed but should have failed';
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Valid IP in CIDR Value but Invalid Bit Range
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('127.0.0.1/-1', '127.0.0.1');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Valid IP in CIDR Value but Invalid Bit Range
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('127.0.0.1/33', '127.0.0.1');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Return errors as an Object in JSON Format
    return $errors;
});

// Test the cidr() function to obtain networking info from an IPv6 CIDR Notation String
$app->get('/cidr-ipv6', function() {
    return array(
        'item1' => \FastSitePHP\Net\IP::cidr('fe80::b091:1117:497a:9dc1/48'),
        'item2' => \FastSitePHP\Net\IP::cidr('2001:db8:0123:4567:89ab:cdef:9876:5432/32'),
    );
});

// Test the cidr() function to compare IP Addresses to an IPv6 CIDR Value
$app->get('/cidr-ipv6-compare', function() {
    // Build an array of options to test
    $tests = array(
        // Checking a local-link Address, returns true
        0 => array('fe80::/10', 'fe80::b091:1117:497a:9dc1'),
        // localhost IPv6, the values look different but are the same 
        // because the CIDR String is omitting leading zeros
        1 => array('::1', '0000:0000:0000:0000:0000:0000:0000:0001'), // return true
        2 => array('::1/128', '0000:0000:0000:0000:0000:0000:0000:0001'), // return true
        // Compaing Values
        3 => array('2001:db8::/32', 'fe80::b091:1117:497a:9dc1'), // returns false
        4 => array('2001:db8::/32', '2001:db8:0123:4567:89ab:cdef:9876:5432'), // returns true
        // Compare an IPv4 Address to an IPv6 CIDR String, return false
        5 => array('fe80::/10', '127.0.0.1'),
        // Invalid IP Address, returns false
        6 => array('fe80::/10', 'abc'),
        // Compare to an Array of CIDR Strings
        7 => array(array('::1', '2001:db8::/32'), 'fe80::b091:1117:497a:9dc1'), // returns false
        8 => array(array('::1', '2001:db8::/32'), '2001:db8:0123:4567:89ab:cdef:9876:5432'), // returns true
        // Compare while using an IPv6 Port Number, returns true
        9 => array('2001:db8::/32', '[2001:db8:cafe::17]:4711'),
        // Compare while using an IPv6 Numeric Zone Index
        // Numeric Zone Indexes are common for Windows
        10 => array('fe80::/10', 'fe80::3030:70d9:5af2:cc71%3'), // returns true
        // Compare while using an IPv6 Alpha-numeric Zone Index
        // Alpha-numeric Zone Indexes are common for Unix/Linux Systems
        11 => array('fe80::/10', 'fe80::3%eth0'), // returns true
        // Compre with both Port Number and Zone Index specified for the IPv6 Address
        12 => array('fe80::/10', '[fe80::3030:70d9:5af2:cc71%3]:4712'), // returns true
        // Compare a valid Googlebot IPv6 Address with Google's Allocated IPv6 Address Range
        13 => array('2001:4860::/32', '2001:4860:4801:1303:0:6006:1300:b075'),
        // Test with a valid IPv6 Unique local address. The first two characters are different
        // between CIDR and IP however 'fc00::/7' also covers the IP Range 'fd00::/8' which 
        // is why this test should return true.
        14 => array('fc00::/7', 'fddb:1273:5643::1234'),
    );

    // Run each test and add to an array, in JavaScript this will
    // get converted to an object with child objects for each property
    $results = array();
    foreach ($tests as $key => $value) {
        $results['item' . str_pad($key, 2, '0', STR_PAD_LEFT)] = array(
            'cidr' => $value[0],
            'ip_to_compare' => $value[1],
            'result' => \FastSitePHP\Net\IP::cidr($value[0], $value[1]),
        );
    }
    
    // Return the result in JSON Format
    return $results;
});

// Test the cidr() function for errors related to IPv6 Addresses
$app->get('/cidr-ipv6-errors', function() use ($app) {
    // Add tested errors to an array
    $errors = array();

    // Invalid CIDR String
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('ggg::/64');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid CIDR String, Test 2
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('abc/64');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Invalid CIDR String with an IP
    try {
        \FastSitePHP\Net\IP::cidr('ggg::/64', '::1');
        $errors['test' . count($errors)] = 'Test passed but should have failed';
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Valid IP in CIDR Value but Invalid Bit Range
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('fe80::/-1', '::1');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Valid IP in CIDR Value but Invalid Bit Range
    try {
        $errors['test' . count($errors)] = \FastSitePHP\Net\IP::cidr('fe80::/129', '::1');
    } catch (\Exception $e) {
        $errors['test' . count($errors)] = sprintf('[%s][%s]', get_class($e), $e->getMessage());
    }

    // Return errors as an Object in JSON Format
    return $errors;
});

// Check the result of privateNetworkAddresses() only for IPv4 Addresses
$app->get('/private-network-addresses-ipv4', function() use ($app) {
    // Expected Result
    $expected = array(
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
    );

    // Get Addresses (the first 5 should be matching IPv4 Addresss)
    // Exclude IPv6 Addresses in case the computer does not support them 
    // as this test is only for IPv4 Addresses.
    $cidr_values = \FastSitePHP\Net\IP::privateNetworkAddresses();
    $cidr_values = array_slice($cidr_values, 0, 5);

    // Compare and Return a Text Response
    // NOTE - in PHP Arrays can be compared to see if the are identical using '==='
    $app->header('Content-Type', 'text/plain');
    return ($expected === $cidr_values ? 'Arrays match: ' : 'Arrays do not match: ') . implode(', ', $cidr_values);
});

// Check the result of all values from privateNetworkAddresses(). 
// If IPv6 is not supported on the computer then this unit test will fail.
$app->get('/private-network-addresses-all', function() use ($app) {
    // Expected Result
    $expected = array(
        '127.0.0.0/8',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '169.254.0.0/16',
        '::1/128',
        'fc00::/7',
        'fe80::/10',
    );

    // Get Addresses
    $cidr_values = \FastSitePHP\Net\IP::privateNetworkAddresses();

    // Compare and Return a Text Response
    // NOTE - in PHP Arrays can be compared to see if the are identical using '==='
    $app->header('Content-Type', 'text/plain');
    return ($expected === $cidr_values ? 'Arrays match: ' : 'Arrays do not match: ') . implode(', ', $cidr_values);
});

// Test [$req->clientIp()] using IPv4 Addresses and many differnet tests to test all logic
// and options for the function. The function [clientIp()] would be a security risk if it
// could provide the wrong info. These tests are extensive and well documented to confirm
// that the function is secure and easy to work with. IPv4 testing is separate from 
// IPv6 Testing in case the server does not support IPv6.
$app->get('/client-ip-ipv4', function() use ($app) {
    // --------------------------------------------------------
    // Define Tests
    // None of these tests should cause an execption or error
    // --------------------------------------------------------
    $tests = array(
        // No option specified so return REMOTE_ADDR even though HTTP_X_FORWARDED_FOR is defined
        array(
            'REMOTE_ADDR' => '54.231.1.1',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'expected' => '54.231.1.1',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return REMOTE_ADDR because it is a public address and untrusted
        array(
            'REMOTE_ADDR' => '54.231.1.2',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'option' => 'from proxy',
            'expected' => '54.231.1.2',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_X_FORWARDED_FOR because REMOTE_ADDR is local and trusted
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.3',
            'option' => 'from proxy',
            'expected' => '54.231.1.3',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_CLIENT_IP because REMOTE_ADDR is local and trusted
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_CLIENT_IP' => '54.231.1.4',
            'option' => 'from proxy',
            'expected' => '54.231.1.4',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_FORWARDED because REMOTE_ADDR is local and trusted.
        // HTTP_FORWARDED is RFC 7239 and uses a different format ('for=')
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_FORWARDED' => 'for=54.231.1.5',
            'option' => 'from proxy',
            'expected' => '54.231.1.5',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with HTTP_FORWARDED that contains additional 
        // parameters ('proto=' and 'by='). Returns the Client IP from HTTP_FORWARDED.
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_FORWARDED' => 'for=192.0.2.60;proto=http;by=203.0.113.43',
            'option' => 'from proxy',
            'expected' => '192.0.2.60',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's return 2nd IP from HTTP_X_FORWARDED_FOR 
        // because REMOTE_ADDR and the last proxy are local
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.4, 10.1.1.2',
            'option' => 'from proxy',
            'expected' => '54.231.1.4',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's return 2nd IP from HTTP_FORWARDED 
        // because REMOTE_ADDR and the last proxy are local
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_FORWARDED' => 'for=54.231.1.255, for=10.2.1.2',
            'option' => 'from proxy',
            'expected' => '54.231.1.255',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's but using ';' rather than ',' to 
        // seperate the IP Addresses. This would not be common and considered an invalid IP 
        // as it will not be split. So the entire invalid string value is returned as the IP. 
        // If a server were setup to handle proxy IP's this way it would need custom code to 
        // parse the data or the clientIp() would need to be modified. This item is returned
        // because the first unmatached item is the return value.
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.4; 10.1.1.2',
            'option' => 'from proxy',
            'expected' => '54.231.1.4; 10.1.1.2',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy headers. Even though 
        // multiple headers are included (HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP) 
        // they are an exact match so they are allowed. If they were not an exact
        // match then an 'IP Spoofing attempt' exception would be thrown.
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.5',
            'HTTP_CLIENT_IP' => '54.231.1.5',
            'option' => 'from proxy',
            'expected' => '54.231.1.5',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with all three headers proxy headers defined 
        // using the same IP while REMOTE_ADDR is local so return the proxy address
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.5',
            'HTTP_CLIENT_IP' => '54.231.1.5',
            'HTTP_FORWARDED' => 'for=54.231.1.5',
            'option' => 'from proxy',
            'expected' => '54.231.1.5',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with all three headers proxy headers defined 
        // using the same multiple IP Addresses. REMOTE_ADDR and the last proxy are trusted
        // so return the first proxy address.
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.6, 10.2.1.2',
            'HTTP_CLIENT_IP' => '54.231.1.6, 10.2.1.2',
            'HTTP_FORWARDED' => 'for=54.231.1.6, for=10.2.1.2',
            'option' => 'from proxy',
            'expected' => '54.231.1.6',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with the $trusted_proxies parameter being defined
        // as a single IP address which matches the value from REMOTE_ADDR so the value
        // from HTTP_X_FORWARDED_FOR is returned. Testing with $trusted_proxies 
        // being both a string and an array.
        array(
            'REMOTE_ADDR' => '10.3.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.7',
            'option' => 'from proxy',
            'trusted_proxies' => '10.3.1.1',
            'expected' => '54.231.1.7',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '10.3.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.7',
            'option' => 'from proxy',
            'trusted_proxies' => array('10.3.1.1'),
            'expected' => '54.231.1.7',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with the $trusted_proxies parameter being defined
        // to a value that does not match REMOTE_ADDR so the REMOTE_ADDR is being returned.
        // Testing with $trusted_proxies being both a string and an array.
        array(
            'REMOTE_ADDR' => '10.3.1.2',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.7',
            'option' => 'from proxy',
            'trusted_proxies' => '10.3.1.1',
            'expected' => '10.3.1.2',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '10.3.1.2',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.7',
            'option' => 'from proxy',
            'trusted_proxies' => array('10.3.1.1'),
            'expected' => '10.3.1.2',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's and the last proxy HTTP_X_FORWARDED_FOR
        // address has Port Number specified however it is local so the client's IP (the first proxy) is returned
        array(
            'REMOTE_ADDR' => '10.3.1.3',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.4, 10.3.1.1:8080',
            'option' => 'from proxy',
            'expected' => '54.231.1.4',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with all values trusted so the first proxy address will be returned.
        // This indicates that the user of the site would likely be on an internal network.
        array(
            'REMOTE_ADDR' => '10.3.1.3',
            'HTTP_X_FORWARDED_FOR' => '10.3.1.5, 10.3.1.4',
            'option' => 'from proxy',
            'expected' => '10.3.1.5',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with no proxy headers defined so return REMOTE_ADDR
        array(
            'REMOTE_ADDR' => '10.3.1.4',
            'option' => 'from proxy',
            'expected' => '10.3.1.4',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a local IP Address containing a Port Number being the 
        // requesting URL so the Address is returned with the Port Number
        array(
            'REMOTE_ADDR' => '10.3.1.5',
            'HTTP_X_FORWARDED_FOR' => '10.3.1.6:8080',
            'option' => 'from proxy',
            'expected' => '10.3.1.6:8080',
            'return_type' => 'string',
        ),
        // Using option 'HTTP_X_FORWARDED_FOR' with both HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP 
        // being defined in the request and each value containing a different IP Address. If the 
        // option 'from proxy' were used this would result with an IP Spoofing Exception however 
        // because the specific header is defined as the parameter then that header is returned 
        // without an exception.
        array(
            'REMOTE_ADDR' => '10.3.1.6',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.6',
            'HTTP_CLIENT_IP' => '54.231.1.7',
            'option' => 'HTTP_X_FORWARDED_FOR',
            'expected' => '54.231.1.6',
            'return_type' => 'string',
        ),
        // Using option 'HTTP_X_FORWARDED_FOR' however only the header HTTP_CLIENT_IP 
        // is defined so return the value from REMOTE_ADDR
        array(
            'REMOTE_ADDR' => '10.3.1.7',
            'HTTP_CLIENT_IP' => '54.231.1.7',
            'option' => 'HTTP_X_FORWARDED_FOR',
            'expected' => '10.3.1.7',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a localhost address ('127.0.0.0/8') as the trusted proxy.
        // NOTE - while 127.0.0.1 is the most well-known localhost address any value in 
        // the range '127.0.0.0/8' is also localhost so both 127.0.0.1 and 127.0.0.2 are tested.
        array(
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.8',
            'option' => 'from proxy',
            'expected' => '54.231.1.8',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '127.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.9',
            'option' => 'from proxy',
            'expected' => '54.231.1.9',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a Private Network, RFC1918 24-bit block address ('10.0.0.0/8') as the trusted proxy
        array(
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.10',
            'option' => 'from proxy',
            'expected' => '54.231.1.10',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a Private Network, RFC1918 20-bit block address ('172.16.0.0/12') as the trusted proxy
        array(
            'REMOTE_ADDR' => '172.16.0.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.11',
            'option' => 'from proxy',
            'expected' => '54.231.1.11',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a Private Network, RFC1918 16-bit block address ('192.168.0.0/16') as the trusted proxy
        array(
            'REMOTE_ADDR' => '192.168.0.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.12',
            'option' => 'from proxy',
            'expected' => '54.231.1.12',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a local-link address ('169.254.0.0/16') as the trusted proxy
        array(
            'REMOTE_ADDR' => '169.254.0.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.13',
            'option' => 'from proxy',
            'expected' => '54.231.1.13',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with three values defined in the proxy header (client1, client2, proxy1). 
        // The client1 value represents an attempted attack from the requestor saying they are localhost by 
        // sending 127.0.0.1 however because client2 is the first untrusted proxy it is the return value. 
        // This type of attack can succeed in the event of a misconfigured server or if the programmer were 
        // to simply accept the first client IP. For more on this type of attack see the link in [$app->isLocal()].
        // Online searches shows that many developers create sites where this type of attack would succeed
        // simply because they ready only the first client IP address.
        array(
            'REMOTE_ADDR' => '169.254.0.2',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1, 54.231.1.14, 10.0.0.2',
            'option' => 'from proxy',
            'expected' => '54.231.1.14',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with three values defined in the proxy header (SQL-Injection, client2, proxy1).
        // This is similar to the previous test but replicating a SQL Injection submitted by the client. 
        // Because the middle value client2 is the first untested proxy it will be the return value.
        // The SQL Injection String instead of the first client IP Address is  "' OR '1'='1' --".
        // This is not a common attack however it is known to happen and it's prevented with FastSitePHP.
        array(
            'REMOTE_ADDR' => '169.254.0.3',
            'HTTP_X_FORWARDED_FOR' => '\' OR \'1\'=\'1 --, 54.231.1.15, 10.0.0.3',
            'option' => 'from proxy',
            'expected' => '54.231.1.15',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with five values defined in the proxy header 
        // (client1, client2, client3, proxy1, proxy2). This type of request would be unlikely in a
        // real-world scenario however its good as a unit test.
        array(
            'REMOTE_ADDR' => '169.254.0.4',
            'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 10.0.0.2, 54.231.1.16, 10.0.0.4, 10.0.0.5',
            'option' => 'from proxy',
            'expected' => '54.231.1.16',
            'return_type' => 'string',
        ),
        // The standards based header 'Forwarded' RFC 7239 allows for Obfuscated Identifiers.
        // These values are skipped when using the default parameter [$trusted_proxies = 'trust local'].
        array(
            'REMOTE_ADDR' => '10.4.0.1',
            'HTTP_FORWARDED' => 'for=54.231.1.17, for=_hidden, for=_SEVKISEK',
            'option' => 'from proxy',
            'expected' => '54.231.1.17',
            'return_type' => 'string',
        ),
        // The standards based header 'Forwarded' RFC 7239 allows for Unknown Identifiers.
        // These values are skipped when using the default parameter [$trusted_proxies = 'trust local'].
        array(
            'REMOTE_ADDR' => '10.4.0.2',
            'HTTP_FORWARDED' => 'for=54.231.1.18, for=unknown, for=UNKNOWN',
            'option' => 'from proxy',
            'expected' => '54.231.1.18',
            'return_type' => 'string',
        ),
        // Using header 'Forwarded' RFC 7239 with Obfuscated Identifiers however
        // the parameter $trusted_proxies is not using 'trust local' so the first 
        // Obfuscated Identifier is returned.
        array(
            'REMOTE_ADDR' => '10.4.0.3',
            'HTTP_FORWARDED' => 'for=54.231.1.19, for=_hidden, for=_SEVKISEK',
            'option' => 'HTTP_FORWARDED',
            'trusted_proxies' => '10.0.0.0/8',
            'expected' => '_SEVKISEK',
            'return_type' => 'string',
        ),
        // Checking that parameter names "FOR/For" are case-insensitive for the header 'Forwarded' RFC 7239
        array(
            'REMOTE_ADDR' => '10.4.0.4',
            'HTTP_FORWARDED' => 'FOR=54.231.1.20, FOR=10.4.0.5',
            'option' => 'HTTP_FORWARDED',
            'expected' => '54.231.1.20',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '10.4.0.5',
            'HTTP_FORWARDED' => 'For=54.231.1.21',
            'option' => 'HTTP_FORWARDED',
            'expected' => '54.231.1.21',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a Custom Header for the Client's IP
        // so the header is never read and the value from REMOTE_ADDR is returned.
        array(
            'REMOTE_ADDR' => '10.4.0.6',
            'HTTP_X_REMOTE_IP' => '54.231.1.22',
            'option' => 'from proxy',
            'expected' => '10.4.0.6',
            'return_type' => 'string',
        ),
        // Same as the above test but using the custom header as the option value
        // so the IP Address comes from the custom header.
        array(
            'REMOTE_ADDR' => '10.4.0.7',
            'HTTP_X_REMOTE_IP' => '54.231.1.23',
            'option' => 'HTTP_X_REMOTE_IP',
            'expected' => '54.231.1.23',
            'return_type' => 'string',
        ),
        // Testing with a custom header and multiple IP Adresses in the header (client, proxy).
        // Returns the client IP Address.
        array(
            'REMOTE_ADDR' => '10.4.0.8',
            'HTTP_X_REMOTE_IP' => '54.231.1.24, 10.4.0.9',
            'option' => 'HTTP_X_REMOTE_IP',
            'expected' => '54.231.1.24',
            'return_type' => 'string',
        ),
        // Test with a null REMOTE_ADDR which should always return null. This would likely
        // only happen if other executing code overwrites the value or the server was
        // configured in a way to not provide it.
        array(
            'REMOTE_ADDR' => null,
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        array(
            'REMOTE_ADDR' => null,
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'option' => 'trust local',
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // Testing with a null option specified, returns value from REMOTE_ADDR
        array(
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.2',
            'option' => null,
            'expected' => '127.0.0.1',
            'return_type' => 'string',
        ),
        // Similar to the above test of a missing REMOTE_ADDR, however this version if testing
        // if there is an invalid value in REMOTE_ADDR.
        array(
            'REMOTE_ADDR' => 'abc',
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'option' => 'trust local',
            'expected' => 'abc',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => 123,
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1',
            'option' => 'trust local',
            'expected' => 123,
            'return_type' => 'integer',
        ),
        // Testing with 'from proxy' with $trusted_proxies set to the default in one test
        // which returns the public IP, and in the 2nd test $trusted_proxies is set to null 
        // which then returns the value from REMOTE_ADDR.
        array(
            'REMOTE_ADDR' => '10.4.0.9',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.25',
            'option' => 'from proxy',
            'expected' => '54.231.1.25',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '10.4.0.9',
            'HTTP_X_FORWARDED_FOR' => '54.231.1.25',
            'option' => 'from proxy',
            'trusted_proxies' => null,
            'expected' => '10.4.0.9',
            'return_type' => 'string',
        ),
    );

    // ---------------------------------------------
    // Test for exceptions, each of these tests
    // are expected to thrown an exception.
    // ---------------------------------------------
    $error_tests = array(
        // Error multiple headers
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.0.0',
            'HTTP_CLIENT_IP' => '127.0.0.1',
            'option' => 'from proxy',
            'expected_error' => 'Error calling [FastSitePHP\Web\Request->clientIp()] using the option [from proxy]. This is either an IP Spoofing attempt or two or more proxy servers are used with incompatible IP Request Headers. If more than one proxy header is included with the request then the IP list in each header must match exactly. The following headers/server-variables were set [HTTP_X_FORWARDED_FOR], [HTTP_CLIENT_IP] and the value from [HTTP_X_FORWARDED_FOR] did not match to [HTTP_CLIENT_IP]. If this error is not due to an IP Spoofing attempt check your server configuration or specify only a single server variable to use as the option for this function (for example: HTTP_X_FORWARDED_FOR which represents the header [X-Forwarded-For]).',
        ),
        // Error multiple headers - all three for 'from proxy' option, last one has the error
        array(
            'REMOTE_ADDR' => '10.2.1.1',
            'HTTP_X_FORWARDED_FOR' => '54.231.0.0',
            'HTTP_CLIENT_IP' => '54.231.0.0',
            'HTTP_FORWARDED' => 'for=127.0.0.1',
            'option' => 'from proxy',
            'expected_error' => 'Error calling [FastSitePHP\Web\Request->clientIp()] using the option [from proxy]. This is either an IP Spoofing attempt or two or more proxy servers are used with incompatible IP Request Headers. If more than one proxy header is included with the request then the IP list in each header must match exactly. The following headers/server-variables were set [HTTP_X_FORWARDED_FOR], [HTTP_CLIENT_IP], [HTTP_FORWARDED] and the value from [HTTP_X_FORWARDED_FOR] did not match to [HTTP_FORWARDED]. If this error is not due to an IP Spoofing attempt check your server configuration or specify only a single server variable to use as the option for this function (for example: HTTP_X_FORWARDED_FOR which represents the header [X-Forwarded-For]).',
        ),
        // Using header 'Forwarded' (RFC 7239) with an Obfuscated Identifier and
        // the Obfuscated Identifier in the $trusted_proxies parameter however
        // the $trusted_proxies parameter only supports valid IP Address so this will
        // return an error. Obfuscated Identifiers are allows using using the default
        // 'trust local' option. The actual Obfuscated value is '_.Obfuscated' however
        // this test is using '_.Obfuscated' (contains a dot/period) so that a specific
        // 'IPv4' error message is returned.
        array(
            'REMOTE_ADDR' => '10.2.1.2',
            'HTTP_FORWARDED' => 'for=54.231.0.1, for=_.Obfuscated',
            'option' => 'HTTP_FORWARDED',
            'trusted_proxies' => array('10.0.0.0/8', '_.Obfuscated'),
            'expected_error' => 'The value [_.Obfuscated] is not in valid IPv4 format',
        ),
    );

    // ---------------------------------------------
    // Run all tests and return the resulting text
    // ---------------------------------------------
    return testClientIp('IPv4', $tests, $error_tests);
});

// Just like the above test [/client-ip-ipv4] but for IPv6 Addresss and IPv4/IPv6 Mixed Headers.
// All logic is tested in the IPv4 function and many of these tests are similar (even the comments)
// but testing IPv6 plus some features unique to IPv6 such as Zone Index. IPv6 Tests are seperate
// from IPv4 tests in case a server doens't support IPv6 and needs to work with IPv4.
$app->get('/client-ip-ipv6', function() use ($app) {
    // --------------------------------------------------------
    // Define Tests
    // None of these tests should cause an execption or error
    // To test a public IPv6 Address a valid Googlebot 
    // IPv6 Address [2001:4860:4801:1301:0:6006:1300:b075] and 
    // variations of it are being used.
    // --------------------------------------------------------
    $tests = array(
        // No option specified so return REMOTE_ADDR even though HTTP_X_FORWARDED_FOR is defined
        array(
            'REMOTE_ADDR' => '2001:4860:4801:1301:0:6006:1300:b075',
            'HTTP_X_FORWARDED_FOR' => 'fe80::b091:1117:497a:9dc1',
            'expected' => '2001:4860:4801:1301:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return REMOTE_ADDR because it is a public address and untrusted
        array(
            'REMOTE_ADDR' => '2001:4860:4801:1302:0:6006:1300:b075',
            'HTTP_X_FORWARDED_FOR' => 'fe80::b091:1117:497a:9dc1',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1302:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_X_FORWARDED_FOR because REMOTE_ADDR is local and trusted
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1303:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1303:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_CLIENT_IP because REMOTE_ADDR is local and trusted
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_CLIENT_IP' => '2001:4860:4801:1304:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1304:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' return HTTP_FORWARDED because REMOTE_ADDR is local and trusted.
        // HTTP_FORWARDED is RFC 7239 and uses a different format ('for=')
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc2',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1305:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1305:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with HTTP_FORWARDED that contains additional 
        // parameters ('proto=' and 'by='). Returns the Client IP from HTTP_FORWARDED.
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc3',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1306:0:6006:1300:b075;proto=http;by=fe80::b091:1117:497a:9dc3',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1306:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's return 2nd IP from HTTP_X_FORWARDED_FOR 
        // because REMOTE_ADDR and the last proxy are local
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc4',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1307:0:6006:1300:b075, fe80::b091:1117:497a:9dc5',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1307:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Same as the above test but using local IPv4 Addresses and a public IPv6 Address
        array(
            'REMOTE_ADDR' => '10.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1308:0:6006:1300:b075, 10.1.1.2',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1308:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's return 2nd IP from HTTP_FORWARDED 
        // because REMOTE_ADDR and the last proxy are local
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc5',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1309:0:6006:1300:b075, for=fe80::b091:1117:497a:9dc6',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1309:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Same as the above test but using local IPv4 Addresses and a public IPv6 Address
        array(
            'REMOTE_ADDR' => '10.1.1.2',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1309:0:6006:1300:b075, for=10.1.1.3',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1309:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy headers. Even though 
        // multiple headers are included (HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP) 
        // they are an exact match so they are allowed. If they were not an exact
        // match then an 'IP Spoofing attempt' exception would be thrown.
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1310:0:6006:1300:b075',
            'HTTP_CLIENT_IP' => '2001:4860:4801:1310:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1310:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with multiple proxy IP's and the last proxy HTTP_X_FORWARDED_FOR
        // address has Port Number specified however it is local so the client's IP (the first proxy) is returned
        array(
            'REMOTE_ADDR' => 'fe80::3030:70d9:5af2:cc71',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1311:0:6006:1300:b075, [fe80::3030:70d9:5af2:cc71]:4712',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1311:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Similar to above proxy test but using IPv6 Numeric Zone Index.
        // Numeric Zone Indexes are common for Windows.
        array(
            'REMOTE_ADDR' => 'fe80::3030:70d9:5af2:cc71',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1312:0:6006:1300:b075, fe80::3030:70d9:5af2:cc71%3',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1312:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Similar to above proxy test but using IPv6 Alpha-numeric Zone Index.
        // Alpha-numeric Zone Indexes are common for Unix/Linux Systems.
        array(
            'REMOTE_ADDR' => 'fe80::3',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1313:0:6006:1300:b075, fe80::3%eth0',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1313:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Similar to above tests but using both IPv6 Zone Index and IPv6 Proxy
        array(
            'REMOTE_ADDR' => '[fe80::3030:70d9:5af2:cc71%3]:4712',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1314:0:6006:1300:b075, [fe80::3030:70d9:5af2:cc71%3]:4712',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1314:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with all values trusted so the first proxy address will be returned.
        // This indicates that the user of the site would likely be on an internal network.
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_X_FORWARDED_FOR' => 'fe80::3030:70d9:5af2:cc71, ::1',
            'option' => 'from proxy',
            'expected' => 'fe80::3030:70d9:5af2:cc71',
            'return_type' => 'string',
        ),
        // Using option 'HTTP_X_FORWARDED_FOR' with both HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP 
        // being defined in the request and each value containing a different IP Address. If the 
        // option 'from proxy' were used this would result with an IP Spoofing Exception however 
        // because the specific header is defined as the parameter then that header is returned 
        // without an exception.
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1315:0:6006:1300:b075',
            'HTTP_CLIENT_IP' => '::1',
            'option' => 'HTTP_X_FORWARDED_FOR',
            'expected' => '2001:4860:4801:1315:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a localhost addresses ('::1' and
        // '0000:0000:0000:0000:0000:0000:0000:0001') as the trusted proxies.
        // NOTE - '::1' is the common format as it is omitting leading zeros
        // however the two addresses have the same value.
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1316:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1316:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => '0000:0000:0000:0000:0000:0000:0000:0001',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1317:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1317:0:6006:1300:b075',
            'return_type' => 'string',
        ),        
        // Using option 'from proxy' with a Private Network, IPv6 Unique local address ('fc00::/7') as the trusted proxy.
        // The IPv6 Unique local address 'fc00::/7' also covers the IP Range 'fd00::/8' which is why
        // REMOTE_ADDR starts with 'fddb:'.
        array(
            'REMOTE_ADDR' => 'fddb:1273:5643::1234',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1318:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1318:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with a Private Network, IPv6 local-link address ('fe80::/10') as the trusted proxy
        array(
            'REMOTE_ADDR' => 'fe80::3030:70d9:5af2:cc72',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1319:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1319:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with three values defined in the proxy header (client1, client2, proxy1). 
        // The client1 value represents an attempted attack from the requestor saying they are localhost by 
        // sending '::1' however because client2 is the first untrusted proxy it is the return value. 
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc1',
            'HTTP_X_FORWARDED_FOR' => '::1, 2001:4860:4801:1320:0:6006:1300:b075, fe80::b091:1117:497a:9dc2',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1320:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Using option 'from proxy' with five values defined in the proxy header 
        // (client1, client2, client3, proxy1, proxy2). This type of request would be unlikely in a
        // real-world scenario however its good as a unit test.
        // The first test returns IPv6 and the 2nd test returns IPv4.
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1320:0:6006:1300:b075, 2001:4860:4801:1321:0:6006:1300:b075, 2001:4860:4801:1322:0:6006:1300:b075, fe80::b091:1117:497a:9dc2, fe80::b091:1117:497a:9dc3',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1322:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:9dc1',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1320:0:6006:1300:b075, 2001:4860:4801:1321:0:6006:1300:b075, 54.231.1.1, fe80::b091:1117:497a:9dc2, 10.10.120.12',
            'option' => 'from proxy',
            'expected' => '54.231.1.1',
            'return_type' => 'string',
        ),
        // The standards based header 'Forwarded' RFC 7239 allows for Obfuscated Identifiers.
        // These values are skipped when using the default parameter [$trusted_proxies = 'trust local'].
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc2',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1321:0:6006:1300:b075, for=_hidden, for=_SEVKISEK',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1321:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // The standards based header 'Forwarded' RFC 7239 allows for Unknown Identifiers.
        // These values are skipped when using the default parameter [$trusted_proxies = 'trust local'].
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc3',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1322:0:6006:1300:b075, for=unknown, for=UNKNOWN, for=fe80::b091:1117:497a:0dc2',
            'option' => 'from proxy',
            'expected' => '2001:4860:4801:1322:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        // Checking that parameter names "FOR/For" are case-insensitive for the header 'Forwarded' RFC 7239
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc4',
            'HTTP_FORWARDED' => 'FOR=2001:4860:4801:1323:0:6006:1300:b075, FOR=10.4.0.5',
            'option' => 'HTTP_FORWARDED',
            'expected' => '2001:4860:4801:1323:0:6006:1300:b075',
            'return_type' => 'string',
        ),
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc5',
            'HTTP_FORWARDED' => 'For=2001:4860:4801:1324:0:6006:1300:b075',
            'option' => 'HTTP_FORWARDED',
            'expected' => '2001:4860:4801:1324:0:6006:1300:b075',
            'return_type' => 'string',
        ),
    );

    // ---------------------------------------------
    // Test for exceptions, each of these tests
    // is expected to thrown an exception.
    // ---------------------------------------------
    $error_tests = array(
        // Error multiple headers
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc5',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1324:0:6006:1300:b075',
            'HTTP_CLIENT_IP' => '2001:4860:4801:1325:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected_error' => 'Error calling [FastSitePHP\Web\Request->clientIp()] using the option [from proxy]. This is either an IP Spoofing attempt or two or more proxy servers are used with incompatible IP Request Headers. If more than one proxy header is included with the request then the IP list in each header must match exactly. The following headers/server-variables were set [HTTP_X_FORWARDED_FOR], [HTTP_CLIENT_IP] and the value from [HTTP_X_FORWARDED_FOR] did not match to [HTTP_CLIENT_IP]. If this error is not due to an IP Spoofing attempt check your server configuration or specify only a single server variable to use as the option for this function (for example: HTTP_X_FORWARDED_FOR which represents the header [X-Forwarded-For]).',
        ),
        // Error multiple headers - all three for 'from proxy' option, last one has the error
        array(
            'REMOTE_ADDR' => 'fe80::b091:1117:497a:0dc5',
            'HTTP_X_FORWARDED_FOR' => '2001:4860:4801:1326:0:6006:1300:b075',
            'HTTP_CLIENT_IP' => '2001:4860:4801:1326:0:6006:1300:b075',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1328:0:6006:1300:b075',
            'option' => 'from proxy',
            'expected_error' => 'Error calling [FastSitePHP\Web\Request->clientIp()] using the option [from proxy]. This is either an IP Spoofing attempt or two or more proxy servers are used with incompatible IP Request Headers. If more than one proxy header is included with the request then the IP list in each header must match exactly. The following headers/server-variables were set [HTTP_X_FORWARDED_FOR], [HTTP_CLIENT_IP], [HTTP_FORWARDED] and the value from [HTTP_X_FORWARDED_FOR] did not match to [HTTP_FORWARDED]. If this error is not due to an IP Spoofing attempt check your server configuration or specify only a single server variable to use as the option for this function (for example: HTTP_X_FORWARDED_FOR which represents the header [X-Forwarded-For]).',
        ),
        // Using header 'Forwarded' (RFC 7239) with an Obfuscated Identifier and
        // the Obfuscated Identifier in the $trusted_proxies parameter however
        // the $trusted_proxies parameter only supports valid IP Address so this will
        // return an error. Obfuscated Identifiers are allows using using the default
        // 'trust local' option.
        array(
            'REMOTE_ADDR' => '::1',
            'HTTP_FORWARDED' => 'for=2001:4860:4801:1303:0:6006:1300:b075, for=_Obfuscated',
            'option' => 'HTTP_FORWARDED',
            'trusted_proxies' => array('::1', '_Obfuscated'),
            'expected_error' => 'The value [_Obfuscated] is not in valid IPv6 format',
        ),
    );

    // ---------------------------------------------
    // Run all tests and return the resulting text
    // ---------------------------------------------
    return testClientIp('IPv6', $tests, $error_tests);
});

// Check that the protocol() returns the expected value for a number of different 
// parameter options. Functions clientIp(), protocol(), host(), and port() 
// allow for reading values from a proxy server so they are all tested in a similar manner.
$app->get('/verify-protocol', function() use ($app) {
    // Server Variables to be redefined for each test
    $server_vars = array('REMOTE_ADDR', 'HTTPS', 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_CLIENT_HTTPS');

    // Array of tests
    $tests = array(
        // Nothing set for 'HTTPS' so return 'http'
        array(
            'expected' => 'http',
            'return_type' => 'string',
        ),        
        // 'HTTPS' set to a value other than 'off' return 'https'
        array(
            'HTTPS' => 'on',
            'expected' => 'https',
            'return_type' => 'string',
        ),
        array(
            'HTTPS' => 'https',
            'expected' => 'https',
            'return_type' => 'string',
        ),
        array(
            'HTTPS' => 1,
            'expected' => 'https',
            'return_type' => 'string',
        ),
        // 'HTTPS' set to off or empty, return 'http'
        array(
            'HTTPS' => 'off',
            'expected' => 'http',
            'return_type' => 'string',
        ),
        array(
            'HTTPS' => null,
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Proxy Server Values set but not using proxy option so return 'http'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Return 'https' from Proxy Server using 'from proxy'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'expected' => 'https',
            'return_type' => 'string',
        ),
        // Return 'https' from Proxy Server using 'HTTP_X_FORWARDED_PROTO'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_FORWARDED_PROTO',
            'expected' => 'https',
            'return_type' => 'string',
        ),
        // Return 'https' from Proxy Server using 'HTTP_X_CLIENT_HTTPS'
        array(
            'HTTPS' => 'off',
            'HTTP_X_CLIENT_HTTPS' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_CLIENT_HTTPS',
            'expected' => 'https',
            'return_type' => 'string',
        ),
        // Specifying HTTP_X_CLIENT_HTTPS but using HTTP_X_FORWARDED_PROTO
        // so return 'http'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_CLIENT_HTTPS',
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Specify a different trusted proxy other than the default
        // 'trust local' so return 'http'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.2',
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Using a public ip address for REMOTE_ADDR so return
        // the server value 'http' instead of the proxy value 'https'.
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '54.231.1.25',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.2',
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Same as above but using an array for trusted_proxies
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => array('10.10.0.2'),
            'expected' => 'http',
            'return_type' => 'string',
        ),
        // Return 'http' from Proxy Server because the value
        // is set to 'on' rather than 'https'
        array(
            'HTTPS' => 'off',
            'HTTP_X_FORWARDED_PROTO' => 'on',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'expected' => 'http',
            'return_type' => 'string',
        ),
    );

    // Run Tests calling the protocol() function and comparing the result to the 
    // expected value. Each of these tests should return the expected value
    // and no exceptions should be thrown.
    $req = new \FastSitePHP\Web\Request();
    $test_count = 0;
    foreach ($tests as $test) {
        // Server Variables are always cleared on each test
        foreach ($server_vars as $server_var) {
            if (isset($test[$server_var])) {
                $_SERVER[$server_var] = $test[$server_var];
            } elseif (isset($_SERVER[$server_var])) {
                unset($_SERVER[$server_var]);
            }
        }

        // Call protocol() with different parameters defined 
        // depending upon how each test is defined.
        if (array_key_exists('trusted_proxies', $test)) {
            $value = $req->protocol($test['option'], $test['trusted_proxies']);
        }elseif (array_key_exists('option', $test)) {
            $value = $req->protocol($test['option']);
        } else {
            $value = $req->protocol();
        }

        // Keep count of each test
        $test_count++;

        // First check the return type then the actual value if it matches.
        // If any test fails the function will end showing details of what test failed.
        if (gettype($value) !== $test['return_type']) {
            echo sprintf('Error with Test %d, Type Mismatch, Expected Type: [%s], Return Type: [%s]', $test_count, $test['return_type'], gettype($value));
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        } elseif ($value !== $test['expected']) {
            echo sprintf('Error with Test %d, Incorrect Return Value', $test_count);
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        }
    }

    // All Tests passed if code execution reaches here
    return sprintf('Success for protocol() function, Completed %d Unit Tests', $test_count);
});

// Check that the host() returns the expected value for a number of different 
// parameter options. Functions clientIp(), protocol(), host(), and port() 
// allow for reading values from a proxy server so they are all tested in a similar manner.
$app->get('/verify-host', function() use ($app) {
    // Server Variables to be redefined for each test
    $server_vars = array('REMOTE_ADDR', 'HTTP_HOST', 'HTTP_X_FORWARDED_HOST', 'HTTP_X_CLIENT_HOST');

    // Array of tests
    $tests = array(
        // All values cleared so return null.
        // This should never happen on a real server but
        // is testing what happens if it does.
        array(
            'expected' => null,
            'return_type' => 'NULL',
        ),
        // Proxy option not specified so return host
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'REMOTE_ADDR' => '10.10.0.1',
            'expected' => 'localhost',
            'return_type' => 'string',
        ),
        // Proxy Specified and is safe so return proxy
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'expected' => 'domain.tld',
            'return_type' => 'string',
        ),
        // Return proxy host from a custom header
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'HTTP_X_CLIENT_HOST' => 'domain2.tld',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_CLIENT_HOST',
            'expected' => 'domain2.tld',
            'return_type' => 'string',
        ),
        // Specific proxy trusted other than REMOTE_ADDR
        // so return host value.
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.2',
            'expected' => 'localhost',
            'return_type' => 'string',
        ),
        // Using a public ip address with the default 'trust local'
        // parameter and specified so return the host value
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'REMOTE_ADDR' => '54.231.1.25',
            'option' => 'from proxy',
            'expected' => 'localhost',
            'return_type' => 'string',
        ),
        array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => 'domain.tld',
            'REMOTE_ADDR' => '54.231.1.25',
            'option' => 'from proxy',
            'trusted_proxies' => 'trust local',
            'expected' => 'localhost',
            'return_type' => 'string',
        ),
    );

    // Define patterns for allowed hosts, Values are matched 
    // case-insensitive so case varies on the items in this array.
    $allowed_hosts = array(
        'Domain.tld',
        '*.DOMAIN.tld',
        'Domain.tld:#',
        '*.sub2.*.domain.tld:#',
    );

    // Return value from proxy based on allowed_hosts array
    $matching_proxy_hosts = array(
        'DOMAIN.TLD',
        'Sub.Domain.tld',
        'domain.tld:3000',
        'sub1.sub2.sub3.domain.tld:3000',
    );
    foreach ($matching_proxy_hosts as $matching_proxy_host) {
        $tests[] = array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => $matching_proxy_host,
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => 'trust local',
            'allowed_hosts' => $allowed_hosts,
            'expected' => $matching_proxy_host,
            'return_type' => 'string',
        );
    }

    // Run Tests calling the host() function and comparing the result to the 
    // expected value. Each of these tests should return the expected value
    // and no exceptions should be thrown.
    $req = new \FastSitePHP\Web\Request();
    $test_count = 0;
    foreach ($tests as $test) {
        // Server Variables are always cleared on each test
        foreach ($server_vars as $server_var) {
            if (isset($test[$server_var])) {
                $_SERVER[$server_var] = $test[$server_var];
            } elseif (isset($_SERVER[$server_var])) {
                unset($_SERVER[$server_var]);
            }
        }

        // Call host() with different parameters defined 
        // depending upon how each test is defined.
        if (array_key_exists('allowed_hosts', $test)) {
            $value = $req->host($test['option'], $test['trusted_proxies'], $test['allowed_hosts']);
        } elseif (array_key_exists('trusted_proxies', $test)) {
            $value = $req->host($test['option'], $test['trusted_proxies']);
        } elseif (array_key_exists('option', $test)) {
            $value = $req->host($test['option']);
        } else {
            $value = $req->host();
        }

        // Keep count of each test
        $test_count++;

        // First check the return type then the actual value if it matches.
        // If any test fails the function will end showing details of what test failed.
        if (gettype($value) !== $test['return_type']) {
            echo sprintf('Error with Test %d, Type Mismatch, Expected Type: [%s], Return Type: [%s]', $test_count, $test['return_type'], gettype($value));
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        } elseif ($value !== $test['expected']) {
            echo sprintf('Error with Test %d, Incorrect Return Value', $test_count);
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        }
    }

    // Raise an exception for proxy host values that do not match the allowed_hosts array
    $not_matched_proxy_hosts = array(
        '.domain.tld',
        'mydomain.tld',
        'other.tld',
        'sub1.sub.sub3.domain.tld:3000',
    );

    $error_tests = array();
    foreach ($not_matched_proxy_hosts as $not_matched_proxy_host) {
        $error_tests[] = array(
            'HTTP_HOST' => 'localhost',
            'HTTP_X_FORWARDED_HOST' => $not_matched_proxy_host,
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => 'trust local',
            'allowed_hosts' => $allowed_hosts,
            'expected_error' => 'Proxy host specified in server variable [HTTP_X_FORWARDED_HOST] contains an invalid value of [' . $not_matched_proxy_host . '] when comparing to a list of allowed hosts.',
        );
    }
    // Add one more with a custom header HTTP_X_CLIENT_HOST
    $error_tests[] = array(
        'HTTP_HOST' => 'localhost',
        'HTTP_X_CLIENT_HOST' => 'mydomain.tld',
        'REMOTE_ADDR' => '10.10.0.1',
        'option' => 'HTTP_X_CLIENT_HOST',
        'trusted_proxies' => 'trust local',
        'allowed_hosts' => $allowed_hosts,
        'expected_error' => 'Proxy host specified in server variable [HTTP_X_CLIENT_HOST] contains an invalid value of [mydomain.tld] when comparing to a list of allowed hosts.',
    );

    // Test for exceptions, each of these tests 
    // is expected to thrown an exception.
    $test_error_count = 0;

    foreach ($error_tests as $test) {
        try
        {
            // Increment the Counter before the test as it should error
            $test_error_count++;

            // Reset Server Variables
            foreach ($server_vars as $server_var) {
                if (isset($test[$server_var])) {
                    $_SERVER[$server_var] = $test[$server_var];
                } elseif (isset($_SERVER[$server_var])) {
                    unset($_SERVER[$server_var]);
                }
            }

            // Test
            $value = $req->host($test['option'], $test['trusted_proxies'], $test['allowed_hosts']);
            
            // If the test doesn't error that there is a problem
            echo sprintf('Error with Exception Test %d, The test did not fail but should have thrown an exception.', $test_error_count);
            echo '<br><br>';
            echo '<strong>Value:</strong> ' . $value;
            echo '<br><br>';
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
    return sprintf('Success for host() function, Completed %d Unit Tests and %d Exception Tests', $test_count, $test_error_count);
});

// Check that the host() returns the expected value for a number of different 
// parameter options. Functions clientIp(), protocol(), host(), and port() 
// allow for reading values from a proxy server so they are all tested in a similar manner.
$app->get('/verify-port', function() use ($app) {
    // Server Variables to be redefined for each test
    $server_vars = array('REMOTE_ADDR', 'SERVER_PORT', 'HTTP_X_FORWARDED_PORT', 'HTTP_X_CLIENT_PORT');
        
    // Array of tests
    $tests = array(
        // Set SERVER_PORT several times (no proxy specifed) and call it.
        array(
            'SERVER_PORT' => '80',
            'expected' => 80,
            'return_type' => 'integer',
        ),
        array(
            'SERVER_PORT' => '443',
            'expected' => 443,
            'return_type' => 'integer',
        ),
        // SERVER_PORT not set (unlikely to happen in the real-world).
        // If it does, return zero.
        array(
            'expected' => 0,
            'return_type' => 'integer',
        ),
        // Specify both Server and Proxy value but return Server Value
        // because Proxy Options not specified
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'expected' => 80,
            'return_type' => 'integer',
        ),
        // Return Proxy Value using option 'from proxy'
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'expected' => 443,
            'return_type' => 'integer',
        ),
        // Return Proxy Value using exact header
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_FORWARDED_PORT',
            'expected' => 443,
            'return_type' => 'integer',
        ),
        // Return Proxy Value using a custom header
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'HTTP_X_CLIENT_PORT' => '8080',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'HTTP_X_CLIENT_PORT',
            'expected' => 8080,
            'return_type' => 'integer',
        ),
        // Return Proxy Header using specified trusted IP
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.1',
            'expected' => 443,
            'return_type' => 'integer',
        ),
        // Return Proxy Header using specified trusted IP
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.1',
            'expected' => 443,
            'return_type' => 'integer',
        ),
        // Return Server Value because REMOTE_ADDR is not trusted
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '10.10.0.1',
            'option' => 'from proxy',
            'trusted_proxies' => '10.10.0.2',
            'expected' => 80,
            'return_type' => 'integer',
        ),
        // Using a public ip address with the default 'trust local'
        // parameter and specified so return the server value
        // instead of proxy value
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '54.231.1.25',
            'option' => 'from proxy',
            'expected' => 80,
            'return_type' => 'integer',
        ),
        // Trust a Public IP using CIDR Notation so that 
        // the Proxy Value is returned
        array(
            'SERVER_PORT' => '80',
            'HTTP_X_FORWARDED_PORT' => '443',
            'REMOTE_ADDR' => '54.231.1.25',
            'option' => 'from proxy',
            'trusted_proxies' => '54.231.0.0/17',
            'expected' => 443,
            'return_type' => 'integer',
        ),
    );

    // Run Tests calling the port() function and comparing the result to the 
    // expected value. Each of these tests should return the expected value
    // and no exceptions should be thrown.
    $req = new \FastSitePHP\Web\Request();
    $test_count = 0;
    foreach ($tests as $test) {
        // Server Variables are always cleared on each test
        foreach ($server_vars as $server_var) {
            if (isset($test[$server_var])) {
                $_SERVER[$server_var] = $test[$server_var];
            } elseif (isset($_SERVER[$server_var])) {
                unset($_SERVER[$server_var]);
            }
        }

        // Call port() with different parameters defined 
        // depending upon how each test is defined.
        if (array_key_exists('trusted_proxies', $test)) {
            $value = $req->port($test['option'], $test['trusted_proxies']);
        }elseif (array_key_exists('option', $test)) {
            $value = $req->port($test['option']);
        } else {
            $value = $req->port();
        }

        // Keep count of each test
        $test_count++;

        // First check the return type then the actual value if it matches.
        // If any test fails the function will end showing details of what test failed.
        if (gettype($value) !== $test['return_type']) {
            echo sprintf('Error with Test %d, Type Mismatch, Expected Type: [%s], Return Type: [%s]', $test_count, $test['return_type'], gettype($value));
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        } elseif ($value !== $test['expected']) {
            echo sprintf('Error with Test %d, Incorrect Return Value', $test_count);
            echo '<br><br>';
            echo json_encode($test, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<strong>Expected:</strong>';
            var_dump($test['expected']);
            echo '<br><br>';
            echo '<strong>Value:</strong>';
            var_dump($value);
            exit();
        }
    }

    // All Tests passed if code execution reaches here
    return sprintf('Success for port() function, Completed %d Unit Tests', $test_count);
});

// -----------------------------------------------------------
// Run the application
// -----------------------------------------------------------
$app->run();
