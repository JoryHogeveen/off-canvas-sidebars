<?php
/**
 * Off-Canvas Sidebars - Class Setup
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin setup
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.6
 * @version 0.5.6
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Setup extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Setup
	 * @since  0.5.6
	 */
	protected static $_instance = null;

	/**
	 * Class constructor.
	 *
	 * @since   0.5.6
	 * @access  private
	 */
	private function __construct() {
		if ( ! OCS_Off_Canvas_Sidebars_Page::get_instance()->has_access() ) {
			return;
		}
		if ( isset( $_GET['ocs-setup-validate'] ) ) {
			$this->run_validation();
		}
	}

	/**
	 * Add validation hooks.
	 */
	public function run_validation() {
		add_action( 'ocs_container_after', array( $this, 'action_ocs_container_after' ) );
	}

	/**
	 * Add element to validate after_site hook.
	 */
	public function action_ocs_container_after() {
		echo '<div id="ocs_validate_website_after"></div>';
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.5.6
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Setup
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
