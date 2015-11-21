<?php
/**
 * Discography module.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Discography module class.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_Module_Discography extends AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id = 'menu-posts-audiotheme_record';

	/**
	 * Module id.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $id = 'discography';

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
	 * Retrieve the name of the module.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'Discography', 'audiotheme' );
	}

	/**
	 * Retrieve the module description.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_description() {
		return esc_html__( 'Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' );
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
	 *
	 * @return $this
	 */
	public function load() {
		// Load discography functionality.
		require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-ajax-discography.php' );
		require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-posttype-playlist.php' );
		require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-posttype-record.php' );
		require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-posttype-track.php' );
		require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-taxonomy-recordtype.php' );
		require( AUDIOTHEME_DIR . 'modules/discography/post-template.php' );

		// Load the admin interface and functionality for discography.
		if ( is_admin() ) {
			require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-screen-editrecord.php' );
			require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-screen-edittrack.php' );
			require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-screen-managerecords.php' );
			require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-screen-managetracks.php' );
			require( AUDIOTHEME_DIR . 'modules/discography/admin/class-audiotheme-screen-editrecordarchive.php' );
		}

		return $this;
	}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		$this->plugin->register_hooks( new AudioTheme_Taxonomy_RecordType( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Playlist( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Record( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Track( $this ) );
		$this->plugin->register_hooks( new AudioTheme_AJAX_Discography() );

		add_action( 'init',                   array( $this, 'register_archive' ) );
		add_action( 'template_include',       array( $this, 'template_include' ) );
		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );

		if ( is_admin() ) {
			$this->plugin->register_hooks( new AudioTheme_Screen_ManageRecords() );
			$this->plugin->register_hooks( new AudioTheme_Screen_ManageTracks() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditRecord() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditTrack() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditRecordArchive() );
		}
	}

	/**
	 * Register the discography archive.
	 *
	 * @since 1.9.0
	 */
	public function register_archive() {
		$this->plugin->modules['archives']->add_post_type_archive( 'audiotheme_record' );
	}

	/**
	 * Get the discography rewrite base. Defaults to 'music'.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_record_rewrite_base', 'videos' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Display the module overview.
	 *
	 * @since 1.9.0
	 */
	public function display_overview() {
		?>
		<figure class="audiotheme-module-card-overview-media">
			<iframe src="https://www.youtube.com/embed/ZopsZEiv1F0?rel=0" frameborder="0" allowfullscreen></iframe>
		</figure>
		<p>
			<?php esc_html_e( 'Everything you need to build your Discography is at your fingertips.', 'audiotheme' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Your discography is the window through which listeners are introduced to and discover your music on the web. Encourage that discovery on your website through a detailed and organized history of your recorded output using the AudioTheme discography feature. Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ); ?>"><?php esc_html_e( 'Add a record', 'audiotheme' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Display a button to perform the module's primary action.
	 *
	 * @since 1.9.0
	 */
	public function display_primary_button() {
		printf(
			'<a href="%s" class="button">%s</a>',
			esc_url( admin_url( 'post-new.php?post_type=audiotheme_record' ) ),
			esc_html__( 'Add Record', 'audiotheme' )
		);
	}

	/**
	 * Load discography templates.
	 *
	 * Templates should be included in an /audiotheme/ directory within the theme.
	 *
	 * @since 1.9.0
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( is_post_type_archive( array( 'audiotheme_record', 'audiotheme_track' ) ) || is_tax( 'audiotheme_record_type' ) ) {
			if ( is_post_type_archive( 'audiotheme_track' ) ) {
				$templates[] = 'archive-track.php';
			}

			if ( is_tax() ) {
				$term = get_queried_object();
				$slug = str_replace( 'record-type-', '', $term->slug );
				$taxonomy = str_replace( 'audiotheme_', '', $term->taxonomy );
				$templates[] = "taxonomy-$taxonomy-{$slug}.php";
				$templates[] = "taxonomy-$taxonomy.php";
			}

			$templates[] = 'archive-record.php';
			$template = audiotheme_locate_template( $templates );
			do_action( 'audiotheme_template_include', $template );
		} elseif ( is_singular( 'audiotheme_record' ) ) {
			$template = audiotheme_locate_template( 'single-record.php' );
			do_action( 'audiotheme_template_include', $template );
		} elseif ( is_singular( 'audiotheme_track' ) ) {
			$template = audiotheme_locate_template( 'single-track.php' );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}

	/**
	 * Add custom discography rewrite rules.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Rewrite $wp_rewrite The main rewrite object. Passed by reference.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$base    = $this->get_rewrite_base();
		$tracks  = $this->get_tracks_rewrite_base();
		$archive = $this->get_tracks_archive_rewrite_base();

		$new_rules[ $base . '/' . $archive . '/?$' ] = 'index.php?post_type=audiotheme_track';
		$new_rules[ $base . '/' . $wp_rewrite->pagination_base . '/([0-9]{1,})/?$' ] = 'index.php?post_type=audiotheme_record&paged=$matches[1]';
		$new_rules[ $base .'/([^/]+)/' . $tracks . '/([^/]+)?$' ] = 'index.php?audiotheme_record=$matches[1]&audiotheme_track=$matches[2]';
		$new_rules[ $base . '/([^/]+)/?$' ] = 'index.php?audiotheme_record=$matches[1]';
		$new_rules[ $base . '/?$' ] = 'index.php?post_type=audiotheme_record';

		$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
	}

	/**
	 * Retrieve the base slug to use for the namespace in track rewrite rules.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	protected function get_tracks_rewrite_base() {
		$slug = preg_replace( '/[^a-z0-9-_]/', '', _x( 'track', 'track permalink slug', 'audiotheme' ) );

		if ( empty( $slug ) ) {
			$slug = 'track';
		}

		return apply_filters( 'audiotheme_tracks_rewrite_base', $slug );
	}

	/**
	 * Retrieve the base slug to use for tracks archive rewrite rules.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	protected function get_tracks_archive_rewrite_base() {
		$slug = preg_replace( '/[^a-z0-9-_]/', '', _x( 'tracks', 'tracks archive permalink slug', 'audiotheme' ) );

		if ( empty( $slug ) ) {
			$slug = 'tracks';
		}

		return apply_filters( 'audiotheme_tracks_archive_rewrite_base', $slug );
	}
}
