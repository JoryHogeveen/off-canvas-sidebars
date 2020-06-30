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
	 * Get parameter to trigger validator.
	 *
	 * @var string
	 */
	protected $param = 'ocs-setup-validate';

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
		// @codingStandardsIgnoreLine
		if ( isset( $_GET[ $this->param ] ) ) {
			$this->run_validation();
		}
	}

	/**
	 * Add validation hooks.
	 * @since  0.5.6
	 */
	public function run_validation() {
		add_action( 'ocs_container_after', array( $this, 'action_ocs_container_after' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add element to validate after_site hook.
	 * @since  0.5.6
	 */
	public function action_ocs_container_after() {
		echo '<div id="ocs_validate_website_after"></div>';
	}

	/**
	 * Add validation scripts.
	 * @since  0.5.6
	 */
	public function enqueue_assets() {

		// @todo Validate and use minified files
		$suffix  = '';//defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : OCS_PLUGIN_VERSION;

		wp_enqueue_script( 'off-canvas-sidebars-setup-validate', OCS_PLUGIN_URL . 'js/setup-validate' . $suffix . '.js', array( 'jquery' ), $version, true );

		$before_hook = off_canvas_sidebars_frontend()->get_website_before_hook();
		$after_hook  = off_canvas_sidebars_frontend()->get_website_after_hook();

		wp_localize_script(
			'off-canvas-sidebars-setup-validate',
			'ocsSetupValidate',
			array(
				'messages'   => array(
					// Translators: %s stands for the hook name.
					'error_website_before' => sprintf( esc_html__( '%s is not fired', OCS_DOMAIN ), '<code>"' . $before_hook . '"</code> hook' ),
					// Translators: %s stands for the hook name.
					'error_website_after'  => sprintf( esc_html__( '%s is not fired', OCS_DOMAIN ), '<code>"' . $after_hook . '"</code> hook' ),
					'error_canvas_child'   => esc_html__( 'Website canvas container is not a direct child of the HTML body tag.', OCS_DOMAIN ),
					'error_canvas_empty'   => esc_html__( 'Website canvas container is empty.', OCS_DOMAIN ),
					'error_sidebars_child' => esc_html__( 'Off-Canvas Sidebars are not direct children of the HTML body tag.', OCS_DOMAIN ),
					'errors_found'         => esc_html__( 'Errors found!', OCS_DOMAIN ),
					'hooks_incorrect'      => esc_html__( 'Please check your hooks and priorities.', OCS_DOMAIN ),
					// Translators: %s stands for the hook names.
					'hooks_correct'        => sprintf( esc_html__( 'Theme hooks %s are working!', OCS_DOMAIN ), '<code>"' . $before_hook . '"</code> &amp; <code>"' . $after_hook . '"</code>' ),
				),
				'css_prefix' => $this->get_settings( 'css_prefix' ),
				'_debug'     => (bool) ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			)
		);
	}

	/**
	 * Get validator link.
	 *
	 * @since   0.5.6
	 * @param   string  $url
	 * @return  string
	 */
	public function get_validator_link( $url = '' ) {
		if ( ! $url ) {
			$url = get_bloginfo( 'url' );
		}
		return add_query_arg( $this->param, 1, $url );
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
