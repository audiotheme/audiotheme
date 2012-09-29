<?php
/**
 * Customizable Submit Meta Box
 *
 * @see post_submit_meta_box()
 * 
 * @since 1.0.0
 * @todo "misc-pub-section-last" and "button-highlighed" classes can be removed when < 3.5 support is ended.
 */
function audiotheme_post_submit_meta_box( $post, $metabox ) {
	global $action;
	
	$defaults = array(
		'force_delete' => false,
		'show_publish_date' => true,
		'show_statuses' => array(
			'pending' => __( 'Pending Review', 'audiotheme-i18n' )
		),
		'show_visibility' => true
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
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button button-highlighted" <?php if ( 'private' == $post->post_status ) { echo 'style="display: none"'; } ?>>
					<?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
						<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button button-highlighted">
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
					<div class="misc-pub-section<?php if( ! $can_publish || ( ! $show_visibility && ! $show_publish_date ) ) { echo ' misc-pub-section-last'; } ?>">
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
						<div class="misc-pub-section curtime misc-pub-section-last">
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
 */
function audiotheme_admin_spinner( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'id' => '',
		'class' => 'ajax-loading'
	) );
	
	if ( version_compare( get_bloginfo( 'version' ), '3.5-beta-1', '<' ) ) {
		printf( '<img src="%1$s" id="%2$s" class="%3$s" alt="">',
			esc_url( admin_url( 'images/wpspin_light.gif' ) ),
			esc_attr( $args['id'] ),
			esc_attr( $args['class'] )
		);
	} else {
		echo '<span class="spinner"></span>';
	}
}
?>