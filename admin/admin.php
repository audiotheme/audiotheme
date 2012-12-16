<?php
/**
 * Admin Includes
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'admin/functions.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-settings.php' );
require( AUDIOTHEME_DIR . 'admin/includes/settings-screens.php' );

/**
 * Admin Setup
 *
 * @since 1.0.0
 */
add_action( 'after_setup_theme', 'audiotheme_admin_setup' );

function audiotheme_admin_setup() {
	add_action( 'init', 'audiotheme_admin_init' );
	add_action( 'init', 'audiotheme_settings_init' );
	
	add_action( 'admin_init', 'audiotheme_register_directory_browsing_setting' );
	add_action( 'update_option_audiotheme_disable_directory_browsing', 'audiotheme_disable_directory_browsing_option_update', 10, 2 );
	
	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'admin_head-nav-menus.php', 'audiotheme_nav_menu_admin_head');
	add_action( 'admin_body_class', 'audiotheme_admin_body_class' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );
	
	add_action( 'manage_pages_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	
	add_filter( 'custom_menu_order', '__return_true' );
	add_filter( 'menu_order', 'audiotheme_admin_menu_order', 999 );
	
	// Fires new action hooks in older versions for backwards compatibility.
	add_action( 'edit_form_advanced', 'audiotheme_edit_form_compat_actions' );
	
	// Print javascript pointer object.
	add_action( 'admin_print_footer_scripts', 'audiotheme_print_pointers' );
	
	
	// @todo Reimplement the license key functionality.
	
	// Automatic updates require support for 'audiotheme-theme-options' to be enabled
	// Otherwise, the license key functionality needs to be added in custom hooks
	/*if ( current_theme_supports( 'audiotheme-automatic-updates' ) ) {
		include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater.php' );
		$support = get_theme_support( 'audiotheme-automatic-updates' );
		Audiotheme_Updater::setup( $support[0] );
		
		add_action( 'pre_update_option_audiotheme_options', 'audiotheme_default_options_update', 10, 2 );
		add_action( 'admin_init', 'audiotheme_default_settings', 9 ); // Will appear before options registered in the theme
		add_action( 'load-appearance_page_audiotheme-theme-options', 'audiotheme_license_status_error' );
	}*/
}

function audiotheme_admin_init() {
	wp_register_script( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/js/audiotheme-admin.js', array( 'jquery-ui-sortable' ) );
	wp_register_script( 'audiotheme-pointer', AUDIOTHEME_URI . 'admin/js/audiotheme-pointer.js', array( 'wp-pointer' ) );
	wp_register_script( 'audiotheme-settings', AUDIOTHEME_URI . 'admin/js/audiotheme-settings.js' );

	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/audiotheme-admin.css' );
	wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
}

/**
 * Register default options
 *
 * @since 1.0.0
 */
function audiotheme_default_settings() {
	$settings = Audiotheme_Settings::instance();
	
	$section = $settings->add_section( 'general', 'General Settings', '' );
		$settings->add_field( 'text', 'license_key', __( 'License Key', 'audiotheme-i18n' ), $section, array(
			'description' => ( 'valid' == get_option( 'audiotheme_license_key_status' ) ) ? ' <span style="color: green; font-style: normal">OK</span>' : ''
		) );
}

/**
 * Execute additional functionality when options are updated
 *
 * Hooks into the 'pre_update_option_{$option}' action so the license can be
 * checked even if a field hasn't been changed.
 *
 * Currently it will activate and check license key status so visual feedback
 * can be provided.
 *
 * @since 1.0.0
 * @todo The status option should be unique per theme.
 * @todo When a key expires, or if it's deactivated on the server, there's no
 *       way to notify the user. The server should return the license status
 *       with an update check.
 */
function audiotheme_default_options_update( $newvalue, $oldvalue ) {
	if ( isset( $newvalue['license_key'] ) ) {
		$license_status = get_option( 'audiotheme_license_key_status' );
		$license_key_changed = ( isset( $oldvalue['license_key'] ) && $oldvalue['license_key'] != $newvalue['license_key'] ) ? true : false;
		
		// Only hit the API if the license key field changed or the current license status is somehow empty
		// If a change is made at the server level, the user would have to make a modification to their key to recognize it
		if ( ( $license_key_changed && ! empty( $newvalue['license_key'] ) ) || empty( $license_status ) ) {
			// Check the license to see if it's active, if not, activate it
			$status = Audiotheme_Updater::check_license_status( $newvalue['license_key'] );
			if ( 'invalid' == $status ) {
				$status = Audiotheme_Updater::activate_license( $newvalue['license_key'] );
			}
			update_option( 'audiotheme_license_key_status', $status );
		} elseif ( empty( $newvalue['license_key'] ) ) {
			update_option( 'audiotheme_license_key_status', '' );
		}
	}
	
	return $newvalue;
}

/**
 * Show an error on the default theme options if the license key is invalid.
 *
 * @since 1.0.0
 * @todo Update the option name if refactored.
 */
function audiotheme_license_status_error() {
	$license_status = get_option( 'audiotheme_license_key_status' );
	if ( $license_status && 'valid' !== $license_status ) {
		add_settings_error( 'audiotheme_options', 'license_key', __( 'Invalid license key.', 'audiotheme-i18n' ) );
	}
}

/**
 * Register Directory Browsing Settings
 *
 * Registers a setting on the Privacy screen to disable directory browsing so
 * the uploads folder can't be accessed directly.
 *
 * @since 1.0.0
 * @todo Only show if using Apache.
 * @todo Error message if .htaccess isn't writable.
 * @todo Remove group comparison after < 3.5 support is dropped.
 */
function audiotheme_register_directory_browsing_setting() {
	// Privacy settings group was deprecated in 3.5
	$group = ( audiotheme_version_compare( 'wp', '3.5-beta-1', '<' ) ) ? 'privacy' : 'reading';
	
	register_setting( $group, 'audiotheme_disable_directory_browsing' );
	
	add_settings_field(
		'audiotheme_disable_directory_browsing',
		'<label for="audiotheme-disable-directory-browsing">' . __( 'Directory Browsing', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_disable_directory_browsing_setting_field',
		$group,
		'default'
	);
}

/**
 * Display Directory Browsing Setting
 * 
 * @since 1.0.0
 */
function audiotheme_disable_directory_browsing_setting_field() {
	$disable_browsing = get_option( 'audiotheme_disable_directory_browsing' );
	?>
	<input type="checkbox" name="audiotheme_disable_directory_browsing" id="audiotheme-disable-directory-browsing" value="1"<?php checked( $disable_browsing, true ); ?>>
	<label for="audiotheme-disable-directory-browsing"><?php _e( 'Disable directory browsing?', 'audiotheme-i18n' ); ?></label>
	<?php
}

/**
 * Update Directory Browsing
 * 
 * Whenever the directory browsing setting is updated, update .htaccess
 * 
 * @since 1.0.0
 */
function audiotheme_disable_directory_browsing_option_update( $oldvalue, $newvalue ) {
	audiotheme_save_htaccess();
}

/**
 * Save .htacess
 * 
 * Updates the .htaccess file.
 * 
 * @see save_mod_rewrite_rules()
 * 
 * @since 1.0.0
 * */
function audiotheme_save_htaccess() {
	$home_path = get_home_path();
	$htaccess_file = $home_path . '.htaccess';
	
	if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) ) {
		$htaccess_contents = file_get_contents( $htaccess_file );
		
		$directive = 'Options All -Indexes';
		$rules = array();
		if ( get_option( 'audiotheme_disable_directory_browsing' ) && false === strpos( $htaccess_contents, $directive ) ) {
			$rules[] = $directive;
		}
		
		return insert_with_markers( $htaccess_file, 'AudioTheme', $rules );
	}	
}

/**
 * Enqueue Admin Scripts
 *
 * Should be loaded on every admin request
 *
 * @since 1.0.0
 */
function audiotheme_enqueue_admin_scripts() {
	wp_enqueue_script( 'audiotheme-admin' );
	wp_enqueue_style( 'audiotheme-admin' );
}

/**
 * Register Nave Menu Meta Box
 * 
 * Registers the meta box for adding AudioTheme CPT archive links to nav
 * menus.
 * 
 * @since 1.0.0
 */
function audiotheme_nav_menu_admin_head() {
	add_meta_box( 'add-audiotheme-archive-links', __( 'AudioTheme Pages', 'audiotheme-i18n' ), 'audiotheme_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'default' );
}

/**
 * Nav Menu Meta Box for AudioTheme CPT Archives
 *
 * Adds a meta box to the nav menu screen for listing AudioTheme CPT archive
 * pages and allowing them to be easily included in nav menus. The CPTs should
 * hook into the `nav_menu_items_audiotheme_archive_pages` filter to add an
 * item to the list.
 *
 * @todo At some point, it would be nice to have these menu items
 * automatically reflect the post type archive link as it's changed without
 * worrying about the URL input, but the only way to currently do it requires
 * making the 'type' argument an empty string and that seems awfully flimsy.
 * 
 * @link http://codeseekah.com/2012/03/01/custom-post-type-archives-in-wordpress-menus-2/
 *
 * @since 1.0.0
 */
function audiotheme_nav_menu_item_link_meta_box( $object, $box ) {
	global $_nav_menu_placeholder, $nav_menu_selected_id;
	$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) : -1;
	
	$post_type_name = 'audiotheme_archive_pages';
	?>
	<div id="posttype-<?php echo $post_type_name; ?>" class="posttypediv">
		<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-active">
			<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name?> categorychecklist form-no-clear">
				<?php
				// Hooks returning items should return them as an array with 'title' and 'url' arguments
				// array( 'title' => 'Custom Title', 'url' => 'Custom URL' ) 
				$items = apply_filters( 'audiotheme_nav_menu_archive_items', array(), $box );
				
				if ( $items ) {
					// Transform the item array into a format expected by the nav walker
					foreach ( $items as $key => $item ) {
						$_nav_menu_placeholder --;
						
						$items[ $key ] = (object) array(
							'ID'           => 0,
							'object'       => '',
							'object_id'    => $_nav_menu_placeholder,
							'post_title'   => $item['title'],
							'post_type'    => 'nav_menu_item',
							'post_content' => '',
							'post_excerpt' => '',
							'type'         => 'custom',
							'url'          => $item['url']
						);
					}
					
					$items = audiotheme_sort_objects( $items, 'post_title', 'asc', false );
				}
				
				$args['walker'] = new Walker_Nav_Menu_Checklist( false );
				echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $items ), 0, (object) $args );
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="add-to-menu">
				
				<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-<?php echo $post_type_name; ?>">
				<?php audiotheme_admin_spinner( array( 'class' => 'waiting' ) ); ?>
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}

/**
 * Add Current Screen ID as CSS Class to <body>
 *
 * @since 1.0.0
 */
function audiotheme_admin_body_class( $class ) {
	return ' ' . sanitize_html_class( get_current_screen()->id );
}

/**
 * Custom Post Type Columns
 *
 * This hook is run for all custom columns, so the column name is prefixed to
 * prevent potential conflicts.
 * 
 * @since 1.0.0
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {
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
 * Custom User Contact Fields
 *
 * @since 1.0.0
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	// Add contact options
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme-i18n' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme-i18n' );
	
	return $contactmethods;
}

/**
 * Re-order the admin menu
 *
 * Positions AudioTheme admin menu items after Posts menu item if the Gigs
 * menu item position hasn't been modified. Should play nice with plugins.
 *
 * @since 1.0.0
 */
function audiotheme_admin_menu_order( $menu_order ) {
	global $menu;
	
	$start_key = array_search( 'edit.php', $menu_order );
	
	// Only try to re-order the menu items if the gigs menu hasn't been moved.
	if ( false !== $start_key && array_key_exists( 512, $menu ) && 'audiotheme-gigs' == $menu[512][2] ) {
		$audiotheme_admin_menu_order = array( 'audiotheme-gigs', 'edit.php?post_type=audiotheme_record', 'edit.php?post_type=audiotheme_video', 'edit.php?post_type=audiotheme_gallery' );
		
		foreach ( $audiotheme_admin_menu_order as $i => $item ) {
			$menu_key = array_search( $item, $menu_order );
			if ( $menu_key ) {
				$new_position = $start_key + $i + 1;
				array_splice( $menu_order, $new_position, 0, $menu_order[ $menu_key ] ); // Insert the item in its new location.
				unset( $menu_order[ $menu_key + 1 ] ); // Remove the old menu item.
				$menu_order = array_values( $menu_order ); // Re-key the array.
			}
		}
	}
	
	return $menu_order;
}
?>