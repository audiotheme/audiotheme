<?php
/**
 * Edit Gig administration screen integration.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */

/**
 * Class providing integration with the Edit Gig administration screen.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */
class AudioTheme_Screen_EditGig {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',                 array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',             array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_gig', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_audiotheme_gig',      array( $this, 'on_gig_save' ), 10, 2 );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.9.0
	 */
	public function load_screen() {
		if ( 'audiotheme_gig' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_title', array( $this, 'display_edit_fields' ) );
		add_action( 'admin_footer',          array( $this, 'print_templates' ) );
	}

	/**
	 * Register gig meta boxes.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post The gig post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box(
			'audiotheme-gig-tickets',
			esc_html__( 'Tickets', 'audiotheme' ),
			array( $this, 'display_tickets_meta_box' ),
			'audiotheme_gig',
			'side',
			'default'
		);
	}

	/**
	 * Enqueue assets for the Edit Gig screen.
	 *
	 * @since 1.9.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-gig-edit' );
		wp_enqueue_style( 'audiotheme-admin' );
		wp_enqueue_style( 'audiotheme-venue-manager' );
		wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	}

	/**
	 * Set up and display the main gig fields for editing.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_edit_fields( $post ) {
		$gig = get_audiotheme_gig( $post->ID );

		$gig_date  = '';
		$gig_time  = '';

		if ( $gig->gig_datetime ) {
			$timestamp = strtotime( $gig->gig_datetime );
			// The jQuery date format is kinda limited.
			$gig_date = date( 'Y/m/d', $timestamp );

			$t = date_parse( $gig->gig_time );
			if ( empty( $t['errors'] ) ) {
				$gig_time = date( $this->compatible_time_format(), $timestamp );
			}
		}

		$gig_venue       = isset( $gig->venue->name ) ? $gig->venue->name : '';
		$timezone_string = isset( $gig->venue->timezone_string ) ? $gig->venue->timezone_string : '';
		$venue_id        = isset( $gig->venue->ID ) ? $gig->venue->ID : 0;

		wp_localize_script( 'audiotheme-gig-edit', '_audiothemeGigEditSettings', array(
			'venue'      => prepare_audiotheme_venue_for_js( $venue_id ),
			'timeFormat' => $this->compatible_time_format(),
		) );

		require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-gig.php' );
	}

	/**
	 * Gig tickets meta box.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post The gig post object being edited.
	 */
	public function display_tickets_meta_box( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="gig-tickets-price"><?php esc_html_e( 'Price:', 'audiotheme' ); ?></label><br>
			<input type="text" name="gig_tickets_price" id="gig-tickets-price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_price', true ) ); ?>" class="large-text">
		</p>
		<p class="audiotheme-field">
			<label for="gig-tickets-url"><?php esc_html_e( 'Tickets URL:', 'audiotheme' ); ?></label><br>
			<input type="text" name="gig_tickets_url" id="gig-tickets-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_url', true ) ); ?>" class="large-text">
		</p>
		<?php
	}

	/**
	 * Print Underscore.js templates.
	 *
	 * @since 1.9.0
	 */
	public function print_templates() {
		include( AUDIOTHEME_DIR . 'modules/gigs/admin/views/templates-gig.php' );
		include( AUDIOTHEME_DIR . 'modules/gigs/admin/views/templates-venue.php' );
	}

	/**
	 * Process and save gig info when the CPT is saved.
	 *
	 * @since 1.9.0
	 *
	 * @param int     $post_id Gig post ID.
	 * @param WP_Post $post Gig post object.
	 */
	public function on_gig_save( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_save_gig_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_gig_nonce'], 'save-gig_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$post_type_object = get_post_type_object( 'audiotheme_gig' );
		if ( isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] ) && current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
			set_audiotheme_gig_venue_id( $post_id, absint( $_POST['gig_venue_id'] ) );

			$dt = date_parse( $_POST['gig_date'] . ' ' . $_POST['gig_time'] );

			// Date and time are always stored local to the venue.
			// If GMT, or time in another locale is needed, use the venue time zone to calculate.
			// Other functions should be aware that time is optional; check for the presence of gig_time.
			if ( checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
				$datetime = sprintf( '%d-%s-%s %s:%s:%s',
					$dt['year'],
					zeroise( $dt['month'], 2 ),
					zeroise( $dt['day'], 2 ),
					zeroise( $dt['hour'], 2 ),
					zeroise( $dt['minute'], 2 ),
				zeroise( $dt['second'], 2 ) );

				update_post_meta( $post_id, '_audiotheme_gig_datetime', $datetime );

				// If the post name is empty, default it to the date.
				if ( empty( $post->post_name ) ) {
					wp_update_post( array(
						'ID'        => $post->ID,
						'post_name' => sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) ),
					) );
				}
			} else {
				update_post_meta( $post_id, '_audiotheme_gig_datetime', '' );
			}

			// Store time separately to check for empty values, TBA, etc.
			$time = $_POST['gig_time'];
			$t = date_parse( $time );
			if ( empty( $t['errors'] ) ) {
				$time = sprintf( '%s:%s:%s',
					zeroise( $t['hour'], 2 ),
					zeroise( $t['minute'], 2 ),
				zeroise( $t['second'], 2 ) );
			}

			update_post_meta( $post_id, '_audiotheme_gig_time', $time );
			update_post_meta( $post_id, '_audiotheme_tickets_price', sanitize_text_field( $_POST['gig_tickets_price'] ) );
			update_post_meta( $post_id, '_audiotheme_tickets_url', esc_url_raw( $_POST['gig_tickets_url'] ) );
		}
	}

	/**
	 * Attempt to make custom time formats more compatible between JavaScript and PHP.
	 *
	 * If the time format option has an escape sequences, use a default format
	 * determined by whether or not the option uses 24 hour format or not.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	protected function compatible_time_format() {
		$time_format = get_option( 'time_format' );

		if ( false !== strpos( $time_format, '\\' ) ) {
			$time_format = false !== strpbrk( $time_format, 'GH' ) ? 'G:i' : 'g:i a';
		}

		return $time_format;
	}
}
