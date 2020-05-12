<?php
/**
 * Off-Canvas Sidebars - Class Mce_Shortcode
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin TinyMCe shortcode generator
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.4.0
 * @since   0.5.2  Renamed from `OCS_Off_Canvas_Sidebars_Editor_Shortcode_Generator`.
 * @version 0.5.5
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Mce_Shortcode extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Mce_Shortcode
	 * @since  0.4.0
	 */
	protected static $_instance = null;

	/**
	 * This class gets called in the init hook.
	 * @since   0.4.0
	 * @access  private
	 */
	private function __construct() {
		// Check user permissions.
		/*if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}*/

		if ( 'true' === get_user_option( 'rich_editing' ) ) {
			add_action( 'media_buttons', array( $this, 'media_buttons' ), 20 );
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
			add_filter( 'tiny_mce_before_init', array( $this, 'mce_inline_css' ) );
			add_action( 'after_wp_tiny_mce', array( $this, 'print_scripts' ) );
			add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
		}
	}

	/**
	 * Show our own button.
	 *
	 * @since   0.4.0
	 * @param   string  $editor_id  The Editor ID.
	 */
	public function media_buttons( $editor_id ) {
		//dashicons-move dashicons-editor-code
?>
<button type="button" class="ocs-shortcode-generator button hidden" data-editor="<?php echo esc_attr( $editor_id ); ?>">
	<span class="dashicons dashicons-editor-contract" style="vertical-align: text-top; margin: -1px 0 -1px -2px; color: #82878c;"></span>
	<?php esc_html_e( 'Off-Canvas trigger', OCS_DOMAIN ); ?>
</button>
<?php

	}

	/**
	 * Add our tinyMCE plugin to the list.
	 *
	 * @since   0.4.0
	 * @param   array  $plugin_array
	 * @return  array
	 */
	public function mce_external_plugins( $plugin_array ) {
		$plugin_array['off_canvas_sidebars'] = OCS_PLUGIN_URL . 'tinymce/mce-plugin-shortcode.js';
		return $plugin_array;
	}

	/**
	 * Add our tinyMCE styles.
	 *
	 * @since   0.4.0
	 * @param   array  $init_array
	 * @return  array
	 */
	public function mce_inline_css( $init_array ) {

		$styles = '.mceItem.ocsTrigger { border: 1px dashed #0073aa; display: inline-block; } ';
		$styles .= 'img.mceItem.ocsTrigger { padding: 5px; } ';
		$styles .= '.mceItem.ocsTrigger img { margin: 5px; } ';

		if ( isset( $init_array['content_style'] ) ) {
			$init_array['content_style'] .= ' ' . $styles . ' ';
		} else {
			$init_array['content_style'] = $styles . ' ';
		}

		return $init_array;
	}

	/**
	 * Print our scripts.
	 *
	 * @since  0.4.0
	 * @since  0.5.1  Use Control Trigger class.
	 * @see    `after_wp_tiny_mce` hook
	 */
	public function print_scripts() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$settings = off_canvas_sidebars()->get_settings();
?>
<script type="text/javascript" id="ocs-mce-settings">
	var ocsMceSettings = {
		prefix: '<?php echo $settings['css_prefix']; ?>',
		title: '<?php esc_html_e( 'Off-Canvas Sidebars - Trigger Control Shortcode', OCS_DOMAIN ); ?>',
		fields: <?php echo wp_json_encode( $this->get_fields() ); ?>,
		elements: <?php echo wp_json_encode( OCS_Off_Canvas_Sidebars_Control_Trigger::$control_elements ); ?>,
		render: <?php echo ( $settings['wp_editor_shortcode_rendering'] ) ? 'true' : 'false'; ?>
	};
</script>
<?php
		$done = true;
	}

	/**
	 * Admin print scripts callback.
	 *
	 * @since 0.5.5
	 */
	public function admin_print_scripts() {
		if ( self::is_gutenberg_page() ) {
			add_filter( 'admin_head', array( $this, 'print_scripts' ) );
		}
	}

	/**
	 * Get control trigger fields and convert them to TinyMCE structure.
	 *
	 * @since   0.5.1
	 * @return  array
	 */
	public function get_fields() {

		$fields = OCS_Off_Canvas_Sidebars_Control_Trigger::get_fields_by_group( 'basic' );
		// Remove select option for Sidebar ID.
		array_shift( $fields['id']['options'] );

		$fields['advanced_options'] = array(
			'type' => 'container',
			'html' => '<b style="font-weight: 600 !important;">' . __( 'Advanced options', OCS_DOMAIN ) . ':</b>',
		);
		$fields = array_merge( $fields, OCS_Off_Canvas_Sidebars_Control_Trigger::get_fields_by_group( 'advanced' ) );

		foreach ( $fields as $key => $field ) {
			switch ( $field['type'] ) {
				case 'text':
					$field['type'] = 'textbox';
					break;
				case 'select':
					$field['type'] = 'listbox';
					break;
			}
			if ( isset( $field['description'] ) ) {
				$field['tooltip'] = $field['description'];
				unset( $field['description'] );
			}
			if ( isset( $field['options'] ) ) {
				$field['values'] = $field['options'];
				unset( $field['options'] );
				foreach ( $field['values'] as $vkey => $value ) {
					if ( isset( $value['label'] ) ) {
						$field['values'][ $vkey ]['text'] = $value['label'];
						unset( $field['values'][ $vkey ]['label'] );
					}
				}
			}
			if ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			$fields[ $key ] = $field;
		}
		return $fields;
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.4.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Mce_Shortcode
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
