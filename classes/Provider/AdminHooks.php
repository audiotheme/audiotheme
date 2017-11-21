<?php
/**
 * Administration hook provider.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Administration hook provider class.
 *
 * @package AudioTheme\Administration
 * @since   2.0.0
 */
class AudioTheme_Provider_AdminHooks extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init',                          array( $this, 'sort_admin_menu' ) );
		add_action( 'admin_body_class',                    array( $this, 'admin_body_classes' ) );
		add_action( 'save_post',                           array( $this, 'update_post_terms' ), 10, 2 );
		add_action( 'manage_pages_custom_column',          array( $this, 'display_list_table_columns' ), 10, 2 );
		add_action( 'manage_posts_custom_column',          array( $this, 'display_list_table_columns' ), 10, 2 );
		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'disable_post_parent_dropdown' ), 10, 2 );

		// Deprecated.
		add_action( 'init', 'audiotheme_settings_init' );
	}

	/**
	 * Sort the admin menu.
	 *
	 * @since 2.0.0
	 */
	public function sort_admin_menu() {
		global $menu;

		if ( is_network_admin() || ! $menu ) {
			return;
		}

		// Re-key the array.
		$menu = array_values( $menu );

		// Move AudioTheme before separator1.
		$this->move_menu_item( 'audiotheme', 'separator1', 'before' );

		// Insert a new separator before the AudioTheme menu item.
		$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
		$this->insert_menu_item( $separator, 'audiotheme', 'before' );

		// Reverse the order and always insert them after the main AudioTheme menu item.
		$this->move_menu_item( 'edit.php?post_type=audiotheme_video', 'audiotheme' );
		$this->move_menu_item( 'edit.php?post_type=audiotheme_record', 'audiotheme' );
		$this->move_menu_item( 'edit.php?post_type=audiotheme_gig', 'audiotheme' );

		$this->move_submenu_item_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
	}

	/**
	 * Add current screen ID as HTML class to the body element.
	 *
	 * @since 2.0.0
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function admin_body_classes( $classes ) {
		global $post;

		$classes .= ' screen-' . sanitize_html_class( get_current_screen()->id );

		if ( 'audiotheme_archive' === get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID ) ) {
			$classes .= ' ' . $post_type . '-archive';
		}

		return implode( ' ', array_unique( explode( ' ', $classes ) ) );
	}

	/**
	 * General custom post type columns.
	 *
	 * This hook is run for all custom columns, so the column name is prefixed to
	 * prevent potential conflicts.
	 *
	 * @since 2.0.0
	 *
	 * @param string $column_name Column identifier.
	 * @param int    $post_id Post ID.
	 */
	public function display_list_table_columns( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'audiotheme_image' :
				printf(
					'<a href="%1$s">%2$s</a>',
					esc_url( get_edit_post_link( $post_id ) ),
					get_the_post_thumbnail( $post_id, array( 60, 60 ) )
				);
				break;
		}
	}

	/**
	 * Save custom taxonomy terms when a post is saved.
	 *
	 * @since 2.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function update_post_terms( $post_id, $post ) {
		$is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision = wp_is_post_revision( $post_id );

		// Bail if the data shouldn't be saved.
		if ( $is_autosave || $is_revision || empty( $_POST['audiotheme_post_terms'] ) ) {
			return;
		}

		foreach ( $_POST['audiotheme_post_terms'] as $taxonomy => $term_ids ) {
			// Don't save if intention can't be verified.
			if ( ! isset( $_POST[ $taxonomy . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $taxonomy . '_nonce' ], 'save-post-terms_' . $post_id ) ) {
				continue;
			}

			$term_ids = array_map( 'absint', $term_ids );
			wp_set_object_terms( $post_id, $term_ids, $taxonomy );
		}
	}

	/**
	 * Disable the post parent dropdown in the Page Attributes meta box for
	 * AudioTheme custom post types.
	 *
	 * The Page Attributes meta box can be enabled by plugins or if a post type
	 * template is added to the theme. We don't support parent/child
	 * relationships for any of the core CPTs, so this prevents the post parent
	 * dropdown from appearing.
	 *
	 * @since 2.2.1
	 *
	 * @param array   $args Dropdown arguments.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function disable_post_parent_dropdown( $args, $post ) {
		if ( 0 === strpos( get_post_type( $post ), 'audiotheme_' ) ) {
			$args['post_type'] = '_' . $post->post_type;
		}

		return $args;
	}

	/**
	 * Insert a menu item relative to an existing item.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $item Menu item.
	 * @param string $relative_slug Slug of existing item.
	 * @param string $position Optional. Defaults to 'after'. (before|after).
	 */
	protected function insert_menu_item( $item, $relative_slug, $position = 'after' ) {
		global $menu;

		$relative_key = $this->get_menu_item_key( $relative_slug );
		$before = ( 'before' === $position ) ? $relative_key : $relative_key + 1;

		array_splice( $menu, $before, 0, array( $item ) );
	}

	/**
	 * Move an existing menu item relative to another item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $move_slug Slug of item to move.
	 * @param string $relative_slug Slug of existing item.
	 * @param string $position Optional. Defaults to 'after'. (before|after).
	 */
	protected function move_menu_item( $move_slug, $relative_slug, $position = 'after' ) {
		global $menu;

		$move_key = $this->get_menu_item_key( $move_slug );
		if ( $move_key ) {
			$item = $menu[ $move_key ];
			unset( $menu[ $move_key ] );

			$this->insert_menu_item( $item, $relative_slug, $position );
		}
	}

	/**
	 * Retrieve the key of a menu item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $menu_slug Menu item slug.
	 * @return int|bool Menu item key or false if it couldn't be found.
	 */
	protected function get_menu_item_key( $menu_slug ) {
		global $menu;

		foreach ( $menu as $key => $item ) {
			if ( $menu_slug === $item[2] ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Move a submenu item after another submenu item under the same top-level item.
	 *
	 * @since 2.0.0
	 *
	 * @param string $move_slug Slug of the item to move.
	 * @param string $after_slug Slug of the item to move after.
	 * @param string $menu_slug Top-level menu item.
	 */
	protected function move_submenu_item_after( $move_slug, $after_slug, $menu_slug ) {
		global $submenu;

		if ( isset( $submenu[ $menu_slug ] ) ) {
			foreach ( $submenu[ $menu_slug ] as $key => $item ) {
				if ( $item[2] === $move_slug ) {
					$move_key = $key;
				} elseif ( $item[2] === $after_slug ) {
					$after_key = $key;
				}
			}

			if ( isset( $move_key ) && isset( $after_key ) ) {
				$move_item = $submenu[ $menu_slug ][ $move_key ];
				unset( $submenu[ $menu_slug ][ $move_key ] );

				// Need to account for the change in the array with the previous unset.
				$new_position = ( $move_key > $after_key ) ? $after_key + 1 : $after_key;

				array_splice( $submenu[ $menu_slug ], $new_position, 0, array( $move_item ) );
			}
		}
	}
}
