<?php
/**
 * Videos AJAX actions.
 *
 * @package AudioTheme\Videos
 * @since 1.9.0
 */

/**
 * Videos AJAX actions class.
 *
 * @pacakge AudioTheme\Videos
 * @since 1.9.0
 */
class AudioTheme_AJAX_Videos {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_audiotheme_get_video_oembed_data', array( $this, 'get_video_oembed_data' ) );
	}

	/**
	 * AJAX method to retrieve the thumbnail for a video.
	 *
	 * @since 1.9.0
	 */
	public function get_video_oembed_data() {
		$post_id = absint( $_POST['post_id'] );
		$json['postId'] = $post_id;

		if ( empty( $_POST['video_url'] ) ) {
			wp_send_json_error();
		}

		$this->sideload_thumbnail( $post_id, esc_url_raw( $_POST['video_url'] ) );

		if ( $thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
			$json['oembedThumbnailId']    = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );
			$json['thumbnailId']          = $thumbnail_id;
			$json['thumbnailUrl']         = wp_get_attachment_url( $thumbnail_id );
			$json['thumbnailMetaBoxHtml'] = _wp_post_thumbnail_html( $thumbnail_id, $post_id );
		}

		wp_send_json_success( $json );
	}

	/**
	 * Import a video thumbnail from an oEmbed endpoint into the media library.
	 *
	 * @todo Consider doing video URL comparison rather than oembed thumbnail
	 *       comparison?
	 *
	 * @since 1.9.0
	 *
	 * @param int    $post_id Video post ID.
	 * @param string $url Video URL.
	 */
	protected function sideload_thumbnail( $post_id, $url ) {
		require_once( ABSPATH . WPINC . '/class-oembed.php' );

		$oembed   = new WP_oEmbed();
		$provider = $oembed->get_provider( $url );

		if (
			! $provider ||
			false === ( $data = $oembed->fetch( $provider, $url ) ) ||
			! isset( $data->thumbnail_url )
		) {
			return;
		}

		$current_thumb_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id  = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );
		$oembed_thumb_url = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', true );

		$thumbnail_url = $data->thumbnail_url;

		// Try to retrieve a higher resolution YouTube thumbnail.
		if ( $this->is_youtube_url( $url ) ) {
			$youtube_thumbnail_url = $this->get_max_youtube_thumbnail( $url );
			if ( ! empty( $youtube_thumbnail_url ) ) {
				$thumbnail_url = $youtube_thumbnail_url;
			}
		}

		// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
		if ( $thumbnail_url === $oembed_thumb_url && ( ! $current_thumb_id || $current_thumb_id !== $oembed_thumb_id ) ) {
			set_post_thumbnail( $post_id, $oembed_thumb_id );
		}

		// Add new thumbnail if the returned URL doesn't match the
		// oEmbed thumb URL or if there isn't a current thumbnail.
		elseif ( ! $current_thumb_id || $thumbnail_url !== $oembed_thumb_url ) {
			$attachment_id = $this->sideload_image( $thumbnail_url, $post_id );

			if ( ! empty( $attachment_id ) && ! is_wp_error( $attachment_id ) ) {
				if ( $this->is_youtube_url( $url ) ) {
					audiotheme_trim_image_letterbox( $attachment_id );
				}

				set_post_thumbnail( $post_id, $attachment_id );

				// Store the oEmbed thumb data so the same image isn't copied on repeated requests.
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', $attachment_id );
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', $thumbnail_url );
			}
		}
	}

	/**
	 * Download an image from the specified URL and attach it to a post.
	 *
	 * @since 1.9.0
	 *
	 * @see media_sideload_image()
	 *
	 * @param string $url The URL of the image to download.
	 * @param int    $post_id The post ID the media is to be associated with.
	 * @param string $desc Optional. Description of the image.
	 * @return int|WP_Error Populated HTML img tag on success.
	 */
	protected function sideload_image( $url, $post_id, $desc = null ) {
		$id = 0;

		if ( ! empty( $url ) ) {
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $url, $matches );

			$file_array             = array();
			$file_array['name']     = basename( $matches[0] );
			$file_array['tmp_name'] = download_url( $url );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
			}
		}

		return $id;
	}

	/**
	 * Whether a URL points to YouTube.
	 *
	 * @since 1.9.0
	 *
	 * @param string $url Video URL.
	 * @return bool
	 */
	protected function is_youtube_url( $url ) {
		return false !== strpos( $url, 'youtube.com' ) || false !== strpos( $url, 'youtu.be' );
	}

	/**
	 * Parse a YouTube URL to retrieve the ID.
	 *
	 * @since 1.9.0
	 *
	 * @link http://stackoverflow.com/a/27728417
	 *
	 * @param string $url Video URL.
	 * @return string
	 */
	function get_youtube_id( $url ) {
		preg_match( '/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))(?<id>[^#\&\?]*).*/', $url, $matches );
		return empty( $matches['id'] ) ? '' : $matches['id'];
	}

	/**
	 * Retrieve the largest possible thumbnail for a YouTube video.
	 *
	 * @since 1.9.0
	 *
	 * @param string $url Video URL.
	 */
	function get_max_youtube_thumbnail( $url ) {
		$video_id      = $this->get_youtube_id( $url );
		$sizes         = array( 'maxresdefault', 'sddefault', 'hqdefault', '0' );
		$thumbnail_url = '';

		foreach ( $sizes as $size ) {
			$test_url = sprintf( 'https://img.youtube.com/vi/%s/%s.jpg', $video_id, $size );
			$response = wp_remote_head( $test_url );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				continue;
			}

			$thumbnail_url = $test_url;
			break;
		}

		return $thumbnail_url;
	}
}
