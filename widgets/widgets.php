<?php
/**
 * This file handles including the widget class files,
 * and registering the widgets in WordPress.
 *
 * @package Genesis
 */

/** Include widget class files */
require_once( GENESIS_WIDGETS_DIR . '/user-profile-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/enews-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/featured-post-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/featured-page-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/latest-tweets-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/menu-pages-widget.php' );
require_once( GENESIS_WIDGETS_DIR . '/menu-categories-widget.php' );

add_action( 'widgets_init', 'genesis_load_widgets' );
/**
 * Register widgets for use in the Genesis theme.
 *
 * @since 1.7
 */
function genesis_load_widgets() {
	
	register_widget('Genesis_eNews_Updates');
	register_widget('Genesis_Featured_Page');
	register_widget('Genesis_Featured_Post');
	register_widget('Genesis_Latest_Tweets_Widget');
	register_widget('Genesis_Widget_Menu_Categories');
	register_widget('Genesis_Menu_Pages_Widget');
	register_widget('Genesis_User_Profile_Widget');
	
}