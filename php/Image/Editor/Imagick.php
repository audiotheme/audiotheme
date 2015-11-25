<?php
/**
 * ImageMagick image editor.
 *
 * @package   AudioTheme\Media
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for the ImageMagick image editor.
 *
 * @package AudioTheme\Media
 * @since   1.9.0
 */
class AudioTheme_Image_Editor_Imagick extends WP_Image_Editor_Imagick {
	/**
	 * Whether the current environment is configured with required methods.
	 *
	 * @since 1.9.0
	 *
	 * @param  array $args Arguments to test for ImageMagick support.
	 * @return bool
	 */
	public static function test( $args = array() ) {
		if ( ! parent::test( $args ) ) {
			return false;
		}

		$required_methods = array(
			'trimimage',
		);

		if ( array_diff( $required_methods, get_class_methods( 'Imagick' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Trim letterbox bars from an image.
	 *
	 * @since 1.9.0
	 *
	 * @param int $fuzz Number between 0 and 100.
	 * @return $this
	 */
	public function trim( $fuzz = 0 ) {
		$this->image->trimImage( $fuzz / 100 * $this->get_quantum() );
		$this->update_size();
		$this->normalize_aspect_ratio();
		return $this;
	}

	/**
	 * Retrieve the quantum range as an integer.
	 *
	 * @since 1.9.0
	 *
	 * @return int
	 */
	public function get_quantum() {
		$quantum_range = $this->image->getQuantumRange();
		return $quantum_range['quantumRangeLong'];
	}

	/**
	 * Whether the image size is closer to 16:9 than 4:3.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function is_widescreenish() {
		$size  = $this->get_size();
		$ratio = $size['width'] / $size['height'];
		return ( abs( $ratio - 16 / 9 ) < abs( $ratio - 4/3 ) );
	}

	/**
	 * Crop the image to 16:9 or 4:3.
	 *
	 * @since 1.9.0
	 *
	 * @return $this
	 */
	public function normalize_aspect_ratio() {
		$size = $this->get_size();

		// Determine the target dimensions based on the aspect ratio
		// of the current image size.
		if ( $this->is_widescreenish() ) {
			$height = $size['width'] / 16 * 9;
			$width  = $size['height'] / 9 * 16;
		} else {
			$height = $size['width'] / 4 * 3;
			$width  = $size['height'] / 3 * 4;
		}

		if ( $size['height'] < $height ) {
			$this->resize( $width, $size['height'], true );
		} else if ( $size['width'] < $width ) {
			$this->resize( $size['width'], $height, true );
		}

		return $this;
	}
}
