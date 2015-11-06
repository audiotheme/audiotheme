<div class="wrap">
	<h1><?php _e( 'Settings', 'audiotheme' ); ?></h1>

	<form action="options.php" method="post">
		<?php settings_fields( 'audiotheme-settings' ); ?>
		<?php do_settings_sections( 'audiotheme-settings' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
