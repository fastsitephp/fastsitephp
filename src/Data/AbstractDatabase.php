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

namespace FastSitePHP\Data;

/**
 * This is a PHP Abstract Class which is used as a base class
 * to provide shared functions for multiple Database classes.
 */
abstract class AbstractDatabase
{
    protected $trim_strings = false;

    /**
     * Get or set whether spaces on strings should be trimmed when calling
     * [query(), queryOne(), queryValue(), queryList(), querySets()].
     *
     * When called strings are trimmed after the records are queried and
     * before the function returns the result.
     *
     * Often legacy databases will use [CHAR] text fields over [VARCHAR]
     * or similar types. For example when using a [CHAR] field:
     *     Field: [name] CHAR(20)
     *     Data saved as "John                "
     * 
     * When querying by default the spaces will be returned however if
     * this function is set to [true] then "John" would be returned.
     *
     * Defaults to [false]. Calling this function takes extra memory vs
     * not using it so if you have a high traffic site and want to trim
     * strings you may want to do so in the SQL Statement and keep this
     * [false].
     *
     * For a small amount of records (several hundred or less) this has
     * little or not noticeable impact however if using a large set
     * of records (1,000+) this setting may cause a about a 10% increase
     * in memory or more.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function trimStrings($new_value = null)
    {
        if ($new_value === null) {
            return $this->trim_strings;
        }
        $this->trim_strings = (bool)$new_value;
        return $this;
    }

    /**
     * Used internally to trim strings on an array of records or values. 
     * The "&" symbol is used to modify each value by reference which means 
     * the array passed gets changed. This is one of the only functions that 
     * uses PHP References in FastSitePHP.
     * 
     * PHP References are basically aliases and different than C/C++ Pointers.
     * In general PHP 7 will optimized array's that do not change as Immutable 
     * Arrays; this means that using references can often slow down code and 
     * decrease performance. References do increase memory however here they
     * are used here to prevent large array's from being fully duplicated. 
     * Different versions of this function were developed and tested for 
     * performance to make sure this was the best option.
     * 
     * For small records there is generally no difference (by-val vs by-ref) 
     * however if over a 1000 records are processed at once this function can 
     * often save 5 to 10 MB per call (tested on both PHP 5 and PHP 7).
     * 
     * @link http://php.net/manual/en/language.references.php
     * @param array &$data
     */
    protected function trimArray(&$data)
    {
        if (count($data) > 0 && isset($data[0]) && is_array($data[0])) {
            // Modify an Array of Records (Arrays)
            foreach ($data as &$record) {
                foreach ($record as $field => &$value) {
                    if (is_string($value)) {
                        $record[$field] = trim($value, ' ');
                    }
                }
                unset($value); // Destroy the last Reference
            }
            unset($record);
        } else {
            // Modify an Array of Values
            foreach ($data as &$value) {
                if (is_string($value)) {
                    $value = trim($value, ' ');
                }
            }
            unset($value);
        }
    }
}