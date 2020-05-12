<?php
/**
 * Off-Canvas Sidebars - Class Settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.5
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Settings extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Settings
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	/**
	 * The plugin settings.
	 *
	 * @var    array
	 * @since  0.1.0
	 */
	protected $settings = array();

	/**
	 * Default settings.
	 *
	 * @var    array
	 * @since  0.2.0
	 */
	protected $default_settings = array(
		'db_version'                    => '0',
		'enable_frontend'               => 1,
		'late_init'                     => 0,
		'frontend_type'                 => 'action',
		'site_close'                    => 1,
		'link_close'                    => 1,
		'disable_over'                  => '',
		'hide_control_classes'          => 0,
		'scroll_lock'                   => 0,
		'background_color_type'         => '',
		'background_color'              => '',
		'website_before_hook'           => 'website_before',
		'website_after_hook'            => 'website_after',
		'use_fastclick'                 => 0,
		'compatibility_position_fixed'  => 'none',
		'wp_editor_shortcode_rendering' => 0,
		'css_prefix'                    => 'ocs',
		'sidebars'                      => array(),
	);

	/**
	 * Default sidebar settings.
	 *
	 * @var    array
	 * @since  0.2.0
	 */
	protected $default_sidebar_settings = array(
		'enable'                    => 0,
		'label'                     => '',
		'content'                   => 'sidebar',
		'location'                  => 'left',
		'style'                     => 'push',
		'size'                      => 'default',
		'size_input'                => '',
		'size_input_type'           => '%',
		'animation_speed'           => '',
		'padding'                   => '',
		'background_color'          => '',
		'background_color_type'     => '',
		// Global overwrites.
		'overwrite_global_settings' => 0,
		'site_close'                => 1,
		'link_close'                => 1,
		'disable_over'              => '',
		'hide_control_classes'      => 0,
		'scroll_lock'               => 0,
	);

	/**
	 * Class constructor.
	 *
	 * @since   0.1.0
	 * @since   0.3.0  Private constructor.
	 * @access  private
	 */
	private function __construct() {
		$this->set_settings( (array) get_option( off_canvas_sidebars()->get_general_key(), array() ) );
	}

	/**
	 * Get the plugin settings.
	 *
	 * @since   0.5.0
	 * @param   string  $key  (optional) Get a single setting by key?
	 * @return  mixed
	 */
	public function get_settings( $key = null ) {
		if ( $key ) {
			return ( isset( $this->settings[ $key ] ) ) ? $this->settings[ $key ] : null;
		}
		return $this->settings;
	}

	/**
	 * Get the plugin sidebars.
	 *
	 * @since   0.5.3
	 * @return  array
	 */
	public function get_sidebars( $sidebar_id = null ) {
		if ( $sidebar_id ) {
			return $this->get_sidebar_settings( $sidebar_id );
		}
		return $this->get_settings( 'sidebars' );
	}

	/**
	 * Get the enabled plugin sidebars.
	 *
	 * @since   0.5.3
	 * @return  array
	 */
	public function get_enabled_sidebars() {
		$sidebars = $this->get_sidebars();
		foreach ( $sidebars as $sidebar_id => $sidebar_data ) {
			if ( ! $this->is_sidebar_enabled( $sidebar_id ) ) {
				unset( $sidebars[ $sidebar_id ] );
			}
		}
		return $sidebars;
	}

	/**
	 * Get the plugin settings.
	 *
	 * @since   0.5.3
	 * @param   string  $sidebar_id  The sidebar ID.
	 * @param   string  $key         (optional) Get a single setting by key?
	 * @return  mixed
	 */
	public function get_sidebar_settings( $sidebar_id, $key = null ) {
		$sidebars = $this->get_sidebars();
		if ( empty( $sidebars[ $sidebar_id ] ) ) {
			return null;
		}
		if ( $key ) {
			$settings = $sidebars[ $sidebar_id ];
			$return   = $this->get_settings( $key );
			if ( $return ) {
				if ( ! empty( $settings['overwrite_global_settings'] ) ) {
					$return = ( isset( $settings[ $key ] ) ) ? $settings[ $key ] : $return;
				}
			} else {
				$return = ( isset( $settings[ $key ] ) ) ? $settings[ $key ] : null;
			}
			return $return;
		}
		return $sidebars[ $sidebar_id ];
	}

	/**
	 * Check if an off-canvas sidebar should be shown.
	 *
	 * @todo Move to sidebar class.
	 *
	 * @since   0.5.3
	 * @param   string  $sidebar_id
	 * @param   array   $sidebar_data
	 * @return  bool
	 */
	public function is_sidebar_enabled( $sidebar_id, $sidebar_data = null ) {
		if ( ! $sidebar_data ) {
			$sidebar_data = $this->get_sidebar_settings( $sidebar_id );
			if ( ! $sidebar_data ) {
				return false;
			}
		}

		return ! empty( $sidebar_data['enable'] );
	}

	/**
	 * Returns the default settings.
	 *
	 * @since   0.2.0
	 * @return  array
	 */
	public function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Returns the default sidebar_settings.
	 *
	 * @since   0.2.0
	 * @return  array
	 */
	public function get_default_sidebar_settings() {
		return $this->default_sidebar_settings;
	}

	/**
	 * Store the settings in the database.
	 *
	 * @since   0.5.0
	 * @param   array  $settings
	 */
	public function update_settings( $settings ) {
		$this->set_settings( $settings );
		update_option( off_canvas_sidebars()->get_general_key(), $this->get_settings() );
	}

	/**
	 * Merge database plugin settings with default settings.
	 *
	 * @since   0.1.0
	 * @since   0.5.0  Renamed from `self::get_settings()`.
	 * @param   array  $settings
	 */
	public function set_settings( $settings ) {

		// Validate global settings.
		$settings = $this->validate_settings( $settings, $this->get_default_settings() );
		// Validate sidebar settings.
		foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$settings['sidebars'][ $sidebar_id ] = $this->validate_settings( $sidebar_settings, $this->get_default_sidebar_settings() );
		}

		$this->settings = $settings;
	}

	/**
	 * Validate setting keys.
	 *
	 * @since   0.2.0
	 * @param   array  $settings
	 * @param   array  $defaults
	 * @return  array
	 */
	public function validate_settings( $settings, $defaults ) {
		// supports one level array
		$settings = array_merge( $defaults, $settings );
		// Remove unknown keys
		foreach ( $settings as $key => $value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $settings[ $key ] );
			} else {
				// Validate types
				settype( $settings[ $key ], gettype( $defaults[ $key ] ) );
			}
		}
		return $settings;
	}

	/**
	 * Parses post values, checks all values with the current existing data.
	 *
	 * @since   0.4.0
	 * @param   array  $input
	 * @return  array  $output
	 */
	protected function parse_input( $input ) {
		// First set current values.
		$current = $this->get_settings();

		/**
		 * Filter the form input data before validation with defaults.
		 * @since  0.5.0
		 * @param  array  $input    New form input.
		 * @param  array  $current  Current settings.
		 * @return array
		 */
		$input = apply_filters( 'ocs_settings_parse_input', $input, $current );

		// Overwrite non existing values with current values.
		foreach ( $current as $key => $value ) {
			if ( ! isset( $input[ $key ] ) ) {
				$input[ $key ] = $value;
			}
		}

		// Make sure all top level keys exists.
		$input = array_merge( $this->get_default_settings(), $input );

		return $input;
	}

	/**
	 * Validates post values.
	 *
	 * @since   0.1.0
	 * @param   array  $input
	 * @return  array  $data
	 */
	public function validate_form( $input ) {
		// Overwrite the old settings.
		$data = $this->parse_input( $input );

		// @todo Enhance saving validation.
		if ( ! has_filter( 'ocs_settings_validate_input' ) || ! OCS_Off_Canvas_Sidebars_Page::get_instance()->get_request_tab() ) {
			wp_die( __( 'Something went wrong, please try again', OCS_DOMAIN ) );
		}

		/**
		 * Filter the parsed form data.
		 * @since  0.5.0
		 * @param  array  $data   Parsed input data.
		 * @param  array  $input  Original input.
		 * @return array
		 */
		$data = apply_filters( 'ocs_settings_validate_input', $data, $input );

		// Validate global settings with defaults.
		$data = $this->validate_settings( $data, $this->get_default_settings() );
		// Validate sidebar settings with defaults.
		foreach ( $data['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$data['sidebars'][ $sidebar_id ] = $this->validate_settings( $sidebar_settings, $this->get_default_sidebar_settings() );
		}

		unset( $data['ocs_tab'] );
		$data['db_version'] = off_canvas_sidebars()->get_db_version();

		return $data;
	}

	/**
	 * Validate an array of fields by through the provided field data.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.5.0
	 * @param   array  $data
	 * @param   array  $fields
	 * @return  array
	 */
	public static function validate_fields( $data, $fields ) {

		$new_data = array();

		foreach ( $fields as $field_key => $field ) {
			$field = wp_parse_args( $field, array(
				'validate' => true,
				'type'     => 'text',
			) );
			if ( empty( $field['name'] ) ) {
				continue;
			}
			$key = $field['name'];

			if ( ! isset( $data[ $key ] ) ) {
				continue;
			}

			if ( ! $field['validate'] ) {
				$new_data[ $key ] = $data[ $key ];
				continue;
			}

			$args     = array( $data[ $key ] );
			$callback = null;

			if ( is_callable( $field['validate'] ) || is_string( $field['validate'] ) ) {
				$callback = $field['validate'];
			} else {
				switch ( $field['type'] ) {
					case 'checkbox':
						// Make sure unchecked checkboxes are 0 on save.
						$callback = 'validate_checkbox';
						break;
					case 'number':
						// Numeric values, not integers!
						$callback = 'validate_numeric';
						break;
					case 'radio':
						// Validate radio options.
						$callback = 'validate_radio';
						$args[]   = array_keys( $field['options'] );
						$args[]   = $field['default'];
						break;
					case 'color':
						$callback = 'validate_color';
						break;
					case 'text':
					default:
						$callback = 'validate_text';
						break;
				}
			}

			if ( is_string( $callback ) ) {
				$callback = array( 'OCS_Off_Canvas_Sidebars_Settings', $callback );
			}

			$new_data[ $key ] = call_user_func_array( $callback, $args );
		}

		return $new_data;
	}

	/**
	 * Validates text values, used by validate_input().
	 *
	 * @since   0.5.0
	 * @param   mixed   $value
	 * @return  string
	 */
	public static function validate_text( $value ) {
		if ( ! is_scalar( $value ) || empty( $value ) ) {
			return '';
		}
		return (string) wp_strip_all_tags( $value );
	}

	/**
	 * Validates checkbox values, used by validate_input().
	 *
	 * @since   0.1.2
	 * @since   0.5.0   Optional $key parameter.
	 * @param   mixed   $value
	 * @param   string  $key
	 * @return  int
	 */
	public static function validate_checkbox( $value, $key = '' ) {
		if ( $key ) {
			return (int) ( ! empty( $value[ $key ] ) );
		}
		return (int) ( ! empty( $value ) );
	}

	/**
	 * Validates radio values against the possible options.
	 *
	 * @since   0.4.0
	 * @param   string  $value
	 * @param   array   $options
	 * @param   string  $default
	 * @return  string
	 */
	public static function validate_radio( $value, $options, $default ) {
		return ( ! empty( $value ) && in_array( $value, $options, true ) ) ? wp_strip_all_tags( $value ) : $default;
	}

	/**
	 * Validates id values, used by validate_input.
	 *
	 * @since   0.2.0
	 * @since   0.3.0  Convert to lowercase and convert spaces to dashes before `preg_replace()`.
	 * @param   string  $value
	 * @return  string
	 */
	public static function validate_id( $value ) {
		return preg_replace( '/[^a-z0-9_-]+/i', '', str_replace( ' ', '-', strtolower( $value ) ) );
	}

	/**
	 * Validates numeric values, used by validate_input().
	 *
	 * @since   0.2.2
	 * @param   mixed  $value
	 * @return  string
	 */
	public static function validate_numeric( $value ) {
		return ( ! empty( $value ) && is_numeric( $value ) ) ? (string) absint( $value ) : '';
	}

	/**
	 * Validates hex color values, used by validate_input().
	 *
	 * @since   0.5.0
	 * @param   string  $value
	 * @return  string
	 */
	public static function validate_color( $value ) {
		$value = self::validate_text( $value );
		$value = self::remove_whitespace( $value );
		if ( ! $value ) {
			return '';
		}
		$value = substr( $value, 0, 7 );
		if ( 0 !== strpos( $value, '#' ) ) {
			$value = '#' . $value;
		}
		return (string) $value;
	}

	/**
	 * Remove whitespace.
	 *
	 * @since   0.3.0
	 * @param   mixed  $value
	 * @return  string
	 */
	public static function remove_whitespace( $value ) {
		return ( ! empty( $value ) ) ? str_replace( ' ', '', (string) $value ) : '';
	}

	/**
	 * Updates the existing widgets when a sidebar ID changes.
	 *
	 * @since   0.3.0
	 * @param   string  $old_id
	 * @param   string  $new_id
	 */
	public static function migrate_sidebars_widgets( $old_id, $new_id ) {
		$old_id           = 'off-canvas-' . $old_id;
		$new_id           = 'off-canvas-' . $new_id;
		$sidebars_widgets = wp_get_sidebars_widgets();

		if ( ! empty( $sidebars_widgets[ $old_id ] ) ) {
			$sidebars_widgets[ $new_id ] = $sidebars_widgets[ $old_id ];
			unset( $sidebars_widgets[ $old_id ] );
		}

		wp_set_sidebars_widgets( $sidebars_widgets );
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Settings
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
