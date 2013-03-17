<div class="wrap about-wrap audiotheme-dashboard audiotheme-dashboard-features">
	<div class="audiotheme-dashboard-intro">
		<h1><?php _e( 'Welcome to AudioTheme', 'audiotheme-i18n' ); ?></h1>

		<div class="about-text">
			<?php _e( 'A platform allowing people like you to easily manage your website and own your content. We hope to dramatically improve the quality and experience for everyone involved. Give it a try and let us know what you think.', 'audiotheme-i18n' ); ?>
		</div>

		<a href="http://audiotheme.com/" target="_blank" class="wp-badge audiotheme-badge"><?php _e( sprintf( 'Version %s', AUDIOTHEME_VERSION ), 'audiotheme-i18n' ); ?></a>
	</div>

	<div class="audiotheme-about-banner">
		<h2><?php _e( 'Premium Websites for<br>Bands, Artists, Musicians &amp; Labels.', 'audiotheme-i18n' ); ?></h2>
		<p>
			<?php _e( 'Let your site be heard.', 'audiotheme-i18n' ); ?>
		</p>
	</div>

	<div class="changelog">
		<h3><?php _e( 'Take Control of Your Content', 'audiotheme-i18n' ); ?></h3>
		<div class="feature-section col two-col">
			<div class="">
				<p>
					<?php _e( 'AudioTheme gives you the <strong>power to add your own content</strong>, including gigs, discography, videos and more.', 'audiotheme-i18n' ); ?>
				</p>
				<p>
					<?php _e( 'Remember the days of having to rely on someone else to make updates to your website? Well, you can kiss those days (and that person) adios.', 'audiotheme-i18n' ); ?>
				</p>
			</div>

			<div class="last-feature">
				<p>
					<?php _e( 'With AudioTheme, you control all the content posted to your site.', 'audiotheme-i18n' ); ?>
				</p>
				<p>
					<?php _e( 'Through WordPress, you can easily add, update and edit all of your content, from tour dates to video to audio to photos to well, everything! This is your website and you have the power to reign over it.', 'audiotheme-i18n' ); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="changelog audiotheme-feature-section">
		<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/gigs.jpg" class="stagger-right">

		<h3><?php _e( 'Gigs &amp; Venues', 'audiotheme-i18n' ); ?></h3>
		<div class="feature-section">
			<p>
				<?php _e( '<strong>Keep fans updated with live performances, tour dates and venue information.', 'audiotheme-i18n' ); ?></strong>
			</p>
			<p>
				<?php _e( "Schedule all the details about your next show, including location (address, city, state), dates, times, ticket prices and links to ticket purchasing. Set up your venue information by creating new venues and assigning shows to venues you've already created. You also have the ability to feature each venue's website, along with their contact information like email address and phone number.", 'audiotheme-i18n' ); ?>
			</p>
			<p>
				<?php _e( '<strong>Try it out:', 'audiotheme-i18n' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_gig' ) ); ?>"><?php _e( 'Add a gig', 'audiotheme-i18n' ); ?></a>
			</p>
		</div>
	</div>

	<div class="changelog audiotheme-feature-section">
		<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/discography.jpg">

		<h3><?php _e( 'Discography', 'audiotheme-i18n' ); ?></h3>
		<div class="feature-section">
			<p>
				<?php _e( '<strong>Put together your albums, assign tracks, plugin your cover art and go.', 'audiotheme-i18n' ); ?></strong>
			</p>
			<p>
				<?php _e( 'Upload cover images, place titles and assign tracks. Everything you need to build your discography is literally at your fingertips. You can also enter purchase URLs to let your music fans know where they cna buy your music. We help guide you through the process to create a dynamic, user friendly discography page.', 'audiotheme-i18n' ); ?>
			</p>
			<p>
				<?php _e( '<strong>Try it out:', 'audiotheme-i18n' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>"><?php _e( 'Add a record', 'audiotheme-i18n' ); ?></a>
			</p>
		</div>
	</div>

	<div class="changelog audiotheme-feature-section">
		<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/videos.jpg" class="stagger-right">

		<h3><?php _e( 'Videos', 'audiotheme-i18n' ); ?></h3>
		<div class="feature-section">
			<p>
				<?php _e( '<strong>Easily build your video galleries from over a dozen popular video services.', 'audiotheme-i18n' ); ?></strong>
			</p>
			<p>
				<?php _e( "Showcasing your videos doesn't need to be a hassle. All of our themes allow you the ability to create your video galleries by simply embedding your videos from a number of video services, including: YouTube, Vimeo, WordPress.tv, DailyMotion, blip.tv, Flickr (images and video), Viddler, Hulu, Qik, Revision3, and FunnyorDie.com.", 'audiotheme-i18n' ); ?>
			</p>
			<p>
				<?php _e( '<strong>Try it out:', 'audiotheme-i18n' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>"><?php _e( 'Add a video', 'audiotheme-i18n' ); ?></a>
			</p>
		</div>
	</div>

</div><!--end div.wrap-->