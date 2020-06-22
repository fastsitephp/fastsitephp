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

namespace FastSitePHP\Data;

use FastSitePHP\Data\AbstractVendorDatabase;
use FastSitePHP\Data\DatabaseInterface;

/**
 * ODBC Database
 *
 * This class provides a thin wrapper for PHP ODBC functions. It reduces the
 * amount of code needed to query a database and provides a compatible
 * class with FastSitePHP's Database class.
 *
 * ODBC is most common on Windows and especially on older servers or databases.
 * In most cases PDO (FastSitePHP class [Database]) is preferred and will
 * provide more recent drivers however if ODBC with PDO is not available the
 * ODBC on a server then ODBC functions might be. Additionally certain databases
 * such as IBM may only work through ODBC on some servers.
 *
 * IMPORTANT - If using this class you may need to call the fuction
 * [allowLargeTextValues()] if working with records that have large text or
 * binary data.
 *
 * @link http://php.net/manual/en/ref.uodbc.php
 */
class OdbcDatabase extends AbstractVendorDatabase implements DatabaseInterface
{
    /**
     * Core functions exist in the class [AbstractVendorDatabase].
     * Protected Member variables define which functions get called.
     */
    protected $func_connect = 'odbc_connect';
    protected $func_pconnect = 'odbc_pconnect';
    protected $func_close = 'odbc_close';
    protected $func_error = 'odbc_error';
    protected $func_errormsg = 'odbc_errormsg';
    protected $func_exec = 'odbc_exec';
    protected $func_prepare = 'odbc_prepare';
    protected $func_execute = 'odbc_execute';
    protected $func_fetch_array_assoc = 'odbc_fetch_array';
    protected $func_fetch_array_index = 'odbc_fetch_into';
    protected $func_free_result = 'odbc_free_result';
    protected $func_num_rows = 'odbc_num_rows';
    protected $index_returns_array = false;
    // The default option [SQL_CUR_USE_ODBC] greatly improves
    // performance for some databases when using [odbc_fetch_array].
    protected $connect_option = SQL_CUR_USE_ODBC;

    /**
     * Use when needed to make sure that ODBC will return large text fields.
     * By default only the first 4096 characters are returned.
     *
     * This sets the PHP INI Setting 'odbc.defaultlrl' to the specified size.
     *
     * @link http://php.net/manual/en/odbc.configuration.php#ini.uodbc.defaultlrl
     * @param int $size - Defaults to 100000 (100,000)
     * @return void
     */
    public function allowLargeTextValues($size = 100000)
    {
        ini_set('odbc.defaultlrl', (string)$size);
    }
}
