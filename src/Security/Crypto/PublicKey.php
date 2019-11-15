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

namespace FastSitePHP\Security\Crypto;

/**
 * Public Key Generator
 * 
 * Used to generate RSA Keys which can be used for JWT Signing. Function 
 * parameters allow for generation of additional public and private key types.
 * 
 * @link https://en.wikipedia.org/wiki/RSA_(cryptosystem)
 * @link https://tools.ietf.org/html/rfc3447
 */
class PublicKey
{
    /**
     * Return the default config options used when generating a new Key Pair.
     * This an array of options set for a 2048-bit RSA Key.
     * 
     * 2048-bit is used as because it is provides a combination of acceptable
     * speed for JWT and strong security. If a JWT needs to be signed and 
     * validated after the year 2030 then a 3072-bit key is recommended, 
     * however 3072-bit keys are much slower to create.
     * 
     * On Windows this will attempt to find and set the [openssl.cnf] file 
     * for the instance of PHP that is running. This option is generally
     * required in order to generate RSA Key Pairs on Windows. The file if
     * found by default will exist at a location such as:
     *     C:\Program Files\PHP\v7.2\extras\ssl\openssl.cnf
     * 
     * @link https://en.wikipedia.org/wiki/Key_size
     * @param int $bits - Defaults to 2048
     * @return array
     */
    public static function defaultConfig($bits = 2048)
    {
        $config = array(
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        );

        if (PHP_OS === 'WINNT') {
            $path = php_ini_loaded_file();
            if ($path !== false) {
                $path = dirname($path) . '\extras\ssl\openssl.cnf';
                if (is_file($path)) {
                    $config['config'] = $path;
                }
            }
        }

        return $config;
    }

    /**
     * Generate a new RSA Key Pair
     * 
     * @param int $bits - Defaults to 2048
     * @return array - [private_key, public_key]
     */
    public static function generateRsaKeyPair($bits = 2048)
    {
        return self::generateKeyPair(self::defaultConfig($bits)); 
    }

    /**
     * Generate a new Public/Private Key Pair
     *
     * @link http://php.net/manual/en/function.openssl-pkey-new.php
     * @param array $config @config - See config options from the PHP link
     * @return array - [private_key, public_key]
     * @throws \Exception
     */
    public static function generateKeyPair(array $config)
    {
        // Generate a New Key Pair
        $resource = \openssl_pkey_new($config);
        if ($resource === false) {
            $error = sprintf('Error with [%s::%s()] at [openssl_pkey_new()]:', __CLASS__, __FUNCTION__);
            while ($msg = openssl_error_string()) {
                $error .= "\n" . $msg;
            }
            throw new \Exception($error);
        }

        // Get Private Key
        $result = \openssl_pkey_export($resource, $private_key, null, $config);
        if ($result === false) {
            $error = sprintf('Error with [%s::%s()] at [openssl_pkey_export()]:', __CLASS__, __FUNCTION__);
            while ($msg = openssl_error_string()) {
                $error .= "\n" . $msg;
            }
            throw new \Exception($error);
        }

        // Get Public Key
        $public_key = \openssl_pkey_get_details($resource);
        $public_key = $public_key['key'];

        // Success
        return array($private_key, $public_key);
    }
}
