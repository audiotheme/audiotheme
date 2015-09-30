<?php
/**
 * Generic utility functions for us in the admin.
 *
 * @package AudioTheme_Framework
 * @subpackage Administration
 */

/**
 * Print a taxonomy checkbox list.
 *
 * @since 1.7.0
 */
function audiotheme_taxonomy_checkbox_list_meta_box( $post, $metabox ) {
	$taxonomy        = $metabox['args']['taxonomy'];
	$taxonomy_object = get_taxonomy( $taxonomy );

	$selected     = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'all' ) );
	$selected_ids = wp_list_pluck( $selected, 'term_id' );
	$selected     = empty( $selected ) || empty( $selected_ids ) ? array() : array_combine( $selected_ids, wp_list_pluck( $selected, 'name' ) );
	$terms        = get_terms( $taxonomy, array( 'fields' => 'id=>name', 'hide_empty' => false, 'exclude' => $selected_ids ) );
	$terms        = $selected + $terms;

	$button_text  = empty( $metabox['args']['button_text'] ) ? __( 'Add', 'audiotheme' ) : $metabox['args']['button_text'];

	wp_nonce_field( 'save-post-terms_' . $post->ID, $taxonomy . '_nonce' );
	include( AUDIOTHEME_DIR . 'admin/views/meta-box-taxonomy-checkbox-list.php' );
}

/**
 * Customizable submit meta box.
 *
 * @see post_submit_meta_box()
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object.
 * @param array $metabox Additional meta box args.
 */
function audiotheme_post_submit_meta_box( $post, $metabox ) {
	global $action;

	$defaults = array(
		'force_delete' => false,
		'show_publish_date' => true,
		'show_statuses' => array(
			'pending' => __( 'Pending Review', 'audiotheme' ),
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

			<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
			<div style="display: none"><?php submit_button( __( 'Save', 'audiotheme' ), 'button', 'save' ); ?></div>


			<?php
			/**
			 * Save/Preview buttons
			 */
			?>
			<div id="minor-publishing-actions">
				<div id="save-action">
					<?php if ( 'publish' !== $post->post_status && 'future' !== $post->post_status && 'pending' !== $post->post_status ) { ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save Draft', 'audiotheme' ); ?>" class="button" <?php if ( 'private' === $post->post_status ) { echo 'style="display: none"'; } ?>>
					<?php } elseif ( 'pending' === $post->post_status && $can_publish ) { ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e( 'Save as Pending', 'audiotheme' ); ?>" class="button">
					<?php } ?>

					<?php audiotheme_admin_spinner( array( 'id' => 'draft-ajax-loading' ) ); ?>
				</div>

				<div id="preview-action">
					<?php
					if ( 'publish' === $post->post_status ) {
						$preview_link = get_permalink( $post->ID );
						$preview_button = __( 'Preview Changes', 'audiotheme' );
					} else {
						$preview_link = set_url_scheme( get_permalink( $post->ID ) );
						$preview_link = apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) );
						$preview_button = __( 'Preview', 'audiotheme' );
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
						<label for="post_status"><?php _e( 'Status:', 'audiotheme' ) ?></label>
						<span id="post-status-display">
							<?php
							switch ( $post->post_status ) {
								case 'private':
									_e( 'Privately Published', 'audiotheme' );
									break;
								case 'publish':
									_e( 'Published', 'audiotheme' );
									break;
								case 'future':
									_e( 'Scheduled', 'audiotheme' );
									break;
								case 'pending':
									_e( 'Pending Review', 'audiotheme' );
									break;
								case 'draft':
								case 'auto-draft':
									_e( 'Draft', 'audiotheme' );
									break;
							}
							?>
						</span>

						<?php if ( 'publish' === $post->post_status || 'private' === $post->post_status || ( $can_publish && count( $show_statuses ) ) ) { ?>
							<a href="#post_status" class="edit-post-status hide-if-no-js" <?php if ( 'private' === $post->post_status ) { echo 'style="display: none"'; } ?>><?php _e( 'Edit', 'audiotheme' ) ?></a>

							<div id="post-status-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ( 'auto-draft' === $post->post_status ) ? 'draft' : $post->post_status ); ?>">
								<select name="post_status" id="post_status">
									<?php if ( 'publish' === $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'publish' ); ?>><?php _e( 'Published', 'audiotheme' ) ?></option>
									<?php elseif ( 'private' === $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'private' ); ?>><?php _e( 'Privately Published', 'audiotheme' ) ?></option>
									<?php elseif ( 'future' === $post->post_status ) : ?>
										<option value="future" <?php selected( $post->post_status, 'future' ); ?>><?php _e( 'Scheduled', 'audiotheme' ) ?></option>
									<?php endif; ?>

									<?php if ( array_key_exists( 'pending', $show_statuses ) ) : ?>
										<option value="pending" <?php selected( $post->post_status, 'pending' ); ?>><?php _e( 'Pending Review', 'audiotheme' ) ?></option>
									<?php endif; ?>

									<?php if ( 'auto-draft' === $post->post_status ) : ?>
										<option value="draft" <?php selected( $post->post_status, 'auto-draft' ); ?>><?php _e( 'Draft', 'audiotheme' ) ?></option>
									<?php else : ?>
										<option value="draft" <?php selected( $post->post_status, 'draft' ); ?>><?php _e( 'Draft', 'audiotheme' ) ?></option>
									<?php endif; ?>
								</select>
								 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e( 'OK', 'audiotheme' ); ?></a>
								 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e( 'Cancel', 'audiotheme' ); ?></a>
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
							$visibility_trans = __( 'Private', 'audiotheme' );
						} elseif ( ! empty( $post->post_password ) ) {
							$visibility = 'password';
							$visibility_trans = __( 'Password protected', 'audiotheme' );
						} elseif ( 'post' === $post_type && is_sticky( $post->ID ) ) {
							$visibility = 'public';
							$visibility_trans = __( 'Public, Sticky', 'audiotheme' );
						} else {
							$visibility = 'public';
							$visibility_trans = __( 'Public', 'audiotheme' );
						}
						?>

						<?php _e( 'Visibility:', 'audiotheme' ); ?>
						<span id="post-visibility-display"><?php echo esc_html( $visibility_trans ); ?></span>

						<?php if ( $can_publish ) { ?>
							<a href="#visibility" class="edit-visibility hide-if-no-js"><?php _e( 'Edit', 'audiotheme' ); ?></a>

							<div id="post-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr( $post->post_password ); ?>">
								<?php if ( 'post' === $post_type ) : ?>
									<input type="checkbox" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> style="display: none">
								<?php endif; ?>
								<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>">

								<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?>>
								<label for="visibility-radio-public" class="selectit"><?php _e( 'Public', 'audiotheme' ); ?></label>
								<br>

								<?php if ( 'post' === $post_type && current_user_can( 'edit_others_posts' ) ) : ?>
									<span id="sticky-span">
										<input type="checkbox" name="sticky" id="sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?>>
										<label for="sticky" class="selectit"><?php _e( 'Stick this post to the front page', 'audiotheme' ); ?></label>
										<br>
									</span>
								<?php endif; ?>

								<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?>>
								<label for="visibility-radio-password" class="selectit"><?php _e( 'Password protected', 'audiotheme' ); ?></label><br />

								<span id="password-span">
									<label for="post_password"><?php _e( 'Password:', 'audiotheme' ); ?></label>
									<input type="text" name="post_password" id="post_password" value="<?php echo esc_attr( $post->post_password ); ?>">
									<br>
								</span>

								<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?>>
								<label for="visibility-radio-private" class="selectit"><?php _e( 'Private', 'audiotheme' ); ?></label>
								<br>

								<p>
									<a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e( 'OK', 'audiotheme' ); ?></a>
									<a href="#visibility" class="cancel-post-visibility hide-if-no-js"><?php _e( 'Cancel', 'audiotheme' ); ?></a>
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
					/* translators: Publish box date format, see http://php.net/date */
					$datef = __( 'M j, Y @ G:i' );
					if ( 0 !== $post->ID ) {
						if ( 'future' === $post->post_status ) { // scheduled for publishing at a future date
							$stamp = __( 'Scheduled for: <strong>%1$s</strong>', 'audiotheme' );
						} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // already published
							$stamp = __( 'Published on: <strong>%1$s</strong>', 'audiotheme' );
						} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
							$stamp = __( 'Publish <strong>immediately</strong>', 'audiotheme' );
						} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
							$stamp = __( 'Schedule for: <strong>%1$s</strong>', 'audiotheme' );
						} else { // draft, 1 or more saves, date specified
							$stamp = __( 'Publish on: <strong>%1$s</strong>', 'audiotheme' );
						}
						$date = date_i18n( $datef, strtotime( $post->post_date ) );
					} else { // draft (no saves, and thus no date specified)
						$stamp = __( 'Publish <strong>immediately</strong>', 'audiotheme' );
						$date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
					}

					if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
						<div class="misc-pub-section curtime">
							<span id="timestamp"><?php printf( $stamp, $date ); ?></span>
							<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><?php _e( 'Edit', 'audiotheme' ) ?></a>
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
							$delete_text = __( 'Delete Permanently', 'audiotheme' );
							$onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						} else {
							$delete_text = __( 'Move to Trash', 'audiotheme' );
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
							<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Schedule', 'audiotheme' ); ?>">
							<?php submit_button( __( 'Schedule', 'audiotheme' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish', 'audiotheme' ) ?>">
							<?php submit_button( __( 'Publish', 'audiotheme' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Submit for Review', 'audiotheme' ) ?>">
						<?php
						submit_button( __( 'Submit for Review', 'audiotheme' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) );
					endif;
				} else { ?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
					<input type="submit" name="save" id="publish" class="button-primary button-large" accesskey="p" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
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
 *
 * @param array $args Array of args to modify output.
 * @return void|string Echoes spinner HTML or returns it.
 */
function audiotheme_admin_spinner( $args = array() ) {
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
 * Insert a menu item relative to an existing item.
 *
 * @since 1.0.0
 *
 * @param array $item Menu item.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after)
 */
function audiotheme_menu_insert_item( $item, $relative_slug, $position = 'after' ) {
	global $menu;

	$relative_key = audiotheme_menu_get_item_key( $relative_slug );
	$before = ( 'before' === $position ) ? $relative_key : $relative_key + 1;

	array_splice( $menu, $before, 0, array( $item ) );
}

/**
 * Move an existing menu item relative to another item.
 *
 * @since 1.0.0
 *
 * @param string $move_slug Slug of item to move.
 * @param string $relative_slug Slug of existing item.
 * @param string $position Optional. Defaults to 'after'. (before|after)
 */
function audiotheme_menu_move_item( $move_slug, $relative_slug, $position = 'after' ) {
	global $menu;

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
 *
 * @param array $menu_slug Menu item slug.
 * @return int|bool Menu item key or false if it couldn't be found.
 */
function audiotheme_menu_get_item_key( $menu_slug ) {
	global $menu;

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
 *
 * @param string $move_slug Slug of the item to move.
 * @param string $after_slug Slug of the item to move after.
 * @param string $menu_slug Top-level menu item.
 */
function audiotheme_submenu_move_after( $move_slug, $after_slug, $menu_slug ) {
	global $submenu;

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

/**
 * Retrieve system data.
 *
 * @since 1.0.0
 *
 * @return array
 */
function audiotheme_system_info( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'format' => '',
	) );

	$theme = wp_get_theme( get_template() );

	$data = array(
		'home_url' => array(
			'label' => __( 'Home URL', 'audiotheme' ),
			'value' => home_url(),
		),
		'site_url' => array(
			'label' => __( 'Site URL', 'audiotheme' ),
			'value' => site_url(),
		),
		'wp_lang' => array(
			'label' => __( 'WP Language', 'audiotheme' ),
			'value' => defined( 'WPLANG' ) ? WPLANG : get_option( 'WPLANG' ),
		),
		'wp_version' => array(
			'label' => __( 'WP Version', 'audiotheme' ),
			'value' => get_bloginfo( 'version' ) . ( ( is_multisite() ) ? ' (WPMU)' : '' ),
		),
		'web_server' => array(
			'label' => __( 'Web Server Info', 'audiotheme' ),
			'value' => $_SERVER['SERVER_SOFTWARE'],
		),
		'php_version' => array(
			'label' => __( 'PHP Version', 'audiotheme' ),
			'value' => phpversion(),
		),
		'mysql_version' => array(
			'label' => __( 'MySQL Version', 'audiotheme' ),
			'value' => $wpdb->db_version(),
		),
		'wp_memory_limit' => array(
			'label' => __( 'WP Memory Limit', 'audiotheme' ),
			'value' => WP_MEMORY_LIMIT,
		),
		'wp_debug_mode' => array(
			'label' => __( 'WP Debug Mode', 'audiotheme' ),
			'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? __( 'Yes', 'audiotheme' ) : __( 'No', 'audiotheme' ),
		),
		'wp_max_upload_size' => array(
			'label' => __( 'WP Max Upload Size', 'audiotheme' ),
			'value' => size_format( wp_max_upload_size() ),
		),
		'php_post_max_size' => array(
			'label' => __( 'PHP Post Max Size', 'audiotheme' ),
			'value' => ini_get( 'post_max_size' ),
		),
		'php_time_limit' => array(
			'label' => __( 'PHP Time Limit', 'audiotheme' ),
			'value' => ini_get( 'max_execution_time' ),
		),
		'php_safe_mode' => array(
			'label' => __( 'PHP Safe Mode', 'audiotheme' ),
			'value' => ( ini_get( 'safe_mode' ) ) ? __( 'Yes', 'audiotheme' ) : __( 'No', 'audiotheme' ),
		),
		'user_agent' => array(
			'label' => __( 'User Agent', 'audiotheme' ),
			'value' => $_SERVER['HTTP_USER_AGENT'],
		),
		'audiotheme_version' => array(
			'label' => __( 'AudioTheme Version', 'audiotheme' ),
			'value' => AUDIOTHEME_VERSION,
		),
		'theme' => array(
			'label' => __( 'Theme', 'audiotheme' ),
			'value' => $theme->get( 'Name' ),
		),
		'theme_version' => array(
			'label' => __( 'Theme Version', 'audiotheme' ),
			'value' => $theme->get( 'Version' ),
		),
	);

	if ( get_template() !== get_stylesheet() ) {
		$theme = wp_get_theme();

		$data['child_theme'] = array(
			'label' => __( 'Child Theme', 'audiotheme' ),
			'value' => $theme->get( 'Name' ),
		);

		$data['child_theme_version'] = array(
			'label' => __( 'Child Theme', 'audiotheme' ),
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
 * Add AudioTheme themes to a site option so they can be checked for updates
 * when in multsite mode.
 *
 * @since 1.3.0
 *
 * @param string $theme Theme slug.
 * @param array $api_args Optional. Arguments to send to the remote API.
 */
function audiotheme_update_themes_list( $theme, $api_args = array() ) {
	if ( ! is_multisite() ) {
		return;
	}

	$themes = (array) get_site_option( 'audiotheme_themes' );

	if ( ! array_key_exists( $theme, $themes ) || $themes[ $theme ] !== $api_args ) {
		$themes[ $theme ] = wp_parse_args( $api_args, array( 'slug' => $theme ) );
		update_site_option( 'audiotheme_themes', $themes );
	}
}
