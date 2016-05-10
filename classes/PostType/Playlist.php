<?php
/**
 * Cue playlist post type and integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for integration with the Cue playlist post type.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_PostType_Playlist {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Post type name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $post_type = 'cue_playlist';

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
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 15 );
		add_action( 'print_media_templates', array( $this, 'print_templates' ) );
	}

	/**
	 * Enqueue playlist scripts and styles.
	 *
	 * @since 1.9.0
	 */
	public function enqueue_assets() {
		if ( 'cue_playlist' !== get_current_screen()->id ) {
			return;
		}

		wp_enqueue_style( 'audiotheme-playlist-admin', $this->plugin->get_url( 'admin/css/playlist.css' ) );

		wp_enqueue_script(
			'audiotheme-playlist-admin',
			$this->plugin->get_url( 'admin/js/playlist.js' ),
			array( 'cue-admin' ),
			'1.0.0',
			true
		);

		wp_localize_script( 'audiotheme-playlist-admin', '_audiothemePlaylistSettings', array(
			'l10n' => array(
				'frameTitle'        => esc_html__( 'AudioTheme Tracks', 'audiotheme' ),
				'frameMenuItemText' => esc_html__( 'Add from AudioTheme', 'audiotheme' ),
				'frameButtonText'   => esc_html__( 'Add Tracks', 'audiotheme' ),
			),
		) );
	}

	/**
	 * Print playlist JavaScript templates.
	 *
	 * @since 1.9.0
	 */
	public function print_templates() {
		?>
		<script type="text/html" id="tmpl-audiotheme-playlist-record">
			<div class="audiotheme-playlist-record-header">
				<img src="{{ data.thumbnail }}">
				<h4 class="audiotheme-playlist-record-title"><em>{{ data.title }}</em> {{ data.artist }}</h4>
			</div>

			<ol class="audiotheme-playlist-record-tracks">
				<# _.each( data.tracks, function( track ) { #>
					<li class="audiotheme-playlist-record-track" data-id="{{ track.id }}">
						<span class="audiotheme-playlist-record-track-cell">
							{{{ track.title }}}
						</span>
					</li>
				<# }); #>
			</ol>
		</script>
		<?php
	}
}
