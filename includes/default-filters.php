<?php
if ( ! is_admin() )
	add_filter( 'wp_get_nav_menu_items', 'audiotheme_nav_menu_classes', 1, 3 );
add_filter( 'nav_menu_css_class', 'audiotheme_nav_menu_name_class', 1, 2 );
add_filter( 'get_pages', 'audiotheme_page_list' );
add_filter( 'page_css_class', 'audiotheme_page_list_classes', 10, 2 );
add_filter( 'dynamic_sidebar_params', 'audiotheme_widget_count_class' );
add_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10, 4 );


/**
 * Nav Menu Item Classes
 *
 * Adds class hooks to various nav menu items since child pseudo selectors
 * aren't supported in all browsers.
 *
 * @since 1.0
 */
function audiotheme_nav_menu_classes( $items, $menu, $args ) {
	$classes = array();
	$first_top = -1;
	foreach ( $items as $key => $item ) {
		if ( 0 == $item->menu_item_parent ) {
			$first_top = ( -1 == $first_top ) ? $key : $first_top;
			$last_top = $key;
		} else {
			if ( ! isset( $classes['first-child-items'][ $item->menu_item_parent ] ) ) {
				$classes['first-child-items'][ $item->menu_item_parent ] = $key;
				$items[ $key ]->classes[] = 'first-child-item';
			}
			$classes['last-child-items'][ $item->menu_item_parent ] = $key;
		}
	}
	
	$items[ $first_top ]->classes[] = 'first-item';
	$items[ $last_top ]->classes[] = 'last-item';
	
	if ( isset( $classes['last-child-items'] ) ) {
		foreach ( $classes['last-child-items'] as $item_id ) {
			$items[ $item_id ]->classes[] = 'last-child-item';
		}
	}
	
	return $items;
}

/**
 * Nav Menu Item Title Class
 *
 * Adds a class to a nav menu item generated from the item's title.
 * Allows for targeting individual items by name.
 *
 * @since 1.0
 */
function audiotheme_nav_menu_name_class( $classes, $item ) {
	$new_classes[] = sanitize_html_class( 'menu-item-' . sanitize_title_with_dashes( $item->title ) );
	
	return array_merge( $classes, $new_classes );
}

/**
 * Page List Class Helper
 *
 * Stores information about the order of pages in a global variable to be accessed
 * by audiotheme_page_list_classes().
 *
 * @since 1.0
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
 * Page List Item Classes
 *
 * Adds a classes to items in wp_list_pages(), which serves as a fallback
 * when nav menus haven't been assigned. Mimics the classes added to nav menus
 * for consistent behavior.
 *
 * @since 1.0
 */
function audiotheme_page_list_classes( $class, $page ) {
	global $audiotheme_page_depth_classes;
	
	$depth = $audiotheme_page_depth_classes;
	
	if ( 0 === $page->post_parent ) { $class[] = 'top-level-item'; }	
	if ( isset( $depth['first-top-level-page'] ) && $page->ID == $depth['first-top-level-page'] ) { $class[] = 'first-item'; }
	if ( isset( $depth['last-top-level-page'] ) && $page->ID == $depth['last-top-level-page'] ) { $class[] = 'last-item'; }
	if ( isset( $depth['first-child-pages'] ) && in_array( $page->ID, $depth['first-child-pages'] ) ) { $class[] = 'first-child-item'; }
	if ( isset( $depth['last-child-pages'] ) && in_array( $page->ID, $depth['last-child-pages'] ) ) { $class[] = 'last-child-item'; }
	
	return $class;
}

/**
 * Nav Menu Item Title Class
 *
 * Adds a class to widgets containing it's position in the sidebar it belongs to
 * and adds a special class to the last widget.
 *
 * @since 1.0
 */
function audiotheme_widget_count_class( $params ) {
	$class = '';
	$sidebar_widgets = wp_get_sidebars_widgets();
	$order = array_search( $params[0]['widget_id'], $sidebar_widgets[ $params[0]['id'] ] ) + 1;
	if ( $order == count( $sidebar_widgets[ $params[0]['id'] ] ) ) {
		$class = ' widget-last';
	}
	
	$params[0]['before_widget'] = preg_replace( '/class="(.*?)"/i', 'class="$1 widget-' . $order . $class . '"', $params[0]['before_widget'] );
	
	return $params;
}
?>