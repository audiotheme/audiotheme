<?php
/**
 * Admin Inclusions
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'admin/functions.php' );
require( AUDIOTHEME_DIR . 'admin/options.php' );

/**
 * Admin Setup
 *
 * @since 1.0.0
 */
add_action( 'init', 'audiotheme_admin_setup' );

function audiotheme_admin_setup() {
	AudioTheme_Options::setup();
	
	add_action( 'save_post', 'audiotheme_video_save' );
	add_action( 'wp_ajax_audiotheme_get_video_data', 'audiotheme_get_video_data' );
	
	add_action( 'admin_init', 'audiotheme_register_directory_browsing_setting' );
	add_action( 'update_option_audiotheme_disable_directory_browsing', 'audiotheme_disable_directory_browsing_option_update', 10, 2 );
	
	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'admin_body_class', 'audiotheme_admin_body_class' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );
	
	add_action( 'manage_pages_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	
	add_filter( 'custom_menu_order', '__return_true' );
	add_filter( 'menu_order', 'audiotheme_admin_menu_order', 999 );
	
	if ( current_theme_supports( 'audiotheme-options' ) ) {
		$options = AudioTheme_Options::get_instance();
		$panel = $options->add_panel( 'theme-options', __( 'Theme Options', 'audiotheme-i18n' ), array(
			'menu_title' => __( 'Theme Options', 'audiotheme-i18n' ),
			'option_group' => 'audiotheme_options',
			'option_name' => array( 'audiotheme_options' ),
			'show_in_menu' => 'themes.php'
		) );
		
		//add_action( 'update_option_audiotheme_options', 'audiotheme_options_update', 10, 2 );
		//add_action( 'admin_init', 'audiotheme_default_options' );
	}
	
	if ( current_theme_supports( 'audiotheme-automatic-updates' ) ) {
		include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-upgrader.php' );
		$support = get_theme_support( 'audiotheme-automatic-updates' );
		new AudioTheme_Upgrader( $support[0] );
	}
}

function audiotheme_register_directory_browsing_setting() {
	register_setting( 'privacy', 'audiotheme_disable_directory_browsing' );
	
	add_settings_field(
		'audiotheme_disable_directory_browsing',
		'<label for="audiotheme-disable-directory-browsing">' . __( 'Directory Browsing', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_disable_directory_browsing_setting_field',
		'privacy',
		'default'
	);
}

function audiotheme_disable_directory_browsing_setting_field() {
	$disable_browsing = get_option( 'audiotheme_disable_directory_browsing' );
	?>
	<input type="checkbox" name="audiotheme_disable_directory_browsing" id="audiotheme-disable-directory-browsing" value="1"<?php checked( $disable_browsing, true ); ?>>
	<label for="audiotheme-disable-directory-browsing"><?php _e( 'Disable directory browsing?', 'audiotheme-i18n' ); ?></label>
	<?php
}

function audiotheme_disable_directory_browsing_option_update( $oldvalue, $newvalue ) {
	audiotheme_save_htaccess();
}

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
	
	add_meta_box( 'add-audiotheme-archive-links', __( 'AudioTheme Pages', 'audiotheme-i18n' ), 'audiotheme_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'default' );
}

function audiotheme_nav_menu_item_link_meta_box( $object, $post_type ) {
	global $_nav_menu_placeholder, $nav_menu_selected_id;
	
	$post_type_name = 'audiotheme_archive_pages';

	$db_fields = false;
	$walker = new Walker_Nav_Menu_Checklist( $db_fields );

	$current_tab = 'all';
	if ( isset( $_REQUEST[ $post_type_name . '-tab' ] ) && in_array( $_REQUEST[ $post_type_name . '-tab' ], array( 'all', 'search' ) ) ) {
		$current_tab = $_REQUEST[ $post_type_name . '-tab' ];
	}

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);
	?>
	<div id="posttype-<?php echo $post_type_name; ?>" class="posttypediv">
		<ul id="posttype-<?php echo $post_type_name; ?>-tabs" class="posttype-tabs add-menu-item-tabs">
			<li class="tabs"><a class="nav-tab-link" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg( $post_type_name . '-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#<?php echo $post_type_name; ?>-all"><?php _e('View All'); ?></a></li>
		</ul>

		<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
			<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name?> categorychecklist form-no-clear">
				<?php
				$args['walker'] = $walker;
				
				$posts = apply_filters( 'nav_menu_items_' . $post_type_name, array(), $args, $post_type );
				$posts = sort_objects( $posts, 'post_title', 'asc', false );
				
				$checkbox_items = walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $posts ), 0, (object) $args );
				if ( 'all' == $current_tab && ! empty( $_REQUEST['selectall'] ) ) {
					$checkbox_items = preg_replace( '/(type=(.)checkbox(\2))/', '$1 checked=$2checked$2', $checkbox_items );
				}

				echo $checkbox_items;
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
					echo esc_url( add_query_arg(
						array(
							$post_type_name . '-tab' => 'all',
							'selectall' => 1,
						),
						remove_query_arg( $removed_args )
					));
				?>#posttype-<?php echo $post_type_name; ?>" class="select-all"><?php _e( 'Select All' ); ?></a>
			</span>

			<span class="add-to-menu">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-<?php echo $post_type_name; ?>">
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}

/**
 * Enqueue Admin Scripts
 *
 * @since 1.0.0
 */
function audiotheme_admin_body_class( $class ) {
	return ' ' . sanitize_html_class( get_current_screen()->id );
}

/**
 * Custom Post Type Columns
 *
 * @since 1.0.0
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {
	global $post;
	
	switch ( $column_name ) {
		case 'image' :
			printf( '<a href="%1$s" title="%2$s">%3$s</a>', 
				esc_url( get_edit_post_link( $post_id ) ),
				esc_attr( $post->post_title ),
				get_the_post_thumbnail( $post->ID, array( 60, 60 ), array( 'title' => trim( strip_tags(  $post->post_title ) ) ) )
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
	// Remove contact options
	unset( $contactmethods['aim'] );
	unset( $contactmethods['yim'] );
	unset( $contactmethods['jabber'] );
	
	// Add Contact Options
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme-i18n' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme-i18n' );
	
	return $contactmethods;
}

/**
 * Re-order the admin menu
 *
 * Positions AudioTheme admin menu items after Posts menu item if the
 * Gigs menu item hasn't been modified. Should place nice with plugins.
 *
 * @since 1.0.0
 */
function audiotheme_admin_menu_order( $menu_order ) {
	global $menu;
	
	$start_key = array_search( 'edit.php', $menu_order );
	
	// only try to re-order the menu items if the gigs menu hasn't been moved
	if ( false !== $start_key && array_key_exists( 512, $menu ) && 'gigs' == $menu[512][2] ) {
		$audiotheme_admin_menu_order = array( 'gigs', 'edit.php?post_type=audiotheme_record', 'edit.php?post_type=audiotheme_video', 'edit.php?post_type=audiotheme_gallery' );
		
		foreach ( $audiotheme_admin_menu_order as $i => $item ) {
			$menu_key = array_search( $item, $menu_order );
			if ( $menu_key ) {
				$new_position = $start_key + $i + 1;
				array_splice( $menu_order, $new_position, 0, $menu_order[ $menu_key ] ); // insert the item in it's new location
				unset( $menu_order[ $menu_key + 1 ] ); // remove the old menu item
				$menu_order = array_values( $menu_order ); // re-key the array
			}
		}
	}
	
	return $menu_order;
}
?>