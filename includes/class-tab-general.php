<?php
/**
 * Off-Canvas Sidebars - Class Tab_General
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab general
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5
 * @version 0.5
 */
final class OCS_Off_Canvas_Sidebars_Tab_General extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Tab_General
	 * @since  0.3
	 */
	protected static $_instance = null;

	/**
	 * @since   0.1
	 * @since   0.3  Private constructor.
	 * @since   0.5  Protected constructor. Refactor into separate tab classes and methods.
	 * @access  private
	 */
	protected function __construct() {
		$this->tab = 'ocs-settings';
		$this->name = esc_attr__( 'Settings', OCS_DOMAIN );
		parent::__construct();

		add_filter( 'ocs_settings_parse_input', array( $this, 'parse_input' ) );
		add_filter( 'ocs_settings_validate_input', array( $this, 'validate_input' ) );
	}

	/**
	 * Initialize this tab.
	 * @since   1.5
	 */
	public function init() {
		add_action( 'ocs_page_form_before', array( $this, 'ocs_page_form_before' ) );
	}

	/**
	 * Before form fields.
	 * @since   1.5
	 */
	public function ocs_page_form_before() {
		echo '<p>';
		echo sprintf(
			// Translators: %s stands for a URL.
			__( 'You can add the control buttons with a widget, menu item or with custom code, <a href="%s" target="_blank">click here for documentation.</a>', OCS_DOMAIN ),
			'https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/theme-setup'
		);
		echo '</p>';

		echo '<p>' . off_canvas_sidebars()->get_general_labels( 'compatibility_notice_theme' ) . '</p>';
	}

	/**
	 * Register settings.
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		parent::register_settings();

		add_settings_section(
			'section_general',
			esc_attr__( 'Global Settings', OCS_DOMAIN ),
			array( $this, 'register_general_settings' ),
			$this->tab
		);
		add_settings_section(
			'section_frontend',
			esc_attr__( 'Frontend Settings', OCS_DOMAIN ),
			array( $this, 'register_frontend_settings' ),
			$this->tab
		);
		add_settings_section(
			'section_admin',
			esc_attr__( 'Admin Settings', OCS_DOMAIN ),
			array( $this, 'register_admin_settings' ),
			$this->tab
		);

		do_action( 'off_canvas_sidebar_settings_general' );
	}

	/**
	 * General settings.
	 *
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_general_settings() {

		add_settings_field(
			'enabled_sidebars',
			esc_attr__( 'Enable Sidebars', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'enabled_sidebars_option' ),
			$this->tab,
			'section_general'
		);
	}

	/**
	 * Frontend settings.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @todo Refactor to enable above checks?
	 *
	 * @since 0.1
	 * @since 0.5 Refactor into separate tab classes and methods
	 */
	public function register_frontend_settings() {

		add_settings_field(
			'enable_frontend',
			esc_attr__( 'Enable front-end', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'enable_frontend',
				'label' => __( 'Let this plugin add the necessary elements on the front-end.', OCS_DOMAIN ),
				'description' => '<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/theme-setup" target="_blank">'
				                 . __( 'Read this to setup your theme for support!', OCS_DOMAIN ) . '</a>',
				//(Themes based on the Genesis Framework are supported by default)
			)
		);
		/*add_settings_field(
			'frontend_type',
			esc_attr__( 'Front-end type', OCS_DOMAIN ),
			array( $this, 'frontend_type_option' ),
			$this->tab,
			'section_frontend'
		);*/
		add_settings_field(
			'css_prefix',
			esc_attr__( 'CSS Prefix', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'text_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'css_prefix',
				'label' => __( 'Default', OCS_DOMAIN ) . ': <code>ocs</code>',
				'placeholder' => 'ocs',
			)
		);
		add_settings_field(
			'site_close',
			esc_attr__( 'Close sidebar when clicking on the site', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'site_close',
				'label' => __( 'Enables closing of the off-canvas sidebar by clicking on the site.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
			)
		);
		add_settings_field(
			'link_close',
			esc_attr__( 'Close sidebar when clicking on a link', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'link_close',
				'label' => __( 'Enables closing of the off-canvas sidebar by clicking on a link.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
			)
		);
		add_settings_field(
			'disable_over',
			esc_attr__( 'Disable over', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'number_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'disable_over',
				'label' => __( 'Disable off-canvas sidebars over specified screen width.', OCS_DOMAIN ),
				'description' => __( 'Leave blank to disable.', OCS_DOMAIN ),
				'input_after' => '<code>px</code>',
			)
		);
		add_settings_field(
			'hide_control_classes',
			esc_attr__( 'Auto-hide control classes', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'hide_control_classes',
				'label' => __( 'Hide off-canvas sidebar control classes over width specified in <strong>"Disable over"</strong>.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
			)
		);
		add_settings_field(
			'scroll_lock',
			esc_attr__( 'Scroll lock', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'scroll_lock',
				'label' => __( 'Prevent site content scrolling whilst a off-canvas sidebar is open.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
			)
		);
		add_settings_field(
			'background_color',
			esc_attr__( 'Background color', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'color_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'background_color',
				'description' => __( 'Choose a background color for the site container.', OCS_DOMAIN )
				                 . '<br>' . __( 'Default', OCS_DOMAIN ) . ': <code>#ffffff</code>.',
			)
		);
		// Genesis already has before and after hooks set.
		if ( get_template() !== 'genesis' ) {
			// Check if the before hook is filtered. If it is this setting is not needed.
			if ( '' === apply_filters( 'ocs_website_before_hook', '' ) ) {
				add_settings_field(
					'website_before_hook',
					'<code>website_before</code> ' . esc_attr__( 'hook name', OCS_DOMAIN ),
					array( 'OCS_Off_Canvas_Sidebars_Form', 'text_option' ),
					$this->tab,
					'section_frontend',
					array(
						'name'        => 'website_before_hook',
						'placeholder' => 'website_before',
					)
				);
			}
			// Check if the after hook is filtered. If it is this setting is not needed.
			if ( '' === apply_filters( 'ocs_website_after_hook', '' ) ) {
				add_settings_field(
					'website_after_hook',
					'<code>website_after</code> ' . esc_attr__( 'hook name', OCS_DOMAIN ),
					array( 'OCS_Off_Canvas_Sidebars_Form', 'text_option' ),
					$this->tab,
					'section_frontend',
					array(
						'name'        => 'website_after_hook',
						'placeholder' => 'website_after',
					)
				);
			}
		}
		// https://github.com/ftlabs/fastclick
		add_settings_field(
			'use_fastclick',
			esc_attr__( 'Use the FastClick library?', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'use_fastclick',
				'label' => __( 'Yes', OCS_DOMAIN ),
				'description' => __( 'Devices with touch capability often have a 300ms delay on click triggers. FastClick is a JavaScript library purposely built to elimate the delay where neccesary.', OCS_DOMAIN )
				                 . '<br>' . __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
			)
		);

		add_settings_field(
			'compatibility_position_fixed',
			esc_attr__( 'Compatibility for fixed elements', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'radio_option' ),
			$this->tab,
			'section_frontend',
			array(
				'name' => 'compatibility_position_fixed',
				'default' => 'none',
				'options' => array(
					'none' => array(
						'name' => 'none',
						'label' => __( 'No', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Use CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
						'value' => 'none',
						'description' => __( 'This is the default Slidebars behaviour.', OCS_DOMAIN ),
					),
					'legacy-css' => array(
						'name' => 'legacy-css',
						'label' => __( 'Legacy CSS solution', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Use basic CSS positioning instead of CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
						'value' => 'legacy-css',
						'description' => __( 'This is your best option if your site uses sticky menus and/or other fixed elements within the site container.', OCS_DOMAIN ),
					),
					'custom-js' => array(
						'name' => 'custom-js',
						'label' => __( 'JavaScript solution', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Experimental', OCS_DOMAIN ) . ')',
						'value' => 'custom-js',
						'description' => __( 'While still in development, this could fix compatibility issues with fixed elements.', OCS_DOMAIN ),
					),
				),
			)
		);
	}

	/**
	 * Admin settings.
	 *
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_admin_settings() {

		add_settings_field(
			'wp_editor_shortcode_rendering',
			esc_attr__( 'Enable shortcode UI for the WordPress Editor?', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_admin',
			array(
				'name' => 'wp_editor_shortcode_rendering',
				'label' => __( 'Yes', OCS_DOMAIN ) . ' (<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/issues/32" target="_blank">' . __( 'Experimental', OCS_DOMAIN ) . '</a>)',
				'description' => __( 'This will render the shortcodes to actual HTML elements in the WP Editor.', OCS_DOMAIN ),
			)
		);
	}

	/**
	 * Parses general post values, checks all values with the current existing data.
	 * @since   0.5
	 * @param   array  $input  Form input.
	 * @return  array
	 */
	public function parse_input( $input ) {
		if ( ! $this->is_request_tab() ) {
			return $input;
		}

		$checkbox_keys = array(
			'enable_frontend',
			'site_close',
			'link_close',
			'hide_control_classes',
			'scroll_lock',
			'use_fastclick',
			'wp_editor_shortcode_rendering',
		);

		// Check checkboxes or they will be overwritten with the current settings.
		foreach ( $checkbox_keys as $key ) {
			$input[ $key ] = OCS_Off_Canvas_Sidebars_Settings::validate_numeric_boolean( $input, $key );
		}

		return $input;
	}

	/**
	 * @since   0.5
	 * @param   array  $data
	 * @return  array
	 */
	public function validate_input( $data ) {
		if ( ! $this->is_request_tab() ) {
			return $data;
		}

		// Checkboxes already done in parse_input().

		// Numeric values, not integers!
		$data['disable_over'] = OCS_Off_Canvas_Sidebars_Settings::validate_numeric( $data['disable_over'] );

		// Remove whitespaces.
		$data['website_before_hook'] = OCS_Off_Canvas_Sidebars_Settings::remove_whitespace( $data['website_before_hook'] );
		$data['website_after_hook']  = OCS_Off_Canvas_Sidebars_Settings::remove_whitespace( $data['website_after_hook'] );

		// Attribute validation.
		$data['css_prefix'] = OCS_Off_Canvas_Sidebars_Settings::validate_id( $data['css_prefix'] );

		// Validate radio options.
		$data['compatibility_position_fixed'] = OCS_Off_Canvas_Sidebars_Settings::validate_radio(
			$data['compatibility_position_fixed'],
			array( 'none', 'custom-js', 'legacy-css' ),
			'none'
		);

		// Set default values if no value is set.
		if ( empty( $data['css_prefix'] ) ) {
			$data['css_prefix'] = 'ocs';
		}

		return $data;
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Tab_General
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
