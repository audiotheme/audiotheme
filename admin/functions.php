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
