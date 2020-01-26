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

namespace FastSitePHP\Net;

/**
 * This class is returned from HTTP Request functions when using the [HttpClient] class.
 */
class HttpResponse
{
    /**
     * Error code if there is a major failure with an HTTP Request 
     * such as a timeout or SSL Cert Error.  0 if no error.
     * 
     * The error code is the value returned by [curl_errno()].
     * 
     * @var int
     */
    public $error = 0;

    /**
     * Status Code of the Response [200, 404, 500, etc].
     * @var int|null
     */
    public $status_code = null;

    /**
     * HTTP Response Headers
     * @var array|null
     */
    public $headers = null;

    /**
     * Response Body as a Text String. PHP Strings are array's of bytes
     * so binary responses will also be in string format.
     * @var string|null
     */
    public $content = null; 

    /**
     * Response Body parsed to an Array for JSON Responses. This can be turned
     * off by setting the option [parse_json = false] from [HttpClient->request()].
     * @var array|null
     */
    public $json = null; 

    /**
     * Array of detailed information for the request and response from [curl_getinfo()].
     * Example ($res->info['CURLINFO_TOTAL_TIME']) if set will return the total transaction
     * time in seconds.
     * 
     * @link http://php.net/manual/en/function.curl-getinfo.php
     * @var array|null
     */
    public $info = null;
}