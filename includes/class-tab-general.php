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
 * @version 0.5.6
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
		$this->tab  = 'ocs-settings';
		$this->name = esc_html__( 'Settings', OCS_DOMAIN );
		parent::__construct();

		add_filter( 'ocs_settings_parse_input', array( $this, 'parse_input' ) );
		add_filter( 'ocs_settings_validate_input', array( $this, 'validate_input' ) );
	}

	/**
	 * Initialize this tab.
	 * @since   0.5.0
	 */
	public function init() {
		add_action( 'ocs_page_form_before', array( $this, 'ocs_page_form_before' ) );
	}

	/**
	 * Before form fields.
	 * @since   0.5.0
	 */
	public function ocs_page_form_before() {
		echo '<p>';
		echo esc_html__( 'You can add the control buttons with a widget, menu item or with custom code.', OCS_DOMAIN );
		echo ' <a href="%s" target="_blank" rel="noopener noreferrer">' . esc_html__( 'click here for documentation.', OCS_DOMAIN ) . '</a>';
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

		/*add_settings_section(
			'section_general',
			esc_html__( 'Global Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);*/
		add_settings_section(
			'section_setup',
			esc_html__( 'Theme Setup Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);
		add_settings_section(
			'section_frontend',
			esc_html__( 'Frontend Settings', OCS_DOMAIN ),
			array( $this, 'register_section_fields' ),
			$this->tab
		);
		add_settings_section(
			'section_admin',
			esc_html__( 'Admin Settings', OCS_DOMAIN ),
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

		//$section = 'section_general';

		$section = 'section_setup';

		$fields['enable_frontend'] = array(
			'title'       => esc_html__( 'Enable front-end', OCS_DOMAIN ),
			'name'        => 'enable_frontend',
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Let this plugin add the necessary elements on the front-end.', OCS_DOMAIN ) . ' (' . esc_html__( 'Recommended', OCS_DOMAIN ) . ')',
			'description' => '<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/theme-setup" target="_blank" rel="noopener noreferrer">'
			                 . esc_html__( 'Read this to setup your theme!', OCS_DOMAIN ) . '</a>',
		);

		$theme_hooks_wiki = esc_html__( 'Click here for a list of currently known compatible theme hooks', OCS_DOMAIN );
		$theme_hooks_wiki = '<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Compatible-theme-hooks" target="_blank" rel="noopener noreferrer">' . $theme_hooks_wiki . '</a>';

		$before_hook = 'wp_body_open';
		$after_hook  = 'wp_footer';
		if ( 'genesis' === get_template() ) {
			$before_hook = 'genesis_before';
			$after_hook  = 'genesis_after';
		}

		// Check if the before hook is filtered. If it is this setting is not needed.
		if ( '' === apply_filters( 'ocs_website_before_hook', '' ) ) {

			$fields['website_before_hook'] = array(
				'name'        => 'website_before_hook',
				'title'       => '<code>website_before</code> ' . esc_html__( 'hook name', OCS_DOMAIN ),
				'callback'    => 'hook_option',
				'type'        => 'text',
				'validate'    => 'remove_whitespace',
				'section'     => $section,
				'placeholder' => $before_hook,
				'description' => $theme_hooks_wiki,
			);

			$fields['website_before_hook_priority'] = array(
				'name'    => 'website_before_hook_priority',
				'hidden'  => true,
				'default' => '',
				'type'    => 'number',
			);
		}

		// Check if the after hook is filtered. If it is this setting is not needed.
		if ( '' === apply_filters( 'ocs_website_after_hook', '' ) ) {

			$fields['website_after_hook'] = array(
				'name'        => 'website_after_hook',
				'title'       => '<code>website_after</code> ' . esc_html__( 'hook name', OCS_DOMAIN ),
				'callback'    => 'hook_option',
				'type'        => 'text',
				'validate'    => 'remove_whitespace',
				'section'     => $section,
				'placeholder' => $after_hook,
				'description' => $theme_hooks_wiki,
			);

			$fields['website_after_hook_priority'] = array(
				'name'    => 'website_after_hook_priority',
				'hidden'  => true,
				'default' => '',
				'type'    => 'number',
			);
		}

		/*$fields['frontend_type'] = array(
			'title'    => esc_html__( 'Front-end type', OCS_DOMAIN ),
			'callback' => array( $this, 'frontend_type_option' ),
			'section'  => $section,
		);*/

		$fields['_setup_validate'] = array(
			'title'       => esc_html__( 'Validation', OCS_DOMAIN ),
			'type'        => 'help',
			'callback'    => 'do_button',
			'link'        => OCS_Off_Canvas_Sidebars_Setup::get_instance()->get_validator_link(),
			'target'      => '_blank',
			'label'       => esc_html__( 'Validate hooks setup', OCS_DOMAIN ),
			'description' => esc_html__( 'Only validates if the hooks are fired, not if they are correctly placed.', OCS_DOMAIN ),
			'section'     => $section,
		);

		$fields['css_prefix'] = array(
			'name'        => 'css_prefix',
			'title'       => esc_html__( 'CSS Prefix', OCS_DOMAIN ),
			'callback'    => 'text_option',
			'validate'    => 'validate_id',
			'type'        => 'text',
			'section'     => $section,
			'label'       => esc_html__( 'Default', OCS_DOMAIN ) . ': <code>ocs</code>',
			'placeholder' => 'ocs',
		);

		$fields['late_init'] = array(
			'title'    => esc_html__( 'Late init', OCS_DOMAIN ),
			'name'     => 'late_init',
			'callback' => 'checkbox_option',
			'type'     => 'checkbox',
			'section'  => $section,
			'label'    => esc_html__( 'Wait for window to be loaded before initializing Off-Canvas Sidebars.', OCS_DOMAIN ),
		);

		$fields['compatibility_position_fixed'] = array(
			'name'     => 'compatibility_position_fixed',
			'title'    => esc_html__( 'Compatibility for fixed elements', OCS_DOMAIN ),
			'callback' => 'radio_option',
			'type'     => 'radio',
			'section'  => $section,
			'default'  => 'none',
			'options'  => array(
				'none'       => array(
					'name'        => 'none',
					'label'       => esc_html__( 'No', OCS_DOMAIN ) . ' &nbsp; (' . esc_html__( 'Use CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
					'value'       => 'none',
					'description' => esc_html__( 'This is the default Slidebars behaviour.', OCS_DOMAIN ),
				),
				'legacy-css' => array(
					'name'        => 'legacy-css',
					'label'       => esc_html__( 'Legacy CSS solution', OCS_DOMAIN ) . ' &nbsp; (' . esc_html__( 'Use basic CSS positioning instead of CSS3 transform with hardware acceleration', OCS_DOMAIN ) . ')',
					'value'       => 'legacy-css',
					'description' => esc_html__( 'This is your best option if your site uses sticky menus and/or other fixed elements within the site container.', OCS_DOMAIN ),
				),
				'custom-js'  => array(
					'name'        => 'custom-js',
					'label'       => esc_html__( 'JavaScript solution', OCS_DOMAIN ) . ' &nbsp; (' . esc_html__( 'Experimental', OCS_DOMAIN ) . ')',
					'value'       => 'custom-js',
					'description' => esc_html__( 'While still in development, this could fix compatibility issues with fixed elements.', OCS_DOMAIN ),
				),
			),
		);

		$section = 'section_frontend';

		$fields['sidebars'] = array(
			'title'    => esc_html__( 'Enabled Sidebars', OCS_DOMAIN ),
			'name'     => 'sidebars',
			'callback' => 'enabled_sidebars_option',
			'validate' => false,
			'section'  => $section,
		);

		$fields['site_close'] = array(
			'name'        => 'site_close',
			'title'       => esc_html__( 'Close sidebar when clicking on the site', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Enable', OCS_DOMAIN ) . '.',
			'description' => esc_html__( 'Default', OCS_DOMAIN ) . ': ' . esc_html__( 'enabled', OCS_DOMAIN ) . '.',
		);

		$fields['link_close'] = array(
			'name'        => 'link_close',
			'title'       => esc_html__( 'Close sidebar when clicking on a link', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Enable', OCS_DOMAIN ) . '.',
			'description' => esc_html__( 'Default', OCS_DOMAIN ) . ': ' . esc_html__( 'disabled', OCS_DOMAIN ) . '.',
		);

		$fields['disable_over'] = array(
			'name'        => 'disable_over',
			'title'       => esc_html__( 'Disable over', OCS_DOMAIN ),
			'callback'    => 'number_option',
			'type'        => 'number',
			'section'     => $section,
			'label'       => esc_html__( 'Disable off-canvas sidebars over specified screen width.', OCS_DOMAIN ),
			'description' => esc_html__( 'Leave blank to disable.', OCS_DOMAIN ),
			'input_after' => '<code>px</code>',
			'min'         => 0,
		);

		$fields['hide_control_classes'] = array(
			'name'        => 'hide_control_classes',
			'title'       => esc_html__( 'Auto-hide control triggers', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Hide off-canvas sidebar control triggers if the sidebar is disabled.', OCS_DOMAIN ),
			'description' => esc_html__( 'Default', OCS_DOMAIN ) . ': ' . esc_html__( 'disabled', OCS_DOMAIN ) . '.',
		);

		$fields['scroll_lock'] = array(
			'name'        => 'scroll_lock',
			'title'       => esc_html__( 'Scroll lock', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Prevent site content scrolling whilst a off-canvas sidebar is open.', OCS_DOMAIN ),
			'description' => esc_html__( 'Default', OCS_DOMAIN ) . ': ' . esc_html__( 'disabled', OCS_DOMAIN ) . '.',
		);

		// @todo Auto handler for radio options with a custom v,
		$fields['background_color'] = array(
			'name'        => 'background_color',
			'title'       => esc_html__( 'Background color', OCS_DOMAIN ),
			'callback'    => 'color_option',
			'type'        => 'color',
			'section'     => $section,
			'description' =>
				esc_html__( 'Choose a background color for the site container.', OCS_DOMAIN )
				. '<br>' . esc_html__( 'Default', OCS_DOMAIN ) . ': <code>#ffffff</code>.<br>' .
				esc_html__( 'You can overwrite this with CSS', OCS_DOMAIN ),
		);
		// @fixme See above. This makes sure the fields gets recognized.
		$fields['background_color_type'] = array(
			'name'    => 'background_color_type',
			'hidden'  => true,
			'type'    => 'radio',
			'section' => $section,
			'default' => '',
			'options' => array(
				'default'     => array(
					'name'  => 'default',
					'label' => esc_html__( 'Default', OCS_DOMAIN ) . ': <code>#ffffff</code>',
					'value' => '',
				),
				'transparent' => array(
					'name'  => 'transparent',
					'label' => esc_html__( 'Transparent', OCS_DOMAIN ),
					'value' => 'transparent',
				),
				'color'       => array(
					'name'  => 'color',
					'label' => esc_html__( 'Color', OCS_DOMAIN ),
					'value' => 'color',
				),
			),
		);

		// https://github.com/ftlabs/fastclic
		$fields['use_fastclick'] = array(
			'name'        => 'use_fastclick',
			'title'       => esc_html__( 'Use the FastClick library?', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Yes', OCS_DOMAIN ),
			'description' => esc_html__( 'Devices with touch capability often have a 300ms delay on click triggers. FastClick is a JavaScript library purposely built to elimate the delay where neccesary.', OCS_DOMAIN )
			                 . '<br>' . esc_html__( 'Default', OCS_DOMAIN ) . ': ' . esc_html__( 'disabled', OCS_DOMAIN ) . '.',
		);

		$section = 'section_admin';

		$fields['wp_editor_shortcode_rendering'] = array(
			'name'        => 'wp_editor_shortcode_rendering',
			'title'       => esc_html__( 'Enable shortcode UI for the WordPress Editor?', OCS_DOMAIN ),
			'callback'    => 'checkbox_option',
			'type'        => 'checkbox',
			'section'     => $section,
			'label'       => esc_html__( 'Yes', OCS_DOMAIN ) . ' (<a href="https://github.com/JoryHogeveen/off-canvas-sidebars/issues/32" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Experimental', OCS_DOMAIN ) . '</a>)',
			'description' => esc_html__( 'This will render the shortcodes to actual HTML elements in the WP Editor.', OCS_DOMAIN ),
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
