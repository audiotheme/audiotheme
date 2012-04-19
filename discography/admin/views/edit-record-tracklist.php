<table class="widefat meta-repeater" id="record-tracklist">
	<thead>
		<tr>
			<th colspan="4">Tracks</th>
			<th class="column-action"><a class="meta-repeater-add-item"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/add.png" width="16" height="16" alt="Add Item" title="Add Item" class="icon-add" /></a></th>
		</tr>
	</thead>
	<tbody class="meta-repeater-items">
		<?php foreach( $tracks as $key => $track ) : ?>
			<tr class="meta-repeater-item">
				<td style="width: 2em; font-size: 16px; text-align: right; vertical-align: middle">
					<input type="hidden" name="audiotheme_tracks[<?php echo $key; ?>][post_id]" value="<?php echo $track->ID; ?>" class="clear-on-add">
					<span class="meta-repeater-index"><?php echo $key+1 . '.'; ?></span>
				</td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][title]" placeholder="Title" value="<?php echo esc_attr( $track->post_title ); ?>" class="widefat clear-on-add"></td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][artist]" placeholder="Artist" value="<?php echo esc_attr( get_post_meta( $track->ID, '_artist', true ) ); ?>" class="widefat"></td>
				<td>
					<?php
					if ( $track->ID && audiotheme_track_has_download( $track->ID ) ) {
						echo '<span class="remove-on-add">&darr;</span>';
					}
					
					if ( $track->ID && $purchase_url = get_post_meta( $track->ID, '_purchase_url', true ) ) {
						echo '<span class="remove-on-add">$</span>';
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
					<a class="meta-repeater-remove-item show-on-add"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="Delete Item" title="Delete Item" class="icon-delete" /></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<style type="text/css">
.meta-repeater tbody tr { cursor: move; width: 100%;}
.meta-repeater tbody tr.active-row td { background: #ececec; border-top-color: #ececec;}
.meta-repeater tbody td { padding: 5px 7px;}
.meta-repeater .column-action { cursor: auto; padding-right: 10px; padding-left: 0; width: 16px; text-align: center;}
.meta-repeater .column-action a { cursor: pointer;}
.meta-repeater .column-action .meta-repeater-remove-item { opacity: .2;}
.meta-repeater .column-action .meta-repeater-remove-item:hover { opacity: 1;}
.meta-repeater .show-on-add { display: none;}

.meta-repeater .ui-sortable-helper { }
.meta-repeater .ui-sortable-helper td { border-top-width: 0; border-bottom-width: 0;}
.meta-repeater .ui-sortable-placeholder { }
.meta-repeater .ui-sortable-placeholder td { }

#record-tracklist { margin-bottom: 20px;}
#record-tracklist input { padding: 3px 8px; font-size: 1.3em;}
#record-tracklist input:focus { border-color: #808080;}
#record-tracklist input::-webkit-input-placeholder { padding: 3px 0;}
#record-tracklist input:-moz-placeholder { }
</style>

<script type="text/javascript">
jQuery('#record-tracklist').appendTo('#post-body-content');
jQuery(function($) { $('#record-tracklist').metaRepeater(); });
</script>