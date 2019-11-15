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

 namespace FastSitePHP\Media;

/**
 * Allow an image file to be opened, manipulated, and saved. This class
 * provides basic functionally and is not full featured with filters or
 * advanced features. It is intended to provide needed core functionally
 * when working with image file uploads and for thumbnail generation.
 *
 * For additional PHP image libraries see the following projects:
 * @link http://image.intervention.io/
 * @link https://kosinix.github.io/grafika/
 * @link https://phpimageworkshop.com/
 * @link https://murze.be/a-package-to-easily-manipulate-images-in-php
 * @link https://github.com/claviska/SimpleImage
 * @link https://github.com/spatie/image-optimizer
 * @link https://github.com/mohuishou/ImageOCR
 */
Class Image
{
	private $image = null;
	private $dir = null;
	private $full_path = null;
	private $save_quality = 90;
	private $png_compression = 6;

	/**
	 * Class Constructor - Optionally open an image
	 *
	 * @param null|string $image_path
	 */
	function __construct($image_path = null)
	{
        if ($image_path !== null) {
			$this->open($image_path);
		}
    }

	/**
	 * Class Destructor - Free memory by closing image when done.
	 */
	function __destruct()
	{
        $this->close();
    }

	/**
	 * Open a image file (jpg, png, gif, or webp). Image type is determined by
	 * the file extension. If the file contains an invalid image then an E_WARNING
	 * error will be triggered causing an ErrorException to be thrown when using
	 * the default error setup. Opening a [*.webp] image requires PHP 5.4 or greater.
	 *
	 * @param string $image_path - Full path of the image
	 * @return $this
	 * @throws \Exception
	 */
	public function open($image_path)
	{
		// Does the file exist
		if (!is_file($image_path)) {
			throw new \Exception('Image file not found. Verify that it exists and that the web user has read permissions.');
		}

		// Make sure [gd] is installed
		if (!extension_loaded('gd')) {
			$error = 'Unable to open Images because PHP extension [gd] is not installed on this server.';
			throw new \Exception($error);
		}

		// Get file extension (required to be correct for this function)
		$this->full_path = $image_path;
		$this->dir = dirname($this->full_path);
        $file_ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

		// Open image based on file extension
		// An alternative method of determining image type is to use
		// exif_imagetype() function to check the file based on file format.
	    switch ($file_ext) {
		    case 'jpg':
		    case 'jpeg':
			    $this->image = imagecreatefromjpeg($image_path);
			    break;
		    case 'gif':
			    $this->image = imagecreatefromgif($image_path);
			    break;
		    case 'png':
			    $this->image = imagecreatefrompng($image_path);
				break;
			case 'webp':
				if (function_exists('imagecreatefromwebp')) {
					$this->image = imagecreatefromwebp($image_path);
				} else {
					throw new \Exception(sprintf('Error opening image file [%s]. WEBP Images are not supported on this server or with this version of PHP.', $image_path));
				}
				break;
			default:
				throw new \Exception('Unsupported file type for opening an image: ' . $file_ext);
	    }
	    return $this;
	}

	/**
	 * Used internally when updating the image
	 *
	 * @param null|resource $new_image
	 * @return $this
	 */
	private function setNewImage($new_image)
	{
		if ($this->image !== null) {
			imagedestroy($this->image);
			$this->image = $new_image;
		}
		return $this;
	}

	/**
	 * Resize an image to a specified max width and height.
	 *
	 * When both width and height are specified the image will be
	 * sized to the smaller of the two values so it fits.
	 *
	 * If only width or only height are specified then image
	 * will be sized to that value.
	 *
	 * @param null|int $max_width
	 * @param null|int $max_height
	 * @return $this
	 * @throws \Exception
	 */
	public function resize($max_width = null, $max_height = null)
	{
	    // Get Image Size
	    $width  = imagesx($this->image);
	    $height = imagesy($this->image);

	    // Resize to fit to the max width/height, this will scale the image either up or down
	    if ($max_height !== null && $max_width !== null) {
	        // Determine percent of width and height to resize by
	        $pct_w = $max_width / $width;
	        $pct_h = $max_height / $height;
	        // Take the smaller of the two numbers
	        $pct = ($pct_w > $pct_h ? $pct_h : $pct_w);
	        // Calculate the new size
	        $new_width = $width * $pct;
	        $new_height = $height * $pct;
	    // Fit to height
	    } else if ($max_height !== null) {
	        $pct = $max_height / $height;
	        $new_width = $width * $pct;
	        $new_height = $height * $pct;
	    // Fit to width
	    } else if ($max_width !== null) {
	        $pct = $max_width / $width;
	        $new_width = $width * $pct;
	        $new_height = $height * $pct;
	    } else {
	        throw new \Exception('Variables not properly defined to resize image.');
	    }

	    $new_width = (int)$new_width;
	    $new_height = (int)$new_height;

	    // Create a new image with new size and resample the existing image to the new one
	    $new_image = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return $this->setNewImage($new_image);
	}

	/**
	 * Resize an image to a specified max width and height.
	 *
	 * This can be used with JavaScript or App cropping libraries to allow
	 * users to generate thumbnails from a full uploaded image. For example
	 * allow a user to crop an uploaded image to a profile thumbnail.
	 *
	 * Target Width and Height are optional and can be used to force images
	 * to a specific size. For example if you want to allow users to generate
	 * a 50x50 profile thumbnail on every crop then use target width and height.
	 *
	 * @param int $left - X Coordinate
	 * @param int $top -  Y Coordinate
	 * @param int $width
	 * @param int $height
	 * @param null|int $target_width
	 * @param null|int $target_height
	 * @return $this
	 */
	public function crop($left, $top, $width, $height, $target_width = null, $target_height = null)
	{
		if ($target_width === null) {
			$target_width = $width;
		}
		if ($target_height === null) {
			$target_height = $height;
		}

		$new_image = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($new_image, $this->image, 0, 0, $left, $top, $target_width, $target_height, $width, $height);
		return $this->setNewImage($new_image);
	}

	/**
	 * Rotate an image 90 degrees left (counter-clockwise).
	 * @return $this
	 */
	public function rotateLeft()
	{
		return $this->rotate(90);
	}

	/**
	 * Rotate an image 90 degrees right (clockwise).
	 * @return $this
	 */
	public function rotateRight()
	{
		return $this->rotate(-90);
	}

	/**
	 * Rotate an image by the given angle.
	 * @param float $degrees
	 * @return $this
	 */
	public function rotate($degrees)
	{
		$new_image = imagerotate($this->image, $degrees, 0);
		return $this->setNewImage($new_image);
	}

    /**
     * Specify the save quality (0 to 100) to be used
     * when saving JPG or WEBP files.
     *
     * Defaults to 90.
     *
     * @param null|int $new_value
     * @return int|$this
     */
	public function saveQuality($new_value = null)
	{
		if ($new_value === null) {
			return $this->save_quality;
		}
		$this->save_quality = $new_value;
        return $this;
	}

    /**
     * Specify the save PNG save compression-level (0 to 9).
     * Defaults to 6.
     *
     * For more on this topic refer to the links:
     * @link http://marcjschmidt.de/blog/2013/10/25/php-imagepng-performance-slow.html
     * @link https://tinypng.com/
     * @link https://compresspng.com/
     *
     * @param null|int $new_value
     * @return int|$this
     */
	public function pngCompression($new_value = null)
	{
		if ($new_value === null) {
			return $this->png_compression;
		}
		$this->png_compression = $new_value;
        return $this;
	}

    /**
     * Save the image. If no file name is specified then the name of the opened
     * file will be overwritten otherwise if only a file name is specified then
     * a new file will be saved in the same directory/folder. Additionally full paths
     * can be specified to save to a different directory/folder.
     *
     * Saving a [*.webp] image requires PHP 5.4 or greater, however using PHP 7
     * or later is recommended if saving WEBP because saving might silently
     * fail on old versions of PHP.
     *
     * @param null|string $file_name
     * @return $this
     * @throws \Exception
     */
	public function save($file_name = null)
	{
		if ($file_name === null) {
			$save_path = $this->full_path;
		} elseif (strpos($file_name, '/') === false && strpos($file_name, '\\') === false) {
			$save_path = $this->dir . '/' . $file_name;
		} else {
			$save_path = $file_name;
		}
        $file_ext = strtolower(pathinfo($save_path, PATHINFO_EXTENSION));

        // Save image based on file extension
	    switch ($file_ext) {
		    case 'jpg':
			case 'jpeg':
				$image_saved = imagejpeg($this->image, $save_path, $this->save_quality);
			    break;
		    case 'gif':
			    $image_saved = imagegif($this->image, $save_path);
			    break;
		    case 'png':
			    $image_saved = imagepng($this->image, $save_path, $this->png_compression);
				break;
			case 'webp':
				if (function_exists('imagewebp')) {
					$image_saved = imagewebp($this->image, $save_path, $this->save_quality);
				} else {
					throw new \Exception(sprintf('Error saving image file [%s]. WEBP Images are not supported on this server or with this version of PHP.', $save_path));
				}
				break;
			default:
				throw new \Exception('Unsupported file type for saving an image. The only valid file extensions to save the image are [jpg, jpeg, png, gif, webp].');
		}

		if ($image_saved === false) {
			throw new \Exception(sprintf('Error saving image file [%s]. Check file permissions or server setup.', $save_path));
		}
	    return $this;
	}

	/**
	 * Close Image to free memory
	 */
	public function close()
	{
		if ($this->image !== null && $this->image !== false) {
			imagedestroy($this->image);
			$this->image = null;
		}
	}
}