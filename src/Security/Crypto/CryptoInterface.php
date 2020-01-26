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

namespace FastSitePHP\Security\Crypto;

/**
 * This class provides an interface for crypto classes [Encryption, 
 * FileEncryption, SignedData, and JWT]. Each of these classes has a 
 * simple [generateKey()] function that can be used to generate secure keys.
 * 
 * By default null is returned data cannot be decrypted or verified however
 * by setting [exceptionOnError(true)] then the actual exception will be 
 * thrown. This allows for [if...then] logic to be used rather than exception
 * handling by the calling app.
 */
interface CryptoInterface
{
    public function generateKey();
    public function exceptionOnError($value = null);
}