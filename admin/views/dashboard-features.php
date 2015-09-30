<div class="audiotheme-wrap">
	<header class="audiotheme-dashboard-hero">
		<div class="audiotheme-dashboard-hero-branding">
			<h1 class="audiotheme-dashboard-hero-title"><a href="https://audiotheme.com/view/audiotheme/" target="_blank"><svg viewBox="0 0 80 40">
				<path fill="#E4002B" d="M10,40C4.5,40,0,35.5,0,30s4.5-10,10-10s10,4.5,10,10S15.5,40,10,40z M0,0l40,40V20L20,0H0z M80,0H40v14h40V0z M74,40L60,18L46,40H74z"/>
			</svg></a></h1>
			<p>
				<?php esc_html_e( 'AudioTheme has the tools you need to easily manage your gigs, discography, videos and more.', 'audiotheme' ); ?>
			</p>
		</div>
	</header>

	<div class="audiotheme-dashboard-body">
		<div class="audiotheme-dashboard-lead">
			<p>
				<?php _e( 'Gigs, Discography, and Videos are the backbone of AudioTheme. Explore each feature below or use the menu options to the left to get started.', 'audiotheme' ); ?>
			</p>
		</div>

		<div class="audiotheme-feature-section">
			<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/gigs.jpg" class="stagger-right">

			<h3><?php _e( 'Gigs &amp; Venues', 'audiotheme' ); ?></h3>
			<div class="audiotheme-feature-body">
				<p>
					<?php _e( '<strong>Keep fans updated with live performances, tour dates and venue information.', 'audiotheme' ); ?></strong>
				</p>
				<p>
					<?php _e( "Schedule all the details about your next show, including location (address, city, state), dates, times, ticket prices and links to ticket purchasing. Set up your venue information by creating new venues and assigning shows to venues you've already created. You also have the ability to feature each venue's website, along with their contact information like email address and phone number.", 'audiotheme' ); ?>
				</p>
				<p>
					<?php _e( '<strong>Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_gig' ) ); ?>"><?php _e( 'Add a gig', 'audiotheme' ); ?></a>
				</p>
			</div>
		</div>

		<div class="audiotheme-feature-section">
			<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/discography.jpg">

			<h3><?php _e( 'Discography', 'audiotheme' ); ?></h3>
			<div class="audiotheme-feature-body">
				<p>
					<?php _e( '<strong>Put together your albums, assign tracks, plug in your cover art and go.', 'audiotheme' ); ?></strong>
				</p>
				<p>
					<?php _e( 'Upload cover images, place titles and assign tracks. Everything you need to build your discography is literally at your fingertips. You can also enter links to let your music fans know where they can buy your music. We help guide you through the process to create a dynamic, user friendly discography page to enhance your online presence.', 'audiotheme' ); ?>
				</p>
				<p>
					<?php _e( '<strong>Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>"><?php _e( 'Add a record', 'audiotheme' ); ?></a>
				</p>
			</div>
		</div>

		<div class="audiotheme-feature-section">
			<img src="<?php echo AUDIOTHEME_URI; ?>admin/images/screenshots/videos.jpg" class="stagger-right">

			<h3><?php _e( 'Videos', 'audiotheme' ); ?></h3>
			<div class="audiotheme-feature-body">
				<p>
					<?php _e( '<strong>Easily build your video galleries from over a dozen popular video services.', 'audiotheme' ); ?></strong>
				</p>
				<p>
					<?php _e( "Showcasing your videos doesn't need to be a hassle. All of our themes allow you the ability to create your video galleries by simply embedding your videos from a number of video services, including: YouTube, Vimeo, WordPress.tv, DailyMotion, blip.tv, Flickr (images and video), Viddler, Hulu, Qik, Revision3, and FunnyorDie.com.", 'audiotheme' ); ?>
				</p>
				<p>
					<?php _e( '<strong>Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>"><?php _e( 'Add a video', 'audiotheme' ); ?></a>
				</p>
			</div>
		</div>
	</div>

	<div class="audiotheme-dashboard-addendum">
		<aside>
			<h1><?php _e( 'Need Help?', 'audiotheme' ); ?></h1>
			<p>
				<?php _e( 'Browse our <a href="https://audiotheme.com/faqs/" target="_blank">Frequently Asked Questions</a> or read the <a href="https://audiotheme.com/support/audiotheme/" target="_blank">AudioTheme documentation</a>.', 'audiotheme' ); ?>

			</p>
			<p>
				<?php _e( 'For additional help, visit the AudioTheme website for <a href="https://audiotheme.com/support/" target="_blank">priority support</a>.', 'audiotheme' ); ?>
			</p>
		</aside>

		<aside>
			<h1><?php _e( 'Email Updates', 'audiotheme' ); ?></h1>
			<p>
				<?php _e( 'Sign up for the latest updates, discounts, new products and more.', 'audiotheme' ); ?>
			</p>
			<form action="//audiotheme.us2.list-manage.com/subscribe/post?u=09290a3b20d0fa9f786ecf6a0&amp;id=1e2ba34b92" method="post" target="_blank" novalidate>
				<label for="mce-EMAIL" class="screen-reader-text"><?php _e( 'Email Address', 'audiotheme' ); ?></label>
				<input type="email" id="mce-EMAIL" name="EMAIL" value="">
				<input type="hidden" name="SOURCE" id="mce-SOURCE" value="Plugin">
				<input type="submit" name="subscribe" id="mc-embedded-subscribe" value="Subscribe" class="button button-primary">
			</form>
		</aside>
	</div>

	<footer class="audiotheme-dashboard-footer">
		<p>
			<a href="https://audiotheme.com/" target="_blank" class="audiotheme-footer-logo"><svg viewBox="0 0 80 40">
				<path fill="#E4002B" d="M10,40C4.5,40,0,35.5,0,30s4.5-10,10-10s10,4.5,10,10S15.5,40,10,40z M0,0l40,40V20L20,0H0z M80,0H40v14h40V0z M74,40L60,18L46,40H74z"/>
			</svg></a>
		</p>
		<p>
			AudioTheme <?php echo AUDIOTHEME_VERSION; ?> |
			<a href="https://twitter.com/AudioTheme"><?php _e( 'Follow @AudioTheme on Twitter', 'audiotheme' ); ?></a>
		</p>
	</footer>


	<br class="clear">

</div><!--end div.audiotheme-wrap-->
