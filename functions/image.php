<?php
/**
 * Control images within Genesis.
 *
 * @package Genesis
 * @todo Document this file
 */

/**
 * Pulls an attachment ID from a post, if one exists
 *
 * @since 0.1
 */
function genesis_get_image_id($num = 0) {
	global $post;

	$image_ids = array_keys(
		get_children(
			array(
				'post_parent' => $post->ID,
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'orderby' => 'menu_order',
				'order' => 'ASC'
			)
		)
	);

	if ( isset($image_ids[$num]) )
		return $image_ids[$num];

	return false;
}

/**
 * Pulls an image from the media gallery and returns it
 *
 * @since 0.1
 */
// pulls an image URL from the media gallery
function genesis_get_image($args = array()) {
	global $post;

	$defaults = array(
		'format' => 'html',
		'size' => 'full',
		'num' => 0,
		'attr' => ''
	);
	$defaults = apply_filters('genesis_get_image_default_args', $defaults);

	$args = wp_parse_args($args, $defaults);

	// Allow child theme to short-circuit this function
	$pre = apply_filters('genesis_pre_get_image', false, $args, $post);
	if ( false !== $pre ) return $pre;

	// check for post image (native WP)
	if ( has_post_thumbnail() && ($args['num'] === 0) ) {
		$id = get_post_thumbnail_id();
		$html = wp_get_attachment_image($id, $args['size'], false, $args['attr']);
		list($url) = wp_get_attachment_image_src($id, $args['size'], false, $args['attr']);
	}
	// else pull the first image attachment
	else {
		$id = genesis_get_image_id($args['num']);
		$html = wp_get_attachment_image($id, $args['size'], false, $args['attr']);
		list($url) = wp_get_attachment_image_src($id, $args['size'], false, $args['attr']);
	}

	// source path, relative to the root
	$src = str_replace( home_url(), '', $url );

	// determine output
	if ( strtolower($args['format']) == 'html' )
		$output = $html;
	elseif ( strtolower($args['format']) == 'url' )
		$output = $url;
	else
		$output = $src;

	// return FALSE if $url is blank
	if ( empty($url) ) $output = FALSE;

	// return FALSE if $src is invalid (file doesn't exist)
	//if ( !file_exists(ABSPATH . $src) ) $output = FALSE;

	// return data, filtered
	return apply_filters('genesis_get_image', $output, $args, $id, $html, $url, $src);
}
/**
 * Pulls an image from media gallery
 * and echos it
 *
 * @since 0.1
 */
function genesis_image($args = array()) {
	$image = genesis_get_image($args);

	if ( $image )
		echo $image;
	else
		return FALSE;
}

add_filter('wp_get_attachment_image_attributes', 'genesis_filter_attachment_image_attributes', 10, 2);
/**
 * Filters the attributes array in the wp_get_attachment_image function
 * For some reason, the wp_get_attachment_image function uses the caption
 * field value as the alt text, not the Alternate Text field value. Strange.
 *
 * @since 0.1.8
 */
function genesis_filter_attachment_image_attributes($attr, $attachment) {
	$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);

	if ( $alt ) $attr['alt'] = esc_attr( $alt );

	return $attr;
}

/**
 * Pulls additional image sizes
 *
 * @since 0.1.7
 * @return array
 */
function genesis_get_additional_image_sizes() {
	global $_wp_additional_image_sizes;

	if ( $_wp_additional_image_sizes )
		return $_wp_additional_image_sizes;

	return array();
}

/**
 * Pulls all image sizes
 *
 * @since 1.0.2
 * @return array
 */
function genesis_get_image_sizes() {
	$builtin_sizes = array(
		'large'		=> array(
			'width' => get_option('large_size_w'),
			'height' => get_option('large_size_h')
		),
		'medium'	=> array(
			'width' => get_option('medium_size_w'),
			'height' => get_option('medium_size_h')
		),
		'thumbnail'	=> array(
			'width' => get_option('thumbnail_size_w'),
			'height' => get_option('thumbnail_size_h')
		)
	);

	$additional_sizes = genesis_get_additional_image_sizes();

	return array_merge( $builtin_sizes, $additional_sizes );
}