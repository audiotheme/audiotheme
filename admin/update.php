<?php
// TODO: implement update details api


add_filter( 'site_transient_update_themes', 'audiotheme_update_theme_transient' );

add_filter( 'delete_site_transient_update_themes', 'audiotheme_delete_theme_update_transient' );
add_action( 'load-update-core.php', 'audiotheme_delete_theme_update_transient' );
add_action( 'load-themes.php', 'audiotheme_delete_theme_update_transient' );


function audiotheme_update_theme_transient( $value ) {
	$theme_update_data = audiotheme_theme_update_check();

	if ( $theme_update_data ) {
		$value->response[ get_template() ] = $theme_update_data;
	}

	return $value;
}


function audiotheme_delete_theme_update_transient() {
	delete_transient( get_template() . '-update' );
}


function audiotheme_theme_update_check() {
	global $wpdb;
	
	$defaults = array(
		'license' => '',
		'server' => 'http://audiotheme.com/memby-webhooks/wordpress-auto-update/',
	);
	$support = get_theme_support( 'audiotheme-automatic-updates' );
	$update = wp_parse_args( $support[0], $defaults );
	
	$theme_slug = get_template();
	$theme = wp_get_theme( $theme_slug );
	$transient_key = $theme_slug . '-update';
	
	$data = get_transient( $transient_key );
	
	if ( ! $data ) {
		$response = wp_remote_post( $update['server'], array(
			'body' => array(
				'license' => $update['license'],
				'theme' => $theme_slug,
				'version' => $theme->get( 'Version' ),
				'wp' => get_bloginfo( 'version' ),
				'php' => phpversion(),
				'mysql' => $wpdb->db_version(),
				'url' => home_url()
			),
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
				'Referer' => home_url()
			),
			'timeout' => 10
		) );
		
		$data = wp_remote_retrieve_body( $response );
		
		if ( is_wp_error( $data ) || ! is_serialized( $data ) ) {
			set_transient( $transient_key, array( 'new_version' => $theme->get( 'Version' ) ), strtotime( '+1 hour' ) );
			return false;
		}

		$data = maybe_unserialize( $data );
		set_transient( $transient_key, $data, strtotime( '+12 hours' ) );
	}
	
	if ( version_compare( $theme->get( 'Version' ), $data['new_version'], '>=' ) ) {
		return false;
	}
	
	return $data;
}
?>