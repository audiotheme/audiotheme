<?php
/**
 * GD image pixel.
 *
 * @package AudioTheme_Framework
 * @subpackage Media
 */

/**
 * Class for a single pixel in an image.
 *
 * @package AudioTheme_Framework
 * @subpackage Media
 */
class AudioTheme_Image_Pixel_GD {
	/**
	 * GD image resource.
	 *
	 * @since 1.9.0
	 * @var resource
	 */
	protected $image;

	/**
	 * Position on the x-axis.
	 *
	 * @since 1.9.0
	 * @var int
	 */
	protected $x;

	/**
	 * Position on the y-axis.
	 *
	 * @since 1.9.0
	 * @var int
	 */
	protected $y;

	/**
	 * Class constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param resource $image GD image resource.
	 * @param int      $x     Position of the pixel on the x-axis.
	 * @param int      $y     Position of the pixel on the y-axis.
	 */
	public function __construct( $image, $x, $y ) {
		$this->image = $image;
		$this->x = $x;
		$this->y = $y;
	}

	/**
	 * Retrieve the pixel's RGB values.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	public function get_rgba() {
		$rgb = imagecolorat( $this->image, $this->x, $this->y );
		return imagecolorsforindex( $this->image, $rgb );
	}

	/**
	 * Retrieve the pixel's luma.
	 *
	 * @since 1.9.0
	 *
	 * @link https://en.wikipedia.org/wiki/Luma_%28video%29
	 *
	 * @return int Luma value (0 to 255).
	 */
	public function get_luma() {
		$rgba = $this->get_rgba();
		$luma = 0.2126 * $rgba['red'] + 0.7152 * $rgba['green'] + 0.0722 * $rgba['blue'];
		return $luma;
	}
}
