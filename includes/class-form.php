<?php
/**
 * Off-Canvas Sidebars - Class Form
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin form
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.4.0
 * @version 0.5.5
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
abstract class OCS_Off_Canvas_Sidebars_Form extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * Frontend type selection
	 * @deprecated
	 * @todo Remove.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param array $args
	 */
	public static function frontend_type_option( $args ) {
		$prefixes     = self::get_option_prefixes( $args );
		$prefix_name  = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id    = $prefixes['prefixId'];

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[frontend_type]" id="' . $prefix_id . '_style_action" value="action" ' . checked( $prefix_value['frontend_type'], 'action' ) . ' /> ' . esc_html__( 'Actions', OCS_DOMAIN ) . ' (' . esc_html__( 'Default', OCS_DOMAIN ) . ')</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[frontend_type]" id="' . $prefix_id . '_style_jquery" value="jquery" ' . checked( $prefix_value['frontend_type'], 'jquery' ) . ' /> ' . esc_html__( 'jQuery', OCS_DOMAIN ) . ' (' . esc_html__( 'Experimental', OCS_DOMAIN ) . ')</label>';
		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Echo checkboxes to enable/disable sidebars outside the sidebars tab.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 */
	public static function enabled_sidebars_option() {
		$sidebars     = off_canvas_sidebars()->get_sidebars();
		$key          = off_canvas_sidebars()->get_general_key();
		$prefix_name  = esc_attr( $key ) . '[sidebars]';
		$prefix_value = $sidebars;
		$prefix_id    = $key . '_sidebars';
		//$prefix_classes = array( $prefix_id );
		if ( ! empty( $sidebars ) ) {
			$html = '<fieldset class="checkbox">';

			foreach ( $prefix_value as $sidebar => $sidebar_data ) {
				//$classes = self::get_option_classes( $prefix_classes, 'enable' );
				$html .= '<label><input type="checkbox" name="' . $prefix_name . '[' . $sidebar . '][enable]" id="' . $prefix_id . '_enable_' . $sidebar . '" value="1" ' . checked( $prefix_value[ $sidebar ]['enable'], 1, false ) . ' /> ' . $sidebars[ $sidebar ]['label'] . '</label>';
			}
			$html .= '</fieldset>';
			echo $html;
		} else {
			$tab  = '&tab=ocs-sidebars';
			$link = '?page=' . esc_attr( off_canvas_sidebars()->get_plugin_key() ) . $tab;
			echo '<a href="' . $link . '">' . esc_html__( 'Click here to add off-canvas sidebars', OCS_DOMAIN ) . '</a>';
		}
	}

	/**
	 * The sidebars size option.
	 * @since   0.1.0
	 * @since   0.2.0  Renamed from sidebar_width()
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   array  $args
	 */
	public static function sidebar_size( $args ) {
		if ( ! isset( $args['sidebar'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = self::get_option_classes( $prefix_classes, 'size' );

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_default" value="default" ' . checked( $prefix_value['size'], 'default', false ) . ' /> ' . esc_html__( 'Default', OCS_DOMAIN ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_small" value="small" ' . checked( $prefix_value['size'], 'small', false ) . ' /> ' . esc_html__( 'Small', OCS_DOMAIN ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_large" value="large" ' . checked( $prefix_value['size'], 'large', false ) . ' /> ' . esc_html__( 'Large', OCS_DOMAIN ) . '</label>';

		$html .= '<div class="custom-input">';
		$html .= '<label style="display: inline-block"><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_custom" value="custom" ' . checked( $prefix_value['size'], 'custom', false ) . ' /> ' . esc_html__( 'Custom', OCS_DOMAIN ) . '</label>';

			$attr = array(
				'type'  => 'number',
				'name'  => $prefix_name . '[size_input]',
				'class' => self::get_option_classes( $prefix_classes, 'size_input' ),
				'id'    => $prefix_id . '_size_input',
				'value' => $prefix_value['size_input'],
				'min'   => 1,
				'max'   => '',
				'step'  => 1,
			);
			$html .= ' &nbsp; <input ' . self::parse_to_html_attr( $attr ) . ' />';
			$html .= '<select name="' . $prefix_name . '[size_input_type]" class="' . self::get_option_classes( $prefix_classes, 'size_input_type' ) . '">';
				$html .= '<option value="%" ' . selected( $prefix_value['size_input_type'], '%', false ) . '>%</option>';
				$html .= '<option value="px" ' . selected( $prefix_value['size_input_type'], 'px', false ) . '>px</option>';
			$html .= '</select>';

		$html .= '</div>';

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * General input fields.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   array  $args
	 */
	public static function text_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		$classes = self::get_option_classes( $prefix_classes, $args['name'] );
		if ( ! empty( $args['class'] ) ) {
			$classes .= ' ' . $args['class'];
		}

		$html = '<fieldset>';

		$attr = array(
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
		);
		if ( isset( $args['placeholder'] ) ) {
			$attr['placeholder'] = $args['placeholder'];
		}

		if ( ! empty( $args['multiline'] ) ) {
			$field = '<textarea ' . self::parse_to_html_attr( $attr ) . '>' . $prefix_value[ $args['name'] ] . '</textarea>';
		} else {
			$attr['type']  = 'text';
			$attr['value'] = $prefix_value[ $args['name'] ];
			$field = '<input ' . self::parse_to_html_attr( $attr ) . ' />';
		}
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}

		$html .= $field;

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render checkbox option.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   array  $args
	 */
	public static function checkbox_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		$classes = self::get_option_classes( $prefix_classes, $args['name'] );

		$html = '<fieldset class="checkbox">';

		$attr = array(
			'type'  => 'checkbox',
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
			'value' => 1,
		);

		$checked = checked( $prefix_value[ $args['name'] ], 1, false );

		$field = '<input ' . self::parse_to_html_attr( $attr ) . $checked . ' />';
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}
		$html .= $field;

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render radio option.
	 * @since   0.3.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   array  $args
	 */
	public static function radio_option( $args ) {
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
			$prefix_value[ $args['name'] ] = $args['default'];
		}
		$classes = self::get_option_classes( $prefix_classes, $args['name'] );

		$html = '<fieldset class="radio">';

		foreach ( $args['options'] as $option ) {

			if ( ! isset( $prefix_value[ $args['name'] ] ) ) {
				$prefix_value[ $args['name'] ] = ( isset( $args['value'] ) ) ? $args['value'] : false;
			}

			$attr = array(
				'type'  => 'radio',
				'name'  => $prefix_name . '[' . $args['name'] . ']',
				'class' => $classes,
				'id'    => $prefix_id . '_' . $args['name'] . '_' . $option['name'],
				'value' => $option['value'],
			);

			$checked = checked( $prefix_value[ $args['name'] ], $option['value'], false );

			$field = '<input ' . self::parse_to_html_attr( $attr ) . $checked . ' />';

			if ( isset( $option['label'] ) ) {
				$field = '<label>' . $field . ' ' . $option['label'] . '</label>';
			}
			$field .= self::do_description( $option, 'span' );
			$field .= '<br />';

			$html .= $field;

		} // End foreach().

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render select option.
	 * @since   0.4.0
	 * @static
	 * @param   array  $args
	 */
	public static function select_option( $args ) {
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
			$prefix_value[ $args['name'] ] = $args['default'];
		}
		$classes = self::get_option_classes( $prefix_classes, $args['name'] );

		$html = '<fieldset class="select">';

		$attr = array(
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
		);

		$html .= '<select ' . self::parse_to_html_attr( $attr ) . ' >';

		foreach ( $args['options'] as $option ) {
			if ( ! isset( $prefix_value[ $args['name'] ] ) ) {
				$prefix_value[ $args['name'] ] = ( isset( $args['value'] ) ) ? $args['value'] : false;
			}
			$value    = ( isset( $option['label'] ) ) ? $option['label'] : $option['value'];
			$selected = selected( $prefix_value[ $args['name'] ], $option['value'], false );
			$html .= '<option value="' . esc_attr( $option['value'] ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';

		} // End foreach().

		$html .= '</select>';
		if ( isset( $args['label'] ) ) {
			$html = '<label>' . $html . ' ' . $args['label'] . '</label><br />';
		}

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render number option.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @since   0.5.5  Changed min attr to 0 (was 1).
	 * @static
	 * @param   array  $args
	 */
	public static function number_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = self::get_option_classes( $prefix_classes, $args['name'] );

		$attr = array(
			'type'  => 'number',
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
			'value' => $prefix_value[ $args['name'] ],
			'min'   => 0,
			'max'   => '',
			'step'  => 1,
		);
		$html = '<fieldset class="number">';

		$field = '<input ' . self::parse_to_html_attr( $attr ) . ' />';
		if ( ! empty( $args['input_after'] ) ) {
			$field .= ' ' . $args['input_after'];
		}
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}
		$html .= $field;

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render color option.
	 * @since   0.1.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   array  $args
	 */
	public static function color_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes       = self::get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = self::get_option_classes( $prefix_classes, $args['name'] . '_type' );

		$html = '<fieldset class="radio color">';

		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_theme" value="" ' . checked( $prefix_value[ $args['name'] . '_type' ], '', false ) . ' /> ' . esc_html__( 'Default', OCS_DOMAIN ) . ' &nbsp; <span class="description">(' . esc_html__( 'Overwritable with CSS', OCS_DOMAIN ) . ')</span></label><br />';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_transparent" value="transparent" ' . checked( $prefix_value[ $args['name'] . '_type' ], 'transparent', false ) . ' /> ' . esc_html__( 'Transparent', OCS_DOMAIN ) . '</label><br />';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_color" value="color" ' . checked( $prefix_value[ $args['name'] . '_type' ], 'color', false ) . ' /> ' . esc_html__( 'Color', OCS_DOMAIN ) . '</label><br />';

		$html .= '<div class="' . $prefix_id . '_' . $args['name'] . '_wrapper">';

		$attr = array(
			'type'  => 'text',
			'class' => 'color-picker ' . self::get_option_classes( $prefix_classes, $args['name'] ),
			'id'    => $prefix_id . '_' . $args['name'],
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'value' => $prefix_value[ $args['name'] ],
		);
		$html .= '<input ' . self::parse_to_html_attr( $attr ) . ' />';
		$html .= '</div>';

		$html .= self::do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Render description.
	 * @since   0.4.0
	 * @static
	 * @param   array   $args
	 * @param   string  $elem
	 * @return  string
	 */
	public static function do_description( $args, $elem = 'p' ) {
		if ( isset( $args['description'] ) ) {
			return '<' . $elem . ' class="description">' . $args['description'] . '</' . $elem . '>';
		}
		return '';
	}

	/**
	 * Returns attribute prefixes for general settings and sidebar settings.
	 *
	 * @since   0.1.0
	 * @static
	 * @param   array  $args      Arguments from the settings field.
	 * @return  array  $prefixes  Prefixes for name, value and id attributes.
	 */
	public static function get_option_prefixes( $args ) {
		$settings = off_canvas_sidebars()->get_settings();
		$key      = off_canvas_sidebars()->get_general_key();
		if ( isset( $args['sidebar'] ) ) {
			$prefix_name    = esc_attr( $key ) . '[sidebars][' . $args['sidebar'] . ']';
			$prefix_value   = off_canvas_sidebars()->get_sidebars( $args['sidebar'] );
			$prefix_id      = $key . '_sidebars_' . $args['sidebar'];
			$prefix_classes = array(
				$key . '_sidebars_' . $args['sidebar'],
				$key . '_sidebars',
			);
		} else {
			$prefix_name    = esc_attr( $key );
			$prefix_value   = $settings;
			$prefix_id      = $key;
			$prefix_classes = array(
				$key,
			);
		}
		if ( ! empty( $args['required'] ) ) {
			$prefix_classes[] = 'required';
		}
		return array(
			'prefixName'    => $prefix_name,
			'prefixValue'   => $prefix_value,
			'prefixId'      => $prefix_id,
			'prefixClasses' => $prefix_classes,
		);
	}

	/**
	 * Combine classes prefixed with the field name.
	 * @since   0.2.0
	 * @since   0.4.0  Moved to this class.
	 * @static
	 * @param   $classes
	 * @param   $append
	 * @return  string
	 */
	public static function get_option_classes( $classes, $append ) {
		if ( $append ) {
			foreach ( $classes as $key => $class ) {
				if ( ! in_array( $class, array( 'required', 'widefat' ), true ) )
				$classes[ $key ] = $class . '_' . $append;
			}
		}
		return implode( ' ', $classes );
	}

	/**
	 * Merge two arrays of attributes into one, combining values.
	 * It currently doesn't convert variable types.
	 *
	 * @since   0.4.0
	 * @static
	 * @param   array  $attr  The current attributes.
	 * @param   array  $new   The new attributes. Attribute names as key.
	 * @return  string[]
	 */
	public static function merge_attr( $attr, $new ) {
		foreach ( $new as $key => $value ) {
			if ( empty( $attr[ $key ] ) ) {
				$attr[ $key ] = $value;
				continue;
			}
			if ( is_array( $attr[ $key ] ) ) {
				$attr[ $key ] = array_merge( $attr[ $key ], (array) $value );
				continue;
			}
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}
			$attr[ $key ] .= ( ! empty( $value ) ) ? ' ' . $value : '';
		}
		return $attr;
	}

} // End class().
