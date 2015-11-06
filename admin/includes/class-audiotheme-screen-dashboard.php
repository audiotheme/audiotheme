<?php
/**
 * Dashboard screen functionality.
 *
 * @package AudioTheme\Administration
 * @since 1.9.0
 */

/**
 * Dashboard screen class.
 *
 * @package AudioTheme\Administration
 * @since 1.9.0
 */
class AudioTheme_Screen_Dashboard {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
	}

	/**
	 * Add menu items.
	 *
	 * @since 1.0.0
	 */
	public function add_menu_item() {
		add_menu_page(
			__( 'AudioTheme', 'audiotheme' ),
			__( 'AudioTheme', 'audiotheme' ),
			'edit_posts',
			'audiotheme',
			array( $this, 'render_screen' ),
			audiotheme_encode_svg( 'admin/images/dashicons/audiotheme.svg' ),
			511
		);

		add_submenu_page(
			'audiotheme',
			__( 'Features', 'audiotheme' ),
			__( 'Features', 'audiotheme' ),
			'edit_posts',
			'audiotheme',
			array( $this, 'render_screen' )
		);
	}

	/**
	 * Display the screen header.
	 *
	 * @since 1.0.0
	 */
	public function render_screen_header() {
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard-header.php' );
	}

	/**
	 * Display the screen footer.
	 *
	 * @since 1.0.0
	 */
	public function render_screen_footer() {
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard-footer.php' );
	}

	/**
	 * Display the Dashboard screen.
	 *
	 * @since 1.0.0
	 */
	public function render_screen() {
		$this->render_screen_header();
		include( AUDIOTHEME_DIR . 'admin/views/screen-dashboard.php' );
		$this->render_screen_footer();
	}
}
