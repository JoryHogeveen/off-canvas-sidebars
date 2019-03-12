<?php
/**
 * Off-Canvas Sidebars - Class Control_Block
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars control block (Gutenberg).
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.6.0
 * @version 0.6.0
 */
final class OCS_Off_Canvas_Sidebars_Control_Block extends OCS_Off_Canvas_Sidebars_Base
{
	private $type = 'off-canvas-sidebars/control-block';

	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Control_Block
	 * @since  0.6.0
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		//add_action( 'enqueue_block_editor_assets', array( $this, 'register' ) );
	}

	/**
	 * Register block.
	 */
	public function register() {
		$dir = OCS_PLUGIN_DIR . 'block/';
		$url = OCS_PLUGIN_URL . 'block/';

		$handle  = 'off-canvas-sidebars-control-block';
		$version = filemtime( $dir . 'control-block.js' );

		wp_register_script(
			$handle,
			$url . 'control-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			$version,
			true
		);

		/*wp_register_style(
			'off-canvas-sidebars-control-block',
			$url . 'control-block.css',
			array( 'wp-edit-blocks' ),
			filemtime( $dir . 'control-block.css' )
		);*/

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				$this->type,
				array(
					'editor_script'   => $handle,
					//'editor_style'    => $handle,
					'render_callback' => array( $this, 'render' ), //the_ocs_control_trigger
					'attributes'      => $this->get_attributes(),
				)
			);
		}

		wp_localize_script(
			$handle,
			'ocsOffCanvasSidebarsBlock',
			array(
				'type'          => $this->type,
				'fields'        => $this->get_fields(),
				'__title'       => __( 'Off-Canvas Control', OCS_DOMAIN ),
				'__description' => __( 'Trigger off-canvas sidebars', OCS_DOMAIN ),
			)
		);
	}

	/**
	 * Get block attributes.
	 * @return array
	 */
	public function get_attributes() {
		$fields = $this->get_fields();

		$attributes = array(
			'align' => array(
				'type' => 'string',
			),
		);

		foreach ( $fields as $field ) {
			$attribute = array(
				'type' => 'string',
			);
			if ( isset( $field['default'] ) ) {
				$attribute['default'] = (string) $field['default'];
			}
			$attributes[ $field['name'] ] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Get block fields.
	 * @return array
	 */
	public function get_fields() {
		$fields = OCS_Off_Canvas_Sidebars_Control_Trigger::get_fields();
		unset( $fields['nested'] );
		return $fields;
	}

	/**
	 * Render block.
	 * @param  array  $atts
	 * @return string
	 */
	public function render( $atts ) {

		if ( $atts['align'] ) {
			$align = $atts['align'];
			switch ( $align ) {
				case 'center':
				case 'right':
				case 'left':
					$align = 'align' . $align;
					break;
			}
			if ( empty( $atts['class'] ) ) {
				$atts['class'] = array();
			}
			$atts['class']   = (array) $atts['class'];
			$atts['class'][] = $align;
		}
		unset( $atts['align'] );

		$atts['echo'] = false;
		return the_ocs_control_trigger( $atts );
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.6.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Control_Block
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}