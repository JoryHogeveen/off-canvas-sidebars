<?php
/**
 * Off-Canvas Sidebars - Class Tab
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @since   0.5
 * @version 0.5
 */
abstract class OCS_Off_Canvas_Sidebars_Tab extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The name of this tab.
	 * @var    string
	 * @since  0.5
	 */
	public $tab = '';

	/**
	 * The ID of this tab.
	 * @var    string
	 * @since  0.5
	 */
	public $name = '';

	/**
	 * The setting key of this tab.
	 * @var    string
	 * @since  0.5
	 */
	public $key = '';

	/**
	 * The capability required of this tab.
	 * @var    string
	 * @since  0.5
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Class constructor.
	 * @since   0.5
	 * @access  protected
	 */
	protected function __construct() {
		$this->key = off_canvas_sidebars()->get_general_key();
		$this->capability = apply_filters( 'ocs_settings_capability_' . $this->name, $this->capability );
		add_filter( 'ocs_page_register_tabs', array( $this, 'register_tab' ) );
	}

	/**
	 * Register this tab.
	 * @since   0.5
	 * @param   array  $tabs
	 * @return  array  mixed
	 */
	public function register_tab( $tabs ) {
		$tabs[ $this->tab ] = $this;
		return $tabs;
	}

	/**
	 * Init function for the tab.
	 */
	abstract public function init();

	/**
	 * Register settings.
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		register_setting( $this->tab, $this->key, array( $this, 'validate_form' ) );
	}

	/**
	 * Validate input.
	 *
	 * @since   0.5
	 * @param   array  $input
	 * @return  array
	 */
	public function validate_form( $input ) {
		return OCS_Off_Canvas_Sidebars_Settings::get_instance()->validate_form( $input );
	}

} // End class().
