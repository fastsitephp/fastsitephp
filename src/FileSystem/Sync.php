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

namespace FastSitePHP\FileSystem;

use FastSitePHP\FileSystem\Search;

/**
 * File System Sync
 *
 * This class provides the ability for syncing of all files and directories
 * from one directory to another directory. This class compares files using a hash
 * (defaults to 'sha256') and updates the files if different. Additionally new files,
 * deleted files, new empty directories, and deleted directories are handled.
 */
class Sync
{
    private $summary_title = 'File System Sync Results';
    private $hash_algo = 'sha256';
    private $dir_from = null;
    private $dir_to = null;
    private $exclude_names = null;
    private $exclude_regex_paths = null;
    private $dry_run = false;
    private $files_added = array();
    private $files_updated = array();
    private $files_deleted = array();
    private $dirs_added = array();
    private $dirs_deleted = array();

    /**
     * Get or set the directory to sync from (source directory).
     *
     * @param null|string $new_value
     * @return string|$this
     */
    function dirFrom($new_value = null)
    {
        if ($new_value === null) {
            return $this->dir_from;
        }
        $this->dir_from = (string)$new_value;
        return $this;
    }

    /**
     * Get or set the directory to sync to (destination directory).
     *
     * @param null|string $new_value
     * @return string|$this
     */
    function dirTo($new_value = null)
    {
        if ($new_value === null) {
            return $this->dir_to;
        }
        $this->dir_to = (string)$new_value;
        return $this;
    }

    /**
     * Get or set an array of files/dir names to exclude. If a file/dir matches
     * any names in the list then it will be excluded from the result. This
     * property does not handle files in nested directories. For nested files
     * use [excludeRegExPaths()].
     *
     * @param null|array $new_value
     * @return array|$this
     */
    function excludeNames(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->exclude_names;
        }
        $this->exclude_names = $new_value;
        return $this;
    }

    /**
     * Get or set an array of files/dir regex path expressions to exclude.
     * If part of the full path matches any regex in the list then it will
     * be excluded from the result.
     *
     * Example usage:
     *     $sync->excludeRegExPaths(['/node_modules/']);
     *
     * @param null|array $new_value
     * @return array|$this
     */
    public function excludeRegExPaths(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->exclude_regex_paths;
        }
        $this->exclude_regex_paths = $new_value;
        return $this;
    }

    /**
     * Get or set the summary title used for report output when calling [printResults()].
     * Defaults to 'File System Sync Results'.
     *
     * @param null|string $new_value
     * @return string|$this
     */
    function summaryTitle($new_value = null)
    {
        if ($new_value === null) {
            return $this->summary_title;
        }
        $this->summary_title = (string)$new_value;
        return $this;
    }

    /**
     * Get or set a dry run boolean value for testing. When set to [true]
     * no changes will be made when calling [sync()]. Defaults to [false].
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    function dryRun($new_value = null)
    {
        if ($new_value === null) {
            return $this->dry_run;
        }
        $this->dry_run = (bool)$new_value;
        return $this;
    }

    /**
     * Get or set the hashing algorithm for comparing files when syncing.
     * Defaults to 'sha256'.
     *
     * @param null|string $new_value
     * @return string|$this
     */
    function hashAlgo($new_value = null)
    {
        if ($new_value === null) {
            return $this->hash_algo;
        }
        if (!in_array($new_value, hash_algos())) {
            throw new \Exception('Invalid hashing algorithm - Allowed algorithms: ' . implode(', ', hash_algos()));
        }
        $this->hash_algo = $new_value;
        return $this;
    }

    /**
     * Sync files and directories (folders) from [dirFrom(path)] to [dirTo(path)].
     * The sync is recursive so all files and directories are synced in all sub-directories.
     *
     * To view the results of the sync call [printResults()] after calling this function.
     *
     * @return $this
     */
    function sync()
    {
        // Validate
        if (!is_dir($this->dir_from)) {
            throw new \Exception('Directory for dirFrom(dir) is not set or this script does not have permissions to view it.');
        }
        if (!is_dir($this->dir_to)) {
            throw new \Exception('Directory for dirTo(dir) is not set or this script does not have permissions to view it.');
        }
        $dir_from = realpath($this->dir_from) . DIRECTORY_SEPARATOR;
        $dir_to = realpath($this->dir_to) . DIRECTORY_SEPARATOR;

        // Setup file search
        $search = new Search();
        $search
            ->recursive(true)
            ->includeRoot(false);

        if ($this->exclude_names) {
            $search->excludeNames($this->exclude_names);
        }
        if ($this->exclude_regex_paths) {
            $search->excludeRegExPaths($this->exclude_regex_paths);
        }

        // Get files
        $files_from = $search->dir($dir_from)->files();
        $files_to = $search->dir($dir_to)->files();

        // Convert array values to keys so isset() can be used for faster compare
        $keys_from = array_flip($files_from);
        $keys_to = array_flip($files_to);

        // Compare for new files and updated files
        $algo = $this->hash_algo;
        foreach ($files_from as $file) {
            $file_from = $dir_from . $file;
            $file_to = $dir_to . $file;
            if (isset($keys_to[$file])) {
                if (hash_file($algo, $file_from) !== hash_file($algo, $file_to)) {
                    if (!$this->dry_run) {
                        $success = copy($file_from, $file_to);
                        if ($success === false) {
                            throw new \Exception('Error, unable to update file: ' . $file_to);
                        }
                    }
                    $this->files_updated[] = $file_to;
                }
            } else {
                if (!$this->dry_run) {
                    $success = copy($file_from, $file_to);
                    if ($success === false) {
                        throw new \Exception('Error, unable to add file: ' . $file_to);
                    }
                }
                $this->files_added[] = $file_to;
            }
        }

        // Compare for files to delete
        foreach ($files_to as $file) {
            if (!isset($keys_from[$file])) {
                $file_to = $dir_to . $file;
                if (!$this->dry_run) {
                    $success = unlink($file_to);
                    if ($success === false) {
                        throw new \Exception('Error, unable to delete file: ' . $file_to);
                    }
                }
                $this->files_deleted[] = $file_to;
            }
        }

        // Handle Empty Directories after all files are synced
        $dirs_from = $search->dir($dir_from)->dirs();
        $dirs_to = $search->dir($dir_to)->dirs();
        $keys_from = array_flip($dirs_from);
        $keys_to = array_flip($dirs_to);

        // New Directories
        foreach ($dirs_from as $dir) {
            if (!isset($keys_to[$dir])) {
                $new_dir = $dir_to . $dir;
                if (!$this->dry_run) {
                    $success = mkdir($new_dir);
                    if ($success === false) {
                        throw new \Exception('Error, unable to create directory: ' . $new_dir);
                    }
                }
                $this->dirs_added[] = $new_dir;
            }
        }

        // Delete Diretories if not in the from list
        foreach ($dirs_to as $dir) {
            if (!isset($keys_from[$dir])) {
                $del_dir = $dir_to . $dir;
                if (!$this->dry_run) {
                    $success = rmdir($del_dir);
                    if ($success === false) {
                        throw new \Exception('Error, unable to delete directory: ' . $del_dir);
                    }
                }
                $this->dirs_deleted[] = $del_dir;
            }
        }

        // Return object instance so [printResults()] as a chainable function
        return $this;
    }

    /**
     * Output the result of [sync()] as a text summary. This includes a list of all
     * affected files and directories and summary counts. This function will typically
     * be used for CLI output, however if used on a web server then <br> will be used
     * for line breaks.
     *
     * @return void
     */
    function printResults()
    {
        // Determine lines breaks based on how PHP is being used (Web or CLI)
        $is_cli = (php_sapi_name() === 'cli');
        $line_break = ($is_cli ? PHP_EOL : '<br>');

        // Changes
        $count_updated = count($this->files_updated);
        $count_added = count($this->files_added);
        $count_deleted = count($this->files_deleted);
        $count_dir_added = count($this->dirs_added);
        $count_dir_deleted = count($this->dirs_deleted);
        $count_total = $count_updated + $count_added + $count_deleted + $count_dir_added + $count_dir_deleted;

        // Print Summary
        echo $this->summary_title . $line_break;
        if ($count_total === 0) {
            echo 'No changes made' . $line_break;
        } else {
            foreach ($this->files_added as $file) {
                echo 'Added: ' . $file . $line_break;
            }
            foreach ($this->files_deleted as $file) {
                echo 'Deleted: ' . $file . $line_break;
            }
            foreach ($this->files_updated as $file) {
                echo 'Updated: ' . $file . $line_break;
            }
            foreach ($this->dirs_added as $dir) {
                echo 'Added Directory: ' . $dir . $line_break;
            }
            foreach ($this->dirs_deleted as $dir) {
                echo 'Deleted Directory: ' . $dir . $line_break;
            }
            if ($count_added) {
                echo 'Files Added: ' . $count_added . $line_break;
            }
            if ($count_updated) {
                echo 'Files Updated: ' . $count_updated . $line_break;
            }
            if ($count_deleted) {
                echo 'Files Deleted: ' . $count_deleted . $line_break;
            }
            if ($count_dir_added) {
                echo 'Directories Added: ' . $count_dir_added . $line_break;
            }
            if ($count_dir_deleted) {
                echo 'Directories Deleted: ' . $count_dir_deleted . $line_break;
            }
            echo 'Total Changes: ' . $count_total . $line_break;
            if ($this->dry_run) {
                echo 'Dry Run only (No changes made)' . $line_break;
            }
        }
    }
}
