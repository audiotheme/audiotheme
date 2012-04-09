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
	
	static function get_instance() {
		if ( NULL == self::$instance ) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
	
	function setup() {
		// let's us register panels in admin_menu and still have their menu item show up
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 20 );
	}
	
	function admin_menu() {
		$options = self::get_instance();
		
		if ( ! empty( $options->panels ) ) {
			foreach ( $options->panels as $panel ) {
				if ( ! empty( $panel->show_in_menu ) ) {
					$pagehook = add_submenu_page( $panel->show_in_menu, $panel->name, $panel->menu_title, $panel->capability, $panel->menu_slug, array( __CLASS__, 'options_screen' ) );
				} else {
					$pagehook = add_menu_page( $panel->name, $panel->menu_title, $panel->capability, $panel->menu_slug, array( __CLASS__, 'options_screen' ) );
				}
				
				add_action( 'load-' . $pagehook, array( __CLASS__, 'options_screen_load' ) );
				
				// register settings before fields are added in admin_init
				$option_names = (array) $panel->option_name;
				foreach ( $option_names as $name ) {
					register_setting( $panel->option_group, $name ); // option_group, option_name, sanitize_callback
				}
				
				#add_filter( 'option_page_capability_' . $panel->option_group, array( __CLASS__, 'option_page_capability' ) );
			}
		}
	}
	
	// http://make.wordpress.org/themes/2011/07/01/wordpress-3-2-fixing-the-edit_theme_optionsmanage_options-bug/
	function option_page_capability() {
		return 'publish_pages';
	}
	
	function options_screen_load() {
		add_thickbox();
		wp_enqueue_script( 'media-upload' );
	}
	
	function options_screen() {
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
				
				
				if ( isset( $_REQUEST['settings-updated'] ) )  {
					echo '<div class="updated fade"><p><strong>Options saved.</strong></p></div>';
				}
				
				
				if ( empty( $wp_settings_sections ) || ! isset( $wp_settings_sections[ $panel->panel_id ] ) ) {
					$wp_settings_sections[ $panel->panel_id ] = array();
				}
				
				// prepend a default section so a section doesn't have to be created before adding options
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
					<button type="submit" class="button-primary">Save Options</button>
				</p>
			</form>
		</div><!--end div.wrap-->
		
		<style type="text/css">
		.form-table td img { margin: 10px 0 0 0; max-width: 300px; height: auto; vertical-align: top;}
		.form-table td .button.thickbox { margin-left: 5px;}
		.js .tab-panel { display: none;}
		.js .tab-panel-active { display: block;}
		</style>
		<script>
		var tbField = null;
		
		jQuery(function($) {
			window.send_to_editor = function(html) {
				var src = (0 === html.indexOf('<img')) ? $(html).attr('src') : $(html).find('img').attr('src');
				$('input#' + tbField).val(src).closest('td').find('img').attr('src', src);
				tb_remove();
				tbField = null;
			}
			
			var updateTabs = function() {
				var hash = window.location.hash,
					refererField = $('input[name="_wp_http_referer"]');
				
				$('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active').filter('[href="' + hash + '"]').addClass('nav-tab-active');
				$('.tab-panel').removeClass('tab-panel-active').filter(hash).addClass('tab-panel-active').trigger('showTabPanel');
				
				if ( $('.nav-tab-wrapper .nav-tab-active').length < 1 ) {
					var href = $('.nav-tab-wrapper .nav-tab:eq(0)').addClass('nav-tab-active').attr('href');
					$('.tab-panel').removeClass('tab-panel-active').filter(href).addClass('tab-panel-active');
				}
				
				// makes /wp-admin/options.php redirect to the appropriate tab
				if ( -1 == refererField.val().indexOf('#') ) {
					refererField.val( refererField.val() + hash );
				} else {
					refererField.val( refererField.val().replace(/#.*/, hash) );
				}
			}
			
			$(window).on('hashchange', updateTabs);
			updateTabs();
		});
		</script>
		<?php
	}
	
	
	/**
	* Helper Methods
	*/
	
	function add_panel( $panel_id, $title, $args=array() ) {
		$default_options_id = str_replace( '-', '_', sanitize_title_with_dashes( $panel_id ) );
		
		$defaults = array(
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
		);
		
		$args = (object) wp_parse_args( $args, $defaults );
		
		// make the option_name parameter an array so multiple option_names can be used in a single panel
		// the first option_name registered for a panel will be used as the default
		$args->option_name = (array) $args->option_name;
		
		$this->current_panel = $panel_id;
		$this->panels[ $panel_id ] = $args;
		
		return self::get_instance();
	}
	
	function get_panel( $panel_id ) {
		$options = self::get_instance();
		
		if ( isset( $options->panels[ $panel_id ] ) ) {
			return $options->panels[ $panel_id ];
		}
		
		return false;
	}
	
	function set_panel( $panel_id ) {
		$options = self::get_instance();
		$options->current_panel = $panel_id;
		
		return $options;
	}
	
	function add_tab( $tab_id, $title, $panel_id=NULL ) {
		$panel_id = ( empty( $panel_id ) ) ? $this->current_panel : $panel_id;
		
		$this->panels[ $panel_id ]->tabs[ $tab_id ] = $title;
		
		return $tab_id;
	}
	
	function add_section( $section_id, $title=NULL, $tab_id=NULL, $args = array() ) {
		$panel = $this->panels[ $this->current_panel ];
		
		if ( empty( $tab_id ) || ! array_key_exists( $tab_id, $panel->tabs ) ) {
			$tab_id = key( (array) $panel->tabs );
		}
		
		extract( wp_parse_args( array(
			'callback' => '__return_false',
			'settings_section' => ( $panel->panel_id == $tab_id ) ?  $panel->panel_id : $panel->panel_id . '-' . $tab_id
		), $args ) );
		
		$title = ( 0 === strpos( $section_id, '_default' ) ) ? '' : $title;
		
		add_settings_section( $section_id, $title, $callback, $settings_section );
		$this->sections[ $section_id ] = $settings_section;
		
		return $section_id;
	}
	
	
	/**
	 * If the 'field_id' and 'option_name' argument are equal, the option will be stored as a string in the database.
	 */
	function add_field( $type, $id, $label, $section_id=NULL, $args = array() ) {
		$panel = $this->panels[ $this->current_panel ];
		
		$field_types = array(
			'checkbox',
			'html',
			'select',
			'text',
			'textarea',
			'thickbox_image'
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
		
		// create default name and value attributes; callbacks don't have to use these if they're not pertinent
		$options = get_option( $args['option_name'] );
		if ( $id == $args['option_name'] ) {
			$args['name_attr'] = $args['option_name'];
			$args['value'] = ( isset( $args['default_value'] ) ) ? $args['default_value'] : '';
			if ( NULL !== $options ) {
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
	}
	
	
	/**
	* Option Field Rendering
	*/
	
	function option_checkbox_field( $args ) {
		extract( $args );
		
		$field_label = ( isset( $field_label ) ) ? $field_label : '';
		$field_value = ( isset( $field_value ) ) ? $field_value : 1;
		$checked = checked( $value, $field_value, false );
		
		echo '<input type="checkbox" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '"' . $checked . '>';
		echo ' <label for="' . esc_attr( $field_id ) . '">' . $field_label . '</label>';
		echo ( isset( $description ) ) ? '<br><span class="description">' . $description . '</span>' : '';
	}
	
	function option_html_field( $args ) {
		extract( $args );
		
		echo ( isset( $output ) ) ? $output : '';
	}
	
	function option_select_field( $args ) {
		extract( $args );
		
		$field_value = ( isset( $field_value ) ) ? (array) $field_value : array( '' );
		echo '<select name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '">';
			foreach ( $field_value as $val => $label ) {
				echo '<option value="' . esc_attr( $val ) . '"' . selected( $val, $value, false ) . '>' . esc_html( $label ) . '</option>';
			}
		echo '</select>';
		echo ( isset( $description ) ) ? '<br><span class="description">' . $description . '</span>' : '';
	}
	
	function option_text_field( $args ) {
		extract( $args );
		
		$class = ( isset( $class ) ) ? $class : 'regular-text';
		echo '<input type="text" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" class="' . esc_attr( $class ) . '">';
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
	}
	
	function option_textarea_field( $args ) {
		extract( $args );
		
		$class = ( isset( $class ) ) ? $class : 'large-text';
		$rows = ( isset( $rows ) ) ? intval( $rows ) : 4;
		echo '<textarea name="' . $name_attr . '" id="' . $field_id . '" rows="' . $rows . '" class="' . $class . '">'. esc_textarea( $value ) .'</textarea>';
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
	}
	
	function option_thickbox_image_field( $args ) {
		extract( $args );
		
		$class = ( isset( $class ) ) ? $class : 'regular-text';
		echo '<input type="text" name="' . esc_attr( $name_attr ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_url( $value ) . '" class="' . esc_attr( $class ) . '">';
		$tb_args = array( 'post_id' => 0, 'type' => 'image', 'TB_iframe' => true, 'width' => 640, 'height' => 750 );
		echo '<a href="' . add_query_arg( $tb_args, admin_url( 'media-upload.php' ) ) . '" title="Choose an Image" class="button thickbox" onclick="tbField=\'' . esc_attr( $field_id ) . '\'; return false;">Choose Image</a>';
		echo ( isset( $description ) ) ? '<span class="description">' . $description . '</span>' : '';
		echo '<br><img src="' . esc_url( $value ) . '">';
	}
}
?>