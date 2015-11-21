<?php
/**
 * Upgrades.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Upgrade class.
 *
 * @package AudioTheme\Administration
 * @since   1.9.0
 */
class AudioTheme_Upgrade {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin_AudioTheme
	 */
	protected $plugin;

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
		add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );
	}

	/**
	 * Upgrade routine.
	 *
	 * @since 1.9.0
	 */
	public function maybe_upgrade() {
		$saved_version   = get_option( 'audiotheme_version', '0' );
		$current_version = AUDIOTHEME_VERSION;

		if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
			$this->upgrade_170();
		}

		if ( version_compare( $saved_version, '1.9.0', '<' ) ) {
			$this->upgrade_190();
		}

		if ( '0' === $saved_version || version_compare( $saved_version, $current_version, '<' ) ) {
			update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
		}
	}

	/**
	 * Upgrade routine for version 1.7.0.
	 *
	 * @since 1.9.0
	 */
	protected function upgrade_170() {
		// Update record types.
		$terms = get_terms( 'audiotheme_record_type', array( 'get' => 'all' ) );

		if ( empty( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			$name = get_audiotheme_record_type_string( $term->slug );
			$name = empty( $name ) ? ucwords( str_replace( array( 'record-type-', '-' ), array( '', ' ' ), $term->name ) ) : $name;
			$slug = str_replace( 'record-type-', '', $term->slug );

			$result = wp_update_term( $term->term_id, 'audiotheme_record_type', array(
				'name' => $name,
				'slug' => $slug,
			) );

			if ( is_wp_error( $result ) ) {
				// Update the name only. We'll account for the 'record-type-' prefix.
				wp_update_term( $term->term_id, 'audiotheme_record_type', array(
					'name' => $name,
				) );
			}
		}
	}

	/**
	 * Upgrade routine for version 1.9.0.
	 *
	 * @since 1.9.0
	 */
	protected function upgrade_190() {
		// Add the archive post type to its metadata.
		if ( $archives = get_option( 'audiotheme_archives_inactive' ) ) {
			foreach ( $archives as $post_type => $post_id ) {
				update_post_meta( $post_id, 'archive_for_post_type', $post_type );
			}

			// Empty the option, but keep it around to prevent an extra SQL query.
			update_option( 'audiotheme_archives_inactive', array() );
		}

		// Add the archive post type to its metadata.
		if ( $archives = get_option( 'audiotheme_archives' ) ) {
			foreach ( $archives as $post_type => $post_id ) {
				update_post_meta( $post_id, 'archive_for_post_type', $post_type );
			}
		}
	}
}
