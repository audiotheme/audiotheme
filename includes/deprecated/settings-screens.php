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
 * @package   AudioTheme\Settings
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
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

		$screen = add_audiotheme_settings_screen( 'audiotheme-theme-options', 'Theme Options', array(
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
			echo '<div class="updated fade"><p><strong>' . 'Settings saved.' . '</strong></p></div>';
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
				$error_msg = ( is_string( $error_msg ) ) ? $error_msg : 'It appears there was a problem with a value entered.';
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

/**
 * Class to abstract and extend the WordPress Settings API and Theme
 * Customizer and provide a generic API for interacting with them.
 *
 * @package AudioTheme\Settings
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 * @link https://core.trac.wordpress.org/ticket/18285
 */
class Audiotheme_Settings {
	/**
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var Audiotheme_Settings
	 */
	private static $instance;

	/**
	 * All registered screens and their settings, including tabs and sections.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var array
	 */
	protected $screens;

	/**
	 * All registered settings.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var array
	 */
	protected $settings;

	/**
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var string
	 */
	protected $current_screen;

	/**
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var string
	 */
	protected $current_tab;

	/**
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @var string
	 */
	protected $current_section;

	/**
	 * Main Audiotheme_Settings instance.
	 *
	 * Provides access to the Audiotheme_Settings object and ensures only one
	 * instance ever exists. Accessing the object should usually be done
	 * through one of the helper functions.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor to setup intial member variables.
	 *
	 * Registers the special Theme Customizer screen object when initialized.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 * @see Audiotheme_Settings::instance();
	 */
	private function __construct() {
		$this->screens = array();
		$this->settings = array();

		$this->add_screen( 'customizer', 'customizer', array(
			'option_name'  => get_audiotheme_theme_options_name(),
			'show_in_menu' => false,
		) );

		$this->current_screen = null;
		$this->current_tab = null;
		$this->current_section = null;
	}

	/**
	 * Add a settings screen.
	 *
	 * A settings screen is a custom dashboard screen consisting of tabs and
	 * sections of settings. Many of the properties registered here are later
	 * used to add the menu items for the screens.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @todo Finish implementing additional $args.
	 *
	 * @param string $screen_id A screen identifier.
	 * @param string $title The screen name. Also used as the first tab.
	 * @param array $args Additional overrides for customizing the screen behavior.
	 * @return Audiotheme_Settings The main settings object.
	 */
	public function add_screen( $screen_id, $title, $args = array() ) {
		$default_options_id = str_replace( '-', '_', sanitize_title_with_dashes( $screen_id ) );

		$args = wp_parse_args( $args, array(
			'capability'    => 'manage_options',
			'menu_icon'     => null,
			'menu_position' => null,
			'menu_slug'     => $screen_id,
			'menu_title'    => $title,
			'option_group'  => $default_options_id,
			'option_name'   => $default_options_id,
			'screen_icon'   => null,
			'show_in_menu'  => null,
		) );

		$args['name'] = $title;
		$args['screen_id'] = $screen_id;

		// Make the option_name parameter an array so multiple option_names can be used on a single screen.
		// The first option_name registered for a screen will be used as the default.
		$args['option_name'] = (array) $args['option_name'];

		$this->screens[ $screen_id ] = (object) $args;

		// Add a default tab, which adds a default section.
		// Allows fields to be added at any level.
		$this->current_screen = $screen_id;
		$this->add_tab( $screen_id, $title );

		$this->set_screen( $screen_id );

		return $this;
	}

	/**
	 * Get a settings screen.
	 *
	 * Return the specified screen and its settings.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $screen_id The screen id.
	 * @return object $screen The screen or null if it doesn't exist.
	 */
	public function get_screen( $screen_id ) {
		return ( isset( $this->screens[ $screen_id ] ) ) ? $this->screens[ $screen_id ] : null;
	}

	/**
	 * Set the current screen.
	 *
	 * Sets the current screen so tabs, sections, and fields can be added.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $screen_id The screen id.
	 * @return Audiotheme_Settings|WP_Error The main settings object or an error if the screen doesn't exist.
	 */
	public function set_screen( $screen_id ) {
		if ( ! isset( $this->screens[ $screen_id ] ) ) {
			return new WP_Error( 'invalid_screen', sprintf( 'Invalid screen: %s.', $screen_id ) );
		}

		$this->current_screen = $screen_id;
		$this->set_tab( $screen_id );

		return $this;
	}

	/**
	 * Get all the screens.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @return array An array of all the screen objects.
	 */
	public function get_screens() {
		return ( ! empty( $this->screens ) ) ? $this->screens : null;
	}

	/**
	 * Check if a screen has any registered settings.
	 *
	 * Should only be called after 'init'.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $screen_id The screen id.
	 * @return bool
	 */
	public function screen_has_settings( $screen_id ) {
		$settings = wp_list_filter( $this->settings, array( 'screen' => $screen_id ) );

		return ( empty( $settings ) ) ? false : true;
	}

	/**
	 * Add a tab to a settings screen.
	 *
	 * Sets the current tab if the tab already exists on the current screen.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $tab_id A tab identifier.
	 * @param string $title The tab title.
	 * @param array $args Optional. Custom arguments for changing the tab's behavior.
	 * @return Audiotheme_Settings The main settings object.
	 */
	public function add_tab( $tab_id, $title, $args = array() ) {
		$screen = $this->get_current_screen();

		// Add the tab if it doesn't exist on the current screen.
		if ( ! isset( $screen->tabs[ $tab_id ] ) ) {
			$args = wp_parse_args( $args, array(
				'priority' => 20,
			) );

			$tab = wp_parse_args( array(
				'title'    => $title,
				'sections' => array(),
			), $args );

			$tab['priority'] = ( $tab_id === $screen->screen_id ) ? -1 : absint( $tab['priority'] );

			$screen->tabs[ $tab_id ] = $tab;

			$this->current_tab = $tab_id;
			$this->add_section( '_default', '' );
		}

		$this->set_tab( $tab_id );

		return $this;
	}

	/**
	 * Sets the current tab.
	 *
	 * Will also set the current section to '_default' if not on the Theme
	 * Customizer screen, otherwise accounts for the Theme Customizer not
	 * having tabs.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $tab_id The tab id.
	 * @return Audiotheme_Settings|WP_Error The main settings object or an error if the tab doesn't exist.
	 */
	public function set_tab( $tab_id ) {
		$screen = $this->get_current_screen();

		if ( 'customizer' === $screen->screen_id ) {
			$this->current_tab = 'customizer';
		} elseif ( isset( $screen->tabs[ $tab_id ] ) ) {
			$this->current_tab = $tab_id;
			$this->set_section( '_default' ); // Reset the current section.
		} else {
			return new WP_Error( 'invalid_screen_tab', sprintf( 'Invalid screen tab: %s.', $tab_id ) );
		}

		return $this;
	}

	/**
	 * Add a settings section to a tab.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $section_id A section identifier.
	 * @param string $title The section title.
	 * @param array $args Optional. Array of arguments to change the section behavior.
	 * @return Audiotheme_Settings The main settings object.
	 */
	public function add_section( $section_id, $title = null, $args = array() ) {
		$screen = $this->get_current_screen();
		$tab_id = $this->get_current_tab_id();

		if ( ! isset( $screen->tabs[ $tab_id ]['sections'][ $section_id ] ) ) {
			$section = wp_parse_args( $args, array(
				'priority'            => 30,

				// Settings screen arguments.
				'callback'            => '__return_false', // To display a description on a settings screen.
				'wp_settings_section' => $this->get_wp_settings_section_id( $screen->screen_id, $tab_id ),

				// Theme Customizer-specific arguments.
				'description'         => '',
				'capability'          => '',
			) );

			$section['title'] = ( 0 === strpos( $section_id, '_default' ) ) ? '' : $title;

			// Sanitize the priority.
			$section['priority'] = ( '_default' === $section_id ) ? -1 : absint( $section['priority'] );

			$screen->tabs[ $tab_id ]['sections'][ $section_id ] = $section;
		}

		$this->set_section( $section_id );

		return $this;
	}

	/**
	 * Sets the current section.
	 *
	 * If the section doesn't exist, will set it as '_default', unless the
	 * current screen is the Theme Customizer, then no sanitization is done.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $section_id The section id.
	 * @return Audiotheme_Settings The main settings object.
	 */
	public function set_section( $section_id ) {
		$screen = $this->get_current_screen();
		$tab_id = $this->get_current_tab_id();

		if ( 'customizer' === $screen->screen_id ) {
			$this->current_section = $section_id;
		} elseif ( ! isset( $screen->tabs[ $tab_id ]['sections'][ $section_id ] ) ) {
			$section_id = '_default';
		}

		$this->current_section = $section_id;

		return $this;
	}

	/**
	 * Add a field.
	 *
	 * If the field $id and 'option_name' argument are equal, the option will
	 * be stored as a string in the database. The 'option_name' must be
	 * registered when the screen is added.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $id Field id.
	 * @param string $label The field label. Should be translated.
	 * @param string $type The type of field to render.
	 * @param array $args Optional. An array of arguments to modify the field behavior.
	 * @return Audiotheme_Settings The main settings object.
	 */
	public function add_field( $id, $label, $type, $args = array() ) {
		$screen = $this->get_current_screen();
		$tab_id = $this->get_current_tab_id();
		$section_id = $this->get_current_section_id();

		// The option key the setting will be stored under in the database.
		$option_name = ( isset( $args['option_name'] ) ) ? $args['option_name'] : current( (array) $screen->option_name );

		// The key in the option array.
		$key = $id;

		// Used for the name attribute in the WP Settings API, to generate the field id attribute, and as the $id for customizer settings.
		$id = ( $key === $option_name ) ? $key : $option_name . '[' . $id . ']';

		// Determine the default callback for rendering the field.
		$field_types = array( 'checkbox', 'color', 'html', 'image', 'radio', 'select', 'text', 'textarea' );
		if ( in_array( $type, $field_types ) ) {
			$default_field_callback = array( $this, 'render_' . $type . '_field' );
		} else {
			$default_field_callback = sanitize_key( $type );
		}

		// These can be overridden in the $args parameter.
		$args = wp_parse_args( $args, array(
			'field_id'       => sanitize_key( $id ),
			'label_for'      => sanitize_key( $id ),
			'default'        => '',
			'description'    => '',
			'priority'       => 20,
			'field_callback' => $default_field_callback, // The callback to render the field on a settings screen.
			'class'          => '',
			'customizer'     => false,
			// choices          => array(),
			// sanitize         => '',
			// validate         => '',
		) );

		// Sanitize and reset some args.
		$args['priority'] = absint( $args['priority'] );

		if ( 'checkbox' === $type || 'radio' === $type ) {
			$args['label_for'] = '';
		}

		// Generated setting properties.
		$setting = array(
			'screen'                  => $screen->screen_id,
			'tab'                     => $tab_id,
			'section'                 => $section_id,  // If this is passed, it will be for the settings screen, unless the current screen is the customizer.
			'type'                    => $type,

			'option_name'             => $option_name,
			'key'                     => $key,         // The option array key. Used in the WordPress Settings API.
			'id'                      => $id,          // The $id argument in the customizer.
			'label'                   => $label,

			'field_name'              => $id,
			'show_on_settings_screen' => true,
		);

		// Set up Settings API specific arguments.
		if ( 'customizer' !== $screen->screen_id ) {
			$setting['wp_settings_section'] = $this->get_wp_settings_section_id( $screen->screen_id, $tab_id );
		}

		// Set up customizer specific arguments.
		if ( 'customizer' === $screen->screen_id || ( false !== $args['customizer'] ) ) {
			$setting['show_in_customizer'] = true;

			if ( 'customizer' === $screen->screen_id ) {
				$setting['show_on_settings_screen'] = false;
			}

			$args['customizer'] = ( isset( $args['customizer'] ) && is_array( $args['customizer'] ) ) ? $args['customizer'] : array();

			$customizer = wp_parse_args( $args['customizer'], array(
				'control'   => $type,
				'transport' => 'refresh', // @see WP_Customize_Control->type
				'type'      => 'option',  // @see WP_Customize_Setting->type

				// 'section'     => '',   // Will override the settings section.
				// 'priority'    => 10,   // Overrides the priority passed as an $arg.
				// 'capability'  => 'edit_theme_options',
			) );

			// The Theme Customizer arguments will be merged with the top level setting arguments later. These keys should not be overridden.
			$customizer = array_diff_key( $customizer, array_flip( array( 'id', 'key', 'option_name' ) ) );

			$setting['customizer'] = $customizer;
			unset( $args['customizer'] );
		}

		$setting = wp_parse_args( $setting, $args );
		$this->settings[] = $setting;

		return $this;
	}

	/**
	 * Callback to render a checkbox field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_checkbox_field( $args ) {
		extract( $args );

		// Bail if a choice hasn't been set.
		if ( ! isset( $choices ) || ! is_array( $choices ) || empty( $choices ) ) {
			return;
		}

		// Just use the first choice for rendering until a list is properly implemented.
		$choices = array_slice( $choices, 0, 1, true );
		$value = get_audiotheme_option( $option_name, $key, $default );

		$class = $this->get_field_class( 'audiotheme-settings-checkbox', $args );
		echo '<div class="audiotheme-settings-checkbox ' . $class . '">';
			$i = 0;
		foreach ( $choices as $val => $label ) {
			$choice_class = 'audiotheme-settings-checkbox-choice audiotheme-settings-checkbox-choice' . $i;

			printf( '<label for="%1$s"><input type="checkbox" name="%2$s" id="%1$s" value="%3$s"%4$s class="%5$s"> %6$s</label>',
				esc_attr( $field_id ),
				esc_attr( $field_name ),
				esc_attr( $val ),
				checked( $val, $value, false ),
				$choice_class,
				( isset( $field_label ) ) ? esc_html( $field_label ) : esc_html( $label )
			);

			$i ++;
		}
		echo '</div>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a color field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_color_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );

		printf( '<input type="text" name="%1$s" id="%2$s" value="%3$s" data-default-color="%3$s" class="%4$s">',
			esc_attr( $field_name ),
			esc_attr( $field_id ),
			esc_attr( $value ),
			$this->get_field_class( 'audiotheme-settings-color', $args, '' )
		);

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to output HTML.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_html_field( $args ) {
		echo ( isset( $args['output'] ) ) ? $args['output'] : '';
	}

	/**
	 * Callback to render an image field.
	 *
	 * Defaults to using thickbox for selecting an image URL.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @todo Allow for overriding the various labels.
	 * @todo Add support for the WordPress 3.5 media manager.
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_image_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );

		$controls = array( 'media_frame', 'thickbox' ); // Whitelist the allowed image controls.
		$control = ( ! isset( $control ) || ! in_array( $control ) ) ? 'thickbox' : $control;

		if ( 'thickbox' === $control ) {
			printf( '<input type="text" name="%s" id="%s" value="%s" class="%s">',
				esc_attr( $field_name ),
				esc_attr( $field_id ),
				esc_attr( $value ),
				$this->get_field_class( 'audiotheme-settings-image', $args, 'regular-text' )
			);

			$tb_args = array( 'post_id' => 0, 'type' => 'image', 'TB_iframe' => true, 'width' => 640, 'height' => 750 );
			$tb_url = add_query_arg( $tb_args, admin_url( 'media-upload.php' ) );

			printf( '<a href="%s" title="%s" class="button thickbox" data-insert-field="%s" data-insert-button-text="%s">%s</a>',
				esc_url( $tb_url ),
				esc_attr( 'Choose an Image' ),
				esc_attr( $field_id ),
				esc_attr( 'Use This Image' ),
				esc_attr( 'Choose Image' )
			);
		}

		/*
		if ( 'media_frame' == $control ) {
			$has_image = ( empty( $value ) ) ? '' : ' has-image';
			echo '<span class="' . $this->get_field_class( 'audiotheme-media-control', $args ) . $has_image . '"';
				echo 'data-title="' . 'Choose an Image' . '"';
				echo 'data-update-text="' . 'Update Image' . '"';
			echo '>';

			printf( '<input type="text" name="%s" id="%s" value="%s" class="audiotheme-media-control-target regular-text">',
				esc_attr( $field_name ),
				esc_attr( $field_id ),
				esc_attr( $value )
			);

			echo '<a class="button audiotheme-media-control-choose">' . 'Choose Image' . '</a>';

			echo '</span>';
		}
		*/

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a radio list field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_radio_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );
		$choices = ( isset( $choices ) && is_array( $choices ) ) ? $choices : array( '' => '' );

		$class = $this->get_field_class( 'audiotheme-settings-radio', $args );
		echo '<div class="' . $class . '">';
			$i = 0;
		foreach ( $choices as $val => $label ) {
			$choice_class = 'audiotheme-settings-radio-choice audiotheme-settings-radio-choice' . $i;

			printf( '<label><input type="radio" name="%s" id="%s" value="%s"%s class="%s"> %s</label><br>',
				esc_attr( $field_name ),
				esc_attr( $field_id . $i ),
				esc_attr( $val ),
				checked( $val, $value, false ),
				$choice_class,
				esc_html( $label )
			);

			$i ++;
		}
		echo '</div>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a select field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_select_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );
		$choices = ( isset( $choices ) && is_array( $choices ) ) ? $choices : array( '' => '' );

		$class = $this->get_field_class( 'audiotheme-settings-select', $args );
		echo '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" class="' . $class . '">';
		foreach ( $choices as $val => $label ) {
			printf( '<option value="%s"%s>%s</option>',
				esc_attr( $val ),
				selected( $value, $val, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a text field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_text_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );

		printf( '<input type="text" name="%s" id="%s" value="%s" class="%s">',
			esc_attr( $field_name ),
			esc_attr( $field_id ),
			esc_attr( $value ),
			$this->get_field_class( 'audiotheme-settings-text', $args, 'regular-text' )
		);

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a textarea field.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_textarea_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );

		printf( '<textarea name="%s" id="%s" rows="%d" class="%s">%s</textarea>',
			esc_attr( $field_name ),
			esc_attr( $field_id ),
			( isset( $rows ) ) ? $rows : 4,
			$this->get_field_class( 'audiotheme-settings-textarea', $args, 'large-text' ),
			esc_textarea( $value )
		);

		echo $this->get_field_description( $args );
	}

	/**
	 * Callback to render a hidden field to store a Theme Customizer setting.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @todo Figure out if this will work for non-scalar values.
	 *
	 * @param array $args Setting field arguments.
	 */
	public function render_customizer_sync_field( $args ) {
		extract( $args );

		$value = get_audiotheme_option( $option_name, $key, $default );
		$value = maybe_serialize( $value );

		printf( '<input type="hidden" name="%s" id="%s" value="%s" class="audiotheme-settings-hidden-field">',
			esc_attr( $field_name ),
			esc_attr( $field_id ),
			esc_attr( $value )
		);

		printf( '<span class="description">%s.</span>',
			sprintf( 'Change this setting in the %s',
				sprintf( '<a href="%s">%s</a>', admin_url( 'customize.php' ), 'theme customizer' )
			)
		);
	}

	/**
	 * Determine which classes to apply to a field.
	 *
	 * The first parameter consists of classes that should always be aded. The
	 * second parameter contains the setting field's registered properties. If
	 * a class name(s) has been set, it will be used in place of any optional
	 * classes passed as the third parameter. If it hasn't been set, then the
	 * optional classes will be used instead.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array|string Array of classes or string separated by spaces.
	 * @param array $args Setting field arguments.
	 * @param array|string $optional_classes Optional. Array of classes or string separated by spaces.
	 */
	public function get_field_class( $classes, $args, $optional_classes = '' ) {
		// Split default classes into an array.
		if ( ! empty( $classes ) && ! is_array( $classes ) ) {
			$classes = preg_split( '#\s+#', $classes );
		}

		$add_classes = array();
		if ( isset( $args['class'] ) && ! empty( $args['class'] ) ) {
			$add_classes = $args['class'];
		} elseif ( ! empty( $optional_classes ) ) {
			$add_classes = $optional_classes;
		}

		if ( ! empty( $add_classes ) && ! is_array( $add_classes ) ) {
			// Split the add-on classes into an array.
			$add_classes = preg_split( '#\s+#', $add_classes );
		}

		// Merge the classes and sanitize them.
		$classes = array_map( 'sanitize_html_class', array_merge( $classes, $add_classes ) );

		return join( ' ', $classes );
	}

	/**
	 * Return the field description from a list of field arguments.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param array $args Setting field arguments.
	 * @return string Description markup.
	 */
	public function get_field_description( $args ) {
		$description = '';

		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			$description = ' <span class="audiotheme-settings-description description">' . $args['description'] . '</span>';
		}

		return $description;
	}

	/**
	 * Register the Theme Customizer sections, settings, and controls.
	 *
	 * Should be called by a hook attached to the 'customize_register' action
	 * and pass the manager object directly.
	 *
	 * @since  1.0.0
	 * @deprecated 1.9.0
	 *
	 * @see audiotheme_settings_register_customizer_settings()
	 *
	 * @todo Add sanitization support.
	 *
	 * @param  WP_Customize_Manager $manager [description]
	 */
	public function register_customizer_settings( $manager ) {
		// Register Theme Customizer sections.
		$sections = $this->screens['customizer']->tabs['customizer']['sections'];
		foreach ( $sections as $section_id => $section_args ) {
			$manager->add_section( $section_id, $section_args );
		}

		// Find Theme Customizer settings in the $settings member variable.
		$settings = wp_list_filter( $this->settings, array( 'screen' => 'customizer' ) );
		$theme_options_settings = wp_list_filter( $this->settings, array( 'screen' => 'audiotheme-theme-options', 'show_in_customizer' => true ) );
		$settings = array_merge( $settings, $theme_options_settings );

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $setting ) {
				// Replace standard args with Theme Customizer args.
				$args = wp_parse_args( $setting['customizer'], $setting );
				unset( $args['customizer'] );

				// Throw the args at the customizer and let it sort out what it needs.
				// Both of these accept a type argument.
				// The first is either 'mod' or 'option' or something custom, the second is the control.
				$manager->add_setting( $setting['id'], $args );

				// Register the control.
				$control = $this->get_customizer_control( $manager, $args );
				if ( is_string( $control ) ) {
					$args['type'] = $control;
					$manager->add_control( $setting['id'], $args );
				} elseif ( $control ) {
					$manager->add_control( $control );
				}
			}
		}
	}

	/**
	 * Register sections and fields using the WordPress Settings API.
	 *
	 * Orders tabs, sections, and fields by priority, then registers them
	 * using the WordPress Settings API. For cases where the priority is the
	 * same, the title or label property will be used for sorting.
	 *
	 * Any Theme Customizer only settings are output as hidden setting fields
	 * on the Theme Options screen so that their values aren't blanked out
	 * whenever the screen is saved.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 */
	public function register_wp_settings() {
		foreach ( $this->screens as $screen_id => $screen ) {
			if ( 'customizer' !== $screen_id && isset( $screen->tabs ) && is_array( $screen->tabs ) ) {
				// Sort the tabs by priority.
				uasort( $screen->tabs, array( $this, 'sort_by_priority' ) );

				foreach ( $screen->tabs as $tab_id => $tab ) {
					// Sort the sections by priority; falls back to title if the priority is equal.
					uasort( $tab['sections'], array( $this, 'sort_by_priority' ) );

					// Loop through and register settings sections if it has registered settings.
					foreach ( $tab['sections'] as $section_id => $section ) {
						// Find settings registered to this section on this tab on this screen.
						$settings = wp_list_filter( $this->settings, array( 'screen' => $screen_id, 'tab' => $tab_id, 'section' => $section_id ) );

						if ( $settings ) {
							add_settings_section( $section_id, $section['title'], $section['callback'], $section['wp_settings_section'] );

							// Sort the fields by priority.
							usort( $settings, array( $this, 'sort_by_priority' ) );

							// Loop through and register settings (fields).
							foreach ( $settings as $setting ) {
								// Don't pass these args to the callback.
								$unset = array_flip( array(
									'screen',
									'tab',
									'section',
									'field_callback',
									'show_in_customizer',
									'show_on_settings_screen',
									'customizer',
									'wp_settings_section',
								) );

								$args = array_diff_key( $setting, $unset );

								add_settings_field( $setting['key'], $setting['label'], $setting['field_callback'], $setting['wp_settings_section'], $setting['section'], $args );
							}
						} elseif ( 'audiotheme-theme-options' === $tab_id && '_default' === $section_id ) {
							// Make sure the default theme options section gets registered for any Theme Customizer settings to be synced.
							add_settings_section( '_default', '', '__return_false', 'audiotheme-theme-options' );
						}
					}

					// If this is the main theme options tab, output any settings that are customizer only so they don't get stomped on when the screen is saved.
					if ( 'audiotheme-theme-options' === $tab_id ) {
						$settings = wp_list_filter( $this->settings, array( 'screen' => 'customizer' ) );
						if ( $settings ) {
							foreach ( $settings as $setting ) {
								add_settings_field( $setting['key'], $setting['label'], array( $this, 'render_customizer_sync_field' ), 'audiotheme-theme-options', '_default', $setting );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Retrieve a list of keys of settings that should only show in the
	 * Theme Customizer.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @return array
	 */
	public function get_customizer_only_settings() {
		$customizer_settings = wp_list_pluck( wp_list_filter( $this->settings, array( 'screen' => 'customizer' ) ), 'key' );
		$theme_options_settings = wp_list_pluck( wp_list_filter( $this->settings, array( 'screen' => 'audiotheme-theme-options', 'show_in_customizer' => true ) ), 'key' );

		return array_diff( $customizer_settings, $theme_options_settings );
	}

	/**
	 * Get a Theme Customizer control from the setting field type.
	 *
	 * Provides an action for returning custom controls.
	 *
	 * @since  1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param WP_Customizer_Manager $manager The Theme Customizer manager object.
	 * @param array $args Setting field arguments.
	 * @return string|WP_Customizer_Control|bool A control to use in the Customizer or false if unknown.
	 */
	protected function get_customizer_control( $manager, $args ) {
		$simple_controls = array( 'checkbox', 'dropdown-pages', 'radio', 'select', 'text' );
		$custom_controls = array( 'color', 'file', 'image', 'textarea' );

		if ( in_array( $args['control'], $simple_controls ) ) {
			return $args['control'];
		} elseif ( in_array( $args['control'], $custom_controls ) ) {
			$args['settings'] = $args['id'];

			switch ( $args['control'] ) {
				case 'color' :
					$args['type'] = 'color';
					return new Wp_Customize_Color_Control( $manager, $args['key'], $args );
				case 'file' :
					$args['type'] = 'upload';
					return new WP_Customize_Upload_Control( $manager, $args['key'], $args );
				case 'image' :
					$args['type'] = 'image';
					return new WP_Customize_Image_Control( $manager, $args['key'], $args );
				case 'textarea' :
					$args['type'] = 'textarea';
				default :
					// Allow for custom customizer controls.
					return apply_filters( 'audiotheme_settings_customizer_control', false, $args['control'], $args );
			}
		}

		return false;
	}

	/**
	 * Return the currently active screen.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @return object Screen object.
	 */
	protected function get_current_screen() {
		return $this->screens[ $this->current_screen ];
	}

	/**
	 * Return the currently active tab id.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @return string Tab id.
	 */
	protected function get_current_tab_id() {
		$screen = $this->get_current_screen();

		if ( empty( $this->current_tab ) || ( isset( $screen->tabs ) && ! array_key_exists( $this->current_tab, $screen->tabs ) ) ) {
			$this->set_tab( key( (array) $screen->tabs ) ); // Get the first tab.
		} elseif ( ! isset( $screen->tabs ) ) {
			$this->set_tab( $screen->screen_id );
		}

		return $this->current_tab;
	}

	/**
	 * Return the currently active section id.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @return string Screen id.
	 */
	protected function get_current_section_id() {
		return $this->current_section;
	}

	/**
	 * Generates a unique section id for displaying all sections on a tab at
	 * once.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @param string $screen_id A screen id.
	 * @param string $tab_id Optional. A tab id.
	 * @return string A section id for the WordPress Settings API.
	 */
	protected function get_wp_settings_section_id( $screen_id, $tab_id = null ) {
		return ( $screen_id === $tab_id ) ?  $screen_id : $screen_id . '-' . $tab_id;
	}

	/**
	 * Custom sorting method for ordering by priority.
	 *
	 * The sorting method in PHP isn't stable (array item position isn't
	 * maintained if $a=$b), so the title or label property is used as a
	 * fallback to sort tabs, sections, and fields alphabetically if their
	 * priority is equal. To use this method, the items must have a priority
	 * and either a label or title property.
	 *
	 * @since 1.0.0
	 * @deprecated 1.9.0
	 *
	 * @link https://core.trac.wordpress.org/ticket/22487
	 */
	protected function sort_by_priority( $a, $b ) {
		$ap = $a['priority'];
		$bp = $b['priority'];

		if ( $ap === $bp ) {
			// Sort by title or label instead since this isn't a stable sort.
			$at = ( isset( $a['label'] ) ) ? $a['label'] : $a['title'];
			$bt = ( isset( $b['label'] ) ) ? $b['label'] : $b['title'];

			if ( $at === $bt ) {
				return 0;
			}

			return $at < $bt ? -1 : 1;
		}

		return $ap < $bp ? -1 : 1;
	}
}
