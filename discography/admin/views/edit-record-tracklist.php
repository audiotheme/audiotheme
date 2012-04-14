<table class="widefat meta-list" id="record-tracklist-ui">
	<thead>
		<tr>
			<th colspan="3">Tracks</th>
			<th class="column-action"><a class="add-list-item"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/add.png" width="16" height="16" alt="Add Item" title="Add Item" class="icon-add" /></a></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $tracks as $key => $track ) : ?>
			<tr>
				<td style="width: 2em; font-size: 16px; text-align: right; vertical-align: middle">
					<input type="hidden" name="audiotheme_tracks[<?php echo $key; ?>][post_id]" value="<?php echo $track->ID; ?>">
					<span class="track-number"><?php echo $key+1 . '.'; ?></span>
				</td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][title]" placeholder="Title" value="<?php echo esc_attr( $track->post_title ); ?>" class="widefat"></td>
				<td><input type="text" name="audiotheme_tracks[<?php echo $key; ?>][artist]" placeholder="Artist" value="<?php echo esc_attr( get_post_meta( $track->ID, '_artist', true ) ); ?>" class="widefat"></td>
				<td class="column-action">
					<!--<a class="remove-list-item"><img src="<?php echo AUDIOTHEME_URI; ?>/admin/images/delete.png" width="16" height="16" alt="Delete Item" title="Delete" class="icon-delete" /></a>-->
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<style type="text/css">
#record-tracklist-ui { margin-bottom: 20px;}

.meta-list tbody tr { cursor: move; width: 100%;}
.meta-list .column-action { cursor: auto; padding-right: 10px; padding-left: 0; width: 16px; text-align: center;}
.meta-list .column-action a { cursor: pointer;}
.meta-list .column-action .icon-delete { visibility: hidden; margin: 0;}
.meta-list tr:hover .column-action .icon-delete,
.meta-list tr.hover .column-action .icon-delete { visibility: visible;}

.meta-list .ui-sortable-helper { }
.meta-list .ui-sortable-helper td { border-top-width: 0; border-bottom-width: 0;}
.meta-list .ui-sortable-placeholder { }
.meta-list .ui-sortable-placeholder td { }


#record-tracklist-ui input { padding: 3px 8px; font-size: 1.3em;}
#record-tracklist-ui input::-webkit-input-placeholder { padding: 3px 0;}
#record-tracklist-ui input:-moz-placeholder { }
</style>

<script type="text/javascript">
jQuery('#record-tracklist-ui').appendTo('#post-body-content');


jQuery(function($) {
	$('.meta-list').each(function() {
		$('tbody', this).sortable({
			axis: 'y',
			forceHelperSize: true,
			forcePlaceholderSize: true,
			helper: function(e, ui) {
				var $helper = ui.clone();
				$helper.children().each(function(index) {
				  $(this).width(ui.children().eq(index).width())
				});
				
				return $helper;
			},
			start: function(e, ui) {
				var colCount = ui.helper.children().length;
				ui.placeholder.css('visibility','visible').html('<td colspan="' + colCount + '">&nbsp;</td>');
			},
			update: function(e, ui) {
				updateOrder();
			}
		});
		
		$(this).data('itemIndex', $('tbody tr', this).length);
		$(this).data('itemRow', $('tbody tr:first-child', this).clone());
		
		$('.add-list-item', this).click(function(e) {
			e.preventDefault();
			
			var itemRow = $(this).parents('.meta-list').data('itemRow');
			$(this).closest('.meta-list').find('tbody').append(itemRow.clone()).children('tr:last-child').find('input[type="text"], select, textarea').val('').each(function(e) {
				var itemIndex = $(this).closest('.meta-list').data('itemIndex');
				$(this).attr('name', $(this).attr('name').replace('0', itemIndex) );
			});
			
			$(this).closest('.meta-list').data('itemIndex', $(this).closest('.meta-list').data('itemIndex')+1 );
			
			updateOrder();
		});
	});
	
	$('.meta-list').delegate('.remove-list-item', 'click', function(e) {
		e.preventDefault();
		jQuery(this).closest('tr').remove();
	});
	
	function updateOrder() {
		$('.track-number', '#record-tracklist-ui').each(function(i) {
			$(this).text(i + 1 + '.');
		});
	}
});
</script>