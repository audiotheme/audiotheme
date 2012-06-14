<?php
/**
 * Gigs Init
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_gigs_init' );

function audiotheme_gigs_init() {
	register_post_type( 'audiotheme_gig', array(
		'has_archive'            => get_audiotheme_gigs_rewrite_base(),
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Gigs', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Gig', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'gig', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Gig', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Gig', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Gig', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Gig', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Gigs', 'audiotheme-i18n' ),
			'not_found'          => __( 'No gigs found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No gigs found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Gigs', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Gigs', 'audiotheme-i18n' )
		),
		'menu_position'          => 512,
		'public'                 => true,
		'register_meta_box_cb'   => 'audiotheme_edit_gig_meta_boxes',
		'rewrite'                => false,
		'show_in_menu'           => 'gigs',
		'show_in_nav_menus'      => false,
		'supports'               => array( 'title', 'editor', 'thumbnail', '' )
	) );
	
	register_post_type( 'audiotheme_venue', array(
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Venues', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'venue', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Venue', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Venue', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Venue', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Venue', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Venues', 'audiotheme-i18n' ),
			'not_found'          => __( 'No venues found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No venues found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Venues', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Venues', 'audiotheme-i18n' )
		),
		'public'                 => false,
		'publicly_queryable'     => false,
		'query_var'              => 'audiotheme_venue',
		'rewrite'                => false,
		'supports'               => array( '' )
	) );
	
	p2p_register_connection_type( array(
        'name' => 'audiotheme_venue_to_gig',
        'from' => 'audiotheme_venue',
        'to' => 'audiotheme_gig',
		'cardinality' => 'one-to-many'
    ) );
	
	add_filter( 'generate_rewrite_rules', 'audiotheme_gig_generate_rewrite_rules' );
	add_action( 'pre_get_posts', 'audiotheme_gig_query' );
	add_action( 'template_redirect', 'audiotheme_gig_template_redirect' );
	add_filter( 'post_type_link', 'audiotheme_gig_permalink', 10, 4 );
}

/**
 * Get Gigs Rewrite Base
 *
 * @since 1.0
 */
function get_audiotheme_gigs_rewrite_base() {
	$base = get_option( 'audiotheme_gigs_rewrite_base' );
	return ( empty( $base ) ) ? 'shows' : $base;
}

/**
 * Add Gig Rewrite Rules
 *
 * /base/YYYY/MM/DD/(feed|ical|json)/
 * /base/YYYY/MM/DD/
 * /base/YYYY/MM/(feed|ical|json)/
 * /base/YYYY/MM/
 * /base/YYYY/(feed|ical|json)/
 * /base/YYYY/
 * /base/(feed|ical|json)/
 * /base/%postname%/
 * /base/
 *
 * @TODO:
 *     /base/tour/%tourname%/
 *     /base/past/page/2/
 *     /base/past/
 *     /base/YYYY/page/2/
 *     etc.
 *
 * @since 1.0
 */
function audiotheme_gig_generate_rewrite_rules( $wp_rewrite ) {
	$base = get_audiotheme_gigs_rewrite_base();
	
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]';
	$new_rules[ $base . '/([0-9]{4})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]';
	$new_rules[ $base . '/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&feed=$matches[1]';
	$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_gig=$matches[1]';
	$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_gig';
	
	$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
}

/**
 * Filter Gigs Requests
 *
 * @since 1.0
 */
function audiotheme_gig_query( $query ) {
	// Sort records by release year
	$orderby = get_query_var( 'orderby' );
	if ( is_main_query() && is_post_type_archive( 'audiotheme_gig' ) && empty( $orderby ) && ! is_admin() ) {
		set_query_var( 'meta_key', 'gig_datetime' );
		set_query_var( 'orderby', 'meta_value' );
		set_query_var( 'order', 'asc' );
		
		
		if ( is_date() ) {
			if ( is_day() ) {
				$d = absint( get_query_var( 'day' ) );
				$m = absint( get_query_var( 'monthnum' ) );
				$y = absint( get_query_var( 'year' ) );
				
				$start = sprintf( '%s-%s-%s 00:00:00', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
				$end = sprintf( '%s-%s-%s 23:59:59', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
			} elseif ( is_month() ) {
				$m = absint( get_query_var( 'monthnum' ) );
				$y = absint( get_query_var( 'year' ) );
				
				$start = sprintf( '%s-%s-01 00:00:00', $y, zeroise( $m, 2 ) );
				$end = sprintf( '%s 23:59:59', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );
			} elseif ( is_year() ) {
				$y = absint( get_query_var( 'year' ) );
				
				$start = sprintf( '%s-01-01 00:00:00', $y );
				$end = sprintf( '%s-12-31 23:59:59', $y );
			}
			
			if ( isset( $start ) && isset( $end ) ) {
				$meta_query[] = array(
					'key' => 'gig_datetime',
					'value' => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type' => 'DATETIME'
				);
				
				set_query_var( 'day', null );
				set_query_var( 'monthnum', null );
				set_query_var( 'year', null );
			}
		} else {
			// Only show upcoming gigs
			$meta_query[] = array(
				'key' => 'gig_datetime',
				'value' => current_time( 'mysql' ),
				'compare' => '>=',
				'type' => 'DATETIME'
			);
		}
		
		if ( isset( $meta_query ) ) {
			set_query_var( 'meta_query', $meta_query );
		}
	}
}

/**
 * Gig feeds and connections
 *
 * Caches gig-venue connections and reroutes feed requests to
 * the appropriate template for processing.
 *
 * @since 1.0
 */
function audiotheme_gig_template_redirect() {
	global $wp_query;
	
	if ( is_post_type_archive( 'audiotheme_gig' ) ) {
		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $GLOBALS['wp_query'] );
	}
	
	$type = get_query_var( 'feed' );
	if ( is_feed() && 'audiotheme_gig' == get_query_var( 'post_type' ) ) {
		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );
		
		require( AUDIOTHEME_DIR . 'gigs/feed.php' );
		
		switch( $type ) {
			case 'feed':
				load_template( AUDIOTHEME_DIR . 'gigs/feed-rss2.php' );
				break;
			case 'ical':
				load_template( AUDIOTHEME_DIR . 'gigs/feed-ical.php' );
				break;
			case 'json':
				load_template( AUDIOTHEME_DIR . 'gigs/feed-json.php' );
				break;
			default:
				$message = sprintf( __( 'ERROR: %s is not a valid feed template.' ), esc_html( $type ) );
				wp_die( $message, '', array( 'response' => 404 ) );
		}
		exit;
	}
}

/**
 * Gig Permalinks
 *
 * @since 1.0
 */
function audiotheme_gig_permalink( $post_link, $post, $leavename, $sample ) {
	global $wpdb;
	
	$permalink = get_option( 'permalink_structure' );
	
	if ( ! empty( $permalink ) && 'audiotheme_gig' == get_post_type( $post ) ) {
		$base = get_audiotheme_gigs_rewrite_base();
		$slug = ( $leavename ) ? '%postname%' : $post->post_name;
		$gig_date = get_post_meta( $post->ID, 'gig_datetime', true );
		$gig_date = ( empty( $gig_date ) ) ? time() : strtotime( $gig_date );
		
		$post_link = home_url( sprintf( '/%s/%s/',
			$base,
			$slug
		) );
	}
	
	return $post_link;
}

/**
 * Gig Inclusions
 *
 * @since 1.0
 */
require( AUDIOTHEME_DIR . 'gigs/general-template.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'gigs/admin/gigs.php' );
	require( AUDIOTHEME_DIR . 'gigs/admin/venues.php' );
}
?>