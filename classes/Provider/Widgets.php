<?php
/**
 * Widgets provider.
 *
 * @package   AudioTheme\Widgets
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Widgets provider class.
 *
 * @package AudioTheme\Widgets
 * @since   2.0.0
 */
class AudioTheme_Provider_Widgets {
	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 2.0.0
	 *
	 * @param AudioTheme_Plugin $plugin Main plugin instance.
	 * @return $this
	 */
	public function set_plugin( AudioTheme_Plugin $plugin ) {
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register supported widgets.
	 *
	 * Themes can load all widgets by calling
	 * add_theme_support( 'audiotheme-widgets' ).
	 *
	 * If support for all widgets isn't desired, a second parameter consisting
	 * of an array of widget keys can be passed to load the specified widgets:
	 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-events' ) )
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {
		$widgets = array();
		$widgets['recent-posts'] = 'AudioTheme_Widget_Recent_Posts';

		if ( $this->plugin->modules['discography']->is_active() ) {
			$widgets['record'] = 'AudioTheme_Widget_Record';
			$widgets['track']  = 'AudioTheme_Widget_Track';
		}

		if ( $this->plugin->modules['gigs']->is_active() ) {
			$widgets['upcoming-gigs'] = 'AudioTheme_Widget_Upcoming_Gigs';
		}

		if ( $this->plugin->modules['videos']->is_active() ) {
			$widgets['video']  = 'AudioTheme_Widget_Video';
		}

		$support = get_theme_support( 'audiotheme-widgets' );
		if ( ! $support || empty( $support ) ) {
			return;
		}

		if ( is_array( $support ) ) {
			$widgets = array_intersect_key( $widgets, array_flip( $support[0] ) );
		}

		if ( empty( $widgets ) ) {
			return;
		}

		foreach ( $widgets as $widget_class ) {
			register_widget( $widget_class );
		}
	}
}
