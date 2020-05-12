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
 * @version 0.5.5
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
		return off_canvas_sidebars_settings()->get_settings( $key );
	}

	/**
	 * Converts an array of attributes to a HTML string format starting with a space.
	 *
	 * @todo Maybe create an API class.
	 *
	 * @since   0.4.0
	 * @since   0.5.0  Moved from OCS_Off_Canvas_Sidebars_Form.
	 * @static
	 * @param   array   $array  Array to parse. (attribute => value pairs)
	 * @return  string
	 */
	public static function parse_to_html_attr( $array ) {
		$str = '';
		if ( is_array( $array ) && ! empty( $array ) ) {
			foreach ( $array as $attr => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}
				$array[ $attr ] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
			$str = implode( ' ', $array );
		}
		return $str;
	}

	/**
	 * @todo Move?
	 * @since 0.5.5
	 * @return bool
	 */
	public static function is_gutenberg_page() {
		if ( function_exists( 'is_gutenberg_page' ) ) {
			// The Gutenberg plugin is on.
			return is_gutenberg_page();
		}
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) ) {
			// Gutenberg page on 5+.
			return $current_screen->is_block_editor();
		}
		return false;
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
