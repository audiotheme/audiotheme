<?php
/**
 * The template for displaying a single video.
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

<?php while ( have_posts() ) : the_post(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'audiotheme-video-single' ); ?> role="article" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">

		<?php if ( $thumbnail = get_post_thumbnail_id() ) : ?>
			<meta itemprop="thumbnailUrl" content="<?php echo esc_url( wp_get_attachment_url( $thumbnail, 'full' ) ); ?>">
		<?php endif; ?>

		<?php if ( $video_url = get_audiotheme_video_url() ) : ?>
			<meta itemprop="embedUrl" content="<?php echo esc_url( $video_url ); ?>">
			<?php the_audiotheme_video(); ?>
		<?php endif; ?>

		<header class="audiotheme-video-header entry-header">
			<?php the_title( '<h1 class="audiotheme-video-title entry-title" itemprop="name">', '</h1>' ); ?>
		</header>

		<?php if ( $tag_list = get_the_tag_list( '', ' ' ) ) : ?>

			<p class="audiotheme-term-list">
				<span class="audiotheme-term-list-label"><?php _e( 'Tags', 'audiotheme' ); ?></span>
				<span class="audiotheme-term-list-items"><?php echo $tag_list; ?></span>
			</p>

		<?php endif; ?>

		<div class="audiotheme-content entry-content" itemprop="description">
			<?php the_content( '' ); ?>
		</div>

	</article>

<?php endwhile; ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>
