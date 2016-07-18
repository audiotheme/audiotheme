<?php
/**
 * View to display a dashboard screen footer.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */
?>

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
			<input type="email" id="mce-EMAIL" name="EMAIL" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
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

</div>
