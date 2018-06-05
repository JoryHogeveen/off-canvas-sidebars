<?php
/**
 * Off-Canvas Sidebars - Class Base
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin base class
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.0
 * @version 0.5.0
 */
abstract class OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * Get the plugin settings.
	 *
	 * @since   0.5.0
	 * @param   string  $key
	 * @return  mixed
	 */
	public function get_settings( $key ) {
		return OCS_Off_Canvas_Sidebars_Settings::get_instance()->get_settings( $key );
	}

	/**
	 * Magic method to output a string if trying to use the object as a string.
	 *
	 * @since   0.5.0
	 * @access  public
	 * @return  string
	 */
	public function __toString() {
		return get_class( $this );
	}

	/**
	 * Magic method to keep the object from being cloned.
	 *
	 * @since   0.5.0
	 * @access  public
	 */
	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to be cloned', OCS_DOMAIN ),
			null
		);
	}

	/**
	 * Magic method to keep the object from being unserialized.
	 *
	 * @since   0.5.0
	 * @access  public
	 */
	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to wake up', OCS_DOMAIN ),
			null
		);
	}

	/**
	 * Magic method to prevent a fatal error when calling a method that doesn't exist.
	 *
	 * @since   0.5.0
	 * @access  public
	 * @param   string  $method
	 * @param   array   $args
	 * @return  null
	 */
	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong(
			get_class( $this ) . "::{$method}",
			esc_html__( 'Method does not exist.', OCS_DOMAIN ),
			null
		);
		unset( $method, $args );
		return null;
	}

} // End class().
