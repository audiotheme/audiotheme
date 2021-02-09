<?php
/**
 * Deprecated functions.
 *
 * These are functions that were most likely never part of a public API and have
 * been replaced by an alternative or the functionality is no longer necessary
 * due to improvements in WordPress core.
 *
 * Functions in this file are scheduled to be removed in the near future, but
 * are maintained here during a transition period to help prevent fatal errors
 * in case they have been called directly.
 *
 * @package   AudioTheme\Deprecated
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Load admin-specific functions and libraries.
 *
 * Has to be loaded after the Theme Customizer in order to determine if the
 * Settings API should be included while customizing a theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_load_admin() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Flush the rewrite rules if needed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_loaded() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! is_network_admin() && 'no' !== get_option( 'audiotheme_flush_rewrite_rules' ) ) {
		update_option( 'audiotheme_flush_rewrite_rules', 'no' );
		flush_rewrite_rules();
	}
}

/**
 * Activation routine.
 *
 * Occurs too late to flush rewrite rules, so set an option to flush the
 * rewrite rules on the next request.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_activate() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
}

/**
 * Deactivation routine.
 *
 * Deleting the rewrite rules option should force them to be regenerated the
 * next time they're needed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_deactivate() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	delete_option( 'rewrite_rules' );
}

/**
 * Additional setup during init.
 *
 * @since 1.2.0
 * @deprecated 2.0.0
 */
function audiotheme_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register discography post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_discography_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Get the discography rewrite base. Defaults to 'music'.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return string
 */
function get_audiotheme_discography_rewrite_base() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'AudioTheme_Module_Discography::get_rewrite_base()' );
	return audiotheme()->modules['discography']->get_rewrite_base();
}

/**
 * Add custom discography rewrite rules.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_discography_generate_rewrite_rules( $wp_rewrite ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
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
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_record_query_sort( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Sort records by title after sorting by release year.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param string $orderby SQL order clause.
 * @return string
 */
function audiotheme_record_query_sort_sql( $orderby ) {
	global $wpdb;
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $orderby . ", {$wpdb->posts}.post_title ASC";
}

/**
 * Filter track requests.
 *
 * Tracks must belong to a record, so the parent record is set for track
 * requests.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_track_query( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Set posts per page for record archives if the default templates are being
 * loaded.
 *
 * The default record archive template uses a 4-column grid. If it's loaded from
 * the plugin, set the posts per page arg to a multiple of 4.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_record_default_template_query( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Load discography templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_discography_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return audiotheme()->modules['discography']->template_include( $template );
}

/**
 * Filter discography permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a discography post type.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $post_link The default permalink.
 * @param object $post_link The record or track to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The record or track permalink.
 */
function audiotheme_discography_permalinks( $post_link, $post, $leavename, $sample ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $post_link;
}

/**
 * Filter the permalink for the discography archive.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The discography archive URL.
 */
function audiotheme_discography_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $link;
}

/**
 * Ensure track slugs are unique.
 *
 * Tracks should always be associated with a record so their slugs only need
 * to be unique within the context of a record.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
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
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $slug;
}

/**
 * Transform a track id or array of data into the expected format for use as a
 * JavaScript object.
 *
 * @since 1.1.0
 * @deprecated 2.0.0
 *
 * @param int|array $track Track ID or array of expected track properties.
 * @return array
 */
function audiotheme_prepare_track_for_js( $track ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * @deprecated 2.0.0
 */
function audiotheme_print_tracks_js() {
	global $audiotheme_enqueued_tracks;

	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * @deprecated 2.0.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_record_archive_post_class( $classes ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $classes;
}

/**
 * Convert a track into the format expected by the Cue plugin.
 *
 * @since 1.5.0
 * @deprecated 2.0.0
 *
 * @param int|WP_Post $post Post object or ID.
 * @return object Track object expected by Cue.
 */
function get_audiotheme_playlist_track( $post = 0 ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * Register gig and venue post types and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_gigs_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register query variables.
 *
 * @since 1.6.3
 * @deprecated 2.0.0
 *
 * @param array $vars Array of valid query variables.
 * @return array
 */
function audiotheme_gigs_register_query_vars( $vars ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filter gigs requests.
 *
 * Automatically sorts gigs in ascending order by the gig date, but limits to
 * showing upcoming gigs unless a specific date range is requested (year,
 * month, day).
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_pre_gig_query( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filter gig permalinks to match the custom rewrite rules.
 *
 * Allows the standard WordPress API function get_permalink() to return the
 * correct URL when used with a gig post type.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 * @see get_post_permalink()
 *
 * @param string $post_link The default gig URL.
 * @param object $post_link The gig to get the permalink for.
 * @param bool $leavename Whether to keep the post name.
 * @param bool $sample Is it a sample permalink.
 * @return string The gig permalink.
 */
function audiotheme_gig_permalink( $post_link, $post, $leavename, $sample ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $post_link;
}

/**
 * Filter the permalink for the gigs archive.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $link The default archive URL.
 * @param string $post_type Post type.
 * @return string The gig archive URL.
 */
function audiotheme_gigs_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
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
 * @deprecated 2.0.0
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
	_deprecated_function( __FUNCTION__, '2.0.0' );
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
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 */
function audiotheme_gig_update_bad_slug( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Update a venue's cached gig count when gig is deleted.
 *
 * Determines if a venue's gig_count meta field needs to be updated
 * when a gig is deleted.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id ID of the gig being deleted.
 */
function audiotheme_gig_before_delete( $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add useful classes to gig posts.
 *
 * @since 1.1.0
 * @deprecated 2.0.0
 *
 * @param array $classes List of classes.
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @return array Array of classes.
 */
function audiotheme_gig_post_class( $classes, $class, $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $classes;
}

/**
 * Get the gigs rewrite base. Defaults to 'shows'.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return string
 */
function audiotheme_gigs_rewrite_base() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'AudioTheme_Module_Gigs::get_rewrite_base()' );
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
 * @deprecated 2.0.0
 *
 * @param object $wp_rewrite The main rewrite object. Passed by reference.
 */
function audiotheme_gig_generate_rewrite_rules( $wp_rewrite ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	audiotheme()->modules['gigs']->generate_rewrite_rules( $wp_rewrite );
}

/**
 * Gig feeds and venue connections.
 *
 * Caches gig->venue connections and reroutes feed requests to
 * the appropriate template for processing.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 * @uses $wp_query
 * @uses p2p_type()->each_connected()
 */
function audiotheme_gig_template_redirect() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	audiotheme()->modules['gigs']->template_redirect();
}

/**
 * Load gig templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_gig_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $template;
}

/**
 * Get the admin panel URL for gigs.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function get_audiotheme_gig_admin_url( $args = '' ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * Register video post type and attach hooks to load related functionality.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_videos_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Get the videos rewrite base. Defaults to 'videos'.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return string
 */
function get_audiotheme_videos_rewrite_base() {
	_deprecated_function( __FUNCTION__, '2.0.0', 'AudioTheme_Module_Videos::get_rewrite_base()' );
	return audiotheme()->modules['videos']->get_rewrite_base();
}

/**
 * Sort video archive requests.
 *
 * Defaults to sorting by publish date in descending order. A plugin can hook
 * into pre_get_posts at an earlier priority and manually set the order.
 *
 * @since 1.4.4
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_video_query_sort( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Set posts per page for video archives if the default templates are being
 * loaded.
 *
 * The default video archive template uses a 4-column grid. If it's loaded from
 * the plugin, set the posts per page arg to a multiple of 4.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_video_default_template_query( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Load video templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_video_template_include( $template ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	audiotheme()->modules['videos']->template_include( $template );
}

/**
 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $attachment_id The ID of the attachment being deleted.
 */
function audiotheme_video_delete_attachment( $attachment_id ) {
	global $wpdb;

	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * @deprecated 2.0.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_video_archive_post_class( $classes ) {
	global $wp_query;

	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * Filter AudioTheme archive requests.
 *
 * Set the number of posts per archive page.
 *
 * @since 1.4.2
 * @deprecated 2.0.0
 *
 * @param object $query The main WP_Query object. Passed by reference.
 */
function audiotheme_archive_query( $query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Sanitize archive columns setting.
 *
 * The allowd columns value may be different between themes, so make sure it
 * exists in the settings defined by the theme, otherwise, return the theme
 * default.
 *
 * @since 1.4.4
 * @deprecated 2.0.0
 *
 * @param mixed $value Existing meta value.
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Optional. Whether to return a single value.
 * @param mixed $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function audiotheme_sanitize_audiotheme_archive_columns( $value, $key, $single, $default, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'AudioTheme_Module_Archives::sanitize_columns_settings()' );
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
 * @deprecated 2.0.0
 *
 * @param array $ids Associative array of post type slugs as keys and archive post IDs as the values.
 */
function audiotheme_archives_save_active_archives( $ids ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Flush the rewrite rules when an archive post slug is changed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID
 * @param WP_Post $post_after Updated post object.
 * @param WP_Post $post_before Post object before udpate.
 */
function audiotheme_archives_post_updated( $post_id, $post_after, $post_before ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Remove the post type archive reference if it's deleted.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archives_deleted_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Update a post type's rewrite base option.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $post_type Post type slug.
 * @param int $archive_id Archive post ID>
 */
function audiotheme_archives_update_post_type_rewrite_base( $post_type, $archive_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Provide an edit link for archives in the admin bar.
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object instance.
 */
function audiotheme_archives_admin_bar_edit_menu( $wp_admin_bar ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filter audiotheme_archive permalinks to match the corresponding post type's
 * archive.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $permalink Default permalink.
 * @param WP_Post $post Post object.
 * @param bool $leavename Optional, defaults to false. Whether to keep post name.
 * @return string Permalink.
 */
function audiotheme_archives_post_type_link( $permalink, $post, $leavename ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $permalink;
}

/**
 * Filter post type archive permalinks.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $link Post type archive link.
 * @param string $post_type Post type name.
 * @return string
 */
function audiotheme_archives_post_type_archive_link( $link, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $link;
}

/**
 * Filter the default post_type_archive_title() template tag and replace with
 * custom archive title.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $label Post type archive title.
 * @return string
 */
function audiotheme_archives_post_type_archive_title( $title ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
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
 * @deprecated 2.0.0
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
	_deprecated_function( __FUNCTION__, '2.0.0', 'version_compare()' );

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
 * @deprecated 2.0.0
 *
 * @return string
 */
function audiotheme_compatible_time_format() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * @deprecated 2.0.0
 */
function audiotheme_load_textdomain() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	load_plugin_textdomain( 'audiotheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Register frontend scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_register_scripts() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
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
 * @deprecated 2.0.0
 */
function audiotheme_widgets_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Filter record type archive titles.
 *
 * @since 1.7.0
 * @deprecated 2.0.0
 *
 * @param string $title Archive title.
 * @return string
 */
function audiotheme_archives_taxonomy_title( $title ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

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
 * @deprecated 2.0.0
 *
 * @param array $items List of menu items.
 * @param array $args Menu display args.
 * @return array
 */
function audiotheme_nav_menu_classes( $items, $args ) {
	global $wp;

	if ( empty( $items ) ) {
		return $items;
	}

	$classes = array();
	$first_top = -1;

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
 * Add audio metadata to attachment response objects.
 *
 * @since 1.4.4
 * @deprecated 2.0.0
 *
 * @param array   $response Attachment data to send as JSON.
 * @param WP_Post $attachment Attachment object.
 * @param array   $meta Attachment meta.
 * @return array
 */
function audiotheme_wp_prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $response;
}

/**
 * Attach hook to load the Posts to Posts core.
 *
 * This doesn't actually occur during the init hook despite the name.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_p2p_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Load Posts 2 Posts core.
 *
 * Requires the scbFramework.
 *
 * Posts 2 Posts requires two custom database tables to store post
 * relationships and relationship metadata. If an alternative version of the
 * library doesn't exist, the tables are created on admin_init.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_p2p_load_core() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Load the LESS compiler and set up Theme Customizer support.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_setup() {
	if ( $support = get_theme_support( 'audiotheme-less' ) ) {
		wp_less::instance();

		add_action( 'wp_loaded', 'audiotheme_less_register_vars', 20 );
		add_filter( 'wp_less_cache_url', 'audiotheme_less_force_ssl' );

		// Register a style sheet specifically for the Theme Customizer.
		$stylesheet = ( empty( $support[0]['customize_stylesheet'] ) ) ? '' : $support[0]['customize_stylesheet'];
		if ( ! empty( $stylesheet ) ) {
			wp_register_style( 'audiotheme-less-customize', $stylesheet );
			add_action( 'wp_footer', 'audiotheme_less_customize_enqueue_stylesheet' );
		}
	}
}

/**
 * Force SSL on LESS cache URLs.
 *
 * @since 1.3.1
 * @deprecated 2.0.0
 *
 * @param string $url URL to compiled CSS.
 * @return string
 */
function audiotheme_less_force_ssl( $url ) {
	if ( is_ssl() ) {
		$url = set_url_scheme( $url, 'https' );
	}

	return $url;
}

/**
 * Execute the callback function to register LESS vars and fire an action so
 * additional vars can be registered.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_register_vars() {
	$support = get_theme_support( 'audiotheme-less' );
	$callback = ( empty( $support[0]['less_vars_callback'] ) ) ? '' : $support[0]['less_vars_callback'];

	// Always points to the parent theme.
	add_less_var( 'templateurl', '~"' . get_template_directory_uri() . '/"' );

	if ( ! empty( $callback ) && function_exists( $callback ) ) {
		call_user_func( $callback );
	}

	do_action( 'audiotheme_less_register_vars' );
}

/**
 * Enqueue the Theme Customizer style sheet.
 *
 * This should only be run after the main style sheets have been output in
 * order to prevent changes from being made live prematurely.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_less_customize_enqueue_stylesheet() {
	global $wp_customize;

	// Load a separate customizer stylesheet when the customizer is being used.
	// Should prevent temporary changes from displaying on the front-end.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		return;
	}

	// Enqueue the Theme Customizer style sheet if it has been registered.
	if ( wp_style_is( 'audiotheme-less-customize', 'registered' ) ) {
		add_filter( 'less_force_compile', '__return_true' );
		wp_enqueue_style( 'audiotheme-less-customize' );
	}
}

if ( ! function_exists( 'get_audiotheme_option' ) ) :
/**
 * Returns an option value.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $option_name Option name as stored in database.
 * @param string $key Optional. Index of value in the option array.
 * @param mixed $default Optional. A default value to return if the requested option doesn't exist.
 * @return mixed The option value or $default.
 */
function get_audiotheme_option( $option_name, $key = null, $default = null ) {
	$option = get_option( $option_name );

	if ( $key === $option_name || empty( $key ) ) {
		return ( $option ) ? $option : $default;
	}

	return ( isset( $option[ $key ] ) ) ? $option[ $key ] : $default;
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_option' ) ) :
/**
 * Returns a theme option value.
 *
 * Function called to get a theme option. The returned value defaults to false
 * unless a default is passed.
 *
 * Note that this function footprint is slightly different than get_audiotheme_option(). While working in themes, the $option_name shouldn't necessarily need to be known or required, so it should be slightly easier to use while in a theme.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string The option key
 * @param mixed Optional. Default value to return if option key doesn't exist.
 * @param string Optional. Retrieve a non-standard option.
 * @return mixed The option value or $default or false.
 */
function get_audiotheme_theme_option( $key, $default = false, $option_name = '' ) {
	$option_name = ( empty( $option_name ) ) ? get_audiotheme_theme_options_name() : $option_name;

	return get_audiotheme_option( $option_name, $key, $default );
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_options_name' ) ) :
/**
 * Retrieve the registered option name for theme options.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function get_audiotheme_theme_options_name() {
	static $option_name;

	if ( ! isset( $option_name ) && ( $name = get_audiotheme_theme_options_support( 'option_name' ) ) ) {
		// The default option name is the first one registered in add_theme_support().
		$option_name = ( is_array( $name ) ) ? $name[0] : $name;
	}

	return ( isset( $option_name ) ) ? $option_name : false;
}
endif;

if ( ! function_exists( 'get_audiotheme_theme_options_support' ) ) :
/**
 * Check if the theme supports theme options and return registered arguments
 * with supplied defaults.
 *
 * Adding support for theme options is as simple as:
 * add_theme_support( 'audiotheme-theme-options' );
 *
 * Additional arguments can be supplied for more control. If the second
 * parameter is a string, it will be the callback for registering theme
 * options. Otherwise, it should be an array of arguments.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $var Optional. Specific argument to return.
 * @return mixed Value of requested argument or theme option support arguments.
 */
function get_audiotheme_theme_options_support( $var = null ) {
	if ( $support = get_theme_support( 'audiotheme-theme-options' ) ) {
		$option_name = 'audiotheme_mods-' . get_option( 'stylesheet' );

		$args = array(
		'callback'    => 'audiotheme_register_theme_options',
		'option_name' => $option_name,
		'menu_title'  => 'Theme Options',
		);

		if ( isset( $support[0] ) ) {
			if ( is_array( $support[0] ) ) {
				$args = wp_parse_args( $support[0], $args );
			} elseif ( is_string( $support[0] ) ) {
				$args['callback'] = $support[0];
			}
		}

		// Reset the option name if it was blanked out.
		if ( empty( $args['option_name'] ) ) {
			$args['option_name'] = $option_name;
		}

		// Option names can be arrays, so make sure it's always an array and sanitize each name.
		$args['option_name'] = array_map( 'sanitize_key', (array) $args['option_name'] );

		// If a specific arg is requested and it exists, return it, otherwise return false.
		if ( ! empty( $var ) ) {
			return ( isset( $args[ $var ] ) ) ? $args[ $var ] : false;
		}

		// Return the args.
		return $args;
	}

	return false;
}
endif;
