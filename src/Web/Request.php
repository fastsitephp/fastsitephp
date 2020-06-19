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

namespace FastSitePHP\Web;

use FastSitePHP\Net\IP;
use FastSitePHP\Security\Crypto;

/**
 * The Request Class represents an HTTP request and can be
 * used to read content submitted by the browser or client.
 */
class Request
{
    /**
     * Saved contents of the PHP input stream as read from file_get_contents('php://input').
     * Prior to PHP 5.6 the input stream can only be read once.
     *
     * @var string
     */
    private $saved_input_stream = null;

    /**
     * Return a Request QueryString Value using format options from the
     * [value()] function. Returns Null if the QueryString doesn't exist.
     *
     * With native PHP Code Query String values can also be read from the
     * [$_GET] Superglobal array. Example:
     *
     *     $value = (isset($_GET['name']) ? $_GET['name'] : null)
     *
     * @param string $name
     * @param string $format (Optional)
     * @return mixed
     */
    public function queryString($name, $format = 'value?')
    {
        return $this->value($_GET, $name, $format);
    }

    /**
     * Return a Request Form Field Value using format options from the
     * [value()] function. Returns Null if the Form Field doesn't exist.
     *
     * With native PHP Form Field values can also be read from the
     * [$_POST] Superglobal array. Example:
     *
     *     $value = (isset($_POST['name']) ? $_POST['name'] : null)
     *
     * @param string $name
     * @param string $format (Optional)
     * @return mixed
     */
    public function form($name, $format = 'value?')
    {
        return $this->value($_POST, $name, $format);
    }

    /**
     * Return a Request Cookie Value using format options from the
     * [value()] function. Returns Null if the Cookie doesn't exist.
     *
     * With native PHP Code Cookie values can also be read from the
     * [$_COOKIE] Superglobal array. Example:
     *
     *     $value = (isset($_COOKIE['name']) ? $_COOKIE['name'] : null)
     *
     * @param string $name
     * @param string $format (Optional)
     * @return mixed
     */
    public function cookie($name, $format = 'value?')
    {
        return $this->value($_COOKIE, $name, $format);
    }

    /**
     * Use to read secure cookies that were created from [Response->signedCookie()].
     * Returns null if cookie is not set or if it cannot be verified.
     *
     * Using this function requires the Application Config Value 'SIGNING_KEY'.
     *
     * @param string $name - Name of the Signed Cookie
     * @return mixed
     */
    public function verifiedCookie($name)
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        return Crypto::verify($_COOKIE[$name]);
    }

    /**
     * Use to read secure cookies that were created from [Response->jwtCookie()].
     * Returns null if cookie is not set or if it cannot be verified.
     *
     * Using this function requires the Application Config Value 'JWT_KEY'.
     *
     * @param string $name - Name of the JWT Cookie
     * @return mixed
     */
    public function jwtCookie($name)
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        return Crypto::decodeJWT($_COOKIE[$name]);
    }

    /**
     * Use to read secure and secret cookies that were created from [Response->encryptedCookie()].
     * Returns null if cookie is not set or if it cannot be decrypted.
     *
     * Using this function requires the Application Config Value 'ENCRYPTION_KEY'.
     *
     * @param string $name - Name of the Encrypted Cookie
     * @return mixed
     */
    public function decryptedCookie($name)
    {
        if (!isset($_COOKIE[$name])) {
            return null;
        }
        return Crypto::decrypt($_COOKIE[$name]);
    }

    /**
     * Helper function to handle user input or objects where a value may or may not exist. This
     * function is very flexible and depending upon the parameters checks if an array key,
     * object property, or an array of keys/properties exists and returns the value in the
     * desired format. This function is ideal for handling user input from PHP superglobals
     * ($_GET, $_POST, $_COOKIE, $_SESSION) and data from JSON Objects.
     *
     * This function can be used to sanitize (clean) data and return it in a needed format
     * (example zero [0] for an integer instead of an invalid string or error message).
     *
     * Options for the return format are specified in the parameter [$format]. Options that end
     * with a question mark '?' will return either null or the value while options that do
     * not end with a question mark are always converted to the specific data type:
     *
     *     'value?'
     *     Optional value, returns the value as-is or null if not set
     *
     *     'string'
     *     Always return a string type and an empty string if no data.
     *     Whitespace is trimmed (spaces, tabs, new lines, etc).
     *
     *     'string?'
     *     Return string data type or null if not set or
     *     the string is empty. Whitespace is trimmed.
     *
     *     'string with whitespace'
     *     Always return a string and keep any whitespace
     *
     *     'int'
     *     Always return an int data type, if the value was
     *     not set or a valid integer then it will return zero.
     *
     *     'int?'
     *     Return int or null
     *
     *     'float'
     *     Always return an float/double data type, if the value
     *     was not set or a valid float then it will return zero.
     *
     *     'float?'
     *     Return float or null
     *
     *     'bool'
     *     Return a boolean (true or false).
     *     returns true if the value is '1', 'true', 'on', or 'yes'
     *     and false for all other values.
     *
     *     'bool?'
     *     Return a boolean (true|false) or null
     *     Using strict bool validation values so the following rules apply:
     *     returns true if the value is '1', 'true', 'on', or 'yes'
     *     returns false if the value is '0', 'false', 'off', or 'no'
     *     returns null for all other values
     *
     *     'checkbox'
     *     Check the value of an HTML Submitted Form Field Checkbox
     *     and convert it to a database bit value of 1 or 0. HTML
     *     Posted Forms if checked will have the value set to 'on'
     *     otherwise the field name will not be included in the POST.
     *     Specifying $format of 'bool' for a checkbox field will
     *     allow return true/false if that is desired over 1/0.
     *
     *     'email?'
     *     Return a valid email address or null
     *
     *     'url?'
     *     Return a valid url address beginning with 'http://' or 'https://' or null
     *
     * Examples:
     *     $_POST['input1'] = 'test';
     *     $_POST['input2'] = '123.456';
     *     $_POST['checkbox1'] = 'on';
     *     $json = json_decode('{"app":"FastSitePHP","strProp":"abc","numProp":"123","items":[{"name":"item1"},{"name":"item2"}]}');
     *
     *     'test'        = $req->value($_POST, 'input1');
     *     'te'          = $req->value($_POST, 'input1', 'string', 2); // Truncate string to 2 characters
     *     123.456       = $req->value($_POST, 'input2', 'float');
     *     ''            = $req->value($_POST, 'missing', 'string'); // Missing Item
     *     1             = $req->value($_POST, 'checkbox1', 'checkbox');
     *     0             = $req->value($_POST, 'checkbox2', 'checkbox'); // Missing Item
     *     true          = $req->value($_POST, 'checkbox1', 'bool');
     *     'FastSitePHP' = $req->value($json, 'app');
     *     'abc'         = $req->value($json, 'strProp', 'string?');
     *     0             = $req->value($json, 'strProp', 'int'); // Invalid Int
     *     null          = $req->value($json, 'strProp', 'int?'); // Invalid Int
     *     123           = $req->value($json, 'numProp', 'int');
     *     'item1'       = $req->value($json, array('items', 0, 'name'));
     *     'item2'       = $req->value($json, array('items', 1, 'name'));
     *     null          = $req->value($json, array('items', 2, 'name')); // Missing Item
     *
     * @param object|array $data     Object or Array where the key or property will be optionally defined
     * @param string|int|array $key  Key or property to lookup
     * @param string $format         Desired return format, see list of options
     * @param null|int $max_length   Max string length, if larger than string is truncated
     * @return mixed
     * @throws \Exception
     */
    public function value($data, $key, $format = 'value?', $max_length = null)
    {
        // Set Default Values
        $isset = false;
        $value = null;
        $error_type = null;

        // Validate that $key is one of the valid data types: [string|int|array]
        if (!(is_string($key) || is_int($key) || is_array($key))) {
            throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The parameter $key must be defined as either an [array], [string], or [int]; it was instead defined as a [%s] data type.', __CLASS__, __FUNCTION__, gettype($key)));
        }

        // Get the value if set otherwise null.
        // The $key will either be an array which can check complex objects
        // and arrays or a basic type (string or int) which will check for
        // a single array element or object property.
        //
        // The code below uses a feature of PHP named Variable variables ($item->{$prop}).
        // While curly braces are commonly used for String interpolation with PHP
        // using them as Variable variables is not a widely used PHP syntax. It
        // allows the object property to be read dynamically by name at runtime.
        // http://docs.php.net/manual/en/language.variables.variable.php
        if (is_array($key)) {
            // Set a reference to the top-level item to check. This variable
            // will get set to the active property or array item on each loop.
            $item = $data;

            // Check each item in the $key array
            foreach ($key as $prop) {
                if (is_array($item)) {
                    // Array, check Key
                    $isset = isset($item[$prop]);
                    $item = ($isset ? $item[$prop] : null);
                } elseif (is_object($item)) {
                    // Object, check Property
                    $isset = property_exists($item, $prop);
                    $item = ($isset ? $item->{$prop} : null);
                } else {
                    // If the current item not an array or object then break the loop
                    $item = null;
                    break;
                }

                // If the property was not set then break the loop
                if (!$isset) {
                    break;
                }
            }

            // The value to use will be last value that $item was set to
            $value = $item;
        } else {
            // Using a single key value on either an Array or Object
            if (is_array($data)) {
                $isset = isset($data[$key]);
                $value = ($isset ? $data[$key] : null);
            } elseif (is_object($data)) {
                $isset = property_exists($data, $key);
                $value = ($isset ? $data->{$key} : null);
            }
        }

        // Truncate to max length if specified
        if ($max_length !== null && $isset && strlen((string)$value) > $max_length) {
            $value = substr($value, 0, $max_length);
        }

        // Cast or validate and return the specified format
        switch ($format) {
            // Optional value, returns the value as-is or null if not set
            case 'value?':
                return $value;
            // Always return a string type and an empty string if no data.
            // Whitespace is trimmed (spaces, tabs, new lines, etc).
            case 'string':
                $value = ($isset ? trim($value) : '');
                return $value;
            // Return string data type or null if not set or
            // the string is empty. Whitespace is trimmed.
            case 'string?':
                $value = ($isset ? trim($value) : '');
                return ($value === '' ? null : $value);
            // Always return a string and keep any whitespace
            case 'string with whitespace':
                return ($isset ? (string)$value : '');
            // Always return an int data type, if the value was
            // not set or a valid integer then it will return zero.
            case 'int':
                return ($isset && filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : 0);
            // Return int or null
            case 'int?':
                return ($isset && filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : null);
            // Always return an float/double data type, if the value
            // was not set or a valid float then it will return zero.
            case 'float':
                return ($isset && filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : (float)0);
            // Return float or null
            case 'float?':
                return ($isset && filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : null);
            // Return a boolean (true or false).
            // returns true if the value is '1', 'true', 'on', or 'yes'
            // and false for all other values.
            case 'bool':
                return ($isset && filter_var($value, FILTER_VALIDATE_BOOLEAN) === true ? true : false);
            // Return a boolean (true|false) or null
            case 'bool?':
                // Using strict bool validation values so the following rules apply:
                // returns true if the value is '1', 'true', 'on', or 'yes'
                // returns false if the value is '0', 'false', 'off', or 'no'
                // returns null for all other values
                return ($isset ? filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null);
            // Check the value of an HTML Submitted Form Field Checkbox
            // and convert it to a database bit value of 1 or 0. HTML
            // Posted Forms if checked will have the value set to 'on'
            // otherwise the field name will not be included in the POST.
            // Specifying $format of 'bool' for a checkbox field will
            // allow return true/false if that is desired over 1/0.
            case 'checkbox':
                return ($isset && $value === 'on' ? 1 : 0);
            // Return a valid email address or null
            case 'email?':
                $value = ($isset ? filter_var($value, FILTER_VALIDATE_EMAIL) : false);
                return ($value !== false ? $value : null);
            // Return a valid url address beginning with 'http://' or 'https://' or null
            case 'url?':
                // FILTER_VALIDATE_URL accepts many types of URL's as valid such as
                // 'mailto:' so this function also requires 'http/https' at the start.
                $value = ($isset ? filter_var($value, FILTER_VALIDATE_URL) : false);
                return ($value !== false && (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) ? $value : null);
            // Invalid Parameter
            default:
                if (is_string($format)) {
                    throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The parameter $format must be either null or one of the valid options: [value?|string|string?|string with whitespace|int|int?|float|float?|bool|bool?|checkbox|email?|url?]; it was instead defined as [%s].', __CLASS__, __FUNCTION__, $format));
                } else {
                    throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The parameter $format must be either a valid string option or null; it was instead defined as a [%s] data type.', __CLASS__, __FUNCTION__, gettype($format)));
                }
        }
    }

    /**
     * Return the value of a Header Field sent with the HTTP Request. If the key does
     * not exist for then this function will return null. Header values are
     * read directly from the PHP Superglobal $_SERVER Array.
     *
     * Examples:
     *     $content_type = $req->header('Content-Type')
     *     $user_agent = $req->header('User-Agent')
     *
     *     Header Keys are Case-insensitive so the following all return the same value
     *     $value = $req->header('content-type')
     *     $value = $req->header('CONTENT-TYPE')
     *     $value = $req->header('Content-Type')
     *
     * @param string $name
     * @return string|null
     * @throws \Exception
     */
    public function header($name)
    {
        // Validation
        if (!is_string($name)) {
            throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The $name parameter must be defined a string but instead was defined as type [%s].', __CLASS__, __FUNCTION__, gettype($name)));
        } elseif ($name === '') {
            throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The $name parameter defined as an empty string. It must instead be set to a valid header field.', __CLASS__, __FUNCTION__));
        }

        // Convert the name to an upper-case string
        $name_upper_case = strtoupper($name);

        // Check for Request Header
        // 'Content-Type' and 'Content-Length' have to be handled different than
        // other headers. Based on the server version these header fields can come
        // in one of two different ways as superglobal variable $_SERVER.
        // If found without the 'HTTP_' prefix then return the request header value.
        if ($name_upper_case === 'CONTENT-TYPE' && isset($_SERVER['CONTENT_TYPE'])) {
            return $_SERVER['CONTENT_TYPE'];
        } elseif ($name_upper_case === 'CONTENT-LENGTH' && isset($_SERVER['CONTENT_LENGTH'])) {
            return $_SERVER['CONTENT_LENGTH'];
        } else {
            // Convert name to the correct format when reading the superglobal variable
            // $_SERVER. For example 'User-Agent' will be 'HTTP_USER_AGENT'
            $server_field_name = 'HTTP_' . str_replace('-', '_', strtoupper($name_upper_case));

            // If found return the request header value.
            if (isset($_SERVER[$server_field_name])) {
                return $_SERVER[$server_field_name];
            }
        }

        // The 'Authorization' will be removed by default from environement
        // variabes when using Apache for security. Many older servers and sites
        // will make it available to 'REDIRECT_HTTP_AUTHORIZATION' from [.htaccess]
        // by using [RewriteRule]. When using PHP 5.4+ [apache_request_headers()]
        // should always exist and return the value without the need to update [.htaccess].
        if ($name_upper_case === 'AUTHORIZATION') {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                $headers = array_change_key_case($headers, CASE_LOWER);
                if (isset($headers['authorization'])) {
                    return $headers['authorization'];
                }
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        // Request header was not found
        return null;
    }

    /**
     * Return an array of all HTTP Request Headers Fields. Header names will be
     * capitalized so the following names ['Content-type', 'Content-Type',
     * and 'CONTENT-TYPE'] would all be returned by this function as
     * 'Content-Type' for the key in the array.
     *
     * @return array
     * @throws \Exception
     */
    public function headers()
    {
        // The functions [apache_request_headers()] and alias [getallheaders()] are not always
        // available in PHP 5.3; for example when using IIS or Nginx. With PHP 5.4+ it should
        // always work. When not available the server variables are used instead.
        // Even though the function is named [apache_*] it works with all servers.
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            // Make sure the function didn't fail, if it did it will return
            // false rather than an array. If it fails continue code execution
            // in this function to get request headers from the $_SERVER array.
            if ($headers !== false) {
                // When using Apache [getallheaders()] will return certain headers
                // using the upper/lower-case values submitted so if 'Content-type'
                // is submitted from a browser then it will come in that way
                // rather than as 'Content-Type'. On IIS or Nginx the value would
                // always come in as 'Content-Type' which is expected. This is
                // due to a different function called for Apache in the PHP Source
                // Code. This code corrects the header capitalization and creates
                // and returns a new array using the corrected names.
                $req_headers = array();
                foreach ($headers as $key => $value) {
                    // Replace '_' with spaces and capitalize the words then replace
                    // the spaces with dashes '-' resulting in the correct header name.
                    $req_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $key))))] = $value;
                }
                return $req_headers;
            }
        }

        // Read request headers from the server variables which start with 'HTTP_'
        // and are uppercase - for example 'User-Agent' will be given the server
        // variable key 'HTTP_USER_AGENT'. Code execution will only make it here
        // if running PHP Version 5.3 or if the above code fails.
        $req_headers = array();
        foreach ($_SERVER as $name => $value) {
            // Find values starting with 'HTTP_'
            if (substr($name, 0, 5) === 'HTTP_') {
                // Code Below:
                // 1) Remove the 'HTTP_' prefix: [substr($name, 5)]
                // 2) Replace '_' with spaces: [str_replace('_', ' ', ...
                // 3) Capitalize the words: [ucwords(strtolower(...
                // 4) Replace the spaces with dashes '-' resulting in the correct header name.
                $req_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        // 'Content-Type' and 'Content-Length' do not always exist with the 'HTTP_' Prefix
        // so make sure and add them if they exist but are not using the 'HTTP_' prefix.
        if (isset($_SERVER['CONTENT_TYPE']) && !isset($req_headers['Content-Type'])) {
            $req_headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH']) && !isset($req_headers['Content-Length'])) {
            $req_headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        // 'Authorization' Header, see comments in the [header()] function
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && !isset($req_headers['Authorization'])) {
            $req_headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Return the array of Request Headers
        return $req_headers;
    }

    /**
     * Return the Request Method as a string ['GET', 'POST', 'PUT', etc].
     *
     * @return string|null
     */
    public function method()
    {
        return (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null);
    }

    /**
     * Read the specified request header 'Content-Type' and return a text string with a simple value
     * 'json|form|text|xml|form-data' to indicate the type of request content, if unknown then the
     * actual value, or if not set then null. This function can be used to show the return format
     * of the input() function. The difference between 'form' and 'form-data' is 'form' is used to
     * indicate a simple html form posted as 'application/x-www-form-urlencoded' while 'form-data'
     * is for forms with possible files posted as 'multipart/form-data'. If the return type is
     * 'form-data' then the input must be read using superglobal variables $_POST and $_FILES
     * because content() and contentText() use 'php://input' which does not read from multipart forms.
     *
     * @return string|null
     */
    public function contentType()
    {
        // Read Request Header 'Content-Type'. Based on the server version the
        // header can come in two different ways as either 'HTTP_CONTENT_TYPE'
        // or 'CONTENT_TYPE' when reading from the superglobal variable $_SERVER.
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        } elseif (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $content_type = $_SERVER['HTTP_CONTENT_TYPE'];
        } else {
            return null;
        }

        // Convert the value of 'Content-Type' to simple one word value for known types.
        // The value of the content type specifies what the format of the input stream
        // should be in.
        $content_type_lcase = strtolower($content_type);

        if (strpos($content_type_lcase, 'application/json') !== false) {
            $content_type = 'json';
        } elseif (strpos($content_type_lcase, 'application/x-www-form-urlencoded') !== false) {
            $content_type = 'form';
        } elseif (strpos($content_type_lcase, 'xml') !== false) {
            // XML could come in either as 'text/xml' or 'application/xml'
            $content_type = 'xml';
        } elseif (strpos($content_type_lcase, 'text/plain') !== false) {
            $content_type = 'text';
        } elseif (strpos($content_type_lcase, 'multipart/form-data') !== false) {
            $content_type = 'form-data';
        }

        // Return the determined content type
        return $content_type;
    }

    /**
     * Read the request input stream and return the result as as
     * an object, array, text, or null based on the specified
     * content-type. This function is a different from contentText()
     * which always returns the request input as a text string.
     * This would commonly be used to handle posted JSON data or put form
     * values to a web service. The supported return type formats are:
     *     *) 'json' which returns an associative array if text is parsed or
     *        null if invalid json
     *     *) 'form' which returns an associative array of the
     *        parsed form values
     *     *) All other input types are returned as text and it's
     *        up to the app developer to handle them. XML is not handled
     *        by default because there are multiple XML Libraries built
     *        into PHP and it would be up to the app developer to determine
     *        the best one to use plus XML is now becoming a lot less
     *        common and typically being replaced with JSON services.
     *
     * @return mixed
     */
    public function content()
    {
        // If the content type is known the parse the input request text string
        // to the correct type otherwise return the full text string
        switch ($this->contentType()) {
            case 'json':
                return json_decode($this->contentText(), true);
            case 'form':
                parse_str($this->contentText(), $form_data);
                return $form_data;
            default:
                return $this->contentText();
                break;
        }
    }

    /**
     * Read the request input stream and return the result as text.
     * This reads 'php://input' which can only be read once prior to
     * PHP 5.6; this function saves the result and can be read over
     * and over. The return value is always a string regardless of type.
     * This would commonly be used to handle posted JSON data or put form
     * values to a web service. To return request input in the actual
     * format sent from the client use the function input().
     *
     * @return string
     */
    public function contentText()
    {
        if ($this->saved_input_stream === null) {
            $this->saved_input_stream = file_get_contents('php://input');
        }
        return $this->saved_input_stream;
    }

    /**
     * Return a Bearer Token value from the Authorization Request Header. If the
     * header is not set or the token is invalid then null will be returned.
     *
     * Bearer Tokens are commonly used with API’s and Web Services. Token values
     * are defined by the app and can include OAuth 2.0, JSON Web Tokens (JWT),
     * or custom formats.
     *
     * Example Request:
     *     'Authorization: Bearer abc123'
     *
     * This function returns:
     *     'abc123'
     *
     * The web standard (RFC 6750) is focused around OAuth 2.0 however it defines
     * a flexible format for the token value to support various encoded token types:
     *     Bearer {OAuth 2.0}
     *     Bearer {JWT}
     *     Bearer {Hex}
     *     Bearer {Base64}
     *     Bearer {Base64url}
     *
     * @link https://tools.ietf.org/html/rfc6750
     * @return string|null
     */
    public function bearerToken()
    {
        // Regex is based on the defined format from rfc 6750:
        //   b64token    = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
        //   credentials = "Bearer" 1*SP b64token
        $token = $this->header('Authorization');
        if ($token === null || preg_match('/^Bearer [A-Za-z0-9\-\._~\+\/]+=*$/', $token) !== 1) {
            return null;
        }
        return substr($token, 7);
    }

    /**
     * Return true if the request was submitted with the header [X-Requested-With] containing
     * the value [XMLHttpRequest]. This header is sent with jQuery and other popular
     * JavaScript Frameworks when making web service calls.
     *
     * An example of using this function for on a site would be if a Web Form allows for both
     * Form POST's and Web Service Calls to the same URL. For this example the server code
     * could check if the function was submitted as an xhr request and if so return JSON
     * otherwise return HTML.
     *
     * @return bool
     */
    public function isXhr()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    /**
     * Get the value of the 'Origin' Request Header if set or null if not set. This header value
     * is submitted by Web Browsers for Cross-Origin Resource Sharing (CORS) Requests. This
     * function can be used with the cors() function to handle CORS Requests. In JavaScript the
     * origin of a web site can be determined from the property [window.location.origin]. For
     * reference links related to the 'Origin' Header refer to the cors() function.
     *
     * If a page is being viewed directly from the file system [window.location.origin] will
     * show 'file://' and submit a string value of 'null' to the server. The string value of null
     * is handled and returned as null with this function.
     *
     * @return null
     */
    public function origin()
    {
        $origin_is_set = isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== 'null';
        return ($origin_is_set ? $_SERVER['HTTP_ORIGIN'] : null);
    }

    /**
     * Get the value of the 'User-Agent' Request Header or null if not submitted. The 'User-Agent'
     * header is a string that often provides info related to what Browser or HTTP client the user
     * is using and what OS they are on. The header value is commonly spoofed meaning requests will
     * say they are a specific browser and OS when they are in fact something else so user agent
     * values generally cannot be relied upon. However if a site is tracking User Agent strings then
     * it can provide a general overview of who is using the site and what devices or browsers they
     * are using. In JavaScript the User Agent can be determined from the property [navigator.userAgent].
     *
     * @link https://en.wikipedia.org/wiki/User_agent
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
     * @return null
     */
    public function userAgent()
    {
        return (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
    }

    /**
     * Get the value of the 'Referer' Request Header. The Referer Header provides address of a
     * web page or web site that linked to the current request. This value will also be set by
     * search engines (e.g.: Google or Bing) when a user is coming from the search engine.
     * The Referer header is a defined web standard and was originally defined as a misspelled
     * English word so it has been kept as a misspelled word for technical purposes; just like
     * JavaScript this function uses the correctly English Spelling. In JavaScript from a
     * Web Browser this value can be determined from the property [document.referrer].
     *
     * @link https://en.wikipedia.org/wiki/HTTP_referer
     * @return null
     */
    public function referrer()
    {
        return (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null);
    }

    /**
     * Return the IP Address of the remote client (the end-user) that is requesting the
     * webpage or resource. For security if no options are specified this function will
     * return the server variable REMOTE_ADDR. However depending upon the environment,
     * the server variable REMOTE_ADDR might not the IP Address of the remote client
     * but rather the IP Address of the a Proxy Server such as a Load Balancer. For
     * websites that use a proxy server this function provides a number of options to
     * securely read the IP Address of the client. If you do not use a proxy server
     * then call this function without passing any arguments if you need the
     * client's IP address.
     *
     * Reading the remote client IP Address is a possible source of attacks by malicious
     * clients for insecure web frameworks and code. FastSitePHP is designed for security
     * out of the box so using this function with default parameters is secure however
     * if using a proxy server the actual web server and environment must also be properly
     * configured. For an example of this type of attack see comments in the function [isLocal()].
     *
     * The server variable REMOTE_ADDR on most server environments will always contain
     * the IP Address of the connecting client. Generally this value is always safe to read.
     * If a proxy server is used and configured to provide the client's IP Address then it
     * will likely be sent in a Request Header such as 'X-Forwarded-For', 'Client-IP',
     * or 'Forwarded'. These request headers typically use the following format:
     *
     *    X-Forwarded-For: Client1, Client2, Proxy1, Proxy2
     *    (Example): 127.0.0.1, 54.231.1.14, 10.0.0.1, 10.0.0.2
     *
     * In this example only the value Client2 would be safe to read as it is the last
     * "untrusted" IP Address to reach a "trusted" proxy server. The IP Address specified
     * in Client1 could be anything (for example 127.0.0.1 to spoof localhost or a
     * SQL Injection Attack) which is why only the value from Client2 would be valid for
     * IP Reporting or Logging. The terms "untrusted" and "trusted" are commonly used when
     * referring to proxy servers and they mean that an "untrusted" client is one that
     * exists on the public internet while a "trusted" client is a known computer
     * (usually on a private network) that you have control over or trust as
     * providing valid IP info.
     *
     * This function has two parameters [$options] and [$trusted_proxies]:
     *
     *     $options (string or null):
     *         *) Defaults to null which returns REMOTE_ADDR and results in remote
     *            IP Addresses not being checked.
     *         *) 'from proxy' - If specified this will check the following three server variables
     *            [ 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED' ] which maps to
     *            Request Headers [ 'X-Forwarded-For', 'Client-IP', 'Forwarded' ]. Each of the
     *            Headers is checked and the matching header is used to lookup the IP Address.
     *            If multiple headers exist with different IP Addresses then an exception
     *            is raised with this option because it would not be possible for the application
     *            to know which header is correct. The three headers checked are the most
     *            common headers used for specifying the client's IP Address. Headers
     *            'X-Forwarded-For' and 'Client-IP' are non-standard headers but widely used.
     *            The header 'Forwarded' is part of Web Standard RFC 7239 however it is relatively
     *            new (defined in 2014) and not yet widely used.
     *         *) 'HTTP_X_FORWARDED_FOR' or the key of a Server Variable for the Request Header
     *            that contains the Client's IP Address from the Proxy Server. For example if
     *            the client's IP Address is sent in the request header 'X-Forwarded-For' then
     *            the server variable 'HTTP_X_FORWARDED_FOR' will contain the value of the header.
     *            Even though FastSitePHP allows for the simple option 'from proxy', entering the
     *            actual server variable is good practice because it allows the application to
     *            ignore all other headers. In some cases a valid public client can also be behind
     *            a proxy server that uses one of the headers which is different than the header
     *            used by the web server. In this case if the headers are not correctly modified
     *            by the proxy server then this function will raise an exception because
     *            it doesn't know which header value to use.
     *
     *     $trusted_proxies (string or array):
     *         *) This option only applies if the parameter [$option] is not null.
     *         *) 'trust local' - The default value. This uses CIDR Notation string
     *            values returned from the array [$app->privateNetworkAddresses()]
     *            and also allows for Web Standard RFC 7239 Obfuscated and
     *            Unknown Identifiers.
     *         *) Optionally this parameter can be set with a string or an array of
     *            specific IP Addresses or CIDR Notation IP Ranges to trust.
     *         *) If using a proxy server then the default value 'trust local' should
     *            be used for most websites as it is secure and only allows for
     *            IP Addresses that would appear on a private network to be trusted.
     *
     * Examples:
     *     Remote Address and one 'X-Forwarded-For' header on a private network
     *         REMOTE_ADDR = '10.1.1.1'
     *         HTTP_X_FORWARDED_FOR = '54.231.1.4, 10.1.1.2'
     *
     *         '10.1.1.1' = req->clientIp()
     *         Function called without any parameters so the value from REMOTE_ADDR is returned
     *
     *         '54.231.1.4' = req->clientIp('from proxy')
     *         '54.231.1.4' = req->clientIp('from proxy', 'trust local')
     *         '54.231.1.4' = req->clientIp('from proxy', $app->privateNetworkAddresses())
     *         Client IP Address is returned when using 'from proxy' as the function
     *         determines the proxy addresses. 'trust local' is the default option and
     *         it uses an array of CIDR Notation String Values from the function
     *         [$app->privateNetworkAddresses()].
     *
     *         '10.1.1.2' = req->clientIp('from proxy', '10.1.1.1')
     *         Only the IP Address '10.1.1.1' is trusted so '10.1.1.2' is returned
     *
     *         '54.231.1.4' = req->clientIp('HTTP_X_FORWARDED_FOR')
     *         Client IP Address is returned when using the specific server variable as an option
     *
     *     Three Client IP Addresses specified ("' OR '1'='1' --", 127.0.0.1, 54.231.1.5).
     *     The left-most address is an attempted SQL Injection String while the 2nd address
     *     '127.0.0.1' is an attempt to spoof localhost permissions. Only the 3rd Address
     *     '54.231.1.5' is the IP Address that the client actually connected from.
     *         REMOTE_ADDR = '10.0.0.1'
     *         HTTP_X_FORWARDED_FOR = "' OR '1'='1 --, 127.0.0.1, 54.231.1.5"
     *
     *         '10.0.0.1' = req->clientIp()
     *         Function called without any parameters so the value from REMOTE_ADDR is returned
     *
     *         '54.231.1.5' = req->clientIp('from proxy')
     *         The correct Client IP Address is returned and the two left-most values are ignored
     *
     *     The Client Connects from their own proxy '54.231.1.7' and specified the final Client IP
     *     '54.231.1.6' in two Request Headers 'X-Forwarded-For' and 'Client-Ip'. An internal
     *     Proxy Server is configured to only handle 'X-Forwarded-For'.
     *         REMOTE_ADDR = '10.0.0.2'
     *         HTTP_X_FORWARDED_FOR = '54.231.1.6, 54.231.1.7'
     *         HTTP_CLIENT_IP = '54.231.1.6'
     *
     *         req->clientIp('from proxy')
     *         An Exception is thrown because the IP Request Headers are
     *         incompatible and the client cannot be determined.
     *
     *         '54.231.1.7' = req->clientIp('HTTP_X_FORWARDED_FOR')
     *         The correct Client IP is returned because the correct server variable is specified.
     *
     *     Client IP supports both IPv4 and IPv6. In this example an IPv6 Unique local address
     *     ('fc00::/7') is specified as the trusted proxy. In CIDR Notation the address 'fc00::/7'
     *     also covers the IP Range 'fd00::/8' which is why REMOTE_ADDR starts with 'fddb:'.
     *         REMOTE_ADDR = 'fddb:1273:5643::1234'
     *         HTTP_X_FORWARDED_FOR = '2001:4860:4801:1318:0:6006:1300:b075'
     *
     *         '2001:4860:4801:1318:0:6006:1300:b075' = req->clientIp('from proxy')
     *         The correct public IPv6 Address (in this case a Googlebot) is returned
     *
     * @link https://en.wikipedia.org/wiki/X-Forwarded-For
     * @link https://tools.ietf.org/html/rfc7239
     * @link http://docs.aws.amazon.com/ElasticLoadBalancing/latest/DeveloperGuide/x-forwarded-headers.html
     * @link http://httpd.apache.org/docs/2.2/mod/mod_proxy.html#x-headers
     * @param null|string           $option
     * @param array|string|null     $trusted_proxies
     * @return string|null
     * @throws \Exception
     */
    public function clientIp($option = null, $trusted_proxies = 'trust local')
    {
        // If no parameters are specified always return the value from REMOTE_ADDR.
        // Proxy IP Addresses will only be checked if specified as an option.
        // This is done for because proxy addresses can be easily spoofed
        // so it's up to the calling application to request proxy addresses
        // when the the web server is known to be behind a proxy server.
        // If the value of REMOTE_ADDR is null or not defined then it means other
        // code in the application cleared it or there are unexpected server
        // variables from the server so the proxy address can't be trusted
        // and null is also returned.
        $remote_addr = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        if ($option === null || $remote_addr === null) {
            return $this->fixIp($remote_addr);
        }

        // Handle the option 'from proxy' which checks that if the following server variables
        // are defined: 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', or 'HTTP_FORWARDED'.
        // If more than one is defined and the values do not match then an exception is thrown
        // indicating a possible IP spoofing attack or a misconfigured server. The reason for
        // this is that when multiple headers are defined with different IP addresses it means
        // there is no way for the site to know which item is the correct header value unless
        // checking only for that one header. If there are multiple headers it means:
        //
        //   1) The client submitted one of the headers in an attempt to either hack
        //      or hide their identity from the application. A hacking example is if they
        //      send a SQL Injection String or '127.0.0.1' with the header value.
        //   2) There are multiple proxy servers using different headers which if the
        //      proxies are on a local network is an error that needs to be corrected.
        //   3) A proxy server is sending all IP's in different headers to handle different
        //      server applications. This is allowed as long as the IP list in each header
        //      is identical. This is known to happen in cases of certain Chrome plugins
        //      that compress requests and add both 'X-Forwarded-For' and 'Forwarded' headers.
        //
        // This Exception is based on logic from Ruby on Rails Source Code (IpSpoofAttackError):
        //   https://github.com/rails/rails/blob/master/actionpack/lib/action_dispatch/middleware/remote_ip.rb
        if (strtolower($option) === 'from proxy') {
            // Check if each of the common Proxy IP Request Headers were sent
            // with the request and add all submitted headers to an array.
            $proxy_headers = array();
            foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED') as $header) {
                if (isset($_SERVER[$header])) {
                    $proxy_headers[] = $header;
                }
            }
            // If no headers found return the value from REMOTE_ADDR
            if (count($proxy_headers) === 0) {
                return $remote_addr;
            }
        } elseif (!isset($_SERVER[$option])) {
            // If the option is not 'from proxy' then the option must be a server variable.
            // If it is not set then return the value from REMOTE_ADDR
            return $remote_addr;
        } else {
            // Otherwise add the single option to an array
            $proxy_headers = array($option);
        }

        // Parse the Client/Proxy IP Addresses from the matched Header Field(s).
        $proxy_ips = array();
        foreach ($proxy_headers as $proxy_header) {
            // The Web Standard RFC 7239 'Forwarded' Header was defined in June of 2014 and at the time
            // of writing (early 2016) is not commonly used by popular proxy servers. Most common is
            // the non-standard but widely used header 'X-Forwarded-For'. Non-standard headers use a
            // simple comma-delimited format while the 'Forwarded' provides a lot of different options.
            // Examples:
            //   [Forwarded]: 'for=192.0.2.43, for="[2001:db8:cafe::17]", for=unknown;proto=http;by=203.0.113.43'
            //   [X-Forwarded-For]: 'client-ip, proxy1, proxy2'
            if ($proxy_header === 'HTTP_FORWARDED') {
                // Parse the 'Forwarded' header with a regular expression. Below are
                // some helpful links for working with regular expressions and the
                // rules for this specific regular expression are described in detail.
                //
                // https://en.wikipedia.org/wiki/Regular_expression
                // http://php.net/manual/en/regexp.reference.meta.php
                // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
                // https://msdn.microsoft.com/en-us/library/az24scfc(v=vs.110).aspx
                //
                //  /               # Start of regular expression using forward slash [/] delimiter
                //      for=        # Search for the text [for=]
                //      (?:         # Start a subexpression [(] search but do not keep the results [?:]
                //      "?)         # Look for an optional ["] Character
                //      (.*?)       # Anything (zero or more characters) after the previous subexpression and before the next
                //      (?:         # Start a subexpression [(] search but do not keep the results [?:]
                //          ;|,|"   # Look for one of the three characters [;] [,] ["] or ...
                //      $)          # match to the end of input [$]
                //  /i              # End of regular expression [/] and specify case-insensitive search [i]

                // NOTE - for the below code the same regular expression and matching in JavaScript would be:
                //   var re = /for=(?:"?)(.*?)(?:;|,|"|$)/gi;
                //   var results, ips = [], header = '{{text of HTTP_FORWARDED}}';
                //   while ((results = re.exec(header)) !== null) { ips.push(results[1]); }

                preg_match_all('/for=(?:"?)(.*?)(?:;|,|"|$)/i', $_SERVER[$proxy_header], $matches);
                $proxy_ips[] = $matches[1];
            } else {
                // Simply split the string into an array and trim the values
                $proxy_ips[] = array_map('trim', explode(',', $_SERVER[$proxy_header]));
            }
        }

        // Validation - If there was more than one proxy IP header defined then compare
        // them and make sure they all match exactly. If not throw an Exception.
        if (count($proxy_ips) > 1) {
            // Skip the first item in the array as it is used to compare
            for ($n = 1, $m = count($proxy_ips); $n < $m; $n++) {
                // Compare the first IP Array List to the current IP Array List in the loop.
                // Note - PHP supports Array Operators and allows for Arrays
                // to be compared based on key/value.
                if ($proxy_ips[0] !== $proxy_ips[$n]) {
                    throw new \Exception(sprintf('Error calling [%s->%s()] using the option [%s]. This is either an IP Spoofing attempt or two or more proxy servers are used with incompatible IP Request Headers. If more than one proxy header is included with the request then the IP list in each header must match exactly. The following headers/server-variables were set [%s] and the value from [%s] did not match to [%s]. If this error is not due to an IP Spoofing attempt check your server configuration or specify only a single server variable to use as the option for this function (for example: HTTP_X_FORWARDED_FOR which represents the header [X-Forwarded-For]).', __CLASS__, __FUNCTION__, $option, implode('], [', $proxy_headers), $proxy_headers[0], $proxy_headers[$n]));
                }
            }
        }

        // There was only one header or all headers
        // matched so get the first header list.
        $ips = $proxy_ips[0];

        // Add the value from REMOTE_ADDR to the end of the array
        $ips[] = $remote_addr;

        // If the option $trusted_proxies is set to 'trust local' then use
        // CIDR Notation Values for IP Address Ranges that would only come
        // from a local network computer or device and not the public Internet.
        $trust_local = (is_string($trusted_proxies) && strtolower($trusted_proxies) === 'trust local');
        if ($trust_local) {
            $trusted_proxies = IP::privateNetworkAddresses();
        }

        // Loop backwards from the right-most address to the left-most address. Check each
        // address and if it is trusted then move to the next item. When the first untrusted
        // item is found then return it as this will be the Client's IP Address. Anything before
        // this address would be generated by the client and may or may not be a real IP.
        // Example:
        //   Array   = client1, client2, proxy1, proxy2, remote_addr
        //   Loop    = remote_addr, proxy2, proxy1, client2
        //   Returns = client2
        for ($n = count($ips) - 1; $n >= 0; $n--) {
            // If there are no trusted proxy addresses then return the first item
            // or if the array reaches the last IP Address then return it. If
            // this happens then the user of the site or app would likely be a
            // user on an internal private network or that a proxy server is
            // misconfigured and not adding the client's IP.
            if ($trusted_proxies === null || $n === 0) {
                return $ips[$n];
            } else {
                // The Web Standard RFC 7239 which defined the header 'Forwarded' provides
                // rules for Obfuscated and Unknown Identifiers. If 'trust local' was set
                // then ignore these items.
                if ($trust_local && (substr($ips[$n], 0, 1) === '_' || strtolower($ips[$n]) === 'unknown')) {
                    continue;
                }

                // Is the current IP address a trusted proxy? If not then
                // the Client's Last Known IP Address was found so return it.
                // This statement is comparing the current IP Address to the
                // array of trusted proxy strings to see if the IP Address
                // matches or if it is on the same network. Trusted proxy strings
                // use CIDR Notation so the function cidr() is used to compare
                // the network values of each IP Address.
                if (IP::cidr($trusted_proxies, $ips[$n]) !== true) {
                    return $ips[$n];
                }
            }
        }

        // If code execution reaches this point then it means that someone changed the
        // original code because this function is designed to always return a value.
        // Provide an error describing that the code has been modified. Because this
        // line cannot be reached in the current state this error is not unit tested.
        throw new \Exception(sprintf('Error, no value has been returned by the function [%s->%s()]. This is due to a change in the local copy of this server\'s code.', __CLASS__, __FUNCTION__));
    }

    /**
     * Return the protocol (a string value of either 'http' or 'https') that was used to make
     * the request. If the web server is behind a proxy server (for example a load balancer)
     * then optional parameters allow for the protocol to be safely read from a proxy request header.
     * If not using a proxy server then functions rootDir() and rootUrl() can be used to easily obtain
     * needed URLs for the hosted site instead of using this function.
     *
     * For reading proxy headers functions clientIp(), protocol(), host(), and port() all share
     * similar parameters; refer to clientIp() documentation for detailed comments on the options.
     * For protocol() if the [option] parameter is set to 'from proxy' then the value is read from the
     * request header 'X-Forwarded-Proto' (server variable X_FORWARDED_PROTO). To use a different proxy
     * request header use the corresponding server variable name as the [option] parameter. If using a
     * proxy header variable the value from the proxy header should be either 'http' or 'https'.
     *
     * @param null|string           $option
     * @param array|string|null     $trusted_proxies
     * @return string|null
     */
    public function protocol($option = null, $trusted_proxies = 'trust local')
    {
        // If the client that connected to the server used HTTPS then
        // the server variable HTTPS will likely be set with a value
        // other than off.
        $is_secure = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
        $proto = ($is_secure ? 'https' : 'http');

        // If not checking for a proxy value then return
        // the value used for the connection.
        if ($option === null) {
            return $proto;
        }

        // Read the Proxy Header Value and if not trusted
        // or found return the default Server Protocal.
        $key = ($option === 'from proxy' ? 'HTTP_X_FORWARDED_PROTO' : $option);
        $proxy_proto = $this->proxyHeader($key, $trusted_proxies);
        if ($proxy_proto === null) {
            return $proto;
        }

        // Proxy Protocal is considered valid and trusted
        return ($proxy_proto === 'https' ? 'https' : 'http');
    }

    /**
     * Return the host value (domain name) for the request. If the host value contains a port number
     * (for example 'site:8080' then it will be included with the host in return value). If the
     * web server is behind a proxy server (for example a load balancer) then optional parameters
     * allow for the host to be safely read from a proxy request header. If not using a proxy server
     * then functions rootDir() and rootUrl() can be used to easily obtain needed URLs for the
     * hosted site instead of using this function.
     *
     * For reading proxy headers functions clientIp(), protocol(), host(), and port() all share
     * similar parameters; refer to clientIp() documentation for detailed comments on the options.
     * For host() if the [option] parameter is set to 'from proxy' then the value is read from the
     * request header 'X-Forwarded-Host' (server variable X_FORWARDED_HOST). To use a different
     * proxy request header use the corresponding server variable name as the [option] parameter.
     *
     * For proxy server values an optional array of allowed hosts can be defined for validation
     * using the [allowed_host] parameter. If the array is defined and the proxy host does not match
     * then an exception is thrown. This can help prevent attacks when using a proxy server that
     * specifies a different domain from the actual web server. Values in the array are matched
     * to the host based on an exact match (case-insensitive) or can also be matched using one of two
     * wildcard card characters: [*] which matches to one or more of any character and [#] which
     * matches to a numeric value of digits.
     *
     * [$allowed_hosts] Examples:
     *     'domain.tld'   - matches [domain.tld] and [DOMAIN.TLD] but not [www.domain.tld]
     *     '*.domain.tld' - matches [sub.domain.tld] and [sub. sub2.domain.tld] but not [domain.tld]
     *     'Domain.tld:#' - matches [domain.tld:8080]
     *
     * @param null|string           $option
     * @param array|string|null     $trusted_proxies
     * @param array|null            $allowed_hosts
     * @return string|null
     * @throws \Exception
     */
    public function host($option = null, $trusted_proxies = 'trust local', array $allowed_hosts = null)
    {
        // Get the Server Host and if not checking for a proxy value then return it.
        $host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
        if ($option === null || $host === null) {
            return $host;
        }

        // Read the Proxy Header Value and if not trusted
        // or found return the default Server Host.
        $key = ($option === 'from proxy' ? 'HTTP_X_FORWARDED_HOST' : $option);
        $proxy_host = $this->proxyHeader($key, $trusted_proxies);
        if ($proxy_host === null) {
            return $host;
        }

        // If an array of allowed hosts is defined for validation
        // then compare with the proxy host value.
        if ($allowed_hosts !== null) {
            $is_valid = false;
            foreach ($allowed_hosts as $allowed_host) {
                // If the allowed host value contains either '*' or '#' then build
                // and perform a regular expression comparison.
                if (stripos($allowed_host, '*') !== false || stripos($allowed_host, '#') !== false) {
                    // Convert the simple wildcard expression to a regular expression.
                    // Example:
                    //   '*.domain.tld:#'
                    // To:
                    //   '/^(.+)\.domain\.tld\:(\d+)$/i'
                    //
                    // How the expression is built:
                    // 1) Add backslash quotes to the existing expression
                    //    Example: '.' becomes '\.' and '*' becomes '\*'
                    // 2) Require one or more of any character for '*'
                    //    '\*' becomes '(.+)
                    // 3) Require numbers for '#'
                    //    '#' becomes '(\d+)'
                    // 4) Match from start of input '^' to the end of input '$'
                    //    and specify case-insensitive modifier 'i'
                    $pattern = preg_quote($allowed_host);
                    $pattern = str_replace('\\#', '#', $pattern); // For PHP 7.3 +
                    $pattern = str_replace('\*', '(.+)', $pattern);
                    $pattern = str_replace('#', '(\d+)', $pattern);
                    $pattern = '/^' . $pattern . '$/i';
                    $is_valid = (preg_match($pattern, $proxy_host) === 1);
                } else {
                    // Must be an exact match (case-insensitive)
                    $is_valid = (strtolower($proxy_host) === strtolower($allowed_host));
                }

                // Break loop once a match is found
                if ($is_valid) {
                    break;
                }
            }

            // Is the host allowed?
            if (!$is_valid) {
                throw new \Exception(sprintf('Proxy host specified in server variable [%s] contains an invalid value of [%s] when comparing to a list of allowed hosts.', $key, $proxy_host));
            }
        }

        // Proxy Host is considered valid and trusted
        return $proxy_host;
    }

    /**
     * Return the port number for the request. In most cases the end user would connect to a
     * server using port 80 for HTTP and port 443 for secure HTTPS requests. Other port numbers
     * may be used in development or on server environments. If the web server is behind a
     * proxy server (for example a load balancer) then optional parameters allow for the
     * port number to be safely read from a proxy request header. If not using a proxy server
     * then functions rootDir() and rootUrl() can be used to easily obtain needed URLs for the
     * hosted site instead of using this function.
     *
     * For reading proxy headers functions clientIp(), protocol(), host(), and port() all share
     * similar parameters; refer to clientIp() documentation for detailed comments on the options.
     * For port() if the [option] parameter is set to 'from proxy' then the value is read from the
     * request header 'X-Forwarded-Port' (server variable X_FORWARDED_PORT). To use a different
     * proxy request header use the corresponding server variable name as the [option] parameter.
     *
     * @param null|string           $option
     * @param array|string|null     $trusted_proxies
     * @return int
     */
    public function port($option = null, $trusted_proxies = 'trust local')
    {
        // Get the Server Port and if not checking
        // for a proxy value then return it.
        $port = (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null);
        if ($option === null || $port === null) {
            return (int)$port;
        }

        // Read the Proxy Header Value, if trusted then return the value
        // from the proxy variable otherwise return the default server port.
        $key = ($option === 'from proxy' ? 'HTTP_X_FORWARDED_PORT' : $option);
        $proxy_port = $this->proxyHeader($key, $trusted_proxies);
        return (int)($proxy_port === null ? $port : $proxy_port);
    }

    /**
     * Used by protocol(), host(), and port() to compare the value of REMOTE_ADDR
     * with any array or string of trusted proxy IP Addresses (for example a local
     * network value of 10.0.0.1). If REMOTE_ADDR then they value of a variable
     * defiend from $_SERVER will be read. If the value from REMOTE_ADDR is not
     * trusted then it means the client could have defined the header value and it
     * is not considered safe to read and then this function will return null.
     *
     * @param  string  $server_variable
     * @param  string|array  $trusted_proxies
     * @return string|null
     */
    private function proxyHeader($server_variable, $trusted_proxies)
    {
        // If the option $trusted_proxies is set to 'trust local' then use
        // CIDR Notation Values for IP Address Ranges that would only come
        // from a local network computer or device and not the public Internet.
        if (is_string($trusted_proxies) && strtolower($trusted_proxies) === 'trust local') {
            $trusted_proxies = IP::privateNetworkAddresses();
        }

        // Get the IP Address from REMOTE_ADDR (this should be socket address of
        // the computer or device that made the connection).
        $remote_addr = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        if ($remote_addr === null) {
            return null;
        }

        // Validate that REMOTE_ADDR matches the trusted proxies
        if (IP::cidr($trusted_proxies, $remote_addr) !== true) {
            return null;
        }

        // Return the Server Variable if it exists because it
        // is now considered to be trusted and safe to read
        return (isset($_SERVER[$server_variable]) ? $_SERVER[$server_variable] : null);
    }

    /**
     * Return the IP Address that the Web Server is running from. When running on localhost or using
     * PHP's Built-in Development Web Server this function will likely return '127.0.0.1' (IPv4)
     * or '::1' (IPv6) and if running a PHP Script from the command line without a web server then
     * this function will likely return null. For default Apache installations this function will
     * get the IP Address from the server variable SERVER_ADDR and for default IIS installations
     * this function will get the IP Address from the server variable LOCAL_ADDR. To get the Network
     * IP Address of the Computer see the function [FastSitePHP\Net\Config->networkIp()].
     *
     * @return string|null
     */
    public function serverIp()
    {
        // Apache
        if (isset($_SERVER['SERVER_ADDR'])) {
            return $this->fixIp($_SERVER['SERVER_ADDR']);
        // IIS
        } elseif (isset($_SERVER['LOCAL_ADDR'])) {
            return $this->fixIp($_SERVER['LOCAL_ADDR']);
        // PHP Built-in Development Web Server, for more see:
        // http://php.net/manual/en/features.commandline.webserver.php
        } elseif (php_sapi_name() === 'cli-server' && isset($_SERVER['REMOTE_ADDR'])) {
            return $this->fixIp($_SERVER['REMOTE_ADDR']);
        // IP could not be determined (likely command line without a web server)
        } else {
            return null;
        }
    }

    /**
     * Fix an invalid IPv6 localhost Address. The format is partically valid, for
     * example '[2001:db8:cafe::17]:4711' is an IPv6 that uses a port, however port
     * is not specified on this and it fails validation with PHP [filter_var()].
     *
     * This was first seen in PHP 7.3 using Windows 64-Bit PHP Built-In Server.
     *
     * @param string|null $ip
     * @return string|null
     */
    private function fixIp($ip)
    {
        return ($ip === '[::1]' ? '::1' : $ip);
    }

    /**
     * Return true if the request is running from localhost '127.0.0.1' (IPv4)
     * or '::1' (IPv6) and if the web server software is also running on localhost.
     * This function can be used to show or hide specific site features for developers
     * or administrators. This function would likely always be safe to call as it does
     * not use IP Addresses from a proxy server however it is possible that a misconfigured
     * sever or server code that overwrites server variables could provide incorrect info.
     * If using this function make sure to test the site in various environments to see that
     * it behaves as expected. The reference link provides an example of how a misconfigured
     * server can cause errors with server software thinking its running in localhost when
     * it's not. In regards to the reference link this function would not have failed
     * as it's checking both Client and Server IP Addresses.
     *
     * @link http://blog.ircmaxell.com/2012/11/anatomy-of-attack-how-i-hacked.html
     * @return bool
     */
    public function isLocal()
    {
        $client_ip = $this->clientIp();
        $server_ip = $this->serverIp();

        return (
            ($client_ip === '127.0.0.1' || $client_ip === '::1')
            && ($server_ip === '127.0.0.1' || $server_ip === '::1')
        );
    }

    /**
     * Parse the 'Accept' Request Header into an array or if an optional parameter is
     * specified then check if the 'Accept' Header contains the specified MIME Type and
     * return true or false. See also comments for the function [acceptLanguage()]
     * because all [accept*()] functions have similar behavior.
     *
     * @param null|string $mime_type
     * @return array|bool
     */
    public function accept($mime_type = null)
    {
        return $this->acceptHeader('HTTP_ACCEPT', $mime_type);
    }

    /**
     * Parse the 'Accept-Charset' Request Header into an array or if an optional parameter is
     * specified then check if the 'Accept-Charset' Header contains the specified Character
     * Encoding and return true or false. See also comments for the function [acceptLanguage()]
     * because all [accept*()] functions have similar behavior.
     *
     * NOTE - this header is no longer commonly used and for web browsers and it is safe for
     * servers to assume that UTF-8 is the accepted character encoding method.
     *
     * @param null|string $character_encoding
     * @return array|bool
     */
    public function acceptCharset($character_encoding = null)
    {
        return $this->acceptHeader('HTTP_ACCEPT_CHARSET', $character_encoding);
    }

    /**
     * Parse the 'Accept-Encoding' Request Header into an array or if an optional parameter is
     * specified then check if the 'Accept-Encoding' Header contains the specified Content
     * Encoding and return true or false. See also comments for the function [acceptLanguage()]
     * because all [accept*()] functions have similar behavior.
     *
     * @param null|string $content_encoding
     * @return array|bool
     */
    public function acceptEncoding($content_encoding = null)
    {
        return $this->acceptHeader('HTTP_ACCEPT_ENCODING', $content_encoding);
    }

    /**
     * For HTTP there are several standard 'Accept*' Request Headers that can be used for
     * content negotiation by a web server to determine how to respond.
     *
     * Parse the 'Accept-Language' Request Header into an array or if an optional parameter is
     * specified then check if the 'Accept-Language' Header contains the specified Language
     * and return true or false.
     *
     * Example:
     *     'Accept-Language' Header Value = 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
     *
     *     acceptLanguage():
     *     returns array(
     *         array('value' => 'ru-RU', 'quality' => null),
     *         array('value' => 'ru',    'quality' => 0.8),
     *         array('value' => 'en-US', 'quality' => 0.6),
     *         array('value' => 'en',    'quality' => 0.4),
     *     );
     *
     *     acceptLanguage('en'): true
     *     acceptLanguage('de'): false
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
     * @link https://www.w3.org/International/questions/qa-accept-lang-locales
     * @param null|string $language
     * @return array|bool
     */
    public function acceptLanguage($language = null)
    {
        return $this->acceptHeader('HTTP_ACCEPT_LANGUAGE', $language);
    }

    /**
     * This function gets called by each of the public [accept*()] functions and will return an
     * array structure based on the header value if no parameters are passed to the function or
     * true or false if a search parameter was specified. If the Request Header is not defined
     * and there is no search parameter specified then an empty array will be returned.
     *
     * @param string $key
     * @param null|string $search
     * @return array|bool
     */
    private function acceptHeader($key, $search = null)
    {
        // Is the header value set? If not and there was no search parameter
        // then return an empty array as there is no data otherwise return false.
        if (!isset($_SERVER[$key])) {
            if ($search === null) {
                return array();
            } else {
                return false;
            }
        }

        // [Accept*] headers use a simple comma-delimited string value for the format.
        // Parse into an array and trim each value.
        $items = array_map('trim', explode(',', $_SERVER[$key]));
        $accept = array();

        // Loop through each item found in the header
        foreach ($items as $item) {
            // Find the quality value for the item if it is set
            $quality = null;
            if (($pos = strpos($item, ';q=')) !== false) {
                $quality = (float)substr($item, $pos + 3);
                $item = substr($item, 0, $pos);
            }

            // Did the calling application try to search? If so and the item matches
            // then return true, otherwise if no search was requested in the parameters
            // then add the item to the array.
            if ($search === $item) {
                return true;
            } else {
                $accept[] = array(
                    'value' => $item,
                    'quality' => $quality,
                );
            }
        }

        // Was there a search? If yes then no matching items were found so return
        // false otherwise if no search then return the array of all parsed items.
        if ($search !== null) {
            return false;
        } else {
            return $accept;
        }
    }
}
