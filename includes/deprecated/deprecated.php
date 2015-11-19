<?php
/**
 * Deprecated functions.
 *
 * These will be removed in a future version.
 *
 * @package AudioTheme\Deprecated
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
 * Parse video oEmbed data.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 * @see WP_oEmbed->data2html()
 *
 * @param string $return Embed HTML.
 * @param object $data Data returned from the oEmbed request.
 * @param string $url The URL used for the oEmbed request.
 * @return string
 */
function audiotheme_parse_video_oembed_data( $return, $data, $url ) {
	global $post_id;

	_deprecated_function( __FUNCTION__, '1.8.0' );

	// Supports any oEmbed providers that respond with 'thumbnail_url'.
	if ( isset( $data->thumbnail_url ) ) {
		$current_thumb_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );
		$oembed_thumb = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', true );

		if ( ( ! $current_thumb_id || $current_thumb_id !== $oembed_thumb_id ) && $data->thumbnail_url === $oembed_thumb ) {
			// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
			set_post_thumbnail( $post_id, $oembed_thumb_id );
		} elseif ( ! $current_thumb_id || $data->thumbnail_url !== $oembed_thumb ) {
			// Add new thumbnail if the returned URL doesn't match the
			// oEmbed thumb URL or if there isn't a current thumbnail.
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_id );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );

			if ( $thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
				// Store the oEmbed thumb data so the same image isn't copied on repeated requests.
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', $thumbnail_id, true );
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', $data->thumbnail_url, true );
			}
		}
	}

	return $return;
}

/**
 * Set a video post's featured image.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 */
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_id;
	_deprecated_function( __FUNCTION__, '1.8.0' );
	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Helper function to enqueue a pointer.
 *
 * The $id will be used to reference the pointer in javascript as well as the
 * key it's saved with in the dismissed pointers user meta. $content will be
 * wrapped in wpautop(). Passing a pointer arg will allow the position of the
 * pointer to be changed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $id Pointer id.
 * @param string $title Pointer title.
 * @param string $content Pointer content.
 * @param array $args Additional args.
 */
function audiotheme_enqueue_pointer( $id, $title, $content, $args = array() ) {
	global $audiotheme_pointers;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	$id = sanitize_key( $id );

	$args = wp_parse_args( $args, array(
		'position' => 'left',
	) );

	$content = sprintf( '<h3>%s</h3>%s', $title, wpautop( $content ) );

	$audiotheme_pointers[ $id ] = array(
		'id'       => $id,
		'content'  => $content,
		'position' => $args['position'],
	);
}

/**
 * Check to see if a pointer has been dismissed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $id The pointer id.
 * @return bool
 */
function is_audiotheme_pointer_dismissed( $id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	return in_array( $id, $dismissed );
}

/**
 * Print enqueued pointers to a global javascript variable.
 *
 * Dismissed pointers are automatically removed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_print_pointers() {
	global $audiotheme_pointers;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	if ( empty( $audiotheme_pointers ) ) {
		return;
	}

	// Remove dismissed pointers.
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	$audiotheme_pointers = array_diff_key( $audiotheme_pointers, array_flip( $dismissed ) );

	if ( empty( $audiotheme_pointers ) ) {
		return;
	}

	// @see WP_Scripts::localize()
	foreach ( (array) $audiotheme_pointers as $id => $pointer ) {
		foreach ( $pointer as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$audiotheme_pointers[ $id ][ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}
	}

	// Output the object directly since there isn't really have a script to attach it to.
	// CDATA and type='text/javascript' is not needed for HTML 5.
	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo 'var audiothemePointers = ' . json_encode( $audiotheme_pointers ) . ";\n";
	echo "/* ]]> */\n";
	echo "</script>\n";
}

/**
 * Register discography post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_discography_init() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Get the discography rewrite base. Defaults to 'music'.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function get_audiotheme_discography_rewrite_base() {
	_deprecated_function( __FUNCTION__, '1.9.0', 'AudioTheme_Module_Discography::get_rewrite_base()' );
	return audiotheme()->modules['discography']->get_rewrite_base();
}

/**
 * Add custom discography rewrite rules.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_discography_generate_rewrite_rules( $wp_rewrite ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	audiotheme()->modules['discography']->generate_rewrite_rules( $wp_rewrite );
}

/**
 * Sort record archive requests.
 *
 * Defaults to sorting by release year in descending order. An option is
 * available on the archive page to sort by title or a custom order. The custom
 * order using the 'menu_order' value, which can be set using a plugin like
 * Simple Page Ordering.
 *
 * Alternatively, a plugin can hook into pre_get_posts at an earlier priority
 * and manually set the order.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_record_query_sort( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Sort records by title after sorting by release year.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param string $orderby SQL order clause.
 * @return string
 */
function audiotheme_record_query_sort_sql( $orderby ) {
	global $wpdb;
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $orderby . ", {$wpdb->posts}.post_title ASC";
}

/**
 * Filter track requests.
 *
 * Tracks must belong to a record, so the parent record is set for track
 * requests.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_track_query( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Set posts per page for record archives if the default templates are being
 * loaded.
 *
 * The default record archive template uses a 4-column grid. If it's loaded from
 * the plugin, set the posts per page arg to a multiple of 4.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_record_default_template_query( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Load discography templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_discography_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return audiotheme()->modules['discography']->template_include( $template );
}

/**
 * Filter discography permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a discography post type.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $post_link The default permalink.
 * @param object $post_link The record or track to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The record or track permalink.
 */
function audiotheme_discography_permalinks( $post_link, $post, $leavename, $sample ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $post_link;
}

/**
 * Filter the permalink for the discography archive.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The discography archive URL.
 */
function audiotheme_discography_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $link;
}

/**
 * Ensure track slugs are unique.
 *
 * Tracks should always be associated with a record so their slugs only need
 * to be unique within the context of a record.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $slug The desired slug (post_name).
 * @param integer $post_ID
 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
 * @param string $post_type
 * @param integer $post_parent
 * @param string $original_slug Slug passed to the uniqueness method.
 * @return string
 */
function audiotheme_track_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $slug;
}

/**
 * Transform a track id or array of data into the expected format for use as a
 * JavaScript object.
 *
 * @since 1.1.0
 * @deprecated 1.9.0
 *
 * @param int|array $track Track ID or array of expected track properties.
 * @return array
 */
function audiotheme_prepare_track_for_js( $track ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$data = array(
		'artist'  => '',
		'artwork' => '',
		'mp3'     => '',
		'record'  => '',
		'title'   => '',
	);

	// Enqueue a track post type.
	if ( 'audiotheme_track' === get_post_type( $track ) ) {
		$track = get_post( $track );
		$record = get_post( $track->post_parent );

		$data['artist'] = get_audiotheme_track_artist( $track->ID );
		$data['mp3'] = get_audiotheme_track_file_url( $track->ID );
		$data['record'] = $record->post_title;
		$data['title'] = $track->post_title;

		// WP playlist format.
		$data['format'] = 'mp3';
		$data['meta']['artist'] = $data['artist'];
		$data['meta']['length_formatted'] = '0:00';
		$data['src'] = $data['mp3'];

		if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $track ) ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'audiotheme_track_js_artwork_size', 'thumbnail' ) );
			$data['artwork'] = $image[0];
		}
	}

	// Add the track data directly.
	elseif ( is_array( $track ) ) {
		if ( isset( $track['artwork'] ) ) {
			$data['artwork'] = esc_url( $track['artwork'] );
		}

		if ( isset( $track['file'] ) ) {
			$data['mp3'] = esc_url_raw( audiotheme_encode_url_path( $track['file'] ) );
		}

		if ( isset( $track['mp3'] ) ) {
			$data['mp3'] = esc_url_raw( audiotheme_encode_url_path( $track['mp3'] ) );
		}

		if ( isset( $track['title'] ) ) {
			$data['title'] = wp_strip_all_tags( $track['title'] );
		}

		$data = array_merge( $track, $data );
	}

	$data = apply_filters( 'audiotheme_track_js_data', $data, $track );

	return $data;
}

/**
 * Convert enqueued track lists into an array of tracks prepared for JavaScript
 * and output the JSON-encoded object in the footer.
 *
 * @since 1.1.0
 * @deprecated 1.9.0
 */
function audiotheme_print_tracks_js() {
	global $audiotheme_enqueued_tracks;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	if ( empty( $audiotheme_enqueued_tracks ) || ! is_array( $audiotheme_enqueued_tracks ) ) {
		return;
	}

	$lists = array();

	// @todo The track & record ids should be collected at some point so they can all be fetched in a single query.

	foreach ( $audiotheme_enqueued_tracks as $list => $tracks ) {
		if ( empty( $tracks ) || ! is_array( $tracks ) ) {
			continue;
		}

		do_action( 'audiotheme_prepare_tracks', $list );

		foreach ( $tracks as $track ) {
			if ( 'audiotheme_record' === get_post_type( $track ) ) {
				$record_tracks = get_audiotheme_record_tracks( $track, array( 'has_file' => true ) );

				if ( $record_tracks ) {
					foreach ( $record_tracks as $record_track ) {
						if ( $track_data = audiotheme_prepare_track_for_js( $record_track ) ) {
							$lists[ $list ][] = $track_data;
						}
					}
				}
			} elseif ( $track_data = audiotheme_prepare_track_for_js( $track ) ) {
				$lists[ $list ][] = $track_data;
			}
		}
	}

	// Print a JavaScript object.
	if ( ! empty( $lists ) ) {
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		window.AudiothemeTracks = window.AudiothemeTracks || {};

		(function( window ) {
			var tracks = <?php echo json_encode( $lists ); ?>,
				i;

			for ( i in tracks ) {
				window.AudiothemeTracks[ i ] = tracks[ i ];
			}
		})( this );
		/* ]]> */
		</script>
		<?php
	}
}

/**
 * Add classes to record posts on the archive page.
 *
 * Classes serve as helpful hooks to aid in styling across various browsers.
 *
 * - Adds nth-child classes to record posts.
 *
 * @since 1.2.0
 * @deprecated 1.9.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_record_archive_post_class( $classes ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $classes;
}

/**
 * Attach hooks for loading and managing discography in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_load_discography_admin() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Rename the top level Records menu item to Discography.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @link https://core.trac.wordpress.org/ticket/23316
 */
function audiotheme_discography_admin_menu() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Discography update messages.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_discography_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $messages;
}

/**
 * Move the playlist menu item under discography.
 *
 * @since 1.5.0
 * @deprecated 1.9.0
 *
 * @param array $args Post type registration args.
 * @return array
 */
function audiotheme_playlist_args( $args ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	$args['show_in_menu'] = 'edit.php?post_type=audiotheme_record';
	return $args;
}

/**
 * Enqueue playlist scripts and styles.
 *
 * @since 1.5.0
 * @deprecated 1.9.0
 */
function audiotheme_playlist_admin_enqueue_scripts() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Print playlist JavaScript templates.
 *
 * @since 1.5.0
 * @deprecated 1.9.0
 */
function audiotheme_playlist_print_templates() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Convert a track into the format expected by the Cue plugin.
 *
 * @since 1.5.0
 * @deprecated 1.9.0
 *
 * @param int|WP_Post $post Post object or ID.
 * @return object Track object expected by Cue.
 */
function get_audiotheme_playlist_track( $post = 0 ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$post = get_post( $post );
	$track = new stdClass;

	$track->id = $post->ID;
	$track->artist = get_audiotheme_track_artist( $post->ID );
	$track->audioUrl = get_audiotheme_track_file_url( $post->ID );
	$track->title = get_the_title( $post->ID );

	if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $post->ID ) ) {
		$size = apply_filters( 'cue_artwork_size', array( 300, 300 ) );
		$image = image_downsize( $thumbnail_id, $size );

		$track->artworkUrl = $image[0];
	}

	return $track;
}

/**
 * Custom sort records on the Manage Records screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_records_admin_query( $wp_query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register record columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $columns An array of the column names to display.
 * @return array Filtered array of column names.
 */
function audiotheme_record_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $columns;
}

/**
 * Register sortable record columns.
 *
 * @since 1.0.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_record_register_sortable_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $columns;
}

/**
 * Display custom record columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_record_display_columns( $column_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Remove quick edit from the record list table.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_record_list_table_actions( $actions, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $actions;
}

/**
 * Remove bulk edit from the record list table.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_record_list_table_bulk_actions( $actions ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $actions;
}

/**
 * Custom rules for saving a record.
 *
 * Creates and updates child tracks and saves additional record meta.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_record_save_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register record meta boxes.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_edit_record_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Tracklist editor.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_edit_record_tracklist() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Record details meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_record_details_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Save record archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_record_archive_save_settings_hook( $post_id, $post, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add an orderby setting to the record archive.
 *
 * Allows for changing the sort order of records. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_record_archive_settings( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Custom sort tracks on the Manage Tracks screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_tracks_admin_query( $wp_query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register track columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_track_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $columns;
}

/**
 * Register sortable track columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_track_register_sortable_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $columns;
}

/**
 * Display custom track columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_track_display_columns( $column_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Remove quick edit from the track list table.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_track_list_table_actions( $actions, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $actions;
}

/**
 * Remove bulk edit from the track list table.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_track_list_table_bulk_actions( $actions ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $actions;
}

/**
 * Custom track filter dropdowns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_tracks_filters() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Custom rules for saving a track.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_track_save_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register track meta boxes.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Track ID.
 */
function audiotheme_edit_track_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}


/**
 * Display track details meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post The track post object being edited.
 */
function audiotheme_track_details_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register gig and venue post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_gigs_init() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register query variables.
 *
 * @since 1.6.3
 * @deprecated 1.9.0
 *
 * @param array $vars Array of valid query variables.
 * @return array
 */
function audiotheme_gigs_register_query_vars( $vars ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Filter gigs requests.
 *
 * Automatically sorts gigs in ascending order by the gig date, but limits to
 * showing upcoming gigs unless a specific date range is requested (year,
 * month, day).
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_pre_gig_query( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Filter gig permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a gig post type.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 * @see get_post_permalink()
 *
 * @param string $post_link The default gig URL.
 * @param object $post_link The gig to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The gig permalink.
 */
function audiotheme_gig_permalink( $post_link, $post, $leavename, $sample ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $post_link;
}

/**
 * Filter the permalink for the gigs archive.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The gig archive URL.
 */
function audiotheme_gigs_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $link;
}

/**
 * Prevent conflicts in gig permalinks.
 *
 * Gigs without titles will fall back to using the ID for the slug, however,
 * when the ID is a 4 digit number, it will conflict with date-based permalinks.
 * Any slugs that match the ID are preprended with 'gig-'.
 *
 * @since 1.6.1
 * @deprecated 1.9.0
 * @see wp_unique_post_slug()
 *
 * @param string $slug The desired slug (post_name).
 * @param integer $post_ID
 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
 * @param string $post_type
 * @param integer $post_parent
 * @param string $original_slug Slug passed to the uniqueness method.
 * @return string
 */
function audiotheme_gig_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $slug;
}

/**
 * Prevent conflicts with numeric gig slugs.
 *
 * If a slug is empty when a post is published, wp_insert_post() will base the
 * slug off the title/ID without a way to filter it until after the post is
 * saved. If the saved slug matches the post ID for a gig, it's prefixed with
 * 'gig-' here to mimic the behavior in audiotheme_gig_unique_slug().
 *
 * @since 1.6.1
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 */
function audiotheme_gig_update_bad_slug( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Update a venue's cached gig count when gig is deleted.
 *
 * Determines if a venue's gig_count meta field needs to be updated
 * when a gig is deleted.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id ID of the gig being deleted.
 */
function audiotheme_gig_before_delete( $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add useful classes to gig posts.
 *
 * @since 1.1.0
 * @deprecated 1.9.0
 *
 * @param array $classes List of classes.
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @return array Array of classes.
 */
function audiotheme_gig_post_class( $classes, $class, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $classes;
}

/**
 * Get the gigs rewrite base. Defaults to 'shows'.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function audiotheme_gigs_rewrite_base() {
	_deprecated_function( __FUNCTION__, '1.9.0', 'AudioTheme_Module_Gigs::get_rewrite_base()' );
	return audiotheme()->modules['gigs']->get_rewrite_base();
}

/**
 * Add custom gig rewrite rules.
 *
 * /base/YYYY/MM/DD/(feed|ical|json)/
 * /base/YYYY/MM/DD/
 * /base/YYYY/MM/(feed|ical|json)/
 * /base/YYYY/MM/
 * /base/YYYY/(feed|ical|json)/
 * /base/YYYY/
 * /base/(feed|ical|json)/
 * /base/%postname%/
 * /base/
 *
 * @todo /base/tour/%tourname%/
 *       /base/past/page/2/
 *       /base/past/
 *       /base/YYYY/page/2/
 *       etc.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_gig_generate_rewrite_rules( $wp_rewrite ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	audiotheme()->modules['gigs']->generate_rewrite_rules( $wp_rewrite );
}

/**
 * Gig feeds and venue connections.
 *
 * Caches gig->venue connections and reroutes feed requests to
 * the appropriate template for processing.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 * @uses $wp_query
 * @uses p2p_type()->each_connected()
 */
function audiotheme_gig_template_redirect() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	audiotheme()->modules['gigs']->template_redirect();
}

/**
 * Load gig templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_gig_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $template;
}

/**
 * Get the admin panel URL for gigs.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function get_audiotheme_gig_admin_url( $args = '' ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$admin_url = admin_url( 'edit.php?post_type=audiotheme_gig' );

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
 * Attach hooks for loading and managing gigs in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_gigs_admin_setup() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add the admin menu items for gigs.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_gigs_admin_menu() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Higlight the correct top level and sub menu items for the gig screen being
 * displayed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $parent_file The screen being displayed.
 * @return string The menu item to highlight.
 */
function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $parent_file;
}

/**
 * Set up the gig Manage Screen.
 *
 * Initializes the custom post list table, and processes any actions that need
 * to be handled.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_gigs_manage_screen_setup() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display the gig Manage Screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_gigs_manage_screen() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Sanitize the 'per_page' screen option on the Manage Gigs and Manage Venues
 * screens.
 *
 * Apparently any other hook attached to the same filter that runs after this
 * will stomp all over it. To prevent this filter from doing the same, it's
 * only attached on the screens that require it. The priority should be set
 * extremely low to help ensure the correct value gets returned.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param bool $return Default is 'false'.
 * @param string $option The option name.
 * @param mixed $value The value to sanitize.
 * @return mixed The sanitized value.
 */
function audiotheme_gigs_screen_options( $return, $option, $value ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $return;
}

/**
 * Set up the gig Add/Edit screen.
 *
 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
 * to display the edit fields after the title.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_edit_screen_setup( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Setup and display the main gig fields for editing.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_edit_gig_fields() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Gig tickets meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_tickets_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Process and save gig info when the CPT is saved.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $gig_id Gig post ID.
 * @param WP_Post $post Gig post object.
 */
function audiotheme_gig_save_post( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Gig update messages.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array
 */
function audiotheme_gig_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $messages;
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
 * Set up the Manage Venues screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_venues_manage_screen_setup() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Set up the Edit Venue screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_venue_edit_screen_setup() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Process venue add/edit actions.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_venue_edit_screen_process_actions() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display the venue add/edit screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_venue_edit_screen() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display venue contact information meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_contact_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display venue notes meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_notes_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display custom venue submit meta box.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_submit_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register video post type and attach hooks to load related functionality.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_videos_init() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Get the videos rewrite base. Defaults to 'videos'.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function get_audiotheme_videos_rewrite_base() {
	_deprecated_function( __FUNCTION__, '1.9.0', 'AudioTheme_Module_Videos::get_rewrite_base()' );
	return audiotheme()->modules['videos']->get_rewrite_base();
}

/**
 * Sort video archive requests.
 *
 * Defaults to sorting by publish date in descending order. A plugin can hook
 * into pre_get_posts at an earlier priority and manually set the order.
 *
 * @since 1.4.4
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_video_query_sort( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Set posts per page for video archives if the default templates are being
 * loaded.
 *
 * The default video archive template uses a 4-column grid. If it's loaded from
 * the plugin, set the posts per page arg to a multiple of 4.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_video_default_template_query( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Load video templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_video_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	audiotheme()->modules['videos']->template_include( $template );
}

/**
 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $attachment_id The ID of the attachment being deleted.
 */
function audiotheme_video_delete_attachment( $attachment_id ) {
	global $wpdb;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_audiotheme_oembed_thumbnail_id' AND meta_value=%d", $attachment_id ) );
	if ( $post_id ) {
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
	}
}

/**
 * Add classes to video posts on the archive page.
 *
 * Classes serve as helpful hooks to aid in styling across various browsers.
 *
 * - Adds nth-child classes to video posts.
 *
 * @since 1.2.0
 * @deprecated 1.9.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_video_archive_post_class( $classes ) {
	global $wp_query;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_video' ) ) {
		$nth_child_classes = audiotheme_nth_child_classes( array(
			'current' => $wp_query->current_post + 1,
			'max'     => get_audiotheme_archive_meta( 'columns', true, 4 ),
		) );

		$classes = array_merge( $classes, $nth_child_classes );
	}

	return $classes;
}

/**
 * Attach hooks for loading and managing videos in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_load_videos_admin() {}

/**
 * Video update messages.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_video_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $messages;
}

/**
 * Register video columns.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_video_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $columns;
}

/**
 * Register video meta boxes.
 *
 * This callback is defined in the video CPT registration function. Meta boxes
 * or any other functionality that should be limited to the Add/Edit Video
 * screen and should occur after 'do_meta_boxes' can be registered here.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_video_meta_boxes() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display a field to enter a video URL after the post title.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_video_after_title() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add a link to get the video thumbnail from an oEmbed endpoint.
 *
 * Adds data about the current thumbnail and a previously fetched thumbnail
 * from an oEmbed endpoint so the link can be hidden or shown as necessary. A
 * function is also fired each time the HTML is output in order to determine
 * whether the link should be displayed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $content Default post thumbnail HTML.
 * @param int $post_id Post ID.
 * @return string
 */
function audiotheme_video_admin_post_thumbnail_html( $content, $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $content;
}

/**
 * AJAX method to retrieve the thumbnail for a video.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_ajax_get_video_oembed_data() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Import a video thumbnail from an oEmbed endpoint into the media library.
 *
 * @todo Considering doing video URL comparison rather than oembed thumbnail
 *       comparison?
 *
 * @since 1.8.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Video post ID.
 * @param string $url Video URL.
 */
function audiotheme_video_sideload_thumbnail( $post_id, $url ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Download an image from the specified URL and attach it to a post.
 *
 * @since 1.8.0
 * @deprecated 1.9.0
 *
 * @see media_sideload_image()
 *
 * @param string $url The URL of the image to download.
 * @param int $post_id The post ID the media is to be associated with.
 * @param string $desc Optional. Description of the image.
 * @return int|WP_Error Populated HTML img tag on success.
 */
function audiotheme_video_sideload_image( $url, $post_id, $desc = null ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $id;
}

/**
 * Save custom video data.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id The ID of the post.
 * @param object $post The post object.
 */
function audiotheme_video_save_post( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Save video archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.4.4
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_video_archive_save_settings_hook( $post_id, $post, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add an orderby setting to the video archive.
 *
 * Allows for changing the sort order of videos. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.4.4
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_video_archive_settings( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register archive post type and setup related functionality.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function register_audiotheme_archives() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Filter AudioTheme archive requests.
 *
 * Set the number of posts per archive page.
 *
 * @since 1.4.2
 * @deprecated 1.9.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_archive_query( $query ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
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
 * Sanitize archive columns setting.
 *
 * The allowd columns value may be different between themes, so make sure it
 * exists in the settings defined by the theme, otherwise, return the theme
 * default.
 *
 * @since 1.4.4
 * @deprecated 1.9.0
 *
 * @param mixed $value Existing meta value.
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Optional. Whether to return a single value.
 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function audiotheme_sanitize_audiotheme_archive_columns( $value, $key, $single, $default, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0', 'AudioTheme_Module_Archives::sanitize_columns_settings()' );
	return audiotheme()->modules['archives']->sanitize_columns_settings( $value, $key, $single, $default, $post_type );
}

/**
 * Save the active archive IDs.
 *
 * Determines when an archive has become inactive and moves it to a separate
 * option so that if it's activated again in the future, a new post won't be
 * created.
 *
 * Will flush rewrite rules if any changes are detected.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $ids Associative array of post type slugs as keys and archive post IDs as the values.
 */
function audiotheme_archives_save_active_archives( $ids ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Flush the rewrite rules when an archive post slug is changed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID
 * @param WP_Post $post_after Updated post object.
 * @param WP_Post $post_before Post object before udpate.
 */
function audiotheme_archives_post_updated( $post_id, $post_after, $post_before ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Remove the post type archive reference if it's deleted.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archives_deleted_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Update a post type's rewrite base option.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $post_type Post type slug.
 * @param int $archive_id Archive post ID>
 */
function audiotheme_archives_update_post_type_rewrite_base( $post_type, $archive_id ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Provide an edit link for archives in the admin bar.
 *
 * @since 1.2.1
 * @deprecated 1.9.0
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object instance.
 */
function audiotheme_archives_admin_bar_edit_menu( $wp_admin_bar ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Setup archive posts for post types that have support.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_archives_init_admin() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add submenu items for archives under the post type menu item.
 *
 * Ensures the user has the capability to edit pages in general as well
 * as the individual page before displaying the submenu item.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_archives_admin_menu() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Replace the submit meta box to remove unnecessary fields.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_archives_add_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Highlight the corresponding top level and submenu items when editing an
 * archive page.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $parent_file A parent file identifier.
 * @return string
 */
function audiotheme_archives_parent_file( $parent_file ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $parent_file;
}

/**
 * Archive update messages.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $messages The array of post update messages.
 * @return array An array with new CPT update messages.
 */
function audiotheme_archives_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $messages;
}

/**
 * Create an archive post for a post type if one doesn't exist.
 *
 * The post type's plural label is used for the post title and the defined
 * rewrite slug is used for the postname.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $post_type_name Post type slug.
 * @return int Post ID.
 */
function audiotheme_archives_create_archive( $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0', 'AudioTheme_Module_Archives::add_post_type_archive()' );
	return audiotheme()->modules['archives']->add_post_type_archive( $post_type );
}

/**
 * Retrieve a post type's archive slug.
 *
 * Checks the 'has_archive' and 'with_front' args in order to build the
 * slug.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $post_type Post type name.
 * @return string Archive slug.
 */
function get_audiotheme_post_type_archive_slug( $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $slug;
}

/**
 * Save archive meta data.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archive_save_hook( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display archive settings meta box.
 *
 * The meta box needs to be activated first, then fields can be displayed using
 * one of the actions.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box( $post, $args = array() ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Add fields to the archive settings meta box.
 *
 * @since 1.4.2
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box_fields( $post, $post_type, $fields = array() ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Filter audiotheme_archive permalinks to match the corresponding post type's
 * archive.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $permalink Default permalink.
 * @param WP_Post $post Post object.
 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
 * @return string Permalink.
 */
function audiotheme_archives_post_type_link( $permalink, $post, $leavename ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $permalink;
}

/**
 * Filter post type archive permalinks.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $link Post type archive link.
 * @param string $post_type Post type name.
 * @return string
 */
function audiotheme_archives_post_type_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $link;
}

/**
 * Filter the default post_type_archive_title() template tag and replace with
 * custom archive title.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $label Post type archive title.
 * @return string
 */
function audiotheme_archives_post_type_archive_title( $title ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $title;
}

/**
 * Compare two version numbers.
 *
 * This function abstracts the logic for determining the current version
 * number for various packages, so the only version number that needs to be
 * known is the one to compare against.
 *
 * Basically serves as a wrapper for the native PHP version_compare()
 * function, but allows a known package to be passed as the first parameter.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @see PHP docs for version_compare()
 * @uses version_compare()
 *
 * @param string $version A package identifier or version number to compare against.
 * @param string $version2 The version number to compare to.
 * @param string $operator Optional. Relationship to test. ( <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne ).
 * @return mixed True or false if operator is supplied. -1, 0, or 1 if operator is empty.
 */
function audiotheme_version_compare( $version, $version2, $operator = null ) {
	_deprecated_function( __FUNCTION__, '1.9.0', 'version_compare()' );

	switch ( $version ) {
		case 'audiotheme' :
			$version = AUDIOTHEME_VERSION;
			break;
		case 'php' :
			$version = phpversion();
			break;
		case 'stylesheet' : // Child theme if it exists, otherwise same as template.
			$theme = wp_get_theme();
			$version = $theme->get( 'Version' );
			break;
		case 'template' : // Parent theme.
			$theme = wp_get_theme( get_template() );
			$version = $theme->get( 'Version' );
			break;
		case 'wp' :
			$version = get_bloginfo( 'version' );
			break;
	}

	return version_compare( $version, $version2, $operator );
}

/**
 * Attempt to make custom time formats more compatible between JavaScript and PHP.
 *
 * If the time format option has an escape sequences, use a default format
 * determined by whether or not the option uses 24 hour format or not.
 *
 * @since 1.7.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function audiotheme_compatible_time_format() {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$time_format = get_option( 'time_format' );

	if ( false !== strpos( $time_format, '\\' ) ) {
		$time_format = false !== strpbrk( $time_format, 'GH' ) ? 'G:i' : 'g:i a';
	}

	return $time_format;
}

/**
 * Support localization for the plugin strings.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_load_textdomain() {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	load_plugin_textdomain( 'audiotheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Register frontend scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_register_scripts() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register Supported Widgets
 *
 * Themes can load all widgets by calling add_theme_support( 'audiotheme-widgets' ).
 *
 * If support for all widgets isn't desired, a second parameter consisting of an array
 * of widget keys can be passed to load the specified widgets:
 * add_theme_support( 'audiotheme-widgets', array( 'upcoming-gigs' ) )
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_widgets_init() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
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

/**
 * Add audio metadata to attachment response objects.
 *
 * @since 1.4.4
 * @deprecated 1.9.0
 *
 * @param array   $response Attachment data to send as JSON.
 * @param WP_Post $attachment Attachment object.
 * @param array   $meta Attachment meta.
 * @return array
 */
function audiotheme_wp_prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	return $response;
}
