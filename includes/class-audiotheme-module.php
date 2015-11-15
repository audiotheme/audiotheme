<?php
/**
 * Module base.
 *
 * @package AudioTheme\Modules
 * @since 1.9.0
 */

/**
 * Base module class.
 *
 * @package AudioTheme\Modules
 * @since 1.9.0
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
	 * Module name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $name;

	/**
	 * Module description.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $description;

	/**
	 * Whether the module is a core module.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $is_core_module = false;

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
			case 'description' :
			case 'name' :
				return $this->{$name};
		}
	}

	/**
	 * Method for loading the module.
	 *
	 * Typically occurs after the text domain has been loaded.
	 *
	 * @since 1.9.0
	 */
	public function load() {}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	abstract public function register_hooks();

	/**
	 * Whether the module is a core module.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function is_core() {
		return (bool) $this->is_core_module;
	}

	/**
	 * Set the module description.
	 *
	 * @since 1.9.0
	 *
	 * @param string $description Module description.
	 * @return $this
	 */
	protected function set_description( $description ) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Set the module name.
	 *
	 * @since 1.9.0
	 *
	 * @param string $name Module name.
	 * @return $this
	 */
	protected function set_name( $name ) {
		$this->name = $name;
		return $this;
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
