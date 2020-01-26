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

use FastSitePHP\Data\AbstractDatabase;
use FastSitePHP\Data\DatabaseInterface;

/**
 * Abstract Class for PHP Vendor Database Extensions and ODBC Functions.
 * 
 * This function makes it easy to define a class that uses PHP Vendor
 * Database Functions. To use this class the inheriting class needs to
 * define which functions to call from protected member variables. 
 * See [OdbcDatabase] or [Db2Database] for examples.
 * 
 * This class works by using [call_user_func()] and [call_user_func_array()]
 * on the function names. For example this code:
 *     $db = odbc_connect($dsn, $user, $password);
 *     $resource = odbc_exec($db, $sql);
 *     odbc_fetch_into($resource, $row);
 *     odbc_free_result($resource);
 *     odbc_close($db);
 * Is called like this here:
 *     $db = call_user_func($func_pconnect, $dsn, $user, $password);
 *     $resource = call_user_func($func_exec, $this->db, $sql);
 *     call_user_func_array($func_fetch_array_index, array($resource, &$row));
 *     call_user_func($func_free_result, $resource);
 *     call_user_func($func_close, $this->db);
 * 
 * The function [call_user_func_array()] is used when one of the function
 * parameters is by reference.
 *
 * @link http://php.net/manual/en/refs.database.vendors.php
 * @link http://php.net/manual/en/ref.uodbc.php
 */
class AbstractVendorDatabase extends AbstractDatabase implements DatabaseInterface
{
    /**
     * Set after the database is connected to based on connection type
     * @var bool
     */
    private $persistent = false;

    /**
     * Connection for the Database
     * @var null|\resource
     */
    public $db = null;

    /**
     * Class Constructor. Creates Db Connection.
     *
     * @param string $dsn - Database Connection String
     * @param null|string $user - User Name for the Connection
     * @param null|string $password - Password for the User
     * @param bool $persistent - If [true] then PHP will keep a persistent connection to the database after the script finishes.
     * @param mixed $options - Default options exist for each database class. To customize refer to PHP documentation based on the driver used.
     * @throws \Exception
     */
    function __construct($dsn, $user = null, $password = null, $persistent = false, $options = null)
    {
        // Is the Database Driver Setup on the Server?
        if (!function_exists($this->func_connect)) {
            $error = 'Missing function [%s]. The Database Vendor Extension is not setup or enabled on this Server.';
            $error = sprintf($error, $this->func_connect);
            throw new \Exception($error);
        }

        // Use default connection options?
        if ($options === null) {
            $options = $this->connect_option;
        }

        // Connect
        if ($persistent) {
            $this->db = call_user_func($this->func_pconnect, $dsn, $user, $password, $options);
        } else {
            $this->db = call_user_func($this->func_connect, $dsn, $user, $password, $options);
        }
        $this->checkResult($this->db);
        $this->persistent = $persistent;
    }

    /**
     * Class Deconstructor. Calls [close()] automatically
     * unless using a Persistent Connection.
     */
    function __destruct()
    {
        if (!$this->persistent) {
            $this->close();
        }
    }

    /**
     * Close the connection
     */
    public function close()
    {
        if ($this->db !== null) {
            call_user_func($this->func_close, $this->db);
            $this->db = null;
        }
    }

    /**
     * Used internally to check function calls
     *
     * @param bool|\resource $result
     * @throws \Exception
     */
    private function checkResult($result)
    {
        if ($result === false) {
            $error = 'Database call failed.';
            if (is_resource($this->db)) {
                $error .= ' Error Code: ' . call_user_func($this->func_error, $this->db);
                $error .= ' Error Message: ' . call_user_func($this->func_errormsg, $this->db);
            }
            throw new \Exception($error);
        }
    }

    /**
     * Run a SQL Query (SELECT, INSERT, ...) and return a Database Resource
     *
     * @param string $sql
     * @param string $params
     * @return \resource
     * @throws \Exception
     */
    private function runQuery($sql, $params)
    {
        if ($params === null) {
            // No Parameters, run query without a prepared statement
            $resource = call_user_func($this->func_exec, $this->db, $sql);
            $this->checkResult($resource);
        } else {
            // First Prepare Statement
            $resource = call_user_func($this->func_prepare, $this->db, $sql);
            $this->checkResult($resource);

            // Run with Parameters
            $result = call_user_func($this->func_execute, $resource, $params);
            $this->checkResult($result);
        }
        return $resource;
    }

    /**
     * Run a Query and return results as any array of records. Records are each
     * associative arrays. If no records are found an empty array is returned.
     *
     * @param string $sql
     * @param array|null $params
     * @return array
     */
    public function query($sql, array $params = null)
    {
        $rows = array();
        $resource = $this->runQuery($sql, $params);
        while ($row = call_user_func($this->func_fetch_array_assoc, $resource)) {
            $rows[] = $row;
        }
        call_user_func($this->func_free_result, $resource);
        if ($this->trim_strings) {
            $this->trimArray($rows);
        }
        return $rows;
    }

    /**
     * Query for a single record and return it as a associative array
     * or return null if the record does not exist.
     *
     * @param string $sql
     * @param array|null $params
     * @return array|null
     */
    public function queryOne($sql, array $params = null)
    {
        $resource = $this->runQuery($sql, $params);
        $row = call_user_func($this->func_fetch_array_assoc, $resource);
        call_user_func($this->func_free_result, $resource);
        if ($this->trim_strings && $row !== false) {
            $this->trimArray($row);
        }
        return ($row === false ? null : $row);
    }

    /**
     * Query for a single value from the first column of the first
     * record found. If no records were found null is returned.
     *
     * @param string $sql
     * @param array|null $params
     * @return mixed
     */
    public function queryValue($sql, array $params = null)
    {
        $resource = $this->runQuery($sql, $params);
        if ($this->index_returns_array) {
            $fields = call_user_func($this->func_fetch_array_index, $resource);
        } else {
            call_user_func_array($this->func_fetch_array_index, array($resource, &$fields));
        }
        call_user_func($this->func_free_result, $resource);
        $value = (isset($fields[0]) ? $fields[0] : null);
        if ($this->trim_strings && is_string($value)) {
            $value = trim($value, ' ');
        }
        return $value;
    }

    /**
     * Query for an array of values from the first column of all records found.
     *
     * @param string $sql
     * @param array|null $params
     * @return array
     */
    public function queryList($sql, array $params = null)
    {
        $resource = $this->runQuery($sql, $params);
        $values = array();
        if ($this->index_returns_array) {
            while ($fields = call_user_func($this->func_fetch_array_index, $resource)) {
                $values[] = $fields[0];
            }
        } else {
            while (call_user_func_array($this->func_fetch_array_index, array($resource, &$fields))) {
                $values[] = $fields[0];
            }
        }
        call_user_func($this->func_free_result, $resource);
        if ($this->trim_strings) {
            $this->trimArray($values);
        }
        return $values;
    }

    /**
     * Run a SQL Action Statement (INSERT, UPDATE, DELETE, etc) and return
     * the number or rows affected. If multiple statments are passed then
     * the returned row count will likely be for only the last query.
     *
     * @param string $sql
     * @param array|null $params
     * @return int - Row count of the last query
     */
    public function execute($sql, array $params = null)
    {
        $resource = $this->runQuery($sql, $params);
        $rows_affected = call_user_func($this->func_num_rows, $resource);
        call_user_func($this->func_free_result, $resource);
        return $rows_affected;
    }

    /**
     * Prepare a SQL Statement and run many record parameters against it.
     * This can be used for transactions such as bulk record inserts.
     * Returns the total number of rows affected for all queries.
     *
     * @param string $sql
     * @param array $records
     * @return int
     */
    public function executeMany($sql, array $records)
    {
        // Prepare the Statement
        $stmt = call_user_func($this->func_prepare, $this->db, $sql);
        $this->checkResult($stmt);

        // Execute Query for each Record
        $rows_affected = 0;
        foreach ($records as $record) {
            $success = call_user_func($this->func_execute, $stmt, $record);
            $this->checkResult($success);
            $rows_affected += call_user_func($this->func_num_rows, $stmt);
        }
        call_user_func($this->func_free_result, $stmt);
        return $rows_affected;
    }
}
