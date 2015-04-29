<?php
/**
 * The template for displaying a single record.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'audiotheme-record-single' ); ?> itemscope itemtype="http://schema.org/MusicAlbum" role="article">

		<?php if ( has_post_thumbnail() ) : ?>

			<p class="audiotheme-record-artwork">
				<a href="<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>" itemprop="image">
					<?php the_post_thumbnail( 'record-thumbnail' ); ?>
				</a>
			</p>

		<?php endif; ?>

		<header class="audiotheme-record-header entry-header">
			<?php the_title( '<h1 class="audiotheme-record-title entry-title" itemprop="name">', '</h1>' ); ?>

			<?php if ( $artist = get_audiotheme_record_artist() ) : ?>
				<h2 class="audiotheme-record-artist" itemprop="byArtist"><?php echo esc_html( $artist ); ?></h2>
			<?php endif; ?>

			<ul class="audiotheme-record-meta audiotheme-meta-list">
				<?php if ( $year = get_audiotheme_record_release_year() ) : ?>
					<li class="audiotheme-meta-item">
						<span class="audiotheme-label"><?php _e( 'Release', 'audiotheme' ); ?></span>
						<span itemprop="dateCreated"><?php echo esc_html( $year ); ?></span>
					</li>
				<?php endif; ?>

				<?php if ( $genre = get_audiotheme_record_genre() ) : ?>
					<li class="audiotheme-meta-item">
						<span class="audiotheme-label"><?php _e( 'Genre', 'audiotheme' ); ?></span>
						<span itemprop="genre"><?php echo esc_html( $genre ); ?></span>
					</li>
				<?php endif; ?>
			</ul><!-- /.record-meta -->
		</header>

		<?php if ( $links = get_audiotheme_record_links() ) : ?>

			<div class="audiotheme-record-links">
				<h2 class="audiotheme-record-links-title"><?php _e( 'Purchase', 'audiotheme' ); ?></h2>
				<ul class="audiotheme-record-links-list">
					<?php
					foreach ( $links as $link ) {
						printf( '<li class="audiotheme-record-links-item"><a href="%s" class="audiotheme-record-link"%s itemprop="url">%s</a></li>',
							$link['url'],
							( false === strpos( $link['url'], home_url() ) ) ? ' target="_blank"' : '',
							$link['name']
						);
					}
					?>
				</ul>
			</div><!-- /.record-links -->

		<?php endif; ?>

		<?php if ( $tracks = get_audiotheme_record_tracks() ) : ?>

			<div class="audiotheme-tracklist-section">
				<h2 class="audiotheme-tracklist-title audiotheme-label"><?php _e( 'Track List', 'audiotheme' ); ?></h2>
				<ol class="audiotheme-tracklist">

					<?php foreach ( $tracks as $track ) : ?>

						<li id="track-<?php echo $track->ID; ?>" class="audiotheme-track" itemprop="track" itemscope itemtype="http://schema.org/MusicRecording">
							<span class="audiotheme-track-info audiotheme-track-cell">
								<a href="<?php echo esc_url( get_permalink( $track->ID ) ); ?>" itemprop="url" class="audiotheme-track-title"><span itemprop="name"><?php echo get_the_title( $track->ID ); ?></span></a>

								<span class="audiotheme-track-meta">
									<?php if ( $download_url = is_audiotheme_track_downloadable( $track->ID ) ) : ?>
										<a href="<?php echo esc_url( $download_url ); ?>" class="audiotheme-track-download-link"><?php _e( 'Download', 'audiotheme' ) ?></a>
									<?php endif; ?>

									<span class="jp-current-time">-:--</span>
								</span>
							</span>
						</li>

					<?php endforeach; ?>

					<?php enqueue_audiotheme_tracks( wp_list_pluck( $tracks, 'ID' ), 'record' ); ?>
				</ol>
			</div><!-- /.tracklist-section -->

		<?php endif; ?>

		<div class="audiotheme-content entry-content" itemprop="description">
			<?php the_content( '' ); ?>
		</div><!-- /.content -->

	</article><!-- /.single-audiotheme-record -->

<?php endwhile; ?>

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>