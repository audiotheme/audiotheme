<p class="audiotheme-field">
	<label for="record-year"><?php _e( 'Release Year', 'audiotheme' ); ?></label>
	<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( get_audiotheme_record_release_year( $post->ID ) ); ?>" class="widefat">
</p>
<p class="audiotheme-field">
	<label for="record-artist"><?php _e( 'Artist', 'audiotheme' ); ?></label>
	<input type="text" name="artist" id="record-artist" value="<?php echo esc_attr( get_audiotheme_record_artist( $post->ID ) ); ?>" class="widefat">
</p>
<p class="audiotheme-field">
	<label for="record-genre"><?php _e( 'Genre', 'audiotheme' ); ?></label>
	<input type="text" name="genre" id="record-genre" value="<?php echo esc_attr( get_audiotheme_record_genre( $post->ID ) ); ?>" class="widefat">
</p>

<table class="audiotheme-repeater" id="record-links">
	<thead>
		<tr>
			<th colspan="3"><?php _e( 'Links', 'audiotheme_i18n' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">
				<a class="button audiotheme-repeater-add-item"><?php _e( 'Add URL', 'audiotheme' ) ?></a>
				<?php
				printf( '<span class="audiotheme-repeater-sort-warning" style="display: none;">%1$s <br /><em>%2$s</em></span>',
					esc_html__( 'The order has been changed.', 'audiotheme' ),
					esc_html__( 'Save your changes.', 'audiotheme' )
				);
				?>
			</td>
			<td>&nbsp;</td>
		</tr>
	</tfoot>
	<tbody class="audiotheme-repeater-items">
		<?php
		foreach ( $record_links as $i => $link ) :
			$link = wp_parse_args( $link, array( 'name' => '', 'url' => '' ) );
			?>
			<tr class="audiotheme-repeater-item">
				<td><input type="text" name="record_links[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $link['name'] ); ?>" placeholder="<?php esc_attr_e( 'Text', 'audiotheme' ); ?>" class="record-link-name audiotheme-clear-on-add" style="width: 8em"></td>
				<td><input type="text" name="record_links[<?php echo $i; ?>][url]" value="<?php echo esc_url( $link['url'] ); ?>" placeholder="<?php esc_attr_e( 'URL', 'audiotheme' ); ?>" class="widefat audiotheme-clear-on-add"></td>
				<td class="column-action"><a class="audiotheme-repeater-remove-item"><img src="<?php echo esc_url( AUDIOTHEME_URI . 'admin/images/delete.png' ); ?>" width="16" height="16" alt="<?php esc_attr_e( 'Delete Item', 'audiotheme' ) ?>" title="<?php _e( 'Delete Item', 'audiotheme' ) ?>" class="icon-delete" /></a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<script type="text/javascript">
jQuery(function($) {
	$('#record-links').audiothemeRepeater().on('focus', 'input.record-link-name', function() {
		var $this = $(this);

		if ( ! $this.hasClass('ui-autocomplete-input')) {
			$this.autocomplete({
				source: <?php echo json_encode( $record_link_source_names ); ?>,
				minLength: 0
			});
		}
	});
});
</script>
