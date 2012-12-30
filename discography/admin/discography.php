<?php
/**
 * Discography-related admin functionality.
 *
 * @package AudioTheme_Framework
 * @subpackage Discography.
 */

/**
 * Include discography admin dependencies.
 */
require( AUDIOTHEME_DIR . 'discography/admin/ajax.php' );
require( AUDIOTHEME_DIR . 'discography/admin/record.php' );
require( AUDIOTHEME_DIR . 'discography/admin/track.php' );

/**
 * Load discography admin on init.
 *
 * @since 1.0.0
 */
add_action( 'init', 'audiotheme_load_discography_admin' );

/**
 * Attach hooks for loading and managing discography in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_load_discography_admin() {
	// Update the discography rewrite base.
	if ( isset( $_POST['audiotheme_discography_rewrite_base_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_discography_rewrite_base_nonce'], 'save-discography-rewrite-base' ) ) {
		update_option( 'audiotheme_discography_rewrite_base', $_POST['audiotheme_discography_rewrite_base'] );
	}
	
	// Register AJAX admin actions.
	add_action( 'wp_ajax_audiotheme_ajax_get_default_track', 'audiotheme_ajax_get_default_track' );
	
	// @todo Change this hook.
	add_action( 'load-themes.php', 'audiotheme_discography_setup' );
	
	add_action( 'admin_menu', 'audiotheme_discography_admin_menu' );
	add_action( 'admin_init', 'audiotheme_discography_admin_init' );
	add_filter( 'post_updated_messages', 'audiotheme_discography_post_updated_messages' );
	add_filter( 'audiotheme_nav_menu_archive_items', 'audiotheme_record_archive_menu_item' );
	
	// Records	
	add_action( 'save_post', 'audiotheme_record_save_post' );
	
	// Manage Records screen.
	add_filter( 'parse_query', 'audiotheme_records_admin_query' );
	add_filter( 'manage_edit-audiotheme_record_columns', 'audiotheme_record_register_columns' );
	add_action( 'manage_edit-audiotheme_record_sortable_columns', 'audiotheme_record_register_sortable_columns' );
	add_action( 'manage_pages_custom_column', 'audiotheme_record_display_columns', 10, 2 );
	add_filter( 'bulk_actions-edit-audiotheme_record', 'audiotheme_record_list_table_bulk_actions' );
	add_action( 'page_row_actions', 'audiotheme_record_list_table_actions', 10, 2 );
	
	// Tracks
	add_action( 'save_post', 'audiotheme_track_save_post' );
	add_action( 'wp_unique_post_slug', 'audiotheme_track_unique_slug', 10, 6 );
	
	// Manage Tracks screen.
	add_filter( 'parse_query', 'audiotheme_tracks_admin_query' );
	add_action( 'restrict_manage_posts', 'audiotheme_tracks_filters' );
	add_filter( 'manage_edit-audiotheme_track_columns', 'audiotheme_track_register_columns' );
	add_action( 'manage_edit-audiotheme_track_sortable_columns', 'audiotheme_track_register_sortable_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_track_display_columns', 10, 2 );
	add_filter( 'bulk_actions-edit-audiotheme_track', 'audiotheme_track_list_table_bulk_actions' );
	add_action( 'page_row_actions', 'audiotheme_track_list_table_actions', 10, 2 );
}

/**
 * Add initial discography data.
 *
 * Ensures the record type taxonomies exist. Runs anytime themes.php is
 * visited to ensure record types exist.
 *
 * @since 1.0.0
 * @todo Hook up elsewhere now that we're going the plugin route.
 */
function audiotheme_discography_setup() {
	if ( taxonomy_exists( 'audiotheme_record_type' ) ) {
		$record_types = get_audiotheme_record_type_slugs();
		if ( $record_types ) {
			foreach( $record_types as $type_slug ) {
				if ( ! term_exists( $type_slug, 'audiotheme_record_type' ) ) {
					wp_insert_term( $type_slug, 'audiotheme_record_type', array( 'slug' => $type_slug ) );
				}
			}
		}
	}
}

/**
 * Discography admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_discography_admin_menu() {
	add_menu_page( __( 'Discography', 'audiotheme-i18n' ), __( 'Discography', 'audiotheme-i18n' ), 'edit_posts', 'edit.php?post_type=audiotheme_record', null, null, 513 );
}

/**
 * Discography update messages.
 *
 * @since 1.0.0
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_discography_post_updated_messages( $messages ) {
	global $post;
	
	$messages['audiotheme_record'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Record updated. <a href="%s">View Record</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3  => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4  => __( 'Record updated.', 'audiotheme-i18n' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Record published. <a href="%s">View Record</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Record saved.', 'audiotheme-i18n' ),
		8  => sprintf( __( 'Record submitted. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Record</a>', 'audiotheme-i18n' ),
		      // translators: Publish box date format, see http://php.net/date
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Record draft updated. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);
	
	$messages['audiotheme_track'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Track updated. <a href="%s">View Track</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3  => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4  => __( 'Track updated.', 'audiotheme-i18n' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Track published. <a href="%s">View Track</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Track saved.', 'audiotheme-i18n' ),
		8  => sprintf( __( 'Track submitted. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Track</a>', 'audiotheme-i18n' ),
		      // translators: Publish box date format, see http://php.net/date
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Track draft updated. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}

/**
 * Register discography rewrite base setting.
 *
 * @since 1.0.0
 */
function audiotheme_discography_admin_init() {
	add_settings_field(
		'audiotheme_discography_rewrite_base',
		'<label for="audiotheme-discography-rewrite-base">' . __( 'Discography base', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_discography_rewrite_base_settings_field',
		'permalink',
		'optional'
	);
}

/**
 * Callback for displaying the discography rewrite base field.
 *
 * @since 1.0.0
 */
function audiotheme_discography_rewrite_base_settings_field() {
	$discography_base = get_option( 'audiotheme_discography_rewrite_base' );
	wp_nonce_field( 'save-discography-rewrite-base', 'audiotheme_discography_rewrite_base_nonce' );
	?>
	<input type="text" name="audiotheme_discography_rewrite_base" id="audiotheme-discography-rewrite-base" value="<?php echo esc_attr( $discography_base ); ?>" class="regular-text code">
	<span class="description"><?php _e( 'Default is <code>music</code>.', 'audiotheme-i18n' ); ?></span>
	<?php
}

/**
 * Add the discography archive link nav menu item to the custom AudioTheme Pages nav menu meta box.
 *
 * @since 1.0.0
 */
function audiotheme_record_archive_menu_item( $items ) {
	$items[] = array(
		'title'     => _x( 'Discography', 'nav menu archive label' ),
		'post_type' => 'audiotheme_record',
		'url'       => get_post_type_archive_link( 'audiotheme_record' )
	);
	
	return $items;
}
?>