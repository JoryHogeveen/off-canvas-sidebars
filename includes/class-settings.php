<?php
/**
 * Off-Canvas Sidebars - Class Settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @since   0.1
 * @version 0.5
 */
final class OCS_Off_Canvas_Sidebars_Settings extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Settings
	 * @since  0.3
	 */
	protected static $_instance = null;

	/**
	 * The plugin settings.
	 *
	 * @var    array
	 * @since  0.1
	 */
	protected $settings = array();

	/**
	 * Default settings.
	 *
	 * @var    array
	 * @since  0.2
	 */
	protected $default_settings = array(
		'db_version'                    => '0',
		'enable_frontend'               => 1,
		'frontend_type'                 => 'action',
		'site_close'                    => 1,
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
	 * @since  0.2
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
		'disable_over'              => '',
		'hide_control_classes'      => 0,
		'scroll_lock'               => 0,
	);

	/**
	 * Class constructor.
	 *
	 * @since   0.1
	 * @since   0.3  Private constructor.
	 * @access  private
	 */
	private function __construct() {
		$this->set_settings( (array) get_option( off_canvas_sidebars()->get_general_key(), array() ) );
	}

	/**
	 * Get the plugin settings.
	 *
	 * @since   0.5
	 * @param   string  $key
	 * @return  array|null
	 */
	function get_settings( $key = null ) {
		if ( $key ) {
			return ( isset( $this->settings[ $key ] ) ) ? $this->settings[ $key ] : null;
		}
		return $this->settings;
	}

	/**
	 * Returns the default settings.
	 *
	 * @since   0.2
	 * @return  array
	 */
	function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Returns the default sidebar_settings.
	 *
	 * @since   0.2
	 * @return  array
	 */
	function get_default_sidebar_settings() {
		return $this->default_sidebar_settings;
	}

	/**
	 * Store the settings in the database.
	 *
	 * @since   0.5
	 * @param   array  $settings
	 */
	public function update_settings( $settings ) {
		$this->set_settings( $settings );
		update_option( off_canvas_sidebars()->get_general_key(), $this->get_settings() );
	}

	/**
	 * Merge database plugin settings with default settings.
	 *
	 * @since   0.1
	 * @since   0.5  Renamed from self::get_settings()
	 * @param   array  $settings
	 * @return  array
	 */
	function set_settings( $settings ) {

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
	 * @since   0.2
	 * @param   array  $settings
	 * @param   array  $defaults
	 * @return  array
	 */
	function validate_settings( $settings, $defaults ) {
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
	 * @since   0.4
	 * @param   array  $input
	 * @return  array  $output
	 */
	protected function parse_input( $input ) {
		// First set current values.
		$current = $this->get_settings();

		// Add new sidebar.
		if ( ! empty( $input['sidebars']['ocs_add_new'] ) ) {
			$new_sidebar_id = self::validate_id( $input['sidebars']['ocs_add_new'] );
			if ( empty( $input['sidebars'][ $new_sidebar_id ] ) && empty( $current['sidebars'][ $new_sidebar_id ] ) ) {
				$input['sidebars'][ $new_sidebar_id ] = array_merge(
					$this->get_default_sidebar_settings(),
					array(
						'enable' => 1,
						'label'  => strip_tags( stripslashes( $input['sidebars']['ocs_add_new'] ) ),
					)
				);
			} else {
				add_settings_error(
					$new_sidebar_id . '_duplicate_id',
					esc_attr( 'ocs_duplicate_id' ),
					// Translators: %s stands for a sidebar ID.
					sprintf( __( 'The ID %s already exists! Sidebar not added.', OCS_DOMAIN ), '<code>' . $new_sidebar_id . '</code>' )
				);
			}
		}
		unset( $input['sidebars']['ocs_add_new'] );

		/**
		 * Filter the form input data.
		 * @since  0.5
		 * @param  array  $input    New form input.
		 * @param  array  $current  Current settings.
		 * @return array
		 */
		$input = apply_filters( 'ocs_settings_parse_input', $input, $current );

		// Handle existing sidebars.
		$input = $this->parse_sidebars_input( $input, $current );

		// Overwrite non existing values with current values.
		foreach ( $current as $key => $value ) {
			if ( ! isset( $input[ $key ] ) ) {
				$input[ $key ] = $value;
			}
		}

		return $input;
	}

	/**
	 * Parses sidebar post values, checks all values with the current existing data.
	 *
	 * @since   0.4
	 * @param   array  $input
	 * @param   array  $current
	 * @return  array
	 */
	private function parse_sidebars_input( $input, $current ) {
		if ( empty( $current['sidebars'] ) || ! isset( $input['sidebars'] ) ) {
			return $input;
		}

		$current  = (array) $current['sidebars'];
		$sidebars = (array) $input['sidebars'];

		foreach ( $current as $sidebar_id => $sidebar_data ) {

			if ( ! isset( $sidebars[ $sidebar_id ] ) ) {
				$sidebars[ $sidebar_id ] = $current[ $sidebar_id ];
				// Sidebars are set but this sidebar isn't checked as active.
				$sidebars[ $sidebar_id ]['enable'] = 0;
				continue;
			}

			// Global settings page.
			if ( count( $sidebars[ $sidebar_id ] ) < 2 ) {
				$current[ $sidebar_id ]['enable'] = self::validate_checkbox( $sidebars[ $sidebar_id ]['enable'] );
				$sidebars[ $sidebar_id ] = $current[ $sidebar_id ];
				continue;
			}

			// Default label is sidebar ID.
			if ( empty( $sidebars[ $sidebar_id ]['label'] ) ) {
				$sidebars[ $sidebar_id ]['label'] = $sidebar_id;
			}

			// Change sidebar ID.
			if ( ! empty( $sidebars[ $sidebar_id ]['id'] ) && $sidebar_id !== $sidebars[ $sidebar_id ]['id'] ) {

				$new_sidebar_id = self::validate_id( $sidebars[ $sidebar_id ]['id'] );

				if ( $sidebar_id !== $new_sidebar_id ) {

					if ( empty( $sidebars[ $new_sidebar_id ] ) ) {

						$sidebars[ $new_sidebar_id ] = $sidebars[ $sidebar_id ];
						$sidebars[ $new_sidebar_id ]['id'] = $new_sidebar_id;

						unset( $sidebars[ $sidebar_id ] );

						// Migrate existing widgets to the new sidebar.
						$this->migrate_sidebars_widgets( $sidebar_id, $new_sidebar_id );

					} else {
						add_settings_error(
							$sidebar_id . '_duplicate_id',
							esc_attr( 'ocs_duplicate_id' ),
							// Translators: %s stands for a sidebar ID.
							sprintf( __( 'The ID %s already exists! The ID is not changed.', OCS_DOMAIN ), '<code>' . $new_sidebar_id . '</code>' )
						);
					}
				}
			}
		} // End foreach().

		$input['sidebars'] = $sidebars;
		return $input;
	}

	/**
	 * Validates post values.
	 *
	 * @since   0.1
	 * @param   array  $input
	 * @return  array  $data
	 */
	public function validate_form( $input ) {
		// Overwrite the old settings.
		$data = $this->parse_input( $input );

		/**
		 * Filter the parsed form data.
		 * @since  0.5
		 * @param  array  $data
		 * @return array
		 */
		$data = apply_filters( 'ocs_settings_validate_form', $data );

		foreach ( $data['sidebars'] as $sidebar_id => $sidebar_data ) {

			// Delete sidebar. Checks for original (non-parsed) input data.
			if ( ! empty( $input['sidebars'][ $sidebar_id ]['delete'] ) ) {
				unset( $input['sidebars'][ $sidebar_id ] );
				unset( $data['sidebars'][ $sidebar_id ] );
				continue;
			}

			$sidebar = $data['sidebars'][ $sidebar_id ];

			$sidebar = array_merge(
				$this->get_default_sidebar_settings(),
				$sidebar
			);

			// Make sure unchecked checkboxes are 0 on save.
			$sidebar['enable']                    = self::validate_checkbox( $sidebar['enable'] );
			$sidebar['overwrite_global_settings'] = self::validate_checkbox( $sidebar['overwrite_global_settings'] );
			$sidebar['site_close']                = self::validate_checkbox( $sidebar['site_close'] );
			$sidebar['hide_control_classes']      = self::validate_checkbox( $sidebar['hide_control_classes'] );
			$sidebar['scroll_lock']               = self::validate_checkbox( $sidebar['scroll_lock'] );

			// Numeric values, not integers!
			$sidebar['padding']         = self::validate_numeric( $sidebar['padding'] );
			$sidebar['disable_over']    = self::validate_numeric( $sidebar['disable_over'] );
			$sidebar['animation_speed'] = self::validate_numeric( $sidebar['animation_speed'] );

			// Validate radio options.
			$sidebar['content'] = self::validate_radio( $sidebar['content'], array( 'sidebar', 'menu', 'action' ), 'sidebar' );

			$data['sidebars'][ $sidebar_id ] = $sidebar;

			$new_sidebar_id = self::validate_id( $sidebar_id );
			if ( $sidebar_id !== $new_sidebar_id ) {
				$data['sidebars'][ $new_sidebar_id ] = $data['sidebars'][ $sidebar_id ];
				$data['sidebars'][ $new_sidebar_id ]['id'] = $new_sidebar_id;

				unset( $data['sidebars'][ $sidebar_id ] );

				$this->migrate_sidebars_widgets( $sidebar_id, $new_sidebar_id );
			}
		} // End foreach().

		// Validate global settings with defaults.
		$data = $this->validate_settings( $data, $this->get_default_settings() );
		// Validate sidebar settings with defaults.
		foreach ( $data['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$data['sidebars'][ $sidebar_id ] = $this->validate_settings( $sidebar_settings, $this->get_default_sidebar_settings() );
		}

		unset( $data['ocs_tab'] );

		return $data;
	}

	/**
	 * Validates checkbox boolean values, used by validate_input().
	 *
	 * @since   0.4
	 * @param   mixed   $value
	 * @param   string  $key
	 * @return  bool
	 */
	public static function validate_numeric_boolean( $value, $key = '' ) {
		if ( $key ) {
			return (int) ( ! empty( $value[ $key ] ) );
		}
		return (int) ( ! empty( $value ) );
	}

	/**
	 * Validates checkbox values, used by validate_input().
	 *
	 * @since   0.1.2
	 * @param   mixed   $value
	 * @return  int
	 */
	public static function validate_checkbox( $value ) {
		return ( ! empty( $value ) ) ? (int) strip_tags( $value ) : 0;
	}

	/**
	 * Validates radio values against the possible options.
	 *
	 * @since   0.4
	 * @param   string  $value
	 * @param   array   $options
	 * @param   string  $default
	 * @return  int
	 */
	public static function validate_radio( $value, $options, $default ) {
		return ( ! empty( $value ) && in_array( $value, $options, true ) ) ? strip_tags( $value ) : $default;
	}

	/**
	 * Validates id values, used by validate_input.
	 *
	 * @since   0.2
	 * @since   0.3  Convert to lowercase and convert spaces to dashes before preg_replace().
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
	 * Remove whitespace.
	 *
	 * @since   0.3
	 * @param   mixed  $value
	 * @return  string
	 */
	public static function remove_whitespace( $value ) {
		return ( ! empty( $value ) ) ? str_replace( array( ' ' ), '', (string) $value ) : '';
	}

	/**
	 * Updates the existing widgets when a sidebar ID changes.
	 *
	 * @since   0.3
	 * @param   string  $old_id
	 * @param   string  $new_id
	 */
	public function migrate_sidebars_widgets( $old_id, $new_id ) {
		$old_id = 'off-canvas-' . $old_id;
		$new_id = 'off-canvas-' . $new_id;
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
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Settings
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
