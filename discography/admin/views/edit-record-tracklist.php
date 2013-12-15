<table id="record-tracklist" class="audiotheme-repeater audiotheme-edit-after-editor widefat" data-item-template-id="audiotheme-track">
	<thead>
		<tr>
			<th colspan="5"><?php _e( 'Tracks', 'audiotheme' ) ?></th>
			<th class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php _e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</th>
		</tr>
	</thead>

	<tfoot>
	    <tr>
	    	<td colspan="5">
	    		<?php
	    		printf( '<span class="audiotheme-repeater-sort-warning" style="display: none">%1$s <em>%2$s</em></span>',
	    			esc_html__( 'The order has been changed.', 'audiotheme' ),
	    			esc_html__( 'Save your changes.', 'audiotheme' )
	    		);
	    		?>
	    	</td>
			<td class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php _e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</td>
	    </tr>
	</tfoot>

	<tbody class="audiotheme-repeater-items is-empty">
		<tr>
			<td colspan="6"><?php echo get_post_type_object( 'audiotheme_track' )->labels->not_found; ?></td>
		</tr>
	</tbody>
</table>

<script type="text/html" id="tmpl-audiotheme-track">
	<tr class="audiotheme-repeater-item">
		<td class="track-number">
			<span class="audiotheme-repeater-index"></span>
			<input type="hidden" name="audiotheme_tracks[__i__][post_id]" value="{{ data.id }}" class="post-id audiotheme-clear-on-add">
		</td>
		<td><input type="text" name="audiotheme_tracks[__i__][title]" placeholder="<?php esc_attr_e( 'Title', 'audiotheme' ) ?>" value="{{{ data.title }}}" class="widefat audiotheme-clear-on-add"></td>
		<td><input type="text" name="audiotheme_tracks[__i__][artist]" placeholder="<?php esc_attr_e( 'Artist', 'audiotheme' ) ?>" value="{{{ data.artist }}}" class="widefat"></td>
		<td>
			<div class="audiotheme-media-control audiotheme-input-append"
				data-title="<?php esc_attr_e( 'Choose an MP3', 'audiotheme' ); ?>"
				data-update-text="<?php esc_attr_e( 'Update MP3', 'audiotheme' ); ?>"
				data-file-type="audio"
				data-upload-extensions="mp3"
				data-target=".track-file-url"
				data-return-property="url">
				<input type="text" name="audiotheme_tracks[__i__][file_url]" id="track-file-url-__i__" placeholder="<?php esc_attr_e( 'File URL', 'audiotheme' ) ?>" value="{{ data.fileUrl }}" class="track-file-url widefat audiotheme-clear-on-add">
				<a href="#" class="audiotheme-media-control-choose audiotheme-input-append-trigger"><img src="<?php echo AUDIOTHEME_URI; ?>admin/images/music-note.png" width="12" height="12" alt="<?php esc_attr_e( 'Choose MP3', 'audiotheme' ); ?>"></a>
			</div>
		</td>
		<td class="column-track-info">
			<# if ( data.downloadable ) { #>
				<span class="has-download audiotheme-remove-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>admin/images/download.png" width="12" height="12"></span>
			<# } #>

			<# if ( data.purchaseUrl ) { #>
				<span class="has-purchase-url audiotheme-remove-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>admin/images/buy.png" width="12" height="12"></span>
			<# } #>
			&nbsp;
		</td>
		<td class="column-action">
			<a href="<?php echo admin_url( 'post.php' ); ?>?post={{ data.id }}&amp;action=edit" class="audiotheme-remove-on-add"><?php esc_html_e( 'Edit', 'audiotheme' ); ?></a>
			<a class="audiotheme-repeater-remove-item audiotheme-show-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="<?php esc_attr_e( 'Delete Item', 'audiotheme' ) ?>" title="<?php esc_attr_e( 'Delete Item', 'audiotheme' ) ?>" class="icon-delete"></a>
		</td>
	</tr>
</script>

<script type="text/javascript">
jQuery(function($) {
	var tracklist = <?php echo ( empty( $tracks ) ) ? 'null' : json_encode( $tracks ); ?>,
		tracklistNonce = '<?php echo wp_create_nonce( 'get-default-track_' . $post->ID ); ?>';

	$('#record-tracklist').audiothemeRepeater({ items: tracklist })
		.on('addItem.audiotheme', function( e, track ) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'audiotheme_ajax_get_default_track',
					record: <?php echo $post->ID; ?>,
					nonce: tracklistNonce
				},
				dataType: 'json',
				success: function( data ) {
					track.find('input.post-id').val( data.track.ID );
					tracklistNonce = data.nonce;
				}
			});
		});
});
</script>
