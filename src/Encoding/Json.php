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

namespace FastSitePHP\Encoding;

/**
 * Encode to JSON and decode from JSON.
 *
 * This classes uses the built-in [json_encode()] and [json_decode()] functions
 * however rather than returning [false] or [null] on errors an exception is thrown.
 *
 * This class will also create a polyfill function for [json_last_error_msg()]
 * on old versions of PHP if there is an error, and provides compatible code for
 * JSON_BIGINT_AS_STRING when decoding. By default PHP converts large integers
 * to floating-point numbers. When this class is used to decode large ints are
 * converted to strings to prevent data loss.
 *
 * For most code calling the built-in [json_encode()] and [json_decode()] functions
 * are recommend over this. The main reason to use this class is for JSON_BIGINT_AS_STRING
 * support on old versions of PHP.
 */
class Json
{
    /**
     * Encode data to a JSON String. By default when using PHP 5.4+ JSON_UNESCAPED_UNICODE
     * is used for [$options]. [$options] can be set to any valid value for [json_encode()].
     *
     * @param mixed $data
     * @param int|null $options (Optional)
     * @return string
     * @throws \Exception
     */
    public static function encode($data, $options = null)
    {
        if ($options === null && PHP_VERSION_ID >= 50400) {
            $options = JSON_UNESCAPED_UNICODE;
        }
        $json = json_encode($data, $options);
        if ($json === false) {
            if (PHP_VERSION_ID < 50500) {
                require_once __DIR__ . '/../Polyfill/json_last_error_msg_compat.php';
            }
            throw new \Exception('Error - Unable to encode data using PHP function json_encode(). Error returned: ' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * Decode a JSON string back to data. This is equivalent to using the
     * following settings with the built-in function:
     *
     *     $data = json_decode($text, true, 512, JSON_BIGINT_AS_STRING);
     *
     * When objects are decoded they are returned as PHP Associative Arrays.
     *
     * @param string $text
     * @return mixed
     * @throws \Exception
     */
    public static function decode($text)
    {
        // JSON (Object or Array)
        // By default PHP converts large integers to floating-point numbers.
        // Make sure large ints are converted to strings to prevent data loss.
        if (PHP_VERSION_ID >= 50400 && defined('JSON_BIGINT_AS_STRING')) {
            $data = json_decode($text, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            // PHP 5.3 and specific OS Versions of PHP 5.5 do not support
            // JSON_BIGINT_AS_STRING. Use regex to check numbers that
            // may be large and if so convert them to strings otherwise
            // keep them as integers.
            $large_int_len = strlen((string)PHP_INT_MAX);
            $text = preg_replace_callback(
                '/(-?\d{' . $large_int_len . ',})/',
                function ($matches) {
                    if (filter_var($matches[0], FILTER_VALIDATE_INT) === false) {
                        return '"' . $matches[0] . '"';
                    } else {
                        return $matches[0];
                    }
                },
                $text
            );
            $data = json_decode($text, true);
        }

        // Is the JSON valid? [json_decode()] returns null if parsing fails.
        // It can also return null if [json_encode()] was called with null,
        // so check for an error.
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            if (PHP_VERSION_ID < 50500) {
                require_once __DIR__ . '/../Polyfill/json_last_error_msg_compat.php';
            }
            throw new \Exception('Error decoding JSON Data: ' . json_last_error_msg());
        }
        return $data;
    }
}
