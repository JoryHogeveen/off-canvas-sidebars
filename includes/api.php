<?php
/**
 * Off-Canvas Sidebars plugin API
 *
 * API
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Echos one, multiple or all OCS sidebars
 *
 * @api
 * @since  0.3
 * @param  string|array  $sidebars        (Optional) The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
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
 * Output a trigger element for off-canvas sidebars
 *
 * @api
 * @since  0.4
 * @param  array   $atts {
 *     Required array of arguments
 *     @type  string        id       (Required) The off-canvas sidebar ID.
 *     @type  string        text     The text to show. Default: ''
 *     @type  string        action   The trigger action. Default: toggle
 *     @type  string        element  The HTML element. Default: button
 *     @type  array|string  class    Add extra classes? Also accepts a string with classes separated with a space
 *     @type  array         attr     Other attributes to add {
 *          Format: attribute name (array key) => attribute value
 *     }
 * }
 * @param  string  $content  (Optional) $content
 * @return string
 */
function the_ocs_control_trigger( $atts, $content = '' ) {
	$instance = off_canvas_sidebars_frontend();
	if ( $instance ) {

		if ( empty( $atts['id'] ) ) {
			return __( 'No Off-Canvas Sidebar ID provided.', 'off-canvas-sidebars' );
		}

		$atts = shortcode_atts( array(
			'id'      => false,
			'text'    => '', // Text to show
			'action'  => 'toggle', // toggle|open|close
			'element' => 'button', // button|span|i|b|a|etc.
			'class'   => array(), // Extra classes, also accepts a string with classes separated with a space
			'attr'    => array(), // An array of attribute keys and their values
			'echo'    => true,
		), $atts, 'ocs_trigger' );

		$return = '';
		if ( ! empty( $content ) ) {
			$atts['text'] = $content;
		}

		$return = $instance->do_control_trigger( $atts['id'], $atts ) . $return;
		if ( (boolean) $atts['echo'] ) {
			echo $return;
		}
		return $return;
	}
	return '';
}

/**
 * Shortcode handler for the_ocs_control_trigger()
 *
 * Example 1 (simple shortcode with all options):
 * [ocs_trigger id="left" action="open" element="a" button="1" text="My trigger button!" attr="href: #; rel: nofollow; alt: Yay!!"]
 *
 * Example 2 (nested shortcode with some options:
 * [ocs_trigger id="right" attr="type:button;alt:Yay!!"]My trigger button text[/ocs_trigger]
 *
 * @since  0.4
 * @see    the_ocs_control_trigger() for detailed info
 * @param  array   $atts
 * @param  string  $content  (Optional) $content
 * @return string
 */
function shortcode_ocs_trigger( $atts, $content = '' ) {
	// Shortcodes don't echo
	$atts['echo'] = false;

	// You can also use `attributes` instead of `attr` for readability (if both are used, `attributes` is ignored)
	if ( empty( $atts['attr'] ) && ! empty( $atts['attributes'] ) ) {
		$atts['attr'] = $atts['attributes'];
	}
	unset( $atts['attributes'] );

	// You can also use `classes` instead of `class` for readability (if both are used, `classes` is ignored)
	if ( empty( $atts['class'] ) && ! empty( $atts['classes'] ) ) {
		$atts['class'] = $atts['classes'];
	}
	unset( $atts['classes'] );

	// Parse attributes send through the shortcode
	if ( ! empty( $atts['attr'] ) ) {
		$attr = explode( ';', $atts['attr'] );
		$atts['attr'] = array();
		foreach ( $attr as $key => $value ) {
			$attr[ $key ] = explode( ':', $value );
			if ( count( $attr[ $key ] ) > 1 ) {
				$attribute = trim( $attr[ $key ][0] );
				unset( $attr[ $key ][0] );
				$atts['attr'][ $attribute ] = trim( implode( ':', $attr[ $key ] ) );
			}
		}
	}

	return the_ocs_control_trigger( $atts, $content );
}
add_shortcode( 'ocs_trigger', 'shortcode_ocs_trigger' );

/**
 * Main instance of Off-Canvas Sidebars Frontend.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars_Frontend to prevent the need to use globals.
 *
 * @since   0.3
 * @return  OCS_Off_Canvas_Sidebars_Frontend|false
 */
function off_canvas_sidebars_frontend() {
	if ( is_callable( array( 'OCS_Off_Canvas_Sidebars_Frontend', 'get_instance' ) ) ) {
		return OCS_Off_Canvas_Sidebars_Frontend::get_instance();
	}
	return false;
}
