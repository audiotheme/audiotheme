<?php
/**
 * The template to display list of videos.
 *
 * @package   AudioTheme\Template
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<header class="audiotheme-archive-header archive-header">
	<?php the_audiotheme_archive_title( '<h1 class="audiotheme-archive-title archive-title">', '</h1>' ); ?>
	<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>
</header>

<ul class="audiotheme-videos audiotheme-grid audiotheme-clearfix">

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<p class="audiotheme-featured-image">
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'video-thumbnail' ); ?></a>
			</p>

			<?php the_title( '<h2 class="audiotheme-video-title entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>

		</li>

	<?php endwhile; ?>

</ul>

<?php audiotheme_archive_nav(); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
