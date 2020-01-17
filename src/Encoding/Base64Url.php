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
 * Base64-URL Safe Encoding
 * 
 * PHP has built-in support for Base64 Encoding but not Base64-URL encoding.
 * This class encodes and decodes Base64-URL safe strings.
 * 
 * The difference between Base64 and Base64-URL is that Base64-URL uses
 * '-_' characters instead of '+/' and doesn't include '=' padding.
 * 
 * @link https://en.wikipedia.org/wiki/Base64
 * @link https://tools.ietf.org/html/rfc4648#section-5
 * @link https://tools.ietf.org/html/rfc7515#appendix-C
 */
class Base64Url
{
    /**
     * Encode a string as Base64-URL
     * 
     * @param string $data
     * @return string
     */
    public static function encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decode Base64-URL to a string
     * 
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public static function decode($data)
    {
        if ($data !== null && !is_string($data)) {
            throw new \Exception(sprintf('Invalid parameter of type [%s] for [%s::%s()], only strings or null can be decoded.', gettype($data), __CLASS__, __FUNCTION__));
        }

        // Calculate Padding to Add
        $padding = strlen($data) % 4;
        if ($padding !== 0) {
            $padding = 4 - $padding;
        }
        
        // Convert to Standard Base64 String
        if ($padding === 0) {
            $data = strtr($data, '-_', '+/');
        } else {
            $data = strtr($data, '-_', '+/') . str_repeat('=', $padding);
        }

        // Decode from Standard Base64 String
        return base64_decode($data, true);
    }    
}