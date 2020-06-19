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
use FastSitePHP\Security\Crypto\CryptoInterface;
use FastSitePHP\Security\Crypto\Random;
use FastSitePHP\Security\Crypto\PublicKey;

/**
 * JSON Web Tokens (JWT)
 *
 * @link https://jwt.io/
 * @link https://tools.ietf.org/html/rfc7519
 * @link https://en.wikipedia.org/wiki/JSON_Web_Token
 * @link http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html
 */
class JWT implements CryptoInterface
{
    // Default Algorithm, see comments in [algo()]
    private $algorithm = 'HS256';
    private $allowed_algos = array('HS256');

    // Supported Algorithms, see comments in [allowedAlgos()]
    private $algorithms = array(
        'HS256' => array('hmac', 'sha256', 32),
        'HS384' => array('hmac', 'sha384', 48),
        'HS512' => array('hmac', 'sha512', 64),
        'RS256' => array('rsa',  'sha256', null),
        'RS384' => array('rsa',  'sha384', null),
        'RS512' => array('rsa',  'sha512', null),
    );

    // Misc Settings
    private $use_insecure_key = false;
    private $exception_on_error = false;
    private $use_mbstring = true;

    // Claim Validation Settings
    private $validate_defined_claims = true;
    private $issuers = null;
    private $subject = null;
    private $audience_list = null;
    private $expiration_time = false;
    private $expiration_time_leeway = 0;
    private $not_before = false;
    private $not_before_leeway = 0;
    private $issued_at = false;
    private $jwt_id = null;

    /**
     * Class Constructor
     *
     * If using PHP 5.5 or below then [hash_equals()] is polyfilled,
     * and [bin2hex()] and [hex2bin()] are polyfilled for PHP 5.3.
     */
    function __construct()
    {
        if (PHP_VERSION_ID < 50400) {
            require_once __DIR__ . '/../../Polyfill/hex_compat.php';
        }
        if (PHP_VERSION_ID < 50600) {
            require_once __DIR__ . '/../../Polyfill/hash_equals_compat.php';
        }

        // Check whether to use multi-byte string
        // functions when the class is created.
        $this->use_mbstring = extension_loaded('mbstring');
    }

    /**
     * Generate a secure key based on the algorithm specified from the function [algo()].
     * When using HMAC the key is returned in either Base64 of Hex format depending on the
     * $type parameter (defaults to Base64); and when using RSA an array is returned
     * in the format of [private_key, public_key].
     *
     * @param string $key_type - 'base64' or 'hex'
     * @return string|array
     */
    public function generateKey($key_type = 'base64')
    {
        list($type, $algo, $len) = $this->algorithms[$this->algorithm];
        if ($type === 'hmac') {
            $bytes = Random::bytes($len);
            return ($key_type === 'base64' ? base64_encode($bytes) : bin2hex($bytes));
        } else {
            return PublicKey::generateRsaKeyPair();
        }
    }

    /**
     * Encode and Create a JWT. The algorithm to use can be specified from the
     * function [algo()]. By default a secure key is required and one can be
     * generated from the function [generateKey()]. If a weak key is required for
     * compatibility with other code see the function [useInsecureKey()].
     *
     * @param array|object $payload - Data to encode
     * @param string $key
     * @return string - JSON Web Token (JWT)
     * @throws \Exception
     */
    public function encode($payload, $key)
    {
        // Validate the Payload
        if (!(is_array($payload) || is_object($payload))) {
            throw new \Exception(sprintf('Error - Invalid data for encoding as a JWT. Only arrays or objects are allowed for the payload but received a [%s].', gettype($payload)));
        }

        // Get Key and Settings
        $key = $this->getKey($key, $this->algorithm, false);
        list($type, $algo) = $this->algorithms[$this->algorithm];

        // Build Header
        $header = Base64Url::encode(Json::encode(array(
            'alg' => $this->algorithm,
            'typ' => 'JWT',
        )));

        // Encode Payload and Build String to Sign
        $payload = Base64Url::encode(Json::encode($payload));
        $data = $header . '.' . $payload;

        // Sign
        if ($type === 'hmac') {
            $signature = \hash_hmac($algo, $data, $key, true);
        } else {
            $success = \openssl_sign($data, $signature, $key, $algo);
            if ($success === false) {
                throw new \Exception($this->getOpenSslError(__FUNCTION__, 'openssl_sign'));
            }
        }

        // Return JWT String
        return $data . '.' . Base64Url::encode($signature);
    }

    /**
     * Decode and Verify a JWT. If the token is not valid null will be
     * returned unless [exceptionOnError(true)] is set and then an exception
     * will be thrown.
     *
     * Unless settings are changed from [allowedAlgos()] only the default
     * algorithm 'HS256' is accepted for decoding.
     *
     * If the payload has a claim defined then it will be validated by default.
     * This can be turned off by calling [validateDefinedClaims(false)].
     *
     * To require and validate specific claims use the [require*()/allowed*()]
     * getter/setter functions of this class.
     *
     * @param string $token
     * @param string $key
     * @return array|null - The payload that was originally encoded.
     * @throws \Exception
     */
    public function decode($token, $key)
    {
        try {
            // Parse and Validate Token
            list($header, $payload, $user_hash, $hash_text) = $this->parseToken($token);
            $this->validateHeader($header);

            // Get Key and Settings
            $alg = $header['alg'];
            $key = $this->getKey($key, $alg, true);
            list($type, $algo) = $this->algorithms[$alg];

            // Verify
            if ($type === 'hmac') {
                $calc_hash = \hash_hmac($algo, $hash_text, $key, true);
                if (!hash_equals($calc_hash, $user_hash)) {
                    throw new \Exception('Error - Unable to verify JWT; either the singing key changed from when the token was signed or the token has been tampered with.');
                }
            } else {
                $result = \openssl_verify($hash_text, $user_hash, $key, $algo);
                if ($result !== 1) {
                    throw new \Exception('Error - Unable to verify JWT; either the singing key changed from when the token was signed or the token has been tampered with.');
                }
            }

            // Validate Claims and Return Payload if Valid
            $this->validateClaims($payload);
            return $payload;
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
     * Helper function to add a JWT field value to either an array or object.
     * Use this prior to calling [encode()]. Time fields ['exp', 'nbf', 'iat']
     * must be either an integer representing a Unix Timestamp or a string that
     * is valid for the PHP function [strtotime()].
     *
     * Example to expire a Token after 10 minutes:
     *     $payload = $jwt->addClaim($payload, 'exp', '+10 minutes');
     *
     * @param array|object $payload - Payload for encoding
     * @param string $claim - Standard JWT Field ['iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti']
     * @param string|int $value
     * @return array|object - The modified payload
     * @throws \Exception
     */
    public function addClaim($payload, $claim, $value)
    {
        // Check for standard field
        $claims = array('iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti');
        if (!in_array($claim, $claims, true)) {
            $error = 'Error adding JWT Claim. Only standard claims are accepted [%s].';
            $error = sprintf($error, implode(', ', $claims));
            throw new \Exception($error);
        }

        // Parse the string date value into a Unix Timestamp (example '+1 hour')
        $date_claims = array('exp', 'nbf', 'iat');
        if (in_array($claim, $date_claims, true) && is_string($value)) {
            $value = strtotime($value);
            if ($value === false) {
                $error = 'Error adding JWT Claim. Invalid [%s] claim. A string was passed however it could not be converted to a valid timestamp. The value must be either a integer representing a Unix Timestamp or a valid string for the PHP function [strtotime()], examples include \'+1 hour\' and \'+30 minutes\'.';
                $error = sprintf($error, $claim);
                throw new \Exception($error);
            }
        }

        // Check data type
        $expected = $this->claimType($claim);
        $type = gettype($value);
        if ($type !== $expected) {
            $error = 'Error adding JWT Claim. Field [%s] should be a [%s] but received a [%s].';
            $error = sprintf($error, $claim, $expected, $type);
            throw new \Exception($error);
        }

        // Add to Array or Object
        if (is_array($payload)) {
            $payload[$claim] = $value;
        } elseif (is_object($payload)) {
            $payload->{$claim} = $value;
        } else {
            $error = 'Error adding JWT Claim. Payload should be either an array or object but was a [%s].';
            $error = sprintf($error, gettype($payload));
            throw new \Exception($error);
        }
        return $payload;
    }

    /**
     * By default [decode()] returns null when a JWT cannot be validated or
     * invalid settings are used. When the value of this function is set to
     * [true] then an exception will be thrown instead.
     *
     * @param string|null $value
     * @return bool|$this
     */
    public function exceptionOnError($value = null)
    {
        if ($value === null) {
            return $this->exception_on_error;
        }
        $this->exception_on_error = (bool)$value;
        return $this;
    }

    /**
     * Used to validate that the key is correct based on the selected algorithm.
     * @param string $key
     * @param string $algorithm
     * @param bool $public_key
     * @return string
     * @throws \Exception
     */
    private function getKey($key, $algorithm, $public_key)
    {
        list($type, $algo, $len) = $this->algorithms[$algorithm];
        if ($type === 'hmac') {
            // By default make sure HMAC Keys are either Hex or Base64 encoded using proper length
            if ($this->use_insecure_key) {
                return $key;
            } else {
                if (ctype_xdigit($key) && $this->strlen($key) === ($len * 2)) {
                    $key_bytes = hex2bin($key);
                } else {
                    $key_bytes = base64_decode($key, true);
                }
                if ($key_bytes === false) {
                    throw new \Exception('Error - Invalid Key for JWT using HMAC. By default this class only allows hex or base64 keys. If you are verifying a JWT signed with another library call [useInsecureKey()] first or use the PHP functions [base64_encode()] or [bin2hex()] to encode the key.');
                }
                if ($this->strlen($key_bytes) !== $len) {
                    $error = 'Error - Invalid Key Size. For the [%s] this class requires a key that is [%d] bytes in length before base64 or hex encoding. This requirement can be turned off by calling [%s->useInsecureKey(true)].';
                    $error = sprintf($error, $algorithm, $len, __CLASS__);
                    throw new \Exception($error);
                }
                $key = $key_bytes;
            }
        } else {
            // Make sure the correct RSA Key is used, this is intended as a helpful
            // message for when developers call encode/decode using the wrong key type.
            $is_public = (strpos($key, '-----BEGIN PUBLIC KEY-----') !== false ? true : false);
            $is_private = (strpos($key, '-----BEGIN PRIVATE KEY-----') !== false ? true : false);
            $is_valid = ($is_public || $is_private);
            if (!$is_valid) {
                $error = 'Error - Invalid Key. An RSA Key is required for JWT when using [%s].';
                $error = sprintf($error, $algorithm);
                throw new \Exception($error);
            }
            if ($public_key && !$is_public) {
                throw new \Exception('Error – Invalid Key. Public Key is required when decoding Tokens but Private Key was passed.');
            } elseif (!$public_key && !$is_private) {
                throw new \Exception('Error – Invalid Key. Private Key is required when encoding Tokens but Public Key was passed.');
            }
        }
        return $key;
    }

    /**
     * Called from [decode()] to parse and validate the token format
     * @param string $token
     * @return array
     * @throws \Exception
     */
    private function parseToken($token)
    {
        // Validate JWT Format
        if (gettype($token) !== 'string') {
            $error = 'Invalid token passed to [%s->%s()]. Expected a [string] but received a [%s].';
            $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($token));
            throw new \Exception($error);
        } elseif (count(explode('.', $token)) !== 3) {
            $error = 'Invalid token passed to [%s->%s()]. Format must be in the format of \'header.payload.signature\'.';
            $error = sprintf($error, __CLASS__, __FUNCTION__);
            throw new \Exception($error);
        }

        // Parse Fields and Decode
        $data = explode('.', $token);
        $header = Base64Url::decode($data[0]);
        $payload = Base64Url::decode($data[1]);
        $user_hash = Base64Url::decode($data[2]);
        if ($header === false || $payload === false || $user_hash === false) {
            $error = 'Invalid JWT for Decoding. All fields [header, payload, or signature] must use Base64-URL format.';
            throw new \Exception($error);
        }
        $header = Json::decode($header);
        $payload = Json::decode($payload);
        $hash_text = $data[0] . '.' . $data[1];
        return array($header, $payload, $user_hash, $hash_text);
    }

    /**
     * Called from [decode()] to validate the JWT Header
     * @param array $header
     * @return void
     * @throws \Exception
     */
    private function validateHeader($header)
    {
        if (!(isset($header['typ']) && isset($header['alg']))) {
            $error = 'Invalid JWT for Decoding. The header does not contain either [typ] or [alg].';
            throw new \Exception($error);
        } elseif ($header['typ'] !== 'JWT') {
            throw new \Exception('Invalid JWT for Decoding. The header contains an invalid [typ] value.');
        } elseif (gettype($header['alg']) !== 'string') {
            throw new \Exception('Invalid JWT for Decoding. The header contains an invalid [alg] data type.');
        } elseif (!in_array($header['alg'], $this->allowed_algos, true)) {
            $error = 'Invalid JWT for Decoding. Algorithm for the JWT is [%s], however this class only allows for [%s]. To change this option specify different algorithms in [%s->allowedAlgos()].';
            $error = sprintf($error, $header['alg'], implode($this->allowed_algos), __CLASS__);
            throw new \Exception($error);
        }
    }

    /**
     * Get string length using [mb_strlen()] if the extension
     * [mbstring] is loaded otherwise use [strlen()].
     *
     * @param string $str
     * @return int
     */
    private function strlen($str)
    {
        if ($this->use_mbstring) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }

    /**
     * Return an error generated by openssl when using RSA.
     * @param string $calling_function
     * @param string $openssl_func
     * @return string
     */
    private function getOpenSslError($calling_function, $openssl_func)
    {
        $error = sprintf('Error with [%s::%s()] at [%s()]:', __CLASS__, $calling_function, $openssl_func);
        while ($msg = openssl_error_string()) {
            $error .= "\n" . $msg;
        }
        return $error;
    }

    /**
     * Used to validate claims when decoding.
     * Claims are validated in the order that they appear in the RFC.
     * @param array $payload
     * @return void
     * @throws \Exception
     * @link https://tools.ietf.org/html/rfc7519#section-4.1
     */
    private function validateClaims($payload)
    {
        if ($this->checkClaim($payload, 'iss') || $this->issuers) {
            $iss = $this->getClaim($payload, 'iss');
            if ($this->issuers === null || count($this->issuers) === 0) {
                $error = 'Error - JWT Validation failed. Submitted [iss] field however no issuers are defined in [allowedIssuers()].';
                throw new \Exception($error);
            } elseif (!in_array($iss, $this->issuers, true)) {
                $error = 'Error - JWT Validation failed. Submitted [iss] field [%s] does not match one of the required issuers [%s].';
                $error = sprintf($error, $iss, implode(', ', $this->issuers));
                throw new \Exception($error);
            }
        }
        if ($this->checkClaim($payload, 'sub') || $this->subject) {
            $sub = $this->getClaim($payload, 'sub');
            if ($this->subject === null || $this->subject === '') {
                $error = 'Error - JWT Validation failed. Submitted [sub] field however a subject is not defined in [requireSubject()].';
                throw new \Exception($error);
            } elseif ($sub !== $this->subject) {
                $error = 'Error - JWT Validation failed. Submitted [sub] field [%s] does not match the subject value [%s].';
                $error = sprintf($error, $sub, $this->subject);
                throw new \Exception($error);
            }
        }
        if ($this->checkClaim($payload, 'aud') || $this->audience_list) {
            $aud = $this->getClaim($payload, 'aud');
            if ($this->audience_list === null || count($this->audience_list) === 0) {
                $error = 'Error - JWT Validation failed. Submitted [aud] field however no audiences are defined in [allowedAudiences()].';
                throw new \Exception($error);
            } elseif (!in_array($aud, $this->audience_list, true)) {
                $error = 'Error - JWT Validation failed. Submitted [aud] field [%s] does not match one of the required audience values [%s].';
                $error = sprintf($error, $aud, implode(', ', $this->audience_list));
                throw new \Exception($error);
            }
        }
        if ($this->checkClaim($payload, 'exp') || $this->expiration_time) {
            $exp = $this->getClaim($payload, 'exp');
            $now = time();
            $leeway = $this->expiration_time_leeway;
            if ($exp < ($now - $leeway)) {
                throw new \Exception('Error - JWT Validation failed. Token is expired.');
            }
        }
        if ($this->checkClaim($payload, 'nbf') || $this->not_before) {
            $nbf = $this->getClaim($payload, 'nbf');
            $now = time();
            $leeway = $this->not_before_leeway;
            if ($nbf > ($now + $leeway)) {
                $error = 'Error - JWT Validation failed. The token is valid but it cannot be used before [%s] which is the value specified by the [nbf] field.';
                $error = sprintf($error, date(DATE_RFC2822, $nbf));
                throw new \Exception($error);
            }
        }
        if ($this->checkClaim($payload, 'iat') || $this->issued_at) {
            // Simply validate field exist with correct data type
            $this->getClaim($payload, 'iat');
        }
        if ($this->checkClaim($payload, 'jti') || $this->jwt_id) {
            $jti = $this->getClaim($payload, 'jti');
            if ($this->jwt_id === null || $this->jwt_id === '') {
                $error = 'Error - JWT Validation failed. Submitted [jti] field however a JWT ID is not defined from [requireJwtId()].';
                throw new \Exception($error);
            } elseif ($jti !== $this->jwt_id) {
                $error = 'Error - JWT Validation failed. Submitted [jti] field [%s] does not match the required JWT ID [%s].';
                $error = sprintf($error, $jti, $this->jwt_id);
                throw new \Exception($error);
            }
        }
    }

    /**
     * Helper function used for validation
     * @param array $payload
     * @param string $field
     * @return bool
     */
    private function checkClaim($payload, $field)
    {
        if (!$this->validate_defined_claims) {
            return false;
        }
        return isset($payload[$field]);
    }

    /**
     * Helper function used for validation
     * @param string $field
     * @return string
     */
    private function claimType($field)
    {
        switch ($field) {
            case 'exp':
            case 'nbf':
            case 'iat':
                return 'integer';
            default:
                return 'string';
        }
    }

    /**
     * Used to validate that a claim exists and data type is correct when decoding.
     * @param array $payload
     * @param string $field
     * @return mixed
     * @throws \Exception
     */
    private function getClaim($payload, $field)
    {
        if (!isset($payload[$field])) {
            throw new \Exception(sprintf('Error - JWT Validation failed. Missing required field [%s] from JWT Payload.', $field));
        }

        $value = $payload[$field];
        $expected = $this->claimType($field);
        $type = gettype($value);
        if ($type !== $expected) {
            $error = 'Error - JWT Validation failed. Field [%s] should be a [%s] but received a [%s].';
            $error = sprintf($error, $field, $expected, $type);
            throw new \Exception($error);
        }

        return $value;
    }

    /**
     * Get or set the default JWT Algorithm to use, for supported algorithms
     * see comments in [allowedAlgos()]. Defaults to 'HMAC' with 'SHA256'.
     *
     * @param string|null $new_value
     * @return string|$this
     */
    public function algo($new_value = null)
    {
        // Get
        if ($new_value === null) {
            return $this->algorithm;
        }

        // Set
        $this->validateAlgo($new_value, __FUNCTION__);
        $this->algorithm = $new_value;
        return $this;
    }

    /**
     * Get or set an array of allowed JWT Algorithms to accept when decoding.
     *
     * HMAC [HS256, HS384, HS512] and RSA [RS256, RS384, RS512] are supported.
     * Elliptic Curve Digital Signature Algorithms (ECDSA) [ES256, ES384, ES512]
     * are not supported by this class because PHP's OpenSSL implementation
     * does not provide built-in support.
     *
     * If need to use (ECDSA) with PHP there are several options:
     *   Encode/Decode Fast (PHP C Extension):
     *     https://github.com/cdoco/php-jwt
     *   Encode/Decode Slower (Using PHP Code):
     *     https://github.com/lcobucci/jwt
     *     https://github.com/web-token/jwt-framework
     *
     * @param array|null $new_value
     * @return array|$this
     */
    public function allowedAlgos(array $new_value = null)
    {
        // Get
        if ($new_value === null) {
            return $this->allowed_algos;
        }

        // Set
        foreach ($new_value as $algo) {
            $this->validateAlgo($algo, __FUNCTION__);
        }
        $this->allowed_algos = $new_value;
        return $this;
    }

    /**
     * Validate the specified JWT Algorithm
     *
     * @param mixed $algo
     * @param string $calling_function
     * @return void
     * @throws \Exception
     */
    private function validateAlgo($algo, $calling_function)
    {
        if (gettype($algo) !== 'string') {
            $error = 'Error calling [%s->%s()]. Expected a [string] but received a [%s].';
            $error = sprintf($error, __CLASS__, $calling_function, gettype($algo));
            throw new \Exception($error);
        } elseif (!isset($this->algorithms[$algo])) {
            $error = 'Error calling [%s->%s()]. Algorithm [%s] is not supported for this JWT Class. The only supported algorithms are [%s].';
            $error = sprintf($error, __CLASS__, $calling_function, $algo, implode(', ', array_keys($this->algorithms)));
            throw new \Exception($error);
        }
    }

    /**
     * Get or set Key Requirement when using and HMAC JWT [HS256, HS384, HS512].
     * Defaults to [false] and with default settings a strong key size must be used.
     *
     * This should only be set to [true] if compatibility with other code is needed.
     * Often online samples use common passwords such as 'secret' when signing JWT's
     * which is why this setting was created.
     *
     * @param bool|null $new_value
     * @return bool|$this
     */
    public function useInsecureKey($new_value = null)
    {
        if ($new_value === null) {
            return $this->use_insecure_key;
        }
        $this->use_insecure_key = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set whether defined claims must be validated when [decode()] is
     * called. Defaults to true.
     *
     * Example if the payload has a value for [exp] then by default the JWT
     * Expiration Time is checked otherwise [requireExpireTime()] would have
     * to be set.
     *
     * @param bool|null $new_value
     * @return bool|$this
     */
    public function validateDefinedClaims($new_value = null)
    {
        if ($new_value === null) {
            return $this->validate_defined_claims;
        }
        $this->validate_defined_claims = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set an allowed list of values for "iss" (Issuer) Claim
     * @param array|null $new_value - If set then "iss" is required and must be in the array
     * @return array|null|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.1
     */
    public function allowedIssuers(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->issuers;
        }
        $this->issuers = $new_value;
        return $this;
    }

    /**
     * Get or set validation for the "sub" (Subject) Claim
     * @param string|null $new_value - If set then "sub" is required and must match
     * @return string|null|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.2
     */
    public function requireSubject($new_value = null)
    {
        if ($new_value === null) {
            return $this->subject;
        }
        $this->subject = (string)$new_value;
        return $this;
    }

    /**
     * Get or set an allowed list of audience values for the "aud" (Audience) Claim
     * @param array|null $new_value - If set then "aud" is required and must be in the array
     * @return array|null|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.3
     */
    public function allowedAudiences(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->audience_list;
        }
        $this->audience_list = $new_value;
        return $this;
    }

    /**
     * Get or set validation for the "exp" (Expiration Time) Claim
     * @param bool|null $new_expiration_time - If [true] then "exp" is required and must be valid
     * @param int|null $leeway - Time in seconds if leeway is to be used for clock skew
     * @return array|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.4
     */
    public function requireExpireTime($new_expiration_time = null, $leeway = null)
    {
        if ($new_expiration_time === null) {
            return array($this->expiration_time, $this->expiration_time_leeway);
        }
        $this->expiration_time = (bool)$new_expiration_time;
        $this->expiration_time_leeway = (int)$leeway;
        return $this;
    }

    /**
     * Get or set validation for the "nbf" (Not Before) Claim
     * @param bool|null $new_not_before_time - If [true] then "nbf" is required and must be valid
     * @param int|null $leeway - Time in seconds if leeway is to be used for clock skew
     * @return array|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.5
     */
    public function requireNotBefore($new_not_before_time = null, $leeway = null)
    {
        if ($new_not_before_time === null) {
            return array($this->not_before, $this->not_before_leeway);
        }
        $this->not_before = (bool)$new_not_before_time;
        $this->not_before_leeway = (float)$leeway;
        return $this;
    }

    /**
     * Get or set validation for the "iat" (Issued At) Claim
     * @param bool|null $new_value - If [true] then "iat" is required and must be a valid number
     * @return bool|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.6
     */
    public function requireIssuedAt($new_value = null)
    {
        if ($new_value === null) {
            return $this->issued_at;
        }
        $this->issued_at = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set validation for the "jti" (JWT ID) Claim
     * @param string|null $new_value - If set then "jti" is required and must match
     * @return bool|$this
     * @link https://tools.ietf.org/html/rfc7519#section-4.1.7
     */
    public function requireJwtId($new_value = null)
    {
        if ($new_value === null) {
            return $this->jwt_id;
        }
        $this->jwt_id = $new_value;
        return $this;
    }
}
