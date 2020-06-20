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

use FastSitePHP\Net\HttpResponse;

/**
 * HTTP Client
 *
 * This class provides a simple and secure API and wraps either CURL or
 * PHP Stream Context depending on what is available. Common HTTP requests
 * can be made with one line of code using static helper functions.
 */
class HttpClient
{
    /**
     * Some OS's (often Windows and Mac) will not support HTTPS by default unless
     * a file is downloaded and [php.ini] is updated outside of PHP. The downloaded
     * file handles [HTTPS] validation. A copy of the file [cacert.pem] is included
     * in this directory in case it doesn't exist on the OS, however the file needs
     * to be updated from time to time to keep current. This property allows an
     * app to specify it's own version of the file in another directory.
     *
     * @link https://curl.haxx.se/docs/caextract.html - Download URL for [cacert.pem]
     * @link http://www.php.net/manual/en/curl.configuration.php#ini.curl.cainfo
     * @var string
     */
    public static $cainfo_path = __DIR__ . '/cacert.pem';

    /**
     * Allow HTTPS to be ignored. This should only be used as last resort in
     * trusted environments as it breaks security. Instead it is better to
     * set [HttpClient::$cainfo_path].
     *
     * @var bool
     */
    public static $allow_insecure = false;

    /**
     * Parse and Save Response Headers as Content is downloaded
     * @var array|null
     */
    private $res_headers = null;

    /**
     * Submit a GET Request and optionally specify an array of Request Headers.
     *
     * @param string $url
     * @param array|null $headers - Optional array of [key => value]
     * @return HttpResponse
     */
    public static function get($url, array $headers = null)
    {
        $http = new HttpClient();
        return $http->request($url, array('headers' => $headers));
    }

    /**
     * Submit a POST Request with JSON Data as the Request Body.
     *
     * @param string $url
     * @param array|object $data
     * @param array|null $headers
     * @return HttpResponse
     */
    public static function postJson($url, $data, array $headers = null)
    {
        $http = new HttpClient();
        return $http->request($url, array(
            'method' => 'POST',
            'headers' => $headers,
            'json' => $data,
        ));
    }

    /**
     * Submit a POST Request with Form Data. If sending a form with files and
     * form type 'multipart/form-data' use the PHP class CURLFile.
     * See examples for more.
     *
     * @link http://php.net/manual/en/class.curlfile.php
     * @param string $url
     * @param array|object $fields
     * @param array|null $headers
     * @return HttpResponse
     */
    public static function postForm($url, $fields, array $headers = null)
    {
        $http = new HttpClient();
        return $http->request($url, array(
            'method' => 'POST',
            'headers' => $headers,
            'form' => $fields,
        ));
    }

    /**
     * Submit a GET Request and save the Response Body to a file.
     * [HttpResponse->content] will contain the saved file path.
     *
     * @param string $url
     * @param string $path - Full path of the file to save
     * @param array|null $headers
     * @return HttpResponse
     */
    public static function downloadFile($url, $path, array $headers = null)
    {
        $http = new HttpClient();
        return $http->request($url, array(
            'headers' => $headers,
            'save_file' => $path,
        ));
    }

    /**
     * Submit a Request and return an HttpResponse object.
     *
     * Options:
     *   'mode' = null or 'curl' or 'php'
     *       Defaults to null which uses curl. This option is defined for unit testing. Leaving the default works best.
     *   'method' = 'GET', 'POST', 'PUT', etc
     *       Defaults to 'GET'
     *   'headers' = Array of Request Headears [key => value]
     *   'json' = Array or Object of Data to send with the Request
     *   'form' = Array or Object of Form Data, see additional comments in [postForm()]
     *   'send_file' = Full path of a file to send as the Request Body
     *   'save_file' = Full path where to save the response, if set then [HttpResponse->content] will contain the saved file path
     *   'timeout' = Timeout in Seconds
     *       Defaults to 0 which means the code stops until the request completes.
     *       When using curl mode this value applies to both the inital connection and the curl request.
     *   'parse_json' = true/false - By default if a JSON response is returned the [$res->content]
     *       will be parsed to [$res->json] as an Associative Array. It can be turned off by setting this to false
     *
     * @param string $url
     * @param array|null $options
     * @return HttpResponse
     */
    public function request($url, array $options = null)
    {
        $mode = (isset($options) && isset($options['mode']) ? $options['mode'] : null);
        if ($mode !== 'php' && function_exists('curl_init')) {
            return $this->requestWithCurl($url, $options);
        }
        return $this->requestWithPhp($url, $options);
    }

    /**
     * Used to make Requests with Curl. This is the default mode.
     *
     * @link http://php.net/manual/en/function.curl-init.php
     * @link http://php.net/manual/en/function.curl-setopt.php
     * @link http://php.net/manual/en/function.curl-getinfo.php
     * @param string $url
     * @param array $options
     * @return HttpResponse
     */
    private function requestWithCurl($url, array $options)
    {
        // Init Curl Request and and define options
        $ch = curl_init($url);
        $path = null;
        $fp_out = null;
        $fp_in = null;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Require Valid Certs or Allow Insecure Mode for HTTPS
        if (stripos($url, 'https://') === 0) {
            if (self::$allow_insecure) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            } else {
                curl_setopt($ch, CURLOPT_CAINFO, $this->certPath());
            }
        }

        // Get passed options
        $req_headers = array();
        if ($options !== null) {
            // Request Headers
            if (isset($options['headers'])) {
                $req_headers = $options['headers'];
            }

            // Optional timeout in seconds
            if (isset($options['timeout'])) {
                $timeout = (int)$options['timeout'];
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            }

            // Request Method - GET, POST, HEAD, PUT, PATH, DELETE
            $method = null;
            if (isset($options['method'])) {
                $method = $options['method'];
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, 1);
                } elseif ($method === 'HEAD') {
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                } else {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
            }

            // Request Body - Data to Send
            $has_file = false;
            // JSON
            if (isset($options['json'])) {
                $req_headers['Content-Type'] = 'application/json; charset=UTF-8';
                $json = json_encode($options['json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            // Form
            } elseif (isset($options['form'])) {
                if (PHP_VERSION_ID >= 50500) {
                    foreach ($options['form'] as $key => $value) {
                        if (is_object($value) && get_class($value) === 'CURLFile') {
                            $has_file = true;
                            break;
                        }
                    }
                }
                if ($has_file) {
                    $req_headers['Content-Type'] = 'multipart/form-data';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['form']);
                } else {
                    $req_headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    $form = http_build_query($options['form'], '', '&');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
                }
            } elseif (isset($options['send_file'])) {
                // File
                $send_file = $options['send_file'];
                curl_setopt($ch, CURLOPT_PUT, true);
                if ($method !== null) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($send_file));
                curl_setopt($ch, CURLOPT_INFILE, ($fp_in = fopen($send_file, 'r')));
            }

            // Save as a File Download
            if (isset($options['save_file'])) {
                $path = $options['save_file'];
                $fp_out = fopen($path, 'w+');
                curl_setopt($ch, CURLOPT_FILE, $fp_out);
            }
        }

        // Add User-Agent unless already specified
        if (!isset($req_headers['User-Agent'])) {
            $user_agent = $this->userAgent(true, $options);
            if ($user_agent !== null) {
                $req_headers['User-Agent'] = $user_agent;
            }
        }

        // Request Headers
        if (count($req_headers) > 0) {
            $list = array();
            foreach ($req_headers as $key => $value) {
                $list[] = "{$key}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $list);
        }

        // Save response headers as they are read
        // Modified from: https://stackoverflow.com/a/41135574
        // Retains duplicate headers and complies with RFC822 and RFC2616.
        $this->res_headers = array();
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len; // Ignore Invalid Header Line
            }
            $name = trim($header[0]);
            if (!array_key_exists($name, $this->res_headers)) {
                $this->res_headers[$name] = trim($header[1]);
            } else {
                $this->res_headers[$name] = (array)$this->res_headers[$name];
                $this->res_headers[$name][] = trim($header[1]);
            }
            return $len;
        });

        // Make the Request
        $content = curl_exec($ch);

        // Build the HttpResponse
        $res = new HttpResponse();
        $res->error = curl_errno($ch);
        if ($res->error) {
            $res->error = curl_error($ch);
            if ($res->error === '') {
                $error = 'Error from CURL - curl_errno(): ' . (string)curl_errno($ch) . '. See: ' . "\n";
                $error .= 'https://curl.haxx.se/libcurl/c/libcurl-errors.html';
                $res->error = $error;
            }
        } else {
            $res->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $res->headers = $this->res_headers;
            $res->content = $content;
            $res->info = curl_getinfo($ch);
        }
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, null);
        curl_close($ch);
        if ($fp_out !== null) {
            fclose($fp_out);
            if ($res->content !== false) {
                $res->content = realpath($path);
            }
        }
        if ($fp_in !== null) {
            fclose($fp_in);
        }

        // Return HttpResponse
        return $this->parseJson($res, $options);
    }

    /**
     * Used to make a Request with PHP built-in functions [stream_context_create]
     * and [file_get_contents]. This is only used if curl is not installed or
     * if the mode is specifically set.
     *
     * @link http://php.net/manual/en/function.file-get-contents.php
     * @link http://php.net/manual/en/function.stream-context-create.php
     * @link http://php.net/manual/en/context.http.php
     * @link http://php.net/manual/en/context.ssl.php
     * @link http://php.net/manual/en/reserved.variables.httpresponseheader.php
     * @param string $url
     * @param array|null $options
     * @return HttpResponse
     * @throws \Exception
     */
    private function requestWithPhp($url, $options)
    {
        $http_options = array(
            'http' => array('ignore_errors' => true),
            'ssl' => array(),
        );

        // Require Valid Certs or Allow Insecure Mode for HTTPS
        if (stripos($url, 'https://') === 0) {
            if (self::$allow_insecure) {
                $http_options['ssl']['verify_peer'] = false;
                $http_options['ssl']['verify_peer_name'] = false;
            } else {
                $http_options['ssl']['cafile'] = $this->certPath();
            }

            // Disable SSL/TLS compression to prevent the CRIME attacks.
            // This option is available as of PHP 5.5.13 and defaults to
            // [true] for PHP 5.6.
            //   https://www.php.net/manual/en/migration56.openssl.php
            if (PHP_VERSION_ID >= 50413) {
                $http_options['ssl']['disable_compression'] = true;
            }
        }

        // Get passed options
        $req_headers = array();
        if ($options !== null) {
            // Request Headers
            if (isset($options['headers'])) {
                $req_headers = $options['headers'];
            }

            // Optional timeout in seconds
            if (isset($options['timeout'])) {
                $http_options['http']['timeout'] = (int)$options['timeout'];
            }

            // Request Method - GET, POST, HEAD, PUT, PATH, DELETE
            if (isset($options['method'])) {
                $http_options['http']['method'] = $options['method'];
            }

            // Request Body - Data to Post
            if (isset($options['json'])) {
                $http_options['http']['content'] = json_encode($options['json']);
                $req_headers['Content-Type'] = 'application/json; charset=UTF-8';
            } elseif (isset($options['form'])) {
                $http_options['http']['content'] = http_build_query($options['form'], '', '&');
                $req_headers['Content-Type'] = 'application/x-www-form-urlencoded';
            } elseif (isset($options['send_file'])) {
                throw new \Exception(sprintf('Sending a files with the request is currently not supported with [%s] unless CURL is installed and used.', __CLASS__));
            }

            // Save to File Download is not supported by this function
            if (isset($options['save_file'])) {
                throw new \Exception(sprintf('Saving file downloads is currently not supported with [%s] unless CURL is installed and used.', __CLASS__));
            }
        }

        // Add User-Agent unless already specified
        if (!isset($req_headers['User-Agent'])) {
            $user_agent = $this->userAgent(false, $options);
            if ($user_agent !== null) {
                $req_headers['User-Agent'] = $user_agent;
            }
        }

        // Request Headers
        if (count($req_headers) > 0) {
            $list = array();
            foreach ($req_headers as $key => $value) {
                $list[] = "{$key}: {$value}";
            }
            $http_options['http']['header'] = $list;
        }

        // Make the request
        $res = new HttpResponse();
        $context = stream_context_create($http_options);
        $res->content = file_get_contents($url, false, $context);
        $res->headers = array();

        // Parse Headers
        // If there is a Redirect 30# response then all headers
        // will be combined so skip any headers from redirects.
        $start_save = false;
        foreach ($http_response_header as $header) {
            // Find the HTTP Status Code
            preg_match('/HTTP\/[1|2].[0|1] ([0-9]{3})/', $header, $matches);
            if ($matches) {
                $status_code = (int)$matches[1];
                if (!($status_code >= 300 && $status_code < 400)) {
                    $res->status_code = $status_code;
                    $start_save = true;
                }
            } else {
                if ($start_save) {
                    $header = explode(':', $header, 2);
                    if (count($header) >= 2) {
                        $name = trim($header[0]);
                        if (!array_key_exists($name, $res->headers)) {
                            $res->headers[$name] = trim($header[1]);
                        } else {
                            $res->headers[$name] = (array)$res->headers[$name];
                            $res->headers[$name][] = trim($header[1]);
                        }
                    }
                }
            }
        }

        // Return HttpResponse
        return $this->parseJson($res, $options);
    }

    /**
     * Automatically parse JSON to an Associative Array
     *
     * @param HttpResponse $res
     * @param array|null $options
     * @return HttpResponse
     */
    private function parseJson(HttpResponse $res, $options)
    {
        // Option turned on (defaults to true)
        $parse_json = (isset($options) && isset($options['parse_json']) ? (bool)$options['parse_json'] : true);
        if (!$parse_json) {
            return $res;
        }

        // Is there a 'Content-Type' header?
        $has_header = (isset($res->headers) && isset($res->headers['Content-Type']));
        $is_json = false;
        if ($has_header) {
            // Curl returns an array of each header on redirects
            // so get the last header if an array is returned.
            $content_type = $res->headers['Content-Type'];
            if (is_array($content_type)) {
                $content_type = $content_type[count($content_type)-1];
            }
            // Check start of the value as the following could be returned:
            //   'application/json'
            //   'application/json; charset=UTF-8'
            $is_json = (strpos($content_type, 'application/json') === 0);
        }

        // Parse JSON
        if ($is_json) {
            $res->json = json_decode($res->content, true);
        }
        return $res;
    }

    /**
     * Returns the default user agent to be used for requests when using curl.
     * By default the version of Curl or PHP is included with the Request.
     *
     * @param bool $curl
     * @param array|null $options
     * @return string|null
     */
    private function userAgent($curl, $options)
    {
        // Based on Optional Param
        $ua = (isset($options) && isset($options['user_agent']) ? $options['user_agent'] : null);
        if ($ua === false) {
            return null;
        } elseif (is_string($ua)) {
            return $ua;
        }

        // Return Default
        if ($curl) {
            $ver = curl_version();
            $ua = 'curl/' . $ver['version'];
            if (isset($ver['ssl_version'])) {
                $ua .= ' ' . $ver['ssl_version'];
            }
            if (isset($ver['host'])) {
                $ua .= ' (' . $ver['host'] . ')';
            }
        } else {
            $ua = 'php/' . phpversion();
        }
        return $ua;
    }

    /**
     * Return the location for the [cacert.pem] file (the name can vary from
     * system to system). Also see comments in for static property [cainfo_path].
     *
     * @return string
     * @throws \Exception
     */
    public function certPath()
    {
        // First get default path from openssl
        $locations = openssl_get_cert_locations();
        $default_cert_file = (isset($locations['default_cert_file']) ? $locations['default_cert_file'] : null);
        if ($default_cert_file !== null && is_file($default_cert_file)) {
            return $default_cert_file;
        }

        // If not found then check if one is specified from [php.ini].
        $path = (string)ini_get('curl.cainfo');
        if ($path === '') {
            // Get from static prop of this class
            $path = (string)self::$cainfo_path;
        }

        // Does the file exist?
        if ($path === '') {
            $error = 'Missing [php.ini] setting [curl.cainfo]. Set it in [php.ini] or download the file and set it to [%s::cainfo_path].';
            $error = sprintf($error, __CLASS__);
            throw new \Exception($error);
        } elseif (!is_file($path)) {
            $error = 'CAInfo File specified in [%s::cainfo_path] at [%s] was not found. Either it doesn\'t exist or the web user doesn\'t have permissions';
            $error = sprintf($error, __CLASS__, $path);
            throw new \Exception($error);
        }

        // Valid path found
        return realpath($path);
    }
}
