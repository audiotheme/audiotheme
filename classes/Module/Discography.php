<?php
/**
 * Discography module.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Discography module class.
 *
 * @package AudioTheme\Discography
 * @since   2.0.0
 */
class AudioTheme_Module_Discography extends AudioTheme_Module_AbstractModule {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $admin_menu_id = 'menu-posts-audiotheme_record';

	/**
	 * Module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'discography';

	/**
	 * Whether the module should show on the dashboard.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $show_in_dashboard = true;

	/**
	 * Retrieve the name of the module.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'Discography', 'audiotheme' );
	}

	/**
	 * Retrieve the module description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return esc_html__( 'Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' );
	}

	/**
	 * Load the module.
	 *
	 * @since 2.0.0
	 *
	 * @return $this
	 */
	public function load() {
		require( $this->plugin->get_path( 'includes/discography-template.php' ) );

		return $this;
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		$this->plugin->register_hooks( new AudioTheme_Taxonomy_RecordType( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Playlist( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Record( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Track( $this ) );
		$this->plugin->register_hooks( new AudioTheme_AJAX_Discography() );

		add_action( 'init',                   array( $this, 'register_archive' ), 20 );
		add_action( 'template_include',       array( $this, 'template_include' ) );
		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'wp_footer',              array( $this, 'maybe_print_jsonld' ) );

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
	 * @since 2.0.0
	 */
	public function register_archive() {
		$this->plugin->modules['archives']->add_post_type_archive( 'audiotheme_record' );
	}

	/**
	 * Get the discography rewrite base. Defaults to 'music'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_record_rewrite_base', 'music' );

		if ( $wp_rewrite->using_index_permalinks() ) {
			$front = $wp_rewrite->index . '/';
		}

		return $front . $base;
	}

	/**
	 * Display the module overview.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * Print a JSON-LD tag for records on the record archive.
	 *
	 * @since 2.0.3
	 */
	public function maybe_print_jsonld() {
		if ( ! is_singular( 'audiotheme_record' ) ) {
			return;
		}

		$this->print_jsonld( $GLOBALS['wp_query']->posts );
	}

	/**
	 * Retrieve the base slug to use for the namespace in track rewrite rules.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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

	/**
	 * Print a JSON-LD script tag for a list of posts.
	 *
	 * @since 2.0.3
	 *
	 * @param array $posts Array of posts.
	 */
	protected function print_jsonld( $posts ) {
		$items = array();

		foreach ( $posts as $post ) {
			$items[] = $this->prepare_record_for_jsonld( $post );
		}

		printf(
			'<script type="application/ld+json">%s</script>',
			wp_json_encode( $items )
		);
	}

	/**
	 * Format a record for JSON-LD.
	 *
	 * @since 2.0.3
	 *
	 * @param  WP_Post $post Record post object.
	 * @return array
	 */
	protected function prepare_record_for_jsonld( $post ) {
		$item = array(
			'@context'    => 'http://schema.org',
			'@type'       => 'MusicAlbum',
			'name'        => esc_html( get_the_title( $post ) ),
			'url'         => esc_url( get_permalink( $post ) ),
		);

		$artist = get_audiotheme_record_artist( $post->ID );
		if ( ! empty( $artist ) ) {
			$item['byArtist'] = array(
				'@type' => 'MusicGroup',
				'name'  => esc_html( $artist ),
			);
		}

		$released = get_audiotheme_record_release_year( $post->ID );
		if ( ! empty( $released ) ) {
			$item['dateCreated'] = esc_html( $released );
		}

		$genre = get_audiotheme_record_genre( $post->ID );
		if ( ! empty( $genre ) ) {
			$item['genre'] = esc_html( $genre );
		}

		if ( has_post_thumbnail() ) {
			$item['image'] = esc_url( get_the_post_thumbnail_url( $post, 'full' ) );
		}

		$tracks = get_audiotheme_record_tracks( $post->ID );
		if ( ! empty( $tracks ) ) {
			$item['numTracks'] = count( $tracks );

			foreach ( $tracks as $track ) {
				$item['track'][] = array(
					'@type' => 'MusicRecording',
					'name'  => esc_html( get_the_title( $track->ID ) ),
					'url'   => esc_url( get_permalink( $track->ID ) ),
				);
			}
		}

		$links = get_audiotheme_record_links( $post->ID );
		if ( ! empty( $links ) ) {
			foreach ( $links as $link ) {
				$item['offers'][] = array(
					'@type'       => 'Offer',
					'url'         => esc_url( $link['url'] ),
					'description' => esc_html( $link['name'] ),
				);
			}
		}

		return apply_filters( 'audiotheme_prepare_record_for_jsonld', $item, $post );
	}
}
