<?php
/**
 * Edit Video administration screen integration.
 *
 * @package AudioTheme\Videos
 * @since 1.9.0
 */

/**
 * Class providing integration with the Edit Video administration screen.
 *
 * @package AudioTheme\Videos
 * @since 1.9.0
 */
class AudioTheme_Screen_EditVideo {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',              array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',          array( $this, 'load_screen' ) );
		add_action( 'admin_enqueue_scripts',      array( $this, 'register_assets' ), 1 );
		add_filter( 'admin_post_thumbnail_html',  array( $this, 'admin_post_thumbnail_html' ), 10, 2 );
		add_action( 'save_post_audiotheme_video', array( $this, 'on_video_save' ), 10, 2 );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.9.0
	 */
	public function load_screen() {
		if ( 'audiotheme_video' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_title', array( $this, 'display_video_url_field' ) );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 1.9.0
	 */
	public function register_assets() {
		wp_register_script(
			'audiotheme-video-edit',
			AUDIOTHEME_URI . 'modules/videos/admin/js/video-edit.js',
			array( 'jquery', 'post', 'wp-backbone', 'wp-util' ),
			'1.9.0',
			true
		);
	}

	/**
	 * Enqueue assets for the Edit Venue screen.
	 *
	 * @since 1.9.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-video-edit' );
	}

	/**
	 * Display a field to enter a video URL after the post title.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_video_url_field( $post ) {
		$video = get_audiotheme_video_url( $post->ID );
		wp_nonce_field( 'save-video-meta_' . $post->ID, 'audiotheme_save_video_meta_nonce', false );
		?>
		<div class="audiotheme-edit-after-title" style="position: relative">
			<p>
				<label for="audiotheme-video-url" class="screen-reader-text"><?php esc_html_e( 'Video URL:', 'audiotheme' ); ?></label>
				<input type="text" name="_video_url" id="audiotheme-video-url" value="<?php echo esc_url( $video ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'audiotheme' ); ?>" class="widefat"><br>

				<span class="description">
					<?php
					printf(
						__( 'Enter a video URL from a <a href="%s" target="_blank">supported video service</a>.', 'audiotheme' ),
						'https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F'
					);
					?>
				</span>
			</p>
		</div>
		<?php
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
	 *
	 * @param string $content Default post thumbnail HTML.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	public function admin_post_thumbnail_html( $content, $post_id ) {
		if ( 'audiotheme_video' !== get_post_type( $post_id ) ) {
			return $content;
		}

		$data = array(
			'thumbnailId'       => get_post_thumbnail_id( $post_id ),
			'oembedThumbnailId' => get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true ),
		);

		ob_start();
		?>
		<p id="audiotheme-select-oembed-thumb" class="hide-if-no-js">
			<a href="#" id="audiotheme-select-oembed-thumb-button"><?php esc_html_e( 'Get video thumbnail', 'audiotheme' ); ?></a>
			<span class="spinner"></span>
		</p>
		<script id="audiotheme-video-thumbnail-data" type="application/json"><?php echo wp_json_encode( $data ); ?></script>
		<script>if ( '_audiothemeVideoThumbnailPing' in window ) { _audiothemeVideoThumbnailPing(); }</script>
		<?php
		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Process and save video info when the CPT is saved.
	 *
	 * @since 1.9.0
	 *
	 * @param int     $post_id Video post ID.
	 * @param WP_Post $post Video post object.
	 */
	public function on_video_save( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_save_video_meta_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_video_meta_nonce'], 'save-video-meta_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		if ( isset( $_POST['_video_url'] ) ) {
			update_post_meta( $post_id, '_audiotheme_video_url', esc_url_raw( $_POST['_video_url'] ) );
		}
	}
}
