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

namespace FastSitePHP\Data\KeyValue;

interface StorageInterface
{
    public function get($key, $default_value = null);
    public function set($key, $value);
    public function exists($key);
    public function remove($key);
}
