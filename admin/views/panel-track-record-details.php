<div class="audiotheme-track-record-panel audiotheme-panel">
	<div class="audiotheme-panel-header">
		<h2 class="audiotheme-panel-title"><?php echo esc_html( get_the_title( $record->ID ) ); ?></h2>
	</div>

	<div class="audiotheme-panel-body">

		<div class="audiotheme-record-card">
			<?php
			if ( has_post_thumbnail( $record->ID ) ) {
				printf(
					'<div class="audiotheme-record-card-thumbnail">%s</div>',
					get_the_post_thumbnail( $record->ID, 'thumbnail' )
				);
			}
			?>

			<div class="audiotheme-record-card-details">

				<?php if ( $artist || $genre || $release ) : ?>

					<table>
						<?php if ( $artist ) : ?>
							<tr>
								<th><?php esc_html_e( 'Artist:', 'audiotheme' ); ?></th>
								<td><?php echo esc_html( $artist ); ?></td>
							</tr>
						<?php endif; ?>

						<?php if ( $release ) : ?>
							<tr>
								<th><?php esc_html_e( 'Release:', 'audiotheme' ); ?></th>
								<td><?php echo esc_html( $release ); ?></td>
							</tr>
						<?php endif; ?>

						<?php if ( $genre ) : ?>
							<tr>
								<th><?php esc_html_e( 'Genre:', 'audiotheme' ); ?></th>
								<td><?php echo esc_html( $genre ); ?></td>
							</tr>
						<?php endif; ?>
					</table>

				<?php endif; ?>

				<p>
					<a href="<?php echo esc_url( get_edit_post_link( $record->ID ) ); ?>" class="button"><?php echo esc_html( $record_post_type_object->labels->edit_item ); ?></a>
				</p>

			</div>
		</div>

	</div>
</div>
