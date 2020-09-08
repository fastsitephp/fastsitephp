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

namespace FastSitePHP\FileSystem;

/**
 * File System Search
 *
 * This Class has functions for searching the local file system for files and
 * directories. Additionally URL Lists can be built from a list of files.
 *
 * This class works by setting the root search directory/folder [dir()],
 * setting various search options, and then calling one of
 * [files(), dirs(), all(), or urlFiles($url_root)] functions.
 */
class Search
{
    private $dir = null;
    private $recursive_search = false;
    private $include_root = true;
    private $full_path = false;
    private $file_types = null;
    private $include_names = null;
    private $include_regex_names = null;
    private $include_regex_paths = null;
    private $exclude_names = null;
    private $exclude_regex_names = null;
    private $exclude_regex_paths = null;
    private $include_text = null;
    private $case_insensitive_text = true;
    private $hide_extensions = false;

    /**
     * Get or set the root directory for searching.
     *
     * @param null|string $new_value
     * @return null|string|$this
     */
    public function dir($new_value = null)
    {
        if ($new_value === null) {
            return $this->dir;
        }
        $this->dir = $new_value;
        return $this;
    }

    /**
     * Reset all options other than the root search directory.
     *
     * @return $this
     */
    public function reset()
    {
        $this->recursive_search = false;
        $this->include_root = true;
        $this->full_path = false;
        $this->file_types = null;
        $this->include_names = null;
        $this->include_regex_names = null;
        $this->include_regex_paths = null;
        $this->exclude_names = null;
        $this->exclude_regex_names = null;
        $this->exclude_regex_paths = null;
        $this->include_text = null;
        $this->case_insensitive_text = true;
        return $this;
    }

    /**
     * If true then sub-directories/folders will be searched when either
     * [dirs() or files()] are called and the full path will be returned.
     *
     * Defaults to false.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function recursive($new_value = null)
    {
        if ($new_value === null) {
            return $this->recursive_search;
        }
        $this->recursive_search = (bool)$new_value;
        return $this;
    }

    /**
     * Applies only when using [recursive(true)]. If set to false then the
     * root search directory will be excluded from the returned file/dir list.
     *
     * Defaults to true.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function includeRoot($new_value = null)
    {
        if ($new_value === null) {
            return $this->include_root;
        }
        $this->include_root = (bool)$new_value;
        return $this;
    }

    /**
     * If true then then the full file paths will be returned when
     * [dirs() or files()] are called. Defaults to false, however when
     * [recursive(true)] is used then the value will always be true.
     *
     * @param null|bool $full_path
     * @return bool|$this
     */
    public function fullPath($full_path = null)
    {
        if ($full_path === null) {
            return $this->full_path;
        }
        $this->full_path = $full_path;
        return $this;
    }

    /**
     * Specify an array of files types to filter on when calling
     * [files() or urlFiles()].
     *
     * Example:
     *     $search->fileTypes(['png', 'jpg', 'svg'])
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function fileTypes(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->file_types;
        }
        $this->file_types = $new_value;
        return $this;
    }

    /**
     * Specify an array of files/dir names to include on when calling
     * [dirs(), files(), or urlFiles()]. If a file/dir matches any names
     * in the list then it will be included in the result.
     *
     * Example:
     *     $search->includeNames(['index.php', 'app.php'])
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function includeNames(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->include_names;
        }
        $this->include_names = $new_value;
        return $this;
    }

    /**
     * Specify an array of regex patterns to include on when calling
     * [dirs(), files(), or urlFiles()]. If a file/dir name matches
     * any regex in the list then it will be included in the result.
     *
     * Example:
     *     $search->includeRegExNames(['/^app/', '/.htm$/'])
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function includeRegExNames(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->include_regex_names;
        }
        $this->include_regex_names = $new_value;
        return $this;
    }

    /**
     * Specify an array of regex patterns to include on when calling
     * [dirs(), files(), or urlFiles()]. If part of the full path matches
     * any regex in the list then it will be included in the result.
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function includeRegExPaths(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->include_regex_paths;
        }
        $this->include_regex_paths = $new_value;
        return $this;
    }

    /**
     * Specify an array of files/dir names to exclude on when calling
     * [dirs(), files(), or urlFiles()]. If a file/dir matches any names
     * in the list then it will be excluded from the result.
     *
     * Example:
     *     $search->excludeNames(['.DS_Store', 'desktop.ini'])
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function excludeNames(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->exclude_names;
        }
        $this->exclude_names = $new_value;
        return $this;
    }

    /**
     * Specify an array of regex patterns to exclude on when calling
     * [dirs(), files(), or urlFiles()]. If a file/dir name matches
     * any regex in the list then it will be excluded from the result.
     *
     * Example:
     *     $search->excludeRegExName(['/^[.]/', '/^testing-/'])
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function excludeRegExNames(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->exclude_regex_names;
        }
        $this->exclude_regex_names = $new_value;
        return $this;
    }

    /**
     * Specify an array of regex patterns to exclude on when calling
     * [dirs(), files(), or urlFiles()]. If part of the full path matches
     * any regex in the list then it will be excluded from the result.
     *
     * @param null|array $new_value
     * @return null|array|$this
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
     * Specify an array of search text that matching files must
     * contain to be included in the result. If running from a web page
     * or web service then this option should only be used against known
     * files because it does not exclude large files from be opened.
     *
     * Example:
     *     $search->fileTypes(['php'])->includeText(['X-API-Key'])
     *
     * By default text searches are case-insensitive which is controlled
     * by the [caseInsensitiveText()] function.
     *
     * @param null|array $new_value
     * @return null|array|$this
     */
    public function includeText(array $new_value = null)
    {
        if ($new_value === null) {
            return $this->include_text;
        }
        $this->include_text = $new_value;
        return $this;
    }

    /**
     * Specify if content searches defined from [includeText()] should
     * be case-insensitive or not.
     *
     * Defaults to [true] which means that ('ABC' === 'abc').
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function caseInsensitiveText($new_value = null)
    {
        if ($new_value === null) {
            return $this->case_insensitive_text;
        }
        $this->case_insensitive_text = (bool)$new_value;
        return $this;
    }

    /**
     * If set to [true] then file extensions will be hidden
     * on the result. This only applies to [files()] and requires
     * [fullPath()] to be false.
     *
     * @param null|bool $new_value
     * @return bool|$this
     */
    public function hideExtensions($new_value = null)
    {
        if ($new_value === null) {
            return $this->hide_extensions;
        }
        $this->hide_extensions = (bool)$new_value;
        return $this;
    }

    /**
     * Returns an array of file names in a directory
     * matching the specified criteria.
     *
     * @return array
     * @throws \Exception
     */
    public function files()
    {
        return $this->getMatchingFilesAndDirs(false);
    }

    /**
     * Returns an array of directory names in a directory matching the
     * specified criteria and excluding the dot directories '.' and '..'.
     *
     * @return array
     * @throws \Exception
     */
    public function dirs()
    {
        return $this->getMatchingFilesAndDirs(true);
    }

    /**
     * Returns an array of all directory names and an array of all files names
     * from the root directory [dir(path)] excluding the dot directories '.' and '..'.
     *
     * This function does not use any search criteria so if searching for files
     * and directories use [files()] or [dirs()] instead.
     *
     * @return array - list($dirs, $files)
     * @throws \Exception
     */
    public function all()
    {
        // Validation
        $this->checkRootDir();

        // Get an array of files and folders excuding dot directories '.' and '..'
        $fs_items = array_diff(scandir($this->dir), array('.', '..'));
        $files = array();
        $dirs = array();

        // Add a forward slash if not included at the end.
        // This works for Windows even though Windows typically used backslashes.
        $dir_path = $this->dir;
        if (substr($dir_path, -1) !== '/') {
            $dir_path .= '/';
        }

        // Seperate all items from directory into a seperate array of dirs and files
        foreach ($fs_items as $name) {
            if (is_file($dir_path . $name)) {
                $files[] = $name;
            } else {
                $dirs[] = $name;
            }
        }
        return array($dirs, $files);
    }

    /**
     * Returns an array of url names for files in
     * directory matching the specified criteria.
     *
     * Currently this option doesn't work with recursive
     * directories [option: recursive(true)].
     *
     * @param string $url_root
     * @return array
     * @throws \Exception
     */
    public function urlFiles($url_root)
    {
        $this->full_path = false;
        $this->recursive_search = false;

        $files = $this->getMatchingFilesAndDirs(false);

        if (substr($url_root, -1) !== '/') {
            $url_root .= '/';
        }

        $urls = array();
        foreach ($files as $file_name) {
            $urls[] = $url_root . rawurlencode($file_name);
        }
        return $urls;
    }

    /**
     * Private function that searches sub-folders/dirs (Recursive Option)
     *
     * @param bool $get_dir
     * @param array $list
     * @param string $cur_dir
     * @return array
     */
    private function getRecursiveFiles($get_dir, $list, $cur_dir)
    {
        $files = array_diff(scandir($cur_dir), array('.', '..'));
        $dirs = array();
        foreach ($files as $file_name) {
            $file_path = realpath($cur_dir . '/' . $file_name);
            $is_dir = is_dir($file_path);
            if ($get_dir === $is_dir) {
                $list[] = $file_path;
            }
            if ($is_dir) {
                $dirs[] = $file_path;
            }
        }
        foreach ($dirs as $dir) {
            $list = $this->getRecursiveFiles($get_dir, $list, $dir);
        }
        return $list;
    }

    /**
     * Make sure that [dir(path)] is set when calling [files(), dirs(), all(), or urlFiles()]
     *
     * @return void
     * @throws \Exception
     */
    private function checkRootDir()
    {
        if ($this->dir === null) {
            throw new \Exception(sprintf('When searching for files or directories the root directory must first be set from [%s->dir()].', __CLASS__));
        } elseif (!is_dir($this->dir)) {
            throw new \Exception(sprintf('Directory [%s] does not exist or the current user does not have permissions to view it.', $this->dir));
        }
    }

    /**
     * Private function used to search for both files and directories
     *
     * @param bool $get_dir
     * @return array
     * @throws \Exception
     */
    private function getMatchingFilesAndDirs($get_dir)
    {
        // Validation
        $this->checkRootDir();

        // Get an array of files and folders excuding dot directories '.' and '..'
        if ($this->recursive_search) {
            $this->full_path = true;
            $files = $this->getRecursiveFiles($get_dir, array(), $this->dir);
        } else {
            $files = array_diff(scandir($this->dir), array('.', '..'));
        }

        // Add a forward slash if not included at the end.
        // This works for Windows even though Windows typically used backslashes.
        $dir_path = $this->dir;
        if (substr($dir_path, -1) !== '/') {
            $dir_path .= '/';
        }

        // Enumerate all items and return matching
        $file_list = array();
        foreach ($files as $file_name) {
            // Get file name and path
            if ($this->recursive_search) {
                // Full Path
                $file_path = $file_name;
                // File Name
                $data = explode(DIRECTORY_SEPARATOR, $file_path);
                $file_name = $data[count($data)-1];
            } else {
                $file_path = realpath($dir_path . $file_name);
            }

            // Check type (file/dir)
            if ($get_dir !== is_dir($file_path)) {
                continue;
            }

            // Compare on file type if specified
            if ($get_dir === false && $this->file_types !== null) {
                // NOTE - pathinfo() works on extensions however when reading
                // file names multibyte characters have to be handled so be
                // carefull if using it in other code.
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($ext, $this->file_types, true)) {
                    continue;
                }
            }

            // Compare file name against a list of file names
            // and regular expressions to be included
            if ($this->include_names !== null) {
                if (!in_array($file_name, $this->include_names, true)) {
                    continue;
                }
            }
            $props = array(
                'include_regex_names' => $file_name,
                'include_regex_paths' => $file_path,
            );
            $keep = true;
            foreach ($props as $prop => $file) {
                if ($this->{$prop} !== null) {
                    $keep = false;
                    foreach ($this->{$prop} as $regex) {
                        if (preg_match($regex, $file) === 1) {
                            $keep = true;
                            continue;
                        }
                    }
                    if (!$keep) {
                        break;
                    }
                }
            }
            if (!$keep) {
                continue;
            }

            // Compare file name against a list of file names
            // and regular expressions to be excluded
            if ($this->exclude_names !== null) {
                if (in_array($file_name, $this->exclude_names, true)) {
                    continue;
                }
            }
            $props = array(
                'exclude_regex_names' => $file_name,
                'exclude_regex_paths' => $file_path,
            );
            $skip = false;
            foreach ($props as $prop => $file) {
                if ($this->{$prop} !== null) {
                    $skip = false;
                    foreach ($this->{$prop} as $regex) {
                        if (preg_match($regex, $file) === 1) {
                            $skip = true;
                            continue;
                        }
                    }
                    if ($skip) {
                        break;
                    }
                }
            }
            if ($skip) {
                continue;
            }

            // Search file contents. Currently full files are read into memory
            // so it's up to a calling application to handle this correctly
            // and not run this on large files. In the future additional options
            // can be added to prevent searching of large files.
            if ($get_dir === false && $this->include_text !== null) {
                $skip = false;
                $contents = file_get_contents($file_path);
                foreach ($this->include_text as $text) {
                    if ($this->case_insensitive_text) {
                        $matches = (stripos($contents, $text));
                    } else {
                        $matches = (strpos($contents, $text));
                    }
                    if ($matches === false) {
                        $skip = true;
                        continue;
                    }
                }
                if ($skip) {
                    continue;
                }
            }

            // Optionally hide file extensions on [files()]
            if (!$get_dir && $this->hide_extensions && !$this->full_path) {
                $data = explode('.', $file_name);
                if (count($data) > 1) {
                    $ext = $data[count($data)-1];
                    $file_name = substr($file_name, 0, strlen($file_name) - strlen($ext) - 1);
                }
            }

            // Matches criteria
            $file_list[] = ($this->full_path ? $file_path : $file_name);
        }

        // Remove root search directory if [recursive(true)] and [includeRoot(false)]
        if ($this->recursive_search && !$this->include_root) {
            $root_dir = strlen(realpath($this->dir)) + 1;
            $file_list = array_map(function ($file) use ($root_dir) {
                return substr($file, $root_dir);
            }, $file_list);
        }
        return $file_list;
    }
}
