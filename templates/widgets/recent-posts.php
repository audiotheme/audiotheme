<?php
/**
 * Template to display a Recent Posts widget.
 *
 * @package AudioTheme_Framework
 * @subpackage Template
 * @since x.x.x
 */
?>

<?php
if ( ! empty( $title ) ) :
	echo $before_title;
		echo $title;

		if ( $show_feed_link ) :
			printf( '<a href="' . esc_url( $feed_link ) . '">' . __( 'Feed', 'audiotheme' ) . '</a>' );
		endif;

	echo $after_title;
endif;
?>

<?php if ( $loop->have_posts() ) : ?>
	<ul>

	<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		<li>
			<h5><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo get_the_title(); ?></a></h5>

			<?php
			if ( $show_date ) :
				$date_html = sprintf( '<time datetime="%s" pubdate="pubdate" class="published">%s</time>',
					get_post_time( 'c', true ),
					get_the_time( $date_format )
				);

				echo apply_filters( 'audiotheme_widget_recent_posts_date_html', $date_html, $instance );
			endif;
			?>

			<?php
			if ( $show_excerpts ) :
				$excerpt = wpautop( wp_html_excerpt( get_the_excerpt(), $excerpt_length ) . '...' );
				echo apply_filters( 'audiotheme_widget_recent_posts_excerpt', $excerpt, $loop->post, $instance );
			endif;
			?>
		</li>
	<?php endwhile; ?>

	</ul>
<?php endif; ?>
