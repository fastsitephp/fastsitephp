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

use FastSitePHP\Encoding\Base64Url;
use FastSitePHP\Encoding\Json;
use FastSitePHP\Security\Crypto\AbstractCrypto;
use FastSitePHP\Security\Crypto\CryptoInterface;
use FastSitePHP\Security\Crypto\Random;

/**
 * Data Signing
 *
 * This class is designed to provide secure and easy to use data signing
 * functions for an application or site. Data Signing is often used with
 * cookies and web data to allow data to be sent back and forth from a
 * client while verifying that it has not been tampered with.
 *
 * A common use of this function would be to provide security information
 * to a webpage that can be ready by JavaScript and where the client sends
 * it back. The client site or app can then handle logic based on different
 * permissions and once sent back to the server it can be verified that
 * nothing changed and that security info is valid. In this scenario of using
 * client side logic based on the signed data it's important to still handle
 * security on the server.
 *
 * The format of signed data and concepts used by this class are similar
 * to JWT with a few differences:
 *   - This class only allows secure keys
 *   - Various data types (string, int, float, bool, array, object, and null)
 *     can be signed and verified back to the original format
 *   - Server-Side Algorithm and Header information is not saved with the signed data
 *   - Claim Rules are simplified as there is only an optional expired time rule
 *   - The expire time option is designed to work well with JavaScript
 *   - Only HMAC is supported; RSA and other Public Key options are not used
 *
 * Data Signing as used in the context of this class are functions
 * that use a Cryptographic Hashing Function (default SHA-256) with
 * Hash-based Message Authentication Code (HMAC).
 *
 * Data Format of Signed Data
 *    'base64url(data).type.base64url(signature)'
 *    'base64url(data).type.timestamp.base64url(signature)'
 */
class SignedData extends AbstractCrypto implements CryptoInterface
{
    /**
     * Return a new key in hex format based on the size of the needed key.
     * Keys are generated using random bytes from the System's CSPRNG
     * (Cryptographically secure pseudorandom number generator).
     *
     * The same key must be used for both signing and verifying
     * and the key should be kept secret in a secure manner and should
     * not be shared publicly.
     *
     * @return string
     */
    public function generateKey()
    {
        $bit_length = $this->key_size_hmac;
        return \bin2hex(Random::bytes($bit_length / 8));
    }

    /**
     * Save data in a text format that can easily be read but cannot be
     * tampered with. Use the [verify()] function to read the data.
     *
     * Data can be in one of the following formats [string, int, float, bool,
     * array, object, and null if the option [allowNull(true)] is set.
     *
     * A secret key is needed to sign data and one can be generated from the
     * function [generateKey()]. The same key used for signing data must also
     * be used to verify data.
     *
     * Expire time if used must be a float representing a Unix Timestamp in
     * Milliseconds or a string that is valid for the PHP function
     * [strtotime()] - for example '+1 hour'. Expire time can be used for
     * features such as sending user permissions to a webpage with a timeout
     * in a cookie or response header instead of using a PHP Session for
     * user permissions.
     *
     * @param mixed $data
     * @param string $key
     * @param null|float|string $expire_time
     * @return string
     * @throws \Exception
     */
    public function sign($data, $key, $expire_time = null)
    {
        // Get data type and convert from any type (int, object, etc) to a string
        list($type, $text) = $this->dataToString($data);

        // Validate the expire time option and convert it to a
        // Unix Timestamp in Milliseconds or Null.
        $expire_time = $this->expireTime($expire_time);

        // Build String to Sign. One of two formats will be used:
        //   base64url(data).type
        //   base64url(data).type.timestamp
        $expire_time = ($expire_time === null ? '' : '.' . (string)$expire_time);
        $hash_text = Base64Url::encode($text) . $type . $expire_time;

        // Validate and decode the the Key from a hex string
        $key = $this->signingKey($key, __FUNCTION__);

        // Sign and return the signed string
        $signature = \hash_hmac($this->hashing_algorithm, $hash_text, $key, true);
        return $hash_text . '.' . Base64Url::encode($signature);
    }

    /**
     * Verify a string that was signed with [sign()]; data is returned in the
     * original format if verification was successful and null is returned
     * if the string cannot be verified. Objects that are signed are returned
     * as an Associative Array (Dictionary).
     *
     * If setting the option [exceptionOnError(true)] then an Exception will
     * be thrown if the text cannot be verified.
     *
     * @param string $signed_text
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function verify($signed_text, $key)
    {
        try {
            // Validate format and parse signed text
            list($text, $type, $expire_time, $user_hash, $hash_text) = $this->parseSignedText($signed_text);

            // Validate and decode the hex string key
            $key = $this->signingKey($key, __FUNCTION__);

            // Verify by generating HMAC and comparing to the previously signed value
            $calc_hash = \hash_hmac($this->hashing_algorithm, $hash_text, $key, true);
            if (!\hash_equals($calc_hash, $user_hash)) {
                throw new \Exception('Error when verifying signed text using the verify() function. The signed text has either been modified or a different key was used to sign the data.');
            }

            // Check if the signed value has expired
            // [$now] the value that JavaScript Date.now() would return.
            // PHP [time() * 1000] also works using second precision rather than milliseconds.
            if ($expire_time !== null) {
                $now = round(microtime(true) * 1000);
                if ($now > (float)$expire_time) {
                    throw new \Exception('Error when verifying signed text using the verify() function. The text is valid however it has expired based on the [expire_time] value.');
                }
            }

            // Convert to return format based on type
            // saved when the data was originally signed
            return $this->stringToData('Verification', $type, $text);
        } catch (\Exception $e) {
            // Re-throw Exception based if [exceptionOnError(true)]
            // otherwise return null by default.
            if ($this->exception_on_error) {
                throw $e;
            }
            return null;
        }
    }

    /**
     * Get variable data type and convert all data to text. The variable type
     * will be between 1 and 3 bytes (s = string, i32 = 32-bit int, etc) with
     * a dot (period/hard stop) character which is used in-between variables.
     *
     * @param mixed $data
     * @return array
     * @throws \Exception
     */
    private function dataToString($data)
    {
        if ($data === null) {
            if ($this->allow_null) {
                $type = '.n';
                $text = chr(0);
            } else {
                throw new \Exception(sprintf('Unable to sign a null value unless [%s->allowNull(true)] is set.', __CLASS__));
            }
        } elseif (is_string($data)) {
            $type = '.s';
            $text = $data;
        } elseif (is_int($data)) {
            // 32-Bit OS/PHP or 64-Bit OS/PHP with 32-Bit Int Size?
            // 32-Bit Int Sizes:
            //   Min = -2147483648 = -(pow(2, 31))  = ~PHP_INT_MAX (PHP_INT_MIN in PHP 7+)
            //   Max =  2147483647 = pow(2, 31) - 1 = PHP_INT_MAX
            if (PHP_INT_SIZE === 4 || ($data >= -2147483648 && $data <= 2147483647)) {
                $type = '.i32';
            } else {
                $type = '.i64';
            }
            $text = (string)$data;
        } elseif (is_float($data)) {
            $type = '.f';
            $text = (string)$data;
        } elseif (is_bool($data)) {
            $type = '.b';
            $text = ($data === true ? '1' : '0');
        } elseif (is_array($data) || is_object($data)) {
            $type = '.j';
            $text = Json::encode($data);
        } else {
            throw new \Exception(sprintf('Invalid type [%s] for signing. Only null, string, int, float, bool, object, and array types can be signed.', gettype($data)));
        }

        return array($type, $text);
    }

    /**
     * Validate Expire Time from [sign()] and convert to null
     * or a Unix Tiemstamp in Milliseconds.
     *
     * @param mixed $expire_time
     * @return null|float
     * @throws \Exception
     */
    private function expireTime($expire_time)
    {
        if ($expire_time !== null) {
            if (is_string($expire_time)) {
                // Parse the string date value into a Unix Timestamp (example '+1 day')
                $value = strtotime($expire_time);
                if ($value === false) {
                    throw new \Exception('Invalid [expire_time] parameter for signing when the function sign() was called. A string was passed however it could not be converted to a valid timestamp. If specified the parameter [expire_time] must be either a float representing a Unix Timestamp in Milliseconds or a valid string for the PHP function [strtotime()], examples include \'+1 day\' and \'+30 minutes\'.');
                }
                // Convert the time value from seconds to milliseconds
                $expire_time = $value * 1000;
            } elseif (!is_float($expire_time)) {
                throw new \Exception(sprintf('Unexpected [expire_time] parameter for signing when the function sign() was called, expected [string|float|null] but was passed [%s].', gettype($expire_time)));
            }
        }
        return $expire_time;
    }

    /**
     * Validate and Parse the signed text from [verify()].
     *
     * @param string $signed_text
     * @return array
     * @throws \Exception
     */
    private function parseSignedText($signed_text)
    {
        if (!is_string($signed_text)) {
            $error = 'Error when verifying signed text using the verify() function. The [$signed_text] parameter was not a string but instead was passed a [%s].';
            $error = sprintf($error, gettype($signed_text));
            throw new \Exception($error);
        }

        // Check for expected format of [data.type.hmac] or [data.type.expireTime.hmac]
        $data = explode('.', $signed_text);
        $count = count($data);
        if (!($count === 3 || $count === 4)) {
            throw new \Exception('Error when verifying signed text using the verify() function. Unexpected format of signed text. The expected format is [base64(data).type.base64(hmac)] or [base64(data).type.expireTime.base64(hmac)].');
        }

        // Parse format and make sure text and hash are valid Base64-URL strings
        $text = Base64Url::decode($data[0]);
        $type = $data[1];
        if ($count === 3) {
            $expire_time = null;
            $hmac_len = $this->strlen($data[2]);
            $user_hash = Base64Url::decode($data[2]);
        } else {
            $expire_time = $data[2];
            $hmac_len = $this->strlen($data[3]);
            $user_hash = Base64Url::decode($data[3]);
        }
        if ($text === false || $user_hash === false) {
            throw new \Exception('Error when verifying signed text using the verify() function. Either text or hmac values have been modified and are not valid base-64-url strings. This error can happen if another function has encoded or decoded and modified the original value.');
        }

        // Remove ".signagure" from the end of original string
        $hash_text = $this->substr($signed_text, 0, $this->strlen($signed_text) - $hmac_len - 1);
        return array($text, $type, $expire_time, $user_hash, $hash_text);
    }

    /**
     * Validate the signing hey and convert from hex string to a plain string of bytes.
     *
     * @param string $key
     * @param string $calling_function
     * @return string
     * @throws \Exception
     */
    private function signingKey($key, $calling_function)
    {
        $this->validateHexString($key, true);
        $hmac_bytes_size = ($this->key_size_hmac / 8);
        $bin_key = hex2bin($key);
        if ($this->strlen($bin_key) !== $hmac_bytes_size) {
            throw new \Exception(sprintf('Invalid Key for signing. The key required for [%s()] using the current settings must be a hex encoded string that is %d characters in length (%d bytes, %d bits). Required key size is determined from [hashingAlgorithm()].', $calling_function, ($hmac_bytes_size * 2), $hmac_bytes_size, $hmac_bytes_size * 8));
        }
        return $bin_key;
    }
}
