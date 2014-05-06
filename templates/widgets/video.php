<?php
/**
 * Template to display a Video widget.
 *
 * @package AudioTheme_Framework
 * @subpackage Template
 * @since 1.5.0
 */
?>

<?php
if ( ! empty( $title ) ) :
	echo $before_title . $title . $after_title;
endif;
?>

<p class="featured-image">
	<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo get_the_post_thumbnail( $post->ID, $image_size ); ?></a>
</p>

<?php
if ( ! empty( $text ) ) :
	echo wpautop( $text );
endif;
?>

<?php if ( ! empty( $link_text ) ) : ?>
	<p class="more">
		<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo $link_text; ?></a>
	</p>
<?php endif; ?>
