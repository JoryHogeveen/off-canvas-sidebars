<?php
/**
 * Off-Canvas Sidebars - Unit tests
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

class OCS_UnitTest extends WP_UnitTestCase {

	// Check that that activation doesn't break
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( TEST_OCS_PLUGIN_PATH ) );
	}
}
