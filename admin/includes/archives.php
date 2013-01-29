<?php
/**
 * Post type archive pages.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link
 * (they stay updated!).
 *
 * @todo Should prevent multiple post types from having the same archive page.
 * @todo Disable the page parent dropdown in quick edit and bulk edit?
 * @todo Add a notification to archive pages alerting the user the page is an archive page.
 * @todo Add some sort of designator in the list of pages that a page is set as an archive page.
 * @todo Add a link to the archive page below the post type's admin menu item.
 * @todo Need to update nav menu classes to reflect when an archive page is the current item, parent, or ancestor.
 */

/**
 * Initialize admin-related archive page functionality.
 *
 * @since 1.0.0
 */
function audiotheme_archive_pages_init() {
	add_action( 'pre_update_option_audiotheme_archive_pages', 'audiotheme_archive_pages_pre_option_update' );
	add_action( 'post_updated', 'audiotheme_archive_update_base_on_page_save', 10, 3 );

	add_action( 'add_meta_boxes_page', 'audiotheme_archive_remove_page_attributes_meta_box' );
}

/**
 * Update post type rewrite base options when the "page for posts" options are updated.
 *
 * Loops through the options and determines if "page for posts" values have changed. If so, the rewrite base options are updated and the rewrite rules are flushed.
 *
 * @since 1.0.0
 *
 * @param array $value Associative array with post type key and page id as the value.
 * @return array
 */
function audiotheme_archive_pages_pre_option_update( $value ) {
	$flush_rewrite_rules = false;

	foreach ( $value as $post_type => $page_id ) {
		$current_base = get_option( $post_type . '_rewrite_base' );
		$new_base = ( empty( $page_id ) ) ? '' : get_page_uri( $page_id );

		if ( $current_base != $new_base ) {
			$flush_rewrite_rules = true;
			update_option( $post_type . '_rewrite_base', $new_base );
		}
	}

	if ( $flush_rewrite_rules ) {
		flush_rewrite_rules();
	}

	return $value;
}

/**
 * Update post type rewrite base when an archive page slug is changed.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID>
 * @param WP_Post $post_after Updated post object.
 * @param WP_Post $post_before Post object before udpate.
 */
function audiotheme_archive_update_base_on_page_save( $post_id, $post_after, $post_before ) {
	if ( $post_after->post_name == $post_before->post_name ) {
		return;
	}

	$archive_pages = get_audiotheme_archive_page_ids();
	if ( in_array( $post_id, $archive_pages ) ) {
		// Fall back to the default base if the page has a parent.
		$slug = ( $post_after->post_parent ) ? '' : $post_after->post_name;
		$post_type = get_audiotheme_archive_post_type_by_page_id( $post_id );

		if ( $post_type ) {
			update_option( $post_type . '_rewrite_base', $slug );
			flush_rewrite_rules();
		}
	}
}

/**
 * Remove the page attributes meta box on archive page screens.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post A page object.
 */
function audiotheme_archive_remove_page_attributes_meta_box( $post ) {
	if ( get_audiotheme_archive_post_type_by_page_id( $post->ID ) ) {
		remove_meta_box( 'pageparentdiv', 'page', 'side' );
	}
}
