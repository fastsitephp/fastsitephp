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

namespace FastSitePHP\Data;

use \PDO;
use FastSitePHP\Data\AbstractDatabase;
use FastSitePHP\Data\DatabaseInterface;

/**
 * The Database class provides a thin wrapper for PHP Data Objects to
 * reduce the amount of code needed when querying a database.
 *
 * @link http://php.net/manual/en/book.pdo.php
 * @link http://php.net/manual/en/pdo.drivers.php
 */
class Database extends AbstractDatabase implements DatabaseInterface
{
    /**
     * PDO Object for the Database
     * @var null|PDO
     */
    public $db = null;

    /**
     * If `true` then `bindValue()` will be used for parametrized queries
     * otherwise parameter type will be dynamic and determined by PHP
     * or the database. If `false` dynamic parameters will be used and the
     * PHP MySQL driver (not other databases) will typically convert all
     * integers to strings by default.
     */
    public $use_bind_value = true;

    /**
     * Class constructor. Creates Db Connection using PDO.
     *
     * @link http://php.net/manual/en/pdo.construct.php
     * @link http://php.net/manual/en/features.persistent-connections.php
     * @param string $dsn - Database Connection String (Data Source Name)
     * @param null|string $user - User Name for the Connection
     * @param null|string $password - Password for the User
     * @param bool $persistent - If [true] then PHP will keep a persistent connection to the database after the script finishes.
     * @param array $options - Init options for the database
     */
    public function __construct($dsn, $user = null, $password = null, $persistent = false, array $options = array())
    {
        $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        if ($persistent) {
            $options[\PDO::ATTR_PERSISTENT] = true;
        }
        $this->db = new \PDO($dsn, $user, $password, $options);
    }

    /**
    * Run a SQL Query (SELECT, INSERT, ...) and return a PDOStatement object
    *
    * @param string $sql
    * @param array|null $params
    * @return \PDOStatement
    * @throws \Exception
    */
    private function runQuery($sql, $params)
    {
        if ($params === null) {
            $stmt = $this->db->query($sql);
        } else {
            $stmt = $this->db->prepare($sql);
            if ($this->use_bind_value) {
                foreach ($params as $key => $value) {
                    $index = (is_string($key) ? $key : $key + 1); // Keys must start at 1 and not 0
                    $type = $this->getBindType($value);
                    $stmt->bindValue($index, $value, $type);
                }
                $stmt->execute();
            } else {
                $stmt->execute($params);
            }
        }
        return $stmt;
    }

    /**
     * Return a PDO Constant for use with `PDOStatement->bindValue()` based on
     * the value type. This is used internally for parametrized queries by default
     * unless `$this->use_bind_value = false`.
     *
     * @param mixed $value
     * @return int
     */
    public function getBindType($value) {
        switch (gettype($value)) {
            case 'NULL':
                return PDO::PARAM_NULL;
            case 'boolean':
                return PDO::PARAM_BOOL;
            case 'integer':
                return PDO::PARAM_INT;
            case 'resource':
                return PDO::PARAM_LOB;
            default:
                return PDO::PARAM_STR;
        }
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
        $stmt = $this->runQuery($sql, $params);
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($this->trim_strings) {
            $this->trimArray($records);
        }
        return $records;
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
        $stmt = $this->runQuery($sql, $params);
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($this->trim_strings && $record !== false) {
            $this->trimArray($record);
        }
        return ($record === false ? null : $record);
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
        $stmt = $this->runQuery($sql, $params);
        $fields = $stmt->fetch(\PDO::FETCH_NUM);
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
        $stmt = $this->runQuery($sql, $params);
        $rowset = $stmt->fetchAll(\PDO::FETCH_NUM);
        $values = array();
        foreach ($rowset as $row) {
            $values[] = $row[0];
        }
        if ($this->trim_strings) {
            $this->trimArray($values);
        }
        return $values;
    }

    /**
     * Query for and return multiple Row Sets from a single query.
     * This feature works in most databases but is not available for SQLite.
     *
     * @param string $sql
     * @param array|null $params
     * @return array
     */
    public function querySets($sql, array $params = null)
    {
        // Query and add all returned row sets to an array
        $stmt = $this->runQuery($sql, $params);
        $rowsets = array();
        do {
            $rowsets[] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } while ($stmt->nextRowset() && $stmt->columnCount());

        // Trim strings on each record in each rowset if the option is set.
        // The "&" is used to modify each rowset by reference rather than
        // by copy it by value. See additional comments in the class
        // [AbstractDatabase] for more on PHP references.
        if ($this->trim_strings) {
            foreach ($rowsets as &$rowset) {
                $this->trimArray($rowset);
            }
            unset($rowset); // Destroy the last Reference
        }
        return $rowsets;
    }

    /**
     * Run a SQL Action Statement (INSERT, UPDATE, DELETE, etc) and return
     * the number or rows affected. If multiple statements are passed then
     * the returned row count will likely be for only the last query.
     *
     * @param string $sql
     * @param array|null $params
     * @return int - Row count of the last query
     */
    public function execute($sql, array $params = null)
    {
        $stmt = $this->runQuery($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Returns the ID of the last inserted row or sequence value. This calls
     * the PDO function [lastInsertId()]. Additionally if using SQL the last
     * ID can be obtained from the following queries:
     *
     *     MySQL:      SELECT LAST_INSERT_ID()
     *     SQLite:     SELECT last_insert_rowid()
     *     SQL Server: SELECT SCOPE_IDENTITY()
     *                 SELECT @@IDENTITY
     *     IBM:        SELECT IDENTITY_VAL_LOCAL() FROM SYSIBM.SYSDUMMY1
     *
     * Oracle and PostgreSQL uses Sequence Objects of Auto-Numbers.
     *
     * Example if using SQL with [queryValue()]:
     *     $id = $db->queryValue('SELECT SCOPE_IDENTITY()');
     *
     * @link http://php.net/manual/en/pdo.lastinsertid.php
     * @param string|null $name - Optional name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->db->lastInsertId($name);
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
        $rows_affected = 0;
        $stmt = $this->db->prepare($sql);
        foreach ($records as $record) {
            $stmt->execute($record);
            $rows_affected += $stmt->rowCount();
        }
        return $rows_affected;
    }
}
