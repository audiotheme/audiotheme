<?php
/**
 * Module base.
 *
 * @package   AudioTheme\Modules
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Base module class.
 *
 * @package AudioTheme\Modules
 * @since   1.9.0
 */
abstract class AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id;

	/**
	 * Module id.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $id;

	/**
	 * Whether the module should show on the dashboard.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $show_in_dashboard = false;

	/**
	 * Magic getter.
	 *
	 * @since 1.9.0
	 *
	 * @param string $name Property name.
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'admin_menu_id' :
			case 'id' :
				return $this->{$name};
			case 'name' :
				return $this->get_name();
			case 'description' :
				return $this->get_description();
		}
	}

	/**
	 * Retrieve the name of the module.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Retrieve the module description.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_description() {
		return '';
	}

	/**
	 * Method for loading the module.
	 *
	 * Typically occurs after the text domain has been loaded.
	 *
	 * @since 1.9.0
	 *
	 * @return $this
	 */
	public function load() {
		return $this;
	}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	abstract public function register_hooks();

	/**
	 * Whether the module's status can be toggled.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function can_toggle_status() {
		return current_user_can( 'activate_plugins' );
	}

	/**
	 * Whether the module is active.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function is_active() {
		$inactive_modules = get_option( 'audiotheme_inactive_modules', array() );
		return ! in_array( $this->id, $inactive_modules );
	}

	/**
	 * Prepare a module for use in JavaScript.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	public function prepare_for_js() {
		$data = array(
			'id'              => $this->id,
			'name'            => $this->name,
			'adminMenuId'     => $this->admin_menu_id,
			'canToggle'       => $this->can_toggle_status(),
			'isActive'        => $this->is_active(),
			'showInDashboard' => $this->show_in_dashboard(),
			'nonces'   => array(
				'toggle' => false,
			),
		);

		if ( $this->can_toggle_status() ) {
			$data['nonces']['toggle'] = wp_create_nonce( 'toggle-module_' . $this->id );
		}

		return $data;
	}

	/**
	 * Whether the module should show on the dashboard.
	 *
	 * @since 1.9.0
	 */
	public function show_in_dashboard() {
		return (bool) $this->show_in_dashboard;
	}
}
