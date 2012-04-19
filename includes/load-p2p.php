<?php
// scbFramework
$scb_classes = array(
    'scbUtil', 'scbOptions', 'scbForms', 'scbTable',
    'scbWidget', 'scbAdminPage', 'scbBoxesPage',
    'scbCron', 'scbHooks',
);

foreach ( $scb_classes as $class_name ) {
    if ( ! class_exists( $class_name ) ) {
        include dirname( __FILE__ ) . '/scb/' . substr( $class_name, 3 ) . '.php';
    }
}

if ( ! function_exists( 'scb_init' ) ) :
function scb_init( $callback ) {
    call_user_func( $callback );
}
endif;


// Posts 2 Posts core
if ( ! function_exists( 'p2p_register_connection_type' ) ) {
    define( 'P2P_TEXTDOMAIN', 'audiotheme-18n' );
	
	$p2p_files = array(
        'storage', 'query', 'query-post', 'query-user', 'url-query',
        'util', 'side', 'list', 'type-factory', 'type', 'directed-type',
        'api', 'extra'
    );
	
    foreach ( $p2p_files as $file ) {
        require dirname( __FILE__ ) . '/p2p/' . $file . '.php';
    }
	
    add_action( 'after_switch_theme', array( 'P2P_Storage', 'install' ) );
}
?>