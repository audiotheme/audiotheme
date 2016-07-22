<?php
/**
 * Upgrade manager.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Upgrade manager class.
 *
 * @package AudioTheme\Administration
 * @since   2.0.0
 */
class AudioTheme_UpgradeManager {
	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );
	}

	/**
	 * Upgrade routine.
	 *
	 * @since 2.0.0
	 */
	public function maybe_upgrade() {
		$saved_version   = get_option( 'audiotheme_version', '0' );
		$current_version = AUDIOTHEME_VERSION;

		if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
			$this->upgrade_170();
		}

		if ( version_compare( $saved_version, '2.0.0', '<' ) ) {
			$this->upgrade_200();
		}

		if ( '0' === $saved_version || version_compare( $saved_version, $current_version, '<' ) ) {
			update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
		}
	}

	/**
	 * Upgrade routine for version 1.7.0.
	 *
	 * @since 2.0.0
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
	 * Upgrade routine for version 2.0.0.
	 *
	 * @since 2.0.0
	 */
	protected function upgrade_200() {
		global $wpdb;

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

		// Add autoloaded options to prevent extra queries on every request.
		$options = array(
			'audiotheme_inactive_modules' => array(),
			'audiotheme_license_key'      => '',
		);

		foreach ( $options as $name => $value ) {
			if ( ! get_option( $name ) ) {
				update_option( $name, $value );
			}
		}

		$p2p_table = $wpdb->prefix . 'p2p';

		// Bail if the P2P table doesn't exist.
		if ( $p2p_table !== $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $p2p_table ) ) ) {
			return;
		}

		// Copy the venue ID from P2P to gig meta.
		$wpdb->query(
			"INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value )
			SELECT p2p_to AS post_id, '_audiotheme_venue_id' AS meta_key, p2p_from AS meta_value
			FROM $p2p_table AS p2p
			WHERE
				p2p.p2p_type = 'audiotheme_venue_to_gig' AND
				NOT EXISTS (
					SELECT post_id FROM $wpdb->postmeta WHERE post_id = p2p.p2p_to AND meta_key = '_audiotheme_venue_id'
				)
			GROUP BY p2p_to"
		);

		// Copy the venue guid from P2P to gig meta.
		$wpdb->query(
			"INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value )
			SELECT p2p.p2p_to AS post_id, '_audiotheme_venue_guid' AS meta_key, p.guid AS meta_value
			FROM $p2p_table AS p2p
			INNER JOIN $wpdb->posts AS p ON p.ID = p2p.p2p_from
			WHERE
				p2p.p2p_type = 'audiotheme_venue_to_gig' AND
				NOT EXISTS (
					SELECT post_id FROM $wpdb->postmeta WHERE post_id = p2p.p2p_to AND meta_key = '_audiotheme_venue_guid'
				)
			GROUP BY p2p_to"
		);
	}
}
