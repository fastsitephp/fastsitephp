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

/**
 * Versions of PHP below 5.5 do not include the function json_last_error_msg().
 * This file can be included in a project as a polyfill for compatibility.
 * 
 * PHP Source:
 *     https://github.com/php/php-src/blob/master/ext/json/json.c
 */

if (!function_exists('json_last_error_msg')) {
    /**
     * Returns the error string of the last json_encode() or json_decode() call
     * 
     * @link http://php.net/manual/en/function.json-last-error-msg.php
     * @return string
     */
    function json_last_error_msg() {        
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'No error';
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'State mismatch (invalid or malformed JSON)';
            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error, possibly incorrectly encoded';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
}