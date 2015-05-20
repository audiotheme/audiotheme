<?php
/**
 *
 */

/**
 * Get record type strings.
 *
 * List of default record types to better define the record, much like a post
 * format.
 *
 * @since 1.0.0
 * @deprecated 1.7.0
 *
 * @return array List of record types.
 */
function get_audiotheme_record_type_strings() {
	$strings = array(
		'record-type-album'  => _x( 'Album',  'Record type', 'audiotheme' ),
		'record-type-single' => _x( 'Single', 'Record type', 'audiotheme' ),
	);

	/**
	 * Filter the list of available of record types.
	 *
	 * Terms will be registered automatically for new record types. Keys must
	 * be prefixed with 'record-type'.
	 *
	 * @since 1.5.0
	 *
	 * @param array strings List of record types. Keys must be prefixed with 'record-type-'.
	 */
	return apply_filters( 'audiotheme_record_type_strings', $strings );
}

/**
 * Get record type slugs.
 *
 * Gets an array of available record type slugs from record type strings.
 *
 * @since 1.0.0
 * @deprecated 1.7.0
 *
 * @return array List of record type slugs.
 */
function get_audiotheme_record_type_slugs() {
	$slugs = array_keys( get_audiotheme_record_type_strings() );
	return $slugs;
}

/**
 * Get record type string.
 *
 * Sets default value of record type if option is not set.
 *
 * @since 1.0.0
 * @deprecated 1.7.0
 *
 * @param string Record type slug.
 * @return string Record type label.
 */
function get_audiotheme_record_type_string( $slug ) {
	if ( false !== strpos( $slug, 'record-type-' ) ) {
		$strings = get_audiotheme_record_type_strings();
		if ( isset( $strings[ $slug ] ) ) {
			return $strings[ $slug ];
		}
	}

	$term = get_term_by( 'slug', $slug, 'audiotheme_record_type' );
	return $term ? $term->name : _x( 'Album', 'Record type', 'audiotheme' );
}

/**
 * Add widget count classes so they can be targeted based on their position.
 *
 * Adds a class to widgets containing it's position in the sidebar it belongs
 * to and adds a special class to the last widget.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 *
 * @param array $params Wiget registration args.
 * @return array
 */
function audiotheme_widget_count_class( $params ) {
	$class = '';
	$sidebar_widgets = wp_get_sidebars_widgets();
	$order = array_search( $params[0]['widget_id'], $sidebar_widgets[ $params[0]['id'] ] ) + 1;
	if ( $order === count( $sidebar_widgets[ $params[0]['id'] ] ) ) {
		$class = ' widget-last';
	}

	$params[0]['before_widget'] = preg_replace( '/class="(.*?)"/i', 'class="$1 widget-' . $order . $class . '"', $params[0]['before_widget'] );

	return $params;
}

/**
 * Add class to nav menu items based on their title.
 *
 * Adds a class to a nav menu item generated from the item's title, so
 * individual items can be targeted by name.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 *
 * @param array $classes CSS classes.
 * @param object $item Menu item.
 * @return array
 */
function audiotheme_nav_menu_name_class( $classes, $item ) {
	$new_classes[] = sanitize_html_class( 'menu-item-' . sanitize_title_with_dashes( $item->title ) );

	return array_merge( $classes, $new_classes );
}

/**
 * Page list CSS class helper.
 *
 * Stores information about the order of pages in a global variable to be
 * accessed by audiotheme_page_list_classes().
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 * @see audiotheme_page_list_classes()
 *
 * @param array $pages List of pages.
 * @return array
 */
function audiotheme_page_list( $pages ) {
	global $audiotheme_page_depth_classes;

	$classes = array();
	foreach ( $pages as $page ) {
		if ( 0 === $page->post_parent ) {
			if ( ! isset($classes['first-top-level-page'] ) ) {
				$classes['first-top-level-page'] = $page->ID;
			}
			$classes['last-top-level-page'] = $page->ID;
		} else {
			if ( ! isset( $classes['first-child-pages'][ $page->post_parent ] ) ) {
				$classes['first-child-pages'][ $page->post_parent ] = $page->ID;
			}
			$classes['last-child-pages'][ $page->post_parent ] = $page->ID;
		}
	}
	$audiotheme_page_depth_classes = $classes;

	return $pages;
}

/**
 * Add classes to items in a page list.
 *
 * Adds a classes to items in wp_list_pages(), which serves as a fallback
 * when nav menus haven't been assigned. Mimics the classes added to nav menus
 * for consistent behavior.
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 *
 * @param array $classes CSS classes.
 * @param WP_Post $page Page object.
 * @return array
 */
function audiotheme_page_list_classes( $classes, $page ) {
	global $audiotheme_page_depth_classes;

	$depth = $audiotheme_page_depth_classes;

	if ( 0 === $page->post_parent ) { $class[] = 'top-level-item'; }
	if ( isset( $depth['first-top-level-page'] ) && $page->ID === $depth['first-top-level-page'] ) { $classes[] = 'first-item'; }
	if ( isset( $depth['last-top-level-page'] ) && $page->ID === $depth['last-top-level-page'] ) { $classes[] = 'last-item'; }
	if ( isset( $depth['first-child-pages'] ) && in_array( $page->ID, $depth['first-child-pages'] ) ) { $classes[] = 'first-child-item'; }
	if ( isset( $depth['last-child-pages'] ) && in_array( $page->ID, $depth['last-child-pages'] ) ) { $classes[] = 'last-child-item'; }

	return $classes;
}
