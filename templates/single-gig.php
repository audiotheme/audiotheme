<?php
/**
 * The template for displaying a single gig.
 *
 * @package AudioTheme
 * @subpackage Template
 * @since 1.2.0
 */

get_header();
?>


<?php do_action( 'audiotheme_template_before_main_content' ); ?>


<?php
while ( have_posts() ) :
	the_post();
	$gig = get_audiotheme_gig();
	$venue = get_audiotheme_venue( $gig->venue->ID );
	?>

	<dl id="audiotheme-gig" <?php post_class( array( 'single-audiotheme-gig', 'audiotheme-clearfix' ) ) ?> itemscope itemtype="http://schema.org/MusicEvent">

		<?php if ( audiotheme_gig_has_venue() ) : ?>

			<dt class="gig-header">
				<?php the_title( '<h1 class="gig-title" itemprop="name">', '</h1>' ); ?>

				<div class="gig-date">
					<meta content="<?php echo get_audiotheme_gig_time( 'c' ); ?>" itemprop="startDate">
					<time class="gig-date" datetime="<?php echo get_audiotheme_gig_time( 'c' ); ?>">
						<strong><?php echo get_audiotheme_gig_time( 'F d, Y' ); ?></strong>
					</time>
				</div><!-- /.gig-date -->
			</dt>

		<?php endif; ?>

		<dd class="gig-description">
			<?php if ( audiotheme_gig_has_venue() ) : ?>

				<p class="gig-place">
					<?php echo get_audiotheme_venue_location( $gig->venue->ID ); ?>
				</p>

			<?php endif; ?>

			<?php the_audiotheme_gig_description( '<div class="gig-note" itemprop="description">', '</div>' ); ?>
		</dd><!-- /.gig-description -->

		<dd class="gig-meta">
			<span class="gig-time">
				<strong class="label"><?php _e( 'Time', 'audiotheme-i18n' ); ?></strong>
				<?php echo get_audiotheme_gig_time( '', 'g:i A', false, array( 'empty_time' => __( 'TBD', 'audiotheme-i18n' ) ) ); ?>
			</span>

			<?php if ( audiotheme_gig_has_ticket_meta() ) : ?>

				<span class="gig-tickets">
					<strong class="label"><?php _e( 'Admission', 'audiotheme-i18n' ); ?></strong>

					<?php if ( $gig_tickets_price = get_audiotheme_gig_tickets_price() ) : ?>
						<span class="gig-tickets-price"><?php echo esc_html( $gig_tickets_price ); ?></span>
					<?php endif; ?>

					<?php if ( $gig_tickets_url = get_audiotheme_gig_tickets_url() ) : ?>
						<span class="gig-tickets-link"><a href="<?php echo esc_url( $gig_tickets_url ); ?>" target="_blank"><?php _e( 'Buy Tickets', 'audiotheme-i18n' ); ?></a></span>
					<?php endif; ?>
				</span>

			<?php endif; ?>

		</dd><!-- /.gig-meta -->

		<?php if ( audiotheme_gig_has_venue() ) : ?>

			<dd class="gig-venue audiotheme-clearfix" itemprop="location" itemscope itemtype="http://schema.org/EventVenue">
				<?php
				the_audiotheme_venue_vcard( array(
					'container'         => '',
					'show_name_link'    => false,
					'show_phone'        => false,
					'separator_country' => ', ',
				) );
				?>
				
				<div class="venue-meta">
					<?php if ( $venue->phone ) : ?>
						<span class="venue-phone"><?php echo esc_html( $venue->phone ); ?></span>
					<?php endif; ?>
					
					<?php if ( $venue->website ) : ?>
						<span class="venue-website"><a href="<?php echo esc_url( $venue->website ); ?>"><?php echo audiotheme_simplify_url( $venue->website ); ?></a></span>
					<?php endif; ?>
				</div>

				<div class="venue-map">
					<?php echo get_audiotheme_google_map_embed( array( 'width' => '100%', 'height' => 220 ), $venue->ID ); ?>
				</div>
			</dd><!-- /.gig-venue -->

		<?php endif; ?>

		<dd class="gig-content entry-content">

			<?php the_content(); ?>

		</dd><!-- /.gig-content -->
	</dl><!-- /#audiotheme-gig -->

<?php endwhile; ?>


<?php do_action( 'audiotheme_template_after_main_content' ); ?>


<?php get_footer(); ?>