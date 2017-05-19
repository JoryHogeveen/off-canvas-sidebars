<?php
/**
 * Off-Canvas Sidebars plugin form
 *
 * Form
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
		?><fieldset class="radio">
		<label><input type="radio" name="<?php echo $prefix_name . '[frontend_type]'; ?>" id="<?php echo $prefix_id . '_style_action'; ?>" value="action" <?php checked( $prefix_value['frontend_type'], 'action' ); ?> /> <?php _e( 'Actions', 'off-canvas-sidebars' ); echo ' (' . __( 'Default', 'off-canvas-sidebars' ) . ')' ?></label>
		<label><input type="radio" name="<?php echo $prefix_name . '[frontend_type]'; ?>" id="<?php echo $prefix_id . '_style_jquery'; ?>" value="jquery" <?php checked( $prefix_value['frontend_type'], 'jquery' ); ?> /> <?php _e( 'jQuery', 'off-canvas-sidebars' ); echo ' (' . __( 'Experimental', 'off-canvas-sidebars' ) . ')' ?></label>
		<?php $this->do_description( $args ); ?>
		</fieldset><?php
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
			?><fieldset class="checkbox"><?php
			foreach ( $prefix_value as $sidebar => $sidebar_data ) {
				//$classes = $this->get_option_classes( $prefix_classes, 'enable' );
				?>
				<label><input type="checkbox" name="<?php echo $prefix_name . '[' . $sidebar . '][enable]'; ?>" id="<?php echo $prefix_id . '_enable_' . $sidebar; ?>" value="1" <?php checked( $prefix_value[ $sidebar ]['enable'], 1 ); ?> /> <?php echo $this->settings['sidebars'][ $sidebar ]['label']; ?></label>
				<?php
			}
			?>
			<input type="hidden" name="<?php echo $prefix_name . '[ocs_update]'; ?>" value="1" />
			</fieldset>
			<?php
		} else {
			$tab = ( isset( $this->sidebars_tab ) ) ? '&tab=' . $this->sidebars_tab : '';
			echo '<a href="?page=' . esc_attr( $this->plugin_key ) . $tab . '">'
			     . __( 'Click here to add off-canvas sidebars', 'off-canvas-sidebars' ) . '</a>';
		}
	}

	/**
	 * The sidebars location option.
	 * @param array $args
	 */
	function sidebar_location( $args ) {
		$prefixes       = $this->get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefix_classes, 'location' );
			?><fieldset class="radio">
			<label><input type="radio" name="<?php echo $prefix_name . '[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_location_left'; ?>" value="left" <?php checked( $prefix_value['location'], 'left' ); ?> /> <?php _e( 'Left', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_location_right'; ?>" value="right" <?php checked( $prefix_value['location'], 'right' ); ?> /> <?php _e( 'Right', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_location_top'; ?>" value="top" <?php checked( $prefix_value['location'], 'top' ); ?> /> <?php _e( 'Top', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_location_bottom'; ?>" value="bottom" <?php checked( $prefix_value['location'], 'bottom' ); ?> /> <?php _e( 'Bottom', 'off-canvas-sidebars' ); ?></label>
			<?php $this->do_description( $args ); ?>
			</fieldset><?php
		}
	}

	/**
	 * The sidebars size option.
	 * @param array $args
	 */
	function sidebar_size( $args ) {
		$prefixes       = $this->get_option_prefixes( $args );
		$prefix_name    = $prefixes['prefixName'];
		$prefix_value   = $prefixes['prefixValue'];
		$prefix_id      = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefix_classes, 'size' );
			?><fieldset class="radio">
			<label><input type="radio" name="<?php echo $prefix_name . '[size]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_size_default'; ?>" value="default" <?php checked( $prefix_value['size'], 'default' ); ?> /> <?php _e( 'Default', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[size]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_size_small'; ?>" value="small" <?php checked( $prefix_value['size'], 'small' ); ?> /> <?php _e( 'Small', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[size]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_size_large'; ?>" value="large" <?php checked( $prefix_value['size'], 'large' ); ?> /> <?php _e( 'Large', 'off-canvas-sidebars' ); ?></label>
			<div class="custom-input">
				<label style="display: inline-block">
					<input type="radio" name="<?php echo $prefix_name . '[size]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_size_custom'; ?>" value="custom" <?php checked( $prefix_value['size'], 'custom' ); ?> /> <?php _e( 'Custom', 'off-canvas-sidebars' ); ?>
				</label>:
				<input type="number" name="<?php echo $prefix_name . '[size_input]'; ?>" class="<?php echo $this->get_option_classes( $prefix_classes, 'size_input' ); ?>" min="1" max="" step="1" value="<?php echo $prefix_value['size_input'] ?>" />
				<select name="<?php echo $prefix_name . '[size_input_type]'; ?>" class="<?php echo $this->get_option_classes( $prefix_classes, 'size_input_type' ); ?>">
					<option value="%" <?php selected( $prefix_value['size_input_type'], '%' ); ?>>%</option>
					<option value="px" <?php selected( $prefix_value['size_input_type'], 'px' ); ?>>px</option>
				</select>
			</div>
			<?php $this->do_description( $args ); ?>
			</fieldset><?php
		}
	}

	/**
	 * The sidebars style option.
	 * @param array $args
	 */
	function sidebar_style( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefix_classes, 'style' );
			?><fieldset class="radio">
			<label><input type="radio" name="<?php echo $prefix_name . '[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_style_push'; ?>" value="push" <?php checked( $prefix_value['style'], 'push' ); ?> /> <?php _e( 'Sidebar slides and pushes the site across when opened.', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_style_reveal'; ?>" value="reveal" <?php checked( $prefix_value['style'], 'reveal' ); ?> /> <?php _e( 'Sidebar reveals and pushes the site across when opened.', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_style_shift'; ?>" value="shift" <?php checked( $prefix_value['style'], 'shift' ); ?> /> <?php _e( 'Sidebar shifts and pushes the site across when opened.', 'off-canvas-sidebars' ); ?></label>
			<label><input type="radio" name="<?php echo $prefix_name . '[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_style_overlay'; ?>" value="overlay" <?php checked( $prefix_value['style'], 'overlay' ); ?> /> <?php _e( 'Sidebar overlays the site when opened.', 'off-canvas-sidebars' ); ?></label>
			<?php $this->do_description( $args ); ?>
			</fieldset><?php
		}
	}

	/**
	 * General input fields.
	 * @param array $args
	 */
	function text_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		$placeholder = '';
		if ( isset( $args['placeholder'] ) ) {
			$placeholder = ' placeholder="' . $args['placeholder'] . '"';
		}
		if ( isset( $args['name'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefix_value[ $args['name'] ] = $args['value'];
			}
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
			if ( ! empty( $args['class'] ) ) {
				$classes .= ' ' . $args['class'];
			}
		?><fieldset>
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<?php if ( ! empty( $args['multiline'] ) ) { ?>
			<textarea name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_' . $args['name']; ?>" <?php echo $placeholder ?>><?php echo $prefix_value[ $args['name'] ]; ?></textarea>
			<?php } else { ?>
			<input type="text" name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_' . $args['name']; ?>" value="<?php echo $prefix_value[ $args['name'] ]; ?>"<?php echo $placeholder ?>/>
			<?php } ?>
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><?php } ?>
			<?php $this->do_description( $args ); ?>
		</fieldset><?php
		}
	}

	/**
	 * @param array $args
	 */
	function checkbox_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefix_value[ $args['name'] ] = $args['value'];
			}
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
		?><fieldset class="checkbox">
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<input type="checkbox" name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_' . $args['name']; ?>" value="1" <?php checked( $prefix_value[ $args['name'] ], 1 ); ?> />
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><?php } ?>
			<?php $this->do_description( $args ); ?>
		</fieldset><?php
		}
	}

	/**
	 * @param array $args
	 */
	function radio_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) && isset( $args['options'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefix_value[ $args['name'] ] = $args['value'];
			}
			if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
				$prefix_value[ $args['name'] ] = $args['default'];
			}
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
		?><fieldset class="radio">
			<?php foreach ( $args['options'] as $option ) {
				if ( ! isset( $prefix_value[ $args['name'] ] ) ) {
					$prefix_value[ $args['name'] ] = ( isset( $args['value'] ) ) ? $args['value'] : false;
				}
			?>
			<?php if ( isset( $option['label'] ) ) { ?><label><?php } ?>
			<input type="radio" name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_' . $args['name'] . '_' . $option['name'] ?>" value="<?php echo $option['value'] ?>" <?php checked( $prefix_value[ $args['name'] ], $option['value'] ); ?> />
			<?php if ( isset( $option['label'] ) ) { echo $option['label'] ?></label><?php
				}
				$this->do_description( $args , 'span' );
				echo '<br />';
			} // End foreach(). ?>
			<?php $this->do_description( $args ); ?>
		</fieldset><?php
		}
	}

	/**
	 * @param array $args
	 */
	function select_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) && isset( $args['options'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefix_value[ $args['name'] ] = $args['value'];
			}
			if ( ! empty( $args['default'] ) && empty( $prefix_value[ $args['name'] ] ) ) {
				$prefix_value[ $args['name'] ] = $args['default'];
			}
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
			?><fieldset>
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<select name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_' . $args['name'] ?>">
			<?php foreach ( $args['options'] as $option ) {
				if ( ! isset( $prefix_value[ $args['name'] ] ) ) {
					$prefix_value[ $args['name'] ] = ( isset( $args['value'] ) ) ? $args['value'] : false;
				}
			?>
				<option value="<?php echo $option['value'] ?>" <?php selected( $prefix_value[ $args['name'] ], $option['value'] ); ?>><?php echo ( isset( $option['label'] ) ) ? $option['label'] : $option['value']; ?></option>
			<?php } // End foreach(). ?>
			</select>
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><br /><?php } ?>
			<?php $this->do_description( $args ); ?>
			</fieldset><?php
		}
	}

	/**
	 * @param array $args
	 */
	function number_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] );
		?><fieldset>
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<input type="number" id="<?php echo $prefix_id . '_' . $args['name']; ?>" class="<?php echo $classes; ?>" name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" value="<?php echo $prefix_value[ $args['name'] ] ?>" min="1" max="" step="1" /> <?php echo ( ! empty( $args['input_after'] ) ) ? $args['input_after'] : ''; ?>
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><?php } ?>
			<?php $this->do_description( $args ); ?>
		</fieldset><?php
		}
	}

	/**
	 * @param array $args
	 */
	function color_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefix_name = $prefixes['prefixName'];
		$prefix_value = $prefixes['prefixValue'];
		$prefix_id = $prefixes['prefixId'];
		$prefix_classes = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			$classes = $this->get_option_classes( $prefix_classes, $args['name'] . '_type' );
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefix_name . '[' . $args['name'] . '_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_background_color_type_theme'; ?>" value="" <?php checked( $prefix_value[ $args['name'] . '_type' ], '' ); ?> /> <?php _e( 'Default', 'off-canvas-sidebars' ); ?></label> <span class="description">(<?php _e( 'Overwritable with CSS', 'off-canvas-sidebars' ); ?>)</span><br />
			<label><input type="radio" name="<?php echo $prefix_name . '[' . $args['name'] . '_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_background_color_type_transparent'; ?>" value="transparent" <?php checked( $prefix_value[ $args['name'] . '_type' ], 'transparent' ); ?> /> <?php _e( 'Transparent', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefix_name . '[' . $args['name'] . '_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefix_id . '_background_color_type_color'; ?>" value="color" <?php checked( $prefix_value[ $args['name'] . '_type' ], 'color' ); ?> /> <?php _e( 'Color', 'off-canvas-sidebars' ); ?></label><br />
			<div class="<?php echo $prefix_id . '_' . $args['name'] . '_wrapper'; ?>">
				<input type="text" class="color-picker <?php echo $this->get_option_classes( $prefix_classes, $args['name'] ) ?>" id="<?php echo $prefix_id . '_' . $args['name']; ?>" name="<?php echo $prefix_name . '[' . $args['name'] . ']'; ?>" value="<?php echo $prefix_value[ $args['name'] ] ?>" />
			</div>
			<?php $this->do_description( $args ); ?>
		</fieldset><?php
		}
	}

	/**
	 * @since  0.4
	 * @param  array   $args
	 * @param  string  $elem
	 */
	function do_description( $args, $elem = 'p' ) {
		if ( isset( $args['description'] ) ) {
			echo '<' . $elem . ' class="description">' . $args['description'] . '</' . $elem . '>';
		}
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
				$this->general_key
			);
		}
		if ( ! empty( $args['required'] ) ) {
			$prefix_classes[] = 'required';
		}
		return array( 'prefixName' => $prefix_name, 'prefixValue' => $prefix_value, 'prefixId' => $prefix_id, 'prefixClasses' => $prefix_classes );
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

} // end class
