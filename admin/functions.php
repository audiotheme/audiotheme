<?php
/**
 * Generic utility functions for us in the admin.
 *
 * @package AudioTheme\Administration
 */

/**
 * Print a taxonomy checkbox list.
 *
 * @since 1.7.0
 *
 * @param WP_Post $post Post object.
 * @param array   $metabox Additional meta box arguments.
 */
function audiotheme_taxonomy_checkbox_list_meta_box( $post, $metabox ) {
	$taxonomy        = $metabox['args']['taxonomy'];
	$taxonomy_object = get_taxonomy( $taxonomy );

	$selected     = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'all' ) );
	$selected_ids = wp_list_pluck( $selected, 'term_id' );
	$selected     = empty( $selected ) || empty( $selected_ids ) ? array() : array_combine( $selected_ids, wp_list_pluck( $selected, 'name' ) );
	$terms        = get_terms( $taxonomy, array( 'fields' => 'id=>name', 'hide_empty' => false, 'exclude' => $selected_ids ) );
	$terms        = $selected + $terms;

	$button_text  = empty( $metabox['args']['button_text'] ) ? __( 'Add', 'audiotheme' ) : $metabox['args']['button_text'];

	wp_nonce_field( 'save-post-terms_' . $post->ID, $taxonomy . '_nonce' );
	include( AUDIOTHEME_DIR . 'admin/views/meta-box-taxonomy-checkbox-list.php' );
}

/**
 * Insert a menu item relative to an existing item.
 *
 * @since 1.0.0
 *
 * @param array  $item Menu item.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after).
 */
function audiotheme_menu_insert_item( $item, $relative_slug, $position = 'after' ) {
	global $menu;

	$relative_key = audiotheme_menu_get_item_key( $relative_slug );
	$before = ( 'before' === $position ) ? $relative_key : $relative_key + 1;

	array_splice( $menu, $before, 0, array( $item ) );
}

/**
 * Move an existing menu item relative to another item.
 *
 * @since 1.0.0
 *
 * @param string $move_slug Slug of item to move.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after).
 */
function audiotheme_menu_move_item( $move_slug, $relative_slug, $position = 'after' ) {
	global $menu;

	$move_key = audiotheme_menu_get_item_key( $move_slug );
	if ( $move_key ) {
		$item = $menu[ $move_key ];
		unset( $menu[ $move_key ] );

		audiotheme_menu_insert_item( $item, $relative_slug, $position );
	}
}

/**
 * Retrieve the key of a menu item.
 *
 * @since 1.0.0
 *
 * @param array $menu_slug Menu item slug.
 * @return int|bool Menu item key or false if it couldn't be found.
 */
function audiotheme_menu_get_item_key( $menu_slug ) {
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
 * @since 1.0.0
 *
 * @param string $move_slug Slug of the item to move.
 * @param string $after_slug Slug of the item to move after.
 * @param string $menu_slug Top-level menu item.
 */
function audiotheme_submenu_move_after( $move_slug, $after_slug, $menu_slug ) {
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

/**
 * Add AudioTheme themes to a site option so they can be checked for updates
 * when in multsite mode.
 *
 * @since 1.3.0
 *
 * @param string $theme Theme slug.
 * @param array  $api_args Optional. Arguments to send to the remote API.
 */
function audiotheme_update_themes_list( $theme, $api_args = array() ) {
	if ( ! is_multisite() ) {
		return;
	}

	$themes = (array) get_site_option( 'audiotheme_themes' );

	if ( ! array_key_exists( $theme, $themes ) || $themes[ $theme ] !== $api_args ) {
		$themes[ $theme ] = wp_parse_args( $api_args, array( 'slug' => $theme ) );
		update_site_option( 'audiotheme_themes', $themes );
	}
}
