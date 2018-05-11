<?php
/**
 * Gig post type registration and integration.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class for registering the gig post type and integration.
 *
 * @package AudioTheme\Gigs
 * @since   2.0.0
 */
class AudioTheme_PostType_Gig extends AudioTheme_PostType_AbstractPostType {
	/**
	 * Gigs module.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Module_Gigs
	 */
	protected $module;

	/**
	 * Post type name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_gig';

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param AudioTheme_Module_Gigs $module Gigs module.
	 */
	public function __construct( AudioTheme_Module_Gigs $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'init',                     array( $this, 'register_post_type' ) );
		add_filter( 'query_vars',               array( $this, 'register_query_vars' ) );
		add_action( 'pre_get_posts',            array( $this, 'query' ) );
		add_filter( 'post_type_archive_link',   array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',           array( $this, 'post_permalink' ), 10, 4 );
		add_filter( 'wp_unique_post_slug',      array( $this, 'get_unique_slug' ), 10, 6 );
		add_action( 'save_post_audiotheme_gig', array( $this, 'update_bad_slug' ), 20, 2 );
		add_filter( 'post_class',               array( $this, 'post_class' ), 10, 3 );
		add_action( 'before_delete_post',       array( $this, 'on_before_delete' ) );
		add_filter( 'wp_insert_post_data',      array( $this, 'add_uuid_to_new_posts' ) );
		add_action( 'added_post_meta',          array( $this, 'sync_venue_guid' ), 10, 4 );
		add_action( 'updated_post_meta',        array( $this, 'sync_venue_guid' ), 10, 4 );
		add_filter( 'post_updated_messages',    array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Register query variables.
	 *
	 * @since 2.0.0
	 *
	 * @param array $vars Array of valid query variables.
	 * @return array
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'audiotheme_gig_range';
		return $vars;
	}

	/**
	 * Filter gigs requests.
	 *
	 * Automatically sorts gigs in ascending order by the gig date, but limits
	 * to showing upcoming gigs unless a specific date range is requested (year,
	 * month, day).
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function query( $wp_query ) {
		// Bail if this isn't a gig archive query.
		if ( is_admin() || ! $wp_query->is_main_query() || ! is_post_type_archive( 'audiotheme_gig' ) ) {
			return;
		}

		$orderby = $wp_query->get( 'orderby' );
		if ( ! empty( $orderby ) ) {
			return;
		}

		$wp_query->set( 'posts_per_archive_page', -1 );
		$wp_query->set( 'meta_key', '_audiotheme_gig_datetime' );
		$wp_query->set( 'orderby', 'meta_value' );
		$wp_query->set( 'order', 'asc' );

		if ( is_date() ) {
			if ( is_day() ) {
				$d = absint( $wp_query->get( 'day' ) );
				$m = absint( $wp_query->get( 'monthnum' ) );
				$y = absint( $wp_query->get( 'year' ) );

				$start = sprintf( '%s-%s-%s 00:00:00', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
				$end = sprintf( '%s-%s-%s 23:59:59', $y, zeroise( $m, 2 ), zeroise( $d, 2 ) );
			} elseif ( is_month() ) {
				$m = absint( $wp_query->get( 'monthnum' ) );
				$y = absint( $wp_query->get( 'year' ) );

				$start = sprintf( '%s-%s-01 00:00:00', $y, zeroise( $m, 2 ) );
				$end = sprintf( '%s 23:59:59', date( 'Y-m-t', mktime( 0, 0, 0, $m, 1, $y ) ) );
			} elseif ( is_year() ) {
				$y = absint( $wp_query->get( 'year' ) );

				$start = sprintf( '%s-01-01 00:00:00', $y );
				$end = sprintf( '%s-12-31 23:59:59', $y );
			}

			if ( isset( $start ) && isset( $end ) ) {
				$meta_query[] = array(
					'key'     => '_audiotheme_gig_datetime',
					'value'   => array( $start, $end ),
					'compare' => 'BETWEEN',
					'type'    => 'DATETIME',
				);

				$wp_query->is_date = false;
				$wp_query->is_day = false;
				$wp_query->is_month = false;
				$wp_query->is_year = false;
				$wp_query->set( 'day', null );
				$wp_query->set( 'monthnum', null );
				$wp_query->set( 'year', null );
			}
		} elseif ( 'past' === $wp_query->get( 'audiotheme_gig_range' ) ) {
			$meta_query[] = array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => date( 'Y-m-d', current_time( 'timestamp' ) ),
				'compare' => '<',
				'type'    => 'DATETIME',
			);

			$wp_query->set( 'posts_per_archive_page', null );
			$wp_query->set( 'order', 'desc' );
		} else {
			// Only show upcoming gigs.
			$meta_query[] = array(
				'key'     => '_audiotheme_gig_datetime',
				'value'   => date( 'Y-m-d', current_time( 'timestamp' ) - DAY_IN_SECONDS ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			);
		}

		if ( isset( $meta_query ) ) {
			$wp_query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Filter the permalink for the gigs archive.
	 *
	 * @since 2.0.0
	 *
	 * @param string $link The default archive URL.
	 * @param string $post_type Post type.
	 * @return string The gig archive URL.
	 */
	public function archive_permalink( $link, $post_type ) {
		if ( 'audiotheme_gig' === $post_type && get_option( 'permalink_structure' ) ) {
			$base = $this->module->get_rewrite_base();
			$link = home_url( '/' . $base . '/' );
		} elseif ( 'audiotheme_gig' === $post_type ) {
			$link = add_query_arg( 'post_type', 'audiotheme_gig', home_url( '/' ) );
		}

		return $link;
	}

	/**
	 * Filter gig permalinks to match the custom rewrite rules.
	 *
	 * Allows the standard WordPress API function get_permalink() to return the
	 * correct URL when used with a gig post type.
	 *
	 * @since 2.0.0
	 *
	 * @see get_post_permalink()
	 *
	 * @param string  $post_link The default gig URL.
	 * @param WP_Post $post The gig to get the permalink for.
	 * @param bool    $leavename Whether to keep the post name.
	 * @param bool    $sample Is it a sample permalink.
	 * @return string The gig permalink.
	 */
	public function post_permalink( $post_link, $post, $leavename, $sample ) {
		if ( ( $this->is_draft_or_pending( $post ) && ! $sample ) || 'audiotheme_gig' !== get_post_type( $post ) ) {
			return $post_link;
		}

		if ( get_option( 'permalink_structure' ) ) {
			$base      = $this->module->get_rewrite_base();
			$slug      = $leavename ? '%postname%' : $post->post_name;
			$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
		}

		return $post_link;
	}

	/**
	 * Prevent conflicts in gig permalinks.
	 *
	 * Gigs without titles will fall back to using the ID for the slug, however,
	 * when the ID is a 4 digit number, it will conflict with date-based permalinks.
	 * Any slugs that match the ID are preprended with 'gig-'.
	 *
	 * @since 2.0.0
	 *
	 * @see wp_unique_post_slug()
	 *
	 * @param string  $slug The desired slug (post_name).
	 * @param integer $post_id Post ID.
	 * @param string  $post_status No uniqueness checks are made if the post is still draft or pending.
	 * @param string  $post_type Post type.
	 * @param integer $post_parent Post parent ID.
	 * @param string  $original_slug Slug passed to the uniqueness method.
	 * @return string
	 */
	public function get_unique_slug( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug = null ) {
		global $wpdb, $wp_rewrite;

		if ( 'audiotheme_gig' !== $post_type ) {
			return $slug;
		}

		$slug = $original_slug;

		$feeds = $wp_rewrite->feeds;
		if ( ! is_array( $feeds ) ) {
			$feeds = array();
		}

		// Four-digit numeric slugs interfere with date-based archives.
		if ( $slug === $post_id ) {
			$slug = 'gig-' . $slug;

			// If a date is set, default to the date rather than the post ID.
			$datetime = get_post_meta( $post_id, '_audiotheme_gig_datetime', true );
			if ( ! empty( $datetime ) ) {
				$dt = date_parse( $datetime );
				$slug = sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) );
			}
		}

		// Make sure the gig slug is unique.
		$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_id ) );

		if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
			$suffix = 2;
			do {
				$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_id ) );
				$suffix++;
			} while ( $post_name_check );
			$slug = $alt_post_name;
		}

		return $slug;
	}

	/**
	 * Prevent conflicts with numeric gig slugs.
	 *
	 * If a slug is empty when a post is published, wp_insert_post() will base the
	 * slug off the title/ID without a way to filter it until after the post is
	 * saved. If the saved slug matches the post ID for a gig, it's prefixed with
	 * 'gig-' here to mimic the behavior in audiotheme_gig_unique_slug().
	 *
	 * @since 2.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_bad_slug( $post_id, $post ) {
		global $wpdb;

		if ( 'audiotheme_gig' !== $post->post_type ) {
			return;
		}

		if ( $post->post_name === $post_id && ! in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
			$slug = $this->get_unique_slug(
				'gig-' . $post_id,
				$post_id,
				$post->post_status,
				$post->post_type,
				$post->post_parent,
				$post_id
			);

			$wpdb->update( $wpdb->posts, array( 'post_name' => $slug ), array( 'ID' => $post_id ) );
		}
	}

	/**
	 * Update a venue's cached gig count when gig is deleted.
	 *
	 * Determines if a venue's gig_count meta field needs to be updated when a
	 * gig is deleted.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id ID of the gig being deleted.
	 */
	public function on_before_delete( $post_id ) {
		if ( 'audiotheme_gig' === get_post_type( $post_id ) ) {
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
	 * @since 2.0.0
	 *
	 * @param array        $classes List of classes.
	 * @param string|array $class One or more classes to add to the class list.
	 * @param int          $post_id An optional post ID.
	 * @return array Array of classes.
	 */
	public function post_class( $classes, $class, $post_id ) {
		if ( 'audiotheme_gig' === get_post_type( $post_id ) && audiotheme_gig_has_ticket_meta() ) {
			$classes[] = 'has-audiotheme-ticket-meta';
		}

		return $classes;
	}

	/**
	 * Synchronize the venue guid metadata when the venue id is updated.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $meta_id    Metadata ID.
	 * @param int    $post_id    Post ID.
	 * @param string $meta_key   Meta key.
	 * @param mixed  $meta_value Meta value.
	 */
	public function sync_venue_guid( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( '_audiotheme_venue_id' !== $meta_key ) {
			return;
		}

		update_post_meta( $post_id, '_audiotheme_venue_guid', get_post( $meta_value )->guid );
	}

	/**
	 * Retrieve post type registration argments.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
			'has_archive'       => $this->module->get_rewrite_base(),
			'hierarchical'      => false,
			'labels'            => $this->get_labels(),
			'menu_icon'         => audiotheme_encode_svg( 'admin/images/dashicons/gigs.svg' ),
			'menu_position'     => 512,
			'public'            => true,
			'rewrite'           => false,
			'show_in_admin_bar' => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'supports'          => array( 'title', 'editor', 'thumbnail' ),
		);
	}

	/**
	 * Retrieve post type labels.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                  => esc_html_x( 'Gigs', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Gig', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'gig', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Gig', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Gig', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Gig', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Gig', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Gigs', 'audiotheme' ),
			'not_found'             => esc_html__( 'No gigs found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No gigs found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Gig:', 'audiotheme' ),
			'all_items'             => esc_html__( 'All Gigs', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Gigs', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Gig', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Gig Archives', 'audiotheme' ),
			'attributes'            => esc_html__( 'Gig Attributes', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into gig', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this gig', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter gigs list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Gigs list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Gigs list', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve post updated messages.
	 *
	 * @since 2.0.0
	 *
	 * @param  WP_Post $post Post object.
	 * @return array
	 */
	protected function get_updated_messages( $post ) {
		return array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Gig updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Gig updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_html__( 'Gig restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Gig published.', 'audiotheme' ),
			7  => esc_html__( 'Gig saved.', 'audiotheme' ),
			8  => esc_html__( 'Gig submitted.', 'audiotheme' ),
			9  => sprintf(
				esc_html__( 'Gig scheduled for: %s.', 'audiotheme' ),
				/* translators: Publish box date format, see http://php.net/date */
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Gig draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview gig', 'audiotheme' ),
			'view'    => esc_html__( 'View gig', 'audiotheme' ),
		);
	}
}
