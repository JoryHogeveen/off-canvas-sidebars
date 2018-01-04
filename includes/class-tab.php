<?php
/**
 * Off-Canvas Sidebars - Class Tab
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
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
	 * The fields for this tab.
	 * @var    array
	 * @since  0.5
	 */
	protected $fields = array();

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

		if ( current_user_can( $this->capability ) ) {
			add_filter( 'ocs_page_register_tabs', array( $this, 'register_tab' ) );
		}
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
		// @todo Enhance this...
		//register_setting( $this->tab, $this->key, array( $this, 'validate_form' ) );
	}

	/**
	 * Register a field for this tab.
	 *
	 * @since   0.5
	 * @param   string  $key
	 * @param   array   $args {
	 *     @type  string  $name      (required)
	 *     @type  string  $type      (required)
	 *     @type  string  $callback  (required)
	 *     @type  string  $validate
	 *     @type  string  $label
	 *     @type  string  $description
	 *     @type  array   $options
	 *     @type  string  $default
	 *     @type  string  $value
	 *     @type  bool    $required
	 * }
	 */
	public function add_settings_field( $key, $args ) {
		$args = wp_parse_args( $args, array(
			'type'     => 'text',
			'callback' => 'text_option',
			'validate' => true,
		) );
		$this->fields[ $key ] = $args;
	}

	/**
	 * Get a registered field.
	 * @since   0.5
	 * @param   string  $key
	 * @return  array
	 */
	public function get_settings_fields( $key = '' ) {
		if ( $key ) {
			if ( isset( $this->fields[ $key ] ) ) {
				return $this->fields[ $key ];
			}
			return null;
		}
		return $this->fields;
	}

	/**
	 * Get a registered field by type.
	 * @since   0.5
	 * @param   string  $type
	 * @param   bool    $return_keys  Return field keys only?
	 * @return  array
	 */
	public function get_settings_fields_by_type( $type = '', $return_keys = false ) {
		$fields = $this->get_settings_fields();
		foreach ( $fields as $key => $field ) {
			if ( empty( $field['type'] ) || $field['type'] !== $type ) {
				unset( $fields[ $key ] );
			}
		}
		if ( $return_keys ) {
			return array_keys( $fields );
		}
		return $fields;
	}

	/**
	 * Check if this instance is the current page tab.
	 * @since   0.5
	 * @return  bool
	 */
	public function is_current_tab() {
		$tab = $this->get_current_tab();
		if ( $tab ) {
			return ( $this->tab === $tab->tab );
		}
		return null;
	}

	/**
	 * Get the current active tab.
	 * @since   0.5
	 * @return  OCS_Off_Canvas_Sidebars_Tab instance
	 */
	public function get_current_tab() {
		return OCS_Off_Canvas_Sidebars_Page::get_instance()->get_current_tab();
	}

	/**
	 * Check if this instance is the current request handler tab.
	 * @since   0.5
	 * @return  bool
	 */
	public function is_request_tab() {
		$tab = $this->get_request_tab();
		if ( $tab ) {
			return ( $this->tab === $tab->tab );
		}
		return null;
	}

	/**
	 * Get the current request handler tab.
	 * @since   0.5
	 * @return  OCS_Off_Canvas_Sidebars_Tab instance
	 */
	public function get_request_tab() {
		return OCS_Off_Canvas_Sidebars_Page::get_instance()->get_request_tab();
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
