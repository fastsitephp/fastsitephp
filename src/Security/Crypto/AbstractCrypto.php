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

namespace FastSitePHP\Security\Crypto;

use FastSitePHP\Encoding\Json;

/**
 * Abstract Crypto Class
 *
 * This is a PHP Abstract Class which is used as a base class for classes
 * [Encryption, SignedData, FileEncryption]. Abstract classes cannot be created
 * directly and instead are inherited by the other classes. This class contains
 * common functions shared by the 3 classes however not all properties and
 * functions are used by each class. PHP does not directly support multiple
 * inheritance however PHP 5.4 and later support a similar concept called
 * Traits. This version of FastSitePHP supports PHP Version 5.3 and which is
 * why Traits are not used.
 *
 * This class and related classes can be modified with relative ease for use in
 * applications outside FastSitePHP as they has no dependencies outside of the
 * widely used [paragonie/random_compat] library for PHP 5 and several polyfill
 * functions for older versions of PHP (5.3, 5.4, and 5.5).
 */
abstract class AbstractCrypto
{
    // Member Variables with Default Settings

    // Used with Classes [Encryption] and [SignedData].
    // [FileEncryption] also used 'sha256' for HMAC however it cannot be changed.
    protected $hashing_algorithm = 'sha256';
    protected $key_size_hmac = 256;
    protected $exception_on_error = false;
    protected $allow_null = false;

    // Used with [Encryption] and [FileEncryption]
    protected $encrypt_then_authenticate = true;
    protected $key_type = 'key'; // or 'password'

    // PBKDF2 Default Settings used with [Encryption] and [FileEncryption]
    // SHA-512 is used because the default generated key is 512 bytes
    // (256 for Enc Key and 256 for HMAC Key). The way that PBKDF2 is designed
    // is that the number of iterations will double if the return byte length
    // is double the hash size. By using SHA-512 the number of iterations can
    // be easily increased for the default key.
    protected $pbkdf2_algorithm = 'sha512';
    protected $pbkdf2_iterations = 200000;

    // Use multi-byte string functions? This gets set from the
    // Class Constructor when an object is first created.
    private $use_mbstring = true;

    /**
     * Class Constructor
     *
     * If using PHP 5.3 then functions [bin2hex()] and [hex2bin()]
     * and OpenSSL Constant [OPENSSL_RAW_DATA] are polyfilled.
     *
     * If using PHP 5.5 or below then [hash_equals()] is polyfilled.
     */
    public function __construct()
    {
        // Create Polyfill/Compatibility Functions
        if (PHP_VERSION_ID < 50400) {
            if (!defined('OPENSSL_RAW_DATA')) {
                define('OPENSSL_RAW_DATA', 1);
            }
            require_once __DIR__ . '/../../Polyfill/hex_compat.php';
        }
        if (PHP_VERSION_ID < 50600) {
            require_once __DIR__ . '/../../Polyfill/hash_equals_compat.php';
        }

        // Check whether to use multi-byte string functions when the class
        // is created. In most installations of PHP multibyte string functions
        // are expected to exist however it's possible for multibyte support
        // to be disabled in [php.ini] or for PHP on the Web server to not include it.
        $this->use_mbstring = extension_loaded('mbstring');
    }

    /**
     * Get or set how invalid values are handled on decryption and verification.
     * By default functions [decrypt()] and [verify()] will return null
     * if data cannot be decrypted or verified respectively. When
     * [exceptionOnError()] is set to [true] then these two functions
     * will throw an exception instead of returning null. This allows
     * for the default behavior of [decrypt()] and [verify()] to allow
     * calling code to handle nulls easily rather than try/catch blocks
     * and if details are needed on why encryption or data verification
     * failed then then this property can be set to [true].
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function exceptionOnError($new_value = null)
    {
        if ($new_value === null) {
            return $this->exception_on_error;
        }
        $this->exception_on_error = (bool)$new_value;
        return $this;
    }

    /**
     * When [allowNull()] is set to the default value [false] then function
     * [encrypt()] and [sign()] will not accept null data parameters and
     * instead will throw an exception. The reason for this is because
     * [decrypt()] and [verify()] return nulls by default if data cannot be
     * decrypted or verified. If it makes sense for your application to
     * encrypt or sign null values then set this property to [true] and
     * likely set [exceptionOnError()] to true so that decryption and
     * authentication can properly validate the null values.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function allowNull($new_value = null)
    {
        if ($new_value === null) {
            return $this->allow_null;
        }
        $this->allow_null = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set the Hashing Algorithm to use with classes [Encryption]
     * and [SignedData]. Defaults to 'sha256'. Only Algorthims allowed
     * for PHP function [hash_hmac()] are supported.
     *
     * @param null|string $new_value
     * @return string|$this
     */
    public function hashingAlgorithm($new_value = null)
    {
        return $this->setHashingAlgorithm($new_value, __FUNCTION__, 'key_size_hmac', 'hashing_algorithm');
    }

    /**
     * Get or set whether to use authenticated encryption. When set to [true]
     * text or data is encrypted using the specified encryption algorithm
     * (default 'aes-256-cbc') then the encrypted bytes are hashed using hmac
     * with the specified hashing algorithm (default 'sha256'). If set to
     * [false] then data or text is only encrypted and the decrypted data
     * cannot be verified if it has been tampered with or not. Authenticating
     * encrypted data is a critical step for data integrity so it's important
     * that this property should be left as the default value [true] unless
     * compatibility with data encrypted outside of this class is needed.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function encryptThenAuthenticate($new_value = null)
    {
        if ($new_value === null) {
            return $this->encrypt_then_authenticate;
        }
        $this->encrypt_then_authenticate = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set the key type which is a string value of either 'key' or
     * 'password'. The default value is 'key' which results in encryption
     * functions validating that the key parameter used for encryption and
     * decryption to match the required length. When using 'password' then
     * keys are generated each time from PBKDF2 (Password-Based Key Derivation
     * Function 2). PBKDF2 takes a considerable amount of CPU so using the
     * 'password' option requires carefull consideration as it can make a site
     * more susceptible to Denial of Service (DoS) attacks if a lot of requests
     * are sent to service or page that uses PBKDF2.
     *
     * @param null|string $new_value
     * @return string|$this
     */
    public function keyType($new_value = null)
    {
        $valid_values = array('key', 'password');
        return $this->getOrSetStringProp($new_value, __FUNCTION__, 'key_type', $valid_values);
    }

    /**
     * Get or set the PBKDF2 Hashing Algorithm to use with the option
     * [keyType('password')]. Defaults to 'sha512'. Only Algorthims
     * allowed for PHP function [hash_pbkdf2()] are supported.
     *
     * @param null|string $new_value
     * @return string|$this
     */
    public function pbkdf2Algorithm($new_value = null)
    {
        return $this->setHashingAlgorithm($new_value, __FUNCTION__, null, 'pbkdf2_algorithm');
    }

    /**
     * Get or set the number of PBKDF2 iterations to use with
     * option [keyType('password')]. Defaults to 200000 [200,000].
     *
     * If you have an older server 200,000 might be too slow and
     * you could use 100,000, however if you change this setting you
     * would need to track what was encrypted with a different number.
     *
     * @param null|int $new_value
     * @return int|$this
     */
    public function pbkdf2Iterations($new_value = null)
    {
        if ($new_value === null) {
            return $this->pbkdf2_iterations;
        }
        $this->pbkdf2_iterations = (int)$new_value;
        return $this;
    }

    /**
     * Get the key size used for HMAC Hashing. This value is set automatically
     * when calling [hashingAlgorithm()] and for security cannot be changed to
     * a different value. The default value is 256-bit (32 Bytes) which is the
     * output length for hashing with 'sha256'.
     *
     * @return int
     */
    public function keySizeHmac()
    {
        return $this->key_size_hmac;
    }

    /**
     * Get string length using [mb_strlen()] if the extension
     * [mbstring] is loaded otherwise use [strlen()].
     *
     * @param string $str
     * @return int
     */
    protected function strlen($str)
    {
        if ($this->use_mbstring) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }

    /**
     * Get part of string using [mb_substr()] if the extension
     * [mbstring] is loaded otherwise use [substr()].
     *
     * @param string $str
     * @param int $start
     * @param int $length
     * @return string
     */
    protected function substr($str, $start, $length)
    {
        if ($this->use_mbstring) {
            return mb_substr($str, $start, $length, '8bit');
        }
        return substr($str, $start, $length);
    }

    /**
     * Get or set a hashing algorithm.
     * Used with properties [hashing_algorithm] and [pbkdf2_algorithm].
     *
     * @param string|null $algorithm
     * @param string $function_name
     * @param string|null $key_size_prop
     * @param string $hash_prop
     * @return string|$this
     * @throws \Exception
     */
    protected function setHashingAlgorithm($algorithm, $function_name, $key_size_prop, $hash_prop)
    {
        // Get Value
        if ($algorithm === null) {
            return $this->{$hash_prop};
        }

        // Set Value

        // Validate
        if (!is_string($algorithm)) {
            throw new \Exception(sprintf('The parameter for [%s->%s()] must be a string or null but was instead a [%s].', get_called_class(), $function_name, gettype($algorithm)));
        } elseif (!in_array($algorithm, \hash_algos(), true)) {
            throw new \Exception(sprintf('The hashing algorithm [%s->%s(\'%s\')] is not available on this computer.', get_called_class(), $function_name, $algorithm));
        }

        // Set Algorithm and a new Key Size based on the Hash Size
        if ($key_size_prop !== null) {
            $this->{$key_size_prop} = $this->strlen(hash($algorithm, '', true)) * 8;
        }
        $this->{$hash_prop} = $algorithm;
        return $this;
    }

    /**
     * Get or set a string property
     *
     * @param string|null $value
     * @param string $function_name
     * @param string $prop_name
     * @param array $valid_values
     * @return string|$this
     * @throws \Exception
     */
    protected function getOrSetStringProp($value, $function_name, $prop_name, $valid_values)
    {
        // Get
        if ($value === null) {
            return $this->{$prop_name};
        }

        // Validate and Set
        if (!in_array($value, $valid_values, true)) {
            if (is_string($value)) {
                $error = 'The specified value for [%s->%s(\'%s\')] is not valid. Valid options are [%s].';
                $error = sprintf($error, get_called_class(), $function_name, $value, implode(', ', $valid_values));
                throw new \Exception($error);
            }
            $error = 'The specified value [%s->%s()] is not the correct type. Expected a string with one of the valid options: [%s], but received at [%s].';
            $error = sprintf($error, get_called_class(), $function_name, implode(', ', $valid_values), gettype($value));
            throw new \Exception($error);
        }
        $this->{$prop_name} = $value;
        return $this;
    }

    /**
     * Return a set for both Encryption and optionally HMAC Hashing of Keys
     * as Byte Strings from a single Hex String Key.
     *
     * @param string $key
     * @param string $iv
     * @param int $key_size_enc
     * @param int $key_size_hmac
     * @param bool $is_aead_mode
     * @return array
     * @throws \Exception
     */
    protected function encryptionKeys($key, $iv, $key_size_enc, $key_size_hmac, $is_aead_mode)
    {
        // Convert to Key Size from Bits to Bytes
        $enc_bytes_size = ($key_size_enc / 8);
        $hmac_bytes_size = 0;
        if ($this->encrypt_then_authenticate && !$is_aead_mode) {
            $hmac_bytes_size = ($key_size_hmac / 8);
        }

        // If using password as a key then create the key or keys from it
        // using PBKDF2 (Password-Based Key Derivation Function 2). PBKDF2
        // is a Key derivation function which takes a password and creates
        // hashes from it in a computationally intensive manner so that
        // brute-force attacks are not practical in many cases or at least
        // take a considerable amount of time.
        if ($this->key_type === 'password') {
            if ($this->strlen($key) === 0) {
                throw new \Exception('Error, the password cannot be empty.');
            }
            if (PHP_VERSION_ID < 50500 && !function_exists('hash_pbkdf2')) {
                require_once __DIR__ . '/../../Polyfill/hash_pbkdf2_compat.php';
            }
            $length = $enc_bytes_size + $hmac_bytes_size;
            $keys = \hash_pbkdf2($this->pbkdf2_algorithm, $key, $iv, $this->pbkdf2_iterations, $length, true);
            if (!$this->encrypt_then_authenticate || $is_aead_mode) {
                return array($keys, null);
            } else {
                return array(
                    $this->substr($keys, 0, $enc_bytes_size),
                    $this->substr($keys, $enc_bytes_size, $hmac_bytes_size),
                );
            }
        }

        // Get Key Values from Single Key Parameter
        $this->validateHexString($key, true);
        $bin_key = hex2bin($key);
        $key_size = $this->strlen($bin_key);
        if ($key_size !== ($enc_bytes_size + $hmac_bytes_size)) {
            $class = get_called_class();
            $props = (
                $class === 'FileEncryption'
                ? 'encryptThenAuthenticate'
                : 'encryptThenAuthenticate, encryptionAlgorithm, hashingAlgorithm, and keySizeEnc'
            );
            $error = 'Invalid Key for encryption. The key required using the current settings must be a hex encoded string that is %d characters in length (%d bytes, %d bits) but was instead %d hex characters. Required key size is determined from the [%s] class using properties [%s].';
            $error = sprintf(
                $error,
                (($enc_bytes_size + $hmac_bytes_size) * 2), // Required Character Length
                ($enc_bytes_size + $hmac_bytes_size),       // Byte Length
                (($enc_bytes_size + $hmac_bytes_size) * 8), // Bit Length
                ($key_size * 2), // Passed Character Length
                $class,
                $props
            );
            throw new \Exception($error);
        }

        if (!$this->encrypt_then_authenticate || $is_aead_mode) {
            return array($bin_key, null);
        } else {
            return array(
                $this->substr($bin_key, 0, $enc_bytes_size),
                $this->substr($bin_key, $enc_bytes_size, $hmac_bytes_size),
            );
        }
    }

    /**
     * Validate a Hex String, if an invalid string and a key is being checked
     * then throw an exception as this is a programming error by the calling
     * application otherwise return false as it's a data check and may be
     * tampered data.
     *
     * @param string $value
     * @param bool $key_function
     * @return bool
     * @throws \Exception
     */
    protected function validateHexString($value, $key_function = false)
    {
        $len = $this->strlen($value);
        if ($len === 0 || $len % 2 !== 0 || !ctype_xdigit($value)) {
            if ($key_function) {
                $error = 'Invalid Key. The key must be a hexadecimal encoded string value and the function was called with a non-hex key.';
                throw new \Exception($error);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Used with [decrypt()] and [verify()] to convert a text string to
     * the original data type (int, array, etc).
     *
     * @param string $method
     * @param string $type
     * @param string $text
     * @return mixed
     * @throws \Exception
     */
    protected function stringToData($method, $type, $text)
    {
        switch ($type) {
            case 'n':
                if ($text === chr(0)) {
                    return null;
                }

                $data_type = ($method === 'Decryption' ? 'decrypted' : 'verified');
                $create_method = ($method === 'Decryption' ? 'encrypted' : 'signed');
                $error = '%s was successful however the %s data did not match a null value. It\'s likely that the data was %s with another program or a software library which is not compatible with this class.';
                $error = sprintf($error, $method, $data_type, $create_method);
                throw new \Exception($error);
            case 's':
                return $text;
            case 'i32': // Int32
            case 'i64': // Int64
                // A large int created on a 64-bit machine will be returned as a string
                // if it is too big to fit in 32-bit address on a 32-bit machine.
                if (filter_var($text, FILTER_VALIDATE_INT) === false) {
                    return $text;
                }

                return (int)$text;
            case 'f':
                // Make sure the float is valid for the instance of PHP otherwise
                // return data as text. Most languages use 64-Bit for floats/doubles/etc
                // so it's unexpected that this would fail. If it does a string is returned.
                if (filter_var($text, FILTER_VALIDATE_FLOAT) === false) {
                    return $text;
                }

                return (float)$text;
            case 'b':
                if (!($text === '0' || $text === '1')) {
                    $data_type = ($method === 'Decryption' ? 'decrypted' : 'verified');
                    $create_method = ($method === 'Decryption' ? 'encrypted' : 'signed');
                    $error = '%s was successful however the %s data did not match a boolean value of either 0 or 1. It\'s likely that the data was %s with another program or a software library which is not compatible with this class.';
                    $error = sprintf($error, $method, $data_type, $create_method);
                    throw new \Exception($error);
                }

                return (bool)$text;
            case 'j':
                try {
                    $data = Json::decode($text);
                } catch (\Exception $e) {
                    $data_type = ($method === 'Decryption' ? 'decrypted' : 'verified');
                    $create_method = ($method === 'Decryption' ? 'encrypted' : 'signed');
                    $error = '%s was successful however the %s data could not be parsed as valid JSON. It\'s likely that the data was %s with another program or a software library which is not compatible with this class. Json Decode Error: %s';
                    $error = sprintf($error, $method, $data_type, $create_method, $e->getMessage());
                    throw new \Exception($error);
                }

                return $data;
            default:
                // Unknown Type, likely a programming error when
                // modifying this code or creating a compatible function.
                $data_type = ($method === 'Decryption' ? 'decrypted' : 'verified');
                $create_method = ($method === 'Decryption' ? 'encrypted' : 'signed');
                $error = '%s was successful however the %s data has an unknown type of [%s]. It\'s likely that the data was %s with another program or a software library which is not compatible with this class.';
                $error = sprintf($error, $method, $data_type, $type, $create_method);
                throw new \Exception($error);
        }
    }
}
