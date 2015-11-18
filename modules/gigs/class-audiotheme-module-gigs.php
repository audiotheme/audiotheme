<?php
/**
 * Gigs module.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */

/**
 * Gigs module class.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */
class AudioTheme_Module_Gigs extends AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id = 'menu-posts-audiotheme_gig';

	/**
	 * Module id.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $id = 'gigs';

	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin_AudioTheme
	 */
	protected $plugin;

	/**
	 * Whether the module should show on the dashboard.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $show_in_dashboard = true;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		$this->set_name( esc_html__( 'Gigs & Venues', 'audiotheme' ) );
		$this->set_description( esc_html__( 'Share event details with your fans, including location, venue, date, time, and ticket prices.', 'audiotheme' ) );
	}

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Plugin $plugin Main plugin instance.
	 * @return $this
	 */
	public function set_plugin( AudioTheme_Plugin $plugin ) {
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * Load the module.
	 *
	 * @since 1.9.0
	 */
	public function load() {
		// Load gigs functionality.
		require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-ajax-gigs.php' );
		require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-posttype-gig.php' );
		require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-posttype-venue.php' );
		require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-gig-query.php' );
		require( AUDIOTHEME_DIR . 'modules/gigs/post-template.php' );

		// Load the admin interface and functionality for gigs and venues.
		if ( is_admin() ) {
			require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-screen-editgig.php' );
			require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-screen-editvenue.php' );
			require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-screen-managegigs.php' );
			require( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-screen-managevenues.php' );
		}
	}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		$this->plugin->register_hooks( new AudioTheme_PostType_Gig( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Venue() );
		$this->plugin->register_hooks( new AudioTheme_AJAX_Gigs() );

		add_action( 'init',                   array( $this, 'register_archive' ) );
		add_action( 'wp_loaded',              array( $this, 'register_post_connections' ) );
		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'template_redirect',      array( $this, 'template_redirect' ) );
		add_action( 'template_include',       array( $this, 'template_include' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ), 1 );

			$this->plugin->register_hooks( new AudioTheme_Screen_ManageGigs() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditGig() );
			$this->plugin->register_hooks( new AudioTheme_Screen_ManageVenues() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditVenue() );
		}
	}

	/**
	 * Register the discography archive.
	 *
	 * @since 1.9.0
	 */
	public function register_archive() {
		$this->plugin->modules['archives']->add_post_type_archive( 'audiotheme_gig' );
	}

	/**
	 * Register post connections.
	 *
	 * @since 1.9.0
	 */
	public function register_post_connections() {
		p2p_register_connection_type( array(
			'name'        => 'audiotheme_venue_to_gig',
			'from'        => 'audiotheme_venue',
			'to'          => 'audiotheme_gig',
			'cardinality' => 'one-to-many',
			'admin_box'   => false,
		) );
	}

	/**
	 * Get the gigs rewrite base. Defaults to 'shows'.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_gig_rewrite_base', 'shows' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Gig feeds and venue connections.
	 *
	 * Caches gig->venue connections and reroutes feed requests to
	 * the appropriate template for processing.
	 *
	 * @since 1.9.0
	 */
	public function template_redirect() {
		global $wp_query;

		if ( is_post_type_archive( 'audiotheme_gig' ) ) {
			p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );
		}

		$type = $wp_query->get( 'feed' );
		if ( is_feed() && 'audiotheme_gig' === $wp_query->get( 'post_type' ) ) {
			p2p_type( 'audiotheme_venue_to_gig' )->each_connected( $wp_query );

			require( AUDIOTHEME_DIR . 'modules/gigs/feed.php' );

			switch ( $type ) {
				case 'feed':
					load_template( AUDIOTHEME_DIR . 'modules/gigs/feed-rss2.php' );
					break;
				case 'ical':
					load_template( AUDIOTHEME_DIR . 'modules/gigs/feed-ical.php' );
					break;
				case 'json':
					load_template( AUDIOTHEME_DIR . 'modules/gigs/feed-json.php' );
					break;
				default:
					$message = sprintf( esc_html__( 'ERROR: %s is not a valid feed template.', 'audiotheme' ), $type );
					wp_die( esc_html( $message ), '', array( 'response' => 404 ) );
			}
			exit;
		}
	}

	/**
	 * Load gig templates.
	 *
	 * Templates should be included in an /audiotheme/ directory within the theme.
	 *
	 * @since 1.9.0
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( is_post_type_archive( 'audiotheme_gig' ) ) {
			$template = audiotheme_locate_template( 'archive-gig.php' );
			do_action( 'audiotheme_template_include', $template );
		} elseif ( is_singular( 'audiotheme_gig' ) ) {
			$template = audiotheme_locate_template( 'single-gig.php' );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}

	/**
	 * Add custom gig rewrite rules.
	 *
	 * /base/YYYY/MM/DD/(feed|ical|json)/
	 * /base/YYYY/MM/DD/
	 * /base/YYYY/MM/(feed|ical|json)/
	 * /base/YYYY/MM/
	 * /base/YYYY/(feed|ical|json)/
	 * /base/YYYY/
	 * /base/(feed|ical|json)/
	 * /base/%postname%/
	 * /base/
	 *
	 * @todo /base/tour/%tourname%/
	 *       /base/past/page/2/
	 *       /base/past/
	 *       /base/YYYY/page/2/
	 *       etc.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Rewrite $wp_rewrite The main rewrite object. Passed by reference.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$base = $this->get_rewrite_base();
		$past = $this->get_past_rewrite_base();

		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]';
		$new_rules[ $base . '/([0-9]{4})/([0-9]{1,2})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]&monthnum=$matches[2]';
		$new_rules[ $base . '/([0-9]{4})/?$' ] = 'index.php?post_type=audiotheme_gig&year=$matches[1]';
		$new_rules[ $base . '/(feed|ical|json)/?$' ] = 'index.php?post_type=audiotheme_gig&feed=$matches[1]';
		$new_rules[ $base . '/' . $past . '/' . $wp_rewrite->pagination_base . '/([0-9]{1,})/?$' ] = 'index.php?post_type=audiotheme_gig&paged=$matches[1]&audiotheme_gig_range=past';
		$new_rules[ $base . '/' . $past . '/?$' ] = 'index.php?post_type=audiotheme_gig&audiotheme_gig_range=past';
		$new_rules[ $base . '/([^/]+)/(ical|json)/?$' ] = 'index.php?audiotheme_gig=$matches[1]&feed=$matches[2]';
		$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_gig=$matches[1]';
		$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_gig';

		$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
	}

	/**
	 * Register administration scripts and styles.
	 *
	 * @since 1.9.0
	 */
	public function register_admin_assets() {
		$post_type_object = get_post_type_object( 'audiotheme_venue' );
		$base_url = set_url_scheme( AUDIOTHEME_URI . 'modules/gigs/admin' );

		wp_register_script( 'audiotheme-gig-edit', $base_url . '/js/gig-edit.bundle.min.js', array( 'audiotheme-admin', 'audiotheme-venue-manager', 'jquery-timepicker', 'jquery-ui-autocomplete', 'pikaday', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-edit', $base_url . '/js/venue-edit.bundle.min.js', array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post', 'underscore' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-venue-manager', $base_url . '/js/venue-manager.bundle.min.js', array( 'audiotheme-admin', 'jquery', 'jquery-ui-autocomplete', 'media-models', 'media-views', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
		wp_register_style( 'audiotheme-venue-manager', AUDIOTHEME_URI . 'admin/css/venue-manager.min.css', array(), '1.0.0' );

		$settings = array(
			'canPublishVenues'      => false,
			'canEditVenues'         => current_user_can( $post_type_object->cap->edit_posts ),
			'defaultTimezoneString' => get_option( 'timezone_string' ),
			'insertVenueNonce'      => false,
			'l10n'                  => array(
				'addNewVenue'  => $post_type_object->labels->add_new_item,
				'addVenue'     => esc_html__( 'Add a Venue', 'audiotheme' ),
				'edit'         => esc_html__( 'Edit', 'audiotheme' ),
				'manageVenues' => esc_html__( 'Select Venue', 'audiotheme' ),
				'select'       => esc_html__( 'Select', 'audiotheme' ),
				'selectVenue'  => esc_html__( 'Select Venue', 'audiotheme' ),
				'venues'       => $post_type_object->labels->name,
				'view'         => esc_html__( 'View', 'audiotheme' ),
			),
		);

		if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
			$settings['canPublishVenues'] = true;
			$settings['insertVenueNonce'] = wp_create_nonce( 'insert-venue' );
		}

		wp_localize_script( 'audiotheme-venue-manager', '_audiothemeVenueManagerSettings', $settings );
	}

	/**
	 * Retrieve the base slug to use for past gigs rewrite rules.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	protected function get_past_rewrite_base() {
		$slug = preg_replace( '/[^a-z0-9-_]/', '', _x( 'past', 'past gigs permalink slug', 'audiotheme' ) );

		if ( empty( $slug ) ) {
			$slug = 'past';
		}

		return apply_filters( 'audiotheme_past_gigs_rewrite_base', $slug );
	}
}
