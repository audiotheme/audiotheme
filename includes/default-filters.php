<?php
/**
 * Define default filters for modifying WordPress behavior.
 *
 * @package AudioTheme_Framework
 */

/**
 * Filter audiotheme_archive permalinks to match the corresponding post type's
 * archive.
 *
 * @since 1.0.0
 *
 * @param string $permalink Default permalink.
 * @param WP_Post $post Post object.
 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
 * @return string Permalink.
 */
function audiotheme_archives_post_type_link( $permalink, $post, $leavename ) {
	global $wp_rewrite;

	if ( 'audiotheme_archive' === $post->post_type  ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		$post_type_object = get_post_type_object( $post_type );

		if ( get_option( 'permalink_structure' ) ) {
			$front = '/';
			if ( isset( $post_type_object->rewrite ) && $post_type_object->rewrite['with_front'] ) {
				$front = $wp_rewrite->front;
			}

			if ( $leavename ) {
				$permalink = home_url( $front . '%postname%/' );
			} else {
				$permalink = home_url( $front . $post->post_name . '/' );
			}
		} else {
			$permalink = add_query_arg( 'post_type', $post_type, home_url( '/' ) );
		}
	}

	return $permalink;
}

/**
 * Filter post type archive permalinks.
 *
 * @since 1.0.0
 *
 * @param string $link Post type archive link.
 * @param string $post_type Post type name.
 * @return string
 */
function audiotheme_archives_post_type_archive_link( $link, $post_type ) {
	if ( $archive_id = get_audiotheme_post_type_archive( $post_type ) ) {
		$link = get_permalink( $archive_id );
	}

	return $link;
}

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
 * Filter the default post_type_archive_title() template tag and replace with
 * custom archive title.
 *
 * @since 1.0.0
 *
 * @param string $label Post type archive title.
 * @return string
 */
function audiotheme_archives_post_type_archive_title( $title ) {
	$post_type_object = get_queried_object();

	if ( $page_id = get_audiotheme_post_type_archive( $post_type_object->name ) ) {
		$page = get_post( $page_id );
		$title = $page->post_title;
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

	$current_url = trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );
	$blog_page_id = get_option( 'page_for_posts' );
	$is_blog_post = is_singular( 'post' );

	$is_audiotheme_post_type = is_singular( array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' ) );
	$post_type_archive_id = get_audiotheme_post_type_archive( get_post_type() );
	$post_type_archive_link = get_post_type_archive_link( get_post_type() );

	foreach ( $items as $key => $item ) {
		if ( empty( $item->menu_item_parent ) ) {
			$first_top = ( -1 === $first_top ) ? $key : $first_top;
			$last_top = $key;
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
				$post_type_archive_id === $item->object_id &&
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
