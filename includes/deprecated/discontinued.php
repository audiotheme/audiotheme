<?php
/**
 * Discontinued functions.
 *
 * These functions were part of a public API or filtered public output that was
 * relied on by themes. They should not be used and any dependencies should be
 * updated, however they will be maintained longer than deprecated functions
 * until we're sure their removal won't cause unintended issues.
 *
 * @package AudioTheme\Deprecated
 * @since 1.6.0
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
		'record-type-album'  => 'Album',
		'record-type-single' => 'Single',
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
	return $term ? $term->name : 'Album';
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

/**
 * Get the base admin panel URL for adding a venue.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function get_audiotheme_venue_admin_url( $args = '' ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$admin_url = admin_url( 'admin.php?page=audiotheme-venue' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for viewing all venues.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function get_audiotheme_venues_admin_url( $args = '' ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$admin_url = admin_url( 'admin.php?page=audiotheme-venues' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for editing a venue.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function get_audiotheme_venue_edit_link( $admin_url, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0', 'get_edit_post_link()' );

	if ( 'audiotheme_venue' === get_post_type( $post_id ) ) {
		$args = array(
			'action'   => 'edit',
			'venue_id' => $post_id,
		);

		$admin_url = get_audiotheme_venue_admin_url( $args );
	}

	return $admin_url;
}

/**
 * Retrieve the AudioTheme post type for the current archive.
 *
 * @since 1.7.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function get_audiotheme_current_archive_post_type() {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$post_type = '';

	// Determine the current post type.
	if ( is_tax() ) {
		$post_type = get_audiotheme_current_taxonomy_archive_post_type();
	} elseif ( is_post_type_archive() ) {
		foreach ( array( 'gig', 'record', 'track', 'video' ) as $type ) {
			if ( ! is_post_type_archive( 'audiotheme_' . $type ) ) {
				continue;
			}

			$post_type = 'audiotheme_' . $type;
			break;
		}
	}

	return $post_type;
}

/**
 * Retrieve the AudioTheme post type for the current taxonomy archive.
 *
 * @since 1.7.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function get_audiotheme_current_taxonomy_archive_post_type() {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$post_type = '';
	$taxonomy  = get_taxonomy( get_queried_object()->taxonomy );

	if ( empty( $taxonomy->object_type ) ) {
		return $post_type;
	}

	foreach ( $taxonomy->object_type as $type ) {
		if ( false === strpos( $type, 'audiotheme_' ) ) {
			continue;
		}

		$post_type = $type;
		break;
	}

	return $post_type;
}

/**
 * Add an HTML wrapper to certain videos retrieved via oEmbed.
 *
 * The wrapper is useful as a styling hook and for responsive designs. Also
 * attempts to add the wmode parameter to YouTube videos and flash embeds.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $html HTML.
 * @param string $url oEmbed URL.
 * @param array  $attr Embed attributes.
 * @param int    $post_id Post ID.
 * @return string Embed HTML with wrapper.
 */
function audiotheme_oembed_html( $html, $url = null, $attr = null, $post_id = null ) {
	$wrapped = '<div class="audiotheme-embed">' . $html . '</div>';

	if ( empty( $url ) && 'video_embed_html' === current_filter() ) { // Jetpack.
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
 * @deprecated 1.9.0
 * @access private
 *
 * @param array $matches Iframe source matches.
 * @return string
 */
function _audiotheme_oembed_youtube_wmode_parameter( $matches ) {
	return add_query_arg( 'wmode', 'transparent', $matches[0] );
}

/**
 * Filter the default gallery shortcode.
 *
 * Not recommended for use -- this will be removed in a future version is
 * currently only maintained for backward compatibility.
 *
 * This filter allows the output of the default gallery shortcode to be
 * customized and adds support for additional functionality, shortcode
 * attributes, and classes for CSS and JavaScript hooks.
 *
 * A lot of the default sanitization is duplicated because WordPress doesn't
 * provide a filter later in the process.
 *
 * @since 1.2.0
 * @deprecated 1.9.0
 *
 * @param string $output Output string passed from default shortcode.
 * @param array  $attr Array of shortcode attributes.
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
	if ( 'RAND' === $attr['order'] ) {
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

	// The id attribute is a combination of post ID and instance to ensure uniqueness.
	$wrapper = sprintf( "\n" . '<div id="gallery-%d-%d" class="%s">', $post->ID, $instance, join( ' ', array_map( 'sanitize_html_class', $gallery_classes ) ) );

	// Hooks should append custom output to the $wrapper arg if necessary and be sure to close the div.
	$output = apply_filters( 'audiotheme_post_gallery_output', $wrapper, $attachments, $attr, $instance );

	// Skip output generation if a hook modified the output.
	if ( empty( $output ) || $wrapper === $output ) {
		// If $output is empty for some reason, restart the output with the default wrapper.
		if ( empty( $output ) ) {
			$output = $wrapper;
		}

		foreach ( $attachments as $i => $attachment ) {
			// More 'link' options have been added.
			if ( 'none' === $link ) {
				// Don't link the thumbnails in the gallery.
				$href = '';
			} elseif ( 'file' === $link ) {
				// Link directly to the attachment.
				$href = wp_get_attachment_url( $attachment->ID );
			} elseif ( 'link' === $link ) {
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
