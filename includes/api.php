<?php
/**
 * Off-Canvas Sidebars plugin API
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @version 0.5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Echos one, multiple or all OCS sidebars.
 *
 * @api
 * @since  0.3.0
 * @param  string|array  $sidebars  (optional) The ID(s) of the sidebar(s) as configured in: Appearances > Off-Canvas Sidebars > Sidebars.
 */
function the_ocs_off_canvas_sidebar( $sidebars = '' ) {
	$instance = off_canvas_sidebars_frontend();
	if ( $instance ) {
		if ( empty( $sidebars ) ) {
			$instance->do_sidebars();
		} else {
			if ( is_array( $sidebars ) ) {
				foreach ( $sidebars as $sidebar ) {
					$instance->do_sidebar( (string) $sidebar );
				}
			} else {
				$instance->do_sidebar( (string) $sidebars );
			}
		}
	}
}

/**
 * Output a trigger element for off-canvas sidebars.
 *
 * @api
 * @since  0.4.0
 * @since  0.5.0  Add icon options.
 * @param  array   $atts {
 *     Required array of arguments
 *     @type  string        $id               (required) The off-canvas sidebar ID.
 *     @type  string        $text             The text to show. Default: ''.
 *     @type  string        $action           The trigger action. Default: `toggle`.
 *     @type  string        $element          The HTML element. Default: `button`.
 *     @type  array|string  $class            Add extra classes? Also accepts a string with classes separated with a space.
 *     @type  string        $icon             Icon classes.
 *     @type  string        $icon_location    The icon location (`before` or `after`). Default: `before`.
 *     @type  array         $attr  {
 *          Other attributes to add. Format: attribute name (array key) => attribute value
 *     }
 * }
 * @param  string  $content  (optional) HTML/text string.
 * @return string
 */
function the_ocs_control_trigger( $atts, $content = '' ) {

	if ( empty( $atts['id'] ) ) {
		return esc_html__( 'No Off-Canvas Sidebar ID provided.', 'off-canvas-sidebars' );
	}

	$atts = shortcode_atts( array(
		'id'            => false,
		'text'          => '', // Text to show.
		'action'        => 'toggle', // toggle|open|close
		'element'       => 'button', // button|span|i|b|a|etc.
		'class'         => array(), // Extra classes, also accepts a string with classes separated with a space.
		'icon'          => '', // Icon classes.
		'icon_location' => 'before', // before|after.
		'attr'          => array(), // An array of attribute keys and their values.
		'echo'          => true,
	), $atts, 'ocs_trigger' );

	$return = '';
	if ( ! empty( $content ) ) {
		//if ( strpos( $content, '[ocs_trigger' ) !== false ) {
			//$return = do_shortcode( $content . '[/ocs_trigger]' );
		//} else {
			$atts['text'] = $content;
		//}
	}

	$return = OCS_Off_Canvas_Sidebars_Control_Trigger::render( $atts['id'], $atts ) . $return;
	if ( (boolean) $atts['echo'] ) {
		echo $return;
	}
	return $return;
}

/**
 * Shortcode handler for the_ocs_control_trigger().
 *
 * Example 1 (simple shortcode with all options):
 * [ocs_trigger id="left" action="open" element="a" button="1" text="My trigger button!" attr="href: #; rel: nofollow; alt: Yay!!"]
 *
 * Example 2 (nested shortcode with some options:
 * [ocs_trigger id="right" attr="type:button;alt:Yay!!"]My trigger button text[/ocs_trigger]
 *
 * @since  0.4.0
 * @see    the_ocs_control_trigger() for detailed info.
 * @param  array   $atts
 * @param  string  $content  (Optional) HTML/text string.
 * @return string
 */
function shortcode_ocs_trigger( $atts, $content = '' ) {
	// Shortcodes don't echo.
	$atts['echo'] = false;

	// You can also use `attributes` instead of `attr` for readability. If both are used, `attributes` is ignored.
	if ( empty( $atts['attr'] ) && ! empty( $atts['attributes'] ) ) {
		$atts['attr'] = $atts['attributes'];
	}
	unset( $atts['attributes'] );

	// You can also use `classes` instead of `class` for readability. If both are used, `classes` is ignored.
	if ( empty( $atts['class'] ) && ! empty( $atts['classes'] ) ) {
		$atts['class'] = $atts['classes'];
	}
	unset( $atts['classes'] );

	// @since 0.5.8.3
	if ( ! empty( $atts['attr'] ) ) {
		if ( false !== strpbrk( $atts['attr'], '<>(){}="\'`' ) ) {
			$atts['attr'] = 'data-warning:invalid-characters';
		}
	}
	if ( ! empty( $atts['class'] ) ) {
		if ( false !== strpbrk( $atts['class'], '<>(){}="\'`' ) ) {
			$atts['class'] = 'data-warning:invalid-characters';
		}
	}

	return the_ocs_control_trigger( $atts, $content );
}
add_shortcode( 'ocs_trigger', 'shortcode_ocs_trigger' );

/**
 * Parses arguments in a custom notation: key:value;key:value;
 * Also uses wp_parse_args.
 *
 * @since  0.5.0
 * @since  0.5.9.1  Sanitize attribute names/values and validate URL schemes.
 * @param  string|array  $args
 * @param  array         $defaults
 * @return array
 */
function off_canvas_sidebars_parse_attr_string( $args, $defaults = array() ) {
	if ( ! is_array( $args ) ) {
		$attr = explode( ';', trim( $args, '; ' ) );
		$args = array();
		foreach ( $attr as $key => $value ) {
			$attr[ $key ] = explode( ':', $value );
			if ( count( $attr[ $key ] ) > 0 ) {
				$attribute = trim( $attr[ $key ][0] );
				unset( $attr[ $key ][0] );
				$value = trim( implode( ':', $attr[ $key ] ) );
				$args[ $attribute ] = $value;
			}
		}
	}

	// Validate and sanitize attribute names and values.
	$sanitized = array();
	if ( is_array( $args ) ) {
		/**
		 * Define a custom list of attributes that are allowed.
		 *
		 * @since  0.5.9.1
		 * @param  ?array  $allowed_attr_names  Attribute names (lowercase).
		 * @return ?array
		 */
		$allowed_attr_names = apply_filters( 'ocs_trigger_allowed_attr_names', null );

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
		$safe_attr_names = apply_filters( 'ocs_trigger_safe_attr_names', $safe_attr_names );

		foreach ( $args as $name => $value ) {
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
					$allowed_protocols = apply_filters( 'ocs_trigger_allowed_url_protocols', wp_allowed_protocols(), $name, $value );

					if ( ! in_array( strtolower( $scheme ), $allowed_protocols, true ) ) {
						continue;
					}
				}
			}
			$sanitized[ $name ] = $value;
		}
		$args = $sanitized;
	}

	return wp_parse_args( $args, $defaults );
}

/**
 * Main instance of Off-Canvas Sidebars Frontend.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars_Frontend to prevent the need to use globals.
 *
 * @since   0.3.0
 * @return  \OCS_Off_Canvas_Sidebars_Frontend
 */
function off_canvas_sidebars_frontend() {
	return OCS_Off_Canvas_Sidebars_Frontend::get_instance();
}

/**
 * Main instance of Off-Canvas Sidebars Settings.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars_Settings to prevent the need to use globals.
 *
 * @since   0.5.0
 * @return  \OCS_Off_Canvas_Sidebars_Settings
 */
function off_canvas_sidebars_settings() {
	return OCS_Off_Canvas_Sidebars_Settings::get_instance();
}
