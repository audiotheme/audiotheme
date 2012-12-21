<?php
/**
 * Load registered widgets.
 *
 * @package AudioTheme_Framework
 * @subpackage Widgets
 */

/**
 * Widget Includes
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'widgets/recent-posts.php' );
require( AUDIOTHEME_DIR . 'widgets/record.php' );
require( AUDIOTHEME_DIR . 'widgets/track.php' );
require( AUDIOTHEME_DIR . 'widgets/twitter.php' );
require( AUDIOTHEME_DIR . 'widgets/upcoming-gigs.php' );
require( AUDIOTHEME_DIR . 'widgets/video.php' );

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
		'recent-posts'  => 'Audiotheme_Widget_Recent_Posts',
		'record'        => 'Audiotheme_Widget_Record',
		'track'         => 'Audiotheme_Widget_Track',
		'twitter'       => 'Audiotheme_Widget_Twitter',
		'upcoming-gigs' => 'Audiotheme_Widget_Upcoming_Gigs',
		'video'         => 'Audiotheme_Widget_Video'
	);
	
	if ( $support = get_theme_support( 'audiotheme-widgets' ) ) {
		if ( is_array( $support ) ) {
			$widgets = array_intersect_key( $widgets, array_flip( $support[0] ) );	
		}
		
		if ( ! empty( $widgets ) ) {
			foreach ( $widgets as $widget_id => $widget_class ) {
				register_widget( $widget_class );
			}
		}
	}
}
?>