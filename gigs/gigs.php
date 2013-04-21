<?php
/**
 * Set up gig-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
 */

/**
 * Load the gig template API.
 */
require( AUDIOTHEME_DIR . 'gigs/post-template.php' );

/**
 * Load the admin interface and functionality for gigs and venues.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'gigs/admin/gigs.php' );
	require( AUDIOTHEME_DIR . 'gigs/admin/venues.php' );
}

/**
 * Register gig and venue post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @uses register_post_type()
 */
function audiotheme_gigs_init() {
	// Register Gig custom post type.
	register_post_type( 'audiotheme_gig', array(
		'has_archive'            => audiotheme_gigs_rewrite_base(),
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
			'menu_name'          => __( 'Gigs', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Gigs', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'menu_position'          => 512,
		'public'                 => true,
		'register_meta_box_cb'   => 'audiotheme_gig_edit_screen_setup',
		'rewrite'                => false,
		'show_in_menu'           => 'audiotheme-gigs',
		'show_in_nav_menus'      => false,
		'supports'               => array( 'title', 'editor', 'thumbnail' ),
	) );

	// Register Venue custom post type.
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
			'menu_name'          => __( 'Venues', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Venues', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'public'                 => false,
		'publicly_queryable'     => false,
		'query_var'              => 'audiotheme_venue',
		'rewrite'                => false,
		'supports'               => array( '' ),
	) );

	// Register the relationship between gigs and venues.
	p2p_register_connection_type( array(
        'name'        => 'audiotheme_venue_to_gig',
        'from'        => 'audiotheme_venue',
        'to'          => 'audiotheme_gig',
		'cardinality' => 'one-to-many',
    ) );

	// Hook into the rewrite generation filter and add custom rewrite rules.
	add_filter( 'generate_rewrite_rules', 'audiotheme_gig_generate_rewrite_rules' );

	// Filter the query to make sure gigs are returned in a logical way.
	add_action( 'pre_get_posts', 'audiotheme_gig_query' );

	// Make sure the correct template is loaded depending on the request.
	add_action( 'template_redirect', 'audiotheme_gig_template_redirect' );
	add_action( 'template_include', 'audiotheme_gig_template_include' );

	// Filter default permalinks to return the custom format.
	add_filter( 'post_type_link', 'audiotheme_gig_permalink', 10, 4 );
	add_filter( 'post_type_archive_link', 'audiotheme_gigs_archive_link', 10, 2 );
	add_filter( 'get_edit_post_link', 'get_audiotheme_venue_edit_link', 10, 2 );

	add_action( 'before_delete_post', 'audiotheme_gig_before_delete' );

	add_filter( 'post_class', 'audiotheme_gig_post_class', 10, 3 );
}

/**
 * Get the gigs rewrite base. Defaults to 'shows'.
 *
 * @since 1.0.0
 *
 * @return string
 */
function audiotheme_gigs_rewrite_base() {
	$base = get_option( 'audiotheme_gig_rewrite_base' );
	return ( empty( $base ) ) ? 'shows' : $base;
}

/**
 * Add custom gig rewrite rules.
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
 * @todo /base/tour/%tourname%/
 *       /base/past/page/2/
 *       /base/past/
 *       /base/YYYY/page/2/
 *       etc.
 *
 * @since 1.0.0
 * @see audiotheme_gigs_rewrite_base()
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_gig_generate_rewrite_rules( $wp_rewrite ) {
	$base = audiotheme_gigs_rewrite_base();

	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]';
	$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]';
	$new_rules[ $base . '/([0-9]{4})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]';
	$new_rules[ $base . '/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&feed=$matches[1]';
	$new_rules[ $base . '/([^/]+)/(ical|json)/?$' ] = 'index.php?audiotheme_gig=$matches[1]&feed=$matches[2]';
	$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_gig=$matches[1]';
	$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_gig';

	$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
}

/**
 * Filter gigs requests.
 *
 * Automatically sorts gigs in ascending order by the gig date, but limits to
 * showing upcoming gigs unless a specific date range is requested (year,
 * month, day).
 *
 * @since 1.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_gig_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'audiotheme_gig' ) ) {
		return;
	}

	$orderby = $query->get( 'orderby' );
	if ( ! empty( $orderby ) ) {
		return;
	}

	$query->set( 'meta_key', '_audiotheme_gig_datetime' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'asc' );

	if ( is_date() ) {
		if ( is_day() ) {
			$d = absint( $query->get( 'day' ) );
			$m = absint( $query->get( 'monthnum' ) );
			$y = absint( $query->get( 'year' ) );

			$start = sprintf( '%s-%s-%s 00:00:00', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
			$end = sprintf( '%s-%s-%s 23:59:59', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
		} elseif ( is_month() ) {
			$m = absint( $query->get( 'monthnum' ) );
			$y = absint( $query->get( 'year' ) );

			$start = sprintf( '%s-%s-01 00:00:00', $y, zeroise( $m, 2 ) );
			$end = sprintf( '%s 23:59:59', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );
		} elseif ( is_year() ) {
			$y = absint( $query->get( 'year' ) );

			$start = sprintf( '%s-01-01 00:00:00', $y );
			$end = sprintf( '%s-12-31 23:59:59', $y );
		}

		if ( isset( $start ) && isset( $end ) ) {
			$meta_query[] = array(
				'key' => '_audiotheme_gig_datetime',
				'value' => array( $start, $end ),
				'compare' => 'BETWEEN',
				'type' => 'DATETIME'
			);

			$query->set( 'day', null );
			$query->set( 'monthnum', null );
			$query->set( 'year', null );
		}
	} else {
		// Only show upcoming gigs.
		$meta_query[] = array(
			'key' => '_audiotheme_gig_datetime',
			'value' => current_time( 'mysql' ),
			'compare' => '>=',
			'type' => 'DATETIME'
		);
	}

	if ( isset( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}

/**
 * Gig feeds and venue connections.
 *
 * Caches gig->venue connections and reroutes feed requests to
 * the appropriate template for processing.
 *
 * @since 1.0.0
 * @uses $wp_query
 * @uses p2p_type()->each_connected()
 */
function audiotheme_gig_template_redirect() {
	global $wp_query;

	if ( is_post_type_archive( 'audiotheme_gig' ) ) {
		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );
	}

	$type = $wp_query->get( 'feed' );
	if ( is_feed() && 'audiotheme_gig' == $wp_query->get( 'post_type' ) ) {
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
				$message = sprintf( __( 'ERROR: %s is not a valid feed template.', 'audiotheme-i18n' ), esc_html( $type ) );
				wp_die( $message, '', array( 'response' => 404 ) );
		}
		exit;
	}
}

/**
 * Load gig templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_gig_template_include( $template ) {
	if ( is_post_type_archive( 'audiotheme_gig' ) ) {
		$template = locate_template( 'audiotheme/archive-gig.php' );
	} elseif ( is_singular( 'audiotheme_gig' ) ) {
		$template = locate_template( 'audiotheme/single-gig.php' );
	}

	return $template;
}

/**
 * Filter gig permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a gig post type.
 *
 * @since 1.0.0
 * @see get_post_permalink()
 * @see audiotheme_gigs_rewrite_base()
 *
 * @param string $post_link The default gig URL.
 * @param object $post_link The gig to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The gig permalink.
 */
function audiotheme_gig_permalink( $post_link, $post, $leavename, $sample ) {
	$is_draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );

	if ( ! empty( $post->post_name ) && ! $is_draft_or_pending ) {
		$permalink = get_option( 'permalink_structure' );

		if ( ! empty( $permalink ) && 'audiotheme_gig' == get_post_type( $post ) ) {
			$base = audiotheme_gigs_rewrite_base();
			$slug = ( $leavename ) ? '%postname%' : $post->post_name;

			$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
		}
	}

	return $post_link;
}

/**
 * Filter the permalink for the gigs archive.
 *
 * @since 1.0.0
 * @uses audiotheme_gigs_rewrite_base()
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The gig archive URL.
 */
function audiotheme_gigs_archive_link( $link, $post_type ) {
	if ( 'audiotheme_gig' == $post_type && get_option( 'permalink_structure' ) ) {
		$base = audiotheme_gigs_rewrite_base();
		$link = home_url( '/' . $base . '/' );
	} elseif ( 'audiotheme_gig' == $post_type ) {
		$link = add_query_arg( 'post_type', 'audiotheme_gig', home_url( '/' ) );
	}

	return $link;
}

/**
 * Update a venue's cached gig count when gig is deleted.
 *
 * Determines if a venue's gig_count meta field needs to be updated
 * when a gig is deleted.
 *
 * @since 1.0.0
 *
 * @param int $post_id ID of the gig being deleted.
 */
function audiotheme_gig_before_delete( $post_id ) {
	if ( 'audiotheme_gig' == get_post_type( $post_id ) ) {
		$gig = get_audiotheme_gig( $post_id );
		if ( isset( $gig->venue->ID ) ) {
			$count = get_audiotheme_venue_gig_count( $gig->venue->ID );
			update_audiotheme_venue_gig_count( $gig->venue->ID, --$count );
		}
	}
}

/**
 * Add useful classes to gig posts.
 *
 * @since 1.1.0
 *
 * @param array $classes List of classes.
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @return array Array of classes.
 */
function audiotheme_gig_post_class( $classes, $class, $post_id ) {
	if ( 'audiotheme_gig' == get_post_type( $post_id ) && audiotheme_gig_has_ticket_meta() ) {
		$classes[] = 'has-ticket-meta';
	}

	return $classes;
}

/**
 * Extend WP_Query and set some default arguments when querying for gigs.
 *
 * @since 1.0.0
 * @link http://bradt.ca/blog/extending-wp_query/
 */
class Audiotheme_Gig_Query extends WP_Query {
	/**
	 * Build the query args.
	 *
	 * @since 1.0.0
	 * @uses p2p_type()
	 *
	 * @todo Add context arg.
	 * @see audiotheme_gig_query()
	 *
	 * @param array $args WP_Query args.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'post_status'         => 'publish',
			'posts_per_page'      => get_option( 'posts_per_page' ),
			'meta_key'            => '_audiotheme_gig_datetime',
			'orderby'             => 'meta_value',
			'order'               => 'asc',
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => current_time( 'mysql' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			),
		) );

		$args = apply_filters( 'audiotheme_gig_query_args', $args );
		$args['post_type'] = 'audiotheme_gig';

		parent::__construct( $args );

		p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $this );
	}
}
