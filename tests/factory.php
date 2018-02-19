<?php
/**
 * Off-Canvas Sidebars - Unit tests for plugin settings
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

class OCS_UnitTest_Factory {

	/**
	 * The single instance of the class.
	 *
	 * @static
	 * @var    OCS_UnitTest_Factory
	 */
	private static $_instance = null;

	/**
	 * @var OCS_Off_Canvas_Sidebars
	 */
	public static $ocs = null;

	/**
	 * @var OCS_Off_Canvas_Sidebars_Settings
	 */
	public static $settings = null;

	/**
	 * @var OCS_Off_Canvas_Sidebars_Page
	 */
	public static $page = null;

	protected function __construct() {}

	static function load() {
		static $done;
		if ( $done ) {
			return;
		}

		self::$ocs = off_canvas_sidebars();

		self::$settings = OCS_Off_Canvas_Sidebars_Settings::get_instance();

		$admin = true; // @todo Frontend only tests?
		if ( $admin ) {
			self::load_admin();
		}

		$done = true;
	}

	static function load_admin() {
		// Load the settings page.
		include_once OCS_PLUGIN_DIR . 'includes/class-form.php';
		include_once OCS_PLUGIN_DIR . 'includes/class-page.php';

		self::$page = OCS_Off_Canvas_Sidebars_Page::get_instance();
	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
