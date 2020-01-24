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
 * Encryption
 *
 * This class is designed to provide secure and easy to use encryption and
 * decryption for an application or site. Encryption is often used with
 * cookies and web data to keep the data secret and also verify that the
 * data has not been tampered with.
 *
 * By default many different data types are supported for encryption
 * [string, int, float, bool, array, object, and null] and when decrypted
 * they are returned in the original format. File encryption is handled
 * separately by the [FileEncryption] Class.
 *
 * This class is designed to work with PHP 5.3 and above and when using
 * PHP 7.1 or above Authenticated Encryption Block Modes of GCM and CCM
 * are available.
 *
 * The type of encryption that this class uses is Advanced Encryption Standard
 * (AES) which is a Symmetric Key Block Cipher meaning that the same key used
 * for encryption must also be used decryption; and that data is encrypted
 * in blocks of text rather than one byte at a time. Additionally when using
 * default and recommended settings Authenticated Encryption is performed
 * using either HMAC (CBC and CTR modes) or GCM mode.
 *
 * Default Algorthims:
 *     'aes-256-cbc' - Encryption Algorithm - AES (Rigndeal) with a 256-bit
 *                     key using CBC mode. CTR and GCM modes are also supported
 *                     depending upon the version of PHP being used.
 *     'sha256'      - Hashing Algorithm for HMAC
 *
 * @link https://en.wikipedia.org/wiki/Advanced_Encryption_Standard
 * @link https://en.wikipedia.org/wiki/Authenticated_encryption
 */
class Encryption extends AbstractCrypto implements CryptoInterface
{
    // Member Variables with Default Settings
    private $encryption_algorithm = 'aes-256-cbc';
    protected $key_size_enc = 256;
    protected $return_format = 'base64url';
    protected $data_format = 'type-byte';

    /**
     * Return a new key in hex format based on the size of the needed key.
     * Keys are generated using random bytes from the System's CSPRNG
     * (Cryptographically secure pseudorandom number generator).
     *
     * The same key must be used for both encryption and decryption
     * and the key should be kept secret in a secure manner and should
     * not be shared publicly.
     *
     * @return string
     */
    public function generateKey()
    {
        $bit_length = $this->key_size_enc;
        if ($this->encrypt_then_authenticate && !$this->isAEAD_Mode()) {
            $bit_length += $this->key_size_hmac;
        }
        return \bin2hex(Random::bytes($bit_length / 8));
    }

    /**
     * Encrypt data "plaintext" from a format of [string, int, float, bool,
     * array, object, and optionally null] to string format "ciphertext" that
     * cannot be ready unless someone has the key used for encryption.
     * The default format of returned ciphertext is base64-url and the
     * function [decrypt()] is used to read the encrypted data.
     *
     * The key is a string value in hexadecimal format that can be securely
     * generated using [generateKey()].
     *
     * The parameter [aad]; an acronym for (Additional authentication data)
     * is optional associated data. It is built-in to GCM and CCM block modes
     * and when using other modes such as CBC or CTR it is included with the
     * HMAC calculation. AAD is used to link or attach plaintext information
     * to the encrypted data in a manner that prevents the AAD from being
     * changed or separated from the encrypted data. Using AAD is not common
     * and requires careful planning.
     *
     * Function Properties used with [encrypt()] and [decrypt()]:
     *   Create a secure key
     *     [generateKey()]
     *
     *   Functions for App Logic (doesn't affect security):
     *     [allowNull()] - Set to [true] to allow null values to be encrypted
     *     [exceptionOnError()] - Used with [decrypt()]
     *     [dataFormat()] - Allow any data type 'type-byte' (default) or 'strings-only'
     *     [returnFormat()] - Change return format, defaults to 'base64url'
     *
     *   Functions that change how Crypto Works:
     *     [encryptionAlgorithm()] - Change the encryption algorithrm
     *     [hashingAlgorithm()] - Change the HMAC algorithrm
     *     [keyType()] - 'key' or 'password' - Default to 'key'
     *     [pbkdf2Algorithm()] - For use with 'password' Key type
     *     [pbkdf2Iterations()] - For use with 'password' Key type
     *     [keySizeEnc()] - Change Key Size Requirements
     *     [encryptThenAuthenticate()] - Set to [false] to disable Authentication
     *
     * By default only secure settings are used so changing most encryption
     * options is not recommend unless you have detailed knowledge of
     * cryptography or have specific app needs. One security option that is
     * safe to change without knowning the details of how to use it is
     * [encryptionAlgorithm('aes-256-gcm')] if all of your servers have
     * PHP 7.1 or greater. This will provide faster encryption and decryption
     * and the ciphertext will be slightly smaller in size.
     *
     * @param mixed $data
     * @param string $key
     * @param string $aad
     * @return string
     * @throws \Exception
     */
    public function encrypt($data, $key, $aad = '')
    {
        // Convert from any data type (int, object, etc) to a string
        $plaintext = $this->dataToString($data);

        // Generate Initialization Vector (IV).
        // This value uses a secure random number and
        // changes every time the function is called.
        $iv_size = \openssl_cipher_iv_length($this->encryption_algorithm);
        $iv = Random::bytes($iv_size);

        // Get Encryption and HMAC Keys from a Single Key or Password.
        // If using a Password the IV is used as Salt with PBKDF2.
        $is_aead_mode = $this->isAEAD_Mode();
        $size_enc = $this->key_size_enc;
        $size_hmac = $this->key_size_hmac;
        list($key_enc, $key_hmac) = $this->encryptionKeys($key, $iv, $size_enc, $size_hmac, $is_aead_mode);

        // Encrypt the text string, depending upon options the resulting format is:
        //   ([Encrypted Bytes] + [IV Bytes] + [Tag Bytes])
        //   ([Encrypted Bytes] + [IV Bytes] + [HMAC Bytes])
        //   ([Encrypted Bytes] + [IV Bytes])
        $algo = $this->encryption_algorithm;
        if ($is_aead_mode) {
            // When using AEAD Block Cipher Modes of GCM or CCM the AEAD Tag
            // generated by this function will always be 16 bytes (128 bits).
            // HMAC is not used with GCM or CCM modes because these two modes
            // automatically provide Authenticated Encryption.
            $ciphertext = \openssl_encrypt($plaintext, $algo, $key_enc, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16);
            $ciphertext = $ciphertext . $iv . $tag;
        } else {
            // Encrypt the Text String and then add the IV after the encrypted
            // bytes/string. By default PKCS #7 is used for padding of the last
            // block in modes that use padding such as the default CBC Mode.
            $ciphertext = \openssl_encrypt($plaintext, $algo, $key_enc, OPENSSL_RAW_DATA, $iv);
            $ciphertext = $ciphertext . $iv;

            // Calculate HMAC from the ciphertext (and optionally AAD)
            // and add it to the end of the ciphertext string.
            if ($this->encrypt_then_authenticate) {
                $hmac = \hash_hmac($this->hashing_algorithm, $ciphertext . $aad, $key_hmac, true);
                $ciphertext = $ciphertext . $hmac;
            }
        }

        // Return the Encrypted Text (defaults to 'base64url').
        // This option is based on the setting [returnFormat()].
        switch ($this->return_format) {
            case 'base64url':
                return Base64Url::encode($ciphertext);
            case 'base64':
                return base64_encode($ciphertext);
            case 'hex':
                return bin2hex($ciphertext);
            case 'bytes':
                return $ciphertext;
            default:
                $error = 'Unexpected Error from [%s->%s()], the property [returnFormat()] must be one of the following valid values [base64url, base64, hex, bytes]. This error can only happen when invalid changes are made this class.';
                $error = sprintf($error, __CLASS__, __FUNCTION__);
                throw new \Exception($error);
        }
    }

    /**
     * Decrypt a string that was encrypted with [encrypt()]. Using the default
     * properties the data is returned in the original format if decryption was
     * successful and null is returned if the string cannot be decrypted.
     *
     * If setting the option [exceptionOnError(true)] then an Exception will
     * be thrown if the encrypted text (ciphertext) cannot be decrypted.
     *
     * The same key and settings used with encryption must also be used here.
     * If AAD (Additional authenticated data) was used [encrypt()] then the
     * same value must also be used here.
     *
     * @param string $encrypted_text
     * @param string $key
     * @param string $aad
     * @return mixed
     * @throws \Exception
     */
    public function decrypt($encrypted_text, $key, $aad = '')
    {
        try {
            // Decode from Base64, Hex, etc
            $encrypted_bytes = $this->decodeText($encrypted_text);

            // Get needed IV/Hash text lengths which will be used for
            // validation and for parsing from the encrypted text.
            $algo = $this->encryption_algorithm;
            $iv_length = \openssl_cipher_iv_length($algo);
            $hash_length = 0;
            $tag_length = 0;

            $is_aead_mode = $this->isAEAD_Mode();
            if ($is_aead_mode) {
                $tag_length = 16;
            } elseif ($this->encrypt_then_authenticate) {
                $hash_length = $this->strlen(\hash($this->hashing_algorithm, '', true));
            }

            // Validate the Size of the Encrypted Text
            $this->validateSize($encrypted_bytes, $iv_length, $tag_length, $hash_length);

            // Depending upon Block Cipher Mode unpack either the Tag
            // or HMAC Result from the end of the Encrypted Bytes String.
            $user_hmac = null;
            $tag = null;
            if ($is_aead_mode) {
                $tag_start = $this->strlen($encrypted_bytes) - 16;
                $tag = $this->substr($encrypted_bytes, $tag_start, 16);
                $encrypted_bytes = $this->substr($encrypted_bytes, 0, $tag_start);
            } elseif ($this->encrypt_then_authenticate) {
                $hmac_start = $this->strlen($encrypted_bytes) - $hash_length;
                $user_hmac = $this->substr($encrypted_bytes, $hmac_start, $hash_length);
                $encrypted_bytes = $this->substr($encrypted_bytes, 0, $hmac_start);
            }

            // Unpack IV from the end of the Encrypted Bytes String
            // but do not remove it because it is needed for authentication.
            $iv_start = $this->strlen($encrypted_bytes) - $iv_length;
            $iv = $this->substr($encrypted_bytes, $iv_start, $iv_length);

            // Get Encryption and HMAC Keys from a Single Key or Password.
            // If using a Password the IV is used as Salt with PBKDF2.
            $size_enc = $this->key_size_enc;
            $size_hmac = $this->key_size_hmac;
            list($key_enc, $key_hmac) = $this->encryptionKeys($key, $iv, $size_enc, $size_hmac, $is_aead_mode);

            // Authenticate ([Encrypted Bytes] + [IV Bytes]) + ([AAD] if used).
            // By default this will be done unless [encryptThenAuthenticate(false)]
            // is set or Block Cipher Modes GCM or CCM are used because those
            // two modes handle authentication automatically.
            if ($this->encrypt_then_authenticate && !$is_aead_mode) {
                $calc_hmac = \hash_hmac($this->hashing_algorithm, $encrypted_bytes . $aad, $key_hmac, true);
                if (!\hash_equals($calc_hmac, $user_hmac)) {
                    throw new \Exception('Decryption failed. The text was encrypted using different settings or has been tampered with.');
                }
            }

            // Remove IV from the end of the Encrypted Bytes and then Decrypt
            $ciphertext = $this->substr($encrypted_bytes, 0, $iv_start);
            if ($is_aead_mode) {
                $plaintext = \openssl_decrypt($ciphertext, $algo, $key_enc, OPENSSL_RAW_DATA, $iv, $tag, $aad);
            } else {
                $plaintext = \openssl_decrypt($ciphertext, $algo, $key_enc, OPENSSL_RAW_DATA, $iv);
            }
            $this->validateDecryption($plaintext);

            // Convert to the appropriate return type (string, int, bool, etc)
            if ($this->data_format === 'type-byte') {
                return $this->stringTypeToData($plaintext);
            } elseif ($this->data_format === 'string-only') {
                return $plaintext;
            } else {
                $error = 'Unexpected Error from [%s->%s()], the property [dataFormat()] must be one of the following valid values [type-byte, string-only]. This error can only happen when invalid changes are made this class.';
                $error = sprintf($error, __CLASS__, __FUNCTION__);
                throw new \Exception($error);
            }
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
     * Get or set the Encryption Algorithm to use. When setting the algorithm
     * any algorithm specified in [\openssl_get_cipher_methods()] can be used.
     *
     * The default encryption algorithm used is AES (Rigndeal) with a 256-bit
     * key using CBC mode. This class is also verified to work with AES using
     * CTR and GCM depending upon the version of PHP being used. If the
     * algorithm is changed to a different key size (example: 'aes-128-cbc')
     * then the value of [keySizeEnc()] will be changed automatically.
     *
     * Algorithms Verified against Standard Test Vectors:
     *     'aes-256-cbc' - Default, works in all supported versions of PHP
     *     'aes-192-cbc'
     *     'aes-128-cbc'
     *     'aes-256-ctr' - Requires PHP 5.5+
     *     'aes-192-ctr'
     *     'aes-128-ctr'
     *     'aes-256-gcm' - Requires PHP 7.1+
     *
     * @link http://php.net/manual/en/function.openssl-get-cipher-methods.php
     * @param string|null $new_value
     * @return string|$this
     * @throws \Exception
     */
    public function encryptionAlgorithm($new_value = null)
    {
        // Get current vlaue if called with null
        if ($new_value === null) {
            return $this->encryption_algorithm;
        }

        // Validate that the specified encryption algorithm is valid.

        // First verify a string was passed.
        if (!is_string($new_value)) {
            $error = 'The parameter [%s->%s($encryption_algorithm)] must be a string but was instead a [%s].';
            $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($new_value));
            throw new \Exception($error);
        }

        // The function [openssl_get_cipher_methods()] will list all available methods
        // from the OpenSSL library however not all may be avaiable for use in PHP.
        // AEAD Block Cipher Mode such as 'aes-256-gcm' will be listed as available
        // in versions of PHP 5 however they can't be used until PHP 7.1+.
        if (PHP_VERSION_ID < 70100) {
            $mode = strtolower($this->substr($new_value, -3, 3));
            if ($mode === 'gcm' || $mode === 'ccm') {
                $error = 'The encryption algorithm [%s->%s(\'%s\')] uses AEAD Block Cipher Mode (GCM or CCM) which requires PHP 7.1 or greater.';
                $error = sprintf($error, __CLASS__, __FUNCTION__, $new_value);
                throw new \Exception($error);
            }
        }

        // Check the supported Cipher Methods.
        // If AES/CTR Mode and less than PHP 5.5 then provide version info.
        if (!in_array($new_value, \openssl_get_cipher_methods(), true)) {
            $error = 'The encryption algorithm [%s->%s(\'%s\')] is not available on this computer.';
            $error = sprintf($error, __CLASS__, __FUNCTION__, $new_value);
            $ctr_algos = array('aes-128-ctr', 'aes-192-ctr', 'aes-256-ctr');
            if (PHP_VERSION_ID < 50500 && in_array(strtolower($new_value), $ctr_algos)) {
                $error .= ' CTR Mode often requires PHP 5.5 or greater. If possible upgrade your PHP version or use the default CBC Mode which is also known to be secure.';
            }
            throw new \Exception($error);
        }

        // Set Key Length, internally PHP calls [EVP_CIPHER_key_length()] from
        // OpenSSL to get the key length but doesn't provide a function to
        // obtain this info. The tested code works with AES which is currently
        // the only validated algorithm with this class. Supported Alorithms
        // that do not use the format like 'aes-256-cbc' simply leave the
        // key size to the default value. For key sizes of additional alogrithms
        // refer to alogrithm documentation or OpenSSL source at:
        //   https://github.com/openssl/openssl
        $data = explode('-', $new_value);
        if (count($data) === 3) {
            $key_size = (int)$data[2];
            switch ($key_size) {
                case 256:
                case 192:
                case 128:
                    $this->key_size_enc = $key_size;
                    break;
            }
        }

        // Set Algorithm
        $this->encryption_algorithm = $new_value;
        return $this;
    }

    /**
     * Get or Set the the return format for Encryption and the input
     * format for Decryption. Defaults to ('base64url'), all options:
     *     [ 'base64url', 'base64', 'hex', 'bytes' ]
     *
     * @param string|null $new_value
     * @return string|$this
     * @throws \Exception
     */
    public function returnFormat($new_value = null)
    {
        $valid_values = array('base64', 'base64url', 'hex', 'bytes');
        return $this->getOrSetStringProp($new_value, __FUNCTION__, 'return_format', $valid_values);
    }

    /**
     * Get or Set the data format to use for Encryption and Decryption.
     * There are two options:
     *     'type-byte' (Default):
     *         This allows for any data type (string, number, object, etc)
     *         to  be encrypted and returned in the same format on decryption.
     *         This works by converting data to a string prior to encryption
     *         and appending a single byte to the end of the string which
     *         represents what the original data type is. Objects and arrays
     *         are converted using JSON which allows for this code to be easily
     *         portable to other programming languages.
     *     'string-only':
     *         When this property is set only strings can be passed to the
     *         function [encrypt()].
     *
     * @param string|null $new_value
     * @return string|$this
     * @throws \Exception
     */
    public function dataFormat($new_value = null)
    {
        $valid_values = array('type-byte', 'string-only');
        return $this->getOrSetStringProp($new_value, __FUNCTION__, 'data_format', $valid_values);
    }

    /**
     * Get or set the required key size for Encryption. This value is changed
     * automatically when setting [encryptionAlgorithm()]. The default value is
     * 256 (32 Bytes) which is the key size for 'aes-256-cbc'.
     *
     * IMPORTANT - This should only be manually set if compatibility with other
     * code is needed or for Unit Testing. Often online samples of AES use
     * insecure passwords such as 'password' when showing encryption demos
     * which is one of the reasons that this setting was created. The main
     * reason this function exists is for Unit Testing. Additionally if
     * using an Encryption Algorithm other than AES then you may need
     * to set this value.
     *
     * @param int|null $new_bit_length
     * @return int|$this
     * @throws \Exception
     */
    public function keySizeEnc($new_bit_length = null)
    {
        // Get
        if ($new_bit_length === null) {
            return $this->key_size_enc;
        }

        // Validate and Set
        // Make sure that the $bit_length parameter is an integer
        // divisible by 8 and at least 8 bits in length. There are 8 bits
        // in 1 byte which is why bit length must be divisible by 8.
        if (!is_int($new_bit_length)) {
            $error = 'When setting a key length from [%s->%s()] the value must be an integer but was instead a [%s].';
            $error = sprintf($error, get_called_class(), __FUNCTION__, gettype($new_bit_length));
            throw new \Exception($error);
        } elseif ($new_bit_length % 8 !== 0) {
            $error = 'When setting a key length from [%s->%s()] the value must be divisible by 8, for example 256 or 512. This function was called with [%d].';
            $error = sprintf($error, get_called_class(), __FUNCTION__, $new_bit_length);
            throw new \Exception($error);
        } elseif ($new_bit_length <= 8) {
            $error = 'When setting a key length from [%s->%s()] value for must be key size of at least 8 bits.';
            $error = sprintf($error, get_called_class(), __FUNCTION__);
            throw new \Exception($error);
        }
        $this->key_size_enc = $new_bit_length;
        return $this;
    }

    /**
     * Returns true if the encryption algorithm specified is using AEAD
     * (Authenticated Encryption with Associated Data) Block Cipher Modes
     * of either GCM or CCM. These two modes of operation are only available
     * on PHP 7.1 and later.
     *
     * @return bool
     */
    public function isAEAD_Mode()
    {
        $mode = strtolower($this->substr($this->encryption_algorithm, -3, 3));
        return ($mode === 'gcm' || $mode === 'ccm');
    }

    /**
     * This function is called by [encrypt()]. When using the default
     * option [dataFormat('type-byte')] it converts the variable for
     * encryption to a string type and appends a single byte at the end
     * of the string to specify the data type. The resulting text string
     * is then encrypted. When [decrypt()] is called and data has
     * successfully been decrypted (and optionally authenticated)
     * then the last byte is read and the same data type is returned.
     *
     * @param mixed $data
     * @return string
     * @throws \Exception
     */
    private function dataToString($data)
    {
        // Return string/bytes as-is if using 'string-only'
        if ($this->data_format === 'string-only') {
            if (is_string($data)) {
                return $data;
            } else {
                $error = 'Error when calling encrypt(), if [dataFormat()] is set to [string-only] then only strings can be encrypted. Data of type [%s] was passed to the function.';
                $error = sprintf($error, gettype($data));
                throw new \Exception($error);
            }
        } elseif ($this->data_format !== 'type-byte') {
            $error = 'Unexpected Error from [%s->encrypt()], the property [dataFormat()] must be one of the following valid values [type-byte, string-only]. This error can only happen when invalid changes are made this class.';
            $error = sprintf($error, __CLASS__);
            throw new \Exception($error);
        }

        // Get Data Type and Convert to a String
        // 0 = Null
        // 1 = String
        // 2 = Int32
        // 3 = Int64
        // 4 = Float (64-Bit)
        // 5 = Bool
        // 6 = JSON (Object or Array)
        if ($data === null) {
            // Null will always be at Bit/Byte 0
            if ($this->allow_null) {
                return chr(0) . chr(0);
            } else {
                $error = 'Unable to encrypt a null value unless [%s->allowNull(true)] is set.';
                $error = sprintf($error, __CLASS__);
                throw new \Exception($error);
            }
        } elseif (is_string($data)) {
            return $data . chr(1);
        } elseif (is_int($data)) {
            // 32-Bit OS/PHP or 64-Bit OS/PHP with 32-Bit Int Size?
            // 32-Bit Int Sizes:
            //   Min = -2147483648 = -(pow(2, 31))  = ~PHP_INT_MAX (PHP_INT_MIN in PHP 7+)
            //   Max =  2147483647 = pow(2, 31) - 1 = PHP_INT_MAX
            if (PHP_INT_SIZE === 4 || ($data >= -2147483648 && $data <= 2147483647)) {
                return (string)$data . chr(2);
            } else {
                return (string)$data . chr(3); // Int64
            }
        } elseif (is_float($data)) {
            // PHP floats are always 64-bit
            return (string)$data . chr(4);
        } elseif (is_bool($data)) {
            return ($data === true ? '1' : '0') . chr(5);
        } elseif (is_array($data) || is_object($data)) {
            return Json::encode($data) . chr(6);
        }

        // Error - likely a [resource] type
        $error = 'Invalid data type for encryption, data passed to encrypt() must be one of the following types: [null, string, int, float, array, object]. Instead [encrypt()] was called with a [%s] data type.';
        $error = sprintf($error, gettype($data));
        throw new \Exception($error);
    }

    /**
     * This gets called from [decrypt()] when using default setting
     * [dataFormat('type-byte')]. It removes the last character of the string
     * which determines the data type and converts the string to the data type
     * that was used during encryption. For example if an [int] was encrypted
     * then an [int] is returned when [decrypt()] is called.
     *
     * @param string $text
     * @return mixed
     * @throws \Exception
     */
    private function stringTypeToData($text)
    {
        // Data Type will be the last byte and text will
        // be all bytes other than the last byte
        $type = ord($this->substr($text, -1, 1));
        $text = $this->substr($text, 0, $this->strlen($text) - 1);

        // Based upon the Numeric ASCII Value of the last byte
        // convert to a string value for the data type and
        // call [stringToData()] from class [AbstractCrypto].
        $types = array(
            0 => 'n',   // Null
            1 => 's',   // String
            2 => 'i32', // Int32
            3 => 'i64', // Int64
            4 => 'f',   // Float
            5 => 'b',   // Boolean
            6 => 'j',   // JSON Object or Array
        );
        $type = (isset($types[$type]) ? $types[$type] : 'Byte ' . (string)$type);
        return $this->stringToData('Decryption', $type, $text);
    }

    /**
     * Called from [decrypt()] this function validates and decodes
     * text from either Base64-Url, Base64, Hex or Plain Bytes.
     *
     * @param string $encrypted_text
     * @return string
     * @throws \Exception
     */
    private function decodeText($encrypted_text)
    {
        // Validate that [$encrypted_text] is a string and not blank
        if (!is_string($encrypted_text)) {
            $error = 'Error when decrypting encrypted text using the decrypt() function. The [$encrypted_text] parameter was not a string and was instead a [%s]. This is a programming error because the function was not called correctly.';
            $error = sprintf($error, gettype($encrypted_text));
            throw new \Exception($error);
        } elseif ($encrypted_text === '') {
            throw new \Exception('Error when decrypting encrypted text using the decrypt() function. The [$encrypted_text] parameter was a blank string. This is a programming error because the function was not called correctly.');
        }

        // Decode and validate that the encrypted text matches the
        // expected encryption format from option [returnFormat()].
        switch ($this->return_format) {
            case 'base64url':
                $encrypted_bytes = Base64Url::decode($encrypted_text);
                if ($encrypted_bytes === false) {
                    throw new \Exception('Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid base-64 url safe string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.');
                }
                break;
            case 'base64':
                $encrypted_bytes = base64_decode($encrypted_text, true);
                if ($encrypted_bytes === false) {
                    throw new \Exception('Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid base-64 string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.');
                }
                break;
            case 'hex':
                if (!$this->validateHexString($encrypted_text)) {
                    throw new \Exception('Error when decrypting encrypted text using the decrypt() function. Either the encrypted text has been modified and is not a valid hex string or the data was encrypted in another format. This error can also happen if another function encoded or decoded the original value.');
                }
                $encrypted_bytes = hex2bin($encrypted_text);
                break;
            case 'bytes':
                $encrypted_bytes = $encrypted_text;
                break;
            default:
                $error = 'Unexpected Error from [%s->%s()], the property [returnFormat()] must be one of the following valid values [base64url, base64, hex, bytes]. This error can only happen when invalid changes are made this class.';
                $error = sprintf($error, __CLASS__, __FUNCTION__);
                throw new \Exception($error);
        }
        return $encrypted_bytes;
    }

    /**
     * Validate size of the encrypted text on [decrypt()]. Block size
     * validation is handled only when using AES/CBC mode. If passing an
     * empty string '' CBC mode will use the minimum block size due to
     * padding while some modes such as CTR will encrypt an empty string
     * and return an empty string as valid encryption/decryption. This function
     * is verified with AES using CBC/CTR/GCM modes. If another encryption
     * algorithm is used or different block mode and decryption fails then
     * it will fail on [openssl_decrypt()] which is ok because this is simply
     * an early check and to provde the developer with helpful messages if
     * [exception_on_error] is set to true.
     *
     * @param string $encrypted_bytes
     * @param int $iv_length
     * @param int $tag_length
     * @param int $hash_length
     * @return void
     * @throws \Exception
     */
    private function validateSize($encrypted_bytes, $iv_length, $tag_length, $hash_length)
    {
        switch ($this->encryption_algorithm) {
            case 'aes-256-cbc':
            case 'aes-128-cbc':
                $block_size = 16;
                break;
            default:
                $block_size = ($this->data_format === 'type-byte' ? 1 : 0);
                break;
        }

        $min_length = $block_size + $iv_length + $tag_length + $hash_length;
        if ($this->strlen($encrypted_bytes) < $min_length) {
            throw new \Exception('The text to decrypt is smaller than the minimum expected text size. The text was either tampered with, encrypted using different settings, or accidently truncated.');
        }
    }

    /**
     * Check the result from [\openssl_decrypt()],
     * if decryption failed then false will be returned.
     *
     * @param string|bool $decrypted_text
     * @return void
     * @throws \Exception
     */
    private function validateDecryption($decrypted_text)
    {
        if ($decrypted_text === false) {
            $error = array();
            while ($msg = openssl_error_string()) {
                $error[] = $msg;
            }
            $error = sprintf('[%s]', implode('], [', $error));
            throw new \Exception('Decryption Failed, Error from openssl: ' . $error);
        }
    }
}