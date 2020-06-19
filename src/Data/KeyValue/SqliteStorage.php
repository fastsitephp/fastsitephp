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

namespace FastSitePHP\Data\KeyValue;

use FastSitePHP\Data\Database;

/**
 * Store Key-Value Pairs in a SQLite Database.
 *
 * This class uses the [FastSitePHP\Data\KeyValue\StorageInterface] class interface.
 */
class SqliteStorage implements StorageInterface
{
    private $db = null;

    /**
     * Class Constructor
     *
     * Specify a path for the SQLite Database. The database file will be created
     * if it does not exist and the table [key_value_pairs] will be added.
     *
     * @param string $file_path
     */
    public function __construct($file_path)
    {
        $dsn = 'sqlite:' . $file_path;
        $this->db = new \FastSitePHP\Data\Database($dsn);
        $sql = 'CREATE TABLE IF NOT EXISTS key_value_pairs (key TEXT PRIMARY KEY, value TEXT)';
        $this->db->execute($sql);
    }

    /**
     * Get a saved value with an optional default value if it doesn't exist or is null.
     *
     * @param string $key
     * @param mixed $default_value
     * @return string
     */
    public function get($key, $default_value = null)
    {
        $sql = 'SELECT value FROM key_value_pairs WHERE key = ?';
        $value = $this->db->queryValue($sql, [$key]);
        return ($value === null ? $default_value : $value);
    }

    /**
     * Set a value for a named key.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->exists($key)) {
            $sql = 'UPDATE key_value_pairs SET value = ? WHERE key = ?';
            $this->db->execute($sql, [$value, $key]);
        } else {
            $sql = 'INSERT INTO key_value_pairs (key, value) VALUES (?, ?)';
            $this->db->execute($sql, [$key, $value]);
        }
    }

    /**
     * Check if a key exists in the database.
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        $sql = 'SELECT COUNT(*) FROM key_value_pairs WHERE key = ?';
        $count = $this->db->queryValue($sql, [$key]);
        return ((int)$count === 1);
    }

    /**
     * Remove (delete) a key exists from the database.
     *
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $sql = 'DELETE FROM key_value_pairs WHERE key = ?';
        $this->db->execute($sql, [$key]);
    }
}
