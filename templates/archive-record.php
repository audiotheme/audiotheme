<?php
/**
 * The template to display list of records.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<header class="audiotheme-archive-header archive-header">
	<h1 class="audiotheme-archive-title archive-title"><?php the_audiotheme_archive_title(); ?></h1>

	<?php the_audiotheme_archive_description( '<div class="audiotheme-archive-intro archive-intro">', '</div>' ); ?>
</header>

<div class="audiotheme-records audiotheme-grid audiotheme-clearfix">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article" itemscope itemtype="http://schema.org/MusicAlbum">

			<?php if ( has_post_thumbnail() ) : ?>

				<p class="audiotheme-record-artwork audiotheme-featured-image">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'record-thumbnail', array( 'itemprop' => 'image' ) ); ?>
						<span class="audiotheme-record-type"><?php echo esc_html( get_audiotheme_record_type_string( get_audiotheme_record_type() ) ); ?></span>
					</a>
				</p>

			<?php endif; ?>

			<?php the_title( '<h2 class="audiotheme-record-title entry-title" itemprop="name"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>

			<?php
			$artist = get_audiotheme_record_artist();
			$year = get_audiotheme_record_release_year();

			if ( $artist ) :
				?>
				<p class="audiotheme-record-meta entry-meta">
					<?php if ( $artist ) : ?>
						<strong class="audiotheme-record-meta-artist" itemprop="byArtist"><?php echo esc_html( $artist ); ?></strong>
					<?php endif; ?>

					<?php if ( $year ) : ?>
						<span class="audiotheme-record-meta-release">(<span itemprop="dateCreated"><?php echo esc_html( $year ); ?></span>)</span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

		</article>

	<?php endwhile; ?>

</div><!-- /.media-grid -->

<?php audiotheme_archive_nav(); ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>