<?php
/**
 * Edit Record Archive administration screen integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class providing integration with the Edit Record Archive administration screen.
 *
 * @package AudioTheme\Discography
 * @since   2.0.0
 */
class AudioTheme_Screen_EditRecordArchive extends AudioTheme_Screen_AbstractScreen{
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_record', '__return_true' );
		add_action( 'save_audiotheme_archive_settings',     array( $this, 'on_save' ), 10, 3 );
		add_action( 'audiotheme_archive_settings_meta_box', array( $this, 'display_orderby_field' ) );
	}

	/**
	 * Save record archive sort order.
	 *
	 * The $post_id and $post parameters will refer to the archive CPT, while the
	 * $post_type parameter references the type of post the archive is for.
	 *
	 * @since 2.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param string  $post_type The type of post the archive lists.
	 */
	public function on_save( $post_id, $post, $post_type ) {
		if ( 'audiotheme_record' !== $post_type ) {
			return;
		}

		$orderby = isset( $_POST['audiotheme_orderby'] ) ? sanitize_text_field( $_POST['audiotheme_orderby'] ) : '';
		update_post_meta( $post_id, 'orderby', $orderby );
	}

	/**
	 * Add an orderby setting to the record archive.
	 *
	 * Allows for changing the sort order of videos. Custom would require a plugin
	 * like Simple Page Ordering.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_orderby_field( $post ) {
		$post_type = is_audiotheme_post_type_archive_id( $post->ID );
		if ( 'audiotheme_record' !== $post_type ) {
			return;
		}

		$options = array(
			'release_year' => esc_html__( 'Release Year', 'audiotheme' ),
			'title'        => esc_html__( 'Title', 'audiotheme' ),
			'custom'       => esc_html__( 'Custom', 'audiotheme' ),
		);

		$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', 'audiotheme_record' );
		?>
		<p>
			<label for="audiotheme-orderby"><?php esc_html_e( 'Order by:', 'audiotheme' ); ?></label>
			<select name="audiotheme_orderby" id="audiotheme-orderby">
				<?php
				foreach ( $options as $id => $value ) {
					printf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $id ),
						selected( $id, $orderby, false ),
						esc_html( $value )
					);
				}
				?>
			</select>
		</p>
		<?php
	}
}
