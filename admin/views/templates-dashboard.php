<?php
/**
 * Dashboard Underscore.js templates.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */
?>

<script type="text/html" id="tmpl-audiotheme-module-modal-header">
	<button class="left dashicons dashicons-no js-previous"><span class="screen-reader-text"><?php esc_html_e( 'Show previous module', 'audiotheme' ); ?></span></button>
	<button class="right dashicons dashicons-no js-next"><span class="screen-reader-text"><?php esc_html_e( 'Show next module', 'audiotheme' ); ?></span></button>
	<button class="close dashicons dashicons-no js-close"><span class="screen-reader-text"><?php esc_html_e( 'Close overlay', 'audiotheme' ); ?></span></button>
</script>

<script type="text/html" id="tmpl-audiotheme-module-modal-content">
	<div class="audiotheme-overlay-content-primary">
		<h1 class="audiotheme-overlay-content-title">{{{ data.name }}}</h1>
		<div class="audiotheme-overlay-content-body">{{{ data.overview }}}</div>
	</div>
	<div class="audiotheme-overlay-content-secondary">
		{{{ data.media }}}
	</div>
</script>
