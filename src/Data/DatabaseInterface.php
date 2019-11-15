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

interface DatabaseInterface
{
    function __construct($dsn, $persistent = false, $user = null, $password = null);
    public function query($sql, array $params = null);
    public function queryOne($sql, array $params = null);
    public function queryValue($sql, array $params = null);
    public function queryList($sql, array $params = null);
    public function execute($sql, array $params = null);
    public function executeMany($sql, array $records);
}