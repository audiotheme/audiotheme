<?php
/**
 * Videos module.
 *
 * @package   AudioTheme\Videos
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Videos module class.
 *
 * @package AudioTheme\Videos
 * @since   2.0.0
 */
class AudioTheme_Module_Videos extends AudioTheme_Module_AbstractModule {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $admin_menu_id = 'menu-posts-audiotheme_video';

	/**
	 * Module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'videos';

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
		return esc_html__( 'Videos', 'audiotheme' );
	}

	/**
	 * Retrieve the module description.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return esc_html__( 'Embed videos from services like YouTube and Vimeo to create your own video library.', 'audiotheme' );
	}

	/**
	 * Load the module.
	 *
	 * @since 2.0.0
	 *
	 * @return $this
	 */
	public function load() {
		require( $this->plugin->get_path( 'includes/video-template.php' ) );

		return $this;
	}

	/**
	 * Register module hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		$this->plugin->register_hooks( new AudioTheme_Taxonomy_VideoCategory( $this ) );
		$this->plugin->register_hooks( new AudioTheme_PostType_Video( $this ) );
		$this->plugin->register_hooks( new AudioTheme_AJAX_Videos() );

		add_action( 'init',             array( $this, 'register_archive' ), 20 );
		add_action( 'template_include', array( $this, 'template_include' ) );

		if ( is_admin() ) {
			$this->plugin->register_hooks( new AudioTheme_Screen_ManageVideos() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditVideo() );
			$this->plugin->register_hooks( new AudioTheme_Screen_EditVideoArchive() );
		}
	}

	/**
	 * Register the discography archive.
	 *
	 * @since 2.0.0
	 */
	public function register_archive() {
		$this->plugin->modules['archives']->add_post_type_archive( 'audiotheme_video' );
	}

	/**
	 * Get the videos rewrite base. Defaults to 'videos'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_rewrite_base() {
		global $wp_rewrite;

		$front = '';
		$base  = get_option( 'audiotheme_video_rewrite_base', 'videos' );

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
			<iframe src="https://www.youtube.com/embed/9x47jmTRUtk?rel=0"></iframe>
		</figure>
		<p>
			<strong><?php esc_html_e( 'Easily build your video galleries from over a dozen popular video services.', 'audiotheme' ); ?></strong>
		</p>
		<p>
			<?php esc_html_e( "Showcasing your videos doesn't need to be a hassle. All of our themes allow you the ability to create your video galleries by simply embedding your videos from a number of video services, including: YouTube, Vimeo, WordPress.tv, DailyMotion, blip.tv, Flickr (images and video), Viddler, Hulu, Qik, Revision3, and FunnyorDie.com.", 'audiotheme' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Try it out:', 'audiotheme' ); ?></strong> <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ); ?>"><?php esc_html_e( 'Add a video', 'audiotheme' ); ?></a>
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
			esc_url( admin_url( 'post-new.php?post_type=audiotheme_video' ) ),
			esc_html__( 'Add Video', 'audiotheme' )
		);
	}

	/**
	 * Load video templates.
	 *
	 * Templates should be included in an /audiotheme/ directory within the theme.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( is_post_type_archive( 'audiotheme_video' ) || is_tax( 'audiotheme_video_category' ) ) {
			if ( is_tax() ) {
				$term = get_queried_object();
				$taxonomy = str_replace( 'audiotheme_', '', $term->taxonomy );
				$templates[] = "taxonomy-$taxonomy-{$term->slug}.php";
				$templates[] = "taxonomy-$taxonomy.php";
			}

			$templates[] = 'archive-video.php';
			$template = audiotheme_locate_template( $templates );
			do_action( 'audiotheme_template_include', $template );
		} elseif ( is_singular( 'audiotheme_video' ) ) {
			$template = audiotheme_locate_template( 'single-video.php' );
			do_action( 'audiotheme_template_include', $template );
		}

		return $template;
	}
}
