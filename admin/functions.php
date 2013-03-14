<?php
/**
 * Generic utility functions for us in the admin.
 *
 * @package AudioTheme_Framework
 */

/**
 * Helper function to enqueue a pointer.
 *
 * The $id will be used to reference the pointer in javascript as well as the
 * key it's saved with in the dismissed pointers user meta. $content will be
 * wrapped in wpautop(). Passing a pointer arg will allow the position of the
 * pointer to be changed.
 *
 * @since 1.0.0
 *
 * @param string $id Pointer id.
 * @param string $title Pointer title.
 * @param string $content Pointer content.
 * @param array $args Additional args.
 */
function audiotheme_enqueue_pointer( $id, $title, $content, $args = array() ) {
	global $audiotheme_pointers;

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
 *
 * @param string $id The pointer id.
 * @return bool
 */
function is_audiotheme_pointer_dismissed( $id ) {
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

	return in_array( $id, $dismissed );
}

/**
 * Print enqueued pointers to a global javascript variable.
 *
 * Dismissed pointers are automatically removed.
 *
 * @since 1.0.0
 */
function audiotheme_print_pointers() {
	global $audiotheme_pointers;

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
		foreach( $pointer as $key => $value ) {
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
	echo "var audiothemePointers = " . json_encode( $audiotheme_pointers ) . ";\n";
	echo "/* ]]> */\n";
	echo "</script>\n";
}

/**
 * Customizable submit meta box.
 *
 * @see post_submit_meta_box()
 *
 * @since 1.0.0
 */
function audiotheme_post_submit_meta_box( $post, $metabox ) {
	global $action;

	$defaults = array(
		'force_delete' => false,
		'show_publish_date' => true,
		'show_statuses' => array(
			'pending' => __( 'Pending Review', 'audiotheme-i18n' ),
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
			<div style="display: none"><?php submit_button( __( 'Save', 'audiotheme-i18n' ), 'button', 'save' ); ?></div>


			<?php
			/**
			 * Save/Preview buttons
			 */
			?>
			<div id="minor-publishing-actions">
				<div id="save-action">
					<?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) { ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button" <?php if ( 'private' == $post->post_status ) { echo 'style="display: none"'; } ?>>
					<?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button">
					<?php } ?>

					<?php audiotheme_admin_spinner( array( 'id' => 'draft-ajax-loading' ) ); ?>
				</div>

				<div id="preview-action">
					<?php
					if ( 'publish' == $post->post_status ) {
						$preview_link = get_permalink( $post->ID );
						$preview_button = __( 'Preview Changes', 'audiotheme-i18n' );
					} else {
						$preview_link = set_url_scheme( get_permalink( $post->ID ) );
						$preview_link = apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) );
						$preview_button = __( 'Preview', 'audiotheme-i18n' );
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
						<label for="post_status"><?php _e( 'Status:' ) ?></label>
						<span id="post-status-display">
							<?php
							switch ( $post->post_status ) {
								case 'private':
									_e( 'Privately Published', 'audiotheme-i18n' );
									break;
								case 'publish':
									_e( 'Published', 'audiotheme-i18n' );
									break;
								case 'future':
									_e( 'Scheduled', 'audiotheme-i18n' );
									break;
								case 'pending':
									_e( 'Pending Review', 'audiotheme-i18n' );
									break;
								case 'draft':
								case 'auto-draft':
									_e( 'Draft', 'audiotheme-i18n' );
									break;
							}
							?>
						</span>

						<?php if ( 'publish' == $post->post_status || 'private' == $post->post_status || ( $can_publish && count( $show_statuses ) ) ) { ?>
							<a href="#post_status" class="edit-post-status hide-if-no-js" <?php if ( 'private' == $post->post_status ) { echo 'style="display: none"'; } ?>><?php _e( 'Edit', 'audiotheme-i18n' ) ?></a>

							<div id="post-status-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ( 'auto-draft' == $post->post_status ) ? 'draft' : $post->post_status ); ?>">
								<select name="post_status" id="post_status">
									<?php if ( 'publish' == $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'publish' ); ?>><?php _e( 'Published', 'audiotheme-i18n' ) ?></option>
									<?php elseif ( 'private' == $post->post_status ) : ?>
										<option value="publish" <?php selected( $post->post_status, 'private' ); ?>><?php _e( 'Privately Published', 'audiotheme-i18n' ) ?></option>
									<?php elseif ( 'future' == $post->post_status ) : ?>
										<option value="future" <?php selected( $post->post_status, 'future' ); ?>><?php _e( 'Scheduled', 'audiotheme-i18n' ) ?></option>
									<?php endif; ?>

									<?php if ( array_key_exists( 'pending', $show_statuses ) ) : ?>
										<option value="pending" <?php selected( $post->post_status, 'pending' ); ?>><?php _e( 'Pending Review', 'audiotheme-i18n' ) ?></option>
									<?php endif; ?>

									<?php if ( 'auto-draft' == $post->post_status ) : ?>
										<option value="draft" <?php selected( $post->post_status, 'auto-draft' ); ?>><?php _e( 'Draft', 'audiotheme-i18n' ) ?></option>
									<?php else : ?>
										<option value="draft" <?php selected( $post->post_status, 'draft' ); ?>><?php _e( 'Draft', 'audiotheme-i18n' ) ?></option>
									<?php endif; ?>
								</select>
								 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e( 'OK', 'audiotheme-i18n' ); ?></a>
								 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e( 'Cancel', 'audiotheme-i18n' ); ?></a>
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
						if ( 'private' == $post->post_status ) {
							$post->post_password = '';
							$visibility = 'private';
							$visibility_trans = __( 'Private', 'audiotheme-i18n' );
						} elseif ( !empty( $post->post_password ) ) {
							$visibility = 'password';
							$visibility_trans = __( 'Password protected', 'audiotheme-i18n' );
						} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
							$visibility = 'public';
							$visibility_trans = __( 'Public, Sticky', 'audiotheme-i18n' );
						} else {
							$visibility = 'public';
							$visibility_trans = __( 'Public', 'audiotheme-i18n' );
						}
						?>

						<?php _e( 'Visibility:', 'audiotheme-i18n' ); ?>
						<span id="post-visibility-display"><?php echo esc_html( $visibility_trans ); ?></span>

						<?php if ( $can_publish ) { ?>
							<a href="#visibility" class="edit-visibility hide-if-no-js"><?php _e( 'Edit', 'audiotheme-i18n'  ); ?></a>

							<div id="post-visibility-select" class="hide-if-js">
								<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr( $post->post_password ); ?>">
								<?php if ( 'post' == $post_type ) : ?>
									<input type="checkbox" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> style="display: none">
								<?php endif; ?>
								<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>">

								<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?>>
								<label for="visibility-radio-public" class="selectit"><?php _e( 'Public', 'audiotheme-i18n' ); ?></label>
								<br>

								<?php if ( 'post' == $post_type && current_user_can( 'edit_others_posts' ) ) : ?>
									<span id="sticky-span">
										<input type="checkbox" name="sticky" id="sticky" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?>>
										<label for="sticky" class="selectit"><?php _e( 'Stick this post to the front page', 'audiotheme-i18n' ); ?></label>
										<br>
									</span>
								<?php endif; ?>

								<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?>>
								<label for="visibility-radio-password" class="selectit"><?php _e( 'Password protected', 'audiotheme-i18n' ); ?></label><br />

								<span id="password-span">
									<label for="post_password"><?php _e( 'Password:', 'audiotheme-i18n' ); ?></label>
									<input type="text" name="post_password" id="post_password" value="<?php echo esc_attr( $post->post_password ); ?>">
									<br>
								</span>

								<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?>>
								<label for="visibility-radio-private" class="selectit"><?php _e( 'Private', 'audiotheme-i18n' ); ?></label>
								<br>

								<p>
									<a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e( 'OK', 'audiotheme-i18n' ); ?></a>
									<a href="#visibility" class="cancel-post-visibility hide-if-no-js"><?php _e( 'Cancel', 'audiotheme-i18n' ); ?></a>
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
					// translators: Publish box date format, see http://php.net/date
					$datef = __( 'M j, Y @ G:i' );
					if ( 0 != $post->ID ) {
						if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
							$stamp = __( 'Scheduled for: <strong>%1$s</strong>', 'audiotheme-i18n' );
						} elseif ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
							$stamp = __( 'Published on: <strong>%1$s</strong>', 'audiotheme-i18n' );
						} elseif ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
							$stamp = __( 'Publish <strong>immediately</strong>', 'audiotheme-i18n' );
						} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
							$stamp = __( 'Schedule for: <strong>%1$s</strong>', 'audiotheme-i18n' );
						} else { // draft, 1 or more saves, date specified
							$stamp = __( 'Publish on: <strong>%1$s</strong>', 'audiotheme-i18n' );
						}
						$date = date_i18n( $datef, strtotime( $post->post_date ) );
					} else { // draft (no saves, and thus no date specified)
						$stamp = __( 'Publish <strong>immediately</strong>', 'audiotheme-i18n' );
						$date = date_i18n( $datef, strtotime( current_time( 'mysql' ) ) );
					}

					if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
						<div class="misc-pub-section curtime">
							<span id="timestamp"><?php printf( $stamp, $date ); ?></span>
							<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><?php _e( 'Edit', 'audiotheme-i18n' ) ?></a>
							<div id="timestampdiv" class="hide-if-js"><?php touch_time( ( 'edit' == $action ), 1 ); ?></div>
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

			<?php if ( 'auto-draft' != $post->post_status ) : ?>
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						$onclick = '';
						if ( ! EMPTY_TRASH_DAYS || $force_delete ) {
							$delete_text = __( 'Delete Permanently', 'audiotheme-i18n' );
							$onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme-i18n' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						} else {
							$delete_text = __( 'Move to Trash', 'audiotheme-i18n' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID , '', $force_delete ) ); ?>"<?php echo $onclick; ?>><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
			<?php endif; ?>

			<div id="publishing-action">
				<?php audiotheme_admin_spinner( array( 'id' => 'ajax-loading' ) ); ?>
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
					if ( $can_publish ) :
						if ( ! empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
							<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Schedule', 'audiotheme-i18n' ); ?>">
							<?php submit_button( __( 'Schedule', 'audiotheme-i18n' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php else : ?>
							<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish', 'audiotheme-i18n' ) ?>">
							<?php submit_button( __( 'Publish', 'audiotheme-i18n' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Submit for Review', 'audiotheme-i18n' ) ?>">
						<?php
						submit_button( __( 'Submit for Review', 'audiotheme-i18n' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) );
					endif;
				} else { ?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update', 'audiotheme-i18n' ) ?>">
					<input type="submit" name="save" id="publish" class="button-primary button-large" accesskey="p" value="<?php esc_attr_e( 'Update', 'audiotheme-i18n' ) ?>">
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
		'id' => '',
		'class' => 'ajax-loading',
		'echo' => true,
	) );

	if ( audiotheme_version_compare( 'wp', '3.5-beta-1', '<' ) ) {
		$spinner = sprintf( '<img src="%1$s" id="%2$s" class="spinner %3$s" alt="">',
			esc_url( admin_url( 'images/wpspin_light.gif' ) ),
			esc_attr( $args['id'] ),
			esc_attr( $args['class'] )
		);
	} else {
		$spinner = sprintf( '<span id="%1$s" class="spinner"></span>', esc_attr( $args['id'] ) );
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
	$before = ( 'before' == $position ) ? $relative_key : $relative_key + 1;

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
	$item = $menu[ $move_key ];
	unset( $menu[ $move_key ] );

	audiotheme_menu_insert_item( $item, $relative_slug, $position );
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
		if ( $menu_slug == $item[2] ) {
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
			if ( $item[2] == $move_slug ) {
				$move_key = $key;
			} elseif ( $item[2] == $after_slug ) {
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
