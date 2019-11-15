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
 * Generates cryptographically secure pseudo-random bytes.
 */
class Random
{
    /**
     * This function calls [random_bytes()] for newer versions of PHP and if  
     * using any version of PHP 5 then the function is first polyfilled using 
     * compatibility functions from the [paragonie/random_compat] library. 
     * [paragonie/random_compat] is widely used and known to be secure. 
     * It is used in WordPress and many other projects.
     * 
     * @link http://php.net/manual/en/function.random-bytes.php
     * @link https://github.com/paragonie/random_compat
     * @param int $length
     * @return string
     */
    public static function bytes($length)
    {
        if (PHP_VERSION_ID < 70000 && !function_exists('random_bytes')) {
            // This assumes a standard [vendor] directory is being used
            // and the [paragonie] will exist next to [FastSitePHP].
            $path = __DIR__ . '/../../../../paragonie/random_compat/lib/random.php';
            if (!is_file($path)) {
                // This path is used when developing the main framework and website
                $path = __DIR__ . '/../../../vendor/paragonie/random_compat/lib/random.php';
            }
            if (!is_file($path)) {
                throw new \Exception('A polyfill from [paragonie/random_compat] is required for your version of PHP. Please run [scripts/install.php] or refer to setup instructions.');
            }

			// [include_once] is used rather than [require_once] in case the 
			// file doesn't exist; if it doesn't exist and [require_once] is used
			// then a White Screen of Death (WSOD) would likely occur.
            include_once $path;
        }
        return random_bytes($length);
    }
}