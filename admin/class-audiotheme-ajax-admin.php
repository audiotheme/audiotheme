<?php
/**
 * Administration AJAX provider.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Administration AJAX provider class.
 *
 * @package AudioTheme\Administration
 * @since   1.9.0
 */
class AudioTheme_AJAX_Admin {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_audiotheme_ajax_insert_term',   array( $this, 'insert_term' ) );
	}

	/**
	 * AJAX callback to insert a new term.
	 *
	 * @since 1.9.0
	 */
	public function insert_term() {
		$response       = array();
		$taxonomy       = $_POST['taxonomy'];
		$is_valid_nonce = isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'add-term_' . $taxonomy );

		if ( ! $is_valid_nonce ) {
			$response['message'] = __( 'Unauthorized request.', 'audiotheme' );
			wp_send_json_error( $response );
		}

		$term      = empty( $_POST['term'] ) ? '' : $_POST['term'];
		$term_data = wp_insert_term( $term, $taxonomy );

		if ( is_wp_error( $term_data ) ) {
			$response['message'] = $term_data->get_error_message();
			wp_send_json_error( $response );
		}

		$response['html'] = sprintf(
			'<li><label><input type="checkbox" name="audiotheme_post_terms[%s][]" value="%d" checked="checked"> %s</label></li>',
			esc_attr( $taxonomy ),
			absint( $term_data['term_id'] ),
			$term
		);

		wp_send_json_success( $response );
	}
}
