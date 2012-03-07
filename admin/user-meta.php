<?php
/**
 * This file handles the insertion of Genesis-specific user meta
 * information, including what features a user has access to,
 * and the SEO information for that user's post archive.
 *
 * @package Genesis
 */

add_action( 'show_user_profile', 'genesis_user_options_fields' );
add_action( 'edit_user_profile', 'genesis_user_options_fields' );
/**
 * This function adds new form elements to the user edit screen.
 *
 * @since 1.4
 */
function genesis_user_options_fields( $user ) {

	if ( !current_user_can( 'edit_users', $user->ID ) )
		return false;

	?>

	<h3><?php _e('Genesis User Settings', 'genesis'); ?></h3>
	<table class="form-table"><tbody>

		<tr>
			<th scope="row" valign="top"><label><?php _e('Genesis Admin Menus', 'genesis'); ?></label></th>
			<td>
				<label><input name="meta[genesis_admin_menu]" type="checkbox" value="1" <?php checked(1, get_the_author_meta('genesis_admin_menu', $user->ID)); ?> /> <?php _e('Enable Genesis Admin Menu?', 'genesis'); ?></label><br />
				<label><input name="meta[genesis_seo_settings_menu]" type="checkbox" value="1" <?php checked(1, get_the_author_meta('genesis_seo_settings_menu', $user->ID)); ?> /> <?php _e('Enable SEO Settings Submenu?', 'genesis'); ?></label><br />
				<label><input name="meta[genesis_import_export_menu]" type="checkbox" value="1" <?php checked(1, get_the_author_meta('genesis_import_export_menu', $user->ID)); ?> /> <?php _e('Enable Import/Export Submenu?', 'genesis'); ?></label>
			</td>
		</tr>

		<tr>
			<th scope="row" valign="top"><label><?php _e('Author Box', 'genesis'); ?></label></th>
			<td>
				<label><input name="meta[genesis_author_box_single]" type="checkbox" value="1" <?php checked(1, get_the_author_meta('genesis_author_box_single', $user->ID)); ?> /> <?php _e('Enable Author Box on this User\'s Posts?', 'genesis'); ?></label><br />
				<label><input name="meta[genesis_author_box_archive]" type="checkbox" value="1" <?php checked(1, get_the_author_meta('genesis_author_box_archive', $user->ID)); ?> /> <?php _e('Enable Author Box on this User\'s Archives?', 'genesis'); ?></label>
			</td>
		</tr>

		</tbody></table>

<?php }

add_action( 'show_user_profile', 'genesis_user_archive_fields' );
add_action( 'edit_user_profile', 'genesis_user_archive_fields' );
/**
 * This function adds new form elements to the user edit screen that
 * allow the user to define their own headline and intro text.
 *
 * @since 1.6
 */
function genesis_user_archive_fields( $user ) {

	if ( ! current_user_can( 'edit_users', $user->ID ) )
		return false;

	?>

		<h3><?php _e('Genesis Author Archive Settings', 'genesis'); ?></h3>
		<p><span class="description"><?php _e('These settings apply to this author\'s archive pages.', 'genesis'); ?></span></p>
		<table class="form-table"><tbody>

		<tr>
			<th scope="row" valign="top"><label for="headline"><?php _e( 'Custom Archive Headline', 'genesis' ); ?></label></th>
			<td><input name="meta[headline]" id="headline" type="text" value="<?php echo esc_attr( get_the_author_meta('headline', $user->ID) ); ?>" class="regular-text" /><br />
			<span class="description"><?php printf( __('Will display in the %s tag at the top of the first page', 'genesis'), '<code>&lt;h1&gt;&lt;/h1&gt;</code>' ); ?></span></td>
		</tr>

		<tr>
			<th scope="row" valign="top"><label for="intro_text"><?php _e( 'Custom Description Text', 'genesis' ); ?></label></th>
			<td><textarea name="meta[intro_text]" id="intro_text" rows="5" cols="30"><?php echo esc_textarea( get_the_author_meta('intro_text', $user->ID) ); ?></textarea><br />
			<span class="description"><?php _e('This text will be the first paragraph, and display on the first page', 'genesis'); ?></span></td>
		</tr>

		</tbody></table>

<?php }



add_action( 'show_user_profile', 'genesis_user_seo_fields' );
add_action( 'edit_user_profile', 'genesis_user_seo_fields' );
/**
 * This function adds new form elements to the user edit screen
 * to control the SEO on the author archive.
 *
 * @since 1.4
 */
function genesis_user_seo_fields( $user ) {

	if ( !current_user_can( 'edit_users', $user->ID ) )
		return false;

	?>

		<h3><?php _e('Genesis SEO Settings', 'genesis'); ?></h3>
		<p><span class="description"><?php _e('These settings apply to this author\'s archive pages.', 'genesis'); ?></span></p>
		<table class="form-table"><tbody>

		<tr>
			<th scope="row" valign="top"><label for="doctitle"><?php printf( __('Custom Document %s', 'genesis'), '<code>&lt;title&gt;</code>' ); ?></label></th>
			<td><input name="meta[doctitle]" id="doctitle" type="text" value="<?php echo esc_attr( get_the_author_meta('doctitle', $user->ID) ); ?>" class="regular-text" /></td>
		</tr>

		<tr>
			<th scope="row" valign="top"><label for="meta-description"><?php printf( __('%s Description', 'genesis'), '<code>META</code>' ); ?></label></th>
			<td><textarea name="meta[meta_description]" id="meta-description" rows="5" cols="30"><?php echo esc_textarea( get_the_author_meta('meta_description', $user->ID) ); ?></textarea></td>
		</tr>

		<tr>
			<th scope="row" valign="top"><label for="meta-keywords"><?php printf( __('%s Keywords', 'genesis'), '<code>META</code>' ); ?></label></th>
			<td><input name="meta[meta_keywords]" id="meta-keywords" type="text" value="<?php echo esc_attr( get_the_author_meta('meta_keywords', $user->ID) ); ?>" class="regular-text" /><br />
			<span class="description"><?php _e('Comma separated list', 'genesis'); ?></span></td>
		</tr>

		<tr>
			<th scope="row" valign="top"><label><?php _e('Robots Meta', 'genesis'); ?></label></th>
			<td>
				<label><input name="meta[noindex]" id="noindex" type="checkbox" value="1" <?php checked(1, get_the_author_meta('noindex', $user->ID)); ?> /> <?php printf( __('Apply %s to this archive?', 'genesis'), '<code>noindex</code>' ); ?></label><br />
				<label><input name="meta[nofollow]" id="nofollow" type="checkbox" value="1" <?php checked(1, get_the_author_meta('nofollow', $user->ID)); ?> /> <?php printf( __('Apply %s to this archive?', 'genesis'), '<code>nofollow</code>' ); ?></label><br />
				<label><input name="meta[noarchive]" id="noarchive" type="checkbox" value="1" <?php checked(1, get_the_author_meta('noarchive', $user->ID)); ?> /> <?php printf( __('Apply %s to this archive?', 'genesis'), '<code>noarchive</code>' ); ?></label>
			</td>
		</tr>

		</tbody></table>

<?php }


add_action( 'show_user_profile', 'genesis_user_layout_fields' );
add_action( 'edit_user_profile', 'genesis_user_layout_fields' );
/**
 * This function adds new layout form elements to the user edit screen
 * to allow the user to define a layout for an author's archive.
 *
 * @since 1.4
 */
function genesis_user_layout_fields( $user ) {

	if ( !current_user_can( 'edit_users', $user->ID ) )
		return false;

	$layout = get_the_author_meta( 'layout', $user->ID );
	$layout = $layout ? $layout : '';

	?>

	<h3><?php _e('Genesis Layout Settings', 'genesis'); ?></h3>
	<p><span class="description"><?php _e('These settings apply to this author\'s archive pages.', 'genesis'); ?></span></p>
	<table class="form-table"><tbody>

	<tr>
		<th scope="row" valign="top"><label><?php _e('Choose Layout', 'genesis'); ?></label></th>
		<td>
			<div class="genesis-layout-selector">
				<p><input type="radio" name="meta[layout]" id="default-layout" value="" <?php checked('', $layout); ?> /> <label class="default" for="default-layout"><?php printf( __('Default Layout set in <a href="%s">Theme Settings</a>', 'genesis'), menu_page_url( 'genesis', 0 ) ); ?></label></p>

				<p><?php genesis_layout_selector( array( 'name' => 'meta[layout]', 'selected' => $layout ) ); ?></p>
			</div>
		</td>
	</tr>

	</tbody></table>

<?php }


add_action( 'personal_options_update', 'genesis_user_meta_save' );
add_action( 'edit_user_profile_update', 'genesis_user_meta_save' );
/**
 * This function stores/updates user meta when page is saved.
 *
 * @since 1.4
 */
function genesis_user_meta_save( $user_id ) {

	if ( !current_user_can( 'edit_users', $user_id ) )
		return;

	if ( !isset( $_POST['meta'] ) || !is_array( $_POST['meta'] ) )
		return;

	$meta = wp_parse_args( $_POST['meta'], array(
		'genesis_admin_menu' => '',
		'genesis_seo_settings_menu' => '',
		'genesis_import_export_menu' => '',
		'genesis_author_box_single' => '',
		'genesis_author_box_archive' => '',
		'headline' => '',
		'intro_text' => '',
		'doctitle' => '',
		'meta_description' => '',
		'meta_keywords' => '',
		'noindex' => '',
		'nofollow' => '',
		'noarchive' => '',
		'layout' => ''
	) );

	foreach ( $meta as $key => $value ) {
		update_user_meta( $user_id, $key, $value );
	}

}


/**
 * This filter function checks to see if user data has actually been saved,
 * or if defaults need to be forced. This filter is useful for user options
 * that need to be "on" by default, but keeps us from having to push defaults
 * into the database, which would be a very expensive task.
 *
 * Yes, this function is hacky. I did the best I could.
 *
 * @since 1.4
 * @author Nathan Rice
 */
function genesis_user_meta_default_on( $value, $user_id ) {

	$field = str_replace( 'get_the_author_', '', current_filter() );

	// if a real value exists, simply return it.
	if ( $value ) return $value;

	// setup user data
	if ( !$user_id )
		global $authordata;
	else
		$authordata = get_userdata( $user_id );

	// just in case
	$user_field = "user_$field";
	if ( isset( $authordata->$user_field ) )
		return $authordata->user_field;

	// if an empty or false value exists, return it
	if ( isset( $authordata->$field ) )
		return $value;

	// if all that fails, default to true
	return 1;

}

add_filter( 'get_the_author_genesis_admin_menu', 'genesis_user_meta_default_on', 10, 2 );
add_filter( 'get_the_author_genesis_seo_settings_menu', 'genesis_user_meta_default_on', 10, 2 );
add_filter( 'get_the_author_genesis_import_export_menu', 'genesis_user_meta_default_on', 10, 2 );

add_filter( 'get_the_author_genesis_author_box_single', 'genesis_author_box_single_default_on', 10, 2 );
/**
 * This is a special filter function to be used to conditionally force
 * a default 1 value for each users' author box setting.
 *
 * @since 1.4
 */
function genesis_author_box_single_default_on( $value, $user_id ) {

	if ( genesis_get_option('author_box_single') )
		return genesis_user_meta_default_on( $value, $user_id );
	else
		return $value;

}