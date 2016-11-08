<?php
/**
 * Template to display a Recent Posts widget.
 *
 * @package AudioTheme\Template
 * @since 1.6.0
 */
?>

<?php
if ( ! empty( $title ) ) :
	echo $before_title;
		echo $title;

	if ( $show_feed_link ) :
		printf( '<a class="recent-posts-feed-link" href="%s">%s</a>',
			esc_url( $feed_link ),
			__( 'Feed', 'audiotheme' )
		);
		endif;

	echo $after_title;
endif;
?>

<?php if ( $loop->have_posts() ) : ?>
	<ul class="recent-posts-list">

	<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		<li class="recent-posts-item">
			<?php the_title( '<h5 class="recent-posts-item-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h5>' ); ?>

			<?php
			if ( $show_date ) :
				$date_html = sprintf( '<time class="recent-posts-item-date published" datetime="%s">%s</time>',
					get_post_time( 'c', true ),
					get_the_time( $date_format )
				);

				echo apply_filters( 'audiotheme_widget_recent_posts_date_html', $date_html, $instance );
			endif;
			?>

			<?php
			if ( $show_excerpts ) :
				$excerpt = wpautop( wp_html_excerpt( get_the_excerpt(), $excerpt_length, '&hellip;' ) );
				printf( '<div class="recent-posts-item-excerpt">%s</div>',
					apply_filters( 'audiotheme_widget_recent_posts_excerpt', $excerpt, $loop->post, $instance )
				);
			endif;
			?>
		</li>
	<?php endwhile; ?>

	</ul>
<?php endif; ?>
