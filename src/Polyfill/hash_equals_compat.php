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
 * PHP 5.5 and below does not include the function hash_equals().
 * This file can be included in a project as a polyfill for compatibility.
 * 
 * PHP Source Version:
 *     https://github.com/php/php-src/blob/master/ext/hash/hash.c
 *     PHP_FUNCTION(hash_equals)
 */

if (!function_exists('hash_equals')) {
    /**
     * Timing attack safe string comparison.
     * Compares two strings using the same time whether they're equal or not.
     * 
     * @param string $known_string 
     * @param string $user_string
     * @return bool
     * @link http://php.net/manual/en/function.hash-equals.php
     */
    function hash_equals($known_string, $user_string) {
        // Only strings are allowed
        if (!is_string($known_string)) {
            trigger_error(sprintf('%s(): Expected known_string to be a string, %s given', __FUNCTION__, gettype($known_string)), E_USER_WARNING);
            return false;
        } elseif (!is_string($user_string)) {
            trigger_error(sprintf('%s(): Expected user_string to be a string, %s given', __FUNCTION__, gettype($user_string)), E_USER_WARNING);
            return false;
        }

        // Return false if string lengths are not equal
        $len = strlen($known_string);
        if ($len !== strlen($user_string)) {
            return false;
        }

        // Compare every character of both strings using Bitwise Operators.
        $result = 0;
        for ($j = 0; $j < $len; $j++) {
            $result |= ord($known_string[$j]) ^ ord($user_string[$j]);
        }
        return ($result === 0);
    }
}
