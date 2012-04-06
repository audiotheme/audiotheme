<?php
/**
 * This file handles including the widget class files,
 * and registering the widgets in WordPress.
 */

/* Include widget class files */
require_once( AUDIOTHEME_DIR . '/widgets/enews.php' );
require_once( AUDIOTHEME_DIR . '/widgets/featured-page.php' );
require_once( AUDIOTHEME_DIR . '/widgets/featured-post.php' );
require_once( AUDIOTHEME_DIR . '/widgets/latest-tweets.php' );
require_once( AUDIOTHEME_DIR . '/widgets/menu-categories.php' );
require_once( AUDIOTHEME_DIR . '/widgets/menu-pages.php' );
require_once( AUDIOTHEME_DIR . '/widgets/user-profile.php' );


add_action( 'widgets_init', 'AudioTheme_load_widgets' );
/**
 * Register widgets for use in the Genesis theme.
 *
 * @since 1.0
 */
function AudioTheme_load_widgets() {
	
	register_widget('AudioTheme_eNews_Updates');
	register_widget('AudioTheme_Featured_Page');
	register_widget('AudioTheme_Featured_Post');
	register_widget('AudioTheme_Latest_Tweets_Widget');
	register_widget('AudioTheme_Widget_Menu_Categories');
	register_widget('AudioTheme_Menu_Pages_Widget');
	register_widget('AudioTheme_User_Profile_Widget');
	
}