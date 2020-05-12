<?php
/**
 * Off-Canvas Sidebars - Class Control_Widget
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars control widget.
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.5
 */
final class OCS_Off_Canvas_Sidebars_Control_Widget extends WP_Widget
{
	private $widget_setting = 'off-canvas-controls';
	private $advanced_fields = array(
		'action',
		'element',
		'class',
		'attr',
	);
	private $optional_fields = array(
		'icon_location',
		'action',
		'element',
		'class',
		'attr',
	);

	/**
	 * Sets up the widgets name etc.
	 */
	public function __construct() {
		parent::__construct(
			'Off-Canvas-Control',
			__( 'Off-Canvas Trigger', OCS_DOMAIN ),
			array(
				'classname'   => 'off_canvas_control',
				'description' => __( 'Trigger off-canvas sidebars', OCS_DOMAIN ),
			)
		);
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param  array  $args
	 * @param  array  $instance
	 */
	public function widget( $args, $instance ) {
		$instance = $this->merge_settings( $instance );
		$content  = '';
		$ocs      = off_canvas_sidebars();

		foreach ( $ocs->get_sidebars() as $sidebar_id => $sidebar_data ) {
			if ( empty( $instance[ $this->widget_setting ][ $sidebar_id ]['enable'] ) ) {
				continue;
			}

			$auto_hide = off_canvas_sidebars_settings()->get_sidebar_settings( $sidebar_id, 'hide_control_classes' );
			if ( ! $ocs->is_sidebar_enabled( $sidebar_id ) && $auto_hide ) {
				continue;
			}

			$trigger_args       = $instance[ $this->widget_setting ][ $sidebar_id ];
			$trigger_args['id'] = $sidebar_id;

			$content .= $this->do_control_trigger( $trigger_args );
		};

		if ( ! $content ) {
			return;
		}

		echo $args['before_widget'];

		echo '<div class="off-canvas-control-wrapper"><div class="off-canvas-triggers">' . $content . '</div></div>';

		echo $args['after_widget'];
	}

	/**
	 * Render a control trigger.
	 * @since  0.5.0
	 * @param  array  $args
	 * @return string
	 */
	public function do_control_trigger( $args ) {
		$trigger_args = array(
			'echo'          => false,
			'id'            => $args['id'],
			'text'          => '', // Text to show.
			'action'        => 'toggle', // toggle|open|close.
			'element'       => 'div', // button|span|i|b|a|etc.
			'class'         => '', // Extra classes (space separated), also accepts an array of classes.
			//'icon'          => '', // Icon classes.
			//'icon_location' => 'before', // before|after.
			//'attr'          => array(), // An array of attribute keys and their values.
		);

		if ( $args['button_class'] ) {
			$trigger_args['class'] .= ' button';
		}
		if ( $args['show_icon'] ) {
			if ( $args['icon'] ) {
				$trigger_args['icon'] = $args['icon'];
			} else {
				$trigger_args['icon'] = 'dashicons dashicons-menu';
			}
		}
		if ( $args['show_label'] ) {
			$trigger_args['text'] = $args['label'];
		}

		foreach ( $this->optional_fields as $key ) {
			if ( ! empty( $args[ $key ] ) ) {
				$trigger_args[ $key ] = $args[ $key ];
			}
		}

		return the_ocs_control_trigger( $trigger_args );
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @param  array  $instance  The widget options.
	 */
	public function form( $instance ) {
		$sidebars = off_canvas_sidebars_settings()->get_enabled_sidebars();

		$instance = $this->merge_settings( $instance );

		$ocs = $instance[ $this->widget_setting ];
		$field_id = $this->get_field_id( $this->widget_setting );
		$field_name = $this->get_field_name( $this->widget_setting );

		// If no sidebars enabled, no other fields available.
		if ( ! $sidebars ) {
			echo '<p>' . off_canvas_sidebars()->get_general_labels( 'no_sidebars_available' ) . '</p>';
		} else {
		?>

		<p id="<?php echo $field_id . '_sidebar_enable'; ?>">
			<b><?php esc_html_e( 'Controls', OCS_DOMAIN ); ?>:</b><br />
			<?php
			foreach ( $sidebars as $sidebar_id => $value ) {
			?>
			<span style="display: inline-block; margin-right: 10px;">
				<label for="<?php echo $field_id . '_' . $sidebar_id; ?>">
					<input type="checkbox" id="<?php echo $field_id . '_' . $sidebar_id; ?>" name="<?php echo $field_name . '[' . $sidebar_id . '][enable]'; ?>" value="1" <?php checked( $instance[ $this->widget_setting ][ $sidebar_id ]['enable'], 1 ); ?> />
					<?php echo esc_html( $value['label'] ); ?>
				</label>
			</span>
			<?php } ?>
		</p>

		<hr />

		<div id="<?php echo $field_id; ?>_tabs" style="display: none;">
		<?php
			$counter = 0;
			foreach ( $sidebars as $sidebar_id => $value ) {
				$disabled = false;
				$class = 'ocs-tab';
				if ( empty( $ocs[ $sidebar_id ]['enable'] ) ) {
					$class .= ' disabled';
					$disabled = true;
				} elseif ( ! $counter ) {
					$class .= ' active';
				}
				?>
				<div id="<?php echo $field_id . '_' . $sidebar_id . '_tab'; ?>" class="<?php echo $class; ?>">
					<?php echo ( ! empty( $value['label'] ) ) ? $value['label'] : ucfirst( $sidebar_id ); ?>
				</div>
				<?php
				if ( ! $disabled ) {
					$counter++;
				}
			}
		?>
		</div>

		<div id="<?php echo $field_id; ?>_panes">
		<?php
		$counter = 0;
		foreach ( $sidebars as $sidebar_id => $value ) {
			$field_sidebar_id = $field_id . '_' . $sidebar_id;
			$field_sidebar_name = $field_name . '[' . $sidebar_id . ']';
		?>
		<div id="<?php echo $field_sidebar_id . '_pane'; ?>" class="ocs-pane <?php echo ( $counter ) ? 'autohide-js' : ''; ?>">
			<h4><?php echo ( ! empty( $value['label'] ) ) ? $value['label'] : ucfirst( $sidebar_id ); ?></h4>
			<p>
				<input type="checkbox" id="<?php echo $field_sidebar_id; ?>_show_label" name="<?php echo $field_sidebar_name . '[show_label]'; ?>" value="1" <?php checked( $ocs[ $sidebar_id ]['show_label'], 1 ); ?>>
				<label for="<?php echo $field_sidebar_id; ?>_show_label"><?php esc_html_e( 'Show label', OCS_DOMAIN ); ?></label>
			</p>
			<p class="<?php echo $field_sidebar_id; ?>_label">
				<label for="<?php echo $field_sidebar_id; ?>_label"><?php esc_html_e( 'Label text', OCS_DOMAIN ); ?>:</label>
				<input type="text" class="widefat" id="<?php echo $field_sidebar_id; ?>_label" name="<?php echo $field_sidebar_name . '[label]'; ?>" value="<?php echo $ocs[ $sidebar_id ]['label']; ?>">
			</p>
			<p>
				<input type="checkbox" id="<?php echo $field_sidebar_id; ?>_show_icon" name="<?php echo $field_sidebar_name . '[show_icon]'; ?>" value="1" <?php checked( $ocs[ $sidebar_id ]['show_icon'], 1 ); ?>>
				<label for="<?php echo $field_sidebar_id; ?>_show_icon"><?php esc_html_e( 'Show icon', OCS_DOMAIN ); ?></label>
			</p>
			<p class="<?php echo $field_sidebar_id; ?>_icon">
				<label for="<?php echo $field_sidebar_id; ?>_icon"><?php esc_html_e( 'Icon classes', OCS_DOMAIN ); ?>:</label>
				<input type="text" class="widefat" id="<?php echo $field_sidebar_id; ?>_icon" name="<?php echo $field_sidebar_name . '[icon]'; ?>" value="<?php echo $ocs[ $sidebar_id ]['icon']; ?>">
			</p>
			<p class="<?php echo $field_sidebar_id; ?>_icon_location">
				<select id="<?php echo $field_sidebar_id; ?>_icon_location" name="<?php echo $field_sidebar_name . '[icon_location]'; ?>">
					<option><?php echo esc_html__( 'Before', OCS_DOMAIN ) . ' (' . esc_html__( 'Default', OCS_DOMAIN ) . ')'; ?></option>
					<option value="after" <?php selected( $ocs[ $sidebar_id ]['icon_location'], 'after' ); ?>><?php esc_html_e( 'After', OCS_DOMAIN ); ?></option>
				</select>
				<label for="<?php echo $field_sidebar_id; ?>_icon_location"><?php esc_html_e( 'Icon location', OCS_DOMAIN ); ?></label>
			</p>
			<p>
				<input type="checkbox" id="<?php echo $field_sidebar_id; ?>_button_class" name="<?php echo $field_sidebar_name . '[button_class]'; ?>" value="1" <?php checked( $ocs[ $sidebar_id ]['button_class'], 1 ); ?>>
				<label for="<?php echo $field_sidebar_id; ?>_button_class">
				<?php
					// Translators: %s stands for `button` wrapped in a <code> html tag.
					echo sprintf( esc_html__( 'Add class: %s', OCS_DOMAIN ), '<code>button</code>' );
				?>
				</label>
			</p>

			<?php
			$has_advanced = (bool) array_intersect_key( $this->remove_defaults( $ocs[ $sidebar_id ] ), array_flip( $this->advanced_fields ) );
			?>

			<p>
				<input type="checkbox" id="<?php echo $field_sidebar_id; ?>_advanced_toggle" value="1" <?php checked( $has_advanced, true ); ?>>
				<label for="<?php echo $field_sidebar_id; ?>_advanced_toggle"><strong><?php esc_html_e( 'Advanced options', OCS_DOMAIN ); ?></strong></label>
			</p>
			<div id="<?php echo $field_sidebar_id . '_advanced'; ?>">
				<p>
					<select id="<?php echo $field_sidebar_id; ?>_action" name="<?php echo $field_sidebar_name . '[action]'; ?>">
						<option value=""><?php echo esc_html__( 'Toggle', OCS_DOMAIN ) . ' (' . esc_html__( 'Default', OCS_DOMAIN ) . ')'; ?></option>
						<option value="open" <?php selected( $ocs[ $sidebar_id ]['action'], 'open' ); ?>><?php esc_html_e( 'Open', OCS_DOMAIN ); ?></option>
						<option value="close" <?php selected( $ocs[ $sidebar_id ]['action'], 'close' ); ?>><?php esc_html_e( 'Close', OCS_DOMAIN ); ?></option>
					</select>
					<label for="<?php echo $field_sidebar_id; ?>_action"><?php esc_html_e( 'Trigger action', OCS_DOMAIN ); ?></label>
				</p>
				<p>
					<select id="<?php echo $field_sidebar_id; ?>_element" name="<?php echo $field_sidebar_name . '[element]'; ?>">
						<option value=""><?php echo 'div (' . __( 'Default', OCS_DOMAIN ) . ')'; ?></option>
						<?php
							$elements = array( /*'div',*/ 'button', 'span', 'a', 'b', 'strong', 'i', 'em', 'img' );
							foreach ( $elements as $element ) {
								?>
								<option value="<?php echo $element; ?>" <?php selected( $ocs[ $sidebar_id ]['element'], $element ); ?>><?php echo $element; ?></option>
								<?php
							}
						?>
					</select>
					<label for="<?php echo $field_sidebar_id; ?>_element"><?php esc_html_e( 'HTML element', OCS_DOMAIN ); ?></label>
				</p>
				<p class="<?php echo $field_sidebar_id; ?>_class">
					<label for="<?php echo $field_sidebar_id; ?>_class"><?php esc_html_e( 'Extra classes', OCS_DOMAIN ); ?>:</label>
					<input type="text" class="widefat" id="<?php echo $field_sidebar_id; ?>_class" name="<?php echo $field_sidebar_name . '[class]'; ?>" value="<?php echo $ocs[ $sidebar_id ]['class']; ?>">
				</p>
				<p class="<?php echo $field_sidebar_id; ?>_attr">
					<label for="<?php echo $field_sidebar_id; ?>_attr"><?php esc_html_e( 'Custom attributes', OCS_DOMAIN ); ?>:</label>
					<textarea class="widefat" id="<?php echo $field_sidebar_id; ?>_attr" name="<?php echo $field_sidebar_name . '[attr]'; ?>"><?php echo $ocs[ $sidebar_id ]['attr']; ?></textarea>
				</p>
			</div>

			<hr class="autohide-js" />
		</div>
		<?php
			$counter++;
		} // End foreach().
		?>
		</div>

		<p>
			<label><?php esc_html_e( 'Preview', OCS_DOMAIN ); ?>:</label>
			<div id="<?php echo $this->id; ?>-preview" class="<?php echo $this->id_base; ?>-preview">
				<?php $this->widget( array( 'before_widget' => '', 'after_widget' => '' ), $instance ); ?>
			</div>
		</p>

		<style type="text/css">
			#<?php echo $field_id; ?>_tabs {
				clear: both;
				width: 100%;
				overflow: hidden;
			}
			#<?php echo $field_id; ?>_tabs .ocs-tab {
				cursor: pointer;
				float: left;
				padding: 5px 8px;
				border: solid 1px #aaa;
				background: #e8e8e8;
			}
			#<?php echo $field_id; ?>_tabs .ocs-tab:hover {
				background: #f5f5f5;
			}
			#<?php echo $field_id; ?>_tabs .ocs-tab.active {
				background: #fafafa;
				border-bottom-color: #fafafa;
			}
			#<?php echo $field_id; ?>_tabs .ocs-tab.disabled {
				display: none;
				color: #aaa;
				cursor: default;
				background: #ddd;
			}
			#<?php echo $field_id; ?>_panes {
				padding: 10px;
				border: 1px solid #ccc;
				background: #fafafa;
			}
			#<?php echo $field_id; ?>_panes h4 {
				margin: .33em 0;;
			}
			#<?php echo $this->id; ?>-preview {
				background: #f5f5f5;
				border: 1px solid #eee;
				padding: 10px;
			}

			.dark-mode #<?php echo $field_id; ?>_tabs .ocs-tab {
				border-color: #000;
				background: #191f25;
			}
			.dark-mode #<?php echo $field_id; ?>_tabs .ocs-tab:hover {
				background: #32373c;
			}
			.dark-mode #<?php echo $field_id; ?>_tabs .ocs-tab.active {
				background: #50626f;
				border-bottom-color: #191f25;
			}
			.dark-mode #<?php echo $field_id; ?>_tabs .ocs-tab.disabled {
				color: #aaa;
				background: #000;
			}
			.dark-mode #<?php echo $field_id; ?>_panes {
				border-color: #191f25;
				background: #23282d;
			}
		</style>
		<script type="text/javascript">
		<!--
			( function( $ ) {
				<?php foreach ( $ocs as $sidebar_id => $value ) { ?>
				gocs_show_hide_options(
					'#<?php echo $field_id . '_' . $sidebar_id; ?>_show_label',
					'.<?php echo $field_id . '_' . $sidebar_id; ?>_label'
				);
				gocs_show_hide_options(
					'#<?php echo $field_id . '_' . $sidebar_id; ?>_show_icon',
					'.<?php echo $field_id . '_' . $sidebar_id; ?>_icon, .<?php echo $field_id . '_' . $sidebar_id; ?>_icon_location'
				);
				gocs_show_hide_options(
					'#<?php echo $field_id . '_' . $sidebar_id; ?>_advanced_toggle',
					'#<?php echo $field_id . '_' . $sidebar_id; ?>_advanced'
				);
				<?php } ?>

				$( '#<?php echo $field_id; ?>_panes .autohide-js' ).hide();
				$( '#<?php echo $field_id; ?>_tabs' ).show();
				$( '#<?php echo $field_id; ?>_tabs .ocs-tab' ).each( function() {
					var $this = $(this);
					$this.on( 'click', function() {
						if ( ! $this.hasClass('disabled') ) {
							var $target = $( '#' + $this.attr('id').replace( '_tab', '_pane' ) );
							$( '#<?php echo $field_id; ?>_panes .ocs-pane' ).not( $target ).slideUp('fast');
							$target.slideDown('fast');
							$( '#<?php echo $field_id; ?>_tabs .ocs-tab' ).not( $this ).removeClass('active');
							$this.addClass('active');
						}
					} );
				} );

				$( '#<?php echo $field_id . '_sidebar_enable'; ?> input' ).on( 'change', function() {
					var $this = $(this);
						pre   = $this.attr('id');
					if ( $this.is(':checked') ) {
						$( '#' + pre + '_tab' ).removeClass('disabled').trigger('click');
					} else {
						$( '#' + pre + '_tab' ).addClass('disabled');
						$( '#' + pre + '_pane' ).slideUp('fast');
					}
				} );

				function gocs_show_hide_options( trigger, target ) {
					var $trigger = $( trigger ),
						$target = $( target );
					if ( ! $trigger.is(':checked') ) {
						$target.slideUp('fast');
					}
					$trigger.bind( 'change', function() {
						if ( $(this).is(':checked') ) {
							$target.slideDown('fast');
						} else {
							$target.slideUp('fast');
						}
					} ).trigger('change');
				}
			} ) ( jQuery );
		-->
		</script>
		<?php
		} // End if().
	}

	/**
	 * Processing widget options on save.
	 *
	 * @param  array  $new_instance  The new options.
	 * @param  array  $old_instance  The old options.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved.
		$instance = $this->merge_settings( array() );

		$ocs = $instance[ $this->widget_setting ];
		$new_ocs = array();
		if ( ! empty( $new_instance[ $this->widget_setting ] ) ) {
			$new_ocs = $new_instance[ $this->widget_setting ];
		}

		$new_ocs = array_map( array( $this, 'parse_values' ), $new_ocs );
		$new_ocs = array_map( array( $this, 'sanitize_value' ), $new_ocs );

		foreach ( $new_ocs as $sidebar_id => $sidebar_settings ) {
			if ( empty( $ocs[ $sidebar_id ] ) ) {
				$ocs[ $sidebar_id ] = $sidebar_settings;
				continue;
			}
			if ( is_array( $sidebar_settings ) ) {
				foreach ( $new_ocs[ $sidebar_id ] as $setting => $value ) {
					$ocs[ $sidebar_id ][ $setting ] = $value;
				}
			} else {
				$ocs[ $sidebar_id ] = $sidebar_settings;
			}
		}

		$ocs = array_map( array( $this, 'remove_defaults' ), $ocs );

		$instance[ $this->widget_setting ] = $ocs;

		return $instance;
	}

	/**
	 * Parse form values.
	 *
	 * @since   0.5.0
	 * @param   array  $sidebar_settings
	 * @return  array  $sidebar_settings
	 */
	public function parse_values( $sidebar_settings ) {
		$sidebar_settings['enable']        = ( ! empty( $sidebar_settings['enable'] ) )         ? 1 : 0;
		$sidebar_settings['show_label']    = ( ! empty( $sidebar_settings['show_label'] ) )     ? 1 : 0;
		$sidebar_settings['show_icon']     = ( ! empty( $sidebar_settings['show_icon'] ) )      ? 1 : 0;
		$sidebar_settings['icon_location'] = ( 'after' === $sidebar_settings['icon_location'] ) ? 'after' : '';
		$sidebar_settings['button_class']  = ( ! empty( $sidebar_settings['button_class'] ) )   ? 1 : 0;
		return $sidebar_settings;
	}

	/**
	 * Remove optional setting keys if they are empty.
	 *
	 * @since   0.5.0
	 * @param   array  $sidebar_settings
	 * @return  array  $sidebar_settings
	 */
	public function remove_defaults( $sidebar_settings ) {
		foreach ( $this->optional_fields as $key ) {
			if ( empty( $sidebar_settings[ $key ] ) ) {
				unset( $sidebar_settings[ $key ] );
			}
		}
		return $sidebar_settings;
	}

	/**
	 * Merge instance with defaults.
	 *
	 * @param   array  $settings
	 * @return  array  $settings
	 */
	public function merge_settings( $settings ) {
		$defaults = array();

		foreach ( off_canvas_sidebars_settings()->get_sidebars() as $key => $value ) {
			$defaults[ $key ] = array(
				'enable'        => 0,
				'show_label'    => 0,
				'label'         => 'menu',
				'show_icon'     => 1,
				'icon'          => false,
				'icon_location' => '',
				'button_class'  => 1,
				// Advanced.
				'action'        => '',
				'element'       => '',
				'class'         => '',
				'attr'          => '',
			);
		};

		$ocs = array();
		if ( ! empty( $settings[ $this->widget_setting ] ) ) {
			$ocs = $settings[ $this->widget_setting ];
		}

		foreach ( $defaults as $key => $value ) {
			if ( empty( $ocs[ $key ] ) ) {
				$ocs[ $key ] = $value;
				continue;
			}
			foreach ( $defaults[ $key ] as $key2 => $value2 ) {
				if ( ! isset( $ocs[ $key ][ $key2 ] ) ) {
					$ocs[ $key ][ $key2 ] = $value2;
				}
			}
		}

		$settings[ $this->widget_setting ] = $ocs;

		return $settings;
	}

	/**
	 * Sanitize values.
	 *
	 * @param  mixed  $val  The value.
	 * @return mixed
	 */
	public function sanitize_value( $val ) {
		if ( is_array( $val ) ) {
			return array_map( array( $this, 'sanitize_value' ), $val );
		} elseif ( is_string( $val ) ) {
			return strip_tags( stripslashes( $val ) );
		} elseif ( is_object( $val ) ) {
			return null;
		}
		return $val;
	}
}
