<?php
class AudioTheme_Options {
	private static $instance;
	private static $current_panel;
	private static $panels;
	private static $sections;
	
	private function __construct() {
		self::$panels = array();
		self::$sections = array();
	}
	
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	public static function setup() {
		// Lower priority lets us register our panels in admin_menu hook and still have the menu item show up
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 20 );
	}
	
	/**
	 * Register Option Screens and Menu Items
	 */
	public static function admin_menu() {
		$options = self::get_instance();
		
		if ( ! empty( $options->panels ) ) {
			foreach ( $options->panels as $panel ) {
				if ( ! empty( $panel->show_in_menu ) ) {
					$pagehook = add_submenu_page( $panel->show_in_menu, $panel->name, $panel->menu_title, $panel->capability, $panel->menu_slug, array( __CLASS__, 'options_screen' ) );
				} else {
					$pagehook = add_menu_page( $panel->name, $panel->menu_title, $panel->capability, $panel->menu_slug, array( __CLASS__, 'options_screen' ) );
				}
				
				add_action( 'load-' . $pagehook, array( __CLASS__, 'options_screen_load' ) );
				add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
				
				// Register settings before fields are added in admin_init
				$option_names = (array) $panel->option_name;
				foreach ( $option_names as $name ) {
					register_setting( $panel->option_group, $name );
					add_filter( 'sanitize_option_' . $name, array( __CLASS__, 'sanitize_option' ), 10, 2 );
				}
				
				// @TODO: http://make.wordpress.org/themes/2011/07/01/wordpress-3-2-fixing-the-edit_theme_optionsmanage_options-bug/
				#add_filter( 'option_page_capability_' . $panel->option_group, array( __CLASS__, 'option_page_capability' ) );
			}
		}
	}
	
	public static function option_page_capability() {
		return 'publish_pages';
	}
	
	/**
	 * Enqueue Thickbox Functionality
	 *
	 * Used for selecting media files.
	 */
	public static function options_screen_load() {
		add_thickbox();
		wp_enqueue_script( 'media-upload' );
	}
	
	/**
	 * Output Error Messages
	 *
	 * Outputs any error messages added when options are saved. Adds a data
	 * attribute to the error message so it can be associated it with a 
	 * specific field.
	 */
	public static function admin_notices() {
		global $plugin_page;
		
		$panel = self::get_panel( $plugin_page );
		
		if ( $panel ) {
			$updated = true;
			$option_names = (array) $panel->option_name;
			foreach ( $option_names as $name ) {
				$errors = get_settings_errors( $name, false );
				if ( is_array( $errors ) ) {
					foreach ( $errors as $key => $details ) {
						printf( '<div id="%1$s" class="%2$s" data-field-id="%3$s"><p><strong>%4$s</strong></p></div>',
							'setting-error-' . str_replace( ':', '-', $details['code'] ),
							$details['type'] . ' settings-error inline',
							end( explode( ':', $details['code'] ) ),
							$details['message']
						);
					}
					$updated = false;
				}
			}
			
			if ( $updated && isset( $_REQUEST['settings-updated'] ) )  {
				echo '<div class="updated fade"><p><strong>Options saved.</strong></p></div>';
			}
		}
	}
	
	/**
	 * Render Option Screen
	 *
	 * Renders the tabs and fields, including CSS and javascript for tabbed
	 * panels and attaching error messages to fields and their parent tabs.
	 */
	public static function options_screen() {
		global $plugin_page, $wp_settings_sections;
		
		$panel = self::get_panel( $plugin_page );
		?>
		<div class="wrap">
			<form action="options.php" method="post">
				<?php
				screen_icon();
				
				echo '<h2 class="nav-tab-wrapper">';
					foreach ( $panel->tabs as $tab_id => $tab ) {
						echo '<a href="#' . $tab_id . '-panel" class="nav-tab">' . esc_html( $tab ) . '</a>';
					}
				echo '</h2>';
				
				
				if ( empty( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $panel->panel_id ] ) ) {
					$wp_settings_sections[ $panel->panel_id ] = array();
				}
				
				// Prepend a default section so a section doesn't have to be created before adding options
				if ( ! isset( $wp_settings_sections[ $panel->panel_id ]['_default'] ) ) {
					$wp_settings_sections[ $panel->panel_id ] = array_merge( array(
						'_default' => array(
							'id' => '_default',
							'title' => '',
							'callback' => '__return_false'
						)
					), $wp_settings_sections[ $panel->panel_id ] );
				}
				
				
				settings_fields( $panel->option_group );
				
				
				foreach ( $panel->tabs as $tab_id => $tab ) {
					echo '<div class="tab-panel" id="' . $tab_id . '-panel">';
						do_action( $panel->option_group . '_' . $tab_id . '_fields_before' );
						
						$settings_section_id = ( $panel->panel_id == $tab_id ) ? $panel->panel_id : $panel->panel_id . '-' . $tab_id;
						do_settings_sections( $settings_section_id );
						
						do_action( $panel->option_group . '_' . $tab_id . '_fields_after' );
					echo '</div>';
				}
				?>
				
				<p class="submit">
					<input type="submit" value="Save Options" class="button-primary">
				</p>
			</form>
		</div><!--end div.wrap-->
		
		<style type="text/css">
		.form-table td .button.thickbox { margin-left: 5px;}
		.js .tab-panel { display: none;}
		.js .tab-panel-active { display: block;}
		
		/* TODO: colors could be improved */
		h2.nav-tab-wrapper a.nav-tab.has-error { background: #fff6f6; border-color: #eedddd;}
		h2.nav-tab-wrapper a.nav-tab-active.has-error { color: #464646; background: #fff; border-color: #ccc; border-bottom-color: #fff;}
		.form-table tr.settings-error td input { background-color: #fff6f6; border-color: #ee9999;}
		.form-table tr.settings-error th label { color: #cc0000; font-weight: bold;}
		</style>
		<script>
		jQuery(function($) {
			var errors = $('div.settings-error'),
				updateTabs = function() {
					var hash = window.location.hash,
						refererField = $('input[name="_wp_http_referer"]');
					
					$('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active').filter('[href="' + hash + '"]').addClass('nav-tab-active');
					$('.tab-panel').removeClass('tab-panel-active').filter(hash).addClass('tab-panel-active').trigger('showTabPanel');
					
					if ( $('.nav-tab-wrapper .nav-tab-active').length < 1 ) {
						var href = $('.nav-tab-wrapper .nav-tab:eq(0)').addClass('nav-tab-active').attr('href');
						$('.tab-panel').removeClass('tab-panel-active').filter(href).addClass('tab-panel-active');
					}
					
					// Makes wp-admin/options.php redirect to the appropriate tab
					if ( -1 == refererField.val().indexOf('#') ) {
						refererField.val( refererField.val() + hash );
					} else {
						refererField.val( refererField.val().replace(/#.*/, hash) );
					}
				};
			
			$(window).on('hashchange', updateTabs);
			updateTabs();
			
			if ( errors.length ) {
				errors.each(function() {
					var $self = $(this),
						field = $( '#' + $self.data('field-id') ),
						tabPanel = field.closest('div.tab-panel');
					
					field.closest('tr').addClass('settings-error'); // Add .settings-error class to field container
					$('a.nav-tab[href="#' + tabPanel.attr('id') + '"]', 'h2.nav-tab-wrapper').addClass('has-error'); // Add .has-error class to tabs with errors
					$self.prependTo(tabPanel); // Prepend errors to the tab panel containing the field
				});
			}
		});
		</script>
		<?php
	}
	
	/**
	 * Default Option Sanitization Callback
	 *
	 * When options are registered using this class, they'll automatically be
	 * passed through this sanitization callback. The callback checks to see
	 * if any sanitization or validation routines have been registered for the
	 * field, and if so, calls them and adds any resulting errors.
	 *
	 * If an field fails a validation routine, this function attempts to
	 * revert to the old value, otherwise, it discards the new value.
	 */
	public static function sanitize_option( $value, $option ) {
		global $wp_settings_fields;
		
		foreach ( $wp_settings_fields as $sections ) {
			foreach ( $sections as $section ) {
				foreach ( $section as $field_name => $field ) {
					if ( is_array( $value ) && ! array_key_exists( $field_name, $value ) )
						continue;
					
					if ( isset( $field['args']['option_name'] ) && $option == $field['args']['option_name'] ) {
						$value = self::sanitize_field( $field, $value );
						
						if ( ! self::validate_field( $field, $option, $value ) ) {
							// Maintain existing value
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
	 * Run Field Sanitization Callbacks
	 *
	 * Looks for registered sanitization callbacks for a field and runs them.
	 * Sanitization callbacks must return a value.
	 *
	 * Accepts a comma delimited string or array of function names and
	 * executes them in order. If a function doesn't exist, such as a custom
	 * callback, it will be skipped.
	 */
	public static function sanitize_field( $field, $option_value ) {
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
	 * Run Field Validation Callbacks
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
	 */
	public static function validate_field( $field, $option_name, $option_value ) {
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
						
						// Used for adding data attributes to the error notice to highlight tabs and fields needing attention
						$error_code = str_replace( array( '[', ']' ), array( ':', '' ), $field['args']['name_attr'] );
						if ( ! $is_valid || is_wp_error( $is_valid ) ) {
							$error_msg = ( is_wp_error( $is_valid ) ) ? $is_valid->get_error_message() : $error_msg;
							
							add_settings_error( $option_name, $error_code, $error_msg );
							
							return false; // Only show one message per field
						}
					}
				}
			}
		}
		
		return true;
	}
	
	
	/**
	 * Add an Option Panel
	 *
	 * A panel is a custom screen consisting of tabs and sections of options.
	 *
	 * @TODO: finish implementing additional $args
	 */
	public function add_panel( $panel_id, $title, $args=array() ) {
		$default_options_id = str_replace( '-', '_', sanitize_title_with_dashes( $panel_id ) );
		
		$args = (object) wp_parse_args( $args, array(
			'capability' => 'manage_options',
			'menu_icon' => null,
			'menu_position' => null,
			'menu_slug' => $panel_id,
			'menu_title' => $title,
			'name' => $title,
			'option_group' => $default_options_id,
			'option_name' => $default_options_id,
			'panel_id' => $panel_id,
			'screen_icon' => null,
			'show_in_menu' => '',
			'tabs' => array( $panel_id => $title )
		) );
		
		// Make the option_name parameter an array so multiple option_names can be used in a single panel
		// The first option_name registered for a panel will be used as the default
		$args->option_name = (array) $args->option_name;
		
		$this->current_panel = $panel_id;
		$this->panels[ $panel_id ] = $args;
		
		return self::get_instance();
	}
	
	/**
	 * Get a Panel
	 *
	 * Return the specified panel for making changes or displaying.
	 */
	public function get_panel( $panel_id ) {
		$options = self::get_instance();
		
		if ( isset( $options->panels[ $panel_id ] ) ) {
			return $options->panels[ $panel_id ];
		}
		
		return false;
	}
	
	/**
	 * Set the Current Panel
	 *
	 * Sets the current panel so tabs, sections, and fields can be added.
	 */
	public function set_panel( $panel_id ) {
		$options = self::get_instance();
		$options->current_panel = $panel_id;
		
		return $options;
	}
	
	/**
	 * Add a Tab
	 *
	 * Adds a tab to a panel.
	 */
	public function add_tab( $tab_id, $title, $panel_id = null ) {
		$panel_id = ( empty( $panel_id ) ) ? $this->current_panel : $panel_id;
		
		$this->panels[ $panel_id ]->tabs[ $tab_id ] = $title;
		
		return $tab_id;
	}
	
	/**
	 * Add a Section
	 *
	 * Add a settings section to a tab.
	 */
	public function add_section( $section_id, $title = null, $tab_id = null, $args = array() ) {
		$panel = $this->panels[ $this->current_panel ];
		
		if ( empty( $tab_id ) || ! array_key_exists( $tab_id, $panel->tabs ) ) {
			$tab_id = key( (array) $panel->tabs );
		}
		
		extract( wp_parse_args( $args, array(
			'callback' => '__return_false',
			'settings_section' => ( $panel->panel_id == $tab_id ) ?  $panel->panel_id : $panel->panel_id . '-' . $tab_id
		) ) );
		
		$title = ( 0 === strpos( $section_id, '_default' ) ) ? '' : $title;
		
		add_settings_section( $section_id, $title, $callback, $settings_section );
		$this->sections[ $section_id ] = $settings_section;
		
		return $section_id;
	}
	
	/**
	 * Add a Field
	 *
	 * Adds a field to a settings section.
	 *
	 * If the 'field_id' and 'option_name' argument are equal, the option will
	 * be stored as a string in the database.
	 */
	public function add_field( $type, $id, $label, $section_id = null, $args = array() ) {
		$panel = $this->panels[ $this->current_panel ];
		
		$field_types = array(
			'checkbox',
			'html',
			'image',
			'select',
			'text',
			'textarea'
		);
		
		$callback = ( in_array( $type, $field_types ) ) ? array( &$this, 'option_' . $type . '_field' ) : $type;
		$section_id = ( empty( $section_id ) ) ? '_default' : $section_id;
		$settings_section = ( isset( $this->sections[ $section_id ] ) ) ? $this->sections[ $section_id ] : $panel->panel_id;
		
		$args = wp_parse_args( $args, array (
			'field_id' => $id,
			'label_for' => $id,
			'option_name' => current( (array) $panel->option_name )
		) );
		
		if ( 'checkbox' == $type ) {
			unset( $args['label_for'] );
		}
		
		// Create default name and value attributes; callbacks don't have to use these if they're not pertinent
		$options = get_option( $args['option_name'] );
		
		if ( $id == $args['option_name'] ) {
			$args['name_attr'] = $args['option_name'];
			$args['value'] = ( isset( $args['default_value'] ) ) ? $args['default_value'] : '';
			if ( null !== $options ) {
				$args['value'] = $options;
			}
		} else {
			$args['name_attr'] = $args['option_name'] . '[' . $id . ']';
			$args['value'] = ( isset( $args['default_value'] ) ) ? $args['default_value'] : '';
			if ( isset( $options[ $id ] ) ) {
				$args['value'] = $options[ $id ];
			}
		}
		
		add_settings_field( $id, $label, $callback, $settings_section, $section_id, $args );
		
		return $this;
	}
	
	/**
	 * Render a Checkbox Field
	 */
	public function option_checkbox_field( $args ) {
		extract( $args );
		
		$field_label = ( isset( $field_label ) ) ? $field_label : '';
		$field_value = ( isset( $field_value ) ) ? $field_value : 1;
		$checked = checked( $value, $field_value, false );
		$disabled = ( isset( $disabled ) && $disabled ) ? ' disabled="disabled"' : '';
		
		echo '<input type="checkbox" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '"' . $checked . $disabled . '>';
		echo ' <label for="' . esc_attr( $field_id ) . '">' . $field_label . '</label>';
		echo ( isset( $description ) ) ? '<br><span class="description">' . $description . '</span>' : '';
	}
	
	/**
	 * Output HTML
	 */
	public function option_html_field( $args ) {
		extract( $args );
		
		echo ( isset( $output ) ) ? $output : '';
	}
	
	/**
	 * Render an Image Field
	 *
	 * Defaults to using Thickbox for selecting an image URL.
	 */
	public function option_image_field( $args ) {
		extract( $args );
		
		$field_types = array( 'thickbox_image' ); // whitelist the allowed field types
		$type = ( ! isset( $type ) || ! in_array( $field_types ) ) ? 'thickbox_image' : $type;
		
		if ( 'thickbox_image' == $type ) {
			$class = ( isset( $class ) ) ? $class : 'regular-text';
			echo '<input type="text" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_url( $value ) . '" class="' . esc_attr( $class ) . '">';
			$tb_args = array( 'post_id' => 0, 'type' => 'image', 'TB_iframe' => true, 'width' => 640, 'height' => 750 );
			echo '<a href="' . add_query_arg( $tb_args, admin_url( 'media-upload.php' ) ) . '" title="Choose an Image" class="button thickbox" data-insert-field="' . esc_attr( $field_id ) . '" data-insert-button-text="Use This Image">Choose Image</a>';
		}
		
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
	}
	
	/**
	 * Render a Select Field
	 */
	public function option_select_field( $args ) {
		extract( $args );
		
		$field_value = ( isset( $field_value ) ) ? (array) $field_value : array( '' );
		echo '<select name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '">';
			foreach ( $field_value as $val => $label ) {
				echo '<option value="' . esc_attr( $val ) . '"' . selected( $val, $value, false ) . '>' . esc_html( $label ) . '</option>';
			}
		echo '</select>';
		echo ( isset( $description ) ) ? '<br><span class="description">' . $description . '</span>' : '';
	}
	
	/**
	 * Render a Text Field
	 */
	public function option_text_field( $args ) {
		extract( $args );
		
		$class = ( isset( $class ) ) ? $class : 'regular-text';
		echo '<input type="text" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $class ) . '">';
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
	}
	
	/**
	 * Render a Textarea Field
	 */
	public function option_textarea_field( $args ) {
		extract( $args );
		
		$class = ( isset( $class ) ) ? $class : 'large-text';
		$rows = ( isset( $rows ) ) ? intval( $rows ) : 4;
		echo '<textarea name="' . $name_attr . '" id="' . $field_id . '" rows="' . $rows . '" class="' . $class . '">'. esc_textarea( $value ) .'</textarea>';
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
	}
}
?>