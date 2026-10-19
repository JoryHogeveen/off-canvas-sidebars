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
	 * @since   0.5.0    Moved from OCS_Off_Canvas_Sidebars_Form.
	 * @since   0.5.9.1  Sanitizes HTML attributes and validates URL schemes.
	 * @static
	 * @param   array   $array  Array to parse. (attribute => value pairs)
	 * @param   bool    $sanitize
	 * @return  string
	 */
	public static function parse_to_html_attr( $array, $sanizite = true ) {
		$str = '';
		if ( is_array( $array ) && ! empty( $array ) ) {

			if ( $sanitize ) {
				$array = self::sanitize_attr( $array );
			}

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
	 * Sanitizes HTML attributes and validates URL schemes.
	 *
	 * @since  0.5.9.1
	 * @param  array  $attr
	 * @return array
	 */
	public static function sanitize_attr( $attr ) {

		$sanitized = array();

		if ( ! is_array( $attr ) ) {
			return $sanitized;
		}

		/**
		 * Define a custom list of attributes that are allowed.
		 *
		 * @since  0.5.9.1
		 * @param  ?array  $allowed_attr_names  Attribute names (lowercase).
		 * @return ?array
		 */
		$allowed_attr_names = apply_filters( 'ocs_allowed_attr_names', null );

		// Attributes whose values are free text and may legitimately start with `word:`
		// (e.g. `style="color:red"`). Only these skip protocol validation. All other
		// attributes are validated, so URL attributes are protected automatically and
		// anything missed fails closed instead of allowing a script scheme through.
		$safe_attr_names = array(
			'style',
			'class',
			'title',
			'alt',
			'placeholder',
			'label',
			'value',
			'content',
			'pattern',
		);

		/**
		 * Filter the attributes that are exempt from URL protocol validation.
		 * Values of these attributes are never treated as URLs.
		 *
		 * The `data-*` and `aria-*` prefixes are always exempt.
		 *
		 * @since  0.5.9.1
		 * @param  array  $safe_attr_names  Attribute names (lowercase).
		 * @return array
		 */
		$safe_attr_names = apply_filters( 'ocs_safe_attr_names', $safe_attr_names );

		foreach ( $attr as $name => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$name_lower = strtolower( $name );
			if ( is_array( $allowed_attr_names ) ) {
				if ( ! in_array( $name, $allowed_attr_names, true ) && ! in_array( $name_lower, $allowed_attr_names, true ) ) {
					continue;
				}
			} else {
				if ( strpos( $name_lower, 'on' ) === 0 ) {
					continue;
				}
				if ( ! preg_match( '/^[a-z][a-z0-9_-]*$/D', $name ) ) {
					continue;
				}
				// @since 0.5.8.3  Block invalid characters in attribute values.
				if ( false !== strpbrk( $value, '<>(){}="\'`' ) ) {
					continue;
				}
			}

			// Validate URL schemes for every attribute except known non-URL (safe) ones.
			$is_safe_attr = in_array( $name_lower, $safe_attr_names, true )
				|| 0 === strpos( $name_lower, 'data-' )
				|| 0 === strpos( $name_lower, 'aria-' );

			if ( ! $is_safe_attr ) {
				// Browsers ignore tab/LF/CR anywhere in a URL and leading/trailing
				// whitespace before parsing the scheme, so strip those for detection
				// only (the value itself is never modified here).
				$url_path = trim( preg_replace( '/[\x00-\x1f\x7f]/', '', (string) $value ) );
				$scheme   = wp_parse_url( $url_path, PHP_URL_SCHEME );

				if ( ! empty( $scheme ) ) {
					/**
					 * Filter the protocols allowed in attribute values.
					 * Defaults to WordPress' allowed protocols (http, https, mailto, etc.).
					 *
					 * @since  0.5.9.1
					 * @param  array   $allowed_protocols  Allowed URL schemes (lowercase).
					 * @param  string  $name               The attribute name.
					 * @param  mixed   $value              The attribute value.
					 * @return array
					 */
					$allowed_protocols = apply_filters( 'ocs_allowed_url_protocols', wp_allowed_protocols(), $name, $value );

					if ( ! in_array( strtolower( $scheme ), $allowed_protocols, true ) ) {
						continue;
					}
				}
			}
			$sanitized[ $name ] = $value;
		}
		
		return $sanitized;
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
