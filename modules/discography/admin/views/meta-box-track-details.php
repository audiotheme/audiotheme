<?php
/**
 * View for the track details meta box.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */
?>


<p class="audiotheme-field">
	<label for="track-artist"><?php esc_html_e( 'Artist:', 'audiotheme' ) ?></label>
	<input type="text" name="artist" id="track-artist" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_artist', true ) ); ?>" class="widefat">
</p>

<p class="audiotheme-field audiotheme-media-control audiotheme-field-upload"
	data-title="<?php esc_attr_e( 'Choose an MP3', 'audiotheme' ); ?>"
	data-update-text="<?php esc_attr_e( 'Use MP3', 'audiotheme' ); ?>"
	data-target="#track-file-url"
	data-return-property="url"
	data-file-type="audio">
	<label for="track-file-url"><?php esc_html_e( 'Audio File URL:', 'audiotheme' ) ?></label>
	<input type="url" name="file_url" id="track-file-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_file_url', true ) ); ?>" class="widefat">

	<input type="checkbox" name="is_downloadable" id="track-is-downloadable" value="1"<?php checked( get_post_meta( $post->ID, '_audiotheme_is_downloadable', true ) ); ?>>
	<label for="track-is-downloadable"><?php esc_html_e( 'Allow downloads?', 'audiotheme' ) ?></label>

	<button class="button audiotheme-media-control-choose" style="float: right"><?php esc_html_e( 'Upload MP3', 'audiotheme' ); ?></button>
</p>

<p class="audiotheme-field">
	<label for="track-length"><?php esc_html_e( 'Length:', 'audiotheme' ) ?></label>
	<input type="text" name="length" id="track-length" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_length', true ) ); ?>" placeholder="00:00" class="widefat">
</p>

<p class="audiotheme-field">
	<label for="track-purchase-url"><?php esc_html_e( 'Purchase URL:', 'audiotheme' ) ?></label>
	<input type="url" name="purchase_url" id="track-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_audiotheme_purchase_url', true ) ); ?>" class="widefat">
</p>

<?php
if ( ! get_post( $post->post_parent ) ) {
	$records = get_posts( 'post_type=audiotheme_record&orderby=title&order=asc&posts_per_page=-1' );
	if ( $records ) {
		echo '<p class="audiotheme-field">';
			echo '<label for="post-parent">' . esc_html__( 'Record:', 'audiotheme' ) . '</label>';
			echo '<select name="post_parent" id="post-parent" class="widefat">';
				echo '<option value=""></option>';

		foreach ( $records as $record ) {
			printf(
				'<option value="%s">%s</option>',
				absint( $record->ID ),
				esc_html( $record->post_title )
			);
		}

			echo '</select>';
			echo '<span class="description">' . esc_html__( 'Associate this track with a record.', 'audiotheme' ) . '</span>';
		echo '</p>';
	}
}
