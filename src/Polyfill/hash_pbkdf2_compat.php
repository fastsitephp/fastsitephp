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

/**
 * Versions of PHP below 5.5 do not include the function hash_pbkdf2().
 * This file can be included in a project as a polyfill for compatibility.
 * It is tested with PHP 5.4 and with PHP 5.3 using the bin2hex()
 * polyfill in this folder.
 * 
 * All code in this function including error validation
 * and messages are based on the defined standard and PHP source:
 *     https://github.com/php/php-src/blob/master/ext/hash/hash.c
 *     PHP_FUNCTION(hash_pbkdf2)
 * 
 * Additional links:
 *     https://tools.ietf.org/html/rfc2898
 *     https://en.wikipedia.org/wiki/List_of_PBKDF2_implementations
 */

if (!function_exists('hash_pbkdf2')) {
    /**
     * Generate a PBKDF2 key derivation of a supplied password
     * 
     * @param string $algo
     * @param string $password
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * @param bool $raw_output 
     * @return string|bool
     * @link http://php.net/manual/en/function.hash-pbkdf2.php
     */
    function hash_pbkdf2($algo, $password, $salt, $iterations, $length = 0, $raw_output = false)
    {
        // Simply cast to the correct type rather than validate parameter data types
        $algo = strtolower($algo);
        $password = (string)$password;
        $salt = (string)$salt;
        $iterations = (int)$iterations;
        $length = (int)$length;

        // Validation messages match exactly to php-src however Non-cryptographic hashing algorithms
        // are not validated for here as that was not added until PHP 7.2 and "Supplied salt is too long"
        // is not checked for as it requires huge strings for testing and would likely not happen.
        if (!in_array($algo, \hash_algos(), true)) {
            $error = sprintf('%s(): Unknown hashing algorithm: %s', __FUNCTION__, $algo);
            trigger_error($error, E_USER_WARNING);
            return false;
        } elseif ($iterations <= 0) {
            $error = sprintf('%s(): Iterations must be a positive integer: %d', __FUNCTION__, $iterations);
            trigger_error($error, E_USER_WARNING);
            return false;
        } elseif ($length < 0) {
            $error = sprintf('%s(): Length must be greater than or equal to 0: %d', __FUNCTION__, $length);
            trigger_error($error, E_USER_WARNING);
            return false;
        }

        // Determine hash length and number of blocks that need to be processed
        $hash_length = strlen(hash($algo, '', true));
        $block_count = ($length === 0 ? 1 : ceil($length / $hash_length));
        if (!$raw_output) {
            $block_count = ceil($block_count / 2);
        }
        
        // Build the Derived Key
        $dk = '';
        for ($block = 1; $block <= $block_count; $block++) {
            // Convert $block to a 32-bit unsigned long (big endian byte order)
            // Equivalent to (chr($block >> 24) . chr($block >> 16) . chr($block >> 8) . chr($block))
            // however with PHP the function [pack()] is faster.
            $t = $salt . pack('N', $block);

            // Calculate the first hmac round
            $u = $t = hash_hmac($algo, $t, $password, true);

            // Calculate additional hmac rounds from 1 to $iterations
            for ($i = 1; $i < $iterations; $i++) {
                $t ^= ($u = hash_hmac($algo, $u, $password, true));
            }

            // Add result to the Derived Key
            $dk .= $t;
        }

        // Return either string bytes or hex string
        if ($length === 0) {
            return ($raw_output ? $dk : bin2hex($dk));
        }
        if (extension_loaded('mbstring')) {
            return mb_substr(($raw_output ? $dk : bin2hex($dk)), 0, $length, '8bit');
        }
        return substr(($raw_output ? $dk : bin2hex($dk)), 0, $length);
    }
}
