<p class="audiotheme-field">
	<label for="record-year"><?php _e( 'Release Year', 'audiotheme-i18n' ); ?></label>
	<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( get_audiotheme_record_release_year( $post->ID ) ) ; ?>" class="widefat">
</p>
<p class="audiotheme-field">
	<label for="record-artist"><?php _e( 'Artist', 'audiotheme-i18n' ); ?></label>
	<input type="text" name="artist" id="record-artist" value="<?php echo esc_attr( get_audiotheme_record_artist( $post->ID ) ) ; ?>" class="widefat">
</p>
<p class="audiotheme-field">
	<label for="record-genre"><?php _e( 'Genre', 'audiotheme-i18n' ); ?></label>
	<input type="text" name="genre" id="record-genre" value="<?php echo esc_attr( get_audiotheme_record_genre( $post->ID ) ) ; ?>" class="widefat">
</p>

<?php if ( $record_types ) : ?>
	<p id="audiotheme-record-types" class="audiotheme-field">
		<label><?php _e( 'Type', 'audiotheme-i18n' ) ?></label><br />
		<?php
		foreach ( $record_types as $slug => $name ) {
			echo sprintf( '<input type="radio" name="record_type[]" id="%1$s" value="%1$s"%2$s> <label for="%1$s">%3$s</label><br />',
				esc_attr( $slug ),
				checked( in_array( $slug, $selected_record_type ), true, false ),
				esc_attr( $name ) );
		}
		?>
	</p>
<?php endif; ?>

<table class="audiotheme-repeater" id="record-links">
	<thead>
		<tr>
			<th colspan="3"><?php _e( 'Links', 'audiotheme_i18n' ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">
				<a class="button audiotheme-repeater-add-item"><?php _e( 'Add URL', 'audiotheme-i18n' ) ?></a>
				<?php
				printf( '<span class="audiotheme-repeater-sort-warning" style="display: none;">%1$s <br /><em>%2$s</em></span>',
					esc_html__( 'The order has been changed.', 'audiotheme-i18n' ),
					esc_html__( 'Save your changes.', 'audiotheme-i18n' )
				);
				?>
			</td>
			<td>&nbsp;</td>
		</tr>
	</tfoot>
	<tbody class="audiotheme-repeater-items">
		<?php
		foreach( $record_links as $i => $link ) :
			$link = wp_parse_args( $link, array( 'name' => '', 'url' => '' ) );
			?>
			<tr class="audiotheme-repeater-item">
				<td><input type="text" name="record_links[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $link['name'] ); ?>" placeholder="<?php esc_attr_e( 'Text', 'audiotheme-i18n' ); ?>" class="record-link-name audiotheme-clear-on-add" style="width: 8em"></td>
				<td><input type="text" name="record_links[<?php echo $i; ?>][url]" value="<?php echo esc_url( $link['url'] ); ?>" placeholder="<?php esc_attr_e( 'URL', 'audiotheme-i18n' ); ?>" class="widefat audiotheme-clear-on-add"></td>
				<td class="column-action"><a class="audiotheme-repeater-remove-item"><img src="<?php echo esc_url( AUDIOTHEME_URI . '/admin/images/delete.png' ); ?>" width="16" height="16" alt="<?php esc_attr_e( 'Delete Item', 'audiotheme-i18n' ) ?>" title="<?php _e( 'Delete Item', 'audiotheme-i18n' ) ?>" class="icon-delete" /></a></td>
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

<style type="text/css">
.ui-autocomplete { overflow-y: auto; overflow-x: hidden; max-height: 180px;}

#audiotheme-record-types { margin: 1em 0;}
#audiotheme-record-types li { margin: 0; vertical-align: middle;}
#audiotheme-record-types li input { vertical-align: middle;}
#audiotheme-record-types li label { margin: 0; font-weight: normal;}
#audiotheme-record-types ul { margin-top: 3px; margin-bottom: 0;}

#record-links { width: 100%; border-spacing: 0;}
#record-links td { padding: 0 0 5px 0;}
#record-links th { text-align: left;}
#record-links tfoot td { padding-top: 5px;}
#record-links tfoot td a { float: right; margin-left: 10px;}
#record-links tfoot td .audiotheme-repeater-sort-warning { color: red;}
#record-links .column-action { padding: 0 0 0 5px;}
</style>