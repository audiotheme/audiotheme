<?php
/**
 * Set up discography functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Discography
 */

/**
 * Load the discography template API.
 */
require( AUDIOTHEME_DIR . 'discography/post-template.php' );

/**
 * Load the admin interface and functionality for discography.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'discography/admin/discography.php' );
}

/**
 * Register discography post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @uses register_post_type()
 */
function audiotheme_discography_init() {
	register_post_type( 'audiotheme_record', array(
		'capability_type'        => 'post',
		'has_archive'            => get_audiotheme_discography_rewrite_base(),
		'hierarchical'           => true,
		'labels'                 => array(
			'name'               => _x( 'Records', 'post format general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Record', 'post format singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'audiotheme_record', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Record', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Record', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Record', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Record', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Records', 'audiotheme-i18n' ),
			'not_found'          => __( 'No records found.', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No records found in Trash.', 'audiotheme-i18n' ),
			'parent_item_colon'  => __( 'Parent Records:', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Records', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Records', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Record', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'menu_position'          => 513,
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => 'audiotheme_edit_record_meta_boxes',
		'rewrite'                => false,
		'show_ui'                => true,
		'show_in_admin_bar'      => true,
		'show_in_menu'           => true,
		'show_in_nav_menus'      => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	) );

	register_post_type( 'audiotheme_track', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Tracks', 'post format general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Track', 'post format singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'audiotheme_track', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Track', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Track', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Track', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Track', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Tracks', 'audiotheme-i18n' ),
			'not_found'          => __( 'No tracks found.', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No tracks found in Trash.', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Tracks', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Tracks', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Track', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => 'audiotheme_edit_track_meta_boxes',
		'rewrite'                => false,
		'show_ui'                => true,
		'show_in_admin_bar'      => true,
		'show_in_menu'           => 'edit.php?post_type=audiotheme_record',
		'show_in_nav_menus'      => true,
		'supports'               => array( 'title', 'editor', 'thumbnail' ),
	) );

	register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme-i18n' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme-i18n' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme-i18n' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme-i18n' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme-i18n' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme-i18n' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme-i18n' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme-i18n' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme-i18n' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme-i18n' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme-i18n' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme-i18n' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme-i18n' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme-i18n' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme-i18n' ),
		),
		'public'                         => false,
		'query_var'                      => true,
		'rewrite'                        => false,
		'show_ui'                        => false,
		'show_in_nav_menus'              => false,
	) );

	add_filter( 'generate_rewrite_rules', 'audiotheme_discography_generate_rewrite_rules' );
	add_action( 'pre_get_posts', 'audiotheme_discography_query' );
	add_action( 'template_include', 'audiotheme_discography_template_include' );
	add_filter( 'post_type_link', 'audiotheme_discography_permalinks', 10, 4 );
	add_filter( 'post_type_archive_link', 'audiotheme_discography_archive_link', 10, 2 );
	add_filter( 'wp_unique_post_slug', 'audiotheme_track_unique_slug', 10, 6 );
	add_action( 'wp_print_footer_scripts', 'audiotheme_print_tracks_js' );
	add_filter( 'post_class', 'audiotheme_record_archive_post_class' );
}

/**
 * Get the discography rewrite base. Defaults to 'music'.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_audiotheme_discography_rewrite_base() {
	$base = get_option( 'audiotheme_record_rewrite_base' );
	return ( empty( $base ) ) ? 'music' : $base;
}

/**
 * Add custom discography rewrite rules.
 *
 * @since 1.0.0
 * @see get_audiotheme_discography_rewrite_base()
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_discography_generate_rewrite_rules( $wp_rewrite ) {
	$base = get_audiotheme_discography_rewrite_base();

	$new_rules[ $base . '/tracks/?$' ] = 'index.php?post_type=audiotheme_track';
	$new_rules[ $base .'/([^/]+)/track/([^/]+)?$'] = 'index.php?audiotheme_record=$matches[1]&audiotheme_track=$matches[2]';
	$new_rules[ $base . '/([^/]+)/?$'] = 'index.php?audiotheme_record=$matches[1]';
	$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_record';

	$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
}

/**
 * Filter discography requests
 *
 * Automatically sorts records by released year.
 *
 * Tracks must belong to a record, so the parent record is set for track
 * requests.
 *
 * @since 1.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_discography_query( $query ) {
	global $wpdb;

	if ( is_admin() ) {
		return;
	}

	// Sort records by release year
	$orderby = $query->get( 'orderby' );
	if ( $query->is_main_query() && is_post_type_archive( 'audiotheme_record' ) && empty( $orderby ) ) {
		$query->set( 'meta_key', '_audiotheme_release_year' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'desc' );

		add_filter( 'posts_orderby_request', 'audiotheme_discography_query_orderby' );
	}

	// Limit requests for single tracks to the context of the parent record
	if ( $query->is_main_query() && is_single() && 'audiotheme_track' == $query->get( 'post_type' ) ) {
		if ( get_option('permalink_structure') ) {
			$record_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='audiotheme_record' AND post_name=%s LIMIT 1", $query->get( 'audiotheme_record' ) ) );
			if ( $record_id ) {
				$query->set( 'post_parent', $record_id );
			}
		} elseif ( ! empty( $_GET['post_parent'] ) ) {
			$query->set( 'post_parent', absint( $_GET['post_parent'] ) );
		}
	}
}

/**
 * Sort records by title after sorting by release year.
 *
 * @since 1.0.0
 *
 * @param string $orderby SQL order clause.
 * @return string
 */
function audiotheme_discography_query_orderby( $orderby ) {
	global $wpdb;

	return $orderby . ", {$wpdb->posts}.post_title asc";
}

/**
 * Load discography templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_discography_template_include( $template ) {
	if ( is_post_type_archive( array( 'audiotheme_record', 'audiotheme_track' ) ) ) {
		if ( is_post_type_archive( 'audiotheme_track' ) ) {
			$templates[] = 'archive-track.php';
		}

		$templates[] = 'archive-record.php';
		$template = audiotheme_locate_template( $templates );
		do_action( 'audiotheme_template_include', $template );
	} elseif ( is_singular( 'audiotheme_record' ) ) {
		$template = audiotheme_locate_template( 'single-record.php' );
		do_action( 'audiotheme_template_include', $template );
	} elseif ( is_singular( 'audiotheme_track' ) ) {
		$template = audiotheme_locate_template( 'single-track.php' );
		do_action( 'audiotheme_template_include', $template );
	}

	return $template;
}

/**
 * Filter discography permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a discography post type.
 *
 * @since 1.0.0
 * @see get_post_permalink()
 * @see audiotheme_discography_rewrite_base()
 *
 * @param string $post_link The default permalink.
 * @param object $post_link The record or track to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The record or track permalink.
 */
function audiotheme_discography_permalinks( $post_link, $post, $leavename, $sample ) {
	global $wpdb;

	$is_draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );

	if ( ! $is_draft_or_pending ) {
		$permalink = get_option( 'permalink_structure' );

		if ( ! empty( $permalink ) && 'audiotheme_record' == get_post_type( $post ) ) {
			$base = get_audiotheme_discography_rewrite_base();
			$slug = ( $leavename ) ? '%postname%' : $post->post_name;
			$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
		}

		if ( ! empty( $permalink ) && 'audiotheme_track' == get_post_type( $post ) && ! empty( $post->post_parent ) ) {
			$base = get_audiotheme_discography_rewrite_base();
			$slug = ( $leavename ) ? '%postname%' : $post->post_name;
			$record = get_post( $post->post_parent );
			if ( $record ) {
				$post_link = home_url( sprintf( '/%s/%s/track/%s/', $base, $record->post_name, $slug ) );
			}
		} elseif ( empty( $permalink ) && 'audiotheme_track' == get_post_type( $post ) && ! empty( $post->post_parent ) ) {
			$post_link = add_query_arg( 'post_parent', $post->post_parent, $post_link );
		}
	}

	return $post_link;
}

/**
 * Filter the permalink for the discography archive.
 *
 * @since 1.0.0
 * @uses audiotheme_discography_rewrite_base()
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The discography archive URL.
 */
function audiotheme_discography_archive_link( $link, $post_type ) {
	$permalink = get_option( 'permalink_structure' );
	if ( ! empty( $permalink ) && ( 'audiotheme_record' == $post_type || 'audiotheme_track' == $post_type ) ) {
		$base = get_audiotheme_discography_rewrite_base();
		$link = home_url( '/' . $base . '/' );
	}

	return $link;
}

/**
 * Ensure track slugs are unique.
 *
 * Tracks should always be associated with a record so their slugs only need
 * to be unique within the context of a record.
 *
 * @since 1.0.0
 * @see wp_unique_post_slug()
 *
 * @param string $slug The desired slug (post_name).
 * @param integer $post_ID
 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
 * @param string $post_type
 * @param integer $post_parent
 * @param string $original_slug Slug passed to the uniqueness method.
 * @return string
 */
function audiotheme_track_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
	global $wpdb, $wp_rewrite;

	if ( 'audiotheme_track' == $post_type ) {
		$slug = $original_slug;

		$feeds = $wp_rewrite->feeds;
		if ( ! is_array( $feeds ) ) {
			$feeds = array();
		}

		// Make sure the track slug is unique within the context of the record only.
		$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name=%s AND post_type=%s AND post_parent=%d AND ID!=%d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_parent, $post_ID ) );

		if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
			$suffix = 2;
			do {
				$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_parent, $post_ID ) );
				$suffix++;
			} while ( $post_name_check );
			$slug = $alt_post_name;
		}
	}

	return $slug;
}

/**
 * Transform a track id or array of data into the expected format for use as a
 * JavaScript object.
 *
 * @since 1.1.0
 *
 * @param int|array $track Track ID or array of expected track properties.
 * @return array
 */
function audiotheme_prepare_track_for_js( $track ) {
	$data = array(
		'artist'  => '',
		'artwork' => '',
		'mp3'     => '',
		'record'  => '',
		'title'   => '',
	);

	// Enqueue a track post type.
	if ( 'audiotheme_track' == get_post_type( $track ) ) {
		$track = get_post( $track );
		$record = get_post( $track->post_parent );

		$data['artist'] = get_audiotheme_track_artist( $track->ID );
		$data['mp3'] = get_audiotheme_track_file_url( $track->ID );
		$data['record'] = $record->post_title;
		$data['title'] = $track->post_title;

		if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $track ) ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'audiotheme_track_js_artwork_size', 'thumbnail' ) );
			$data['artwork'] = $image[0];
		}
	}

	// Add the track data directly.
	elseif ( is_array( $track ) ) {
		if ( isset( $track['artwork'] ) ) {
			$data['artwork'] = esc_url( $track['artwork'] );
		}

		if ( isset( $track['file'] ) ) {
			$data['mp3'] = esc_url( $track['file'] );
		}

		if ( isset( $track['mp3'] ) ) {
			$data['mp3'] = esc_url( $track['mp3'] );
		}

		if ( isset( $track['title'] ) ) {
			$data['title'] = wp_strip_all_tags( $track['title'] );
		}

		$data = array_merge( $track, $data );
	}

	$data = apply_filters( 'audiotheme_track_js_data', $data, $track );

	return $data;
}

/**
 * Convert enqueue track lists into an array of tracks prepared for JavaScript
 * and output the JSON-encoded object in the footer.
 *
 * @since 1.1.0
 */
function audiotheme_print_tracks_js() {
	global $audiotheme_enqueued_tracks;

	if ( empty( $audiotheme_enqueued_tracks ) || ! is_array( $audiotheme_enqueued_tracks ) ) {
		return;
	}

	$lists = array();

	// @todo The track & record ids should be collected at some point so they can all be fetched in a single query.

	foreach ( $audiotheme_enqueued_tracks as $list => $tracks ) {
		if ( empty( $tracks ) || ! is_array( $tracks ) ) {
			continue;
		}

		do_action( 'audiotheme_prepare_tracks', $list );

		foreach ( $tracks as $track ) {
			if ( 'audiotheme_record' == get_post_type( $track ) ) {
				$record_tracks = get_audiotheme_record_tracks( $track, array( 'has_file' => true ) );

				if ( $record_tracks ) {
					foreach ( $record_tracks as $record_track ) {
						if ( $track_data = audiotheme_prepare_track_for_js( $record_track ) ) {
							$lists[ $list ][] = $track_data;
						}
					}
				}
			} elseif ( $track_data = audiotheme_prepare_track_for_js( $track ) ) {
				$lists[ $list ][] = $track_data;
			}
		}
	}

	// Print a JavaScript object.
	if ( ! empty( $lists ) ) {
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "var AudiothemeTracks = " . json_encode( $lists ) . ";\n";
		echo "/* ]]> */\n";
		echo "</script>\n";
	}
}

/**
 * Add classes to record posts on the archive page.
 *
 * Classes serve as helpful hooks to aid in styling across various browsers.
 *
 * - Adds nth-child classes to record posts.
 *
 * @since 1.2.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_record_archive_post_class( $classes ) {
	global $wp_query;

	if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_record' ) ) {
		$nth_child_classes = audiotheme_nth_child_classes( array(
			'current' => $wp_query->current_post + 1,
			'max'     => 4,
		) );

		$classes = array_merge( $classes, $nth_child_classes );
	}

	return $classes;
}
