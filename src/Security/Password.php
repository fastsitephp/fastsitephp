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

use FastSitePHP\Security\Crypto\Random;

/**
 * Class to hash a user-suppied password with a secure algorithm
 * [bcrypt or Argon2]. This class includes additional password
 * functionality such as the abilty to generate secure random passwords.
 */
class Password
{
    private $algorithm = 'bcrypt'; // Or 'Argon2'
    private $bcrypt_cost = 10;
    private $argon2_options = null;
    private $password_pepper = null;

    /**
     * Class Constructor
     *
     * If using PHP 5.3 or 5.4 then functions [password_hash() and password_verify()]
     * are created with polyfills using [https://github.com/ircmaxell/password_compat].
     *
     * If using PHP 5.3 then functions [bin2hex()] and [hex2bin()] are polyfilled.
     */
    public function __construct()
    {
        if (PHP_VERSION_ID < 50400) {
            require_once __DIR__ . '/../Polyfill/hex_compat.php';
        }
        if (PHP_VERSION_ID < 50500) {
            // This assumes a standard [vendor] directory is being used
            // and the [ircmaxell] will exist next to [FastSitePHP].
            $path = __DIR__ . '/../../../ircmaxell/password-compat/lib/password.php';
            if (!is_file($path)) {
                // This path is used when developing the main framework and website
                $path = __DIR__ . '/../../vendor/ircmaxell/password-compat/lib/password.php';
            }
            if (!is_file($path)) {
                throw new \Exception('A polyfill from [ircmaxell/password-compat] is required for your version of PHP. Please run [scripts/install.php] or refer to setup instructions.');
            }

            // [include_once] is used rather than [require_once] in case the
            // file doesn't exist; if it doesn't exist and [require_once] is used
            // then a White Screen of Death (WSOD) would likely occur.
            include_once $path;
        }
    }

    /**
     * Create a secure password hash from a user password using using a strong
     * one-way hashing algorithm (bcrypt or Argon2). In a secure application
     * the password itself must not be saved or logged but instead the resulting
     * hash gets saved to the database or storage provider.
     *
     * The resulting text will be 60 characters in length for bcrypt which would
     * need to be the minimum field size in a database. Argon2 will be longer,
     * at least 95 characters and can be larger when using different options.
     *
     * Using default options this is equivalent to calling built-in PHP function:
     *     \password_hash($password, PASSWORD_BCRYPT, ['cost' => 10])
     *
     * And for Argon2:
     *     \password_hash($password, PASSWORD_ARGON2I)
     *
     * @param string $password
     * @return string
     */
    public function hash($password)
    {
        if ($this->password_pepper !== null) {
            $password .= $this->password_pepper;
        }
        if ($this->algorithm === 'bcrypt') {
            return \password_hash($password, PASSWORD_BCRYPT, array('cost' => $this->bcrypt_cost));
        }
        return \password_hash($password, PASSWORD_ARGON2I, $this->argon2_options);
    }

    /**
     * Verify a hashed password. Returns true/false.
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify($password, $hash)
    {
        if ($this->password_pepper !== null) {
            $password .= $this->password_pepper;
        }
        return \password_verify($password, $hash);
    }

    /**
     * Return true if a verified password hash should be re-hashed.
     * For example if bcrypt cost was increased after users started
     * using the app.
     *
     * Example Usage:
     *
     *     if ($password->verify($submitted_password, $hash)) {
     *         if ($password->needsRehash($hash)) {
     *             $new_hash = $password->hash($submitted_password);
     *             // Save to db, etc
     *
     * @param string $hash
     * @return bool
     */
    public function needsRehash($hash)
    {
        if ($this->algorithm === 'bcrypt') {
            return \password_needs_rehash($hash, PASSWORD_BCRYPT, array('cost' => $this->bcrypt_cost));
        }
        return \password_needs_rehash($hash, PASSWORD_ARGON2I, $this->argon2_options);
    }

    /**
     * Return a randomly generated password that is 12 characters in length
     * and contains the following:
     *
     *   4 Uppercase Letters (A - Z)
     *   4 Lowercase Letters (a - z)
     *   2 Digits (0 - 9)
     *   2 Special Characters (~, !, @, #, $, %, ^, &, *, ?, -, _)
     *
     * For strong online password creation with options try:
     *   https://www.lastpass.com/password-generator
     *
     * @return string
     * @throws \Exception
     */
    public function generate()
    {
        $password = '';
        $loop_count = 0; // Prevent endless loop
        $count_ucase = 0; // Need 4
        $count_lcase = 0; // Need 4
        $count_digit = 0; // Need 2
        $count_special_char = 0; // Need 2
        $special_chars = array('~', '!', '@', '#', '$', '%', '^', '&', '*', '?', '-', '_');

        while ($loop_count < 5) {
            $bytes = Random::bytes(100);

            for ($n = 0, $m = strlen($bytes); $n < $m; $n++) {
                $byte = ord($bytes[$n]);

                if ($byte >= ord('A') && $byte <= ord('Z')) {
                    if ($count_ucase < 4) {
                        $count_ucase++;
                        $password .= chr($byte);
                    }
                } elseif ($byte >= ord('a') && $byte <= ord('z')) {
                    if ($count_lcase < 4) {
                        $count_lcase++;
                        $password .= chr($byte);
                    }
                } elseif ($byte >= ord('0') && $byte <= ord('9')) {
                    if ($count_digit < 2) {
                        $count_digit++;
                        $password .= chr($byte);
                    }
                } elseif (in_array($bytes[$n], $special_chars)) {
                    if ($count_special_char < 2) {
                        $count_special_char++;
                        $password .= chr($byte);
                    }
                }

                if ($count_special_char === 2 &&
                    $count_digit === 2 &&
                    $count_lcase === 4 &&
                    $count_ucase === 4
                ) {
                    return $password;
                }
            }

            $loop_count++;
        }

        // This error is not Unit Tested and would likely never
        // happen unless the System's CSPRNG were not working.
        throw new \Exception('Failed to generate password. This is a serious error and your OS might be compromised because the System\'s CSPRNG is likely generating numbers in known order instead of secure and random.');
    }

    /**
     * Specify Cost to use when hashing the Password with bcrypt.
     * Function [findCost()] shows how high the cost can be
     * without slowing down your server.
     *
     * Defaults to 10.
     *
     * @param null|int $new_value
     * @return int|$this
     */
    public function cost($new_value = null)
    {
        if ($new_value === null) {
            return $this->bcrypt_cost;
        }
        $this->bcrypt_cost = $new_value;
        return $this;
    }

    /**
     * This code will benchmark your server to determine how high of a cost you can
     * afford when using bcrypt. You want to set the highest cost that you can without
     * slowing down your server too much. 8-10 is a good baseline, and more is good
     * if your servers are fast enough. The code below aims for ≤ 50 milliseconds
     * stretching time, which is a good baseline for systems handling interactive logins.
     *
     * This function comes directly from the PHP Docs at:
     * https://secure.php.net/manual/en/function.password-hash.php
     *
     * @return int
     */
    public function findCost()
    {
        $timeTarget = 0.05; // 50 milliseconds
        $cost = 8;
        do {
            $cost++;
            $start = microtime(true);
            \password_hash('test', PASSWORD_BCRYPT, array('cost' => $cost));
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);
        return $cost;
    }

    /**
     * Get or set the Algorithm to use: ['bcrypt' or 'Argon2'].
     * [bcrypt] is used by default and supported in all versions
     * of PHP while [Argon2] requires PHP 7.2+.
     *
     * @param null|string $new_value
     * @return string|$this
     * @throws \Exception
     */
    public function algo($new_value = null)
    {
        if ($new_value === null) {
            return $this->algorithm;
        }

        if (!($new_value === 'bcrypt' || $new_value === 'Argon2')) {
            throw new \Exception('Unsupported Algorithm. The only valid options for this class are [bcrypt] and [Argon2].');
        } elseif ($new_value === 'Argon2' && !defined('PASSWORD_ARGON2I')) {
            throw new \Exception('Using [Argon2] is not supported on this server. [Argon2] requires PHP 7.2 or later.');
        }

        if ($new_value === 'Argon2') {
            $this->argon2_options = array(
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
            );
        }

        $this->algorithm = $new_value;
        return $this;
    }

    /**
     * Specify options when using Argon2. For bcrypt use [cost()].
     *
     * Defaults to:
     *   [
     *       'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
     *       'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
     *       'threads'     => PASSWORD_ARGON2_DEFAULT_THREAD,
     *   ]
     *
     * @param null|array $new_value
     * @return array|$this
     */
    public function options($new_value = null)
    {
        if ($new_value === null) {
            return $this->argon2_options;
        }
        $this->argon2_options = $new_value;
        return $this;
    }

    /**
     * Get or set an optional pepper value to use when hashing and verifying the
     * password. For Password Hashing, Pepper is different than the Salt as Pepper
     * is a secret value that is shared by all users. Salt is one of the primary
     * security features of Passwords, while Pepper can be used in certain
     * environments to provide an additional layer of security. If used the
     * Pepper value MUST be saved outside of the database where the password hashes
     * are saved and the pepper value must not be shared with end users. While it
     * can provide an additional level of security it comes with significant
     * complexity so using it requires careful consideration.
     *
     * The pepper value must be a hexadecimal string and one can be generated
     * from the [generatePepper()] function.
     *
     * Advantages of Pepper:
     *  - In a highly structured environment where a Developer would not have access
     *    to the database and the DBA would not have access to the source code then
     *    adding pepper limits the number of people would have required shared secrets.
     *  - If a Database is compromised and hashes are stolen but the Application along
     *    with the Pepper is safe then Pepper can used to prevent dictionary attacks.
     *  - Further Reading:
     *    https://security.stackexchange.com/questions/3272/password-hashing-add-salt-pepper-or-is-salt-enough/3701
     *
     * Disadvantages of Pepper:
     *  - The Pepper value can't be changed without requiring all users of the
     *    application to change their password. This can cause a lot of complexity
     *    for managing users of the app. If all password hashes are stolen then
     *    simply requiring users to change their password would likely be enough
     *    for most apps.
     *  - If the Database and Application are on the same server then someone who
     *    obtains Database Access is more likely to get source code access as well
     *    which defeats the purpose of Pepper.
     *  - The algorithms used in this class (bcrypt and Argon2 with proper salt)
     *    are well studied and known to be secure. Pepper only works in specific cases.
     *  - Further Reading:
     *    https://stackoverflow.com/questions/16891729/best-practices-salting-peppering-passwords
     *    https://blog.ircmaxell.com/2012/04/properly-salting-passwords-case-against.html
     *
     * Other Option:
     *  - Another option instead of using Pepper would be to encrypt the password
     *    before hashing it or encrypt the hash. This has similar advantages and
     *    disadvantages compared to using pepper.
     *
     * For an overview of Salt and Pepper see the following links:
     *    https://en.wikipedia.org/wiki/Salt_(cryptography)
     *    https://en.wikipedia.org/wiki/Pepper_(cryptography)
     *
     * @param null|string $new_hex_value
     * @return null|string|$this
     * @throws \Exception
     */
    public function pepper($new_hex_value = null)
    {
        // Return existing value if no param
        if ($new_hex_value === null) {
            return ($this->password_pepper === null ? $this->password_pepper : \bin2hex($this->password_pepper));
        }

        // Set pepper (must be a valid hex string)
        $len = strlen($new_hex_value);
        if ($len === 0 || $len % 2 !== 0 || !ctype_xdigit($new_hex_value)) {
            throw new \Exception('Invalid Pepper. The pepper value must be a hexadecimal encoded string and the function was called with a non-hex value.');
        }
        $this->password_pepper = \hex2bin($new_hex_value);
        return $this;
    }

    /**
     * Generate a random hex string for the [pepper()] function using
     * cryptographically secure pseudo-random bytes.
     *
     * Defaults to 8 bytes in length (16 characters in hex)
     *
     * @param int $bytes
     * @return string
     */
    public function generatePepper($bytes = 8)
    {
        return \bin2hex(Random::bytes($bytes));
    }
}
