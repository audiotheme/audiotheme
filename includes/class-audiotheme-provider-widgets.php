<?php
/**
 * Widgets provider.
 *
 * @package AudioTheme
 * @since 1.9.0
 */

/**
 * Widgets provider class.
 *
 * @package AudioTheme
 * @since 1.9.0
 */
class AudioTheme_Provider_Widgets {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin_AudioTheme
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 1.9.0
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
	 * @since 1.9.0
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
	 * @since 1.9.0
	 */
	public function register_widgets() {
		$widgets = array();
		$widgets['recent-posts'] = 'Audiotheme_Widget_Recent_Posts';

		if ( $this->plugin->modules['discography']->is_active() ) {
			$widgets['record'] = 'Audiotheme_Widget_Record';
			$widgets['track']  = 'Audiotheme_Widget_Track';
		}

		if ( $this->plugin->modules['gigs']->is_active() ) {
			$widgets['upcoming-gigs'] = 'Audiotheme_Widget_Upcoming_Gigs';
		}

		if ( $this->plugin->modules['videos']->is_active() ) {
			$widgets['video']  = 'Audiotheme_Widget_Video';
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
