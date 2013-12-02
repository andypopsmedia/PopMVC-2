<?php
/**
 * Copyright (c) 2013 Andypops Media Limited
 *
 * @author Andy Mills
 *
 * This code is created and distributed under the GNU
 * General Public License (GPL)
 *
 */

// Create some definitions
define('GREYSCALE_AVERAGE', 2);
define('GREYSCALE_LUMINOSITY', 4);

class PopImage
{
	// Name of the Image File
	var $imagefile;
	var $imageext;
	
	// Image Object
	var $image;
	
	// Image Attributes
	var $im_w, $im_h, $im_t, $im_a;

	/**
	 * Load the image file
	 *
	 * @param string $filename					Name and path of the Image File to edit.
	 *											Must be relative to the current working directory.
	 *
	 * @return bool
	 */
	public function load($filename)
	{
		// Get the File Details
		$imagepath = pathinfo($filename);
		$this->imageext = strtolower($imagepath['extension']);
		
		// Allowed Image Extensions
		$allowed_exts = array( 'jpg', 'jpeg', 'png', 'gif' );
		
		// Check if the Image is an allowed Image Type (JPEG, GIF or PNG)
		if (!in_array($this->imageext, $allowed_exts)) {
			// Throw an Exception. Hurl it with all your might!
			report_error('Invalid File Type', "The file <strong>$filename</strong> is not a valid image file.");
			
			// Exit the Function
			return FALSE;
		}
		
		// Make the Full Path
		$this->imagefile = getcwd().'/'.$filename;
		
		// Get the Image Attributes
		list($this->im_w, $this->im_h, $this->im_t, $this->im_a) = getimagesize($this->imagefile);
		
		// Create the Image Object
		switch($this->imageext)
		{
			case 'jpg':
			case 'jpeg':
				$this->image = imagecreatefromjpeg($this->imagefile);
				break;
			case 'gif':
				$this->image = imagecreatefrompng($this->imagefile);
				break;
			case 'png':
				$this->image = imagecreatefrompng($this->imagefile);
				break;
		}
		
		// If the Image wasn't created, return FALSE
		if (!$this->image) return FALSE;
		
		// Save Transparency
		imagealphablending($this->image, false);
		imagesavealpha($this->image, true);
		
		// Otherwise, just return TRUE
		return TRUE;
	}
	
	/**
	 * Gets the width of the image, in pixels.
	 *
	 * @return int
	 */
	public function get_width()
	{
		return $this->im_w;
	}
	
	/**
	 * Gets the height of the image, in pixels.
	 *
	 @return int
	 */
	public function get_height()
	{
		return $this->im_h;
	}
	
	/**
	 * Create a Thumbnail Image of a specified size.
	 *
	 * @param int $width						The width of the thumbnail, in pixels.
	 * @param int $height						The height of the thumbnail, in pixels.
	 */
	public function create_thumbnail($width, $height)
	{
		// Check that the Image Exists
		if (is_null($this->image)) return FALSE;
		
		// Landscape Image
		if ($this->im_w > $this->im_h)
		{
			// Calculate the Offsets
			$new_h = $this->im_h;
			$new_w = ($this->im_h / $height) * $width;
			$off_x = ($this->im_w / 2) - ($new_w / 2);
			$off_y = 0;
		}
		// Portrait Image
		elseif ($this->im_h > $this->im_w)
		{
			// Calculate the Offsets
			$new_w = $this->im_w;
			$new_h = ($this->im_w / $width) * $height;
			$off_x = 0;
			$off_y = ($this->im_h / 2) - ($new_h / 2);
		}
		// Square Image
		else
		{
			// Calculate the Offsets
			$new_w = $this->im_w;
			$new_h = $this->im_h;
			$off_x = 0;
			$off_y = 0;
		}
		
		// Round the Values
		$new_w = round($new_w); $new_h = round($new_h);
		$off_x = round($off_x); $off_y = round($off_y);
		
		// Create the new Image Object
		$thumbnail = imagecreatetruecolor($width, $height);
		
		// Save Transparency
		imagealphablending($thumbnail, false);
		imagesavealpha($thumbnail, true);
		
		// Copy and Resize the Thumbnail
		imagecopyresampled($thumbnail, $this->image, 0, 0, $off_x, $off_y, $width, $height, $new_w, $new_h);
		
		// Set the new Size
		$this->im_w = $width;
		$this->im_h = $height;
		
		
		// Reassign the Instance Image
		imagedestroy($this->image);
		$this->image = $thumbnail;
	}
	
	/**
	 * Constrain an image to a specified size.
	 *
	 * @param int $width						The width to constrain the image to, in pixels.
	 * @param int $height						The height to constrain the image to, in pixels.
	 * @param bool $allow_scale_up				All the image to be scaled up if smaller than the
	 *											constrained size.
	 */
	public function constrain($width, $height, $allow_scale_up = FALSE)
	{
		// Check that the Image Exists
		if (is_null($this->image)) return FALSE;
		
		// Get the Ratios
		$orig_ratio = $this->im_h / $this->im_w;
		$cons_ratio = $height / $width;
		
		// If the Original Ratio is Less than the Constrained Ratio...
		if ($orig_ratio < $cons_ratio)
		{
			// Width is the same as Constrain Size
			$new_w = $width;
			
			// ... unless we're disabling scale up
			if (!$allow_scale_up) $new_w = min($this->im_w, $width);
			
			// Calculate the new Height
			$new_h = ($new_w / $this->im_w) * $this->im_h;
		}
		elseif ($cons_ratio < $orig_ratio)
		{
			// Height is the same as Constrain Size
			$new_h = $height;
			
			// ... unless we're disabling scale up
			if (!$allow_scale_up) $new_h = min($this->im_h, $height);
			
			// Calculate the new Width
			$new_w = ($new_h / $this->im_h) * $this->im_w;
		}
		else
		{
			// Set to the Constrain Size if Ratio is the Same
			$new_h = $height;
			$new_w = $width;
		}
		
		// Round the Values
		$new_w = round($new_w); $new_h = round($new_h);
		
		// Create the new Image Object
		$constrained = imagecreatetruecolor($new_w, $new_h);
		
		// Save Transparency
		imagealphablending($constrained, false);
		imagesavealpha($constrained, true);
		
		// Copy and Resize the Thumbnail
		imagecopyresampled($constrained, $this->image, 0, 0, 0, 0, $new_w, $new_h, $this->im_w, $this->im_h);
		
		// Set the new Size
		$this->im_w = $width;
		$this->im_h = $height;

		// Reassign the Instance Image
		imagedestroy($this->image);
		$this->image = $constrained;
	}
	
	/**
	 * Crops an Image to the Specified Rectangle.
	 *
	 * @param int $x_pos						Left position of the start of the crop area.
	 * @param int $y_pos						Top position of the start of the crop area.
	 * @param int $width						Width of the crop area.
	 * @param int $height						Height of the crop area.
	 *
	 */
	public function crop($x_pos, $y_pos, $width, $height)
	{
		// Create a new Image Object
		$cropped = imagecreatetruecolor($width, $height);
		
		// Copy and Resize the Image
		imagecopyresampled($cropped, $this->image, 0, 0, $x_pos, $y_pos, $width, $height, $width, $height);
		
		// Set the new Size
		$this->im_w = $width;
		$this->im_h = $height;
		
		// Reassign the Instance Image
		imagedestroy($this->image);
		$this->image = $cropped;
	}
	
	/**
	 * Convert an Image to Greyscale
	 *
	 * @param int $mode							the greyscale mode to use. Default is
	 *											GREYSCALE_LUMINOSITY.
	 */
	public function greyscale($mode = GREYSCALE_LUMINOSITY)
	{
		// Check that the Image Exists
		if (is_null($this->image)) return FALSE;

		// Loop through the Image Pixels
		for ($x = 0; $x < $this->im_w; $x++)
		{
			for ($y = 0; $y < $this->im_h; $y++)
			{
				// Get the Colour Index
				$colour_index = imagecolorat($this->image, $x, $y);
				
				// Get the Colour
				$colour = imagecolorsforindex($this->image, $colour_index);

				// Check if we're using Average Mode
				if ($mode & GREYSCALE_AVERAGE)
				{
					// Work out the Average
					$avg = round(($colour['red'] + $colour['green'] + $colour['blue']) / 3);
				
					// Set the Colour
					$new_colour = imagecolorallocatealpha($this->image, $avg, $avg, $avg, $colour['alpha']);
					imagesetpixel($this->image, $x, $y, $new_colour);
				}
				// or Luminosity Mode
				elseif ($mode & GREYSCALE_LUMINOSITY)
				{
					// Work out the values
					$colour['red'] *= 0.21;
					$colour['green'] *= 0.71;
					$colour['blue'] *= 0.07;
					
					$avg = $colour['red'] + $colour['green'] + $colour['blue'];
					
					// Set the colour
					$new_colour = imagecolorallocatealpha($this->image, $avg, $avg, $avg, $colour['alpha']);
					imagesetpixel($this->image, $x, $y, $new_colour);
				}
			}
		}
	}

	/**
	 * Adds a reflection underneath the image.
	 *
	 * @param int $reflection_height			the height of the reflection.Default is
	 *											half of the original height.
	 */
	public function add_reflection($reflection_height = null)
	{
		// Check that the Image Exists
		if (is_null($this->image)) return FALSE;
		
		// Get the Height of the Reflection
		if (is_null($reflection_height)) {
			$reflection_height = round($this->im_h / 2);
		}

		// Alpha Increment
		$alpha_inc = $reflection_height / 127;
		$running_alpha = 0;
		$nth_row = 0;

		// Create a new Image
		$new_image = imagecreatetruecolor($this->im_w, $this->im_h + $reflection_height);
		
		// Save Transparency
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		
		// Set the Transparency
		$transparent_colour = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
		imagecolortransparent($new_image, $transparent_colour);
		imagefill($new_image, 0, 0, $transparent_colour);

		// Copy the Original Image Over
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $this->im_w, $this->im_h, $this->im_w, $this->im_h);
		
		// Loop through and set the Reflection Pixels
		for ($y = 0; $y < $reflection_height; $y++)
		{
			if ($y == $nth_row)
			{
				// Set the Alpha
				$nth_row = round($alpha_inc + $nth_row);
				
				// Increment the Alpha
				$running_alpha++;
			}
			
			for ($x = 0; $x < $this->im_w; $x++)
			{
				// Get the Colour Index
				$colour_index = imagecolorat($new_image, $x, $this->im_h - $y - 1);
				
				// Get the Colour
				$colour = imagecolorsforindex($new_image, $colour_index);
				
				$alpha = $running_alpha + $colour['alpha'];
				
				if ($alpha > 127) $alpha = 127;

				// Set the new Colour
				if ($colour_index !== $transparent_colour)
				{
					$new_colour = imagecolorallocatealpha($new_image, $colour['red'], $colour['green'], $colour['blue'], $alpha);
					imagesetpixel($new_image, $x, $this->im_h + $y, $new_colour);
				}
			}
		}
		
		// Set the new Size
		$this->im_h = $this->im_h + $reflection_height;

		// Reassign the Instance Image
		imagedestroy($this->image);
		$this->image = $new_image;
	}
	
	/**
	 * Tints the image a particular colour.
	 *
	 * @param string $tint_colour				the HEX value of the colour to tint to.
	 * @param int $mode							the greyscale mode to use. Default is
	 *											GREYSCALE_LUMINOSITY.
	 */
	public function tint($tint_colour, $mode = GREYSCALE_LUMINOSITY)
	{
		// Check that the Image Exists
		if (is_null($this->image)) return FALSE;
		
		// Get the HEX value for the tint colour
		$tint_colour = str_replace('#', '', $tint_colour);
		list($r, $g, $b) = str_split($tint_colour, 2);
		
		// Convert to RGB
		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);
		
		// Create a multiplication factor for the tint
		$r /= 255;
		$g /= 255;
		$b /= 255;

		// Loop through and set the pixels
		for ($x = 0; $x < $this->im_w; $x++)
		{
			for ($y = 0; $y < $this->im_h; $y++)
			{
				// Get the Colour Index
				$colour_index = imagecolorat($this->image, $x, $y);
				
				// Get the Colour
				$colour = imagecolorsforindex($this->image, $colour_index);

				// Check if we're using Average Mode
				if ($mode & GREYSCALE_AVERAGE)
				{
					// Work out the Average
					$avg = round(($colour['red'] + $colour['green'] + $colour['blue']) / 3);
				}
				// or Luminosity Mode
				elseif ($mode & GREYSCALE_LUMINOSITY)
				{
					// Work out the values
					$colour['red'] *= 0.21;
					$colour['green'] *= 0.71;
					$colour['blue'] *= 0.07;
					
					$avg = $colour['red'] + $colour['green'] + $colour['blue'];
				}
				
				// Set the Colour
				$new_colour = imagecolorallocatealpha($this->image, $avg * $r, $avg * $g, $avg * $b, $colour['alpha']);
				imagesetpixel($this->image, $x, $y, $new_colour);
			}
		}
	}

	/**
	 * Save the current image to a file.
	 *
	 * @param string $filename					The name of the file to save the image as. If left
	 *											empty, the original filename will be used.
	 */
	public function save($filename = null)
	{
		// If we're saving to a new Image
		if (!is_null($filename)) {
			// Determine whether we're changing the Extension as well
			$imagepath = pathinfo($filename);
			
			// Determine whether we have an extension specified
			$this->imageext = strtolower($imagepath['extension']);
			
			// Set the new Filename
			$this->imagefile = getcwd().'/'.$filename;
		}

		switch($this->imageext)
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->image, $this->imagefile);
				break;
			case 'gif':
				imagegif($this->image, $this->imagefile);
				break;
			case 'png':
				imagepng($this->image, $this->imagefile);
				break;
		}
	}
}
?>