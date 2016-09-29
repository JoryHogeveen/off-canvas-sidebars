<?php
/**
 * Off-Canvas Sidebars control widget
 *
 * Control Widget
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.3
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

final class OCS_Off_Canvas_Sidebars_Control_Widget extends WP_Widget {

	private $general_settings = array();
	private $general_labels = array();
	private $widget_setting = 'off-canvas-controls';

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// widget actual processes
		parent::__construct(
			'Off-Canvas-Control',
			__('Off-Canvas Control', 'off-canvas-sidebars'),
			array(
				'class_name' => 'off_canvas_control',
				'description' => __('Button to trigger off-canvas sidebars', 'off-canvas-sidebars' ),
			)
		);

		$this->load_plugin_data();
	}

	/**
	 * Get plugin defaults
	 */
	function load_plugin_data() {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->general_settings = $off_canvas_sidebars->get_settings();
		$this->general_labels = $off_canvas_sidebars->get_general_labels();
		//$this->general_key = $off_canvas_sidebars->get_general_key();
		//$this->plugin_key = $off_canvas_sidebars->get_plugin_key();
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$this->load_plugin_data();
		$instance = $this->merge_settings( $instance );
		$prefix = $this->general_settings['css_prefix'];

		// outputs the content of the widget
		echo $args['before_widget'].'<div class="off-canvas-control-wrapper">';

		?>
		<div id="slidebar_control">
		<?php
		foreach ( $this->general_settings['sidebars'] as $sidebar_id => $sidebar_data ) {
			if ( $sidebar_data['enable'] == 1 && $instance[ $this->widget_setting ][ $sidebar_id ]['enable'] == 1 ) {
				$widget_data = $instance[ $this->widget_setting ][ $sidebar_id ];
				$classes = array(
					$prefix . '-button',
					$prefix . '-toggle',
					$prefix . '-toggle-' . $sidebar_id
				);
				if ( $widget_data['button_class'] == 1 ) {
					$classes[] = 'button';
				}
		?>
			<div class="<?php echo implode( ' ', $classes ); ?>">
				<div class="inner">
				<?php if ( $widget_data['show_icon'] == 1 ) { ?>
					<?php if ( $widget_data['icon'] != '' ) { ?>
					<?php if ( strpos( $widget_data['icon'], 'dashicons' ) !== false ) { wp_enqueue_style('dashicons'); } ?>
					<span class="icon <?php echo $widget_data['icon'] ?>"></span>
					<?php } else {
					wp_enqueue_style('dashicons'); ?>
					<span class="icon dashicons dashicons-menu"></span>
					<?php } ?>
				<?php } ?>
				<?php if ( $widget_data['show_label'] == 1 ) { ?>
					<span class="label"><?php echo $widget_data['label'] ?></span>
				<?php } ?>
				</div>
			</div>
		<?php } } ?>
		</div>
		<?php

		echo '</div>'.$args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 * @return void
	 */
	public function form( $instance ) {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->load_plugin_data();
		$instance = $this->merge_settings( $instance );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( $this->widget_setting ); ?>"><?php _e( 'Controls', 'off-canvas-sidebars' ); ?>:</label>&nbsp;
			<?php foreach ( $this->general_settings['sidebars'] as $sidebar_id => $value ) {
					if ( empty( $this->general_settings['sidebars'][ $sidebar_id ]['enable'] ) ) {
						continue;
					}
			?>
			<span><label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>"><input type="checkbox" id="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>" class="off-canvas-control-left" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][enable]'; ?>" value="1" <?php checked( $instance[ $this->widget_setting][ $sidebar_id ]['enable'], 1 ); ?> /><?php echo $this->general_settings['sidebars'][ $sidebar_id ]['label']; ?></label></span> &nbsp;
			<?php } ?>

		</p>

		<?php
		// If no sidebars enabled, no other fields available
		if ( ! $off_canvas_sidebars->is_sidebar_enabled() ) {
			echo '<p>' . $this->general_labels['no_sidebars_available'] . '</p>';
		} else {
		?>

		<hr />

		<?php foreach ( $this->general_settings['sidebars'] as $sidebar_id => $value ) {
			if ( empty( $this->general_settings['sidebars'][ $sidebar_id ]['enable'] ) ) {
				continue;
			}
			$hidden = '';
			if ( empty( $instance[ $this->widget_setting ][ $sidebar_id ]['enable'] ) ) {
				$hidden = 'style="display:none;"';
			}
		?>
		<div class="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id . '_wrapper'; ?>" <?php echo $hidden ?>>
			<h4 class=""><?php echo ucfirst( $sidebar_id ) ?></h4>
			<p>
				<input type="checkbox" id="<?php echo $this->get_field_id( $this->widget_setting ).'_'.$sidebar_id; ?>_show_label" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][show_label]'; ?>" value="1" <?php checked( $instance[ $this->widget_setting ][ $sidebar_id ]['show_label'], 1 ); ?>>
				<label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_show_label"><?php _e( 'Show label', 'off-canvas-sidebars' ); ?></label>
			</p>
			<p class="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_label" <?php echo ($instance[ $this->widget_setting ][ $sidebar_id ]['show_label'] != 1)?'style="display: none;"':''; ?>>
				<label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_label"><?php _e( 'Label text', 'off-canvas-sidebars' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_label" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][label]'; ?>" value="<?php echo $instance[ $this->widget_setting ][ $sidebar_id ]['label']; ?>">
			<p>
				<input type="checkbox" id="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_show_icon" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][show_icon]'; ?>" value="1" <?php checked( $instance[ $this->widget_setting ][ $sidebar_id ]['show_icon'], 1 ); ?>>
				<label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_show_icon"><?php _e( 'Show icon', 'off-canvas-sidebars' ); ?></label>
			</p>
			<p class="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_icon" <?php echo ($instance[ $this->widget_setting ][ $sidebar_id ]['show_icon'] != 1)?'style="display: none;"':''; ?>>
				<label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_icon"><?php _e( 'Icon classes', 'off-canvas-sidebars' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_icon" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][icon]'; ?>" value="<?php echo $instance[ $this->widget_setting ][ $sidebar_id ]['icon']; ?>">
			</p>
			<p>
				<input type="checkbox" id="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_button_class" name="<?php echo $this->get_field_name( $this->widget_setting ) . '[' . $sidebar_id . '][button_class]'; ?>" value="1" <?php checked( $instance[ $this->widget_setting ][ $sidebar_id ]['button_class'], 1 ); ?>>
				<label for="<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_button_class"><?php _e( 'Add <code>button</code> class', 'off-canvas-sidebars' ); ?></label>
			</p>
			<hr />
		</div>
		<?php } ?>

		<p>
			<label>Preview:</label>
			<div id="<?php echo $this->id ?>-preview" class="<?php echo $this->id_base ?>-preview" style="background: #f5f5f5; border: 1px solid #eee; padding: 10px;">
				<?php $this->widget( array( 'before_widget'=>'','after_widget'=>'' ), $instance ); ?>
			</div>
		</p>

		<script type="text/javascript">
		<!--
			(function($) {
				<?php foreach ( $instance[ $this->widget_setting ] as $sidebar_id => $value ) { ?>
				gocs_show_hide_options('<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>', '<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id . '_wrapper'; ?>');
				gocs_show_hide_options('<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_show_label', '<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_label');
				gocs_show_hide_options('<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_show_icon', '<?php echo $this->get_field_id( $this->widget_setting ) . '_' . $sidebar_id; ?>_icon');
				<?php } ?>

				function gocs_show_hide_options(trigger, target) {
					trigger = $('#'+trigger);
					if ( ! trigger.is(':checked') ) {
						$('.'+target).slideUp('fast');
					}
					trigger.bind('change', function() {
						if ( $(this).is(':checked') ) {
							$('.'+target).slideDown('fast');
						} else {
							$('.'+target).slideUp('fast');
						}
					});
				}
			})( jQuery );
		-->
		</script>
		<?php } // end fields output ?>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param  array $new_instance The new options
	 * @param  array $old_instance The previous options
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();

		$this->load_plugin_data();
		$instance = $this->merge_settings( $instance );

		// checkboxes
		foreach ( $instance[ $this->widget_setting ] as $sidebar_id => $value ) {
			$instance[ $this->widget_setting ][ $sidebar_id ]['enable']       = ( ! empty( $new_instance[ $this->widget_setting ][ $sidebar_id ]['enable'] ) )       ? strip_tags( $new_instance[ $this->widget_setting ][ $sidebar_id ]['enable'] )       : '0';
			$instance[ $this->widget_setting ][ $sidebar_id ]['show_label']   = ( ! empty( $new_instance[ $this->widget_setting ][ $sidebar_id ]['show_label'] ) )   ? strip_tags( $new_instance[ $this->widget_setting ][ $sidebar_id ]['show_label'] )   : '0';
			$instance[ $this->widget_setting ][ $sidebar_id ]['show_icon']    = ( ! empty( $new_instance[ $this->widget_setting ][ $sidebar_id ]['show_icon'] ) )    ? strip_tags( $new_instance[ $this->widget_setting ][ $sidebar_id ]['show_icon'] )    : '0';
			$instance[ $this->widget_setting ][ $sidebar_id ]['button_class'] = ( ! empty( $new_instance[ $this->widget_setting ][ $sidebar_id ]['button_class'] ) ) ? strip_tags( $new_instance[ $this->widget_setting ][ $sidebar_id ]['button_class'] ) : '0';
		}
		// Allow 3 level arrays
		foreach ( $new_instance as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $new_instance[ $key ] as $key2 => $value2 ) {
					if ( is_array( $value2 ) ) {
						foreach ( $new_instance[ $key ][ $key2 ] as $key3 => $value3 ) {
							$instance[ $key ][ $key2 ][ $key3 ] = strip_tags( stripslashes( $new_instance[ $key ][ $key2 ][ $key3 ] ) );
						}
					} else {
						$instance[ $key ][ $key2 ] = strip_tags( stripslashes( $new_instance[ $key ][ $key2 ] ) );
					}
				}
			} else {
				$instance[ $key ] = strip_tags( stripslashes( $new_instance[ $key ] ) );
			}
		}
		return $instance;
	}

	/**
	 * Merge instance with defaults
	 *
	 * @param   array   $args
	 * @return  array   $args
	 */
	function merge_settings( $args ) {
		$defaults = array(
			$this->widget_setting => array()
		);

		foreach ( $this->general_settings['sidebars'] as $key => $value ) {
			$defaults[ $this->widget_setting ][ $key ] = array(
				'enable' => 0,
				'show_label' => 0,
				'label' => 'menu',
				'show_icon' => 1,
				'icon' => false,
				'button_class' => 1
			);
		};

		$args = array_merge( $defaults, $args );

		foreach ( $defaults[ $this->widget_setting ] as $key => $value ) {
			if ( empty( $args[ $this->widget_setting ][ $key ] ) ) {
				$args[ $this->widget_setting ][ $key ] = $defaults[ $this->widget_setting ][ $key ];
			}
			foreach ( $defaults[ $this->widget_setting ][ $key ] as $key2 => $value2 ) {
				if ( empty( $args[ $this->widget_setting ][ $key ][ $key2 ] ) ) {
					$args[ $this->widget_setting ][ $key ][ $key2 ] = $defaults[ $this->widget_setting ][ $key ][ $key2 ];
				}
			}
		}
		return $args;
	}
}
