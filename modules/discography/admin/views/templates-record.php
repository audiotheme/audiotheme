<script type="text/html" id="tmpl-audiotheme-track">
	<tr class="audiotheme-repeater-item">
		<td class="track-number">
			<span class="audiotheme-repeater-index"></span>
			<input type="hidden" name="audiotheme_tracks[__i__][post_id]" value="{{ data.id }}" class="post-id audiotheme-clear-on-add">
		</td>
		<td><input type="text" name="audiotheme_tracks[__i__][title]" placeholder="<?php esc_attr_e( 'Title', 'audiotheme' ) ?>" value="{{{ data.title }}}" class="audiotheme-tracklist-track-title widefat audiotheme-clear-on-add"></td>
		<td><input type="text" name="audiotheme_tracks[__i__][artist]" placeholder="<?php esc_attr_e( 'Artist', 'audiotheme' ) ?>" value="{{{ data.artist }}}" class="audiotheme-tracklist-track-artist widefat"></td>
		<td>
			<div class="audiotheme-media-control audiotheme-input-group"
				data-title="<?php esc_attr_e( 'Choose an MP3', 'audiotheme' ); ?>"
				data-update-text="<?php esc_attr_e( 'Update MP3', 'audiotheme' ); ?>"
				data-file-type="audio/mpeg"
				data-upload-extensions="mp3"
				data-target=".track-file-url"
				data-return-property="url">
				<input type="text" name="audiotheme_tracks[__i__][file_url]" id="track-file-url-__i__" placeholder="<?php esc_attr_e( 'File URL', 'audiotheme' ) ?>" value="{{ data.fileUrl }}" class="track-file-url audiotheme-input-group-field widefat audiotheme-clear-on-add">
				<a href="#" class="audiotheme-media-control-choose audiotheme-input-group-trigger dashicons dashicons-format-audio"></a>
			</div>
		</td>
		<td class="column-track-info">
			<# if ( data.isDownloadable ) { #>
				<span class="has-download audiotheme-remove-on-add dashicons dashicons-download"></span>
			<# } #>

			<# if ( data.purchaseUrl ) { #>
				<span class="has-purchase-url audiotheme-remove-on-add dashicons dashicons-cart"></span>
			<# } #>
			&nbsp;
		</td>
		<td class="column-action">
			<a href="<?php echo admin_url( 'post.php' ); ?>?post={{ data.id }}&amp;action=edit" class="audiotheme-remove-on-add"><?php esc_html_e( 'Edit', 'audiotheme' ); ?></a>
			<a class="audiotheme-repeater-remove-item audiotheme-show-on-add"><span class="dashicons dashicons-trash"></span></a>
		</td>
	</tr>
</script>
