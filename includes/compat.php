<?php
/**
 * AudioTheme implementation of newer WordPress functions to maintain
 * backwards comptability if the framework is used with older versions of
 * WordPress.
 *
 * @package AudioTheme_Framework
 * @access private
 */

/**
 * Make some of the newer edit form actions backwards compatible.
 *
 * @since 1.0.0
 */
function audiotheme_edit_form_compat_actions() {
	if ( ! did_action( 'edit_form_after_title' ) ) {
		do_action( 'edit_form_after_title' );
		?>
		<script type="text/javascript">
		jQuery(function($) {
			$('.audiotheme-edit-after-title').insertBefore('#postdivrich');
		});
		</script>
		<?php
	}

	if ( ! did_action( 'edit_form_after_editor' ) ) {
		do_action( 'edit_form_after_editor' );
		?>
		<script type="text/javascript">
		jQuery(function($) {
			jQuery('.audiotheme-edit-after-editor').appendTo('#post-body-content');
		});
		</script>
		<?php
	}
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @since 1.0.0
 * @see wp-includes/functions.php in WP 3.5
 *
 * @param mixed $response Variable (usually an array or object) to encode as JSON, then print and die.
 */
if ( ! function_exists( 'wp_send_json' ) ) :
function wp_send_json( $response ) {
	@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	echo json_encode( $response );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		wp_die();
	else
		die;
}
endif;

/**
 * Send a JSON response back to an Ajax request, indicating success.
 *
 * @since 1.0.0
 * @see wp-includes/functions.php in WP 3.5
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
if ( ! function_exists( 'wp_send_json_success' ) ) :
function wp_send_json_success( $data = null ) {
	$response = array( 'success' => true );

	if ( isset( $data ) )
		$response['data'] = $data;

	wp_send_json( $response );
}
endif;

/**
 * Send a JSON response back to an Ajax request, indicating failure.
 *
 * @since 1.0.0
 * @see wp-includes/functions.php in WP 3.5
 *
 * @param mixed $data Data to encode as JSON, then print and die.
 */
if ( ! function_exists( 'wp_send_json_error' ) ) :
function wp_send_json_error( $data = null ) {
	$response = array( 'success' => false );

	if ( isset( $data ) )
		$response['data'] = $data;

	wp_send_json( $response );
}
endif;
