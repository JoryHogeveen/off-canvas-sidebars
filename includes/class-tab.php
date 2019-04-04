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
 * @since   0.5.0
 * @version 0.5.4
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
abstract class OCS_Off_Canvas_Sidebars_Tab extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The ID of this tab.
	 * @var    string
	 * @since  0.5.0
	 */
	public $tab = '';

	/**
	 * The name of this tab.
	 * @var    string
	 * @since  0.5.0
	 */
	public $name = '';

	/**
	 * The setting key of this tab.
	 * @var    string
	 * @since  0.5.0
	 */
	public $key = '';

	/**
	 * The filter name of this tab.
	 * @var    string
	 * @since  0.5.0
	 */
	public $filter = '';

	/**
	 * The fields for this tab.
	 * @var    array
	 * @since  0.5.0
	 */
	protected $fields = array();

	/**
	 * The capability required of this tab.
	 * @var    string
	 * @since  0.5.0
	 */
	public $capability = 'edit_theme_options';

	/**
	 * Class constructor.
	 * @since   0.5.0
	 * @access  protected
	 */
	protected function __construct() {
		$this->key = off_canvas_sidebars()->get_general_key();
		if ( ! $this->filter ) {
			$this->filter = str_replace( array( 'ocs-', 'ocs_' ), '', $this->tab );
		}
		$this->capability = apply_filters( 'ocs_settings_capability_' . $this->filter, $this->capability );

		if ( current_user_can( $this->capability ) ) {
			add_filter( 'ocs_page_register_tabs', array( $this, 'register_tab' ) );
		}
	}

	/**
	 * Register this tab.
	 * @since   0.5.0
	 * @param   array  $tabs
	 * @return  array
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
	 * @since   0.1.0
	 * @since   0.5.0  Refactor into separate tab classes and methods.
	 */
	public function register_settings() {
		// @todo Enhance this...
		//register_setting( $this->tab, $this->key, array( $this, 'validate_form' ) );
	}

	/**
	 * Register setting section fields.
	 *
	 * @since   0.5.0  Refactor into separate tab classes and methods.
	 * @param   array  $args {
	 *     @type  string           $id
	 *     @type  string           $title
	 *     @type  string|callable  $callback
	 * }
	 */
	public function register_section_fields( $args ) {
		$section = $args['id'];

		$fields = $this->get_settings_fields_by_section( $section );

		foreach ( $fields as $id => $args ) {

			if ( ! empty( $args['hidden'] ) ) {
				continue;
			}

			$title = $args['title'];
			unset( $args['title'] );

			$callback = $args['callback'];
			unset( $args['callback'] );
			if ( is_string( $callback ) ) {
				$callback = array( 'OCS_Off_Canvas_Sidebars_Form', $callback );
			}

			add_settings_field( $id, $title, $callback, $this->tab, $section, $args );
		}
	}

	/**
	 * Register a field for this tab.
	 *
	 * @since   0.5.0
	 * @param   string  $key
	 * @param   array   $args {
	 *     @type  string           $name      (required)
	 *     @type  string           $type
	 *     @type  string|callable  $callback
	 *     @type  string|callable  $validate
	 *     @type  string           $label
	 *     @type  string           $description
	 *     @type  array            $options
	 *     @type  string           $default
	 *     @type  string           $value
	 *     @type  bool             $required
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
	 * @since   0.5.0
	 * @param   string  $key  The field setting key.
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
	 * @since   0.5.4
	 * @param   string  $prop         The field property key.
	 * @param   string  $value        The field property value.
	 * @param   bool    $return_keys  Return field keys only?
	 * @return  array
	 */
	public function get_settings_fields_by( $prop = '', $value = '', $return_keys = false ) {
		$fields = $this->get_settings_fields();
		foreach ( $fields as $key => $field ) {
			if ( empty( $field[ $prop ] ) || $field[ $prop ] !== $value ) {
				unset( $fields[ $key ] );
			}
		}
		if ( $return_keys ) {
			return array_keys( $fields );
		}
		return $fields;
	}

	/**
	 * Get a registered field by type.
	 * @since   0.5.0
	 * @param   string  $type
	 * @param   bool    $return_keys  Return field keys only?
	 * @return  array
	 */
	public function get_settings_fields_by_type( $type = '', $return_keys = false ) {
		return $this->get_settings_fields_by( 'type', $type, $return_keys );
	}

	/**
	 * Get a registered field by section.
	 * @since   0.5.0
	 * @param   string  $section
	 * @param   bool    $return_keys  Return field keys only?
	 * @return  array
	 */
	public function get_settings_fields_by_section( $section = '', $return_keys = false ) {
		return $this->get_settings_fields_by( 'section', $section, $return_keys );
	}

	/**
	 * Get a tab.
	 * @since   0.5.1
	 * @param   string  $tab
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_tab( $tab ) {
		return OCS_Off_Canvas_Sidebars_Page::get_instance()->get_tab( $tab );
	}

	/**
	 * Check if this instance is the current page tab.
	 * @since   0.5.0
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
	 * @since   0.5.0
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_current_tab() {
		return OCS_Off_Canvas_Sidebars_Page::get_instance()->get_current_tab();
	}

	/**
	 * Check if this instance is the current request handler tab.
	 * @since   0.5.0
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
	 * @since   0.5.0
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_request_tab() {
		return OCS_Off_Canvas_Sidebars_Page::get_instance()->get_request_tab();
	}

	/**
	 * Validate input.
	 *
	 * @since   0.5.0
	 * @param   array  $input
	 * @return  array
	 */
	public function validate_form( $input ) {
		return off_canvas_sidebars_settings()->validate_form( $input );
	}

} // End class().
