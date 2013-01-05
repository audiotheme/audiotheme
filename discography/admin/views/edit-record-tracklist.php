<table id="record-tracklist" class="audiotheme-repeater audiotheme-edit-after-editor widefat" data-item-template-id="audiotheme-track">
	<thead>
		<tr>
			<th colspan="5"><?php _e( 'Tracks', 'audiotheme-i18n' ) ?></th>
			<?php
			// @todo Add a button to the table footer for adding tracks.
			// @todo Only show if the user has the required capability.
			?>
			<th class="column-action"><a class="button audiotheme-repeater-add-item"><?php _e( 'Add Track', 'audiotheme-i18n' ) ?></a></th>
		</tr>
	</thead>

	<tfoot>
	    <tr class="audiotheme-repeater-sort-warning" style="display: none;">
	    	<td colspan="6">
	    		<?php
	    		printf( '<span>%1$s <em>%2$s</em></span>',
	    			esc_html__( 'The order has been changed.', 'audiotheme-i18n' ),
	    			esc_html__( 'Save your changes.', 'audiotheme-i18n' )
	    		);
	    		?>
	    	</td>
	    </tr>
	</tfoot>

	<tbody class="audiotheme-repeater-items is-empty">
		<tr>
			<td colspan="6"><?php echo get_post_type_object( 'audiotheme_track' )->labels->not_found; ?></td>
		</tr>
	</tbody>
</table>

<style type="text/css">
.audiotheme-repeater input:-moz-placeholder { color: #a9a9a9;}
.audiotheme-repeater tbody tr { cursor: move; width: 100%; }
.audiotheme-repeater tbody tr:last-child td { border-bottom: none; }
.audiotheme-repeater tbody td { padding: 7px; }
.audiotheme-repeater tbody td.track-number { width: 2em; text-align: right; vertical-align: middle;}
.audiotheme-repeater .column-action { width: 16px; cursor: auto; text-align: right; vertical-align: middle;}
.audiotheme-repeater .column-action a { cursor: pointer; font-family: sans-serif;}
.audiotheme-repeater .column-action .audiotheme-repeater-remove-item { opacity: .2;}
.audiotheme-repeater .column-action .audiotheme-repeater-remove-item:hover { opacity: 1;}
.audiotheme-repeater .audiotheme-show-on-add { display: none;}
.audiotheme-repeater-sort-warning td { padding: 10px; color: #ff0000; border-top: 1px solid #dfdfdf; border-bottom: none;}

.audiotheme-repeater .column-track-info { width: 48px; font-size: 12px; vertical-align: middle;}
.audiotheme-repeater .column-track-info span { padding: 0 3px;}

#record-tracklist.audiotheme-repeater tbody tr.audiotheme-repeater-active-item td { background: #ececec; border-top-color: #eee;}
#record-tracklist.audiotheme-repeater .ui-sortable-helper { background: #f9f9f9; border-top: 1px solid #dfdfdf; border-bottom: 1px solid #dfdfdf;}
#record-tracklist.audiotheme-repeater .ui-sortable-helper td { border-top-width: 0; border-bottom-width: 0;}

#record-tracklist .audiotheme-input-append { overflow: hidden; width: 100%;}
#record-tracklist .audiotheme-input-append input { float: left; padding-right: 34px;}
#record-tracklist .audiotheme-input-append-trigger { float: left; margin: 0 0 0 -30px; width: 30px; height: 23px; line-height: 23px; border-left-width: 1px;}
</style>

<script type="text/html" id="tmpl-audiotheme-track">
	<tr class="audiotheme-repeater-item">
		<td class="track-number">
			<span class="audiotheme-repeater-index"></span>
			<input type="hidden" name="audiotheme_tracks[__i__][post_id]" value="{{ data.id }}" class="post-id audiotheme-clear-on-add">
		</td>
		<td><input type="text" name="audiotheme_tracks[__i__][title]" placeholder="<?php esc_attr_e( 'Title', 'audiotheme-i18n' ) ?>" value="{{ data.title }}" class="widefat audiotheme-clear-on-add"></td>
		<td><input type="text" name="audiotheme_tracks[__i__][artist]" placeholder="<?php esc_attr_e( 'Artist', 'audiotheme-i18n' ) ?>" value="{{ data.artist }}" class="widefat"></td>
		<td>
			<div class="audiotheme-input-append">
				<input type="text" name="audiotheme_tracks[__i__][file_url]" id="track-file-url-__i__" placeholder="<?php esc_attr_e( 'File URL', 'audiotheme-i18n' ) ?>" value="{{ data.fileUrl }}" class="widefat audiotheme-clear-on-add">
				<a href="<?php echo esc_url( $thickbox_url ); ?>" title="<?php esc_attr_e( 'Choose a MP3', 'audiotheme-i18n' ); ?>" class="thickbox audiotheme-input-append-trigger" data-insert-field="track-file-url-__i__" data-insert-button-text="<?php esc_attr_e( 'Use MP3', 'audiotheme-i18n' ) ?>"><img src="<?php echo AUDIOTHEME_URI; ?>admin/images/music-note.png" width="12" height="12"></a>
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
			<a href="<?php echo admin_url( 'post.php' ); ?>?post={{ data.id }}&amp;action=edit" class="audiotheme-remove-on-add"><?php esc_html_e( 'Edit', 'audiotheme-i18n' ); ?></a>
			<a class="audiotheme-repeater-remove-item audiotheme-show-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="<?php esc_attr_e( 'Delete Item', 'audiotheme-i18n' ) ?>" title="<?php esc_attr_e( 'Delete Item', 'audiotheme-i18n' ) ?>" class="icon-delete"></a>
		</td>
	</tr>
</script>

<script type="text/javascript">
jQuery(function($) {
	var tracklist = <?php echo ( empty( $tracks ) ) ? 'null' : json_encode( $tracks ); ?>;

	// @todo Send and manage a nonce.
	$('#record-tracklist').audiothemeRepeater({ items: tracklist })
		.on('addItem.audiotheme', function( e, track ) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'audiotheme_ajax_get_default_track'
				},
				dataType: 'json',
				success: function( data ) {
					track.find('input.post-id').val( data.ID );
				}
			});
		});
});
</script>