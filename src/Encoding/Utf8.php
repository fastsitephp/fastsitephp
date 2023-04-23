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

namespace FastSitePHP\Encoding;

/**
 * UTF-8 Encoding
 *
 * For most PHP sites UTF-8 is the default character set and using this class
 * is not needed. This class is useful for forcing different character sets
 * to UTF-8 prior to JSON or XML encoding.
 *
 * The PHP function [json_encode()] requires UTF-8 prior to encoding,
 * otherwise it will trigger an error:
 *     JSON_ERROR_UTF8 - 'Malformed UTF-8 characters, possibly incorrectly encoded'
 *
 * For example, IBM i-Series Servers use EBCDIC character encoding which
 * correctly translates to UTF-8 for most characters, however it will trigger
 * the error for some characters.
 *
 * Another example is on FastSitePHP's Encryption Unit Tests, an array of binary
 * strings need to be converted to UTF-8 prior to JSON encoding and hashing.
 * Using this for binary data in most situations is not recommended, rather
 * if you are working with binary files or data use binary format directly.
 *
 * @link https://en.wikipedia.org/wiki/UTF-8
 */
class Utf8
{
    /**
     * Encode data to UTF-8; this function recursively encodes data on Arrays and Objects.
     *
     * With PHP 8.2 and above this function uses the following encoding function:
     *     iconv('windows-1252', 'UTF-8', $data)
     * And below PHP 8.2:
     *     utf8_encode($data)
     *
     * If you have different needs for a special character set then copying and
     * modifying this class is recommended.
     *
     * @param mixed $data
     * @return string
     */
    public static function encode($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::encode($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = self::encode($value);
            }
        } elseif (is_string($data)) {
            if (PHP_VERSION_ID >= 80200) {
                return iconv('windows-1252', 'UTF-8', $data);
            }
            return utf8_encode($data);
        }
        return $data;
    }
}
