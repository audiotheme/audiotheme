<?php
/**
 * Define default filters for modifying WordPress behavior.
 *
 * @package AudioTheme
 */

/**
 * Filter record type archive titles.
 *
 * @since 1.7.0
 *
 * @param string $title Archive title.
 * @return string
 */
function audiotheme_archives_taxonomy_title( $title ) {
	if ( is_tax() ) {
		$title = get_queried_object()->name;
	}

	return $title;
}

/**
 * Add helpful nav menu item classes.
 *
 * Adds class hooks to various nav menu items since child pseudo selectors
 * aren't supported in all browsers.
 *
 * @since 1.0.0
 *
 * @param array $items List of menu items.
 * @param array $args Menu display args.
 * @return array
 */
function audiotheme_nav_menu_classes( $items, $args ) {
	global $wp;

	$classes = array();
	$first_top = -1;

	$current_url  = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
	$blog_page_id = get_option( 'page_for_posts' );
	$is_blog_post = is_singular( 'post' );

	$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
	$post_type_archive_id    = get_audiotheme_post_type_archive( get_post_type() );
	$post_type_archive_link  = get_post_type_archive_link( get_post_type() );

	foreach ( $items as $key => $item ) {
		if ( empty( $item->menu_item_parent ) ) {
			$first_top = ( -1 === $first_top ) ? $key : $first_top;
			$last_top  = $key;
		} else {
			if ( ! isset( $classes['first-child-items'][ $item->menu_item_parent ] ) ) {
				$classes['first-child-items'][ $item->menu_item_parent ] = $key;
				$items[ $key ]->classes[] = 'first-child-item';
			}
			$classes['last-child-items'][ $item->menu_item_parent ] = $key;
		}

		if ( ! is_404() && ! is_search() ) {
			if (
				'audiotheme_archive' === $item->object &&
				$post_type_archive_id === (int) $item->object_id &&
				trailingslashit( $item->url ) === $current_url
			) {
				$items[ $key ]->classes[] = 'current-menu-item';
			}

			if ( $is_blog_post && $blog_page_id === $item->object_id ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}

			// Add 'current-menu-parent' class to CPT archive links when viewing a singular template.
			if ( $is_audiotheme_post_type && $post_type_archive_link === $item->url ) {
				$items[ $key ]->classes[] = 'current-menu-parent';
			}
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
 * Set up AudioTheme templates when they're loaded.
 *
 * Limits default scripts and styles to load only for AudioTheme templates.
 *
 * @since 1.2.0
 */
function audiotheme_template_setup( $template ) {
	if ( is_audiotheme_default_template( $template ) ) {
		add_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );
	}
}

/**
 * Enqueue default frontend scripts and styles.
 *
 * Themes can remove default styles and scripts by removing this hook:
 * <code>remove_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );</code>
 *
 * @since 1.2.0
 */
function audiotheme_enqueue_scripts() {
	wp_enqueue_script( 'audiotheme' );
	wp_enqueue_style( 'audiotheme' );
}

/**
 * Add wrapper open tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_before_main_content() {
	echo '<div class="audiotheme">';
}

/**
 * Add wrapper close tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_after_main_content() {
	echo '</div>';
}
