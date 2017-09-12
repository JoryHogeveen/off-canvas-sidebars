<?php
/**
 * Off-Canvas Sidebars plugin shortcode generator
 *
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

final class OCS_Off_Canvas_Sidebars_Editor_Shortcode_Generator extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Editor_Shortcode_Generator
	 * @since  0.4
	 */
	protected static $_instance = null;

	private $settings = array();

	/**
	 * This class gets called in the init hook.
	 *
	 * @since  0.4
	 * @access private
	 */
	private function __construct() {
		// Check user permissions.
		/*if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}*/

		if ( 'true' === get_user_option( 'rich_editing' ) ) {
			//add_action( 'admin_init', array( $this, 'load_plugin_data' ) );
			$this->load_plugin_data();
			add_action( 'media_buttons', array( $this, 'media_buttons' ), 20 );
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
			add_filter( 'tiny_mce_before_init', array( $this, 'mce_inline_css' ) );
			add_action( 'after_wp_tiny_mce', array( $this, 'print_scripts' ) );
		}
	}

	/**
	 * Get plugin defaults.
	 * @since  0.4
	 */
	function load_plugin_data() {
		$this->settings = off_canvas_sidebars()->get_settings();
	}

	/**
	 * Show our own button.
	 *
	 * @since  0.4
	 * @param  string  $editor_id  The Editor ID.
	 */
	function media_buttons( $editor_id ) {
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
	 * @since  0.4
	 * @param  array  $plugin_array
	 * @return array
	 */
	function mce_external_plugins( $plugin_array ) {
		$plugin_array['off_canvas_sidebars'] = OCS_PLUGIN_URL . 'js/mce-plugin-shortcode.js';
		return $plugin_array;
	}

	/**
	 * Add our tinyMCE styles.
	 *
	 * @since  0.4
	 * @param  $init_array
	 * @return array
	 */
	function mce_inline_css( $init_array ) {

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
	 * @since  0.4
	 * @see    `after_wp_tiny_mce` hook
	 */
	function print_scripts() {
		static $done = false;
		if ( $done ) {
			return;
		}

		$sidebars = array();
		foreach ( $this->settings['sidebars'] as $sidebar_id => $sidebar_data ) {
			if ( empty( $sidebar_data['enable'] ) ) {
				continue;
			}
			$label = $sidebar_id;
			if ( ! empty( $sidebar_data['label'] ) ) {
				$label = $sidebar_data['label'] . ' (' . $sidebar_id . ')';
			}
			$sidebars[] = array(
				'text'  => $label,
				'value' => $sidebar_id,
			);
		}

		$elements = array( 'button', 'span', 'a', 'b', 'strong', 'i', 'em', 'img', 'div' );
		$element_values = array();
		foreach ( $elements as $e ) {
			$element_values[] = array(
				'text'  => '<' . $e . '>',
				'value' => $e,
			);
		}

		$fields = array(
			'id' => array(
				'type'    => 'listbox',
				'name'    => 'id',
				'label'   => __( 'Sidebar ID', OCS_DOMAIN ),
				'value'   => '',
				'values'  => $sidebars,
				'tooltip' => __( '(Required) The off-canvas sidebar ID', OCS_DOMAIN ),
			),
			'text' => array(
				'type'      => 'textbox',
				'name'      => 'text',
				'label'     => __( 'Text', OCS_DOMAIN ),
				'value'     => '',
				'tooltip'   => __( 'Limited HTML allowed', OCS_DOMAIN ),
				'multiline' => true,
			),
			'optional_container' => array(
				'type' => 'container',
				'html' => '<b style="font-weight: 600 !important;">' . __( 'Optional options', OCS_DOMAIN ) . ':</b>',
			),
			'action' => array(
				'type'   => 'listbox',
				'name'   => 'action',
				'label'  => __( 'Trigger action', OCS_DOMAIN ),
				'value'  => '',
				'values' => array(
					array(
						'text'  => __( 'Toggle', OCS_DOMAIN ) . ' (' . __( 'Default', OCS_DOMAIN ) . ')',
						'value' => '',
					),
					array(
						'text' => __( 'Open', OCS_DOMAIN ),
						'value' => 'open',
					),
					array(
						'text' => __( 'Close', OCS_DOMAIN ),
						'value' => 'close',
					),
				),
				//'tooltip' => __( 'The trigger action. Default: toggle', OCS_DOMAIN ),
			),
			'element' => array(
				'type'    => 'listbox',
				'name'    => 'element',
				'label'   => __( 'HTML element', OCS_DOMAIN ),
				'value'   => '',
				'values'  => $element_values,
				'tooltip' => __( 'Choose wisely', OCS_DOMAIN ),
			),
			'class' => array(
				'type'    => 'textbox',
				'name'    => 'class',
				'label'   => __( 'Extra classes', OCS_DOMAIN ),
				'value'   => '',
				'tooltip' => __( 'Separate multiple classes with a space', OCS_DOMAIN ),
			),
			'attr' => array(
				'type'      => 'textbox',
				'name'      => 'attr',
				'label'     => __( 'Custom attributes', OCS_DOMAIN ),
				'value'     => '',
				'tooltip'   => __( 'key : value ; key : value', OCS_DOMAIN ),
				'multiline' => true,
			),
			'nested' => array(
				'type'    => 'checkbox',
				'name'    => 'nested',
				'label'   => __( 'Nested shortcode', OCS_DOMAIN ) . '?',
				'value'   => '',
				'tooltip' => __( '[ocs_trigger text="Your text"] or [ocs_trigger]Your text[/ocs_trigger]', OCS_DOMAIN ),
			),
		);
?>
<script type="text/javascript" id="ocs-mce-settings">
	var ocsMceSettings = {
		prefix: '<?php echo $this->settings['css_prefix']; ?>',
		title: '<?php esc_html_e( 'Off-Canvas Sidebars - Trigger Control Shortcode', OCS_DOMAIN ); ?>',
		fields: <?php echo wp_json_encode( $fields ); ?>,
		elements: <?php echo wp_json_encode( $elements ); ?>,
		render: <?php echo ( $this->settings['wp_editor_shortcode_rendering'] ) ? 'true' : 'false'; ?>
	};
</script>
<?php
		$done = true;
	}

	/**
	 * Main Off-Canvas Sidebars Shortcode Generator Instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.4
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Editor_Shortcode_Generator
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
