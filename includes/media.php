<?php
/**
 * AudioTheme API for working with media and defines filters for modifying
 * WordPress behavior related to media.
 *
 * @package AudioTheme_Framework
 */

/**
 * Register custom oEmbed providers.
 *
 * Post content is filtered on display, so limited services should be
 * supported by default.
 *
 * @since 1.0.0
 * @link http://core.trac.wordpress.org/ticket/15734
 * @link http://core.trac.wordpress.org/ticket/21635#comment:8
 */
function audiotheme_add_default_oembed_providers() {
	#wp_oembed_add_provider( 'http://snd.sc/*', 'http://soundcloud.com/oembed' );
	#wp_oembed_add_provider( 'http://www.rdio.com/#artist/*album/*', 'http://www.rdio.com/api/oembed/' );
	#wp_oembed_add_provider( 'http://rd.io/*', 'http://www.rdio.com/api/oembed/' );
}

/**
 * Add an HTML wrapper to certain videos retrieved via oEmbed.
 *
 * The wrapper is useful as a styling hook and for responsive designs. Also
 * attempts to add the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 *
 * @param string $html HTML.
 * @param string $url oEmbed URL.
 * @param array $attr Embed attributes.
 * @param int $post_id Post ID.
 * @return string Embed HTML with wrapper.
 */
function audiotheme_oembed_html( $html, $url = null, $attr = null, $post_id = null ) {
	$wrapped = '<div class="audiotheme-embed">' . $html . '</div>';

    if ( empty( $url ) && 'video_embed_html' == current_filter() ) { // Jetpack
        $html = $wrapped;
    } elseif ( ! empty( $url ) ) {
        $players = array( 'youtube', 'youtu.be', 'vimeo', 'dailymotion', 'hulu', 'blip.tv', 'wordpress.tv', 'viddler', 'revision3' );

        foreach ( $players as $player ) {
            if ( false !== strpos( $url, $player ) ) {
                if ( false !== strpos( $url, 'youtube' ) && false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'wmode' ) ) {
                    $html = preg_replace_callback( '|https?://[^"]+|im', '_audiotheme_oembed_youtube_wmode_parameter', $html );
                }

                $html = $wrapped;
                break;
            }
        }
    }

    if ( false !== strpos( $html, '<embed' ) && false === strpos( $html, 'wmode' ) ) {
        $html = str_replace( '</param><embed', '</param><param name="wmode" value="opaque"></param><embed wmode="opaque"', $html );
    }

	return $html;
}

/**
 * Private callback.
 *
 * Adds wmode=transparent query argument to oEmbedded YouTube videos.
 *
 * @since 1.0.0
 * @access private
 *
 * @param array $matches
 * @return string
 */
function _audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}

/**
 * Filter the default gallery shortcode.
 *
 * This filter allows the output of the default gallery shortcode to be
 * customized and adds support for additional functionality, shortcode
 * attributes, and classes for CSS and JavaScript hooks.
 *
 * A lot of the default sanitization is duplicated because WordPress doesn't
 * provide a filter later in the process.
 *
 * @since 1.2.0
 *
 * @param string $output Output string passed from default shortcode.
 * @param array $attr Array of shortcode attributes.
 * @return string Custom gallery output markup.
 */
function audiotheme_post_gallery( $output, $attr ) {
	global $post;

	// Something else is already overriding the gallery. Jetpack?
	if ( ! empty( $output ) ) {
		return $output;
	}

	static $instance = 0;
	$instance ++;

	// Let WordPress handle the output for feed requests.
	if ( is_feed() ) {
		return $output;
	}

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] ) {
			unset( $attr['orderby'] );
		}
	}

	$attr = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'link'       => 'file',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'ids'        => '',
		'include'    => '',
		'exclude'    => ''
	), $attr, 'gallery' );

	$attr['id'] = absint( $attr['id'] );
	if ( 'RAND' == $attr['order'] ) {
		$attr['orderby'] = 'none';
	}

	// Build up an array of arguments to pass to get_posts().
	$args = array(
		'post_parent'    => $attr['id'],
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => $attr['order'],
		'orderby'        => $attr['orderby'],
		'numberposts'    => -1
	);

	if ( ! empty( $attr['ids'] ) ) {
		$attr['include'] = $attr['ids'];

		// 'ids' should be explicitly ordered.
		$args['orderby'] = 'post__in';
	}

	if ( ! empty( $attr['include'] ) ) {
		$args['include'] = $attr['include'];

		// Don't want to restrict images to a parent post if 'include' is set.
		unset( $args['post_parent'] );
	} elseif ( ! empty( $attr['exclude'] ) ) {
		$args['exclude'] = $attr['exclude'];
	}

	$attachments = get_posts( $args );
	if ( empty( $attachments ) ) {
		return '';
	}

	// Sanitize tags and values.
	$attr['captiontag'] = tag_escape( $attr['captiontag'] );
	$attr['icontag'] = tag_escape( $attr['icontag'] );
	$attr['itemtag'] = tag_escape( $attr['itemtag'] );

	$valid_tags = wp_kses_allowed_html( 'post' );
	$attr['captiontag'] = isset( $valid_tags[ $attr['captiontag'] ] ) ? $attr['captiontag'] : 'dd';
	$attr['icontag'] = isset( $valid_tags[ $attr['icontag'] ] ) ? $attr['icontag'] : 'dl';
	$attr['itemtag'] = isset( $valid_tags[ $attr['itemtag'] ] ) ? $attr['itemtag'] : 'dl';
	$attr['columns'] = ( absint( $attr['columns'] ) ) ? absint( $attr['columns'] ) : 1;

	// Add gallery wrapper classes to $attr variable so they can be passed to the filter.
	$attr['gallery_classes'] = array(
		'gallery',
		'galleryid-' . $attr['id'],
		'gallery-columns-' . $attr['columns'],
		'gallery-size-' . $attr['size'],
		'gallery-link-' . $attr['link'],
		( is_rtl() ) ? 'gallery-rtl' : 'gallery-ltr',
	);
	$attr['gallery_classes'] = apply_filters( 'audiotheme_post_gallery_classes', $attr['gallery_classes'], $attr, $instance );

	extract( $attr );

	// id attribute is a combination of post ID and instance to ensure uniqueness.
	$wrapper = sprintf( "\n" . '<div id="gallery-%d-%d" class="%s">', $post->ID, $instance, join( ' ', array_map( 'sanitize_html_class', $gallery_classes ) ) );

	// Hooks should append custom output to the $wrapper arg if necessary and be sure to close the div.
	$output = apply_filters( 'audiotheme_post_gallery_output', $wrapper, $attachments, $attr, $instance );

	// Skip output generation if a hook modified the output.
	if ( empty( $output ) || $wrapper == $output ) {
		// If $output is empty for some reason, restart the output with the default wrapper.
		if ( empty( $output ) ) {
			$output = $wrapper;
		}

		foreach ( $attachments as $i => $attachment ) {
			// More 'link' options have been added.
			if ( 'none' == $link ) {
				// Don't link the thumbnails in the gallery.
				$href = '';
			} elseif ( 'file' == $link ) {
				// Link directly to the attachment.
				$href = wp_get_attachment_url( $attachment->ID );
			} elseif ( 'link' == $link ) {
				// Use a custom meta field associated with the image for the link.
				$href = get_post_meta( $attachment->ID, '_audiotheme_attachment_url', true );
			} else {
				// Link to the attachment's permalink page.
				$href = get_permalink( $attachment->ID );
			}

			$image_meta = wp_get_attachment_metadata( $attachment->ID );

			$orientation = '';
			if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
			}

			$classes = array( 'gallery-item', 'gallery-item-' . ( $i + 1 ) );
			$classes = array_merge( $classes, audiotheme_nth_child_classes( array(
				'base'    => 'gallery-item',
				'current' => ( $i + 1 ),
				'max'     => $columns,
			) ) );

			$output .= "\n\t\t" . '<' . $itemtag . ' class="' . join( ' ', $classes ) . '">';

				$output .= '<' . $icontag . ' class="gallery-icon ' . $orientation . '">';

					$image  = ( $href ) ? '<a href="' . esc_url( $href ) . '">' : '';
						$image .= wp_get_attachment_image( $attachment->ID, $size, false );
					$image .= ( $href ) ? '</a>' : '';

					// Some plugins use this filter, so mimic it as best we can.
					if ( 'none' !== $link ) {
						$permalink = in_array( $link, array( 'file', 'link' ) ) ? false: true;
						$icon = $text = false;
						$image = apply_filters( 'wp_get_attachment_link', $image, $attachment->ID, $size, $permalink, $icon, $text );
					}

					$output .= $image;
				$output .= '</' . $icontag . '>';

				if ( $captiontag && trim( $attachment->post_excerpt ) ) {
					$output .= '<' . $captiontag . ' class="wp-caption-text gallery-caption">';
						$output .= wptexturize( $attachment->post_excerpt );
					$output .= '</' . $captiontag . '>';
				}

			$output .= '</' . $itemtag .'>';
		}


		$output .= "\n</div>\n"; // Close the default gallery wrapper.
	}

	return $output;
}

/**
 * Add audio metadata to attachment response objects.
 *
 * @since x.x.x
 *
 * @param array $response Attachment data to send as JSON.
 * @param WP_Post $attachment Attachment object.
 * @param array $meta Attachment meta.
 * @return array
 */
function audiotheme_wp_prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
	if ( 'audio' !== $response['type'] ) {
		return $response;
	}

	if ( empty( $meta ) && ! get_post_meta( $attachment->ID, '_audiotheme_metadata_cached', true ) ) {
		// Read and cache the audio metadata.
		$file = get_attached_file( $attachment->ID );
		wp_update_attachment_metadata( $attachment->ID, wp_generate_attachment_metadata( $attachment->ID, $file ) );
		$meta = wp_get_attachment_metadata( $attachment->ID );
		update_post_meta( $attachment->ID, '_audiotheme_metadata_cached', true );
	}

	$response['meta'] = $meta;

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'audiotheme_wp_prepare_audio_attachment_for_js', 10, 3 );
