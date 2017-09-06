<?php
/**
 * Off-Canvas Sidebars plugin form
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

abstract class OCS_Off_Canvas_Sidebars_Form
{
	protected $general_key = '';
	protected $settings = array();
	protected $plugin_key = '';

	/**
	 * Frontend type selecton
	 * @deprecated
	 * @param array $args
	 */
	function frontend_type_option( $args ) {
		$prefixes     = $this->get_option_prefixes( $args );
		$prefix_name  = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id    = $prefixes['prefixId'];

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[frontend_type]" id="' . $prefix_id . '_style_action" value="action" ' . checked( $prefix_value['frontend_type'], 'action' ) . ' /> ' . esc_html__( 'Actions', 'off-canvas-sidebars' ) . ' (' . esc_html__( 'Default', 'off-canvas-sidebars' ) . ')</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[frontend_type]" id="' . $prefix_id . '_style_jquery" value="jquery" ' . checked( $prefix_value['frontend_type'], 'jquery' ) . ' /> ' . esc_html__( 'jQuery', 'off-canvas-sidebars' ) . ' (' . esc_html__( 'Experimental', 'off-canvas-sidebars' ) . ')</label>';
		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * Echo checkboxes to enable/disable sidebars outside the sidebars tab.
	 */
	function enabled_sidebars_option() {
		$prefix_name  = esc_attr( $this->general_key ) . '[sidebars]';
		$prefix_value = $this->settings['sidebars'];
		$prefix_id    = $this->general_key . '_sidebars';
		//$prefix_classes = array( $prefix_id );
		if ( ! empty( $this->settings['sidebars'] ) ) {
			$html  = '<fieldset class="checkbox">';

			foreach ( $prefix_value as $sidebar => $sidebar_data ) {
				//$classes = $this->get_option_classes( $prefix_classes, 'enable' );
				$html .= '<label><input type="checkbox" name="' . $prefix_name . '[' . $sidebar . '][enable]" id="' . $prefix_id . '_enable_' . $sidebar . '" value="1" ' . checked( $prefix_value[ $sidebar ]['enable'], 1, false ) . ' /> ' . $this->settings['sidebars'][ $sidebar ]['label'] . '</label>';
			}
			$html .= '<input type="hidden" name="' . $prefix_name . '[ocs_update]" value="1" />';
			$html .= '</fieldset>';
			echo $html;
		} else {
			$tab = ( isset( $this->sidebars_tab ) ) ? '&tab=' . $this->sidebars_tab : '';
			echo '<a href="?page=' . esc_attr( $this->plugin_key ) . $tab . '">'
				. esc_html__( 'Click here to add off-canvas sidebars', 'off-canvas-sidebars' ) . '</a>';
		}
	}

	/**
	 * The sidebars location option.
	 * @param array $args
	 */
	function sidebar_location( $args ) {
		if ( ! isset( $args['sidebar'] ) ) {
			return;
		}
		$prefixes       = $this->get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = $this->get_option_classes( $prefix_classes, 'location' );

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[location]" class="' . $classes . '" id="' . $prefix_id . '_location_left" value="left" ' . checked( $prefix_value['location'], 'left', false ) . ' /> ' . esc_html__( 'Left', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[location]" class="' . $classes . '" id="' . $prefix_id . '_location_right" value="right" ' . checked( $prefix_value['location'], 'right', false ) . ' /> ' . esc_html__( 'Right', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[location]" class="' . $classes . '" id="' . $prefix_id . '_location_top" value="top" ' . checked( $prefix_value['location'], 'top', false ) . ' /> ' . esc_html__( 'Top', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[location]" class="' . $classes . '" id="' . $prefix_id . '_location_bottom" value="bottom" ' . checked( $prefix_value['location'], 'bottom', false ) . ' /> ' . esc_html__( 'Bottom', 'off-canvas-sidebars' ) . '</label>';
		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * The sidebars size option.
	 * @param array $args
	 */
	function sidebar_size( $args ) {
		if ( ! isset( $args['sidebar'] ) ) {
			return;
		}
		$prefixes       = $this->get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = $this->get_option_classes( $prefix_classes, 'size' );

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_default" value="default" ' . checked( $prefix_value['size'], 'default', false ) . ' /> ' . esc_html__( 'Default', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_small" value="small" ' . checked( $prefix_value['size'], 'small', false ) . ' /> ' . esc_html__( 'Small', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_large" value="large" ' . checked( $prefix_value['size'], 'large', false ) . ' /> ' . esc_html__( 'Large', 'off-canvas-sidebars' ) . '</label>';

		$html .= '<div class="custom-input">';
		$html .= '<label style="display: inline-block"><input type="radio" name="' . $prefix_name . '[size]" class="' . $classes . '" id="' . $prefix_id . '_size_custom" value="custom" ' . checked( $prefix_value['size'], 'custom', false ) . ' /> ' . esc_html__( 'Custom', 'off-canvas-sidebars' ) . '</label>';

			$attr = array(
				'type'  => 'number',
				'name'  => $prefix_name . '[size_input]',
				'class' => $this->get_option_classes( $prefix_classes, 'size_input' ),
				'id'    => $prefix_id . '_size_input',
				'value' => $prefix_value['size_input'],
				'min'   => 1,
				'max'   => '',
				'step'  => 1,
			);
			$html .= ' &nbsp; <input ' . self::parse_to_html_attr( $attr ) . ' />';
			$html .= '<select name="' . $prefix_name . '[size_input_type]" class="' . $this->get_option_classes( $prefix_classes, 'size_input_type' ) . '">';
				$html .= '<option value="%" ' . selected( $prefix_value['size_input_type'], '%', false ) . '>%</option>';
				$html .= '<option value="px" ' . selected( $prefix_value['size_input_type'], 'px', false ) . '>px</option>';
			$html .= '</select>';

		$html .= '</div>';

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * The sidebars style option.
	 * @param array $args
	 */
	function sidebar_style( $args ) {
		if ( ! isset( $args['sidebar'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = $this->get_option_classes( $prefix_classes, 'style' );

		$html  = '<fieldset class="radio">';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[style]" class="' . $classes . '" id="' . $prefix_id . '_style_push" value="push" ' . checked( $prefix_value['style'], 'push', false ) . ' /> ' . esc_html__( 'Sidebar slides and pushes the site across when opened.', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[style]" class="' . $classes . '" id="' . $prefix_id . '_style_reveal" value="reveal" ' . checked( $prefix_value['style'], 'reveal', false ) . ' /> ' . esc_html__( 'Sidebar reveals and pushes the site across when opened.', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[style]" class="' . $classes . '" id="' . $prefix_id . '_style_shift" value="shift" ' . checked( $prefix_value['style'], 'shift', false ) . ' /> ' . esc_html__( 'Sidebar shifts and pushes the site across when opened.', 'off-canvas-sidebars' ) . '</label>';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[style]" class="' . $classes . '" id="' . $prefix_id . '_style_overlay" value="overlay" ' . checked( $prefix_value['style'], 'overlay', false ) . ' /> ' . esc_html__( 'Sidebar overlays the site when opened.', 'off-canvas-sidebars' ) . '</label>';
		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * General input fields.
	 * @param array $args
	 */
	function text_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
		if ( ! empty( $args['class'] ) ) {
			$classes .= ' ' . $args['class'];
		}

		$html  = '<fieldset>';

		$attr = array(
			'name' => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id' => $prefix_id . '_' . $args['name'],
		);
		if ( isset( $args['placeholder'] ) ) {
			$attr['placeholder'] = $args['placeholder'];
		}

		if ( ! empty( $args['multiline'] ) ) {
			$field = '<textarea ' . self::parse_to_html_attr( $attr ) . '>' . $prefix_value[ $args['name'] ] . '</textarea>';
		} else {
			$attr['type'] = 'text';
			$attr['value'] = $prefix_value[ $args['name'] ];
			$field = '<input ' . self::parse_to_html_attr( $attr ) . ' />';
		}
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}

		$html .= $field;

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @param array $args
	 */
	function checkbox_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		$classes = $this->get_option_classes( $prefix_classes, $args['name'] );

		$html  = '<fieldset class="checkbox">';

		$attr = array(
			'type'  => 'checkbox',
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
			'value' => 1,
		);

		$field = '<input ' . self::parse_to_html_attr( $attr ) . checked( $prefix_value[ $args['name'] ], 1, false ) . ' />';
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}
		$html .= $field;

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @param array $args
	 */
	function radio_option( $args ) {
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
			$prefix_value[ $args['name'] ] = $args['default'];
		}
		$classes = $this->get_option_classes( $prefix_classes, $args['name'] );

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

			$field = '<input ' . self::parse_to_html_attr( $attr ) . checked( $prefix_value[ $args['name'] ], $option['value'], false ) . ' />';

			if ( isset( $option['label'] ) ) {
				$field = '<label>' . $field . ' ' . $option['label'] . '</label>';
			}
			$field .= $this->do_description( $option , 'span' );
			$field .= '<br />';

			$html .= $field;

		} // End foreach().

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @param array $args
	 */
	function select_option( $args ) {
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		if ( isset( $args['value'] ) ) {
			$prefix_value[ $args['name'] ] = $args['value'];
		}
		if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
			$prefix_value[ $args['name'] ] = $args['default'];
		}
		$classes = $this->get_option_classes( $prefix_classes, $args['name'] );

		$html = '<fieldset>';

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
			$value = ( isset( $option['label'] ) ) ? $option['label'] : $option['value'];
			$html .= '<option value="' . $option['value'] . '" ' . selected( $prefix_value[ $args['name'] ], $option['value'], false ) . '>' . $value . '</option>';

		} // End foreach().

		$html .= '</select>';
		if ( isset( $args['label'] ) ) {
			$html = '<label>' . $html . ' ' . $args['label'] . '</label><br />';
		}

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @param array $args
	 */
	function number_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = $this->get_option_classes( $prefix_classes, $args['name'] );

		$attr = array(
			'type'  => 'number',
			'name'  => $prefix_name . '[' . $args['name'] . ']',
			'class' => $classes,
			'id'    => $prefix_id . '_' . $args['name'],
			'value' => $prefix_value[ $args['name'] ],
			'min'   => 1,
			'max'   => '',
			'step'  => 1,
		);
		$html  = '<fieldset>';

		$field = '<input ' . self::parse_to_html_attr( $attr ) . checked( $prefix_value[ $args['name'] ], 1, false ) . ' />';
		if ( ! empty( $args['input_after'] ) ) {
			$field .= ' ' . $args['input_after'];
		}
		if ( isset( $args['label'] ) ) {
			$field = '<label>' . $field . ' ' . $args['label'] . '</label>';
		}
		$html .= $field;

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @param array $args
	 */
	function color_option( $args ) {
		if ( ! isset( $args['name'] ) ) {
			return;
		}
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];

		$classes = $this->get_option_classes( $prefix_classes, $args['name'] . '_type' );

		$html  = '<fieldset>';

		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_theme" value="" ' . checked( $prefix_value[ $args['name'] . '_type' ], '', false ) . ' /> ' . esc_html__( 'Default', 'off-canvas-sidebars' ) . '</label> <span class="description">(' . esc_html__( 'Overwritable with CSS', 'off-canvas-sidebars' ) . ')</span><br />';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_transparent" value="transparent" ' . checked( $prefix_value[ $args['name'] . '_type' ], 'transparent', false ) . ' /> ' . esc_html__( 'Transparent', 'off-canvas-sidebars' ) . '</label><br />';
		$html .= '<label><input type="radio" name="' . $prefix_name . '[' . $args['name'] . '_type]" class="' . $classes . '" id="' . $prefix_id . '_background_color_type_color" value="color" ' . checked( $prefix_value[ $args['name'] . '_type' ], 'color', false ) . ' /> ' . esc_html__( 'Color', 'off-canvas-sidebars' ) . '</label><br />';

		$html .= '<div class="' . $prefix_id . '_' . $args['name'] . '_wrapper">';
		$attr = array(
			'type' => 'text',
			'class' => 'color-picker ' . $this->get_option_classes( $prefix_classes, $args['name'] ),
			'id' => $prefix_id . '_' . $args['name'],
			'name' => $prefix_name . '[' . $args['name'] . ']',
			'value' => $prefix_value[ $args['name'] ],
		);
		$html .= '<input ' . self::parse_to_html_attr( $attr ) . ' />';
		$html .= '</div>';

		$html .= $this->do_description( $args );
		$html .= '</fieldset>';
		echo $html;
	}

	/**
	 * @since  0.4
	 * @param  array   $args
	 * @param  string  $elem
	 * @return string
	 */
	function do_description( $args, $elem = 'p' ) {
		if ( isset( $args['description'] ) ) {
			return '<' . $elem . ' class="description">' . $args['description'] . '</' . $elem . '>';
		}
		return '';
	}

	/**
	 * Returns attribute prefixes for general settings and sidebar settings
	 *
	 * @since  0.1
	 *
	 * @param  array  $args      Arguments from the settings field
	 * @return array  $prefixes  Prefixes for name, value and id attributes
	 */
	function get_option_prefixes( $args ) {
		if ( isset( $args['sidebar'] ) ) {
			$prefix_name = esc_attr( $this->general_key ) . '[sidebars][' . $args['sidebar'] . ']';
			$prefix_value = $this->settings['sidebars'][ $args['sidebar'] ];
			$prefix_id = $this->general_key . '_sidebars_' . $args['sidebar'];
			$prefix_classes = array(
				$this->general_key . '_sidebars_' . $args['sidebar'],
				$this->general_key . '_sidebars',
			);
		} else {
			$prefix_name = esc_attr( $this->general_key );
			$prefix_value = $this->settings;
			$prefix_id = $this->general_key;
			$prefix_classes = array(
				$this->general_key,
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
	 * Combine classes prefixed with the field name
	 * @since  0.2
	 * @param  $classes
	 * @param  $append
	 * @return string
	 */
	function get_option_classes( $classes, $append ) {
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
	 * @since   0.4
	 * @static
	 *
	 * @param   array  $attr  The current attributes.
	 * @param   array  $new   The new attributes. Attribute names as key.
	 * @return  array
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

	/**
	 * Converts an array of attributes to a HTML string format starting with a space.
	 *
	 * @since   0.4
	 * @static
	 *
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

} // end class
