<table class="widefat meta-repeater" id="record-tracklist">
	<thead>
		<tr>
			<th colspan="5"><?php _e( 'Tracks', 'audiotheme-i18n' ) ?></th>
			<th class="column-action"><a class="button meta-repeater-add-item"><?php _e( 'Add Track', 'audiotheme-i18n' ) ?></a></th>
		</tr>
	</thead>
	
	<tfoot>
	    <tr class="meta-repeater-sort-warning" style="display: none;">
	    	<td colspan="6">
	    		<?php printf( '<span>%1$s <em>%2$s</em></span>',
	    			esc_html__( 'The order has been changed.', 'audiotheme-i18n' ),
	    			esc_html__( 'Save your changes.', 'audiotheme-i18n' )
	    		); ?>
	    	</td>
	    </tr>
	</tfoot>
	
	<tbody class="meta-repeater-items">
		<?php foreach( $tracks as $key => $track ) : ?>
			<tr class="meta-repeater-item">
				<td class="track-number">
					<input type="hidden" name="audiotheme_tracks[<?php echo $key; ?>][post_id]" value="<?php echo $track->ID; ?>" class="clear-on-add">
					<span class="meta-repeater-index"><?php echo $key + 1 . '.'; ?></span>
				</td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][title]" placeholder="<?php _e( 'Title', 'audiotheme-i18n' ) ?>" value="<?php echo esc_attr( $track->post_title ); ?>" class="widefat clear-on-add"></td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][artist]" placeholder="<?php _e( 'Artist', 'audiotheme-i18n' ) ?>" value="<?php echo esc_attr( get_post_meta( $track->ID, '_audiotheme_artist', true ) ); ?>" class="widefat"></td>
				<td>
					<?php
					$field_id = 'track-file-url-' . $key;
					
					$tb_args = array( 
						'post_id' => $post->ID, 
						'type' => 'audio', 
						'TB_iframe' => true, 
						'width' => 640, 
						'height' => 750 
					);
					
					$tb_url = add_query_arg( $tb_args, admin_url( 'media-upload.php' ) );
					?>
					<div class="audiotheme-input-append">
						<input type="text" name="audiotheme_tracks[<?php echo $key; ?>][file_url]" id="<?php echo $field_id; ?>" placeholder="<?php _e( 'File URL', 'audiotheme-i18n' ) ?>" value="<?php echo esc_attr( get_post_meta( $track->ID, '_audiotheme_file_url', true ) ); ?>" class="widefat clear-on-add">
						<a href="<?php echo esc_url( $tb_url ); ?>" title="<?php _e( 'Choose a MP3', 'audiotheme-i18n' ); ?>" class="thickbox audiotheme-input-append-trigger" data-insert-field="<?php echo $field_id; ?>" data-insert-button-text="<?php _e( 'Use MP3', 'audiotheme-i18n' ) ?>"><img src="<?php echo AUDIOTHEME_URI; ?>admin/images/music-note.png" width="12" height="12"></a>
					</div>
				</td>
				<td class="column-track-info">
					<?php
					if ( $track->ID && is_audiotheme_track_downloadable( $track->ID ) ) {
						echo '<span class="has-download remove-on-add"><img src="' . AUDIOTHEME_URI . 'admin/images/download.png" width="12" height="12"></span>';
					}
					
					if ( $track->ID && $purchase_url = get_post_meta( $track->ID, '_audiotheme_purchase_url', true ) ) {
						echo '<span class="has-purchase-url remove-on-add"><img src="' . AUDIOTHEME_URI . 'admin/images/buy.png" width="12" height="12"></span>';
					}
					?>
					&nbsp;
				</td>
				<td class="column-action">
					<?php
					if ( $track->ID ) {
						$args = array( 'post' => $track->ID, 'action' => 'edit' );
						printf( '<a href="%1$s" class="remove-on-add">%2$s</a>',
							esc_url( add_query_arg( $args, admin_url( 'post.php' ) ) ),
							esc_html__( 'Edit', 'audiotheme-i18n' )
						);
					}
					?>
					<a class="meta-repeater-remove-item show-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="<?php _e( 'Delete Item', 'audiotheme-i18n' ) ?>" title="<?php _e( 'Delete Item', 'audiotheme-i18n' ) ?>" class="icon-delete" /></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<style type="text/css">
.meta-repeater input:-moz-placeholder { color: #aaa;}
.meta-repeater tbody tr { cursor: move; width: 100%; }
.meta-repeater tbody tr:last-child td { border-bottom: none; }
.meta-repeater tbody tr.meta-repeater-active-item td { background: #ececec; border-top-color: #eee;}
.meta-repeater tbody td { padding: 7px; }
.meta-repeater tbody td.track-number { width: 2em; text-align: right; vertical-align: middle;}

.meta-repeater .column-action { width: 16px; cursor: auto; text-align: right; vertical-align: middle;}
.meta-repeater .column-action a { cursor: pointer; font-family: sans-serif;}
.meta-repeater .column-action .meta-repeater-remove-item { opacity: .2;}
.meta-repeater .column-action .meta-repeater-remove-item:hover { opacity: 1;}

.meta-repeater .column-track-info { width: 48px; font-size: 12px; vertical-align: middle;}
.meta-repeater .column-track-info span { padding: 0 3px;}

.meta-repeater .show-on-add { display: none;}

.meta-repeater .ui-sortable-helper { background: #f9f9f9; border-top: 1px solid #dfdfdf; border-bottom: 1px solid #dfdfdf;}
.meta-repeater .ui-sortable-helper td { border-top-width: 0; border-bottom-width: 0;}

.meta-repeater-sort-warning td { padding: 10px; color: #ff0000; border-top: 1px solid #dfdfdf; border-bottom: none;}

#record-tracklist { margin-bottom: 20px;}
#record-tracklist .audiotheme-input-append { overflow: hidden; width: 100%;}
#record-tracklist .audiotheme-input-append input { float: left; padding-right: 34px;}
#record-tracklist .audiotheme-input-append-trigger { float: left; margin: 0 0 0 -30px; width: 30px; height: 23px; line-height: 23px; border-left-width: 1px;}
</style>

<script type="text/javascript">
jQuery('#record-tracklist').appendTo('#post-body-content');
jQuery(function($) { $('#record-tracklist').metaRepeater(); });
</script>