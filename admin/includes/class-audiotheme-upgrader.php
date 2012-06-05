<?php
class AudioTheme_Upgrader {
	var $remote_api_url;
	var $request_data;
	var $response_key;
	var $theme_slug;
	
	function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'remote_api_url' => 'http://audiotheme.com/memby-webhooks/wordpress-auto-update/',
			'request_data' => array(),
			'theme_slug' => get_template()
		) );
		extract( $args );
		
		$this->theme_slug = sanitize_key( $theme_slug );
		$this->remote_api_url = $remote_api_url;
		$this->response_key = $this->theme_slug . '-update-response';
		
		// TODO: implement default license field
		$this->request_data = wp_parse_args( $request_data, array(
			'license' => ''
		) );
		
		
		add_filter( 'site_transient_update_themes', array( &$this, 'theme_update_transient' ) );
		add_filter( 'delete_site_transient_update_themes', array( &$this, 'delete_theme_update_transient' ) );
		add_action( 'load-update-core.php', array( &$this, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( &$this, 'delete_theme_update_transient' ) );
		
		add_action( 'load-themes.php', array( &$this, 'load_themes_screen' ) );
		add_action( 'load-appearance_page_theme-options', array( &$this, 'load_themes_screen' ) );
	}
	
	function load_themes_screen() {
		add_thickbox();
		add_action( 'admin_notices', array( &$this, 'update_nag' ) );
	}
	
	function update_nag() {
		$theme = wp_get_theme( $this->theme_slug );
		
		$api_response = get_transient( $this->response_key );
		$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->theme_slug ), 'upgrade-theme_' . $this->theme_slug );
		$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';
		
		if ( ! empty( $api_response->code ) ) {
			echo '<div id="update-nag">';
				if ( 'update_available' == $api_response->code ) {
					printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox">Check out what\'s new</a> or <a href="%4$s"%5$s>update now</a>.',
						$theme->get( 'Name' ),
						$api_response->wp_args->new_version,
						add_query_arg( array( 'TB_iframe' => 'true', 'width' => 1024, 'height' => 800 ), $api_response->wp_args->url ),
						$update_url,
						$update_onclick
					);
				} else {
					$update_available = false;
					if ( version_compare( $theme->get( 'Version' ), $api_response->theme->current_version, '<' ) ) {
						$update_available = true;
					}
					
					// TODO: solidify potential response codes and corresponding messages
					switch( $api_response->code ) {
						case 'empty_license':
							if ( $update_available ) {
								printf( '<strong>%1$s %2$s</strong> is available. ', $theme->get( 'Name' ), $api_response->theme->current_version );
							}
							
							printf( '<a href="%1$s">Register</a> your copy to receive automatic upgrades and support. ',
								add_query_arg( 'page', 'theme-options', admin_url( 'themes.php' ) )
							);
							printf( '<strong>Need a license key?</strong> <a href="%1$s">Purchase one now.</a>', $theme->get( 'ThemeURI' ) );
							break;
						case 'invalid_license':
							
							break;
						case 'expired_license':
							
							break;
						default:
							do_action( 'audiotheme_upgrader_nag', $api_response );
							do_action( 'audiotheme_upgrader_nag-' . $api_response->code, $api_response );
							break;
					}
				}
			echo '</div>';
		}
	}
	
	function theme_update_transient( $value ) {
		$update_data = $this->check_for_update();
		
		if ( $update_data ) {
			$value->response[ $this->theme_slug ] = $update_data;
		}
		
		return $value;
	}
	
	function delete_theme_update_transient() {
		delete_transient( $this->response_key );
	}
	
	function check_for_update() {
		global $wpdb;
		
		$theme = wp_get_theme( $this->theme_slug );
		
		$update_data = get_transient( $this->response_key );
		if ( ! $update_data ) {
			$failed = false;
			
			$request_data = wp_parse_args( array(
				'theme' => $this->theme_slug,
				'version' => $theme->get( 'Version' ),
				
				'language' => WPLANG,
				'mysql' => $wpdb->db_version(),
				'php' => phpversion(),
				'url' => home_url(),
				'wp' => get_bloginfo( 'version' )
			), $this->request_data );
			
			$response = wp_remote_post( $this->remote_api_url, array(
				'body' => $request_data,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
					'Referer' => home_url()
				)
			) );
			
			// make sure the response was successful
			if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$failed = true;
			}
			
			$update_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! is_object( $update_data ) ) {
				$failed = true;
			}
			
			// if the response failed, try again in 30 minutes
			if ( $failed ) {
				$data = new stdClass;
				$data->wp_args->new_version = $theme->get( 'Version' );
				set_transient( $this->response_key, $data, strtotime( '+30 minutes' ) );
				return false;
			}
			
			// if the status is 'ok', return the update arguments
			if ( 'ok' == $update_data->status ) {
				set_transient( $this->response_key, $update_data, strtotime( '+12 hours' ) );
			}
		}
		
		if ( version_compare( $theme->get( 'Version' ), $update_data->wp_args->new_version, '>=' ) ) {
			return false;
		}
		
		return (array) $update_data->wp_args;
	}
}
?>