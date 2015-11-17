<?php
/**
 * Load registered widgets.
 *
 * @package AudioTheme\Widgets
 */

/**
 * Widget Includes
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'includes/widgets/recent-posts.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/record.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/track.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/upcoming-gigs.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/video.php' );

/**
 * Register Supported Widgets
 *
 * Themes can load all widgets by calling add_theme_support( 'audiotheme-widgets' ).
 *
 * If support for all widgets isn't desired, a second parameter consisting of an array
 * of widget keys can be passed to load the specified widgets:
 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-gigs' ) )
 *
 * @since 1.0.0
 */
function audiotheme_widgets_init() {
	$widgets = array(
		'recent-posts'  => array( 'class' => 'Audiotheme_Widget_Recent_Posts' ),
		'record'        => array( 'class' => 'Audiotheme_Widget_Record',        'module' => 'discography' ),
		'track'         => array( 'class' => 'Audiotheme_Widget_Track',         'module' => 'discography' ),
		// 'twitter'       => 'Audiotheme_Widget_Twitter',
		'upcoming-gigs' => array( 'class' => 'Audiotheme_Widget_Upcoming_Gigs', 'module' => 'gigs' ),
		'video'         => array( 'class' => 'Audiotheme_Widget_Video',         'module' => 'videos' ),
	);

	$support = get_theme_support( 'audiotheme-widgets' );
	if ( empty( $support ) ) {
		return;
	}

	if ( is_array( $support ) ) {
		$widgets = array_intersect_key( $widgets, array_flip( $support[0] ) );
	}

	if ( empty( $widgets ) ) {
		return;
	}

	$modules = audiotheme()->modules;

	foreach ( $widgets as $widget_id => $details ) {
		if ( isset( $details['module'] ) && ! $modules->is_active( $details['module'] ) ) {
			continue;
		}

		register_widget( $details['class'] );
	}
}
