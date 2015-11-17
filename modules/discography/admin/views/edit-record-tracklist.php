<?php
/**
 * View the record track list repeater.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */
?>

<table id="record-tracklist" class="audiotheme-repeater audiotheme-edit-after-editor widefat" data-item-template-id="audiotheme-track">
	<thead>
		<tr>
			<th colspan="5"><?php esc_html_e( 'Tracks', 'audiotheme' ) ?></th>
			<th class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php esc_html_e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="5">
				<?php
				printf(
					'<span class="audiotheme-repeater-sort-warning" style="display: none">%1$s <em>%2$s</em></span>',
					esc_html__( 'The order has been changed.', 'audiotheme' ),
					esc_html__( 'Save your changes.', 'audiotheme' )
				);
				?>
			</td>
			<td class="column-action">
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<a class="button audiotheme-repeater-add-item"><?php esc_html_e( 'Add Track', 'audiotheme' ) ?></a>
				<?php endif; ?>
			</td>
		</tr>
	</tfoot>

	<tbody class="audiotheme-repeater-items is-empty">
		<tr>
			<td colspan="6"><?php echo esc_html( get_post_type_object( 'audiotheme_track' )->labels->not_found ); ?></td>
		</tr>
	</tbody>
</table>
