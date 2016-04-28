<?php
/**
 * Post type archives admin functionality.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link
 * (they stay updated!).
 *
 * @package AudioTheme_Framework
 * @subpackage Archives
 *
 * @since 1.0.0
 */

/**
 * Register archive post type and setup related functionality.
 *
 * @since 1.0.0
 */
function register_audiotheme_archives() {
	$labels = array(
		'name'               => _x( 'Archives', 'post format general name', 'audiotheme' ),
		'singular_name'      => _x( 'Archive', 'post format singular name', 'audiotheme' ),
		'add_new'            => _x( 'Add New', 'audiotheme_archive',        'audiotheme' ),
		'add_new_item'       => __( 'Add New Archive',                      'audiotheme' ),
		'edit_item'          => __( 'Edit Archive',                         'audiotheme' ),
		'new_item'           => __( 'New Archive',                          'audiotheme' ),
		'view_item'          => __( 'View Archive',                         'audiotheme' ),
		'search_items'       => __( 'Search Archives',                      'audiotheme' ),
		'not_found'          => __( 'No archives found.',                   'audiotheme' ),
		'not_found_in_trash' => __( 'No archives found in Trash.',          'audiotheme' ),
		'all_items'          => __( 'All Archives',                         'audiotheme' ),
		'menu_name'          => __( 'Archives',                             'audiotheme' ),
		'name_admin_bar'     => _x( 'Archive', 'add new on admin bar',      'audiotheme' ),
	);

	$args = array(
		'can_export'                 => false,
		'capability_type'            => array( 'post', 'posts' ),
		'capabilities'               => array(
			'delete_post'            => 'delete_audiotheme_archive',
			// Custom caps prevent unnecessary fields from showing up in post_submit_meta_box().
			'create_posts'           => 'create_audiotheme_archives',
			'delete_posts'           => 'delete_audiotheme_archives',
			'delete_private_posts'   => 'delete_audiotheme_archives',
			'delete_published_posts' => 'delete_audiotheme_archives',
			'delete_others_posts'    => 'delete_audiotheme_archives',
			'publish_posts'          => 'publish_audiotheme_archives',
		),
		'exclude_from_search'        => true,
		'has_archive'                => false,
		'hierarchical'               => false,
		'labels'                     => $labels,
		'map_meta_cap'               => true,
		'public'                     => true,
		'publicly_queryable'         => true,
		'rewrite'                    => 'audiotheme_archive', // Allows slug to be edited. Extra rules wont' be generated.
		'query_var'                  => false,
		'show_ui'                    => true,
		'show_in_admin_bar'          => true,
		'show_in_menu'               => false,
		'show_in_nav_menus'          => true,
		'supports'                   => array( 'title', 'editor' ),
	);

	register_post_type( 'audiotheme_archive', apply_filters( 'audiotheme_archive_register_args', $args ) );

	add_action( 'pre_get_posts',               'audiotheme_archive_query' );
	add_filter( 'get_audiotheme_archive_meta', 'audiotheme_sanitize_audiotheme_archive_columns', 10, 5 );
	add_filter( 'get_next_post_join',          'audiotheme_archives_post_navigation_join_clause', 15 );
	add_filter( 'get_previous_post_join',      'audiotheme_archives_post_navigation_join_clause', 15 );
	add_filter( 'get_next_post_where',         'audiotheme_archives_post_navigation_where_clause' );
	add_filter( 'get_previous_post_where',     'audiotheme_archives_post_navigation_where_clause' );
	add_filter( 'get_next_post_sort',          'audiotheme_archives_post_navigation_sort_clause' );
	add_filter( 'get_previous_post_sort',      'audiotheme_archives_post_navigation_sort_clause' );
}

/**
 * Filter AudioTheme archive requests.
 *
 * Set the number of posts per archive page.
 *
 * @since 1.4.2
 * @todo Refactor to make it easier to retrieve settings and to define defaults in a single location.
 * @todo Implement a "rows" setting for calculating "posts_per_archive_page".
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_archive_query( $query ) {
	if (
		is_admin() ||
		! $query->is_main_query() ||
		! ( is_post_type_archive() || is_tax() )
	) {
		return;
	}

	$post_type = get_audiotheme_current_archive_post_type();

	if ( empty( $post_type ) ) {
		return;
	}

	// Determine if the 'posts_per_archive_page' setting is active for the current post type.
	$fields = apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );

	$columns = 1;
	if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
		$default = empty( $fields['columns']['default'] ) ? 4 : absint( $fields['columns']['default'] );
		$columns = get_audiotheme_archive_meta( 'columns', true, $default, $post_type );
	}

	if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
		// Get the number of posts to display for this post type.
		$posts_per_archive_page = get_audiotheme_archive_meta( 'posts_per_archive_page', true, '', $post_type );

		if ( ! empty( $posts_per_archive_page ) ) {
			$query->set( 'posts_per_archive_page', intval( $posts_per_archive_page ) );
		}
	}

	if ( empty( $posts_per_archive_page ) && $columns > 1 ) {
		// Default to three even rows.
		$query->set( 'posts_per_archive_page', intval( $columns * 3 ) );
	}
}

/**
 * Retrieve the AudioTheme post type for the current archive.
 *
 * @since 1.7.0
 *
 * @return string
 */
function get_audiotheme_current_archive_post_type() {
	$post_type = '';

	// Determine the current post type.
	if ( is_tax() ) {
		$post_type = get_audiotheme_current_taxonomy_archive_post_type();
	} elseif ( is_post_type_archive() ) {
		foreach ( array( 'gig', 'record', 'track', 'video' ) as $type ) {
			if ( ! is_post_type_archive( 'audiotheme_' . $type ) ) {
				continue;
			}

			$post_type = 'audiotheme_' . $type;
			break;
		}
	}

	return $post_type;
}

/**
 * Retrieve the AudioTheme post type for the current taxonomy archive.
 *
 * @since 1.7.0
 *
 * @return string
 */
function get_audiotheme_current_taxonomy_archive_post_type() {
	$post_type = '';
	$taxonomy  = get_taxonomy( get_queried_object()->taxonomy );

	if ( empty( $taxonomy->object_type ) ) {
		return $post_type;
	}

	foreach ( $taxonomy->object_type as $type ) {
		if ( false === strpos( $type, 'audiotheme_' ) ) {
			continue;
		}

		$post_type = $type;
		break;
	}

	return $post_type;
}

/**
 * Get archive post IDs.
 *
 * @since 1.0.0
 *
 * @return array Associative array with post types as keys and post IDs as the values.
 */
function get_audiotheme_archive_ids() {
	return ( $archives = get_option( 'audiotheme_archives' ) ) ? $archives : array();
}

/**
 * Get the archive post ID for a particular post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type_name Optional. Post type name
 * @return array
 */
function get_audiotheme_post_type_archive( $post_type = null ) {
	$post_type = ( $post_type ) ? $post_type : get_post_type();
	$archives = get_audiotheme_archive_ids();

	if ( empty( $post_type ) ) {
		$post_type = get_query_var( 'post_type' );
	}

	return empty( $archives[ $post_type ] ) ? null : $archives[ $post_type ];
}

/**
 * Determine if the current template is a post type archive.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function is_audiotheme_post_type_archive() {
	return ( is_post_type_archive( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_video' ) ) );
}

/**
 * Determine if a post ID is for a post type archive post.
 *
 * @since 1.0.0
 *
 * @param int $archive_id Post ID.
 * @return string|bool Post type name if true, otherwise false.
 */
function is_audiotheme_post_type_archive_id( $archive_id ) {
	$archives = get_audiotheme_archive_ids();
	return array_search( $archive_id, $archives );
}

/**
 * Retrieve archive meta.
 *
 * @since 1.0.0
 *
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Optional. Whether to return a single value.
 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_audiotheme_archive_meta( $key = '', $single = false, $default = null, $post_type = null ) {
	$post_type = ( empty( $post_type ) ) ? get_post_type() : $post_type;

	if (
		! $post_type &&
		(
			! is_audiotheme_post_type_archive() ||
			! is_tax( array( 'audiotheme_record_type', 'audiotheme_video_category' ) )
		)
	) {
		return null;
	}

	$archive_id = get_audiotheme_post_type_archive( $post_type );
	if ( ! $archive_id ) {
		return null;
	}

	$value = get_post_meta( $archive_id, $key, $single );
	if ( empty( $value ) && ! empty( $default ) ) {
		$value = $default;
	}

	return apply_filters( 'get_audiotheme_archive_meta', $value, $key, $single, $default, $post_type );
}

/**
 * Sanitize archive columns setting.
 *
 * The allowd columns value may be different between themes, so make sure it exists in the settings defined by the theme, otherwise, return the theme default.
 *
 * @since 1.4.4
 *
 * @param mixed $value Existing meta value.
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Optional. Whether to return a single value.
 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function audiotheme_sanitize_audiotheme_archive_columns( $value, $key, $single, $default, $post_type ) {
	if ( 'columns' !== $key || $value === $default ) {
		return $value;
	}

	$fields = apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );
	if ( ! empty( $fields['columns']['choices'] ) && ! in_array( $value, $fields['columns']['choices'] ) ) {
		$value = $default;
	}

	return $value;
}

/**
 * Save the active archive IDs.
 *
 * Determines when an archive has become inactive and moves it to a separate
 * option so that if it's activated again in the future, a new post won't be
 * created.
 *
 * Will flush rewrite rules if any changes are detected.
 *
 * @since 1.0.0
 *
 * @param array $ids Associative array of post type slugs as keys and archive post IDs as the values.
 */
function audiotheme_archives_save_active_archives( $ids ) {
	$archives = get_audiotheme_archive_ids();
	$diff = array_diff_key( $archives, $ids );

	if ( count( $ids ) !== count( $archives ) || $diff || array_diff( $ids, $archives ) ) {
		$inactive = (array) get_option( 'audiotheme_archives_inactive' );

		// Remove $ids from $inactive.
		$inactive = array_diff_key( array_filter( $inactive ), $ids );

		// Move the diff between the $ids parameter and the $archives option to the $inactive option.
		$inactive = array_merge( $inactive, $diff );

		update_option( 'audiotheme_archives', $ids );
		update_option( 'audiotheme_archives_inactive', $inactive );

		// Update post type rewrite base options.
		foreach ( $ids as $post_type => $id ) {
			audiotheme_archives_update_post_type_rewrite_base( $post_type, $id );
		}

		update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
	}
}

/**
 * Flush the rewrite rules when an archive post slug is changed.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 * @param WP_Post $post_after Updated post object.
 * @param WP_Post $post_before Post object before udpate.
 */
function audiotheme_archives_post_updated( $post_id, $post_after, $post_before ) {
	if ( ( $post_type = is_audiotheme_post_type_archive_id( $post_id ) ) && $post_after->post_name !== $post_before->post_name ) {
		audiotheme_archives_update_post_type_rewrite_base( $post_type, $post_id );
		update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
	}
}

/**
 * Remove the post type archive reference if it's deleted.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archives_deleted_post( $post_id ) {
	if ( 'audiotheme_archive' !== get_post_type( $post_id ) ) {
		return;
	}

	$active = get_audiotheme_archive_ids();
	if ( $key = array_search( $post_id, $active ) ) {
		unset( $active[ $key ] );
		audiotheme_archives_save_active_archives( $active );
	}
}

/**
 * Update a post type's rewrite base option.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type slug.
 * @param int $archive_id Archive post ID>
 */
function audiotheme_archives_update_post_type_rewrite_base( $post_type, $archive_id ) {
	$archive = get_post( $archive_id );
	update_option( $post_type . '_rewrite_base', $archive->post_name );
}

/**
 * Display classes for a wrapper div on an AudioTheme archive page.
 *
 * @since 1.2.1
 * @uses audiotheme_class()
 *
 * @param array|string $classes Optional. List of default classes as an array or space-separated string.
 * @param array|string $args Optional. Override defaults.
 * @return array
 */
function audiotheme_archive_class( $classes = array(), $args = array() ) {
	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		// Split a string.
		$classes = preg_split( '#\s+#', $classes );
	}

	if ( is_audiotheme_post_type_archive() ) {
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		$post_type_class = 'audiotheme-archive-' . str_replace( 'audiotheme_', '', $post_type );
		$classes = array_merge( $classes, array( 'audiotheme-archive', $post_type_class ) );
	}

	return audiotheme_class( 'archive', $classes, $args );
}

/**
 * Provide an edit link for archives in the admin bar.
 *
 * @since 1.2.1
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object instance.
 */
function audiotheme_archives_admin_bar_edit_menu( $wp_admin_bar ) {
	if ( ! is_admin() && is_audiotheme_post_type_archive() ) {
		$id = get_audiotheme_post_type_archive();
		$post_type_object = get_post_type_object( get_post_type( $id ) );

		if ( empty( $post_type_object ) ) {
			return;
		}

		$wp_admin_bar->add_menu( array(
			'id'    => 'edit',
			'title' => $post_type_object->labels->edit_item,
			'href'  => get_edit_post_link( $id ),
		) );
	}
}

/**
 * Filter the adjacent posts JOIN clause.
 *
 * The `$where` clauses generated by the JOIN functionality in
 * `get_adjacent_post()` isn't passed to the `get_*_post_where`, so it's
 * blanked out here since it's not available to be appended.
 *
 * @since 1.8.4
 *
 * @param string $join SQL clause.
 * @return string
 */
function audiotheme_archives_post_navigation_join_clause( $join ) {
	global $wpdb;

	if ( ! in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ), true ) ) {
		return $join;
	}

	$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', get_post_type() );

	if ( 'post_date' !== $orderby ) {
		$join = '';
	}

	if ( 'audiotheme_record' === get_post_type() && 'release_year' === $orderby ) {
		$join = "INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id";
	}

	return $join;
}

/**
 * Filter the adjacent posts WHERE clause.
 *
 * @since 1.8.4
 *
 * @param string $where WHERE clause.
 * @return string
 */
function audiotheme_archives_post_navigation_where_clause( $where ) {
	global $wpdb;

	if ( in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
		$post      = get_post();
		$previous  = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$adjacent  = $previous ? 'previous' : 'next';
		$operation = $previous ? '<' : '>';
		$orderby   = get_audiotheme_archive_meta( 'orderby', true, 'release_year', get_post_type() );
		$order     = $previous ? 'DESC' : 'ASC';

		if ( 'custom' == $orderby ) {
			$where = $wpdb->prepare(
				"WHERE p.menu_order $operation %d AND p.post_type = %s AND p.post_status = 'publish'",
				$post->menu_order,
				$post->post_type
			);
		} elseif ( 'title' == $orderby ) {
			$where = $wpdb->prepare(
				"WHERE p.post_title $operation %s AND p.post_type = %s AND p.post_status = 'publish' AND ID != %d",
				$post->post_title,
				$post->post_type,
				$post->ID
			);
		} elseif ( 'post_date' == $orderby ) {
			$operation = $previous ? '>' : '<';
			$where = $wpdb->prepare(
				"WHERE p.post_date $operation %s AND p.post_type = %s AND p.post_status = 'publish'",
				$post->post_date,
				$post->post_type
			);
		} elseif ( 'release_year' == $orderby ) {
			$operation       = $previous ? '>' : '<';
			$operation_title = $previous ? '<' : '>';
			$operation_year  = $previous ? '>=' : '<=';

			$where = $wpdb->prepare(
				"WHERE
					pm.meta_key = '_audiotheme_release_year' AND
					(
						CAST( pm.meta_value AS UNSIGNED ) $operation %d OR
						( CAST( pm.meta_value AS UNSIGNED ) $operation_year %d AND p.post_title $operation_title %s )
					) AND
					p.post_type = %s AND p.post_status = 'publish' AND ID != %d",
				substr( $post->_audiotheme_release_year, 0, 4 ),
				substr( $post->_audiotheme_release_year, 0, 4 ),
				$post->post_title,
				$post->post_type,
				$post->ID
			);
		}
	}

	if ( 'audiotheme_track' == get_post_type() ) {
		$post      = get_post();
		$previous  = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$adjacent  = $previous ? 'previous' : 'next';
		$operation = $previous ? '<' : '>';
		$order     = $previous ? 'DESC' : 'ASC';

		$where = $wpdb->prepare(
			"WHERE p.menu_order $operation %d AND p.post_type = %s AND p.post_parent = %d AND p.post_status = 'publish'",
			$post->menu_order,
			$post->post_type,
			$post->post_parent
		);
	}

	return $where;
}

/**
 * Filter the adjacent posts ORDER BY clause.
 *
 * @since 1.8.4
 *
 * @param string $sort ORDER BY clause.
 * @return string
 */
function audiotheme_archives_post_navigation_sort_clause( $sort ) {
	if ( in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
		$previous = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$orderby  = get_audiotheme_archive_meta( 'orderby', true, 'release_year', get_post_type() );
		$order    = $previous ? 'DESC' : 'ASC';

		if ( 'custom' == $orderby ) {
			$sort = "ORDER BY p.menu_order $order LIMIT 1";
		} elseif ( 'title' == $orderby ) {
			$sort = "ORDER BY p.post_title $order LIMIT 1";
		} elseif( 'post_date' == $orderby ) {
			$order = $previous ? 'ASC' : 'DESC';
			$sort  = "ORDER BY p.post_date $order LIMIT 1";
		} elseif ( 'release_year' == $orderby ) {
			$order = $previous ? 'ASC' : 'DESC';
			$sort  = "ORDER BY pm.meta_value $order, p.post_title ASC LIMIT 1";
		}
	}

	if ( 'audiotheme_track' == get_post_type() ) {
		$post     = get_post();
		$previous = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
		$order    = $previous ? 'DESC' : 'ASC';

		$sort = "ORDER BY p.menu_order $order LIMIT 1";
	}

	return $sort;
}
