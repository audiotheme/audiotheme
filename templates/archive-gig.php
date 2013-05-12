<?php
/**
 * The template to display a list of gigs.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>

<?php do_action( 'audiotheme_before_main_content' ); ?>

<ul id="audiotheme-gigs" class="audiotheme-gigs audiotheme-clearfix">

	<?php
	while ( have_posts() ) :
		the_post();
		$gig = get_audiotheme_gig();
		?>

		<li <?php post_class( array( 'audiotheme-gig-card', 'audiotheme-clearfix' ) ) ?> itemscope itemtype="http://schema.org/MusicEvent">

			<div class="audiotheme-gig-meta-datetime">
				<meta content="<?php echo get_audiotheme_gig_time( 'c' ); ?>" itemprop="startDate">
				<time datetime="<?php echo get_audiotheme_gig_time( 'c' ); ?>">
					<span class="audiotheme-gig-date"><a href="<?php the_permalink(); ?>" class="audiotheme-gig-permalink" itemprop="url"><?php echo get_audiotheme_gig_time( 'M d, Y' ); ?></a></span>
					<span class="audiotheme-gig-time"><?php echo get_audiotheme_gig_time( '', 'g:i A' ); ?></span>
				</time>
			</div><!-- /.gig-meta-datetime -->

			<div class="audiotheme-gig-details">

				<?php the_title( '<h2 class="audiotheme-gig-title" itemprop="name">', '</h2>' ); ?>

				<?php if ( audiotheme_gig_has_venue() ) : ?>

					<p class="audiotheme-gig-place" itemprop="location" itemscope itemtype="http://schema.org/EventVenue">

						<span class="audiotheme-gig-location" itemprop="location"><?php echo get_audiotheme_venue_location( $gig->venue->ID ); ?></span>

						<?php
						the_audiotheme_gig_venue_link( array(
							'before'      => '<span class="audiotheme-gig-venue">',
							'after'       => '</span>',
							'before_link' => '<span itemprop="name">',
							'after_link'  => '</span>',
						) );
						?>
					</p>

				<?php endif; ?>

				<?php the_audiotheme_gig_description( '<div class="audiotheme-gig-note" itemprop="description">', '</div>' ); ?>

			</div><!-- /.gig-details -->

			<?php if ( audiotheme_gig_has_ticket_meta() ) : ?>

				<div class="audiotheme-gig-meta-tickets" itemprop="offers" itemscope itemtype="http://schema.org/Offer">

					<?php if ( $gig_tickets_price = get_audiotheme_gig_tickets_price() ) : ?>
						<span class="audiotheme-gig-tickets-price" itemprop="price"><?php echo esc_html( $gig_tickets_price ); ?></span>
					<?php endif; ?>

					<?php if ( $gig_tickets_url = get_audiotheme_gig_tickets_url() ) : ?>
						<span class="audiotheme-gig-tickets-link" >
							<a href="<?php echo esc_url( $gig_tickets_url ); ?>" target="_blank" itemprop="url"><?php _e( 'Buy Tickets', 'audiotheme-i18n' ); ?></a>
						</span>
					<?php endif; ?>

				</div><!-- /.gig-meta-tickets -->

			<?php endif; ?>

		</li><!-- /.audiotheme-gig-card -->

	<?php endwhile; ?>

</ul><!-- /#audiotheme-gigs -->

<?php do_action( 'audiotheme_after_main_content' ); ?>

<?php get_footer(); ?>