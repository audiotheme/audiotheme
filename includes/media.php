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
	$wrapped = '<div class="audiotheme-video">' . $html . '</div>';

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
 * Enqueue tracks.
 *
 * Saves basic track data to a global variable so it can be output as
 * JavaScript in the footer for use by scripts.
 *
 * If an associative array representing a track is passed, it should be wrapped
 * in an array itself. IDs and post objects can be passed by themselves or as an
 * array of IDs or objects.
 *
 * Example format of associative array:
 * <code>
 * $track = array(
 *     array(
 *         'title' => '',
 *         'file'  => '',
 *     )
 * )
 * </code>
 *
 * @since 1.1.0
 * @uses $audiotheme_enqueued_tracks
 * @see audiotheme_print_tracks_js()
 * @see audiotheme_prepare_track_for_js()
 *
 * @param int|array|object $track Accepts a track ID, record ID, post object, or array in the expected format.
 * @param string $list A list identifier.
 */
function enqueue_audiotheme_tracks( $track, $list = 'tracks' ) {
	global $audiotheme_enqueued_tracks;

	$key = sanitize_key( $list );
	if ( ! isset( $audiotheme_enqueued_tracks[ $key ] ) ) {
		$audiotheme_enqueued_tracks[ $key ] = array();
	}

	$audiotheme_enqueued_tracks[ $key ] = array_merge( $audiotheme_enqueued_tracks[ $key ], (array) $track );
}

/**
 * Transform a track id or array of data into the expected format for use as a
 * JavaScript object.
 *
 * @since 1.1.0
 *
 * @param int|array $track Track ID or array of expected track properties.
 * @return array
 */
function audiotheme_prepare_track_for_js( $track ) {
	$data = array(
		'artist'  => '',
		'artwork' => '',
		'mp3'     => '',
		'record'  => '',
		'title'   => '',
	);

	// Enqueue a track post type.
	if ( 'audiotheme_track' == get_post_type( $track ) ) {
		$track = get_post( $track );
		$record = get_post( $track->post_parent );

		$data['artist'] = get_audiotheme_track_artist( $track->ID );
		$data['mp3'] = get_audiotheme_track_file_url( $track->ID );
		$data['record'] = $record->post_title;
		$data['title'] = $track->post_title;

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
			$data['mp3'] = esc_url( $track['file'] );
		}

		if ( isset( $track['mp3'] ) ) {
			$data['mp3'] = esc_url( $track['mp3'] );
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
 * Convert enqueue track lists into an array of tracks prepared for JavaScript
 * and output the JSON-encoded object in the footer.
 *
 * @since 1.1.0
 */
function audiotheme_print_tracks_js() {
	global $audiotheme_enqueued_tracks;

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
			if ( 'audiotheme_record' == get_post_type( $track ) ) {
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
		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "var AudiothemeTracks = " . json_encode( $lists ) . ";\n";
		echo "/* ]]> */\n";
		echo "</script>\n";
	}
}
