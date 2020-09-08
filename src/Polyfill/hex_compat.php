<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (https://conradsollitt.com)
 * @license  MIT License
 */

/**
 * PHP 5.3 does not include functions bin2hex() or hex2bin() so this file
 * can be included in a project to add them as a polyfill for compatibility.
 *
 * Error validation and messages are based
 * on error warnings from the PHP Source:
 *     https://github.com/php/php-src/blob/master/ext/standard/string.c
 *     PHP_FUNCTION(bin2hex)
 *     PHP_FUNCTION(hex2bin)
 */

if (!function_exists('bin2hex')) {
    /**
     * Convert binary data into hexadecimal representation
     *
     * @link http://php.net/manual/en/function.bin2hex.php
     * @param string $str
     * @return string|false
     */
    function bin2hex($str)
    {
        // Validation
        if (!is_scalar($str) && $str !== null) {
            trigger_error(sprintf('%s() expects parameter 1 to be string, %s given', __FUNCTION__, gettype($str)), E_USER_WARNING);
            return false;
        }

        // Convert from binary string to hex string using pack()
        $array = unpack('H*', $str);
        return $array[0];
    }
}

if (!function_exists('hex2bin')) {
    /**
     * Decodes a hexadecimally encoded binary string
     *
     * @link http://php.net/manual/en/function.hex2bin.php
     * @param string $data
     * @return string|false
     */
    function hex2bin($data)
    {
        // Parameter Type Validation
        if (!is_scalar($data) && $data !== null) {
            trigger_error(sprintf('%s() expects parameter 1 to be string, %s given', __FUNCTION__, gettype($data)), E_USER_WARNING);
            return false;
        }

        // Convert to string and check that the string is a hex string
        $data = (string)$data;
        if (strlen($data) % 2 !== 0) {
            trigger_error(sprintf('%s(): Hexadecimal input string must have an even length', __FUNCTION__), E_USER_WARNING);
            return false;
        } elseif ($data !== '' && !ctype_xdigit($data)) {
            trigger_error(sprintf('%s(): Input string must be hexadecimal string', __FUNCTION__), E_USER_WARNING);
            return false;
        }

        // Convert from hex string to binary string using pack()
        return pack('H*', $data);
    }
}
