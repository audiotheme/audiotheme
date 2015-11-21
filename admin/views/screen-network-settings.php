<?php
/**
 * View to display the network settings screen.
 *
 * @package   AudioTheme\Settings
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */
?>

<div class="wrap">
	<h1><?php _e( 'AudioTheme Settings', 'audiotheme' ); ?></h1>

	<form action="edit.php?action=audiotheme-save-network-settings" method="post">
		<?php settings_fields( 'audiotheme-network-settings' ); ?>
		<?php do_settings_sections( 'audiotheme-network-settings' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
