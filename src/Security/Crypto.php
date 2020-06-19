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

namespace FastSitePHP\Security;

use FastSitePHP\Security\Crypto\Encryption;
use FastSitePHP\Security\Crypto\FileEncryption;
use FastSitePHP\Security\Crypto\JWT;
use FastSitePHP\Security\Crypto\SignedData;

/**
 * FastSitePHP Crypto Class
 *
 * This is a helper class using a Facade Software Design Pattern
 * to simplify Secure Encryption, Data Signing, and JWT usage.
 *
 * This class allows for only the default secure settings of
 * each object and requires secure keys to be defined in either the
 * App Config Array or as Environment Variables.
 *
 * By default Signed Data and JSON Web Tokens are valid for 1 hour
 * and expire after. The expiration time can be changed from a
 * parameter when calling either [sign()] or [encodeJWT].
 *
 * Example usage:
 *     $app->config['ENCRYPTION_KEY'] = 'dd8daccb9e2b66321a......';
 *     $encrypted_text = Crypto::encrypt($data);
 *     $decrypted_data = Crypto::decrypt($encrypted_text);
 *
 * [$app] Config (or Environment Variables) Settings:
 *     ENCRYPTION_KEY
 *         encrypt()
 *         decrypt()
 *         encryptFile()
 *         decryptFile()
 *     SIGNING_KEY
 *         sign()
 *         verify()
 *     JWT_KEY
 *         encodeJWT()
 *         decodeJWT()
 */
class Crypto
{
    /**
     * Encrypt data (string, int, float, bool, object, or array)
     * and return the encrypted text as a base64-url string.
     *
     * @param mixed $data
     * @return string
     */
    public static function encrypt($data)
    {
        $key = self::getConfigKey('ENCRYPTION_KEY', __FUNCTION__);
        $crypto = new Encryption();
        return $crypto->encrypt($data, $key);
    }

    /**
     * Decrypt data that was encrypted using the [encrypt()] function
     * and return the original value.
     *
     * The same data type is returned so if a string was encrypted
     * a string will be returned and if an array was encrypted then
     * an array will be returned. Objects of type stdClass or custom
     * classes that were encrypted are returned as an Array (Dictionary).
     *
     * If decryption fails then null will be returned.
     *
     * @param string $encrypted_text
     * @return mixed
     */
    public static function decrypt($encrypted_text)
    {
        $key = self::getConfigKey('ENCRYPTION_KEY', __FUNCTION__);
        $crypto = new Encryption();
        return $crypto->decrypt($encrypted_text, $key);
    }

    /**
     * Sign data (string, int, float, bool, object, or array)
     * and return the signed data in one of the following formats:
     *     'base64url(data).type.base64url(signature)'
     *     'base64url(data).type.timestamp.base64url(signature)'
     *
     * The default expiration time is 1 hour; this can be changed
     * to a different time or turned off passing [null].
     *
     * @param mixed $data
     * @param null|string|float $expire_time
     * @return string
     */
    public static function sign($data, $expire_time = '+1 hour')
    {
        $key = self::getConfigKey('SIGNING_KEY', __FUNCTION__);
        $csd = new SignedData();
        return $csd->sign($data, $key, $expire_time);
    }

    /**
     * Verify data that was signed using the [sign()] function
     * and return the original value.
     *
     * The same data type is returned so if a string was signed
     * a string will be returned and if an array was signed then
     * an array will be returned. Objects of type stdClass or custom
     * classes that were signed are returned as an Array (Dictionary).
     *
     * If verification fails then null will be returned.
     *
     * @param string $signed_text
     * @return mixed
     */
    public static function verify($signed_text)
    {
        $key = self::getConfigKey('SIGNING_KEY', __FUNCTION__);
        $csd = new SignedData();
        return $csd->verify($signed_text, $key);
    }

    /**
     * Encrypt a file
     *
     * If a file is 10 MB or smaller (or any size on Windows) then it will
     * be processed in memory, otherwise the file will be processed using
     * shell commands. This allows for files of any size to be encrypted.
     *
     * If you need to encrypt files that are larger than 2 GB on a 32-Bit OS
     * then you should use the [FileEncryption] class directly like this:
     *     $crypto = new FileEncryption();
     *     $crypto->processFilesWithCmdLine(true);
     *     $crypto->encryptFile($file_path, $enc_file, $key);
     *
     * @param string $file_path - Input file to encrypt, this file will not be modified
     * @param string $enc_file - Path to save the encrypted (output) file
     * @return void
     */
    public static function encryptFile($file_path, $enc_file)
    {
        $key = self::getConfigKey('ENCRYPTION_KEY', __FUNCTION__);
        $crypto = self::setupFileCrypto($file_path);
        $crypto->encryptFile($file_path, $enc_file, $key);
    }

    /**
     * Decrypt a file that was created from [encryptFile()]. The same
     * memory and OS rules apply for file processing.
     *
     * This function has no return value so if decryption fails an
     * exception is thrown.
     *
     * @param string $enc_file - Encrypted file, this file will not be modified
     * @param string $output_file - Path to save the decrypted file
     * @return void
     */
    public static function decryptFile($enc_file, $output_file)
    {
        $key = self::getConfigKey('ENCRYPTION_KEY', __FUNCTION__);
        $crypto = self::setupFileCrypto($enc_file);
        $crypto->decryptFile($enc_file, $output_file, $key);
    }

    /**
     * Create a JSON Web Token (JWT) with a default expiration time of 1 hour.
     *
     * @param array|object $payload
     * @param null|string|int $exp_time
     * @return string
     */
    public static function encodeJWT($payload, $exp_time = '+1 hour')
    {
        $key = self::getConfigKey('JWT_KEY', __FUNCTION__);
        $jwt = new JWT();
        if ($exp_time !== null) {
            $payload = $jwt->addClaim($payload, 'exp', $exp_time);
        }
        return $jwt->encode($payload, $key);
    }

    /**
     * Decode and Verify a JWT. If the token is not valid null will be returned.
     *
     * @param string $token
     * @return array|null - The payload that was originally encoded.
     */
    public static function decodeJWT($token)
    {
        $key = self::getConfigKey('JWT_KEY', __FUNCTION__);
        $jwt = new JWT();
        return $jwt->decode($token, $key);
    }

    /**
     * Validate the config value is setup and return the key
     *
     * @param string $name
     * @param string $calling_function
     * @return string
     * @throws \Exception
     */
    private static function getConfigKey($name, $calling_function)
    {
        // Get from [app->config] array
        global $app;
        if (isset($app) && isset($app->config[$name])) {
            return $app->config[$name];
        }

        // Get from the System's Enviroment Variable. If a project is set to use
        // a [.env] file and a related class then the value would come from here.
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }

        // No value found
        $error = 'Missing Application Config Value or Environment Variable for [%s]. If this error is not clear then please review FastSitePHP documentation and examples on how to use [%s::%s()].';
        $error = sprintf($error, $name, __CLASS__, $calling_function);
        throw new \Exception($error);
    }

    /**
     * Setup file encryption object. Files are processed in memory for Windows
     * and if they are less than 10 megabytes in size on Linux/Mac/Unix.
     * Command line file processing takes longer but allows for processing
     * of large files.
     *
     * @param string $file_path
     * @return FileEncryption
     */
    private static function setupFileCrypto($file_path)
    {
        // NOTE - the file size check may not work properly if the file
        // is over 2 GB and the OS or version of PHP being used is 32-bit.
        // If you need to encrypt large files (over 2 GB) on a 32-bit OS.
        // See comments in [encryptFile()] of this calss.
        $crypto = new FileEncryption();
        $ten_megabytes = (1024 * 1024 * 10);
        if (PHP_OS !== 'WINNT'
            && (is_file($file_path) && filesize($file_path) > $ten_megabytes)
        ) {
            $crypto->processFilesWithCmdLine(true);
        }
        return $crypto;
    }
}
