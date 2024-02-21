<?php
/**
 * Off-Canvas Sidebars - Class Control Trigger
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin control trigger API.
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.1
 * @version 0.5.6
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Control_Trigger extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * HTML elements not supported as a control trigger.
	 * @since  0.5.1
	 * @var    array
	 */
	public static $unsupported_elements = array(
		'base',
		'body',
		'html',
		'link',
		'meta',
		'noscript',
		'style',
		'script',
		'title', // Meta
	);

	/**
	 * HTML elements that are rendered as singleton elements.
	 * @since  0.5.1
	 * @var    array
	 */
	public static $singleton_elements = array(
		'br', // Why?!
		'hr', // Why?!
		'img',
		'input', // Why?!
	);

	/**
	 * HTML elements that are rendered as singleton elements.
	 * @since  0.5.1
	 * @var    array
	 */
	public static $control_elements = array(
		'button',
		'span',
		'a',
		'b',
		'strong',
		'i',
		'em',
		'img',
		'div',
	);

	/**
	 * HTML elements that are rendered considered link elements.
	 * @link   https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/rel
	 * @since  0.5.8
	 * @var    array
	 */
	public static $link_elements = array(
		'a',
		'area',
		'form',
		'link',
	);

	/**
	 * Do not allow this class to be instantiated.
	 */
	private function __construct() { }

	/**
	 * Generate a trigger element.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.4.0
	 * @since   0.5.0    Add icon options.
	 * @since   0.5.1    Moved to this class and renamed from `do_control_trigger()`.
	 * @since   0.5.8.2  Added escaping for passed attributes.
	 * @static
	 *
	 * @param   string  $sidebar_id  Required.
	 * @param   array   $args        See API: the_ocs_control_trigger() for info.
	 * @return  string
	 */
	public static function render( $sidebar_id, $args = array() ) {

		if ( empty( $sidebar_id ) ) {
			return esc_html__( 'No Off-Canvas Sidebar ID provided.', 'off-canvas-sidebars' );
		}

		$sidebar_id = (string) $sidebar_id;

		$defaults = array(
			'text'          => '', // Text to show.
			'action'        => 'toggle', // toggle|open|close.
			'element'       => 'button', // button|span|i|b|a|etc.
			'class'         => array(), // Extra classes (space separated), also accepts an array of classes.
			'icon'          => '', // Icon classes.
			'icon_location' => 'before', // before|after.
			'attr'          => array(), // An array of attribute keys and their values.
		);

		$args = wp_parse_args( $args, $defaults );

		$args['element'] = strtolower( $args['element'] );
		$args['attr']    = off_canvas_sidebars_parse_attr_string( $args['attr'] );
		$args['text']    = wp_kses_post( $args['text'] );

		if (
			in_array( $args['element'], self::$unsupported_elements, true )
			|| ! preg_match( '/^[\w]*$/', $args['element'] )
		) {
			return '<span class="error">' . esc_html__( 'This element is not supported for use as a button', OCS_DOMAIN ) . '</span>';
		}

		$singleton = false;

		// Is it a singleton element? Add the text to the attributes.
		if ( in_array( $args['element'], self::$singleton_elements, true ) ) {
			$singleton = true;
			if ( 'img' === $args['element'] && empty( $args['attr']['alt'] ) ) {
				$args['attr']['alt'] = $args['text'];
			}
			if ( 'input' === $args['element'] && empty( $args['attr']['value'] ) ) {
				$args['attr']['value'] = $args['text'];
			}
		}

		// Fix link elements XFN. See https://github.com/JoryHogeveen/off-canvas-sidebars/issues/116
		if ( in_array( $args['element'], self::$link_elements, true ) ) {
			if ( empty( $args['attr']['rel'] ) ) {
				$args['attr']['rel'] = 'nofollow';
			}
		}

		$attr = array(
			'class' => array(),
		);
		$attr = array_merge( $attr, $args['attr'] );

		// Get the default classes.
		$classes = self::get_trigger_classes( $sidebar_id, $args['action'] );

		// Optionally add extra classes.
		if ( ! empty( $args['class'] ) ) {
			if ( ! is_array( $args['class'] ) ) {
				$args['class'] = explode( ' ', $args['class'] );
			}
			$classes = array_merge( $classes, (array) $args['class'] );
		}

		// Parse classes.
		if ( ! is_array( $attr['class'] ) ) {
			$attr['class'] = explode( ' ', $attr['class'] );
		}
		$attr['class'] = array_merge( $attr['class'], $classes );
		$attr['class'] = array_map( 'trim', $attr['class'] );
		$attr['class'] = array_filter( $attr['class'] );
		$attr['class'] = array_unique( $attr['class'] );

		// Icons can not be used with singleton elements.
		if ( $args['icon'] && ! $singleton ) {
			if ( strpos( $args['icon'], 'dashicons' ) !== false ) {
				wp_enqueue_style( 'dashicons' );
			}
			$icon = '<span class="icon ' . esc_attr( $args['icon'] ) . '"></span>';
			if ( $args['text'] ) {
				// Wrap label in a separate span for styling purposes.
				$args['text'] = '<span class="label">' . $args['text'] . '</span>';
			}
			if ( 'after' === $args['icon_location'] ) {
				$args['text'] .= $icon;
			} else {
				$args['text'] = $icon . $args['text'];
			}
		}

		$return = '<' . $args['element'] . ' ' . self::parse_to_html_attr( $attr );
		if ( $singleton ) {
			$return .= ' />';
		} else {
			$return .= '>' . $args['text'] . '</' . $args['element'] . '>';
		}

		return $return;
	}

	/**
	 * Get the default control trigger classes.
	 *
	 * @since   0.5.3
	 * @static
	 *
	 * @param   string  $sidebar_id  The sidebar ID.
	 * @param   string  $action      The trigger action.
	 * @return  array
	 */
	public static function get_trigger_classes( $sidebar_id, $action = 'toggle' ) {
		$prefix = off_canvas_sidebars_settings()->get_settings( 'css_prefix' );

		$classes = array(
			'ocs-trigger',
			$prefix . '-trigger',
			$prefix . '-' . $action,
			$prefix . '-' . $action . '-' . $sidebar_id,
		);

		return $classes;
	}

	/**
	 * Get control trigger field options.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.5.1
	 * @static
	 *
	 * @return  array {
	 *     @type array $field_id {
	 *         @type  string  $type
	 *         @type  string  $name
	 *         @type  string  $label
	 *         @type  string  $description
	 *         @type  string  $group
	 *         @type  bool    $multiline  Note: Only if $type is `text`!
	 *         @type  array   $options {
	 *             NOTE: Only if $type is `select`!
	 *             @type  string  $label
	 *             @type  string  $value
	 *         }
	 *     }
	 * }
	 */
	public static function get_fields() {
		static $fields;

		if ( $fields ) {
			return $fields;
		}

		$sidebars = array(
			array(
				'value' => '',
				'label' => '-- ' . esc_html__( 'select', OCS_DOMAIN ) . ' --',
			),
		);
		foreach ( off_canvas_sidebars_settings()->get_enabled_sidebars() as $sidebar_id => $sidebar_data ) {
			$label = $sidebar_id;
			if ( ! empty( $sidebar_data['label'] ) ) {
				$label = $sidebar_data['label'] . ' (' . $sidebar_id . ')';
			}
			$sidebars[] = array(
				'label' => $label,
				'value' => $sidebar_id,
			);
		}

		$elements = array();
		foreach ( self::$control_elements as $e ) {
			$elements[] = array(
				'label' => '<' . $e . '>',
				'value' => $e,
			);
		}

		$strings = array(
			// Translators: [ocs_trigger text="Your text"] or [ocs_trigger]Your text[/ocs_trigger]
			'your_text' => esc_html__( 'Your text', OCS_DOMAIN ),
			// Translators: [ocs_trigger text="Your text"] or [ocs_trigger]Your text[/ocs_trigger]
			'or'        => esc_html__( 'or', OCS_DOMAIN ),
		);

		$fields = array(
			'id'            => array(
				'type'        => 'select',
				'name'        => 'id',
				'label'       => esc_html__( 'Sidebar ID', OCS_DOMAIN ),
				'options'     => $sidebars,
				'description' => esc_html__( '(Required) The off-canvas sidebar ID', OCS_DOMAIN ),
				'required'    => true,
				'group'       => 'basic',
			),
			'text'          => array(
				'type'        => 'text',
				'name'        => 'text',
				'label'       => esc_html__( 'Text', OCS_DOMAIN ),
				'description' => esc_html__( 'Limited HTML allowed', OCS_DOMAIN ),
				'multiline'   => true,
				'group'       => 'basic',
			),
			'icon'          => array(
				'type'        => 'text',
				'name'        => 'icon',
				'label'       => esc_html__( 'Icon', OCS_DOMAIN ),
				// Translators: %s stands for <code>dashicons</code>.
				'description' => esc_html__( 'The icon classes.', OCS_DOMAIN ) . ' ' . sprintf( esc_html__( 'Do not forget the base icon class like %s', OCS_DOMAIN ), '<code>dashicons</code>' ),
				'group'       => 'basic',
			),
			'icon_location' => array(
				'type'    => 'select',
				'name'    => 'icon_location',
				'label'   => esc_html__( 'Icon location', OCS_DOMAIN ),
				'options' => array(
					array(
						'label' => esc_html__( 'Before', OCS_DOMAIN ) . ' (' . esc_html__( 'Default', OCS_DOMAIN ) . ')',
						'value' => '',
					),
					array(
						'label' => esc_html__( 'After', OCS_DOMAIN ),
						'value' => 'after',
					),
				),
				'group'   => 'basic',
			),
			'action'        => array(
				'type'    => 'select',
				'name'    => 'action',
				'label'   => esc_html__( 'Trigger action', OCS_DOMAIN ),
				'options' => array(
					array(
						'label' => esc_html__( 'Toggle', OCS_DOMAIN ) . ' (' . esc_html__( 'Default', OCS_DOMAIN ) . ')',
						'value' => '',
					),
					array(
						'label' => esc_html__( 'Open', OCS_DOMAIN ),
						'value' => 'open',
					),
					array(
						'label' => esc_html__( 'Close', OCS_DOMAIN ),
						'value' => 'close',
					),
				),
				'group'   => 'advanced',
			),
			'element'       => array(
				'type'        => 'select',
				'name'        => 'element',
				'label'       => esc_html__( 'HTML element', OCS_DOMAIN ),
				'options'     => $elements,
				'description' => esc_html__( 'Choose wisely', OCS_DOMAIN ),
				'group'       => 'advanced',
			),
			'class'         => array(
				'type'        => 'text',
				'name'        => 'class',
				'label'       => esc_html__( 'Extra classes', OCS_DOMAIN ),
				'description' => esc_html__( 'Separate multiple classes with a space', OCS_DOMAIN ),
				'group'       => 'advanced',
			),
			'attr'          => array(
				'type'        => 'text',
				'name'        => 'attr',
				'label'       => esc_html__( 'Custom attributes', OCS_DOMAIN ),
				'description' => esc_html__( 'key : value ; key : value', OCS_DOMAIN ),
				'multiline'   => true,
				'group'       => 'advanced',
			),
			'nested'        => array(
				'type'        => 'checkbox',
				'name'        => 'nested',
				'label'       => esc_html__( 'Nested shortcode', OCS_DOMAIN ) . '?',
				'description' => '[ocs_trigger text="' . $strings['your_text'] . '"] ' . $strings['or'] . ' [ocs_trigger]' . $strings['your_text'] . '[/ocs_trigger]',
				'group'       => 'advanced',
			),
		);

		return $fields;
	}

	/**
	 * Filters the list of fields, based on a set of key => value arguments.
	 * @since   0.5.1
	 * @see     \wp_list_filter
	 * @param   array  $filter
	 * @return  array
	 */
	public static function get_fields_by( $filter ) {
		return wp_list_filter( self::get_fields(), $filter );
	}

	/**
	 * Filters the list of fields by group.
	 * @since   0.5.1
	 * @see     \wp_list_filter
	 * @param   string  $group
	 * @return  array
	 */
	public static function get_fields_by_group( $group ) {
		return self::get_fields_by( array(
			'group' => $group,
		) );
	}

	/**
	 * Filters the list of fields by type.
	 * @since   0.5.1
	 * @see     \wp_list_filter
	 * @param   string  $type
	 * @return  array
	 */
	public static function get_fields_by_type( $type ) {
		return self::get_fields_by( array(
			'type' => $type,
		) );
	}

} // End class().
