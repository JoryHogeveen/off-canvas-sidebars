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

		// @todo Check compare with current (non-default) settings.
		// @todo Change sidebar enabled. Use factory to set default sidebars?
		// @todo More validation types.
	}
}
