<?php
/**
 * API methods and hooks for working with and displaying AudioTheme settings
 * screens.
 *
 * Theme Options support is added in 'after_setup_theme' using
 * add_theme_support().
 *
 * The AudioTheme Settings API is loaded on 'init'. It fires a custom action
 * called 'audiotheme_register_settings', which is where any settings should
 * be registered to ensure they're available to the Theme Customizer and the
 * WordPress Settings API.
 *
 * The 'customizer_register' action is fired during 'wp_loaded', which occurs
 * right after 'init'. Theme Customizer settings are registered here.
 *
 * Settings screens menu items are added during 'admin_menu'.
 *
 * Finally, settings are registered with the WordPress Settings API during
 * 'admin_init'.
 *
 * @package AudioTheme\Settings
 * @since 1.0.0
 * @deprecated 1.9.0
 */

/*
 * Basic API functions for interfacing with the Audiotheme_Settings object.
 */

/**
 * Get the settings object instance.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return Audiotheme_Settings The main settings object.
 */
function get_audiotheme_settings() {
	return Audiotheme_Settings::instance();
}

/**
 * Add a settings screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $screen_id A screen identifier.
 * @param string $title The screen name. Also used as the first tab.
 * @param array $args Additional overrides for customizing the screen behavior.
 * @return Audiotheme_Settings The main settings object.
 */
function add_audiotheme_settings_screen( $screen_id, $title, $args = array() ) {
	$settings = Audiotheme_Settings::instance();

	$settings->add_screen( $screen_id, $title, $args );

	return $settings;
}

/**
 * Get a settings screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param string $screen_id The screen id. Defaults to 'audiotheme-theme-options'.
 * @return Audiotheme_Settings The main settings object.
 */
function get_audiotheme_settings_screen( $screen_id = 'audiotheme-theme-options' ) {
	$settings = Audiotheme_Settings::instance();

	if ( $screen_id ) {
		$settings->set_screen( $screen_id );
	}

	return $settings;
}

/*
 * Hooks.
 */

/**
 * Initialize the settings object and related hooks, and add a Theme Options
 * screen if the current theme supports it.
 *
 * Hooked on 'init' in audiotheme_admin_setup().
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_init() {
	// Add theme options support.
	if ( ( $support = get_audiotheme_theme_options_support() ) && ! empty( $support['callback'] ) && function_exists( $support['callback'] ) ) {
		$settings = get_audiotheme_settings();

		$screen = add_audiotheme_settings_screen( 'audiotheme-theme-options', __( 'Theme Options', 'audiotheme' ), array(
			'menu_title'   => $support['menu_title'],
			'option_group' => 'audiotheme_theme_mods',
			'option_name'  => $support['option_name'],
			'show_in_menu' => 'themes.php',
			'capability'   => 'edit_theme_options',
		) );

		// Registering the callback like this ensures that an error isn't thrown if the framework isn't active.
		add_action( 'audiotheme_register_settings', $support['callback'] );
	}

	// These must occur after the callback to register settings.
	add_action( 'customize_register', 'audiotheme_settings_register_customizer_settings' );

	// Lower priority allows screens to be registered in the 'admin_menu' hook and still have the menu item display.
	add_action( 'admin_menu', 'audiotheme_settings_add_admin_menus', 20 );
	add_action( 'network_admin_menu', 'audiotheme_settings_add_admin_menus', 20 );

	// Settings should be registered before this.
	add_action( 'admin_init', 'audiotheme_settings_register_wp_settings_api', 20 );
	add_action( 'admin_init', 'audiotheme_settings_save_network_options' );

	// Custom settings should be registered during this hook.
	do_action( 'audiotheme_register_settings' );
}

/**
 * Fire an action when a network settings screen is saved.
 *
 * Plugins need to manually save each registered options. Check the nonce in
 * $_POST['_wpnonce'] to be sure the action is '{$option_group}-options'.
 *
 * Don't call wp_die() or exit() since all network settings screens will use
 * the same action.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_save_network_options() {
	if ( ! is_network_admin() || empty( $_GET['action'] ) || 'audiotheme-save-network-settings' !== $_GET['action'] ) {
		return;
	}

	do_action( 'audiotheme_settings_save_network_options' );
}

/**
 * Register Theme Customizer settings.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_register_customizer_settings( $wp_customize ) {
	// Include custom Theme Customizer controls.
	require( AUDIOTHEME_DIR . 'admin/deprecated/settings-theme-customizer-controls.php' );

	do_action( 'audiotheme_settings_before_customizer' );

	$settings = Audiotheme_Settings::instance();
	$settings->register_customizer_settings( $wp_customize );
}

/**
 * Register setting screens and menu items.
 *
 * Adds a menu item for any settings screens that support them. Registers
 * settings before sections and fields are added. Adds a sanitization
 * callback to process any sanitization routines that have been registered
 * with a setting.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @todo https://make.wordpress.org/themes/2011/07/01/wordpress-3-2-fixing-the-edit_theme_optionsmanage_options-bug/
 */
function audiotheme_settings_add_admin_menus() {
	$settings = Audiotheme_Settings::instance();

	if ( $screens = $settings->get_screens() ) {
		foreach ( $screens as $screen ) {
			if ( false !== $screen->show_in_menu && $settings->screen_has_settings( $screen->screen_id ) ) {
				if ( true === $screen->show_in_menu || ! is_string( $screen->show_in_menu ) ) {
					$pagehook = add_menu_page( $screen->name, $screen->menu_title, $screen->capability, $screen->menu_slug, 'audiotheme_settings_display_screen' );
				} else {
					$pagehook = add_submenu_page( $screen->show_in_menu, $screen->name, $screen->menu_title, $screen->capability, $screen->menu_slug, 'audiotheme_settings_display_screen' );
				}

				add_action( 'load-' . $pagehook, 'audiotheme_settings_screen_load' );
				add_action( 'admin_notices', 'audiotheme_settings_screen_notices' );

				$option_names = (array) $screen->option_name;
				foreach ( $option_names as $name ) {
					register_setting( $screen->option_group, $name );
					add_filter( 'sanitize_option_' . $name, 'audiotheme_settings_sanitize_option', 10, 2 );
				}

				#add_filter( 'option_page_capability_' . $screen->option_group, 'audiotheme_settings_page_capability' );
			}
		}
	}
}

/**
 * Change the capability required for modifying a particular option.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return string
 */
function audiotheme_settings_page_capability() {
	return 'manage_options';
}

/**
 * Register sections and settings with the WordPress Settings API.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_register_wp_settings_api() {
	$settings = Audiotheme_Settings::instance();
	$settings->register_wp_settings();
}

/**
 * Enqueue thickbox functionality for selecting media files.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_screen_load() {
	wp_enqueue_media();

	add_thickbox();
	wp_enqueue_script( 'audiotheme-settings' );
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_enqueue_style( 'audiotheme-admin' );
	wp_enqueue_style( 'wp-color-picker' );
}

/**
 * Output error message.
 *
 * Outputs any error messages added when options are saved. Adds a data
 * attribute to the error message so it can be associated it with a
 * specific field and moved with javascript.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_screen_notices() {
	global $plugin_page;

	$settings = Audiotheme_Settings::instance();
	$screen = $settings->get_screen( $plugin_page );

	if ( $screen ) {
		$updated = true;
		$option_names = (array) $screen->option_name;
		foreach ( $option_names as $name ) {
			$errors = get_settings_errors( $name, false );
			if ( ! empty( $errors ) && is_array( $errors ) ) {
				foreach ( $errors as $key => $details ) {
					printf( '<div id="%1$s" class="%2$s" data-field-id="%3$s"><p><strong>%4$s</strong></p></div>',
						'audiotheme-settings-error-' . str_replace( ':', '-', $details['code'] ),
						$details['type'] . ' audiotheme-settings-error inline',
						end( explode( ':', $details['code'] ) ),
						$details['message']
					);
				}

				$updated = false;
			}
		}

		if ( $updated && isset( $_REQUEST['settings-updated'] ) )  {
			echo '<div class="updated fade"><p><strong>' . __( 'Settings saved.', 'audiotheme' ) . '</strong></p></div>';
		}
	}
}

/**
 * Render a settings screen.
 *
 * Renders the tabs and fields, including javascript, for tabbed screens and
 * attaching error messages to fields and their parent tabs.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_settings_display_screen() {
	global $plugin_page;

	$settings = Audiotheme_Settings::instance();
	$screen = $settings->get_screen( $plugin_page );

	$has_tabs = ( count( $screen->tabs ) < 2 ) ? false : true;
	?>
	<div class="wrap audiotheme-settings-screen<?php echo ( $has_tabs ) ? ' audiotheme-settings-screen-has-tabs' : ''; ?>">
		<form action="<?php echo ( is_network_admin() ) ? 'edit.php?action=audiotheme-save-network-settings' : 'options.php'; ?>" method="post">
			<?php
			screen_icon();

			// Don't add tabs if there isn't more than one registered.
			if ( ! $has_tabs ) {
				echo '<h1>' . $screen->name . '</h2>';
			} else {
				echo '<h1 class="nav-tab-wrapper">';
				foreach ( $screen->tabs as $tab_id => $tab ) {
					echo '<a href="#' . $tab_id . '-panel" class="nav-tab">' . esc_html( $tab['title'] ) . '</a>';
				}
				echo '</h1>';
			}

			// Output the nonce stuff.
			settings_fields( $screen->option_group );

			// Output the tab panels.
			foreach ( $screen->tabs as $tab_id => $tab ) {
				echo '<div class="tab-panel" id="' . $tab_id . '-panel">';
					do_action( $screen->option_group . '_' . $tab_id . '_fields_before' );

					$wp_settings_section = ( $screen->screen_id === $tab_id ) ? $screen->screen_id : $screen->screen_id . '-' . $tab_id;
					do_settings_sections( $wp_settings_section );

					do_action( $screen->option_group . '_' . $tab_id . '_fields_after' );
				echo '</div>';
			}
			?>

			<p class="submit">
				<input type="submit" value="Save Changes" class="button-primary">
			</p>
		</form>
	</div><!--end div.wrap-->
	<?php
}

/**
 * Default option sanitization callback.
 *
 * When options are registered using the AudioTheme Settings API, they'll
 * automatically be passed through this sanitization callback. The callback
 * checks to see if any sanitization or validation routines have been
 * registered for the field, and if so, calls them and adds any resulting
 * errors via the WordPress Settings API.
 *
 * If a field fails a validation routine, this function attempts to
 * revert to the old value, otherwise, it discards the new value.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param mixed $value Value to sanitize/validate.
 * @param string $option Name of the option.
 * @return mixed The sanitized value.
 */
function audiotheme_settings_sanitize_option( $value, $option ) {
	global $wp_settings_fields;

	if ( empty( $wp_settings_fields ) ) {
		return $value;
	}

	$settings = get_audiotheme_settings();
	$customizer = $settings->get_customizer_only_settings();

	foreach ( $wp_settings_fields as $sections ) {
		foreach ( $sections as $section ) {
			foreach ( $section as $field_name => $field ) {
				if ( is_array( $value ) && ! array_key_exists( $field_name, $value ) ) {
					continue;
				}

				if ( isset( $field['args']['option_name'] ) && $option === $field['args']['option_name'] && ! in_array( $field_name, $customizer ) ) {
					$value = audiotheme_settings_sanitize_field( $field, $value );

					if ( ! audiotheme_settings_validate_field( $field, $option, $value ) ) {
						// Maintain the existing value.
						$current_value = get_option( $option );
						if ( is_array( $value ) ) {
							$value[ $field_name ] = ( isset( $current_value[ $field_name ] ) ) ? $current_value[ $field_name ] : '';
						} else {
							$value = $current_value;
						}
					}
				}
			}
		}
	}

	return $value;
}

/**
 * Execute field sanitization callbacks.
 *
 * Looks for registered sanitization callbacks for a field and runs them.
 * Sanitization callbacks must return a sanitized value.
 *
 * Accepts a comma delimited string or array of function names and
 * executes them in order. If a function doesn't exist, such as a custom
 * callback, it will be skipped.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $field Settings field properties.
 * @param mixed $option_value The option value to sanitize.
 * @return mixed The sanitized value.
 */
function audiotheme_settings_sanitize_field( $field, $option_value ) {
	if ( ! empty( $field['args']['sanitize'] ) ) {
		$sanitize = $field['args']['sanitize'];
		if ( is_string( $sanitize ) ) {
			$sanitize = array_map( 'trim', explode( ',', $sanitize ) );
		}

		if ( is_array( $sanitize ) ) {
			foreach ( $sanitize as $func ) {
				if ( function_exists( $func ) ) {
					if ( is_array( $option_value ) ) {
						$option_value[ $field['id'] ] = call_user_func( $func, $option_value[ $field['id'] ] );
					} else {
						$option_value = call_user_func( $func, $option_value );
					}
				}
			}
		}
	}

	return $option_value;
}

/**
 * Execute field validation callbacks.
 *
 * Looks for registered validation callbacks for a field and runs them.
 * Validation callbacks should return true, false, or a WP_Error object.
 *
 * Accepts a comma delimited string or array of function names and
 * executes them in order. If a function doesn't exist, such as a custom
 * callback, it will be skipped.
 *
 * If an array is passed, the keys should be the validation functions and
 * the values should be error messages. If a validation callback returns a
 * WP_Error object, the error message will overload any others. If an
 * error message isn't registered, a default message will be shown.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $field Settings field properties.
 * @param mixed $option_value The option name.
 * @param mixed $option_value The option value to check for errors.
 * @return bool
 */
function audiotheme_settings_validate_field( $field, $option_name, $option_value ) {
	if ( ! empty( $field['args']['validate'] ) ) {
		$validate = $field['args']['validate'];
		if ( is_string( $validate ) ) {
			$validate = array_flip( array_map( 'trim', explode( ',', $validate ) ) );
		}

		if ( is_array( $validate ) ) {
			foreach ( $validate as $func => $error_msg ) {
				$error_msg = ( is_string( $error_msg ) ) ? $error_msg : __( 'It appears there was a problem with a value entered.', 'audiotheme' );
				if ( function_exists( $func ) ) {
					$value = ( is_array( $option_value ) ) ? $option_value[ $field['id'] ] : $option_value;
					$is_valid = call_user_func( $func, $value );

					// Used for adding data attributes to the error notice to highlight tabs and fields needing attention.
					$error_code = $field['args']['field_id'];
					if ( ! $is_valid || is_wp_error( $is_valid ) ) {
						$error_msg = ( is_wp_error( $is_valid ) ) ? $is_valid->get_error_message() : $error_msg;

						add_settings_error( $option_name, $error_code, $error_msg );

						return false; // Only show one error message per field.
					}
				}
			}
		}
	}

	return true;
}
