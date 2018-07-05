<?php
/**
 * Off-Canvas Sidebars - Unit tests for plugin settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

class OCS_Settings_UnitTest extends WP_UnitTestCase {

	static function ocs_set_tab( $tab ) {
		OCS_UnitTest_Factory::load();
		OCS_UnitTest_Factory::load_admin( $tab );
	}

	function test_save() {

		self::ocs_set_tab( 'ocs-settings' );
		$settings = OCS_UnitTest_Factory::$settings;

		$defaults               = $settings->get_default_settings();
		$defaults['db_version'] = off_canvas_sidebars()->get_db_version();

		// Reset settings.
		$settings->set_settings( $defaults );

		/**
		 * Empty data.
		 */
		$this->assertEquals(
			$defaults,
			$settings->validate_form(
				array(
					// These fields are on by default so will generate differences on save (checkboxes).
					'enable_frontend' => 1,
					'site_close'      => 1,
					'link_close'      => 1,
				)
			)
		);

		/**
		 * Valid data.
		 */

		$new = array(
			'enable_frontend'               => 0,
			'site_close'                    => 0,
			'link_close'                    => 1,
			'disable_over'                  => '300',
			'background_color_type'         => 'color',
			'background_color'              => '#123456',
			'website_before_hook'           => 'test_before',
			'website_after_hook'            => 'test_after',
			'use_fastclick'                 => 1,
			'compatibility_position_fixed'  => 'legacy-css',
			'wp_editor_shortcode_rendering' => 1,
			'css_prefix'                    => 'test_ocs',
		);

		$compare = array_replace( $defaults, $new );

		// All valid values.
		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Invalid data.
		 */

		$new = array(
			// Invalid values.
			'enable_frontend'              => new stdClass(), // Parse as true (1)
			'site_close'                   => 123, // Parse as true (1)
			'link_close'                   => 321, // Parse as true (1)
			'disable_over'                 => '300.123', // Parsed as integer
			'background_color_type'        => 'color-123', // Invalid radio option, will return to default
			'background_color'             => '#123456123', // Invalid color, will be trimmed
			'website_before_hook'          => array(), // Invalid value, will return empty
			'website_after_hook'           => array(), // Invalid value, will return empty
			'use_fastclick'                => true, // Parse as int version (1)
			'compatibility_position_fixed' => 'legacy-css-yay', // Invalid radio option, will return to default
		);

		$compare = array_replace(
			$defaults,
			array(
				// Parsed values.
				'enable_frontend'              => 1,
				'site_close'                   => 1,
				'link_close'                   => 1,
				'disable_over'                 => '300',
				'background_color_type'        => '',
				'background_color'             => '#123456',
				'website_before_hook'          => '',
				'website_after_hook'           => '',
				'use_fastclick'                => 1,
				'compatibility_position_fixed' => 'none',
			)
		);

		// All values should be parsed correctly.
		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Overwrite or keep current settings.
		 */

		$current = array_replace( $defaults, array(
			'enable_frontend'       => 1,
			'site_close'            => 1,
			'link_close'            => 1,
			'disable_over'          => '300',
			'background_color_type' => 'color',
			'background_color'      => '#123456',
		) );

		$settings->set_settings( $current );

		$new = array(
			'enable_frontend'               => 1,
			'site_close'                    => 1,
			'link_close'                    => 0,
			'compatibility_position_fixed'  => 'legacy-css',
			'wp_editor_shortcode_rendering' => 1,
			'css_prefix'                    => 'test_ocs',
		);

		$compare = array_replace( $current, $new );

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		// @todo Change sidebar enabled. Use factory to set default sidebars?
		// @todo More validation types.

		/**
		 * SIDEBARS
		 * No new function because filters are reset.
		 *
		 * @fixme
		 */

		self::ocs_set_tab( 'ocs-sidebars' );
		$settings = OCS_UnitTest_Factory::$settings;

		$defaults = $settings->get_default_settings();
		$defaults['db_version'] = off_canvas_sidebars()->get_db_version();

		// Reset settings.
		$settings->set_settings( $defaults );

		/**
		 * Adding new sidebar through the front end form.
		 */

		$compare = $defaults;
		$compare['sidebars'] = array(
			'test' => $settings->get_default_sidebar_settings(),
		);
		// Overwrites by parser.
		$compare['sidebars']['test']['enable'] = 1;
		$compare['sidebars']['test']['label'] = 'Test';

		$new = array(
			'sidebars' => array(
				'_ocs_add_new' => 'Test',
			),
		);

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Adding new sidebar through the front end form without name should not work.
		 */

		$compare = $defaults;
		$compare['sidebars'] = array();

		$new = array(
			'sidebars' => array(
				'_ocs_add_new' => '',
			),
		);

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Adding new sidebar through code (ID only).
		 */

		$compare = $defaults;
		$compare['sidebars'] = array(
			'new' => $settings->get_default_sidebar_settings(),
		);

		$new = array(
			'sidebars' => array(
				'new' => array(),
			),
		);

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Valid data.
		 */

		$new = array(
			'sidebars' => array(
				'test' => array(
					// All valid, non default values.
					'enable'                    => 1,
					'label'                     => 'Test',
					'content'                   => 'menu',
					'location'                  => 'right',
					'style'                     => 'overlay',
					'size'                      => 'large',
					'size_input'                => '300',
					'size_input_type'           => 'px',
					'animation_speed'           => '5000',
					'padding'                   => '25',
					'background_color'          => '#123456',
					'background_color_type'     => 'color',
					// Global overwrites.
					'overwrite_global_settings' => 1,
					'site_close'                => 0,
					'link_close'                => 0,
					'disable_over'              => '500',
					'hide_control_classes'      => 1,
					'scroll_lock'               => 1,
				),
			),
		);

		$compare['sidebars'] = $new['sidebars'];

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Invalid data.
		 */

		$new = array(
			'sidebars' => array(
				'test' => array(
					// All invalid values.
					'enable'                    => 'yes', // Parse as true (1)
					'label'                     => 'Test <span>strip_tags test</span>',
					'content'                   => array( 'action' ),// Invalid radio option, will return to default
					'location'                  => 'nope', // Invalid radio option, will return to default
					'style'                     => false, // Invalid radio option, will return to default
					'size'                      => 'nope', // Invalid radio option, will return to default
					'size_input'                => true, // Invalid numeric value, will return empty
					'size_input_type'           => array(), // Invalid radio option, will return to default
					'animation_speed'           => -5000, // Negative number should be parsed as positive
					'padding'                   => 50.30, // Should be parsed as int string
					'background_color'          => '123', // Should be parsed as `#123`
					'background_color_type'     => false, // Invalid radio option, will return to default
					// Global overwrites.
					'overwrite_global_settings' => '', // Parse as false (0)
					'site_close'                => '505', // Parse as true (1)
					'link_close'                => 600, // Parse as true (1)
					'disable_over'              => '500.60', // Parse as int string
					'hide_control_classes'      => true, // Parse as true (1)
					'scroll_lock'               => false, // Parse as false (0)
				),
			),
		);

		$compare['sidebars']['test'] = array(
			// All parsed values.
			'enable'                    => 1,
			'label'                     => 'Test strip_tags test',
			'content'                   => 'sidebar',
			'location'                  => 'left',
			'style'                     => 'push',
			'size'                      => 'default',
			'size_input'                => '',
			'size_input_type'           => '%',
			'animation_speed'           => '5000',
			'padding'                   => '50',
			'background_color'          => '#123',
			'background_color_type'     => '',
			// Global overwrites.
			'overwrite_global_settings' => 0,
			'site_close'                => 1,
			'link_close'                => 1,
			'disable_over'              => '500',
			'hide_control_classes'      => 1,
			'scroll_lock'               => 0,
		);

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

		/**
		 * Overwrite or keep current settings.
		 */

		$current = array_replace( $defaults, array(
			'enable_frontend'       => 1,
			'site_close'            => 1,
			'link_close'            => 1,
			'disable_over'          => '300',
			'background_color_type' => 'color',
			'background_color'      => '#123456',
			'sidebars'              => array(
				'test' => array(
					// All valid, non default values.
					'enable'                    => 1,
					'label'                     => 'Test',
					'content'                   => 'menu',
					'location'                  => 'right',
					'style'                     => 'overlay',
					'size'                      => 'large',
					'size_input'                => '300',
					'size_input_type'           => 'px',
					'animation_speed'           => '5000',
					'padding'                   => '25',
					'background_color'          => '#123456',
					'background_color_type'     => 'color',
					// Global overwrites.
					'overwrite_global_settings' => 1,
					'site_close'                => 0,
					'link_close'                => 0,
					'disable_over'              => '500',
					'hide_control_classes'      => 1,
					'scroll_lock'               => 1,
				),
			),
		) );

		$settings->set_settings( $current );

		$new = array(
			'sidebars' => array(
				'test' => array(
					// All valid, non default values.
					'enable'                    => 0,
					'label'                     => 'Test rename',
					'content'                   => 'menu',
					'location'                  => 'left', // update
					'style'                     => 'push', // update
					'size'                      => 'large',
					'size_input'                => '300',
					'size_input_type'           => 'px',
					'animation_speed'           => '5000',
					'padding'                   => '25',
					'background_color'          => '', // update
					'background_color_type'     => 'color',
					// Global overwrites.
					'overwrite_global_settings' => 0, // update
					'site_close'                => 1, // update
					'link_close'                => 1, // update
					'disable_over'              => '', // update
					'hide_control_classes'      => 0, // update
					'scroll_lock'               => 0, // update
				),
				'test_2' => array(
					// All valid, non default values.
					'enable'                    => 1,
					'label'                     => 'Test 2',
					'content'                   => 'menu',
					'location'                  => 'right',
					'style'                     => 'overlay',
					'size'                      => 'large',
					'size_input'                => '300',
					'size_input_type'           => 'px',
					'animation_speed'           => '5000',
					'padding'                   => '25',
					'background_color'          => '#123456',
					'background_color_type'     => 'color',
					// Global overwrites.
					'overwrite_global_settings' => 1,
					'site_close'                => 0,
					'link_close'                => 0,
					'disable_over'              => '500',
					'hide_control_classes'      => 1,
					'scroll_lock'               => 1,
				),
			),
		);

		$compare = array_replace( $current, $new );

		$this->assertEquals( $compare, $settings->validate_form( $new ) );

	}
}
