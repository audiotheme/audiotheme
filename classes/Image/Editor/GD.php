<?php
/**
 * GD image editor.
 *
 * @package   AudioTheme\Media
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class for the GD image editor.
 *
 * @package AudioTheme\Media
 * @since   2.0.0
 */
class AudioTheme_Image_Editor_GD extends WP_Image_Editor_GD {
	/**
	 * Whether the current environment is configured with required methods.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Arguments to test for GD support.
	 * @return bool
	 */
	public static function test( $args = array() ) {
		if ( ! parent::test( $args ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Trim letterbox bars from an image.
	 *
	 * @since 2.0.0
	 *
	 * @param int $fuzz Number between 0 and 100.
	 * @return $this
	 */
	public function trim( $fuzz = 0 ) {
		$size = $this->get_size();

		$top    = $this->get_top_letterbox_height( $fuzz );
		$bottom = $this->get_bottom_letterbox_height( $fuzz );
		$height = $size['height'] - $top - $bottom;

		$this->crop( 0, $top, $size['width'], $height );
		$this->normalize_aspect_ratio();

		return $this;
	}

	/**
	 * Retrieve a pixel.
	 *
	 * @since 2.0.0
	 *
	 * @param int $x Position on the x-axis.
	 * @param int $y Position on the y-axis.
	 * @return AudioThme_Image_Pixel_GD
	 */
	public function get_pixel( $x, $y ) {
		return new AudioTheme_Image_Pixel_GD( $this->image, $x, $y );
	}

	/**
	 * Whether the image size is closer to 16:9 than 4:3.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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

	/**
	 * Retrieve the height of the top letterbox bar.
	 *
	 * @since 2.0.0
	 *
	 * @param int $fuzz Number between 0 and 100.
	 * @return int
	 */
	protected function get_top_letterbox_height( $fuzz = 0 ) {
		$height = 0;
		$size   = $this->get_size();

		for ( $y = 0; $y < $size['height']; $y++ ) {
			if ( $this->is_letterbox_line( $y, $fuzz ) ) {
				continue;
			}

			$height = $y;
			break;
		}

		return $height;
	}

	/**
	 * Retrieve the height of the bottom letterbox bar.
	 *
	 * @since 2.0.0
	 *
	 * @param int $fuzz Number between 0 and 100.
	 * @return int
	 */
	protected function get_bottom_letterbox_height( $fuzz = 0 ) {
		$height = 0;
		$size   = $this->get_size();

		for ( $y = $size['height'] - 1; $y >= 0; $y-- ) {
			if ( $this->is_letterbox_line( $y, $fuzz ) ) {
				continue;
			}

			$height = $size['height'] - $y;
			break;
		}

		return $height;
	}

	/**
	 * Whether a line is part of a letterbox matte.
	 *
	 * @since 2.0.0
	 *
	 * @param int $y    Position on the y-axis.
	 * @param int $fuzz Number between 0 and 100.
	 * @return bool
	 */
	protected function is_letterbox_line( $y, $fuzz = 0 ) {
		$is_letterbox = true;
		$size         = $this->get_size();
		$fuzz         = $fuzz / 100 * 255;

		$left_luma   = $this->get_pixel( 0, $y )->get_luma();
		$middle_luma = $this->get_pixel( floor( $size['width'] / 2 ), $y )->get_luma();
		$right_luma  = $this->get_pixel( $size['width'] - 1, $y )->get_luma();

		if ( $left_luma > $fuzz || $middle_luma > $fuzz || $right_luma > $fuzz ) {
			$is_letterbox = false;
		}

		return $is_letterbox;
	}
}
