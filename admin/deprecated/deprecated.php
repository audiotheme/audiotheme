<?php
/**
 * Deprecated functions.
 *
 * These will be removed in a future version.
 *
 * @package AudioTheme\Deprecated
 */

/**
 * Custom user contact fields.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $contactmethods List of contact methods.
 * @return array
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	$contactmethods['twitter'] = 'Twitter Username';
	$contactmethods['facebook'] = 'Facebook URL';
	return $contactmethods;
}

/**
 * Retrieve system data.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return array
 */
function audiotheme_system_info( $args = array() ) {
	global $wpdb;

	_deprecated_function( __FUNCTION__, '1.9.0' );

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
 * @deprecated 1.9.0
 *
 * @param WP_Post $post Post object.
 * @param array   $metabox Additional meta box arguments.
 */
function audiotheme_post_submit_meta_box( $post, $metabox ) {
	global $action;

	_deprecated_function( __FUNCTION__, '1.9.0' );

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
 * @deprecated 1.9.0
 *
 * @param array $args Array of args to modify output.
 * @return void|string Echoes spinner HTML or returns it.
 */
function audiotheme_admin_spinner( $args = array() ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );

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
