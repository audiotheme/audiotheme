<?php
/**
 * Deprecated functions.
 *
 * @package AudioTheme\Deprecated
 * @since 1.9.0
 */

/**
 * Get the admin panel URL for gigs.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function get_audiotheme_gig_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-gigs' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Higlight the correct top level and sub menu items for the gig screen being
 * displayed.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 *
 * @param string $parent_file The screen being displayed.
 * @return string The menu item to highlight.
 */
function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	global $pagenow, $post_type, $submenu, $submenu_file;

	if ( 'audiotheme_gig' === $post_type ) {
		$parent_file = 'audiotheme-gigs';
		$submenu_file = ( 'post.php' === $pagenow ) ? 'audiotheme-gigs' : $submenu_file;
	}

	if ( 'audiotheme-gigs' === $parent_file && isset( $_GET['page'] ) && 'audiotheme-venue' === $_GET['page'] ) {
		$submenu_file = 'audiotheme-venues';
	}

	if ( 'audiotheme_venue' === $post_type ) {
		$parent_file = 'audiotheme-gigs';
	}

	// Remove the Add New Venue submenu item.
	if ( isset( $submenu['audiotheme-gigs'] ) ) {
		foreach ( $submenu['audiotheme-gigs'] as $key => $sm ) {
			if ( isset( $sm[0] ) && 'audiotheme-venue' === $sm[2] ) {
				unset( $submenu['audiotheme-gigs'][ $key ] );
			}
		}
	}

	return $parent_file;
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
 * @deprecated 1.9.0
 * @since 1.0.0
 *
 * @param bool $return Default is 'false'.
 * @param string $option The option name.
 * @param mixed $value The value to sanitize.
 * @return mixed The sanitized value.
 */
function audiotheme_gigs_screen_options( $return, $option, $value ) {
	if ( 'toplevel_page_audiotheme_gigs_per_page' === $option || 'gigs_page_audiotheme_venues_per_page' === $option ) {
		$return = absint( $value );
	}

	return $return;
}

/**
 * Set up the gig Manage Screen.
 *
 * Initializes the custom post list table, and processes any actions that need
 * to be handled.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen_setup() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );

	require_once( AUDIOTHEME_DIR . 'modules/gigs/admin/class-audiotheme-gigs-list-table.php' );

	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->process_actions();
}

/**
 * Display the gig Manage Screen.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );

	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->prepare_items();

	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/list-gigs.php' );
}

/**
 * Get the base admin panel URL for adding a venue.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function get_audiotheme_venue_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-venue' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for viewing all venues.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function get_audiotheme_venues_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-venues' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for editing a venue.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function get_audiotheme_venue_edit_link( $admin_url, $post_id ) {
	if ( 'audiotheme_venue' === get_post_type( $post_id ) ) {
		$args = array(
			'action'   => 'edit',
			'venue_id' => $post_id,
		);

		$admin_url = get_audiotheme_venue_admin_url( $args );
	}

	return $admin_url;
}

/**
 * Process venue add/edit actions.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen_process_actions() {
	$action = '';
	if ( isset( $_POST['audiotheme_venue'] ) && isset( $_POST['audiotheme_venue_nonce'] ) ) {
		$data = $_POST['audiotheme_venue'];
		$nonce_action = ( empty( $data['ID'] ) ) ? 'add-venue' : 'update-venue_' . $data['ID'];

		// Should die on error.
		if ( check_admin_referer( $nonce_action, 'audiotheme_venue_nonce' ) ) {
			$action = ( ! empty( $data['ID'] ) ) ? 'edit' : 'add';
		}
	}

	if ( ! empty( $action ) ) {
		$venue_id = save_audiotheme_venue( $data );
		$sendback = get_edit_post_link( $venue_id );

		if ( $venue_id && 'add' === $action ) {
			$sendback = add_query_arg( 'message', 1, $sendback );
		} elseif ( $venue_id && 'edit' === $action ) {
			$sendback = add_query_arg( 'message', 2, $sendback );
		}

		wp_safe_redirect( esc_url_raw( $sendback ) );
		exit;
	}
}

/**
 * Display the venue add/edit screen.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen() {
	$screen = get_current_screen();
	$post_type_object = get_post_type_object( 'audiotheme_venue' );

	$action = 'add';
	$nonce_field = wp_nonce_field( 'add-venue', 'audiotheme_venue_nonce', true, false );
	$values = get_default_audiotheme_venue_properties();

	if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
		$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );

		$action = 'edit';
		$nonce_field = wp_nonce_field( 'update-venue_' . $venue_to_edit->ID, 'audiotheme_venue_nonce', true, false );
		$values = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
	}

	extract( $values, EXTR_SKIP );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-venue.php' );
}

/**
 * Display custom venue submit meta box.
 *
 * @deprecated 1.9.0
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_submit_meta_box( $post ) {
	$post = ( empty( $post ) ) ? get_default_post_to_edit( 'audiotheme_venue' ) : $post;
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	?>
	<div class="submitbox" id="submitpost">

		<div id="major-publishing-actions">

			<?php if ( 'auto-draft' !== $post->post_status && 'draft' !== $post->post_status ) : ?>
				<div id="delete-action">
					<?php
					if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
						$delete_args['action'] = 'delete';
						$delete_args['venue_id'] = $post->ID;
						$delete_url = get_audiotheme_venues_admin_url( $delete_args );
						$delete_url_onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						echo sprintf( '<a href="%s" class="submitdelete deletion"%s>%s</a>', wp_nonce_url( $delete_url, 'delete-venue_' . $post->ID ), $delete_url_onclick, esc_html( __( 'Delete Permanently', 'audiotheme' ) ) );
					}
					?>
				</div>
			<?php endif; ?>

			<div id="publishing-action">
				<?php audiotheme_admin_spinner( array( 'id' => 'ajax-loading' ) ); ?>
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 === $post->ID ) {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish', 'audiotheme' ) ?>">
					<?php
					submit_button( $post_type_object->labels->add_new_item, 'primary', 'publish', false, array( 'accesskey' => 'p' ) );
				} else {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
					<input type="submit" name="save" id="publish" class="button-primary" accesskey="p" value="<?php esc_attr_e( 'Update', 'audiotheme' ) ?>">
				<?php } ?>
			</div><!--end div#publishing-action-->

			<div class="clear"></div>
		</div><!--end div#major-publishing-actions-->
	</div><!--end div#submitpost-->

	<script type="text/javascript">
	jQuery(function($) {
		$('input[type="submit"], a.submitdelete').click(function(){
			window.onbeforeunload = null;
			$(':button, :submit', '#submitpost').each(function(){
				var t = $(this);
				if ( t.hasClass('button-primary') )
					t.addClass('button-primary-disabled');
				else
					t.addClass('button-disabled');
			});

			if ( $(this).attr('id') == 'publish' )
				$('#major-publishing-actions .spinner').show();
		});
	});
	</script>
	<?php
}
