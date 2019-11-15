<?php
/**
 * Copyright 2019 Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Web;

use FastSitePHP\Application;
use FastSitePHP\Security\Crypto;

/**
 * The Response Class represents an HTTP response
 * and can be used to build and send the response.
 */
class Response
{
    /**
     * HTTP Response Status Code
     *
     * @var string|null
     */
    private $status_code = null;

    /**
     * HTTP Response Headers Fields
     *
     * @var array
     */
    private $header_fields = array();

    /**
     * Cookies that will be sent with the response
     *
     * @var array
     */
    private $response_cookies = array();

    /**
     * Type of ETag, if defined it gets set from the etag() function and if set will be either 'strong' or 'weak'
     *
     * @var string|null
     */
    private $etag_type = null;

    /**
     * Query String Parameters to lookup for a JSONP Request (JSON with Padding).
     * If defined this gets set from the contentType() or jsonpQueryString() functions.
     *
     * @var string|array|null
     */
    private $jsonp_query_string = null;

    /**
     * If the function [file()] is called with a valid file then this property
     * will contain the full file path. If set the file will be streamed and sent
     * to the client as the response's content in an memory efficient manner
     * when [send()] if called.
     *
     * @var string|null
     */
    private $response_file = null;

    /**
     * Content that will be sent with the response when [send()] is called.
     * This is set from the function [content()].
     *
     * @var mixed|null
     */
    private $response_content = null;

    /**
     * Specify options for [json_encode()] when a JSON Response is returned.
     * Defaults to [JSON_UNESCAPED_UNICODE] if using PHP 5.4+
     * 
     * @var int
     */
    private $json_options = 0;
    
    /**
     * Class Constructor
     * 
     * The FastSitePHP Application can be passed as an optional parameter and when 
     * used the Status Code and any Response Headers defined from the Application
     * Object will be assigned to the Response Object.
     * 
     * @param Application|null $app
     */
    function __construct(Application $app = null)
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->json_options = JSON_UNESCAPED_UNICODE;
        }

        if ($app !== null) {
            // Set Status Code
            $status_code = $app->statusCode();
            if ($status_code !== null) {
                $this->statusCode($status_code);
            }

            // CORS Headers
            $headers = $app->cors();
            if ($headers !== null) {
                foreach ($headers as $key => $value) {
                    $this->header($key, $value);
                }
            }

            // No-Cache Headers
            $no_cache = $app->noCache(null);
            if ($no_cache === true) {
                $this->noCache();
            }

            // Set any other Response Headers
            $headers = $app->headers();
            foreach ($headers as $key => $value) {
                $this->header($key, $value);
            }

            // Set Cookies
            $this->response_cookies = $app->cookies();

            // JSON Options
            if ($app->json_options !== 0) {
                $this->json_options = $app->json_options;
            }
        }
    }

    /**
     * Define an HTTP Header to be sent with the Response. Additionally previously
     * defined Header fields can be read and cleared using this function. To set a
     * Header field specify both $name and $value parameters. To read the value of
     * a Header field specify only the $name parameter; if the value has been defined
     * it will be returned otherwise if it has not been defined then null will be
     * returned. To clear a Header field pass an empty string '' for the $value
     * parameter. If setting or clearing a Header field then the Response Object
     * will be returned so it can be called as a chainable method.
     * 
     * The Class [\FastSitePHP\Application] also has this function defined. 
     * The difference is that Application version is used for basic responses
     * and headers are not validated.
     *
     * Examples:
     *     Set the Response Header 'Content-Type' to 'text/plain'
     *     $res->header('Content-Type', 'text/plain')
     *
     *     Get the Response Header 'Content-Type' that has been set.
     *     If no value has been set then null will be returned.
     *     $value = $res->header('Content-Type')
     *
     *     Clear the Response Header 'Content-Type' that has been set
     *     $res->header('Content-Type', '')
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

        // Convert the name to a lower-case string
        $name_lower_case = strtolower($name);

        // Validation for Value Variable Type
        $value_is_valid = ($value === null || is_string($value));
        $value_is_valid = ($value_is_valid || ($name_lower_case === 'etag' && $value instanceof \Closure));
        $value_is_valid = ($value_is_valid || (is_int($value) && ($name_lower_case === 'expires' || $name_lower_case === 'last-modified' || $name_lower_case === 'content-length')));
        if (!$value_is_valid) {
            throw new \Exception(sprintf('The function [%s->%s()] was called with an invalid parameter. The parameter $value must be either [string|null] for most headers, [string|int|null] for [Expires], [Last-Modified], or [Content-Length] Response Headers, and [string|int|Closure|null] for the [ETag] Response Header. The function was called with the following parameters: $name = [%s], type of $value = [%s]', __CLASS__, __FUNCTION__, $name, gettype($value)));
        }

        // First check for exact match, example 'Content-Type'
        $key_exists = false;
        if (isset($this->header_fields[$name])) {
            $key_exists = true;
        }

        // If not found perform a case-insensitive search of the array keys
        if (!$key_exists) {
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

        // When setting or clearing return this Response Object Instance
        return $this;
    }

    /**
     * Return an array of Headers fields defined from the header() function
     * that will be or have been sent with the HTTP Response.
     *
     * @return array
     */
    public function headers()
    {
        return $this->header_fields;
    }

    /**
     * Get or set the response status code by number (for example 200 for 'OK'
     * or 404 for 'Not Found'). By default the PHP will set a status of 200
     * so setting a status code is usually only needed for other status codes
     * other than 200. This gets sent when the response is sent to the client.
     * If this function is called without a status code passed as a parameter
     * then it will return the current status code otherwise when setting a
     * status code it will return the Response object so it can be used
     * in chainable methods.
     *
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     * @param int|null $new_value (default: null)
     * @return null|int|$this
     * @throws \Exception
     */
    public function statusCode($new_value = null)
    {
        // Get
        if ($new_value === null) {
            return $this->status_code;
        }

        // Set
        if (is_int($new_value) && $new_value >= 100 && $new_value <= 599) {
            $this->status_code = $new_value;
            return $this;
        }

        // If invalid then thrown an Exception
        $error = 'Unhandled Response Status Code of [%s] for [%s->%s()].';
        if (is_int($error)) {
            $error = sprintf($error, $new_value, __CLASS__, __FUNCTION__);
        } else {
            $error = sprintf($error, 'type: ' . gettype($new_value), __CLASS__, __FUNCTION__);
        }
        throw new \Exception($error);
    }

    /**
     * Get or set the response content type header. This function is designed
     * for developer ease of use so rather than having to define the full header
     * such as 'Content-Type: application/json' the type 'json' can simply be
     * used. The header is sent in the response from the function send(). 
     * This function supports the most common text content types used in 
     * web sites and web applications. For a full list of content media types see 
     * the defined standards from Internet Assigned Numbers Authority (IANA) in 
     * the reference link below.
     *
     * If this function is called without a type passed as a a parameter then it
     * will return the current content type otherwise it will return the
     * Response object so it can be used in chainable methods.
     *
     * Parameters:
     * [$type] If specified will be one of 'html|json|jsonp|text|css|javascript|xml|graphql', 
     *     any type from function [Response->fileTypeToMimeType()],
     *     or the actual content type for example 'text/html'.
     * [$option] If $type is in 'html|css|javascript|text' then $option can be a string 
     *     to specify the charset (e.g.: 'UTF-8') otherwise if 'jsonp' is the content type 
     *     then $option can be a string or an array of query string parameters. 
     *     By default if $option is null and the type is 'html' then 'UTF-8' is used 
     *     as the charset and if the type is 'jsonp' and then the query string 
     *     parameters 'callback' and 'jsonp' are used as the default.
     *
     * @link http://www.iana.org/assignments/media-types/media-types.xhtml
     * @param string|null $type
     * @param string|null $option
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function contentType($type = null, $option = null)
    {
	    // If this method was called without a parameter then return the
	    // content type that was already set or null if it has not been set.
        if ($type === null) {
            return $this->header('Content-Type');
        }

        // Validation
        if ($type === 'html' || $type === 'javascript' || $type === 'css' || $type === 'text') {
            // If defined check that the option if not null is one of the 5 most commonly used character sets
            if ($option !== null) {
                // http://trends.builtwith.com/encoding
                // http://www.iana.org/assignments/character-sets/character-sets.xhtml
                $valid_charsets = array('UTF-8', 'ISO-8859-1', 'GB2312', 'Shift_JIS', 'GBK');
                if (!in_array($option, $valid_charsets, true)) {
                    throw new \Exception(sprintf('Invalid option for [%s->%s()]. Null which defaults to [UTF-8] or only widely used charsets are support as the option. The $type parameter was [%s] and the invalid $option parameter was [%s]. Valid Options for this function are [%s]. Additional charsets can be defined if specifying the full [Content-Type] header as the first parameter when calling this function.', __CLASS__, __FUNCTION__, $type, $option, implode('], [', $valid_charsets)));
                }
            }
        } elseif ($option !== null && $type !== 'jsonp') {
            // The option value for 'jsonp' is handled by the function jsonpParameters() while
            // all other content types do not have an option defined by FastSitePHP.
            throw new \Exception(sprintf('Invalid option for [%s->%s()]. The only content types that allow for an option to be specified are [html], [javascript], [css], [text], and [jsonp]. The $type parameter was [%s] and the invalid $option parameter was [%s].', __CLASS__, __FUNCTION__, $type, $option));
        }

		// Convert the simple content type from the parameter
		// to a content type that is valid for the http protocol
        switch ($type)
        {
            case 'html':
                // By default the HTML character encoding for FastSitePHP is 'UTF-8'
                // because it is the most widely used character encoding method
                // (around 85% of all Websites in late 2015) and the recommend
                // encoding method by the World Wide Web Consortium (W3C) plus
                // it is the most widely supported option for most code editors.
                // Another common option would be 'ISO-8859-1'. To set the value
                // as 'text/html' without encoding specify 'text/html' for the
                // $type parameter when calling this function.
                $charset = ($option === null ? 'UTF-8' : $option);
                $value = 'text/html; charset=' . $charset;
                break;
            case 'json':
                $value = 'application/json';
                break;
            case 'jsonp':
                // If charset is needed then it can be manually set with the full header value
                // and then jsonpQueryString() would be used to set [jsonp_query_string].
                // A unit test shows how this can be done.
                $value = 'application/javascript';
                $this->jsonpQueryString($option === null ? array('callback', 'jsonp') : $option);
                break;
            case 'text':
                $value = 'text/plain' . ($option === null ? '' : '; charset=' . $option);
                break;
            case 'css':
                $value = 'text/css' . ($option === null ? '' : '; charset=' . $option);
                break;
            case 'javascript':
                $value = 'application/javascript' . ($option === null ? '' : '; charset=' . $option);
                // 'jsonp' and 'javascript' share the same 'Content-Type' so make sure
                // the [jsonp_query_string] is null when setting the content type as 'javascript'.
                $this->jsonp_query_string = null;
                break;
            case 'xml':
                // Note - 'text/xml' is also valid however 'application/xml'
                // is the preferred mime type in most cases. To use 'text/xml'
                // this function can instead be called with contentType('text/xml')
                $value= 'application/xml';
                break;
            case 'graphql':
                $value= 'application/graphql';
                break;
            default:
                if (strpos($type, '/') !== false) {
                    $value = $type;
                } else {
                    $value = $this->fileTypeToMimeType($type);
                    if ($value === 'application/octet-stream') {
                        $value = $type;
                    }
                }
                break;
        }

        // Validate valid format 'top-level/sub-type'
        if (strpos($value, '/') === false) {
            $error = 'Error - Invalid option [%s] sepecified for [%s->%s()]. Valid values include [html, json, jsonp, text, css, javascript, xml], any type from function [Response->fileTypeToMimeType()], or the actual content type for example [video/mp4].';
            throw new \Exception(sprintf($error, $type, __CLASS__, __FUNCTION__));
        }

		// Set the 'Content-Type' Response Header and return this Response Object Instance
        return $this->header('Content-Type', $value);
    }

    /**
     * Gets or sets the value of the Query String Parameters that would be used for a
     * JSONP Request (JSON with Padding). This function would not commonly be used and
     * instead simply calling contentType('jsonp') would be enough for most apps.
     * The only time this function would likely be used is if the contentType() was
     * manually set with a specific JavaScript character set then this function could
     * be used to set the Query String Parameters.
     *
     * @param array|null|string $value
     * @return $this|array|null|string
     * @throws \Exception
     */
    public function jsonpQueryString($value = null)
    {
        // If this method was called without a parameter then return
        // the value that was already set or null if it has not been set.
        if ($value === null) {
            return $this->jsonp_query_string;
        }

        // Validation
        if (!is_array($value) && !is_string($value)) {
            throw new \Exception(sprintf('Unexpected parameter $value for [%s->%s()], expected [string|array|null] but was passed [%s]', __CLASS__, __FUNCTION__, gettype($value)));
        } elseif (is_array($value) && count($value) === 0) {
            throw new \Exception(sprintf('Error with the parameter $value for [%s->%s()], when passing an array as the parameter the array must have at least one or more values. The array passed to this function was empty.', __CLASS__, __FUNCTION__));
        } elseif (is_string($value) && $value === '') {
            throw new \Exception(sprintf('Error with the parameter $value for [%s->%s()], when passing a string as the parameter the value cannot be empty.', __CLASS__, __FUNCTION__));
        }

        // Set the value and return this Application Instance Object
        $this->jsonp_query_string = $value;
        return $this;
    }

    /**
     * Get or set content that will be sent to the client. When a Route returns a
     * Response Object the function [send()] will be called which sends the actual
     * Response. To get the content of the response object call this function without
     * any parameters and to specify content specify the [$content] parameter. If the
     * response type [define from contentType()] is JSON or JSONP then the [$content]
     * can be an array or object otherwise it should be a string. When setting a
     * content value the Response Object Instance is returned.
     *
     * Examples:
     *
     *     $app->get('/html', function() {
     *         $res = new \FastSitePHP\Web\Response();
     *         return $res->content('<h1>FastSitePHP</h1>');
     *     });
     * 
     *     $app->get('/json', function() {
     *         $res = new \FastSitePHP\Web\Response();
     *         return $res
     *             ->contentType('json')
     *             ->content(array('Name' => 'FastSitePHP'));
     *     });
     *
     * @param null|mixed $content   Content that will be sent to the client
     * @return $this|mixed
     */
    public function content($content = null)
    {
        if ($content === null) {
            return $this->response_content;
        }
        $this->response_content = $content;
        return $this;
    }

    /**
     * Prepare the Response Object for a JSON Response with Content. This is a
     * helper function to provide a shorter syntax for the following:
     *
     *     $res->contentType('json')->content($object);
     *
     * This is a setter function only and returns the Response Object. For practical
     * usage a basic JSON response can be returned from the Application Object by
     * simply returning an array to the route. In most cases this would be used along
     * with other Response fields, for example:
     *
     *     $res->header('API-Key', '123')->etag('hash:md5')->json($data);
     *
     * @param object|array $content
     * @return $this
     * @throws \Exception
     */
    public function json($content)
    {
        if (!(is_array($content) || is_object($content))) {
            $error = 'Error - Invalid Parameter at [%s->%s()]. Expected and Array or Object but was passed a [%s].';
            throw new \Exception(sprintf($error, __CLASS__, __FUNCTION__, gettype($content)));
        }
        $this->header('Content-Type', 'application/json');
        $this->response_content = $content;
        return $this;
    }

    /**
     * Get or set options for [json_encode()] when a JSON Response is returned.
     * Defaults to [JSON_UNESCAPED_UNICODE] when using PHP 5.4 or later.
     * 
     * Example:
     *     $res->jsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
     * 
     * @param null|int $new_value
     * @return int|$this
     */
    public function jsonOptions($new_value = null)
    {
        if ($new_value === null) {
            return $this->json_options;
        }
        $this->json_options = (int)$new_value;
        return $this; 
    }
    
    /**
     * Get or set a value for the 'ETag' Response Header which is used to specify
     * rules for HTTP Caching. ETag is short for Entity Tag and is typically a
     * hash value (e.g.: md5 value of the content). When the function sendResponse()
     * in this class gets called the ETag value is compared to the Request Header
     * 'If-None-Match' and based on matching rules either a 200 'Ok' or 304 'Not Modified'
     * response is returned for most webpages. There are two types of ETags validators:
     * 'strong' and 'weak'.  A strong ETag is used to indicate that the contents of
     * two requested resources including header fields at a URL (e.g.: webpages) are
     * byte-for-byte identical. A weak ETag indicates that two requested resources
     * are semantically equivalent. Strong ETags are not suitable for gzipped content
     * because with compression turned on the response is not byte-for-byte identical.
     * Depending upon the web server version if using strong ETags with gzipped content
     * Nginx will remove them from the Response or convert them to weak ETags and
     * Apache using mod_deflate will add a suffix which makes it unusable to most
     * server side frameworks. Because of this weak ETags are used as the default option.
     * If your site is using ETags then the Unit Testing Pages and the Samples Cache Test
     * Section can be used to make sure that caching for your site is working as expected.
     *
     * @link https://en.wikipedia.org/wiki/HTTP_ETag
     * @param null|string|\Closure $value   If the format 'hash:{algorithim}' is used (example: 'hash:md5') then FastSitePHP will calculate the hash from the content when the response is sent.
     * @param string $type  'weak' or 'strong' (defaults to 'weak')
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function etag($value = null, $type = 'weak')
    {
	    // If this method was called without a parameter then return
        // the ETag that was already set or null if it has not been set.
        if ($value === null) {
            return $this->header('ETag');
        }

        // $value must be a string or a closure function
        if (!(is_string($value) || $value instanceof \Closure)) {
            throw new \Exception(sprintf('Unexpected parameter $value for [%s->%s()], expected [string|Closure|null] but was passed [%s]', __CLASS__, __FUNCTION__, gettype($value)));
        }

        // Validate the type of ETag, there are two valid types: 'strong' and 'weak'.
        if ($type !== 'weak' && $type !== 'strong') {
            throw new \Exception(sprintf('Incorrect parameter $type for [%s->%s()]; $type must be specified as either \'strong\' or \'weak\'', __CLASS__, __FUNCTION__));
        }

        // Handle strings specifying a built-in hash algorithim to use.
        // The format of 'hash:algorithim', for example 'hash:md5'.
        if (is_string($value) && substr($value, 0, 5) === 'hash:') {
            // Get and validate the hash
            $algo = substr($value, 5);
            if (!in_array($algo, hash_algos(), true)) {
                throw new \Exception(sprintf('Error calling [%s->%s()] using the parameter [%s]. Invalid hash, the required format for this function when specifying a hash is "hash:{algorithim}", for example "hash:md5". Any algorithm registered in the PHP function hash_algos() can be used.', __CLASS__, __FUNCTION__, $value));
            }

            // Define a closure that calculates from the hash
            $value = function($content) use ($algo) {
                return hash($algo, $content);
            };
        }

        // If a ETag is specified as a string then make sure it is in the correct format
        if (is_string($value) && $value !== '') {
            // ETags are enclosed in quotes and if using a weak validator
            // are then prefixed with 'W/'. If the ETag value is '123abc'
            // the the value for the Response header will look like '"123abc"'
            // for a strong ETag or 'W/"123abc"' for a weak ETag.

            // First check if the ETag needs to have quotes added,
            // if it does then add them.
            $has_quotes = (substr($value, 0, 1) === '"' && substr($value, -1, 1) === '"');
            if (!$has_quotes) {
                $value = '"' . $value . '"';
            }

            // If the ETag is using a weak validator then add the 'W/' prefix
            if ($type === 'weak') {
                $value = 'W/' . $value;
            }
        } elseif ($value instanceof \Closure) {
            // ETag type is only saved when a Closure function is used.
            // It is used later when sendResponse() gets called.
            $this->etag_type = $type;
        }

        // Set the ETag and return this Application Object Instance
        return $this->header('ETag', $value);
    }

    /**
     * Get or set a value for the 'Last-Modified' Response Header which is used to specify rules for HTTP Caching.
     * Additionally when sendResponse() gets called the 'Last-Modified' value is compared to the Request Header
     * 'If-Modified-Since' and based on matching rules either a 200 'Ok' or 304 'Not Modified' response is returned.
     * If setting the value then the parameter value $ last_modified_time needs to be defined as an int to represent
     * a Unix Timestamp or a valid string format for the php function strtotime().
     *
     * @param null|string|int $last_modified_time
     * @return $this|mixed|null
     */
    public function lastModified($last_modified_time = null)
    {
        return $this->dateHeader('Last-Modified', $last_modified_time, 'lastModified', '$last_modified_time');
    }

    /**
     * Get or set a value for the 'Cache-Control' Response Header which is used to specify rules for HTTP Caching.
     * When setting the header value this function validates that it is in a correct format for defined HTTP 1.1
     * directive options and values. Per HTTP 1.1 Specs user defined fields are allowed however they are not commonly
     * used so this function validates only for standards fields. This function is handled this way so that if
     * there is a typo it can be caught. If the a custom 'Cache-Control' field name is needed then it can be
     * added with the header() function instead.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
     * @param null|string $value
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function cacheControl($value = null)
    {
	    // If this method was called without a parameter then return
        // the value that was already set or null if it has not been set.
        if ($value === null) {
            return $this->header('Cache-Control');
        }

        // If a blank string was passed as the parameter then
        // call the header() function with a blank string
        // to clear the existing value.
        if ($value === '') {
            return $this->header('Cache-Control', '');
        }

        // Check if the value contains a double-quote character. If not
        // then a simple string split can be used to get the options
        // otherwise it needs to use more complex parsing.
        if (strpos($value, '"') === false) {
            // Split the string into an array
            $options = explode(',', $value);
        } else {
            // Parse the option list including quoted strings
            // The definition for a quoted-string is located at:
            // https://tools.ietf.org/html/rfc2616#section-2.2
            $options = array();

            // Values used while parsing the string
            $in_quote = false;
            $option = '';

            // Loop through each character in the string one at at time
            for ($n = 0, $m = strlen($value); $n < $m; $n++) {
                // Get the current character
                $char = $value[$n];

                // Is the current character is a comma and not
                // part of a quoted string?
                if ($char === ',' && !$in_quote) {
                    // Add all parsed text prior to the comma
                    // to the options array as a new option
                    $options[] = $option;

                    // Reset the option text for the next item
                    $option = '';
                } else {
                    // All characters that aren't a separator comma
                    // are added to the current option
                    $option .= $char;
                }

                // If the current character is a double-quote then
                // mark it as either the start or end of the quoted string.
                if ($char === '"') {
                    $in_quote = !$in_quote; //Toggle between true/false
                }
            }

            // Quoted strings must have both the starting and ending quote.
            // If the loop finished with the last character still in a quoted
            // string then the format is invalid.
            if ($in_quote) {
                throw new \Exception(sprintf('The cacheControl() function was called with an invalid format. A quoted-string string was started by using the ["] character however an ending ["] was not added to the string. All quoted-strings must have both the starting and ending quote characters. The cacheControl() parameter was [%s].', $value));
            }

            // Add the last option to the array
            if ($option !== '') {
                $options[] = $option;
            }
        }

        // Set defaults for flag variables
        $has_public = false;
        $has_private = false;
        $has_no_store = false;

        // Check each option
        foreach ($options as $option) {
            // Get the field name
            $field_name = trim($option);
            $field_value = null;

            // Check if the option contain a field value, if so then parse it
            $contains_field = strpos($field_name, '=');
            if ($contains_field !== false) {
                $field_value = substr($field_name, $contains_field + 1);
                $field_name = substr($field_name, 0, $contains_field);
            }

            // Check that the field name is a defined field for HTTP/1.1 (RFC 2616).
            switch ($field_name) {
                case 'public':
                    $has_public = true;
                    break;
                case 'private':
                    $has_private = true;
                    break;
                case 'no-store':
                    $has_no_store = true;
                    break;
                case 'max-age':
                case 's-maxage':
                    // Make sure the value is an int
                    if (filter_var($field_value, FILTER_VALIDATE_INT) === false) {
                        throw new \Exception(sprintf('The cacheControl() function was called with an invalid option for [%s], the value must be specified as a time in seconds using integer format. The cacheControl() parameter was [%s].', $field_name, $value));
                    }

                    // Make sure the int value is positive, the largest value allowed
                    // is the max size of an int so only min validation is needed.
                    // https://tools.ietf.org/html/rfc7234#section-5.2.2.8
                    // https://tools.ietf.org/html/rfc7234#section-1.2.1
                    if ((int)$field_value < 0) {
                        throw new \Exception(sprintf('The cacheControl() function was called with a negative number for [%s], the value must be specified as a time in seconds using integer format and must be 0 or greater. The cacheControl() parameter was [%s].', $field_name, $value));
                    }
                    break;
                case 'no-cache':
                case 'no-transform':
                case 'must-revalidate':
                case 'proxy-revalidate':
                    // Do nothing, these are valid options
                    break;
                default:
                    // Cache Control Extensions are not supported, provide
                    // the developer with a helpful error message.
                    throw new \Exception(sprintf('Cache Control Extensions are not supported the cacheControl() function, please check that the value is not a typo and if user defined fields are needed then use the header() function instead. The cacheControl() parameter was [%s] and the invalid field name is [%s].', $value, $field_name));
            }

            // Make sure that the option didn't specify a field value for fields that do not allow them
            if ($field_value !== null) {
                switch ($field_name) {
                    case 'public':
                    case 'no-store':
                    case 'no-transform':
                    case 'must-revalidate':
                    case 'proxy-revalidate':
                        if ($field_value !== '') {
                            throw new \Exception(sprintf('A Cache Control Option was set with a field value for an option that does not support field values. Please check the options specified and if needed use the header() function instead. The cacheControl() parameter was [%s] and the option name with a field value is [%s].', $value, $field_name));
                        }
                        break;
                }
            }
        }

        // Check for values that appear valid but do not make sense logically.
        // A response should not specify both 'public' and 'private' and if
        // 'no-store' is set then the the resource should not be cached with
        //either 'public' or 'private'.
        if ($has_public && $has_private) {
            throw new \Exception(sprintf('A Cache-Control header value cannot have both [public] and [private] specified. Please check the value and if needed use the header() function instead. The cacheControl() parameter was [%s].', $value));
        } elseif ($has_no_store && ($has_public || $has_private)) {
            throw new \Exception(sprintf('A Cache-Control header value cannot have [no-store] set with either [public] or [private] specified. Please check the value and if needed use the header() function instead. The cacheControl() parameter was [%s].', $value));
        }

        // Value for 'Cache-Control' header is valid, set the value
        // and return this Application Instance.
        return $this->header('Cache-Control', $value);
    }

    /**
     * Get or set a value for the 'Expires' Response Header which is used to specify rules for HTTP Caching.
     * If setting the value then the parameter value $expires_time needs to be defined as an int to represent a
     * Unix Timestamp, string values of '0' or '-1' for to prevent caching, or a valid string format for the
     * php function strtotime(). When setting the header value through this function the largest date allowed
     * is one year from today. To set the header value to a date greater than one year from today use the
     * header() function instead.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21
     * @param null|string|int $expires_time
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function expires($expires_time = null)
    {
        // If the values '0' or '-1' are specified then set the value through the header()
        // function as it will accept any string, otherwise use the private dateHeader()
        // function to verify that the value is valid for an 'Expires' header.
        if (is_string($expires_time) && ($expires_time === '0' || $expires_time === '-1')) {
            return $this->header('Expires', $expires_time);
        } else {
            return $this->dateHeader('Expires', $expires_time, 'expires', '$expires_time');
        }
    }

    /**
     * Get or set a value for the 'Vary' Response Header which is used to specify rules for
     * HTTP Caching and also to provide content hints to Google and other Search Engines.
     * When setting the header value this function validates that the options specified are
     * valid options; this includes all server driven content negotiation headers and several
     * commonly used request headers. It's possible that other options could be used and would
     * be valid however this function validates for common options so that if there is a typo
     * it can be caught and to catch logic errors with the parameters. The valid parameters that
     * can be used with this function are ['Accept', 'Accept-Charset', 'Accept-Encoding',
     * 'Accept-Language', 'User-Agent', 'Origin', 'Cookie', and 'Referer']. If the a custom 'Vary'
     * option is needed then it can be added with the header() function instead. Additionally
     * testing should be performed when using this function because depending upon how the actual
     * web server is configured this function could overwrite the web server value or the
     * web server could overwrite the value set from this function.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.44
     * @link https://developers.google.com/webmasters/mobile-sites/mobile-seo/configurations/dynamic-serving
     * @link http://httpd.apache.org/docs/2.2/content-negotiation.html
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
     * @link https://www.fastly.com/blog/best-practices-for-using-the-vary-header
     * @link https://www.youtube.com/watch?v=va6qtaiZRHg
     * @link https://support.microsoft.com/en-us/kb/2877816
     *
     * @param null|string $value
     * @return $this|mixed|null
     * @throws \Exception
     */
    public function vary($value = null)
    {
	    // If this method was called without a parameter then return
        // the value that was already set or null if it has not been set.
        if ($value === null) {
            return $this->header('Vary');
        }

        // If a blank string was passed as the parameter then
        // call the header() function with a blank string
        // to clear the existing value.
        if ($value === '') {
            return $this->header('Vary', '');
        }

        // The 'Vary' value of '*' is a special option that tells the client to not cache anything.
        // It is a valid value however in most cases the headers set from the function noCache()
        // would be used instead.
        if ($value !== '*') {
            // Array of valid options for Server Driven Content Negotiation and
            // several commonly used Request Headers: Cookie and Referer
            $valid_options = array(
                'Accept', 'Accept-Charset', 'Accept-Encoding',
                'Accept-Language', 'User-Agent', 'Origin',
                'Cookie', 'Referer',
            );

            // Create a lower-case version of the array for a case-insensitive search
            $value_options_lcase = array_map('strtolower', $valid_options);

            // Split the parameter value into an array and remove spaces from each value
            $options = array_map('trim', explode(',', $value));

            // Check each option
            foreach ($options as $option) {
                // Does the current option exist in the array?
                $is_valid = in_array(strtolower($option), $value_options_lcase, true);

                // If not then an unknown value is used so throw an exception with
                // a helpful message for the application developer.
                if (!$is_valid) {
                    if ($option === '*') {
                        throw new \Exception(sprintf('The [Vary] Response Header Option [*] cannot be combined with other options, the vary() value specified was [%s].', $value));
                    } else {
                        throw new \Exception(sprintf('An unknown option was specified for the [Vary] Response Header. The vary() function only supports options used with server driven content negotiation and several commonly used request headers. If you have confirmed that the header value is valid then use the header() function instead. The vary() parameter was [%s] and the invalid option is [%s]. Valid Options are [%s].', $value, $option, implode('], [', $valid_options)));
                    }
                }
            }

            // The actual HTTP 1.1 Specs do not specify a maximum number of options
            // however in reality if more than two options are used the content will
            // likely never be cached so throw an exception which a message explaining
            // the issue and that if the options are valid to use the
            // header() function instead.
            if (count($options) > 2) {
                throw new \Exception(sprintf('The [Vary] Response Header was specified with more than 2 options. The vary() function supports a maximum of two options because if more than 2 are used the content would likely never be cached. Please double-check the need for your site or application to use these options together and if you have confirmed that the header value is valid then use the header() function instead. The vary() parameter was [%s].', $value));
            }
        }

        // Value for 'Vary' header is valid, set the value
        // and return this Application Instance.
        return $this->header('Vary', $value);
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
     * @return $this|mixed
     */
    public function noCache()
    {
        return $this
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '-1');
    }

    /**
     * Get or set a values for Cross-Origin Resource Sharing (CORS) Response Headers. 
     * For security reasons browsers will restrict content that is from a different domain
     * when using JavaScript (for example: calling a Web Service from XMLHttpRequest). 
     * CORS is a web standard that allows for restricted resources to work on domains 
     * other than the domain where the resource is hosted.
     *
     * CORS Headers are sent with both the OPTIONS request method and the calling method.
     * Because OPTIONS requests are required for certain response types CORS Headers are
     * initially defined from the Application Object and then passed the Response Object.
     *
     * For more on this topic refer to documentation from the cors() function in the
     * Application Object.
     *
     * Example:
     *     $app->cors(array(
     *         'Access-Control-Allow-Origin' => '*',
     *         'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Authorization',
     *     ));
     *     $res = new \FastSitePHP\Web\Response();
     *     $res->cors($app);
     *
     * @param Application|null $app
     * @return $this|array|null
     */
    public function cors(Application $app = null)
    {
	    // Return any previously set CORS Headers on this 
        // Response Object or null if no CORS Headers are set.
        if ($app === null) {
            $cors_headers = array();
            foreach ($this->header_fields as $key => $value) {
                if (strpos(strtolower($key), 'access-control-') === 0) {
                    $cors_headers[$key] = $value;
                }
            }
            return (count($cors_headers) === 0 ? null : $cors_headers);
        }
        
        // Before setting CORS Headers from the Application Object
        // to this Response Object, clear any previous CORS Headers 
        // that were set on this Response Object.
	    foreach ($this->header_fields as $key => $value) {
            if (strpos(strtolower($key), 'access-control-') === 0) {
                unset($this->header_fields[$key]);
            }
        }

        // Set any defined CORS Headers from the Application Object
        // to this Response Object and return this Response Object.
        $cors_headers = $app->cors();
        if ($cors_headers !== null) {
            foreach ($cors_headers as $key => $value) {
                $this->header($key, $value);
            }
        }
        return $this;
    }

    /**
     * Define a cookie to be sent with the response along with the response headers.
     * Internally this calls the PHP function setcookie() from the private function
     * sendResponse(). To delete a cookie use the function [clearCookie()]. To read
     * cookies use the [cookie()] function of the [FastSitePHP\Web\Request] Object 
     * or use the PHP superglobal array $_COOKIE.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     * @link http://php.net/manual/en/features.cookies.php
     * @link http://php.net/manual/en/reserved.variables.cookies.php
     * @param $name
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
     * @param $name
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
     * Create a secure cookie that can be read by clients client but not tampered with.
     * Cookies sent using this method need to be read with [Request->verifiedCookie()]
     * to verify that they are not tampered with or expired. The default expiration time  
     * is 1 hour and it applies to the signed data and not the cookie itself.
     * 
     * Using this function requires the Application Config Value 'SIGNING_KEY'.
     * 
     * See also [encryptedCookie()] and [jwtCookie()].
     *
     * @param $name
     * @param string $value
     * @param string|int|null $expire_time - Expire time for the Signed Data and not the Cookie
     * @param int $expire - Defaults to 0 which makes the cookie expire at the end of the session
     * @param string $path (default: '')
     * @param string $domain (default: '')
     * @param bool $secure (default: false)
     * @param bool $httponly (default: false)
     * @return $this
     */    
    public function signedCookie($name, $value = '', $expire_time = '+1 hour', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $value = Crypto::sign($value, $expire_time);
        return $this->cookie($name, $value, $expire, $path, $domain, $secure, $httponly);        
    }

    /**
     * Create a secure cookie with a JSON Web Token (JWT). Cookies sent using this
     * method need to be read with [Request->jwtCookie()] to verify that they are
     * not tampered with or expired. The default expiration time is 1 hour and it 
     * applies to the JWT and not the cookie itself.
     * 
     * Using this function requires the Application Config Value 'JWT_KEY'.
     * 
     * See also [encryptedCookie()] and [signedCookie()].
     *
     * @param $name
     * @param string $value
     * @param string|int|null $expire_time - Expire time for the Signed Data and not the Cookie
     * @param int $expire - Defaults to 0 which makes the cookie expire at the end of the session
     * @param string $path (default: '')
     * @param string $domain (default: '')
     * @param bool $secure (default: false)
     * @param bool $httponly (default: false)
     * @return $this
     */    
    public function jwtCookie($name, $value = '', $expire_time = '+1 hour', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $value = Crypto::encodeJWT($value, $expire_time);
        return $this->cookie($name, $value, $expire, $path, $domain, $secure, $httponly);        
    }

    /**
     * Create a secure and secret cookie that cannot be read by clients.
     * Cookies sent using this method need to be read with [Request->decryptedCookie()].
     * 
     * Using this function requires the Application Config Value 'ENCRYPTION_KEY'.
     * 
     * See also [signedCookie()] and [jwtCookie()].
     * 
     * @param $name
     * @param string $value
     * @param int $expire    Defaults to 0 which makes the cookie expire at the end of the session
     * @param string $path (default: '')
     * @param string $domain (default: '')
     * @param bool $secure (default: false)
     * @param bool $httponly (default: false)
     * @return $this
     */
    public function encryptedCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $value = Crypto::encrypt($value);
        return $this->cookie($name, $value, $expire, $path, $domain, $secure, $httponly);
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

    /**
     * Private function used internally to get or set a Response Date Header.
     * This functions makes sure the value is saved as a Unix Timestamp (int).
     *
     * @param string $name
     * @param null|string|int $time
     * @param string $func_name
     * @param string $var_name
     * @return $this|mixed|null
     * @throws \Exception
     */
    private function dateHeader($name, $time, $func_name, $var_name)
    {
        // If the $time option is null then the function
        // is used to return the currently set response
        // header value.
        if ($time === null) {
            return $this->header($name);
        }

        // Validate the that the $time parameter is valid and looks like or is a date
        if (is_string($time)) {
            // Parse the string date value into a Unix Timestamp
            $value = strtotime($time);

            // If the value returned is false then it was not a valid date string
            if ($value === false) {
                throw new \Exception(sprintf('Invalid parameter %s for [%s->%s(\'%s\')]. The parameter must be a valid value for the php function strtotime()', $var_name, __CLASS__, $func_name, $time));
            }

            // Convert the time value to save as a Unix Timestamp value
            $time = $value;
        // If the $time value is an int then assume it's a valid Unix Timestamp
        } elseif (!is_int($time)) {
            throw new \Exception(sprintf('Unexpected parameter %s for [%s->%s()], expected [string|int|null] but was passed [%s]', $var_name, __CLASS__, $func_name, gettype($time)));
        }

        // Validate that the expires header has a maximum value of 1 year from today.
        // In HTTP Specs this rule is defined as "SHOULD NOT send Expires dates more than one year in the future"
        // so it is not a required validation by HTTP Specs but rather a recommendation.
        // Date values great than one year can still be set through the header() function.
        if ($func_name === 'expires') {
            if ($time > strtotime('+ 1 year')) {
                throw new \Exception(sprintf('Invalid Value for [%s->expires()]. Expires date values cannot be greater than one year from the current time using this function. To set the header value to a date greater than one year from today use the header() function instead.', __CLASS__));
            }
        }

        // Set the time value for the header in Unix Timestamp format
        // and return this Application object
        return $this->header($name, $time);
    }

    /**
     * Return a Response Mime-type for a File extension for commonly used file formats
     * in modern web apps and supported by popular browsers. For example 'file.txt' will
     * return 'text/plain' and 'file.mp4' will return 'video/mp4'. The file path does not
     * need to be a real file when calling this function. If a file type is not associated
     * with a mime-type then 'application/octet-stream' will be returned indicating that
     * the type of file is a file download; this includes common file types such as
     * Office Documents and Windows BMP Images. For a large list of known Mime-types
     * refer to the reference links below.
     * 
     * File extensions that map to a Mime type with the function are: 
     *     Text: htm, html, txt, css, csv, md, markdown, jsx
     *     Image: jpg, jpeg, png, gif, webp, svg, ico
     *     Application: js, json, xml, pdf, woff, graphql
     *     Video: mp4, webm, ogv, flv
     *     Audio: mp3, weba, ogg, m4a, aac
     *
     * @link http://www.iana.org/assignments/media-types/media-types.xhtml
     * @link http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     * @link https://msdn.microsoft.com/en-us/library/ms775147
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types
     * @param string $file_name
     * @return string
     */
    public function fileTypeToMimeType($file_name)
    {
        // Get the file type, example 'file.htm' = 'htm'
        if (strpos($file_name, '.') === false) {
            $file_type = $file_name;
        } else {
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        }
        
        // Return mime-type based on file extension
        switch ($file_type) {
            // Text Files
            case 'htm':
            case 'html':
                return 'text/html';
            case 'txt':
                return 'text/plain';
            case 'md':
            case 'markdown':
                return 'text/markdown';
            case 'csv':
            case 'css':
            case 'jsx':
                return 'text/' . $file_type;
            // Images
            case 'png':
            case 'gif':
            case 'webp':
                return 'image/' . $file_type;
            case 'jpg':
            case 'jpeg':
                return 'image/jpg';
            case 'svg':
                return 'image/svg+xml';
            case 'ico':
                return 'image/x-icon';
            // Web
            case 'js':
                return 'application/javascript';
            case 'woff':
                return 'application/font-woff';
            case 'json':
            case 'xml':
            case 'pdf':
            case 'graphql':
                return 'application/' . $file_type;
            // Video
            case 'mp4':
            case 'webm':
                return 'video/' . $file_type;
            case 'ogv':
                return 'video/ogg';
            case 'flv':
                return 'video/x-flv';
            // Audio
            case 'mp3':
            case 'weba':
            case 'ogg':
                return 'audio/' . $file_type;
            case 'm4a':
            case 'aac':
                return 'audio/aac';
            // All others
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * Specify a file for the response; the file specified will be streamed to the
     * client and sent in a memory efficient manner so this function can be called
     * on very large files with minimal performance impact for the server. The contents
     * of the file are not modified when calling this function so it cannot be used
     * to render templates. This function is ideal for sending file download responses
     * and media files such as images or video.
     *
     * This function provides several optional parameters to specify the response 
     * content type and caching headers:
     *
     * $content_type
     * The content type to set for contentType() such as 'text', 'html' or 'download' to 
     * specify 'application/octet-stream' and related headers for a file download. 
     * If not set then the mime type is determined from the file extension
     * using the function fileTypeToMimeType().
     *
     * $cache_type
     * Value to set for either ETag or Last-Modified headers which allow for 
     * Cached Responses of 304 'Not Modified'. Valid options are: 'etag:md5', 
     * 'etag:sha1', and 'last-modified'. All values are calculated from the file directly.
     * When calculating Etag based on a hash be aware that the value will be calculated 
     * each time this function is called so in the case of large files that are 100's of
     * megabytes or more in size it can delay the initial streamed response by as much as
     * a few seconds. If very large files are used with this function and ETag is needed
     * then an alternative solution such as saving a hash of the file and setting it
     * through the etag() function can be used to improve performance.
     *
     * $cache_control
     * Value for the 'Cache-Control' header which gets set from the function cacheControl().
     *
     * @param string|null $file_path
     * @param string|null $content_type
     * @param string|null $cache_type
     * @param string|null $cache_control
     * @return $this
     * @throws \Exception
     */
    public function file($file_path = null, $content_type = null, $cache_type = null, $cache_control = null)
    {
        // If null is passed then return the current file value
        // otherwise if a blank string then clear the current file
        // and return this Response object.
        if ($file_path === null) {
            return $this->response_file;
        } elseif ($file_path === '') {
            $this->response_file = null;
            return $this;
        }

        // Validate that the file exists
        if (!is_file($file_path)) {
            throw new \Exception(sprintf('[%s->%s()] was called for a file that does not exist: %s', __CLASS__, __FUNCTION__, $file_path));
        }

        // Determine the Mime-type to send if not specified as a parameter
        if ($content_type === null) {
            $content_type = $this->fileTypeToMimeType($file_path);
        }

        // Set the 'Content-Type' Responder Header or if a 'Download' file is
        // specified then set Responder Headers so that the browswer will prompt
        // to download the file.
        if ($content_type === 'download' || $content_type === 'application/octet-stream') {
            // Get the file name and replace any double-quotes.
            // Note - [basename()] is not used because it doesn't always 
            // work in some environments (often Linux or Unix) for Unicode
            // Characters unless calling [setlocale()]. Since the Locale 
            // is not known this method is more reliable.
            //   $file_name = str_replace('"', '', basename($file_path));
            $data = explode(DIRECTORY_SEPARATOR, realpath($file_path));
            $file_name = $data[count($data)-1];

            // Headers [ 'Content-Description', 'Content-Type', 'Content-Disposition' ]
            // are related to the download while headers [ 'Cache-Control', 'Pragma', 'Expires' ]
            // are related to caching. These caching headers are similar to what is sent
            // from noCache() but vary slightly for 'Cache-Control'.
            $this
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $file_name . '"')
                ->header('Cache-Control', 'must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '-1');
        } else {
            $this->contentType($content_type);
        }
        
        // If a cache type is specified then calculate either a
        // hash or last modified date from the file.
        if ($cache_type !== null) {
            switch (strtolower($cache_type)) {
                case 'etag:md5':
                    $this->etag(md5_file($file_path));
                    break;
                case 'etag:sha1':
                    $this->etag(sha1_file($file_path));
                    break;
                case 'last-modified':
                    $this->lastModified(filemtime($file_path));
                    break;
                default:
                    throw new \Exception('Invalid parameter for option $cache_type: ' . $cache_type);
            }
        }

        // Set a 'Cache-Control' header if one is defined from this function
        if ($cache_control !== null) {
            $this->cacheControl($cache_control);
        }
        
        // Set a private property to the file path and return the Response Object
        $this->response_file = $file_path;
        return $this;
    }

    /**
     * Specify a redirect URL for the response. By calling this function the response will
     * redirect the user to another page or site by sending a status code of 3## for the
     * response with the Location Header set to the new URL.
     *
     * The redirect() function also exists in the main Application Object but can be used 
     * here instead if your site is designed to return a Response object for all routes.
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
     *     $app->get('/page1', function() {
     *         $res = new \FastSitePHP\Web\Response();
     *         return $res->redirect('page2');
     *     });
     *
     *     // User will then see this URL and Response
     *     $app->get('/page2', function() {
     *         return 'page2';
     *     });
     *
     * @link http://en.wikipedia.org/wiki/URL_redirection
     * @param string $url
     * @param int $status_code  Default 302 for 'Found'
     * @return $this
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

        // Build the Response Body. This is not actually required and using a Web Browser
        // the end user would never see this, however RFC 2616 recommends that the body 
        // of a Redirect Response should include a short note with a link to the new URI.
        $html_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8', true);
        $content = '<h1>' . $status_code_text[$status_code] . '</h1>';
        $content .= '<p>Redirecting to <a href="' . $html_url . '">' . $html_url . '</a></p>';
        
        // Set values for the Response and return this Response Object
        $this->status_code = $status_code;
        $this->header_fields['Location'] = $url;
        $this->response_content = $content;
        return $this;
    }

    /**
     * Reset the Response object to its default state as if it were just created.
     *
     * @return $this
     */
    public function reset()
    {
        $this->status_code = null;
        $this->header_fields = array();
        $this->response_cookies = array();
        $this->etag_type = null;
        $this->jsonp_query_string = null;
        $this->response_file = null;
        $this->response_content = null;
        return $this;
    }

    /**
     * Send the Response to the Client. This function gets called automatically if a route 
     * returns a response object and would normally not be manually called. This function
     * handles sending Response Headers, Cookies, and Content.
     *
     * @throws \Exception
     */
    public function send()
    {
        // This function is large and handles validation, preparing headers, formatting headers,
        // sending headers, and sending content. It could be separated into smaller private functions
        // however doing so increases the amount of memory and decreases performance (this was tested
        // during development). The increase in memory is relatively small at 2% or less however
        // FastSitePHP is designed for performance (while being a scripting language) so large functions
        // are acceptable (plus every single line of code in this function is unit tested so
        // even though it is large it functions as expected).

        // Basic validation of the Response that either [content()] or [file()] 
        // was called for most response types but that not both are called.
        if ($this->response_content !== null && $this->response_file !== null) {
            throw new \Exception(sprintf('The [%s] Object for the current Route had content set through both [content()] and [file()] functions. When returning the response object from a route or when sending the response only one of these functions can be called.', __CLASS__));
        } else {
            if ($this->status_code !== 304 
                && $this->status_code !== 204 
                && $this->status_code !== 205
                && $this->response_content === null
                && $this->response_file === null) {
                    throw new \Exception(sprintf('The [%s] Object for the current Route had no content set from either [content()], [file()], or [redirect()] functions. Before returning the response object from a route or before sending the response content must be set unless the status code is [204 - No Content], [205 - Reset Content], or [304 - Not Modified].', __CLASS__));
            }
        }
        
        // Check if JSON or JSONP Response and if so then update the Response Content
        if ($this->response_file === null && gettype($this->response_content) !== 'string') {
            // Get the Response Content-Type
            $content_type = $this->header('Content-Type');

            // If the response type is specified as JSON then convert
            // the response content to a JSON string.
            if ($content_type === 'application/json') {
                $this->response_content = json_encode($this->response_content, $this->json_options);
            // If JSONP (JSON with Padding) then export as a JavaScript
            // function with a JSON Object or Array as the parameter.
            } elseif ($this->jsonp_query_string !== null && strpos($content_type, 'application/javascript') === 0) {
                // [jsonp_query_string] can be either a string or array
                // so if string then cast it as an array
                $qs_params = (array)$this->jsonp_query_string;
                $qs_param = null;
                $js_function = null;

                // Find the JavaScript Function Name from the Query String
                foreach ($qs_params as $item) {
                    if (isset($_GET[$item])) {
                        $js_function = $_GET[$item];
                        $qs_param = $item;
                        break;
                    }
                }

                // Validate that a valid JavaScript function was specified. This does not validate
                // against any allowed JavaScript function name but rather is checking for a function
                // name that contains only letters, numbers, or an underscore; and that it is at least
                // two characters in length and does not start with a number.
                if ($js_function === null) {
                    throw new \Exception('[jsonp] was specified as the content-type however a JavaScript function was not found in one of the query string parameters: ' . implode(', ', $qs_params));
                } elseif ($js_function === '') {
                    throw new \Exception(sprintf('The [jsonp] callback query string parameter [%s] was defined however it did not contain a function name and was instead an empty string.', $qs_param));
                } elseif (preg_match('/^[A-Za-z_][A-Za-z0-9_]+$/', $js_function) !== 1) {
                    throw new \Exception(sprintf('The [jsonp] callback function was not using a format supported. The function name must contain only letters, numbers, or the underscore character; it must be at least two characters in length and cannot start with a number. Query String Parameter [%s] and Value [%s]', $qs_param, $js_function));
                }

                // There are two Unicode Control Characters supported in JSON Strings but not in JavaScript:
                //   LINE SEPARATOR (U+2028)
                //   PARAGRAPH SEPARATOR (U+2029)
                // In some Frameworks and Languages these have to be manually handled (for example
                // in Ruby on Rails and Express with Node.js), however in PHP the function json_encode()
                // escapes all Unicode Characters by default so the two characters do not have to be
                // handled here. A Unit Test [test-web-response.php/jsonp-escape-characters] was created
                // to confirm this.

                // Create the JavaScript/JSONP Response, the resulting text from the
                // string concatenation would be in the following format: '/**/callback({"prop":"value"});'.
                // The '/**/' prefix is to prevent an attack type named the "Rosetta Flash".
                // For a details on how the attack works refer to the blog post:
                //   https://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
                $this->response_content = '/**/' . $js_function . '(' . json_encode($this->response_content) . ');';
            } else {
                // Unknown result, raise an exception
                throw new \Exception(sprintf('Unexpected Response Content Variable Type set when [%s->content()] was called. If contentType() is [json] or [jsonp] then content() can be set with a string or any type that can be encoded to a JSON string such as an object or an array, however for all other response types that do not use a file response the content() must be a [string] type. At the time of the response the contentType() was set to [%s] and the type of content set was a [%s] type.', __CLASS__, $content_type, gettype($this->response_content)));
            }
        }

        // Get the Request method, example 'GET' or 'POST'
        $method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');

        // Send Response Headers unless they have already been sent. For most PHP installations
        // they would only be sent if a route had manually called header() and has output buffering
        // turned on or manually called ob_flush(). This condition is unlikely to be meet by
        // most websites however it's possible and several unit tests show how it can happen.
        if (!headers_sent()) {
            // First check if the page is allowing the browser or client to cache the response.
            // In HTTP 1.1 Specs the Response Header Fields 'ETag' and 'Last-Modified' allow for the
            // browser or client to save cached copies of a resource or webpage and send request headers
            // 'If-None-Match' and 'If-Modified-Since' to determine if the content needs to be resent.
            if (count($this->header_fields) > 0) {
                // Get the values that will be used for the response header fields
                $etag = $this->header('ETag');
                $last_modified = $this->header('Last-Modified');
                $cache_control = $this->header('Cache-Control');
                $expires = $this->header('Expires');
                $can_send_304 = false;

                // If either value is set then check if a 304 status code is allowed for the response
                if ($etag !== null || $last_modified !== null) {
                    // Is the request method either 'GET' or 'HEAD'
                    $method_matches = ($method === 'GET' || $method === 'HEAD');
                    // Make sure the status code to be returned is 200 or more but less than 300
                    $status_code_is_200_range = ($this->status_code === null || ($this->status_code >= 200 && $this->status_code < 300));
                    // Check Response headers indicating if the user is not allowed to cache the response.
                    // If any of these headers are set then do not send a 304. See noCache() for setting these.
                    // Each of the lines below have been unit tested individually by first setting
                    // [$user_can_cache] to true. This specific logic is not required by HTTP 1.1 Specs
                    // but rather based on logic that if the server is instructing the client to not
                    // store cached copied then it will never send a 304 response.
                    $user_can_cache = ($cache_control === null || strpos($cache_control, 'no-store') === false);
                    $user_can_cache = ($user_can_cache && ($expires === null || (string)$expires !== '0'));
                    $user_can_cache = ($user_can_cache && ((($pragma = $this->header('Pragma')) === null || $pragma !== 'no-cache')));
                    // A 304 response can only be sent if all of the above statements return true
                    $can_send_304 = ($method_matches && $status_code_is_200_range && $user_can_cache);
                }

                // If defined handle the 'ETag' Response Header and 'If-None-Match' Request Header
                // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
                if ($etag !== null) {
                    // If the ETag is defined as a closure function then call the function
                    // with the response content as a parameter to generate the etag.
                    if ($etag instanceof \Closure) {
                        // Make sure this is not a file response
                        if ($this->response_file !== null) {
                            throw new \Exception('Etag must not be defined as a closure function for file responses when calling the function file(). To specify an etag for file responses use the $cache_type parameter of the file() function.');
                        }

                        // Validate that the closure function returns a string
                        $etag_value = call_user_func($etag, $this->response_content);
                        if (!is_string($etag_value)) {
                            throw new \Exception(sprintf('The ETag function defined by the app should return a string but instead returned a [%s]', gettype($etag_value)));
                        }

                        // Reset the ETag value using the etag() function
                        // because so that it will correctly format the value.
                        $this->etag($etag_value, $this->etag_type);
                        $etag = $this->etag();
                    }

                    // Get the request header value from the $_SERVER superglobal
                    $if_none_match = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : null);

                    // Compare request header value with the response value, if they match
                    // then set the status code to 304 'Not Modified'
                    if ($if_none_match !== null && $can_send_304) {
                        // ETag specs allow for a request to send up one or more
                        // ETags using the format '"etag"' or '"etag1", "etag2"' so
                        // split the request value to an array and check each item.
                        // In reality all web browsers cache only one copy and send
                        // one ETag so this might only happen if using a special cache
                        // server or software to cache multiple copies of a page.
                        //
                        // A special 'If-None-Match' value of '*' exists to match any
                        // resource however based on HTTP Protocol it is intended
                        // only on being used for PUT requests. Many popular frameworks
                        // incorrectly send a 304 for '*' GET Requests however to
                        // correctly handle it the site would need to have custom logic
                        // in place check if the resource exists or not and then return
                        // either a 304 Response or 412 'Precondition Failed' Response.
                        //
                        // Convert the string to an array and trim whitespace from each
                        // item then compare the item to the request header.
                        $items = array_map('trim', explode(',', $if_none_match));
                        foreach ($items as $item) {
                            if ($item === $etag) {
                                $this->status_code = 304;
                                break;
                            }
                        }
                    }
                }

                // If defined handle the 'Last-Modified' Response Header and 'If-Modified-Since' Request Header
                // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.29
                // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.25
                if ($last_modified !== null) {
                    // Last-Modified will be sent as a 'HTTP-date' format which looks like:
                    // [Last-Modified: Tue, 25 Aug 2015 00:10:58 GMT]. When the value is set
                    // from the function lastModified() it ends up a Unix Timestamp (int)
                    // which is then converted into the proper format, however ff the value is
                    // a string then it was set from the header() function of this class.
                    if (!is_int($last_modified)) {
                        // Parse the string date value into a Unix Timestamp
                        $last_modified_value = strtotime($last_modified);

                        // If the value returned is false then it was not a valid date string.
                        // Throw an exception with a helpful message for the developer.
                        if ($last_modified_value === false) {
                            throw new \Exception(sprintf('Invalid value for the header [Last-Modified] which was likely set by calling the header() function. If using a string value then the parameter must be a valid value for the php function strtotime(), the value specified was: [%s]', $last_modified));
                        }

                        // Set the time value to the Unix Timestamp value, note the value
                        // is not set above so that the original value can be used in an
                        // error message if needed.
                        $last_modified = $last_modified_value;
                    }

                    // If status code of 304 was not set from the etag then check the date last modified
                    // in comparison to the request header 'If-Modified-Since'. This only gets checked if
                    // and etag was not defined.
                    if ($this->status_code !== 304 && $etag === null && $can_send_304) {
                        // Get the request header value from the $_SERVER superglobal
                        $if_modified_since = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : null);

                        // Compare request header time value with the response value, if they match
                        // set the status code to 304 'Not Modified'. If an invalid request header
                        // is sent then this will not cause an exception but rather evaluate to
                        // false. This is confirmed with a unit test.
                        if ($last_modified === strtotime($if_modified_since)) {
                            $this->status_code = 304;
                        }
                    }

                    // Update the 'Last-Modified' header so the date format will look like 'Wed, 10 Jun 2015 23:48:48 GMT'
                    $this->header('Last-Modified', gmdate('D, d M Y H:i:s T', $last_modified));
                }

                // Handle the 'Expires' Header so that if specified as a time value it gets
                // converted to the correct date/time format.
                if ($expires !== null && is_int($expires)) {
                    $this->header('Expires', gmdate('D, d M Y H:i:s T', $expires));
                }                
            }

            // First send the response status code if one is set, by default the web
            // server will return 200 so in most cases this doesn't need to be set.
            // The functions statusCode() and exceptionHandler() set the value for this.
            if ($this->status_code !== null) {
                // The function http_response_code() is available in PHP 5.4 and later.
                // A polyfill is provided for PHP 5.3.
                if (function_exists('http_response_code')) {
                    http_response_code($this->status_code);
                } else {
                    // If your site requires PHP 5.3 and uses additional status codes
                    // then you can get them from the PHP Source Code:
                    // https://github.com/php/php-src/blob/master/main/http_status_codes.h
                    $status_code_text = array(
                        200 => 'OK',
                        201 => 'Created',
                        202 => 'Accepted',
                        204 => 'No Content',
                        205 => 'Reset Content',
                        301 => 'Moved Permanently',
                        302 => 'Found',
                        303 => 'See Other',
                        304 => 'Not Modified',
                        307 => 'Temporary Redirect',
                        308 => 'Permanent Redirect',
                        404 => 'Not Found',
                        429 => 'Too Many Requests',
                        500 => 'Internal Server Error',
                    );

                    // Add status to the header response, for example 'HTTP/1.1 200 OK'
                    if (isset($status_code_text[$this->status_code])) {
                        // Even though this framework specifies HTTP/1.1 headers it's possible
                        // that the server version could be HTTP/1.0 or HTTP/2.0 so the SERVER_PROTOCOL
                        // is used to get the supported version from the actual web server. At
                        // the time of development (late 2015) it not common for servers to support
                        // only HTTP/1.0, however if they do specifying another version can cause
                        // problems. For examples of this refer to PHP documentation at:
                        //   http://php.net/manual/en/function.header.php
                        header(sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $this->status_code, $status_code_text[$this->status_code]));
                    }
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

        // After headers are sent output the response unless the
        // request method was HEAD or one of the the following Status Codes:
        // [204 'No Content'], [205 'Reset Content'], or [304 'Not Modified'].
        // HTTP Specs for a HEAD Request:
        //   http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        //   The HEAD method is identical to GET except that the server MUST NOT
        //   return a message-body in the response. The meta information contained
        //   in the HTTP headers in response to a HEAD request SHOULD be identical
        //   to the information sent in response to a GET request.
        // Based on the specs the response headers should be the same however in reality
        // they usually are not because the actual web servers (e.g.: Apache or IIS)
        // will add additional headers based on the content. Specifically FastSitePHP
        // does not modify or include the 'Content-Length' header for most responses
        // because it is handled by the web server. It is easy to calculate what the 
        // uncompressed content length would be however HTML content is often gzipped 
        // by the web server so the actual content length of the response is not known 
        // with PHP Code if compression is handled by the server. However FastSitePHP 
        // does send the 'Content-Length' for file responses, see more below.
        //
        // Additionally if this check is not in place and content is sent to output
        // then tested versions of both IIS and Apache prevent the content from being
        // submitted for both HEAD requests and 304 Responses. Status Codes between
        // 100 and 199 indicate informational messages also do not included content but
        // they are not checked here because FastSitePHP doesn't handle them. They are
        // handled at a lower-level by the web server software. The unit test
        // [test-web-request.php/post-data-12] with a Windows C# Program or
        // Mac/Linux Shell Script can be used to confirm that the Web Server being
        // tested properly handles status code 100. See comments on that unit test
        // for more info related to status code 100.
        if ($method !== 'HEAD'
            && ($this->status_code === null
                || ($this->status_code !== 304 && $this->status_code !== 204 && $this->status_code !== 205))) {

            if ($this->response_file === null) {
                echo $this->response_content;
            } else {
                // Send [Content-Length] Response Header based on the file size.
                // If the file type ends up with a content type such as 'text/html' 
                // then the web server will likely buffer all output and overwrite this,
                // however if the file type is a file download, video streaming, etc 
                // then the header value will come from here and the web server will
                // stream the file.
                header('Content-Length: ' . filesize($this->response_file));
                
                // Make sure any previous output has been cleared. Without calling
                // [ob_end_clean()] at least once [readfile()] would attempt to load
                // the entire file in memory rather than streaming it.
                while (ob_get_level()) {
                    ob_end_clean();
                }

                // This streams with a buffer size of 8192 based on PHP-SRC
                readfile($this->response_file);
            }
        }
    }
}