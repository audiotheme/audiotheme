<?php
/**
 * Archive post type registration and integration.
 *
 * @package   AudioTheme\Archives
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class for registering the archive post type and integration.
 *
 * @package AudioTheme\Archives
 * @since   2.0.0
 */
class AudioTheme_PostType_Archive extends AudioTheme_PostType_AbstractPostType {
	/**
	 * Archives module.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Module_Archives
	 */
	protected $module;

	/**
	 * Post type name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_archive';

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 *
	 * @param AudioTheme_Module_Archives $module Archives module.
	 */
	public function __construct( AudioTheme_Module_Archives $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'init',                    array( $this, 'register_post_type' ), 5 );
		add_action( 'pre_get_posts',           array( $this, 'pre_get_posts' ) );
		add_filter( 'post_type_link',          array( $this, 'post_type_link' ), 10, 3 );
		add_filter( 'post_type_archive_link',  array( $this, 'post_type_archive_link' ), 10, 2 );
		add_filter( 'post_type_archive_title', array( $this, 'post_type_archive_title' ) );
		add_action( 'admin_bar_menu',          array( $this, 'admin_bar_edit_menu' ), 80 );
		add_action( 'post_updated',            array( $this, 'on_archive_update' ), 10, 3 );
		add_action( 'delete_post',             array( $this, 'on_archive_delete' ) );
		add_action( 'post_updated_messages',   array( $this, 'post_updated_messages' ) );
		add_filter( 'get_next_post_join',      array( $this, 'post_navigation_join_clause' ), 15 );
		add_filter( 'get_previous_post_join',  array( $this, 'post_navigation_join_clause' ), 15 );
		add_filter( 'get_next_post_where',     array( $this, 'post_navigation_where_clause' ) );
		add_filter( 'get_previous_post_where', array( $this, 'post_navigation_where_clause' ) );
		add_filter( 'get_next_post_sort',      array( $this, 'post_navigation_sort_clause' ) );
		add_filter( 'get_previous_post_sort',  array( $this, 'post_navigation_sort_clause' ) );

		// Prevent the audiotheme_archive post type rules from being registered.
		add_filter( 'audiotheme_archive_rewrite_rules', '__return_empty_array' );
	}

	/**
	 * Filter archive queries.
	 *
	 * Sets the number of posts per archive page based on saved archive meta.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function pre_get_posts( $wp_query ) {
		$post_type = apply_filters( 'audiotheme_archive_query_post_type', $this->module->get_current_archive_post_type(), $wp_query );

		if ( empty( $post_type ) && $this->module->is_post_type_archive() ) {
			$post_type = $this->module->get_post_type();
		}

		if ( is_admin() || ! $wp_query->is_main_query() || empty( $post_type ) ) {
			return;
		}

		// Determine if the 'posts_per_archive_page' setting is active for the current post type.
		$fields = $this->module->get_settings_fields( $post_type );

		$columns = 1;
		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$default = empty( $fields['columns']['default'] ) ? 4 : absint( $fields['columns']['default'] );
			$columns = $this->module->get_archive_meta( 'columns', true, $default, $post_type );
		}

		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			// Get the number of posts to display for this post type.
			$posts_per_archive_page = $this->module->get_archive_meta( 'posts_per_archive_page', true, 0, $post_type );

			if ( ! empty( $posts_per_archive_page ) ) {
				$wp_query->set( 'posts_per_archive_page', intval( $posts_per_archive_page ) );
			}
		}

		if ( empty( $posts_per_archive_page ) && $columns > 1 ) {
			// Default to three even rows.
			$wp_query->set( 'posts_per_archive_page', intval( $columns * 3 ) );
		}

		do_action_ref_array( 'audiotheme_archive_query', array( $wp_query, $post_type ) );
	}

	/**
	 * Filter archive CPT permalinks to match the corresponding post type's
	 * archive link.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $post_link Default permalink.
	 * @param WP_Post $post Post object.
	 * @param bool    $leavename Optional, defaults to false. Whether to keep post name.
	 * @return string Permalink.
	 */
	public function post_type_link( $post_link, $post, $leavename ) {
		global $wp_rewrite;

		if ( $this->post_type !== $post->post_type ) {
			return $post_link;
		}

		$post_type        = $this->module->is_archive_id( $post->ID );
		$post_type_object = get_post_type_object( $post_type );

		if ( get_option( 'permalink_structure' ) ) {
			$front = '/';
			if ( $wp_rewrite->using_index_permalinks() ) {
				$front .= $wp_rewrite->index . '/';
			}

			if ( isset( $post_type_object->rewrite ) && $post_type_object->rewrite['with_front'] ) {
				$front = $wp_rewrite->front;
			}

			if ( $leavename ) {
				$post_link = home_url( $front . '%postname%/' );
			} else {
				$post_link = home_url( $front . $post->post_name . '/' );
			}
		} else {
			$post_link = add_query_arg( 'post_type', $post_type, home_url( '/' ) );
		}

		return $post_link;
	}

	/**
	 * Filter post type archive permalinks.
	 *
	 * @since 2.0.0
	 *
	 * @param string $link Post type archive link.
	 * @param string $post_type Post type name.
	 * @return string
	 */
	public function post_type_archive_link( $link, $post_type ) {
		$archive_post_id = $this->module->get_archive_id( $post_type );

		if ( ! empty( $archive_post_id ) ) {
			$link = get_permalink( $archive_post_id );
		}

		return $link;
	}

	/**
	 * Filter the default post_type_archive_title() template tag and replace
	 * with custom archive title.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Post type archive title.
	 * @return string
	 */
	public function post_type_archive_title( $title ) {
		$post_type_object = get_queried_object();
		return $this->module->get_archive_title( $post_type_object->name, $title );
	}

	/**
	 * Provide an edit link for archives in the admin bar.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar object instance.
	 */
	public function admin_bar_edit_menu( $wp_admin_bar ) {
		if ( is_admin() || ! $this->module->is_post_type_archive() ) {
			return;
		}

		$archive_post_id  = $this->module->get_archive_id();
		$post_type_object = get_post_type_object( get_post_type( $archive_post_id ) );

		if ( empty( $post_type_object ) ) {
			return;
		}

		$wp_admin_bar->add_menu( array(
			'id'    => 'edit',
			'title' => $post_type_object->labels->edit_item,
			'href'  => get_edit_post_link( $archive_post_id ),
		) );
	}

	/**
	 * Flush the rewrite rules when an archive post slug is changed.
	 *
	 * @since 2.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post_after Updated post object.
	 * @param WP_Post $post_before Post object before udpate.
	 */
	public function on_archive_update( $post_id, $post_after, $post_before ) {
		$post_type = $this->module->is_archive_id( $post_id );

		if ( $post_type && $post_after->post_name !== $post_before->post_name ) {
			$this->module->update_post_type_rewrite_base( $post_type, $post_id );
			update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
		}
	}

	/**
	 * Delete the cached reference to an archive post.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Post type name.
	 */
	public function on_archive_delete( $post_type ) {
		$archives = $this->module->get_archive_ids();

		// Look up the post type by post ID.
		if ( is_int( $post_type ) ) {
			$post_type = array_search( $post_type, $archives );
		}

		if ( ! empty( $archives[ $post_type ] ) ) {
			unset( $archives[ $post_type ] );
			update_option( 'audiotheme_archives', $archives );
		}
	}

	/**
	 * Filter the adjacent posts JOIN clause.
	 *
	 * The `$where` clauses generated by the JOIN functionality in
	 * `get_adjacent_post()` isn't passed to the `get_*_post_where`, so it's
	 * blanked out here since it's not available to be appended.
	 *
	 * @since 2.0.0
	 *
	 * @param string $join SQL clause.
	 * @return string
	 */
	public function post_navigation_join_clause( $join ) {
		global $wpdb;

		if ( ! in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_video' ), true ) ) {
			return $join;
		}

		$orderby = $this->get_archive_orderby();

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
	 * @since 2.0.0
	 *
	 * @param string $where WHERE clause.
	 * @return string
	 */
	public function post_navigation_where_clause( $where ) {
		global $wpdb;

		if ( in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
			$post      = get_post();
			$previous  = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
			$adjacent  = $previous ? 'previous' : 'next';
			$operation = $previous ? '<' : '>';
			$orderby   = $this->get_archive_orderby();
			$order     = $previous ? 'DESC' : 'ASC';

			if ( 'custom' === $orderby ) {
				$where = $wpdb->prepare(
					"WHERE p.menu_order $operation %d AND p.post_type = %s AND p.post_status = 'publish'",
					$post->menu_order,
					$post->post_type
				);
			} elseif ( 'title' === $orderby ) {
				$where = $wpdb->prepare(
					"WHERE p.post_title $operation %s AND p.post_type = %s AND p.post_status = 'publish' AND ID != %d",
					$post->post_title,
					$post->post_type,
					$post->ID
				);
			} elseif ( 'post_date' === $orderby ) {
				$operation = $previous ? '>' : '<';
				$where = $wpdb->prepare(
					"WHERE p.post_date $operation %s AND p.post_type = %s AND p.post_status = 'publish'",
					$post->post_date,
					$post->post_type
				);
			} elseif ( 'release_year' === $orderby ) {
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

		if ( 'audiotheme_track' === get_post_type() ) {
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
	 * @since 2.0.0
	 *
	 * @param string $sort ORDER BY clause.
	 * @return string
	 */
	public function post_navigation_sort_clause( $sort ) {
		if ( in_array( get_post_type(), array( 'audiotheme_record', 'audiotheme_video' ) ) ) {
			$previous = ( 0 === strpos( current_filter(), 'get_previous_post_' ) );
			$orderby  = $this->get_archive_orderby();
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

	/**
	 * Retrieve post type registration argments.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
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
			'labels'                     => $this->get_labels(),
			'map_meta_cap'               => true,
			'public'                     => true,
			'publicly_queryable'         => true, // Necessary to make the permalink editor visible.
			'rewrite'                    => 'audiotheme_archive', // Allows the slug to be edited. Extra rules wont' be generated.
			'query_var'                  => false,
			'show_ui'                    => true,
			'show_in_admin_bar'          => true,
			'show_in_menu'               => false,
			'show_in_nav_menus'          => true,
			'supports'                   => array( 'title', 'editor' ),
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
			'name'                  => esc_html_x( 'Archives', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Archive', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'archive', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Archive', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Archive', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Archive', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Archive', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Archives', 'audiotheme' ),
			'not_found'             => esc_html__( 'No archives found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No archives found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Archive:', 'audiotheme' ),
			'all_items'             => esc_html__( 'Archives', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Archives', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Archive', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Post Archives', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into archive', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this archive', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter archives list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Archives list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Archives list', 'audiotheme' ),
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
			1  => esc_html__( 'Archive updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Archive updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_html__( 'Archive restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Archive published.', 'audiotheme' ),
			7  => esc_html__( 'Archive saved.', 'audiotheme' ),
			8  => esc_html__( 'Archive submitted.', 'audiotheme' ),
			9  => sprintf(
				esc_html__( 'Archive scheduled for: %s.', 'audiotheme' ),
				/* translators: Publish box date format, see http://php.net/date */
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Archive draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview archive', 'audiotheme' ),
			'view'    => esc_html__( 'View archive', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve the field to sort an archive.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_archive_orderby() {
		$default = 'post_date';

		if ( 'audiotheme_record' === get_post_type() ) {
			$default = 'release_year';
		}

		return get_audiotheme_archive_meta( 'orderby', true, $default, get_post_type() );
	}
}
