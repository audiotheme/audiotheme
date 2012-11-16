<?php
/**
 * Loads the Posts to Posts library and dependencies.
 *
 * @package AudioTheme_Framework
 * @link https://github.com/AppThemes/wp-posts-to-posts-core
 */

/**
 * scbFramework.
 */
$scb_classes = array(
    'scbUtil', 'scbOptions', 'scbForms', 'scbTable',
    'scbWidget', 'scbAdminPage', 'scbBoxesPage',
    'scbCron', 'scbHooks',
);

foreach ( $scb_classes as $class_name ) {
    if ( ! class_exists( $class_name ) ) {
        include AUDIOTHEME_DIR . 'includes/scb/' . substr( $class_name, 3 ) . '.php';
    }
}

if ( ! function_exists( 'scb_init' ) ) :
function scb_init( $callback ) {
    call_user_func( $callback );
}
endif;

/**
 * Posts 2 Posts core.
 *
 * Requires the scbFramework.
 *
 * Posts 2 Posts requires two custom database tables to store post
 * relationships and relationship metadata. If an alternative version of the
 * library doesn't exist, the tables are created after the theme is switched.
 * If a theme is previewed or customized before it's activated, the tables
 * won't exist and any functionality relying on P2P won't work.
 *
 * @todo Remove the reliance on the after_switch_theme hook so the tables can
 *       be installed whenever the are needed (but not before the theme is
 *       activated).
 * @todo Consider creating a plugin for cleaning up the database after an
 *       AudioTheme is no longer in use.
 */
if ( ! function_exists( 'p2p_register_connection_type' ) ) {
    define( 'P2P_TEXTDOMAIN', 'audiotheme-18n' );
	
	$p2p_files = array(
        'storage', 'query', 'query-post', 'query-user', 'url-query',
        'util', 'side', 'list', 'type-factory', 'type', 'directed-type',
        'api', 'extra'
    );
	
    foreach ( $p2p_files as $file ) {
        require AUDIOTHEME_DIR . 'includes/p2p/' . $file . '.php';
    }
	
    add_action( 'after_switch_theme', array( 'P2P_Storage', 'install' ) );
}
?>