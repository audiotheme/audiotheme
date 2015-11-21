<?php
/**
 * Archives module.
 *
 * The archives module allows for editing a post type's archive properties by
 * registering a new audiotheme_archive custom post type that's connected to the
 * post type (that's a mind bender). By default, archive titles, descriptions
 * and permalinks can be managed through a familiar interface.
 *
 * It also allows archives to be easily added to nav menus without using a
 * custom link (they stay updated!).
 *
 * For a general solution, see https://github.com/cedaro/cpt-archives
 *
 * @package   AudioTheme\Archives
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Archives module class.
 *
 * @package AudioTheme\Archives
 * @since   1.9.0
 */
class AudioTheme_Module_Archives extends AudioTheme_Module {
	/**
	 * Cached archive settings.
	 *
	 * @since 1.9.0
	 * @var array Post type name is the key and the value is an array of archive settings.
	 */
	protected $archives = array();

	/**
	 * Post type for the current request.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $current_archive_post_type = '';

	/**
	 * Module id.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $id = 'archives';

	/**
	 * Retrieve the name of the module.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'Archives', 'audiotheme' );
	}

	/**
	 * Load the module.
	 *
	 * @since 1.9.0
	 *
	 * @return $this
	 */
	public function load() {
		require( $this->plugin->get_path( 'modules/archives/class-audiotheme-posttype-archive.php' ) );
		require( $this->plugin->get_path( 'modules/archives/post-template.php' ) );

		if ( is_admin() ) {
			require( $this->plugin->get_path( 'modules/archives/admin/class-audiotheme-screen-editarchive.php' ) );
		}

		return $this;
	}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		$this->plugin->register_hooks( new AudioTheme_PostType_Archive( $this ) );

		if ( is_admin() ) {
			// High priority makes archive links appear last in submenus.
			add_action( 'admin_menu',                  array( $this, 'admin_menu' ), 100 );
			add_action( 'parent_file',                 array( $this, 'parent_file' ) );
			add_filter( 'get_audiotheme_archive_meta', array( $this, 'sanitize_columns_setting' ), 10, 5 );

			$this->plugin->register_hooks( new AudioTheme_Screen_EditArchive( $this ) );
		}
	}

	/**
	 * Create an archive post for a post type.
	 *
	 * This should be called after the post type has been registered.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type name.
	 * @param array  $args {
	 *     An array of arguments. Optional.
	 *
	 *     @type string $admin_menu_parent Admin menu parent slug.
	 * }
	 * @return int Archive post ID.
	 */
	public function add_post_type_archive( $post_type, $args = array() ) {
		$archives = $this->get_archive_ids();
		$post_id  = isset( $archives[ $post_type ] ) ? $archives[ $post_type ] : '';

		if ( empty( $post_id ) ) {
			$post_id = $this->maybe_insert_archive_post( $post_type );
			$archives[ $post_type ] = $post_id;
			update_option( 'audiotheme_archives', $archives );

			// Update the post type rewrite base.
			$this->update_post_type_rewrite_base( $post_type, $post_id );
			update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
		}

		// Cache the archive args.
		$this->archives[ $post_type ] = array_merge( array( 'post_id' => $post_id ), $args );

		return $post_id;
	}

	/**
	 * Retrieve the archive post ID for a post type.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Optional. Post type name. Defaults to the current post type.
	 * @return int
	 */
	public function get_archive_id( $post_type = null ) {
		$post_type = $post_type ? $post_type : $this->get_post_type();
		$archives  = $this->get_archive_ids();

		if ( empty( $post_type ) ) {
			$post_type = get_query_var( 'post_type' );
		}

		return empty( $archives[ $post_type ] ) ? null : $archives[ $post_type ];
	}

	/**
	 * Retrieve archive post IDs.
	 *
	 * @since 1.9.0
	 *
	 * @return array Associative array with post types as keys and post IDs as the values.
	 */
	public function get_archive_ids() {
		return get_option( 'audiotheme_archives', array() );
	}

	/**
	 * Retrieve archive meta.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
	 * @param bool   $single Optional. Whether to return a single value.
	 * @param mixed  $default Optional. A default value to return if the requested meta doesn't exist.
	 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_archive_meta( $key = '', $single = false, $default = null, $post_type = null ) {
		$post_type = empty( $post_type ) ? get_post_type() : $post_type;
		if ( ! $post_type && ! $this->is_post_type_archive() ) {
			return null;
		}

		$archive_id = $this->get_archive_id( $post_type );
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
	 * Retrieve the title for a post type archive.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Optional. Post type name. Defaults to the current post type.
	 * @param string $title Optional. Fallback title.
	 * @return string
	 */
	public function get_archive_title( $post_type = '', $title = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
		}

		if ( $post_id = $this->get_archive_id( $post_type ) ) {
			$title = get_post( $post_id )->post_title;
		}

		return $title;
	}

	/**
	 * Retrieve the post type for the current query.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_post_type() {
		$post_type = get_query_var( 'post_type' );

		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		return $post_type;
	}

	/**
	 * Retrieve archive settings fields and data.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type name.
	 * @return array
	 */
	public function get_settings_fields( $post_type ) {
		/**
		 * Enable and filter post type archive settings.
		 *
		 * @since 1.9.0
		 *
		 * @param array $settings {
		 *     Settings to enable for the archive.
		 *
		 *     @type array $columns {
		 *         Archive column settings.
		 *
		 *         @type int $default The default number of columns to show. Defaults to 4 if enabled.
		 *         @type array $choices An array of possible values.
		 *     }
		 *     @type bool $posts_per_archive_page Whether to enable the setting
		 *                                        for modifying the number of
		 *                                        posts to show on the post
		 *                                        type's archive.
		 * }
		 */
		return apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );
	}

	/**
	 * Retrieve the post type for the current archive request.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_current_archive_post_type() {
		return $this->current_archive_post_type;
	}

	/**
	 * Set the post type for the current archive request.
	 *
	 * This can be used to set the post type for archive requests that aren't
	 * post type archives. For example, to have a term archive use the same
	 * settings as a post type archive, set the post type with this method in
	 * 'pre_get_posts'.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type name.
	 */
	public function set_current_archive_post_type( $post_type ) {
		$this->current_archive_post_type = $post_type;
	}

	/**
	 * Determine if a post ID is for an archive post.
	 *
	 * @since 1.9.0
	 *
	 * @param int $archive_id Post ID.
	 * @return string|bool Post type name if true, otherwise false.
	 */
	public function is_archive_id( $archive_id ) {
		$archives = $this->get_archive_ids();
		return array_search( $archive_id, $archives );
	}

	/**
	 * Whether the current query has a corresponding archive post.
	 *
	 * @since 1.9.0
	 *
	 * @param array|string $post_types Optional. A post type name or array of
	 *                                 post type names. Defaults to all archives
	 *                                 registered via AudioTheme_PostType_Archive::add_post_type_archive().
	 * @return bool
	 */
	public function is_post_type_archive( $post_types = array() ) {
		if ( empty( $post_types ) ) {
			$post_types = array_keys( $this->get_archive_ids() );
		}

		return is_post_type_archive( $post_types );
	}

	/**
	 * Add submenu items for archives under the post type menu item.
	 *
	 * Ensures the user has the capability to edit pages in general as well as
	 * the individual page before displaying the submenu item.
	 *
	 * @since 1.9.0
	 */
	public function admin_menu() {
		$archives = $this->get_archive_ids();

		if ( empty( $archives ) ) {
			return;
		}

		// Verify the user can edit audiotheme_archive posts.
		$archive_type_object = get_post_type_object( 'audiotheme_archive' );
		if ( ! current_user_can( $archive_type_object->cap->edit_posts ) ) {
			return;
		}

		foreach ( $archives as $post_type => $archive_id ) {
			// Verify the user can edit the particular audiotheme_archive post in question.
			if ( ! current_user_can( $archive_type_object->cap->edit_post, $archive_id ) ) {
				continue;
			}

			$parent_slug = 'edit.php?post_type=' . $post_type;
			if ( isset( $this->archives[ $post_type ]['admin_menu_parent'] ) ) {
				$parent_slug = $this->archives[ $post_type ]['admin_menu_parent'];
			}

			// Add the submenu item.
			add_submenu_page(
				$parent_slug,
				$archive_type_object->labels->singular_name,
				$archive_type_object->labels->singular_name,
				$archive_type_object->cap->edit_posts,
				add_query_arg( array( 'post' => $archive_id, 'action' => 'edit' ), 'post.php' ),
				null
			);
		}
	}

	/**
	 * Highlight the corresponding top level and submenu items when editing an
	 * archive post.
	 *
	 * @since 1.9.0
	 *
	 * @param string $parent_file A parent file identifier.
	 * @return string
	 */
	public function parent_file( $parent_file ) {
		global $post, $submenu_file;

		if ( $post && 'audiotheme_archive' === get_current_screen()->id && ( $post_type = $this->is_archive_id( $post->ID ) ) ) {
			$parent_file  = 'edit.php?post_type=' . $post_type;
			$submenu_file = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), 'post.php' );

			if ( isset( $this->archives[ $post_type ]['admin_menu_parent'] ) ) {
				$parent_file = $this->archives[ $post_type ]['admin_menu_parent'];
			}
		}

		return $parent_file;
	}

	/**
	 * Sanitize archive columns setting.
	 *
	 * The allowed columns value may be different between themes, so make sure
	 * it exists in the settings defined by the theme, otherwise, return the
	 * theme default.
	 *
	 * @since 1.9.0
	 *
	 * @param mixed  $value Existing meta value.
	 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
	 * @param bool   $single Optional. Whether to return a single value.
	 * @param mixed  $default Optional. A default value to return if the requested meta doesn't exist.
	 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function sanitize_columns_setting( $value, $key, $single, $default, $post_type ) {
		if ( 'columns' !== $key || $value === $default ) {
			return $value;
		}

		$fields = $this->get_settings_fields( $post_type );
		if ( ! empty( $fields['columns']['choices'] ) && ! in_array( $value, $fields['columns']['choices'] ) ) {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Update a post type's rewrite base option.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type slug.
	 * @param int    $archive_id Archive post ID.
	 */
	public function update_post_type_rewrite_base( $post_type, $archive_id ) {
		$archive = get_post( $archive_id );
		update_option( $post_type . '_rewrite_base', $archive->post_name );
	}

	/**
	 * Retrieve a post type's archive slug.
	 *
	 * Checks the 'has_archive' and 'with_front' args in order to build the slug.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type name.
	 * @return string Archive slug.
	 */
	protected function get_post_type_archive_slug( $post_type ) {
		global $wp_rewrite;

		$post_type_object = get_post_type_object( $post_type );

		$slug = $post_type_object->name;
		if ( false !== $post_type_object->rewrite ) {
			$slug = $post_type_object->rewrite['slug'];
		}

		if ( $post_type_object->has_archive ) {
			$slug = $post_type_object->has_archive;
			if ( true === $post_type_object->has_archive ) {
				$post_type_object->rewrite['slug'];
			}

			if ( $post_type_object->rewrite['with_front'] ) {
				$slug = substr( $wp_rewrite->front, 1 ) . $slug;
			} else {
				$slug = $wp_rewrite->root . $slug;
			}
		}

		return $slug;
	}

	/**
	 * Create an archive post for a post type if one doesn't exist.
	 *
	 * The post type's plural label is used for the post title and the defined
	 * rewrite slug is used for the postname.
	 *
	 * @since 1.9.0
	 *
	 * @param string $post_type Post type name.
	 * @return int Post ID.
	 */
	protected function maybe_insert_archive_post( $post_type ) {
		$archive_id = $this->get_archive_id( $post_type );
		if ( $archive_id ) {
			return $archive_id;
		}

		// Search for an inactive archive before creating a new post.
		$inactive_posts = get_posts( array(
			'post_type'  => 'audiotheme_archive',
			'meta_key'   => 'archive_for_post_type',
			'meta_value' => $post_type,
			'fields'     => 'ids',
		) );

		if ( ! empty( $inactive_posts ) ) {
			return $inactive_posts[0];
		}

		// Search the inactive option before creating a new page.
		// The 'audiotheme_archives_inactive' option should be empty when
		// upgrading to 1.9.0. This is here for legacy purposes.
		$inactive = get_option( 'audiotheme_archives_inactive' );
		if ( $inactive && isset( $inactive[ $post_type ] ) && get_post( $inactive[ $post_type ] ) ) {
			return $inactive[ $post_type ];
		}

		// Otherwise, create a new archive post.
		$post_type_object = get_post_type_object( $post_type );

		$post_id = wp_insert_post( array(
			'post_title'  => $post_type_object->labels->name,
			'post_name'   => $this->get_post_type_archive_slug( $post_type ),
			'post_type'   => 'audiotheme_archive',
			'post_status' => 'publish',
		) );

		if ( $post_id ) {
			update_post_meta( $post_id, 'archive_for_post_type', $post_type );
		}

		return $post_id;
	}
}
