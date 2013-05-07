<?php
/**
 * The template for displaying a single track.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_template_before_main_content' ); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'audiotheme-record-single audiotheme-track-single' ); ?> itemtype="http://schema.org/MusicRecording" itemscope role="article">

		<?php if ( $thumbnail_id = get_audiotheme_track_thumbnail_id() ) : ?>

			<p class="audiotheme-record-artwork">
				<a href="<?php echo wp_get_attachment_url( $thumbnail_id ); ?>" itemprop="image">
					<?php echo wp_get_attachment_image( $thumbnail_id, 'record-thumbnail' ); ?>
				</a>
			</p>

		<?php endif; ?>

		<header class="audiotheme-record-header entry-header">
			<?php the_title( '<h1 class="record-title entry-title" itemprop="name">', '</h1>' ); ?>

			<?php if ( $artist = get_audiotheme_record_artist() ) : ?>
				<h2 class="audiotheme-record-artist" itemprop="byArtist"><?php echo esc_html( $artist ); ?></h2>
			<?php endif; ?>

			<h3 class="audiotheme-record-subtitle"><a href="<?php echo get_permalink( $post->post_parent ); ?>"><em itemprop="inAlbum"><?php echo get_the_title( $post->post_parent ); ?></em></a></h3>
		</header>

		<div class="audiotheme-tracklist-section">
			<ol class="audiotheme-tracklist audiotheme-tracklist-single">

				<li id="track-<?php the_ID(); ?>" class="audiotheme-track">
					<span class="audiotheme-track-info audiotheme-track-cell">
						<a href="<?php the_permalink(); ?>" itemprop="url" class="audiotheme-track-title"><span itemprop="name"><?php the_title(); ?></span></a>
						
						<span class="audiotheme-track-meta">
							<span class="jp-current-time">-:--</span>
						</span>
					</span>
				</li>

				<?php enqueue_audiotheme_tracks( get_the_ID(), 'record' ); ?>
			</ol>
		</div><!-- /.tracklist-section -->

		<?php
		$download_url = is_audiotheme_track_downloadable();
		$purchase_url = get_audiotheme_track_purchase_url();

		if ( $download_url || $purchase_url ) :
			?>
			<div class="audiotheme-record-links audiotheme-track-links">
				<ul class="audiotheme-record-links-list">
					<?php if ( $download_url ) : ?>
						<li class="audiotheme-record-links-item">
							<a href="<?php echo esc_url( $download_url ); ?>" class="audiotheme-record-link" itemprop="url" target="_blank"><?php _e( 'Download', 'audiotheme-i18n' ); ?></a>
						</li>
					<?php endif; ?>

					<?php if ( $purchase_url ) : ?>
						<li class="audiotheme-record-links-item">
							<a href="<?php echo esc_url( $purchase_url ); ?>" class="audiotheme-record-link" itemprop="url" target="_blank"><?php _e( 'Purchase', 'audiotheme-i18n' ); ?></a>
						</li>
					<?php endif; ?>
				</ul>
			</div><!-- /.record-links -->
		<?php endif; ?>

		<div class="audiotheme-content entry-content" itemprop="description">
			<?php the_content( '' ); ?>
		</div><!-- /.content -->

	</article><!-- /.single-audiotheme-record -->

<?php endwhile; ?>

<?php do_action( 'audiotheme_template_after_main_content' ); ?>

<?php get_footer(); ?>