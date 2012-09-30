<?php
class Audiotheme_Updater {
	private static $instance;
	private static $remote_api_url;
	private static $request_data;
	private static $response_key;
	private static $theme_slug;
	private static $license_key;
	private static $item_name;
	private static $version;
	private static $author;
	
	function setup( $args = array() ) {
		$theme_slug = ( isset( $args['theme_slug'] ) && ! empty( $args['theme_slug'] ) ) ? $args['theme_slug'] : get_template();
		$theme = wp_get_theme( $theme_slug );
		
		$args = wp_parse_args( $args, array(
			'remote_api_url' => 'http://audiotheme.com',
			'request_data' => array(),
			'theme_slug' => $theme_slug,
			'item_name' => $theme->get( 'Name' ),
			'license_key' => get_audiotheme_theme_option( 'license_key' ),
			'version' => $theme->get( 'Version' ),
			'author' => $theme->get( 'Author' )
		) );
		extract( $args );
		
		self::$license_key = $license_key;
		self::$item_name = $item_name;
		self::$version = $version;
		self::$theme_slug = sanitize_key( $theme_slug );
		self::$author = $author;
		self::$remote_api_url = $remote_api_url;
		self::$response_key = self::$theme_slug . '-update-response';
		
		add_filter( 'site_transient_update_themes', array( __CLASS__, 'theme_update_transient' ) );
		add_filter( 'delete_site_transient_update_themes', array( __CLASS__, 'delete_theme_update_transient' ) );
		add_action( 'load-update-core.php', array( __CLASS__, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( __CLASS__, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( __CLASS__, 'load_themes_screen' ) );
	}
	
	function load_themes_screen() {
		add_thickbox();
		add_action( 'admin_notices', array( __CLASS__, 'update_nag' ) );
	}
	
	function update_nag() {
		$theme = wp_get_theme( self::$theme_slug );
		
		$api_response = get_transient( self::$response_key );
		
		if( false === $api_response )
			return;

		$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( self::$theme_slug ), 'upgrade-theme_' . self::$theme_slug );
		$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';
		
		if ( version_compare( $theme->get( 'Version' ), $api_response->new_version, '<' ) ) {

			echo '<div id="update-nag">';
				printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.',
					$theme->get( 'Name' ),
					$api_response->new_version,
					add_query_arg( array( 'TB_iframe' => 'true', 'width' => 1024, 'height' => 800 ), $api_response->url ),
					#'#TB_inline?width=640&amp;inlineId=' . self::$theme_slug . '_changelog',
					$theme->get( 'Name' ),
					$update_url,
					$update_onclick
				);
			echo '</div>';
		}
	}
	
	function theme_update_transient( $value ) {
		$update_data = self::check_for_update();
		if ( $update_data ) {
			$value->response[ self::$theme_slug ] = $update_data;
		}
		return $value;
	}
	
	function delete_theme_update_transient() {
		delete_transient( self::$response_key );
	}
	
	function check_for_update() {

		$theme = wp_get_theme( self::$theme_slug );
		
		$update_data = get_transient( self::$response_key );
		if ( false === $update_data ) {
			$failed = false;
			
			$api_params = array( 
				'edd_action' => 'get_version',
				'license' => self::$license_key, 
				'name' => self::$item_name,
				'slug' => self::$theme_slug,
				'author' => self::$author
			);

			$response = wp_remote_post( self::$remote_api_url, array( 'timeout' => 5, 'body' => $api_params ) );
			
			// make sure the response was successful
			if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$failed = true;
			}
			
			$update_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			// @todo temporary workaround for the server not sending a URL
			if ( ! isset( $update_data->url ) && isset( $update_data->homepage ) ) {
				$update_data->url = $update_data->homepage;
			}

			if ( ! is_object( $update_data ) ) {
				$failed = true;
			}
			
			// if the response failed, try again in 30 minutes
			if ( $failed ) {
				$data = new stdClass;
				$data->new_version = $theme->get( 'Version' );
				set_transient( self::$response_key, $data, strtotime( '+30 minutes' ) );
				return false;
			}
			
			// if the status is 'ok', return the update arguments
			if ( ! $failed ) {
				$update_data->sections = maybe_unserialize( $update_data->sections );
				set_transient( self::$response_key, $update_data, strtotime( '+12 hours' ) );
			}
		}
		
		if ( version_compare( $theme->get( 'Version' ), $update_data->new_version, '>=' ) ) {
			return false;
		}
		
		return (array) $update_data;
	}
	
	function activate_license( $license ) {
		$api_params = array( 
			'edd_action' => 'activate_license', 
			'license' => $license,
			'item_name' => self::$item_name
		);
		
		$response = wp_remote_get( add_query_arg( $api_params, self::$remote_api_url ) );
		if ( is_wp_error( $response ) )
			return false;
		
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		return $license_data->license;
	}
	
	function check_license_status( $license ) {
		$api_params = array( 
			'edd_action' => 'check_license', 
			'license' => $license,
			'item_name' => self::$item_name
		);
		
		$response = wp_remote_get( add_query_arg( $api_params, self::$remote_api_url ) );
		if ( is_wp_error( $response ) )
			return false;
		
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		return $license_data->license;
	}
}
?>