<?php
/**
 * Custom Theme Customizer controls.
 *
 * Custom controls extend the WP_Customize_Control class, so they should only
 * be included when that class is available.
 *
 * @see _wp_customize_include()
 * 
 * @package AudioTheme_Framework
 * @subpackage Settings
 */

/**
 * Theme Customizer textarea control.
 *
 * @package AudioTheme_Framework
 * @subpackage Settings
 * 
 * @since 1.0.0
 */
class Audiotheme_Settings_Customize_Textarea_Control extends WP_Customize_Control {
	/**
	 * @access public
	 * @var string
	 */
	public $type = 'textarea';
	
	/**
	 * @access public
	 * @var int
	 */
	public $rows = 4;
	
	/**
	 * Constructor.
	 *
	 * Overrides the parent constructor to support the rows argument, then calls the parent constructor to continue setup.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args = array() ) {
		$this->rows = ( isset( $args['rows'] ) ) ? absint( $args['rows'] ) : 4;
		parent::__construct( $manager, $id, $args );
	}
	
	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="<?php echo $this->rows; ?>" <?php $this->link(); ?> style="width: 98%"><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
		<?php
	}
}
?>