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
 * IBM - DB2 and AS/400 Databases
 *
 * This class provides a thin wrapper for PHP DB2 functions. It reduces the
 * amount of code needed to query IBM databases and provides a compatible
 * class with FastSitePHP's Database class.
 *
 * The IBM DB2 Drivers needed to run this class will not be available on most
 * systems however on an IBM Server such as AIX (AS/400) that has PHP installed
 * the driver will likely be available by default.
 *
 * On IBM Servers PHP is typically installed through Zend Server. PHP supports
 * IBM Severs so the versions of PHP 7.# can be installed on old IBM Servers.
 *
 * If using this class directly on the server you can use the server's credentials
 * and pass [null] for [DSN, User, and Password]. In fact this is recommended
 * as you can get better performance. If empty strings '' are used as shown in the
 * PHP docs online it can cause extra print spool jobs to run so use [null] instead.
 *
 * @link https://www.ibm.com/it-infrastructure/power/os/ibm-i
 * @link https://www.ibm.com/it-infrastructure/power/os/aix
 * @link http://www.zend.com/en/solutions/modernize-ibm-i
 * @link http://files.zend.com/help/Zend-Server/content/i5_installation_guide.htm
 * @link http://php.net/manual/en/book.ibm-db2.php
 * @link http://php.net/manual/en/function.db2-connect.php
 */
class Db2Database extends AbstractVendorDatabase implements DatabaseInterface
{
    protected $func_connect = 'db2_connect';
    protected $func_pconnect = 'db2_pconnect';
    protected $func_close = 'db2_close';
    protected $func_error = 'db2_conn_error';
    protected $func_errormsg = 'db2_conn_errormsg';
    protected $func_exec = 'db2_exec';
    protected $func_prepare = 'db2_prepare';
    protected $func_execute = 'db2_execute';
    protected $func_fetch_array_assoc = 'db2_fetch_assoc';
    protected $func_fetch_array_index = 'db2_fetch_array';
    protected $func_free_result = 'db2_free_result';
    protected $func_num_rows = 'db2_num_rows';
    protected $index_returns_array = true;
    protected $connect_option = array();
}
