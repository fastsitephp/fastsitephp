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

/*
This file is a port of the following Node Package:

    https://www.npmjs.com/package/dotenv
    https://github.com/motdotla/dotenv

    LICENSE: BSD 2-Clause "Simplified" License

-------------------------------------------------------------------------------
Copyright (c) 2015, Scott Motte
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace FastSitePHP\Environment;

/**
 * Loads environment variables from a [.env] file into [getenv()] and [$_ENV].
 *
 * This class is a port of the widely used node [dotenv] package so the
 * following syntax is supported:
 *
 *   - Empty lines are skipped
 *   - Lines beginning with # are treated as comments
 *   - Empty values become empty strings ([EMPTY=] becomes {EMPTY: ''})
 *   - Whitespace is trimmed for unquoted values ([FOO= some value ] becomes {FOO: 'some value'})
 *   - Single and double quoted values are escaped ([SINGLE_QUOTE='quoted'] becomes {SINGLE_QUOTE: "quoted"})
 *   - Double quoted values expand new lines (example: [MULTILINE="new\nline"])
 *
 * This class is minimal like the node pacakage and does not support
 * nested variables, inline shell execution, or advanced validation.
 * If you prefer to use those features then PHP packages [vlucas/phpdotenv]
 * or [symfony/dotenv] are recommended.
 *
 * Because FastSitePHP's DotEnv Class is minimal and has fast performance it can be
 * used for production sites, however it’s a good idea to load it from middleware
 * or controller logic on needed routes rather than loading it for every route.
 *
 * @link https://www.npmjs.com/package/dotenv
 * @link https://github.com/vlucas/phpdotenv
 * @link https://github.com/symfony/dotenv
 */
class DotEnv
{
    /**
     * Load a [.env] file from a directory. The directory and file must exist
     * or an exception will be thrown.
     *
     * An optional array [$required_vars] can be passed and if any key from
     * the array do not exist in the file an exception will be thrown.
     * The actual values are not validated by this class.
     *
     * Returns an array of all variables read from the file.
     *
     * @param string $dir_path
     * @param null|array $required_vars
     * @return array
     * @throws \Exception
     */
    public static function load($dir_path, array $required_vars = array())
    {
        if (!is_dir($dir_path)) {
            throw new \Exception(sprintf('Error - Directory [%s] was not found', $dir_path));
        }
        $file_path = realpath($dir_path) . DIRECTORY_SEPARATOR . '.env';
        return self::loadFile($file_path, $required_vars);
    }

    /**
     * Load a file using [.env] file format. The full path of the file is
     * specified so it can be named anything.
     *
     * @param string $file_path
     * @param null|array $required_vars
     * @return array
     * @throws \Exception
     */
    public static function loadFile($file_path, array $required_vars = array())
    {
        // Validate and read file as a string
        if (!is_file($file_path)) {
            throw new \Exception(sprintf('Error - File [%s] was not found', $file_path));
        }
        $contents = file_get_contents($file_path);

        // [.env] files should use [LF] but just in case,
        // normalize line endings [CRLF -> LF]
        $contents = str_replace("\r\n", "\n", $contents);

        // Parse
        $lines = explode("\n", $contents);
        $vars = array();
        foreach ($lines as $line) {
            $pattern = '/^\s*([\w.-]+)\s*=\s*(.*)?\s*$/';
            if (preg_match($pattern, $line, $matches)) {
                # [$value] will equal '' if missing
                $key = $matches[1];
                $value = $matches[2];

                $len = strlen($value) - 1;
                $is_single_quoted = ($len > 1 && $value[0] === "'" && $value[$len] === "'");
                $is_double_quoted = ($len > 1 && $value[0] === '"' && $value[$len] === '"');

                if ($is_single_quoted || $is_double_quoted) {
                    // If single or double quoted, remove quotes
                    $value = substr($value, 1, $len - 1);
                    // If double quoted, expand newlines
                    if ($is_double_quoted) {
                        $value = str_replace("\\n", "\n", $value);
                    }
                } else {
                    // Trim whitespace
                    $value = trim($value);
                }

                $vars[$key] = $value;
            }
        }

        // Optional validation for required keys
        if ($required_vars) {
            $diff = array_diff($required_vars, array_keys($vars));
            if ($diff) {
                $error = 'Missing the following keys from a [.env] file: [%s]';
                $error = sprintf($error, implode(', ', $diff));
                throw new \Exception($error);
            }
        }

        // Set environment variables if not already set and return
        // the full array/dictionary of values from the file.
        foreach ($vars as $key => $value) {
            if (getenv($key) === false) {
                putenv("$key=$value");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }
        return $vars;
    }
}
