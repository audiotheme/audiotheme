<?php
/**
 * Deprecated functions.
 *
 * These will be removed in a future version.
 *
 * @package   AudioTheme\Deprecated
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Set up the admin.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_admin_setup() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Check for AudioTheme framework and theme updates.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_update() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display a notice to register if the license key is empty.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $notice The default notice.
 * @return string
 */
function audiotheme_update_notice( $notice ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$settings_page = is_network_admin() ? 'network/settings.php' : 'admin.php';

	$notice  = sprintf(
		'<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?',
		esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( $settings_page ) ) )
	);
	$notice .= ' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">Purchase one now</a>';

	return $notice;
}

/**
 * Disable SSL verification when interacting with audiotheme.com.
 *
 * Prevents automatic updates from failing when 'sslverify' is true.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array  $r Request args.
 * @param string $url URI resource.
 * @return array Filtered request args.
 */
function audiotheme_update_request( $r, $url ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $r;
}

/**
 * Sort the admin menu.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_sort_menu() {
	global $menu;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( is_network_admin() || ! $menu ) {
		return;
	}

	$menu = array_values( $menu ); // Re-key the array.

	audiotheme_menu_move_item( 'audiotheme', 'separator1', 'before' );

	$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
	audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

	// Reverse the order and always insert them after the main AudioTheme menu item.
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'audiotheme' );
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme' );
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_gig', 'audiotheme' );

	audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
}

/**
 * Add current screen ID as CSS class to the body element.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $classes Body classes.
 * @return string
 */
function audiotheme_admin_body_class( $classes ) {
	global $post;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$classes .= ' screen-' . sanitize_html_class( get_current_screen()->id );

	if ( 'audiotheme_archive' === get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID ) ) {
		$classes .= ' ' . $post_type . '-archive';
	}

	return implode( ' ', array_unique( explode( ' ', $classes ) ) );
}

/**
 * General custom post type columns.
 *
 * This hook is run for all custom columns, so the column name is prefixed to
 * prevent potential conflicts.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $column_name Column identifier.
 * @param int    $post_id Post ID.
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	switch ( $column_name ) {
		case 'audiotheme_image' :
			printf( '<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $post_id ) ),
				get_the_post_thumbnail( $post_id, array( 60, 60 ) )
			);
			break;
	}
}

/**
 * Save custom taxonomy terms when a post is saved.
 *
 * @since 1.7.0
 * @deprecated 2.0.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post Post object.
 */
function audiotheme_update_post_terms( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision = wp_is_post_revision( $post_id );

	// Bail if the data shouldn't be saved.
	if ( $is_autosave || $is_revision || empty( $_POST['audiotheme_post_terms'] ) ) {
		return;
	}

	foreach ( $_POST['audiotheme_post_terms'] as $taxonomy => $term_ids ) {
		// Don't save if intention can't be verified.
		if ( ! isset( $_POST[ $taxonomy . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $taxonomy . '_nonce' ], 'save-post-terms_' . $post_id ) ) {
			continue;
		}

		$term_ids = array_map( 'absint', $term_ids );
		wp_set_object_terms( $post_id, $term_ids, $taxonomy );
	}
}

/**
 * Upgrade routine.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_upgrade() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$saved_version   = get_option( 'audiotheme_version', '0' );
	$current_version = AUDIOTHEME_VERSION;

	if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
		audiotheme_upgrade_170();
	}

	if ( '0' === $saved_version || version_compare( $saved_version, $current_version, '<' ) ) {
		update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
	}
}

/**
 * Upgrade routine for version 1.7.0.
 *
 * @since 1.7.0
 * @deprecated 2.0.0
 */
function audiotheme_upgrade_170() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	// Update record types.
	$terms = get_terms( 'audiotheme_record_type', array( 'get' => 'all' ) );
	if ( ! empty( $terms ) ) {
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
}

/**
 * Set up the framework dashboard.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_admin_init() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add AudioTheme themes to a site option so they can be checked for updates
 * when in multsite mode.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param string $theme Theme slug.
 * @param array  $api_args Optional. Arguments to send to the remote API.
 */
function audiotheme_update_themes_list( $theme, $api_args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( ! is_multisite() ) {
		return;
	}

	$themes = (array) get_site_option( 'audiotheme_themes' );

	if ( ! array_key_exists( $theme, $themes ) || $themes[ $theme ] !== $api_args ) {
		$themes[ $theme ] = wp_parse_args( $api_args, array( 'slug' => $theme ) );
		update_site_option( 'audiotheme_themes', $themes );
	}
}

/**
 * Enqueue admin scripts and styles.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_enqueue_admin_scripts() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	wp_enqueue_script( 'audiotheme-admin' );
	wp_enqueue_style( 'audiotheme-admin' );
}

/**
 * Build the framework admin menu.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_admin_menu() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register default global settings.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_register_settings() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Manually save network settings.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_save_network_settings() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display the system data tables.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_settings_system_section() {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$data = audiotheme_system_info();

	$sections = array(
		array(
			'section' => 'AudioTheme',
			'keys'    => array( 'audiotheme_version', 'theme', 'theme_version', 'child_theme', 'child_theme_version' ),
		),
		array(
			'section' => 'WordPress',
			'keys'    => array( 'home_url', 'site_url', 'wp_version', 'wp_lang', 'wp_memory_limit', 'wp_debug_mode', 'wp_max_upload_size' ),
		),
		array(
			'section' => 'Environment',
			'keys'    => array( 'web_server', 'php_version', 'mysql_version', 'php_post_max_size', 'php_time_limit', 'php_safe_mode' ),
		),
		array(
			'section' => 'Browser',
			'keys'    => array( 'user_agent' ),
		),
	);

	foreach ( $sections as $section ) :
		?>
		<table class="audiotheme-system-info widefat">
			<thead>
				<tr>
					<th colspan="2"><?php echo esc_html( $section['section'] ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $section['keys'] as $key ) {
					if ( isset( $data[ $key ] ) ) {
						printf( '<tr><th scope="row">%s</th><td>%s</td></tr>',
							esc_html( $data[ $key ]['label'] ),
							esc_html( $data[ $key ]['value'] )
						);
					}
				}
				?>
			</tbody>
		</table>
		<?php
	endforeach;
	?>
	<script type="text/javascript">
	jQuery(function($) {
		$('#audiotheme-system-info-export').on('focus', function() {
			$(this).select();
		});
	});
	</script>
	<?php
}

/**
 * Display the main dashboard screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_features_screen() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * License section description callback.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * A custom callback to display the field for entering and activating a license key.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $args Array of arguments to modify output.
 */
function audiotheme_dashboard_license_input( $args ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Send a request to the remote API to activate the license for the current
 * site.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_ajax_activate_license() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Clear the license status option when the key is changed.
 *
 * Forces the new key to be activated.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $oldvalue Old option value.
 * @param array $newvalue New option value.
 */
function audiotheme_license_key_option_update( $oldvalue, $newvalue ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Clear the license status option if an update response was invalid.
 *
 * Forces the license key to be reactivated.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param object $response Update response.
 */
function audiotheme_license_clear_status( $response ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Custom user contact fields.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $contactmethods List of contact methods.
 * @return array
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	$contactmethods['twitter'] = 'Twitter Username';
	$contactmethods['facebook'] = 'Facebook URL';
	return $contactmethods;
}

/**
 * Retrieve system data.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @return array
 */
function audiotheme_system_info( $args = array() ) {
	global $wpdb;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$args = wp_parse_args( $args, array(
		'format' => '',
	) );

	$theme = wp_get_theme( get_template() );

	$data = array(
		'home_url' => array(
			'label' => 'Home URL',
			'value' => home_url(),
		),
		'site_url' => array(
			'label' => 'Site URL',
			'value' => site_url(),
		),
		'wp_lang' => array(
			'label' => 'WP Language',
			'value' => defined( 'WPLANG' ) ? WPLANG : get_option( 'WPLANG' ),
		),
		'wp_version' => array(
			'label' => 'WP Version',
			'value' => get_bloginfo( 'version' ) . ( ( is_multisite() ) ? ' (WPMU)' : '' ),
		),
		'web_server' => array(
			'label' => 'Web Server Info',
			'value' => $_SERVER['SERVER_SOFTWARE'],
		),
		'php_version' => array(
			'label' => 'PHP Version',
			'value' => phpversion(),
		),
		'mysql_version' => array(
			'label' => 'MySQL Version',
			'value' => $wpdb->db_version(),
		),
		'wp_memory_limit' => array(
			'label' => 'WP Memory Limit',
			'value' => WP_MEMORY_LIMIT,
		),
		'wp_debug_mode' => array(
			'label' => 'WP Debug Mode',
			'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
		),
		'wp_max_upload_size' => array(
			'label' => 'WP Max Upload Size',
			'value' => size_format( wp_max_upload_size() ),
		),
		'php_post_max_size' => array(
			'label' => 'PHP Post Max Size',
			'value' => ini_get( 'post_max_size' ),
		),
		'php_time_limit' => array(
			'label' => 'PHP Time Limit',
			'value' => ini_get( 'max_execution_time' ),
		),
		'php_safe_mode' => array(
			'label' => 'PHP Safe Mode',
			'value' => ( ini_get( 'safe_mode' ) ) ? 'Yes' : 'No',
		),
		'user_agent' => array(
			'label' => 'User Agent',
			'value' => $_SERVER['HTTP_USER_AGENT'],
		),
		'audiotheme_version' => array(
			'label' => 'AudioTheme Version',
			'value' => AUDIOTHEME_VERSION,
		),
		'theme' => array(
			'label' => 'Theme',
			'value' => $theme->get( 'Name' ),
		),
		'theme_version' => array(
			'label' => 'Theme Version',
			'value' => $theme->get( 'Version' ),
		),
	);

	if ( get_template() !== get_stylesheet() ) {
		$theme = wp_get_theme();

		$data['child_theme'] = array(
			'label' => 'Child Theme',
			'value' => $theme->get( 'Name' ),
		);

		$data['child_theme_version'] = array(
			'label' => 'Child Theme',
			'value' => $theme->get( 'Version' ),
		);
	}

	if ( 'plaintext' === $args['format'] ) {
		$plain = '';

		foreach ( $data as $key => $info ) {
			$plain .= $info['label'] . ': ' . $info['value'] . "\n";
		}

		$data = trim( $plain );
	}

	return $data;
}

/**
 * Customizable submit meta box.
 *
 * @see post_submit_meta_box()
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Post object.
 * @param array   $metabox Additional meta box arguments.
 */
function audiotheme_post_submit_meta_box( $post, $metabox ) {
	global $action;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$defaults = array(
		'force_delete' => false,
		'show_publish_date' => true,
		'show_statuses' => array(
			'pending' => 'Pending Review',
		),
		'show_visibility' => true,
	);

	$args = apply_filters( 'audiotheme_post_submit_meta_box_args', $metabox['args'], $post );
	$args = wp_parse_args( $metabox['args'], $defaults );
	extract( $args, EXTR_SKIP );

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	?>

	<div class="submitbox" id="submitpost">

		<div id="minor-publishing">

			<!-- Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key. -->
			<div style="display: none"><?php submit_button( 'Save', 'button', 'save' ); ?></div>


			<?php
			/**
			 * Save/Preview buttons
			 */
			?>
			<div id="minor-publishing-actions">
				<div id="save-action">
					<?php if ( 'publish' !== $post->post_status && 'future' !== $post->post_status && 'pending' !== $post->post_status ) { ?>
						<input type="submit" name="save" id="save-post" value="Save Draft" class="button" <?php if ( 'private' === $post->post_status ) { echo 'style="display: none"'; } ?>>
					<?php } elseif ( 'pending' === $post->post_status && $can_publish ) { ?>
						<input type="submit" name="save" id="save-post" value="Save as Pending" class="button">
					<?php } ?>

					<?php audiotheme_admin_spinner( array( 'id' => 'draft-ajax-loading' ) ); ?>
				</div>

				<div id="preview-action">
					<?php
					if ( 'publish' === $post->post_status ) {
						$preview_link = get_permalink( $post->ID );
						$preview_button = 'Preview Changes';
					} else {
						$preview_link = set_url_scheme( get_permalink( $post->ID ) );
						$preview_link = apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) );
						$preview_button = 'Preview';
					}
					?>
					<a class="preview button" href="<?php echo esc_url( $preview_link ); ?>" target="wp-preview" id="post-preview"><?php echo esc_html( $preview_button ); ?></a>
					<input type="hidden" name="wp-preview" id="wp-preview" value="">
				</div>

				<div class="clear"></div>
			</div><!--end div#minor-publishing-actions-->


			<div id="misc-publishing-actions">

				<?php
				/**
				 * Post status
				 */
				if ( false !== $show_statuses ) : ?>
					<div class="misc-pub-section">
						<label for="post_status">Status:</label>
						<span id="post-status-display">
							<?php
							switch ( $post->post_status ) {
								case 'private':
									'Privately Published';
									break;
								case 'publish':
									'Published';
									break;
								case 'future':
									'Scheduled';
									break;
								case 'pending':
									'Pending Review';
									break;
								case 'draft':
								case 'auto-draft':
									'Draft';
									break;
							}
							?>
						</span>

						<?php if ( 'publish' === $post->post_status || 'private' === $post->post_status || ( $can_publish && count( $show_statuses ) ) ) { ?>
							<a href="#post_status" class="edit-post-status hide-if-no-js" <?php if ( 'private' === $post->post_status ) { echo 'style="display: none"'; } ?>>Edit</a>

							<div id="post-status-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ( 'auto-draft' === $post->post_status ) ? 'draft' : $post->post_status ); ?>">
								<select name="post_status" id="post_status">
									<?php if ( 'publish' === $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'publish' ); ?>>Published</option>
									<?php elseif ( 'private' === $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'private' ); ?>>Privately Published</option>
									<?php elseif ( 'future' === $post->post_status ) : ?>
										<option value="future" <?php selected( $post->post_status, 'future' ); ?>>Scheduled</option>
									<?php endif; ?>

									<?php if ( array_key_exists( 'pending', $show_statuses ) ) : ?>
										<option value="pending" <?php selected( $post->post_status, 'pending' ); ?>>Pending Review</option>
									<?php endif; ?>

									<?php if ( 'auto-draft' === $post->post_status ) : ?>
										<option value="draft" <?php selected( $post->post_status, 'auto-draft' ); ?>>Draft</option>
									<?php else : ?>
										<option value="draft" <?php selected( $post->post_status, 'draft' ); ?>>Draft</option>
									<?php endif; ?>
								</select>
								 <a href="#post_status" class="save-post-status hide-if-no-js button">OK</a>
								 <a href="#post_status" class="cancel-post-status hide-if-no-js">Cancel</a>
							</div>
						<?php } ?>
					</div><!--end div.misc-pub-section-->
				<?php else : ?>
					<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish">
					<input type="hidden" name="post_status" id="post_status" value="publish">
				<?php endif; ?>


				<?php
				/**
				 * Visibility
				 */
				if ( $show_visibility ) : ?>
					<div class="misc-pub-section" id="visibility">
						<?php
						if ( 'private' === $post->post_status ) {
							$post->post_password = '';
							$visibility = 'private';
							$visibility_trans = 'Private';
						} elseif ( ! empty( $post->post_password ) ) {
							$visibility = 'password';
							$visibility_trans = 'Password protected';
						} elseif ( 'post' === $post_type && is_sticky( $post->ID ) ) {
							$visibility = 'public';
							$visibility_trans = 'Public, Sticky';
						} else {
							$visibility = 'public';
							$visibility_trans = 'Public';
						}
						?>

						Visibility:
						<span id="post-visibility-display"><?php echo esc_html( $visibility_trans ); ?></span>

						<?php if ( $can_publish ) { ?>
							<a href="#visibility" class="edit-visibility hide-if-no-js">Edit</a>

							<div id="post-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr( $post->post_password ); ?>">
								<?php if ( 'post' === $post_type ) : ?>
									<input type="checkbox" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> style="display: none">
								<?php endif; ?>
								<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>">

								<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?>>
								<label for="visibility-radio-public" class="selectit">Public</label>
								<br>

								<?php if ( 'post' === $post_type && current_user_can( 'edit_others_posts' ) ) : ?>
									<span id="sticky-span">
										<input type="checkbox" name="sticky" id="sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?>>
										<label for="sticky" class="selectit">Stick this post to the front page</label>
										<br>
									</span>
								<?php endif; ?>

								<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?>>
								<label for="visibility-radio-password" class="selectit">Password protected</label><br />

								<span id="password-span">
									<label for="post_password">Password:</label>
									<input type="text" name="post_password" id="post_password" value="<?php echo esc_attr( $post->post_password ); ?>">
									<br>
								</span>

								<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?>>
								<label for="visibility-radio-private" class="selectit">Private</label>
								<br>

								<p>
									<a href="#visibility" class="save-post-visibility hide-if-no-js button">OK</a>
									<a href="#visibility" class="cancel-post-visibility hide-if-no-js">Cancel</a>
								</p>
							</div>
						<?php } ?>
					</div><!--end div.misc-pub-section#visibility-->
				<?php else : ?>
					<input type="hidden" name="hidden_post_visibility" value="public">
					<input type="hidden" name="visibility" value="public">
				<?php endif; ?>


				<?php
				/**
				 * Publish date
				 */
				if ( $show_publish_date ) :
					$datef = 'M j, Y @ G:i';
					if ( 0 !== $post->ID ) {
						if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
							$stamp = 'Scheduled for: <strong>%1$s</strong>';
						} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published.
							$stamp = 'Published on: <strong>%1$s</strong>';
						} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
							$stamp = 'Publish <strong>immediately</strong>';
						} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
							$stamp = 'Schedule for: <strong>%1$s</strong>';
						} else { // Draft, 1 or more saves, date specified.
							$stamp = 'Publish on: <strong>%1$s</strong>';
						}
						$date = date_i18n( $datef, strtotime( $post->post_date ) );
					} else { // Draft (no saves, and thus no date specified).
						$stamp = 'Publish <strong>immediately</strong>';
						$date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
					}

					if ( $can_publish ) : // Contributors don't get to choose the date of publish. ?>
						<div class="misc-pub-section curtime">
							<span id="timestamp"><?php printf( $stamp, $date ); ?></span>
							<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js">Edit</a>
							<div id="timestampdiv" class="hide-if-js"><?php touch_time( ( 'edit' === $action ), 1 ); ?></div>
						</div>
					<?php
					endif;
				endif;
				?>

				<?php do_action( 'post_submitbox_misc_actions' ); ?>
			</div><!--end div#misc-publishing-actions-->
			<div class="clear"></div>
		</div><!--end div#minor-publishing-->


		<div id="major-publishing-actions">
			<?php do_action( 'post_submitbox_start' ); ?>

			<?php if ( 'auto-draft' !== $post->post_status ) : ?>
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						$onclick = '';
						if ( ! EMPTY_TRASH_DAYS || $force_delete ) {
							$delete_text = 'Delete Permanently';
							$onclick = " onclick=\"return confirm('" . esc_js( sprintf( 'Are you sure you want to delete this %s?', strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						} else {
							$delete_text = 'Move to Trash';
						}
						?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID , '', $force_delete ) ); ?>"<?php echo $onclick; ?>><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
			<?php endif; ?>

			<div id="publishing-action">
				<?php audiotheme_admin_spinner( array( 'id' => 'ajax-loading' ) ); ?>
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 === $post->ID ) {
					if ( $can_publish ) :
						if ( ! empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
							<input type="hidden" name="original_publish" id="original_publish" value="Schedule">
							<?php submit_button( 'Schedule', 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input type="hidden" name="original_publish" id="original_publish" value="Publish">
							<?php submit_button( 'Publish', 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<input type="hidden" name="original_publish" id="original_publish" value="Submit for Review">
						<?php
						submit_button( 'Submit for Review', 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) );
					endif;
				} else { ?>
					<input type="hidden" name="original_publish" id="original_publish" value="Update">
					<input type="submit" name="save" id="publish" class="button-primary button-large" accesskey="p" value="Update">
				<?php } ?>
			</div><!--end div#publishing-action-->

			<div class="clear"></div>
		</div><!--end div#major-publishing-actions-->
	</div><!--end div#submitpost-->
	<?php
}

/**
 * Backwards compatible AJAX spinner
 *
 * Displays the correct AJAX spinner depending on the version of WordPress.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $args Array of args to modify output.
 * @return void|string Echoes spinner HTML or returns it.
 */
function audiotheme_admin_spinner( $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$args = wp_parse_args( $args, array(
		'id'    => '',
		'class' => 'ajax-loading',
		'echo'  => true,
	) );

	if ( audiotheme_version_compare( 'wp', '3.5-beta-1', '<' ) ) {
		$spinner = sprintf( '<img src="%1$s" id="%2$s" class="spinner %3$s" alt="">',
			esc_url( admin_url( 'images/wpspin_light.gif' ) ),
			esc_attr( $args['id'] ),
			esc_attr( $args['class'] )
		);
	} else {
		$spinner = sprintf( '<span id="%1$s" class="spinner %2$s"></span>',
			esc_attr( $args['id'] ),
			esc_attr( $args['class'] )
		);
	}

	if ( $args['echo'] ) {
		echo $spinner;
	} else {
		return $spinner;
	}
}

/**
 * Add a Template Version header for child themes to declare which version of a
 * parent theme they're compatible with.
 *
 * @since 1.5.0
 * @deprecated 2.0.0
 *
 * @param array $headers List of extra headers.
 * @return array
 */
function audiotheme_theme_headers( $headers ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	$headers['TemplateVersion'] = 'Template Version';
	return $headers;
}

/**
 * Helper function to enqueue a pointer.
 *
 * The $id will be used to reference the pointer in javascript as well as the
 * key it's saved with in the dismissed pointers user meta. $content will be
 * wrapped in wpautop(). Passing a pointer arg will allow the position of the
 * pointer to be changed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $id Pointer id.
 * @param string $title Pointer title.
 * @param string $content Pointer content.
 * @param array $args Additional args.
 */
function audiotheme_enqueue_pointer( $id, $title, $content, $args = array() ) {
	global $audiotheme_pointers;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$id = sanitize_key( $id );

	$args = wp_parse_args( $args, array(
		'position' => 'left',
	) );

	$content = sprintf( '<h3>%s</h3>%s', $title, wpautop( $content ) );

	$audiotheme_pointers[ $id ] = array(
		'id'       => $id,
		'content'  => $content,
		'position' => $args['position'],
	);
}

/**
 * Check to see if a pointer has been dismissed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $id The pointer id.
 * @return bool
 */
function is_audiotheme_pointer_dismissed( $id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	return in_array( $id, $dismissed );
}

/**
 * Print enqueued pointers to a global javascript variable.
 *
 * Dismissed pointers are automatically removed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_print_pointers() {
	global $audiotheme_pointers;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( empty( $audiotheme_pointers ) ) {
		return;
	}

	// Remove dismissed pointers.
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	$audiotheme_pointers = array_diff_key( $audiotheme_pointers, array_flip( $dismissed ) );

	if ( empty( $audiotheme_pointers ) ) {
		return;
	}

	// @see WP_Scripts::localize()
	foreach ( (array) $audiotheme_pointers as $id => $pointer ) {
		foreach ( $pointer as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$audiotheme_pointers[ $id ][ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}
	}

	// Output the object directly since there isn't really have a script to attach it to.
	// CDATA and type='text/javascript' is not needed for HTML 5.
	echo "<script type='text/javascript'>\n";
	echo "/* <![CDATA[ */\n";
	echo 'var audiothemePointers = ' . json_encode( $audiotheme_pointers ) . ";\n";
	echo "/* ]]> */\n";
	echo "</script>\n";
}

/**
 * Attach hooks for loading and managing discography in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_load_discography_admin() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Rename the top level Records menu item to Discography.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @link https://core.trac.wordpress.org/ticket/23316
 */
function audiotheme_discography_admin_menu() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Discography update messages.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_discography_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $messages;
}

/**
 * Move the playlist menu item under discography.
 *
 * @since 1.5.0
 * @deprecated 2.0.0
 *
 * @param array $args Post type registration args.
 * @return array
 */
function audiotheme_playlist_args( $args ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	$args['show_in_menu'] = 'edit.php?post_type=audiotheme_record';
	return $args;
}

/**
 * Enqueue playlist scripts and styles.
 *
 * @since 1.5.0
 * @deprecated 2.0.0
 */
function audiotheme_playlist_admin_enqueue_scripts() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Print playlist JavaScript templates.
 *
 * @since 1.5.0
 * @deprecated 2.0.0
 */
function audiotheme_playlist_print_templates() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Custom sort records on the Manage Records screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_records_admin_query( $wp_query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register record columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array Filtered array of column names.
 */
function audiotheme_record_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $columns;
}

/**
 * Register sortable record columns.
 *
 * @since 1.0.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_record_register_sortable_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $columns;
}

/**
 * Display custom record columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_record_display_columns( $column_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Remove quick edit from the record list table.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_record_list_table_actions( $actions, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $actions;
}

/**
 * Remove bulk edit from the record list table.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_record_list_table_bulk_actions( $actions ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $actions;
}

/**
 * Custom rules for saving a record.
 *
 * Creates and updates child tracks and saves additional record meta.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_record_save_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register record meta boxes.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_edit_record_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Tracklist editor.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_edit_record_tracklist() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Record details meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_record_details_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Save record archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_record_archive_save_settings_hook( $post_id, $post, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add an orderby setting to the record archive.
 *
 * Allows for changing the sort order of records. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_record_archive_settings( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Custom sort tracks on the Manage Tracks screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_tracks_admin_query( $wp_query ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register track columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_track_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $columns;
}

/**
 * Register sortable track columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_track_register_sortable_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $columns;
}

/**
 * Display custom track columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_track_display_columns( $column_name, $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Remove quick edit from the track list table.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_track_list_table_actions( $actions, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $actions;
}

/**
 * Remove bulk edit from the track list table.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_track_list_table_bulk_actions( $actions ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $actions;
}

/**
 * Custom track filter dropdowns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_tracks_filters() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Custom rules for saving a track.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_track_save_post( $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register track meta boxes.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Track ID.
 */
function audiotheme_edit_track_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}


/**
 * Display track details meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post The track post object being edited.
 */
function audiotheme_track_details_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Attach hooks for loading and managing gigs in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_gigs_admin_setup() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add the admin menu items for gigs.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_gigs_admin_menu() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Higlight the correct top level and sub menu items for the gig screen being
 * displayed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $parent_file The screen being displayed.
 * @return string The menu item to highlight.
 */
function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $parent_file;
}

/**
 * Set up the gig Manage Screen.
 *
 * Initializes the custom post list table, and processes any actions that need
 * to be handled.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_gigs_manage_screen_setup() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display the gig Manage Screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_gigs_manage_screen() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Sanitize the 'per_page' screen option on the Manage Gigs and Manage Venues
 * screens.
 *
 * Apparently any other hook attached to the same filter that runs after this
 * will stomp all over it. To prevent this filter from doing the same, it's
 * only attached on the screens that require it. The priority should be set
 * extremely low to help ensure the correct value gets returned.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param bool $return Default is 'false'.
 * @param string $option The option name.
 * @param mixed $value The value to sanitize.
 * @return mixed The sanitized value.
 */
function audiotheme_gigs_screen_options( $return, $option, $value ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $return;
}

/**
 * Set up the gig Add/Edit screen.
 *
 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
 * to display the edit fields after the title.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_edit_screen_setup( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Setup and display the main gig fields for editing.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_edit_gig_fields() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Gig tickets meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_tickets_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Process and save gig info when the CPT is saved.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $gig_id Gig post ID.
 * @param WP_Post $post Gig post object.
 */
function audiotheme_gig_save_post( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Gig update messages.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array
 */
function audiotheme_gig_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $messages;
}

/**
 * Set up the Manage Venues screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_venues_manage_screen_setup() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Set up the Edit Venue screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_venue_edit_screen_setup() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Process venue add/edit actions.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_venue_edit_screen_process_actions() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display the venue add/edit screen.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_venue_edit_screen() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display venue contact information meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_contact_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display venue notes meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_notes_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display custom venue submit meta box.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_submit_meta_box( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Attach hooks for loading and managing videos in the admin dashboard.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_load_videos_admin() {}

/**
 * Video update messages.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_video_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $messages;
}

/**
 * Register video columns.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_video_register_columns( $columns ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $columns;
}

/**
 * Register video meta boxes.
 *
 * This callback is defined in the video CPT registration function. Meta boxes
 * or any other functionality that should be limited to the Add/Edit Video
 * screen and should occur after 'do_meta_boxes' can be registered here.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_video_meta_boxes() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display a field to enter a video URL after the post title.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_video_after_title() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add a link to get the video thumbnail from an oEmbed endpoint.
 *
 * Adds data about the current thumbnail and a previously fetched thumbnail
 * from an oEmbed endpoint so the link can be hidden or shown as necessary. A
 * function is also fired each time the HTML is output in order to determine
 * whether the link should be displayed.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $content Default post thumbnail HTML.
 * @param int $post_id Post ID.
 * @return string
 */
function audiotheme_video_admin_post_thumbnail_html( $content, $post_id ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $content;
}

/**
 * AJAX method to retrieve the thumbnail for a video.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_ajax_get_video_oembed_data() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Import a video thumbnail from an oEmbed endpoint into the media library.
 *
 * @todo Considering doing video URL comparison rather than oembed thumbnail
 *       comparison?
 *
 * @since 1.8.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Video post ID.
 * @param string $url Video URL.
 */
function audiotheme_video_sideload_thumbnail( $post_id, $url ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Download an image from the specified URL and attach it to a post.
 *
 * @since 1.8.0
 * @deprecated 2.0.0
 *
 * @see media_sideload_image()
 *
 * @param string $url The URL of the image to download.
 * @param int $post_id The post ID the media is to be associated with.
 * @param string $desc Optional. Description of the image.
 * @return int|WP_Error Populated HTML img tag on success.
 */
function audiotheme_video_sideload_image( $url, $post_id, $desc = null ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $id;
}

/**
 * Save custom video data.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int $post_id The ID of the post.
 * @param object $post The post object.
 */
function audiotheme_video_save_post( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Save video archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.4.4
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_video_archive_save_settings_hook( $post_id, $post, $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add an orderby setting to the video archive.
 *
 * Allows for changing the sort order of videos. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.4.4
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_video_archive_settings( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Register archive post type and setup related functionality.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function register_audiotheme_archives() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Parse video oEmbed data.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 * @see WP_oEmbed->data2html()
 *
 * @param string $return Embed HTML.
 * @param object $data Data returned from the oEmbed request.
 * @param string $url The URL used for the oEmbed request.
 * @return string
 */
function audiotheme_parse_video_oembed_data( $return, $data, $url ) {
	global $post_id;

	_deprecated_function( __FUNCTION__, '1.8.0' );

	// Supports any oEmbed providers that respond with 'thumbnail_url'.
	if ( isset( $data->thumbnail_url ) ) {
		$current_thumb_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );
		$oembed_thumb = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', true );

		if ( ( ! $current_thumb_id || $current_thumb_id !== $oembed_thumb_id ) && $data->thumbnail_url === $oembed_thumb ) {
			// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
			set_post_thumbnail( $post_id, $oembed_thumb_id );
		} elseif ( ! $current_thumb_id || $data->thumbnail_url !== $oembed_thumb ) {
			// Add new thumbnail if the returned URL doesn't match the
			// oEmbed thumb URL or if there isn't a current thumbnail.
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_id );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );

			if ( $thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
				// Store the oEmbed thumb data so the same image isn't copied on repeated requests.
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', $thumbnail_id, true );
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', $data->thumbnail_url, true );
			}
		}
	}

	return $return;
}

/**
 * Set a video post's featured image.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 */
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_id;
	_deprecated_function( __FUNCTION__, '1.8.0' );
	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Setup archive posts for post types that have support.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_archives_init_admin() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add submenu items for archives under the post type menu item.
 *
 * Ensures the user has the capability to edit pages in general as well
 * as the individual page before displaying the submenu item.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 */
function audiotheme_archives_admin_menu() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Replace the submit meta box to remove unnecessary fields.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_archives_add_meta_boxes( $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Highlight the corresponding top level and submenu items when editing an
 * archive page.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $parent_file A parent file identifier.
 * @return string
 */
function audiotheme_archives_parent_file( $parent_file ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $parent_file;
}

/**
 * Archive update messages.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $messages The array of post update messages.
 * @return array An array with new CPT update messages.
 */
function audiotheme_archives_post_updated_messages( $messages ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $messages;
}

/**
 * Create an archive post for a post type if one doesn't exist.
 *
 * The post type's plural label is used for the post title and the defined
 * rewrite slug is used for the postname.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $post_type_name Post type slug.
 * @return int Post ID.
 */
function audiotheme_archives_create_archive( $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'AudioTheme_Module_Archives::add_post_type_archive()' );
	return audiotheme()->modules['archives']->add_post_type_archive( $post_type );
}

/**
 * Retrieve a post type's archive slug.
 *
 * Checks the 'has_archive' and 'with_front' args in order to build the
 * slug.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $post_type Post type name.
 * @return string Archive slug.
 */
function get_audiotheme_post_type_archive_slug( $post_type ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	return $slug;
}

/**
 * Save archive meta data.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archive_save_hook( $post_id, $post ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Display archive settings meta box.
 *
 * The meta box needs to be activated first, then fields can be displayed using
 * one of the actions.
 *
 * @since 1.3.0
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box( $post, $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Add fields to the archive settings meta box.
 *
 * @since 1.4.2
 * @deprecated 2.0.0
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box_fields( $post, $post_type, $fields = array() ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Insert a menu item relative to an existing item.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array  $item Menu item.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after).
 */
function audiotheme_menu_insert_item( $item, $relative_slug, $position = 'after' ) {
	global $menu;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$relative_key = audiotheme_menu_get_item_key( $relative_slug );
	$before = ( 'before' === $position ) ? $relative_key : $relative_key + 1;

	array_splice( $menu, $before, 0, array( $item ) );
}

/**
 * Move an existing menu item relative to another item.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $move_slug Slug of item to move.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after).
 */
function audiotheme_menu_move_item( $move_slug, $relative_slug, $position = 'after' ) {
	global $menu;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	$move_key = audiotheme_menu_get_item_key( $move_slug );
	if ( $move_key ) {
		$item = $menu[ $move_key ];
		unset( $menu[ $move_key ] );

		audiotheme_menu_insert_item( $item, $relative_slug, $position );
	}
}

/**
 * Retrieve the key of a menu item.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param array $menu_slug Menu item slug.
 * @return int|bool Menu item key or false if it couldn't be found.
 */
function audiotheme_menu_get_item_key( $menu_slug ) {
	global $menu;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	foreach ( $menu as $key => $item ) {
		if ( $menu_slug === $item[2] ) {
			return $key;
		}
	}

	return false;
}

/**
 * Move a submenu item after another submenu item under the same top-level item.
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param string $move_slug Slug of the item to move.
 * @param string $after_slug Slug of the item to move after.
 * @param string $menu_slug Top-level menu item.
 */
function audiotheme_submenu_move_after( $move_slug, $after_slug, $menu_slug ) {
	global $submenu;

	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( isset( $submenu[ $menu_slug ] ) ) {
		foreach ( $submenu[ $menu_slug ] as $key => $item ) {
			if ( $item[2] === $move_slug ) {
				$move_key = $key;
			} elseif ( $item[2] === $after_slug ) {
				$after_key = $key;
			}
		}

		if ( isset( $move_key ) && isset( $after_key ) ) {
			$move_item = $submenu[ $menu_slug ][ $move_key ];
			unset( $submenu[ $menu_slug ][ $move_key ] );

			// Need to account for the change in the array with the previous unset.
			$new_position = ( $move_key > $after_key ) ? $after_key + 1 : $after_key;

			array_splice( $submenu[ $menu_slug ], $new_position, 0, array( $move_item ) );
		}
	}
}
