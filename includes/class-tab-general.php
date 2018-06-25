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
 * @since   0.5.0
 * @version 0.5.0
 * @uses    \OCS_Off_Canvas_Sidebars_Tab Extends class
 */
final class OCS_Off_Canvas_Sidebars_Tab_General extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Tab_General
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	/**
	 * Class constructor.
	 * @since   0.1.0
	 * @since   0.3.0  Private constructor.
	 * @since   0.5.0  Protected constructor. Refactor into separate tab classes and methods.
	 * @access  protected
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
	 * @since   1.5.0
	 */
	public function init() {
		add_action( 'ocs_page_form_before', array( $this, 'ocs_page_form_before' ) );
	}

	/**
	 * Before form fields.
	 * @since   1.5.0
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
	 * @since   0.1.0
	 * @since   0.5.0  Refactor into separate tab classes and methods.
	 */
	public function register_settings() {
		parent::register_settings();

		add_settings_section(
			'section_general',
			esc_attr__( 'Global Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);
		add_settings_section(
			'section_frontend',
			esc_attr__( 'Frontend Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);
		add_settings_section(
			'section_admin',
			esc_attr__( 'Admin Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);

		foreach ( $this->get_tab_fields() as $key => $field ) {
			$this->add_settings_field( $key, $field );
		}

		do_action( 'off_canvas_sidebar_settings_' . $this->filter );
	}

	/**
	 * Parses general post values, checks all values with the current existing data.
	 * @since   0.5.0
	 * @param   array  $input  Form input.
	 * @return  array
	 */
	public function parse_input( $input ) {
		if ( ! $this->is_request_tab() ) {
			return $input;
		}

		// Check checkboxes or they will be overwritten with the current settings.
		foreach ( $this->get_settings_fields_by_type( 'checkbox', true ) as $key ) {
			$input[ $key ] = OCS_Off_Canvas_Sidebars_Settings::validate_checkbox( $input, $key );
		}

		return $input;
	}

	/**
	 * @since   0.5.0
	 * @param   array  $data
	 * @return  array
	 */
	public function validate_input( $data ) {
		if ( ! $this->is_request_tab() ) {
			return $data;
		}

		$data = OCS_Off_Canvas_Sidebars_Settings::validate_fields( $data, $this->get_settings_fields() );

		// Set default values if no value is set.
		if ( empty( $data['css_prefix'] ) ) {
			$data['css_prefix'] = 'ocs';
		}

		return $data;
	}

	/**
	 * Register tab fields.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.5.0
	 */
	protected function get_tab_fields() {

		$fields = array();

		$section = 'section_general';

		$fields['sidebars'] = array(
			'title'    => esc_attr__( 'Enable Sidebars', OCS_DOMAIN ),
			'name'     => 'sidebars',
			'callback' => 'enabled_sidebars_option',
			'validate' => false,
			'section'  => $section,
		);

		$section = 'section_frontend';

		$fields['enable_frontend'] = array(
			'title'       => esc_attr__( 'Enable front-end', OCS_DOMAIN ),
			'name'        => 'enable_frontend',
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Let this plugin add the necessary elements on the front-end.', OCS_DOMAIN ),
			'description' => '<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/theme-setup" target="_blank">'
			                 . __( 'Read this to setup your theme!', OCS_DOMAIN ) . '</a>',
			//(Themes based on the Genesis Framework are supported by default)
		);

		/*$fields['frontend_type'] = array(
			'title'    => esc_attr__( 'Front-end type', OCS_DOMAIN ),
			'callback' => array( $this, 'frontend_type_option' ),
			'section'  => $section,
		);*/

		$fields['css_prefix'] = array(
			'name'        => 'css_prefix',
			'title'       => esc_attr__( 'CSS Prefix', OCS_DOMAIN ),
			'callback'    => 'text_option',
			'validate'    => 'validate_id',
			'type'        => 'text',
			'section'     => $section,
			'label'       => __( 'Default', OCS_DOMAIN ) . ': <code>ocs</code>',
			'placeholder' => 'ocs',
		);

		$fields['site_close'] = array(
			'name'        => 'site_close',
			'title'       => esc_attr__( 'Close sidebar when clicking on the site', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Enable', OCS_DOMAIN ) . '.',
			'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
		);

		$fields['link_close'] = array(
			'name'        => 'link_close',
			'title'       => esc_attr__( 'Close sidebar when clicking on a link', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Enable', OCS_DOMAIN ) . '.',
			'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
		);

		$fields['disable_over'] = array(
			'name'        => 'disable_over',
			'title'       => esc_attr__( 'Disable over', OCS_DOMAIN ),
			'callback'    => 'number_option',
			'type'        => 'number',
			'section'     => $section,
			'label'       => __( 'Disable off-canvas sidebars over specified screen width.', OCS_DOMAIN ),
			'description' => __( 'Leave blank to disable.', OCS_DOMAIN ),
			'input_after' => '<code>px</code>',
		);

		$fields['hide_control_classes'] = array(
			'name'        => 'hide_control_classes',
			'title'       => esc_attr__( 'Auto-hide control classes', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Hide off-canvas sidebar control classes over width specified in <strong>"Disable over"</strong>.', OCS_DOMAIN ),
			'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
		);

		$fields['scroll_lock'] = array(
			'name'        => 'scroll_lock',
			'title'       => esc_attr__( 'Scroll lock', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Prevent site content scrolling whilst a off-canvas sidebar is open.', OCS_DOMAIN ),
			'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
		);

		// @todo Auto handler for radio options with a custom v,
		$fields['background_color'] = array(
			'name'        => 'background_color',
			'title'       => esc_attr__( 'Background color', OCS_DOMAIN ),
			'callback'    => 'color_option',
			'type'        => 'color',
			'section'     => $section,
			'description' =>
				__( 'Choose a background color for the site container.', OCS_DOMAIN )
				. '<br>' . __( 'Default', OCS_DOMAIN ) . ': <code>#ffffff</code>.<br>' .
				__( 'You can overwrite this with CSS', OCS_DOMAIN ),
		);
		// @fixme See above. This makes sure the fields gets recognized.
		$fields['background_color_type'] = array(
			'name'    => 'background_color_type',
			'hidden'  => true,
			'type'    => 'radio',
			'section' => $section,
			'default' => '',
			'options' => array(
				'default' => array(
					'name'  => 'default',
					'label' => esc_html__( 'Default', OCS_DOMAIN ) . ': <code>#ffffff</code>',
					'value' => '',
				),
				'transparent' => array(
					'name'  => 'transparent',
					'label' => esc_html__( 'Transparent', OCS_DOMAIN ),
					'value' => 'transparent',
				),
				'color' => array(
					'name'  => 'color',
					'label' => esc_html__( 'Color', OCS_DOMAIN ),
					'value' => 'color',
				),
			),
		);

		// Genesis already has before and after hooks set.
		if ( get_template() !== 'genesis' ) {
			// Check if the before hook is filtered. If it is this setting is not needed.
			if ( '' === apply_filters( 'ocs_website_before_hook', '' ) ) {
				$fields['website_before_hook'] = array(
					'name'        => 'website_before_hook',
					'title'       => '<code>website_before</code> ' . esc_attr__( 'hook name', OCS_DOMAIN ),
					'callback'    => 'text_option',
					'type'        => 'text',
					'validate'    => 'remove_whitespace',
					'section'     => $section,
					'placeholder' => 'website_before',
				);
			}
			// Check if the after hook is filtered. If it is this setting is not needed.
			if ( '' === apply_filters( 'ocs_website_after_hook', '' ) ) {
				$fields['website_after_hook'] = array(
					'name'        => 'website_after_hook',
					'title'       => '<code>website_after</code> ' . esc_attr__( 'hook name', OCS_DOMAIN ),
					'callback'    => 'text_option',
					'type'        => 'text',
					'validate'    => 'remove_whitespace',
					'section'     => $section,
					'placeholder' => 'website_after',
				);
			}
		}

		// https://github.com/ftlabs/fastclic
		$fields['use_fastclick'] = array(
			'name'        => 'use_fastclick',
			'title'       => esc_attr__( 'Use the FastClick library?', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => __( 'Yes', OCS_DOMAIN ),
			'description' => __( 'Devices with touch capability often have a 300ms delay on click triggers. FastClick is a JavaScript library purposely built to elimate the delay where neccesary.', OCS_DOMAIN )
			                 . '<br>' . __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
		);

		$fields['compatibility_position_fixed'] = array(
			'name'     => 'compatibility_position_fixed',
			'title'    => esc_attr__( 'Compatibility for fixed elements', OCS_DOMAIN ),
			'callback' => 'radio_option',
			'type'     => 'radio',
			'section'  => $section,
			'default'  => 'none',
			'options'  => array(
				'none' => array(
					'name'  => 'none',
					'label' => __( 'No', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Use CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
					'value' => 'none',
					'description' => __( 'This is the default Slidebars behaviour.', OCS_DOMAIN ),
				),
				'legacy-css' => array(
					'name'  => 'legacy-css',
					'label' => __( 'Legacy CSS solution', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Use basic CSS positioning instead of CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
					'value' => 'legacy-css',
					'description' => __( 'This is your best option if your site uses sticky menus and/or other fixed elements within the site container.', OCS_DOMAIN ),
				),
				'custom-js' => array(
					'name'  => 'custom-js',
					'label' => __( 'JavaScript solution', OCS_DOMAIN ) . ' &nbsp; (' . __( 'Experimental', OCS_DOMAIN ) . ')',
					'value' => 'custom-js',
					'description' => __( 'While still in development, this could fix compatibility issues with fixed elements.', OCS_DOMAIN ),
				),
			),
		);

		$section = 'section_admin';

		$fields['wp_editor_shortcode_rendering'] = array(
			'name'     => 'wp_editor_shortcode_rendering',
			'title'    => esc_attr__( 'Enable shortcode UI for the WordPress Editor?', OCS_DOMAIN ),
			'callback' => 'checkbox_option',
			'type'     => 'checkbox',
			'section'  => $section,
			'label'    => __( 'Yes', OCS_DOMAIN ) . ' (<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/issues/32" target="_blank">' . __( 'Experimental', OCS_DOMAIN ) . '</a>)',
			'description' => __( 'This will render the shortcodes to actual HTML elements in the WP Editor.', OCS_DOMAIN ),
		);

		return $fields;
	}



	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Tab_General
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
