<?php
/**
 * Manage Videos administration screen integration.
 *
 * @package   AudioTheme\Videos
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class providing integration with the Manage Videos administration screen.
 *
 * @package AudioTheme\Videos
 * @since   1.9.0
 */
class AudioTheme_Screen_ManageVideos extends AudioTheme_Screen {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_filter( 'manage_edit-audiotheme_video_columns', array( $this, 'register_columns' ) );
	}

	/**
	 * Register list table columns.
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array Filtered array of column names.
	 */
	public function register_columns( $columns ) {
		// Register an image column and insert it after the checkbox column.
		$image_column = array( 'audiotheme_image' => esc_html_x( 'Image', 'column name', 'audiotheme' ) );
		$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );

		return $columns;
	}
}
