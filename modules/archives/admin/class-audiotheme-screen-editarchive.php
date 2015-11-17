<?php
/**
 * Edit Archive administration screen integration.
 *
 * @package AudioTheme\Archives
 * @since 1.9.0
 */

/**
 * Class providing integration with the Edit Archive administration screen.
 *
 * @package AudioTheme\Archives
 * @since 1.9.0
 */
class AudioTheme_Screen_EditArchive {
	/**
	 * Archives module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Archives
	 */
	protected $module;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Archives $module Archives module.
	 */
	public function __construct( AudioTheme_Module_Archives $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes_audiotheme_archive',    array( $this, 'add_meta_boxes' ) );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'settings_meta_box_fields' ), 15, 3 );
		add_action( 'save_post',                            array( $this, 'on_archive_save' ), 10, 2 );
	}

	/**
	 * Replace the submit meta box to remove unnecessary fields.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function add_meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', 'audiotheme_archive', 'side' );

		add_meta_box(
			'submitdiv',
			esc_html__( 'Update', 'audiotheme' ),
			'audiotheme_post_submit_meta_box',
			'audiotheme_archive',
			'side',
			'high',
			array(
				'force_delete'      => false,
				'show_publish_date' => false,
				'show_statuses'     => false,
				'show_visibility'   => false,
			)
		);

		$post_type = $this->module->is_archive_id( $post->ID );

		// Activate the default archive settings meta box.
		$show = apply_filters( 'add_audiotheme_archive_settings_meta_box', false, $post_type );
		$show_for_post_type = apply_filters( 'add_audiotheme_archive_settings_meta_box_' . $post_type, false );

		// Show if any settings fields have been registered for the post type.
		$fields = $this->module->get_settings_fields( $post_type );

		if ( $show || $show_for_post_type || ! empty( $fields ) ) {
			add_meta_box(
				'audiothem-archive-settings',
				esc_html__( 'Archive Settings', 'audiotheme' ),
				array( $this, 'display_settings_meta_box' ),
				'audiotheme_archive',
				'side',
				'default',
				array(
					'fields' => $fields,
				)
			);
		}
	}

	/**
	 * Display archive settings meta box.
	 *
	 * The meta box needs to be activated first, then fields can be displayed
	 * using one of the actions.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Archive post.
	 * @param array   $args Meta box arguments.
	 */
	public function display_settings_meta_box( $post, $args = array() ) {
		$post_type = $this->module->is_archive_id( $post->ID );
		wp_nonce_field( 'save-archive-meta_' . $post->ID, 'audiotheme_archive_nonce' );
		do_action( 'audiotheme_archive_settings_meta_box', $post, $post_type, $args['args']['fields'] );
		do_action( 'audiotheme_archive_settings_meta_box_' . $post_type, $post, $args['args']['fields'] );
	}

	/**
	 * Add fields to the archive settings meta box.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Archive post.
	 * @param string  $post_type Post type name.
	 * @param array   $fields Settings fields.
	 */
	public function settings_meta_box_fields( $post, $post_type, $fields = array() ) {
		if ( empty( $fields ) ) {
			return;
		}

		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			$value = $this->module->get_archive_meta( 'posts_per_archive_page', true, '', $post_type );
			?>
			<p>
				<label for="audiotheme-posts-per-archive-page"><?php _e( 'Posts per page:', 'audiotheme' ); ?></label>
				<input type="text" name="posts_per_archive_page" id="audiotheme-posts-per-archive-page" value="<?php echo esc_attr( $value ); ?>" class="small-text">
			</p>
			<?php
		}

		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$default = empty( $fields['columns']['default'] ) ? 4 : absint( $fields['columns']['default'] );
			$value   = $this->module->get_archive_meta( 'columns', true, $default, $post_type );
			$choices = range( 3, 5 );

			if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
				$choices = $fields['columns']['choices'];
			}
			?>
			<p>
				<label for="audiotheme-columns"><?php _e( 'Columns:', 'audiotheme' ); ?></label>
				<select name="columns" id="audiotheme-columns">
					<?php
					foreach ( $choices as $number ) {
						$number = absint( $number );

						printf( '<option value="%1$d"%2$s>%1$d</option>',
							$number,
							selected( $number, $value, false )
						);
					}
					?>
				</select>
			</p>
			<?php
		}
	}

	/**
	 * Save archive meta data.
	 *
	 * @since 1.9.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function on_archive_save( $post_id, $post ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_archive_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_archive_nonce'], 'save-archive-meta_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$post_type = $this->module->is_archive_id( $post->ID );
		do_action( 'save_audiotheme_archive_settings', $post_id, $post, $post_type );

		$fields = $this->module->get_settings_fields( $post_type );

		// Sanitize and save the posts per archive page setting.
		if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
			$posts_per_archive_page = is_numeric( $_POST['posts_per_archive_page'] ) ? intval( $_POST['posts_per_archive_page'] ) : '';
			update_post_meta( $post_id, 'posts_per_archive_page', $posts_per_archive_page );
		}

		// Sanitize and save the columns setting.
		if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
			$choices = range( 3, 5 );
			if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
				$choices = array_map( 'absint', $fields['columns']['choices'] );
			}

			$value = absint( $_POST['columns'] );
			if ( ! in_array( $value, $choices ) ) {
				$choices_min = min( $choices );
				$choices_max = max( $choices );
				$value       = min( max( $value, $choices_min ), $choices_max );
			}

			update_post_meta( $post_id, 'columns', $value );
		}
	}
}
