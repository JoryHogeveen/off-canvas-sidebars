<?php
/**
 * Off-Canvas Sidebars plugin base class
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @since   0.5
 * @version 0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

abstract class OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * Magic method to output a string if trying to use the object as a string.
	 *
	 * @since   0.5
	 * @access  public
	 * @return  string
	 */
	public function __toString() {
		return get_class( $this );
	}

	/**
	 * Magic method to keep the object from being cloned.
	 *
	 * @since   0.5
	 * @access  public
	 * @return  void
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
	 * @since   0.5
	 * @access  public
	 * @return  void
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
	 * @since   0.5
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
