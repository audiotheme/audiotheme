<?php if ( $tracks = get_audiotheme_record_tracks( $record->ID ) ) : ?>

	<ol class="audiotheme-tracklist">
		<?php
		foreach ( $tracks as $track ) {
			echo '<li>';
				if ( $track->ID === $post->ID ) {
					echo esc_html( get_the_title( $track->ID ) );
				} else {
					printf(
						'<a href="%s">%s</a>',
						esc_url( get_edit_post_link( $track->ID ) ),
						esc_html( get_the_title( $track->ID ) )
					);
				}
			echo '</li>';
		}
		?>
	</ol>

<?php endif; ?>
