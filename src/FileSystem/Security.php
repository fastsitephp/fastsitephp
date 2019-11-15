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

namespace FastSitePHP\FileSystem;

use \DOMDocument;
use FastSitePHP\Media\Image;

/**
 * File System Security
 */
class Security
{
	/**
	 * Prevent Path Traversal Attacks by verifying if a file name exists in a
	 * specified directory. Path Traversal Attacks can happen if a user is
	 * allowed to specify a file on a file system through input and uses a
	 * pattern such as '/../' to obtain files from another directory.
	 *
	 * This function returns [true] if the file exists in the directory and
	 * the file name matches exactly to the [$file] parameter. The [$dir]
	 * parameter can be a relative path with '../' characters so it should
	 * not come from a user. The [$dir] parameter is required to be a valid
	 * directory otherwise an exception is thrown as it indicates a logic
	 * or permissions error in the app.
	 *
	 * Example:
	 *     // Assume both files exist and would return [true] from built-in function [is_file()].
	 *     // False is returned for the 2nd file because a '/' character was used.
	 *     $dir = __DIR__ . '/../img';
	 *     true  = Security::dirContainsFile($dir, 'user_image.jpg')
	 *     false = Security::dirContainsFile($dir, '../../index.php')
	 *
	 * @link https://en.wikipedia.org/wiki/Directory_traversal_attack
	 * @link https://www.owasp.org/index.php/Path_Traversal
	 * @link http://php.net/manual/en/security.filesystem.php
	 * @param string $dir - Directory/Folder Path to look for the file in.
	 * @param string $file - File name to search for in a directory.
	 * @return bool
	 * @throws \Exception
	 */
	public static function dirContainsFile($dir, $file)
	{
		// A valid directory is required
		if (!is_dir($dir)) {
			throw new \Exception('Directory specified either does not exist or the web user does not have permissions to view it.');
		}

		// If the file name contains characters to possibly change
		// directory or if it is not a valid file name then return false.
		// Character ':' is for Windows File Streams:
		//   https://docs.microsoft.com/en-us/windows/desktop/fileio/file-streams
		if (strpos($file, '\\') !== false
			|| strpos($file, '/') !== false
			|| strpos($file, chr(0)) !== false // NULL Character
			|| (PHP_OS === 'WINNT' && strpos($file, ':') !== false)
		) {
			return false;
		}

		// Remove either [/] or [\] characters from the end of the directory to
		// prevent a duplicate [//] or [\\] characters and then build full path.
		$dir = rtrim($dir, '\\/');
		$full_path = implode(DIRECTORY_SEPARATOR, array($dir, $file));

		// Return true only if the directory contains the file
		return is_file($full_path);
	}

	/**
	 * Prevent Path Traversal Attacks by verifying if a file exists under the
	 * specified directory. Sub-directories can be specified, however path traversal
	 * using '../' or '..\' is not allowed for the [$path] paramater.
	 *
	 * See additional comments and links in [dirContainsFile()].
	 *
	 * Example:
	 *     // Assume both files exist and would return [true] from built-in function [is_file()].
	 *     // False is returned for the 2nd file because a '../' was used.
	 *     $dir = __DIR__ . '/../img';
	 *     true  = Security::dirContainsPath($dir, 'icons/clipboard.svg')
	 *     false = Security::dirContainsPath($dir, '../../app/app.php')
	 *
	 * @param string $dir - Directory/Folder Path to look for the file under.
	 * @param string $path - File path to search for under the root directory.
	 * @return bool
	 * @throws \Exception
	 */
	public static function dirContainsPath($dir, $path)
	{
		// A valid directory is required
		if (!is_dir($dir)) {
			throw new \Exception('Directory specified either does not exist or the web user does not have permissions to view it.');
		}

		// Validate that the path to search is only looking below the root directory
		// and does not contain relative paths that navigate up to a parent directory.
		if (strpos($path, '../') !== false
			|| strpos($path, '..\\') !== false
			|| strpos($path, chr(0)) !== false
			|| (PHP_OS === 'WINNT' && strpos($path, ':') !== false)
		) {
			return false;
		}

		// Build full path
		$dir = rtrim($dir, '\\/');
		$full_path = implode(DIRECTORY_SEPARATOR, array($dir, $path));

		// Return true only if the directory contains the file
		return is_file($full_path);
	}

	/**
	 * Prevent Path Traversal Attacks by verifying if a directory exists
	 * in a specified directory.
	 *
	 * This function returns [true] if the directory exists in the directory and
	 * the directory name matches exactly to the [$dir_name] parameter.
	 *
	 * See additional comments and links in [dirContainsFile()].
	 *
	 * Example:
	 *     // Assume both directories exist and would return [true] from built-in function [is_dir()].
	 *     // False is returned for the 2nd file because a '/' character was used.
	 *     $dir = __DIR__ . '/../img';
	 *     true  = Security::dirContainsDir($dir, 'icons')
	 *     false = Security::dirContainsDir($dir, '../../app')
	 *
	 * @param string $root_dir - Directory/Folder Path to look for the directory in.
	 * @param string $dir_name - Directory name to search for in a directory.
	 * @return bool
	 * @throws \Exception
	 */
	public static function dirContainsDir($root_dir, $dir_name)
	{
		// A valid directory is required
		if (!is_dir($root_dir)) {
			throw new \Exception('Directory specified either does not exist or the web user does not have permissions to view it.');
		}

		// Validate name
		if (strpos($dir_name, '\\') !== false
			|| strpos($dir_name, '/') !== false
			|| strpos($dir_name, chr(0)) !== false
		) {
			return false;
		}

		// Build full path
		$root_dir = rtrim($root_dir, '\\/');
		$full_path = implode(DIRECTORY_SEPARATOR, array($root_dir, $dir_name));

		// Return true only if the directory contains the sub-directory
		return is_dir($full_path);
	}

	/**
	 * Returns [true] if a image file (jpg, jpeg, gif, png, webp, svg) is valid
	 * and the file's extension matches the image type.
	 *
	 * This function can be used to verify if image files created from
	 * user input are valid. For example a malicious user may try to rename
	 * a PHP Script or executable file as an image and upload it to a site.
	 *
	 * For SVG Files this function simply verifies that the file is a valid
	 * XML file with [svg] as the root element.
	 *
	 * For other images types such as JPG or PNG this function uses the
	 * [FastSitePHP\Media\Image] class to check if a file is valid. If you
	 * intended on using the [Image] class from the same calling function
	 * then using this function is not needed as it would open the same
	 * image file twice.
	 *
	 * If your app or site needs to resize an image after a user upload then
	 * the [Image] class is recommend, however if you simply need to verify
	 * an image then this helper function allows for simple and clear code.
	 *
	 * @link https://cwe.mitre.org/data/definitions/434.html
	 * @link https://www.sans.org/reading-room/whitepapers/testing/paper/36487
	 * @link https://www.owasp.org/index.php/Unrestricted_File_Upload
	 * @param string $full_path
	 * @return bool
	 * @throws \Exception
	 */
	public static function fileIsValidImage($full_path)
	{
		// This function requires specific image formats
		$supported_extensions = array('jpg', 'jpeg', 'gif', 'png', 'webp', 'svg');
		$file_ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
		if (!in_array($file_ext, $supported_extensions, true)) {
			$error = 'Invalid file type of [%s] for function [%s::%s()]. Only files with the following extensions can be checked [%s]';
			$error = sprintf($error, $file_ext, __CLASS__, __FUNCTION__, implode(', ', $supported_extensions));
			throw new \Exception($error);
		}

		// Open image, if it is invalid then an Exception will be thrown
		try {
			if ($file_ext === 'svg') {
				if (!class_exists('DOMDocument')) {
					$error = 'Unable to validate SVG Images because PHP extension [libxml] is not installed on this server.';
					throw new \Exception($error);
				}
				$doc = new \DOMDocument();
				if (!$doc->load($full_path)) {
					return false;
				} elseif ($doc->documentElement->nodeName !== 'svg') {
					return false;
				}
			} else {
				$img = new Image($full_path);
				$img = null;
			}
		} catch (\Exception $e) {
			return false;
		}

		// Valid - Image was opened
		return true;
	}
}
