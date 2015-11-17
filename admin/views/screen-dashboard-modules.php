<?php
/**
 * View to modules on the dashboard screen.
 *
 * @package AudioTheme\Administration
 * @since 1.9.0
 */
?>

<div class="audiotheme-dashboard-lead">
	<p>
		<?php _e( 'Gigs, Discography, and Videos are the backbone of AudioTheme. Explore each feature below or use the menu options to the left to get started.', 'audiotheme' ); ?>
	</p>
</div>

<div class="audiotheme-module-cards">

	<?php foreach ( $modules as $module_id => $module ) :
		$classes   = array( 'audiotheme-module-card', 'audiotheme-module-card--' . $module_id );
		$classes[] = $modules->is_active( $module_id ) ? 'is-active' : 'is-inactive';
		$nonce     = wp_create_nonce( 'toggle-module_' . $module_id );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			data-module-id="<?php echo esc_attr( $module_id ); ?>"
			data-toggle-nonce="<?php echo esc_attr( $nonce ); ?>">

			<div class="audiotheme-module-card-details">
				<h2 class="audiotheme-module-card-name"><?php echo esc_html( $module->name ); ?></h2>
				<div class="audiotheme-module-card-description">
					<?php echo wpautop( esc_html( $module->description ) ); ?>
				</div>
				<div class="audiotheme-module-card-overview">
					<?php if ( 'discography' === $module_id ) : ?>
						<figure class="audiotheme-module-card-overview-media">
							<iframe src="https://www.youtube.com/embed/ZopsZEiv1F0?rel=0" frameborder="0" allowfullscreen></iframe>
						</figure>
						<p>
							<?php esc_html_e( 'Everything you need to build your Discography is at your fingertips.', 'audiotheme' ); ?>
						</p>
						<p>
							<?php esc_html_e( 'Your discography is the window through which listeners are introduced to and discover your music on the web. Encourage that discovery on your website through a detailed and organized history of your recorded output using the AudioTheme discography feature. Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' ); ?>
						</p>
						<p>
							<strong><?php esc_html_e( 'Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>"><?php esc_html_e( 'Add a record', 'audiotheme' ); ?></a>
						</p>
					<?php elseif ( 'gigs' === $module_id ) : ?>
						<figure class="audiotheme-module-card-overview-media">
							<iframe src="https://www.youtube.com/embed/3ApVW-5MLLU?rel=0"></iframe>
						</figure>
						<p>
							<strong><?php esc_html_e( 'Keep fans updated with live performances, tour dates and venue information.', 'audiotheme' ); ?></strong>
						</p>
						<p>
							<?php esc_html_e( "Schedule all the details about your next show, including location (address, city, state), dates, times, ticket prices and links to ticket purchasing. Set up your venue information by creating new venues and assigning shows to venues you've already created. You also have the ability to feature each venue's website, along with their contact information like email address and phone number.", 'audiotheme' ); ?>
						</p>
						<p>
							<strong><?php esc_html_e( 'Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_gig' ) ); ?>"><?php esc_html_e( 'Add a gig', 'audiotheme' ); ?></a>
						</p>
					<?php elseif ( 'videos' === $module_id ) : ?>
						<figure class="audiotheme-module-card-overview-media">
							<iframe src="https://www.youtube.com/embed/9x47jmTRUtk?rel=0"></iframe>
						</figure>
						<p>
							<strong><?php esc_html_e( 'Easily build your video galleries from over a dozen popular video services.', 'audiotheme' ); ?></strong>
						</p>
						<p>
							<?php esc_html_e( "Showcasing your videos doesn't need to be a hassle. All of our themes allow you the ability to create your video galleries by simply embedding your videos from a number of video services, including: YouTube, Vimeo, WordPress.tv, DailyMotion, blip.tv, Flickr (images and video), Viddler, Hulu, Qik, Revision3, and FunnyorDie.com.", 'audiotheme' ); ?>
						</p>
						<p>
							<strong><?php esc_html_e( 'Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>"><?php esc_html_e( 'Add a video', 'audiotheme' ); ?></a>
						</p>
					<?php else : ?>
						<?php do_action( 'audiotheme_module_card_overview', $module_id ); ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="audiotheme-module-card-actions">
				<div class="audiotheme-module-card-actions-primary">
					<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
						<span class="spinner"></span>
						<button class="button button-primary button-activate js-toggle-module"><?php esc_html_e( 'Activate', 'audiotheme' ); ?></button>
					<?php endif; ?>

					<?php if ( 'discography' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>" class="button"><?php esc_html_e( 'Add Record', 'audiotheme' ); ?></a>
					<?php elseif ( 'gigs' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_gig' ) ); ?>" class="button"><?php esc_html_e( 'Add Gig', 'audiotheme' ); ?></a>
					<?php elseif ( 'videos' == $module_id ) : ?>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>" class="button"><?php esc_html_e( 'Add Video', 'audiotheme' ); ?></a>
					<?php else : ?>
						<?php do_action( 'audiotheme_module_card_primary_button', $module_id ); ?>
					<?php endif; ?>
				</div>

				<div class="audiotheme-module-card-actions-secondary">
					<a href=""><?php esc_html_e( 'Details', 'audiotheme' ); ?></a>
				</div>
			</div>

		</div>
	<?php endforeach; ?>

</div>
