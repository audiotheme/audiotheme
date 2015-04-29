<?php
/**
 * Post type archives admin functionality.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link
 * (they stay updated!).
 *
 * @package AudioTheme_Framework
 * @subpackage Archives
 *
 * @since 1.0.0
 */

/**
 * Setup archive posts for post types that have support.
 *
 * @since 1.0.0
 */
function audiotheme_archives_init_admin() {
	if ( is_network_admin() ) {
		return;
	}

	$archives = array();

	$post_types = array( 'audiotheme_gig', 'audiotheme_record', 'audiotheme_video' );

	// Add an archive if one doesn't exist for whitelisted post types.
	foreach ( $post_types as $post_type ) {
		$id = audiotheme_archives_create_archive( $post_type );
		if ( $id ) {
			$archives[ $post_type ] = $id;
		}
	}

	audiotheme_archives_save_active_archives( $archives );

	add_action( 'save_post', 'audiotheme_archive_save_hook', 10, 2 );
	add_action( 'load-post.php', 'audiotheme_archive_help' );
	add_action( 'parent_file', 'audiotheme_archives_parent_file' );
	add_filter( 'post_updated_messages', 'audiotheme_archives_post_updated_messages' );

	// Make archive links appear last.
	add_action( 'admin_menu', 'audiotheme_archives_admin_menu', 100 );
	add_action( 'add_meta_boxes_audiotheme_archive', 'audiotheme_archives_add_meta_boxes' );
	add_action( 'audiotheme_archive_settings_meta_box', 'audiotheme_archive_settings_meta_box_fields', 15, 3 );
}

/**
 * Add submenu items for archives under the post type menu item.
 *
 * Ensures the user has the capability to edit pages in general as well
 * as the individual page before displaying the submenu item.
 *
 * @since 1.0.0
 */
function audiotheme_archives_admin_menu() {
	$archives = get_audiotheme_archive_ids();

	if ( empty( $archives ) ) {
		return;
	}

	// Verify the user can edit audiotheme_archive posts.
	$archive_type_object = get_post_type_object( 'audiotheme_archive' );
	if ( ! current_user_can( $archive_type_object->cap->edit_posts ) ) {
		return;
	}

	foreach ( $archives as $post_type => $archive_id ) {
		// Verify the user can edit the particular audiotheme_archive post in question.
		if ( ! current_user_can( $archive_type_object->cap->edit_post, $archive_id ) ) {
			continue;
		}

		$parent_slug = ( 'audiotheme_gig' === $post_type ) ? 'audiotheme-gigs' : 'edit.php?post_type=' . $post_type;

		// Add the submenu item.
		add_submenu_page(
			$parent_slug,
			$archive_type_object->labels->singular_name,
			$archive_type_object->labels->singular_name,
			$archive_type_object->cap->edit_posts,
			add_query_arg( array( 'post' => $archive_id, 'action' => 'edit' ), 'post.php' ),
			null
		);
	}
}

/**
 * Replace the submit meta box to remove unnecessary fields.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_archives_add_meta_boxes( $post ) {
	$post_type = is_audiotheme_post_type_archive_id( $post->ID );

	remove_meta_box( 'submitdiv', 'audiotheme_archive', 'side' );

	add_meta_box( 'submitdiv', __( 'Update', 'audiotheme' ), 'audiotheme_post_submit_meta_box', 'audiotheme_archive', 'side', 'high', array(
		'force_delete'      => false,
		'show_publish_date' => false,
		'show_statuses'     => false,
		'show_visibility'   => false,
	) );

	// Activate the default archive settings meta box.
	$show = apply_filters( 'add_audiotheme_archive_settings_meta_box', false, $post_type );
	$show_for_post_type = apply_filters( 'add_audiotheme_archive_settings_meta_box_' . $post_type, false );

	// Show if any settings fields have been registered for the post type.
	$fields = apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );

	if ( $show || $show_for_post_type || ! empty( $fields ) ) {
		add_meta_box(
			'audiothemesettingsdiv',
			__( 'Archive Settings', 'audiotheme' ),
			'audiotheme_archive_settings_meta_box',
			'audiotheme_archive',
			'side',
			'default',
			array(
				'fields' => $fields,
			)
		);
	}
}

/**
 * Highlight the corresponding top level and submenu items when editing an
 * archive page.
 *
 * @since 1.0.0
 *
 * @param string $parent_file A parent file identifier.
 * @return string
 */
function audiotheme_archives_parent_file( $parent_file ) {
	global $post, $submenu_file;

	if ( $post && 'audiotheme_archive' === get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID ) ) {
		$parent_file = 'edit.php?post_type=' . $post_type;
		$submenu_file = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), 'post.php' );

		// The Gigs list has a custom slug.
		if ( 'audiotheme_gig' === $post_type ) {
			$parent_file = 'audiotheme-gigs';
		}
	}

	return $parent_file;
}

/**
 * Archive update messages.
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array An array with new CPT update messages.
 */
function audiotheme_archives_post_updated_messages( $messages ) {
	global $post;

	$messages['audiotheme_archive'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Archive updated. <a href="%s">View Archive</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme' ),
		3  => __( 'Custom field deleted.', 'audiotheme' ),
		4  => __( 'Archive updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Archive restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Archive published. <a href="%s">View Archive</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Archive saved.', 'audiotheme' ),
		8  => sprintf( __( 'Archive submitted. <a target="_blank" href="%s">Preview Archive</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Archive scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Archive</a>', 'audiotheme' ),
		      /* translators: Publish box date format, see http://php.net/date */
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Archive draft updated. <a target="_blank" href="%s">Preview Archive</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}

/**
 * Create an archive post for a post type if one doesn't exist.
 *
 * The post type's plural label is used for the post title and the defined
 * rewrite slug is used for the postname.
 *
 * @since 1.0.0
 *
 * @param string $post_type_name Post type slug.
 * @return int Post ID.
 */
function audiotheme_archives_create_archive( $post_type ) {
	$archive_id = get_audiotheme_post_type_archive( $post_type );
	if ( $archive_id ) {
		return $archive_id;
	}

	// Search the inactive option before creating a new page.
	$inactive = get_option( 'audiotheme_archives_inactive' );
	if ( $inactive && isset( $inactive[ $post_type ] ) && get_post( $inactive[ $post_type ] ) ) {
		return $inactive[ $post_type ];
	}

	// Otherwise, create a new archive post.
	$post_type_object = get_post_type_object( $post_type );

	$post = array(
		'post_title'  => $post_type_object->labels->name,
		'post_name'   => get_audiotheme_post_type_archive_slug( $post_type ),
		'post_type'   => 'audiotheme_archive',
		'post_status' => 'publish',
	);

	return wp_insert_post( $post );
}

/**
 * Retrieve a post type's archive slug.
 *
 * Checks the 'has_archive' and 'with_front' args in order to build the
 * slug.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type name.
 * @return string Archive slug.
 */
function get_audiotheme_post_type_archive_slug( $post_type ) {
	global $wp_rewrite;

	$post_type_object = get_post_type_object( $post_type );

	$slug = ( false !== $post_type_object->rewrite ) ? $post_type_object->rewrite['slug'] : $post_type_object->name;

	if ( $post_type_object->has_archive ) {
		$slug = ( true === $post_type_object->has_archive ) ? $post_type_object->rewrite['slug'] : $post_type_object->has_archive;

		if ( $post_type_object->rewrite['with_front'] ) {
			$slug = substr( $wp_rewrite->front, 1 ) . $slug;
		} else {
			$slug = $wp_rewrite->root . $slug;
		}
	}

	return $slug;
}

/**
 * Save archive meta data.
 *
 * @since 1.3.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archive_save_hook( $post_id, $post ) {
	$is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = isset( $_POST['audiotheme_archive_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_archive_nonce'], 'save-archive-meta_' . $post_id );

	// Bail if the data shouldn't be saved or intention can't be verified.
	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	$post_type = is_audiotheme_post_type_archive_id( $post->ID );
	do_action( 'save_audiotheme_archive_settings', $post_id, $post, $post_type );

	// Save default field data.
	$fields = apply_filters( 'audiotheme_archive_settings_fields', array(), $post_type );

	if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
		$posts_per_archive_page = ( is_numeric( $_POST['posts_per_archive_page'] ) ) ? intval( $_POST['posts_per_archive_page'] ) : '';
		update_post_meta( $post_id, 'posts_per_archive_page', $posts_per_archive_page );
	}

	if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
		$choices = range( 3, 5 );
		if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
			$choices = array_map( 'absint', $fields['columns']['choices'] );
		}

		$value = absint( $_POST['columns'] );
		if ( ! in_array( $value, $choices ) ) {
			$choices_min = min( $choices );
			$choices_max = max( $choices );
			$value = min( max( absint( $_POST['columns'] ), $choices_min ), $choices_max );
		}

		update_post_meta( $post_id, 'columns', $value );
	}
}

/**
 * Display archive settings meta box.
 *
 * The meta box needs to be activated first, then fields can be displayed using
 * one of the actions.
 *
 * @since 1.3.0
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box( $post, $args = array() ) {
	$post_type = is_audiotheme_post_type_archive_id( $post->ID );
	wp_nonce_field( 'save-archive-meta_' . $post->ID, 'audiotheme_archive_nonce' );
	do_action( 'audiotheme_archive_settings_meta_box', $post, $post_type, $args['args']['fields'] );
	do_action( 'audiotheme_archive_settings_meta_box_' . $post_type, $post, $args['args']['fields'] );
}

/**
 * Add fields to the archive settings meta box.
 *
 * @since 1.4.2
 *
 * @param WP_Post $post Archive post.
 */
function audiotheme_archive_settings_meta_box_fields( $post, $post_type, $fields = array() ) {
	if ( empty( $fields ) ) {
		return;
	}

	if ( ! empty( $fields['posts_per_archive_page'] ) && $fields['posts_per_archive_page'] ) {
		$default = ( empty( $fields['posts_per_archive_page']['default'] ) ) ? '' : absint( $default );
		$value = get_audiotheme_archive_meta( 'posts_per_archive_page', true, $default, $post_type );
		?>
		<p>
			<label for="audiotheme-posts-per-archive-page"><?php _e( 'Posts per page:', 'audiotheme' ); ?></label>
			<input type="text" name="posts_per_archive_page" id="audiotheme-posts-per-archive-page" value="<?php echo esc_attr( $value ); ?>" class="small-text">
		</p>
		<?php
	}

	if ( ! empty( $fields['columns'] ) && $fields['columns'] ) {
		$default = ( empty( $fields['columns']['default'] ) ) ? 4 : absint( $default );
		$value = get_audiotheme_archive_meta( 'columns', true, $default, $post_type );
		$choices = range( 3, 5 );

		if ( ! empty( $fields['columns']['choices'] ) && is_array( $fields['columns']['choices'] ) ) {
			$choices = array_map( 'absint', $fields['columns']['choices'] );
		}
		?>
		<p>
			<label for="audiotheme-columns"><?php _e( 'Columns:', 'audiotheme' ); ?></label>
			<select name="columns" id="audiotheme-columns">
				<?php
				foreach ( $choices as $number ) {
					printf( '<option value="%1$d"%2$s>%1$d</option>',
						$number,
						selected( $number, $value, false )
					);
				}
				?>
			</select>
		</p>
		<?php
	}
}

/**
 * Add a help tab to the add/edit archive screen.
 *
 * @since 1.0.0
 */
function audiotheme_archive_help() {
	if ( 'audiotheme_archive' !== get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'standard-fields',
		'title'   => __( 'Standard Fields', 'audiotheme' ),
		'content' =>
			'<p>' . __( "<strong>Title</strong> - Enter the title of the archive screen.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Editor</strong> - Enter an introduction for the archive. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your description text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular editor.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "When you're done editing, click the Update button.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'permalinks',
		'title'		=> __( 'Permalinks', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( "Editing the permalink for an archive changes the URL for all content associated with that archive. For example, the default discography slug is <code>music</code>; changing it to <code>albums</code> would change your URLs like this:", 'audiotheme' ) . '</p>' .
			'<p><strong>' . __( "Before:", 'audiotheme' ) . '</strong> <code>' . home_url( '/music/a-record-name/' ) . '</code><br><strong>' . __( "After:", 'audiotheme' ) . '</strong> <code>' . home_url( '/albums/a-record-name/' ) . '</code></p>' .
			'<p>' . __( "Taking this into consideration, you shouldn't change your slug on an established site, but it provides a powerful option to easily customize the structure of your URLs.", 'audiotheme' ) . '</p>',
	) );
}
