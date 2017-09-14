<?php
/**
 * Off-Canvas Sidebars - Class Tab_Shortcode
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab shortcode
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @since   0.5
 * @version 0.5
 */
final class OCS_Off_Canvas_Sidebars_Tab_Shortcode extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Tab_Shortcode
	 * @since  0.3
	 */
	protected static $_instance = null;

	/**
	 * Class constructor.
	 * @since   0.1
	 * @since   0.3  Private constructor.
	 * @since   0.5  Protected constructor. Refactor into separate tab classes and methods.
	 * @access  private
	 */
	protected function __construct() {
		$this->tab = 'ocs-shortcode';
		$this->name = esc_attr__( 'Shortcode', OCS_DOMAIN );
		parent::__construct();
	}

	/**
	 * Initialize this tab.
	 * @since   1.5
	 */
	public function init() {
		add_filter( 'ocs_page_form_do_submit', '__return_false' );
		add_filter( 'ocs_page_form_do_settings_fields', '__return_false' );
		add_filter( 'ocs_page_form_do_sections', '__return_false' );
		add_action( 'ocs_page_form', array( $this, 'tab_content' ) );
	}

	/**
	 * Register settings.
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		//parent::register_settings();

		do_action( 'off_canvas_sidebar_settings_shortcode' );
	}

	/**
	 * Tab content.
	 * @since   0.5
	 */
	public function tab_content() {

		?>
		<div id="section_shortcode" class="stuffbox postbox">
			<h3 class="hndle"><span><?php esc_html_e( 'Shortcode', OCS_DOMAIN ); ?>:</span></h3>
			<div class="inside">
				<textarea id="ocs_shortcode" class="widefat">[ocs_trigger id=""]</textarea>
			</div></div>
		<?php

		echo '<div id="section_shortcode_options" class="stuffbox postbox postbox postbox-third first">';

		echo '<h3 class="hndle"><span>' . __( 'Required options', OCS_DOMAIN ) . ':</span></h3>';

		echo '<div class="inside"><table class="form-table">';
		echo '<tr><td>';

		$sidebar_select = array();
		foreach ( (array) off_canvas_sidebars()->get_sidebars() as $sidebar_id => $sidebar_data ) {
			$sidebar_select[] = array(
				'value' => $sidebar_id,
				'label' => $sidebar_data['label'],
			);
		}
		OCS_Off_Canvas_Sidebars_Form::select_option( array(
			'name' => 'id',
			'label' => __( 'Sidebar ID', OCS_DOMAIN ),
			'description' => __( '(Required) The off-canvas sidebar ID', OCS_DOMAIN ),
			'options' => $sidebar_select,
		) );

		echo '</td></tr>';
		echo '<tr><td>';

		OCS_Off_Canvas_Sidebars_Form::text_option( array(
			'name'        => 'text',
			'label'       => __( 'Text', OCS_DOMAIN ),
			'value'       => '',
			'class'       => 'widefat',
			'description' => __( 'Limited HTML allowed', OCS_DOMAIN ),
			'multiline'   => true,
		) );

		echo '</td></tr>';
		echo '</table></div></div>';

		echo '<div id="section_shortcode_optionaloptions" class="stuffbox postbox postbox postbox-third">';

		echo '<h3 class="hndle"><span>' . __( 'Optional options', OCS_DOMAIN ) . ':</span></h3>';

		echo '<div class="inside"><table class="form-table">';
		echo '<tr><td>';

		OCS_Off_Canvas_Sidebars_Form::select_option( array(
			'name' => 'action',
			'label' => __( 'Trigger action', OCS_DOMAIN ),
			'options' => array(
				array(
					'label' => __( 'Toggle', OCS_DOMAIN ) . ' (' . __( 'Default', OCS_DOMAIN ) . ')',
					'value' => '',
				),
				array(
					'label' => __( 'Open', OCS_DOMAIN ),
					'value' => 'open',
				),
				array(
					'label' => __( 'Close', OCS_DOMAIN ),
					'value' => 'close',
				),
			),
			//'tooltip' => __( 'The trigger action. Default: toggle', OCS_DOMAIN ),
		) );

		echo '</td></tr>';
		echo '<tr><td>';

		$elements = array( 'button', 'span', 'a', 'b', 'strong', 'i', 'em', 'img', 'div' );
		$element_values = array();
		$element_values[] = array(
			'value' => '',
			'label' => ' - ' . __( 'Select', OCS_DOMAIN ) . ' - ',
		);
		foreach ( $elements as $e ) {
			$element_values[] = array(
				'value' => $e,
				'label' => '' . $e . '',
			);
		}
		OCS_Off_Canvas_Sidebars_Form::select_option( array(
			'name' => 'element',
			'label' => __( 'HTML element', OCS_DOMAIN ),
			'options' => $element_values,
			'description' => __( 'Choose wisely', OCS_DOMAIN ) . '. ' . __( 'Default', OCS_DOMAIN ) . ': <code>button</code>',
		) );

		echo '</td></tr>';
		echo '<tr><td>';

		OCS_Off_Canvas_Sidebars_Form::text_option( array(
			'name' => 'class',
			'label' => __( 'Extra classes', OCS_DOMAIN ),
			'value' => '',
			'class' => 'widefat',
			'description' => __( 'Separate multiple classes with a space', OCS_DOMAIN ),
		) );

		echo '</td></tr>';
		echo '<tr><td>';

		OCS_Off_Canvas_Sidebars_Form::text_option( array(
			'name' => 'attr',
			'label' => __( 'Custom attributes', OCS_DOMAIN ),
			'value' => '',
			'class' => 'widefat',
			'description' => __( 'key : value ; key : value', OCS_DOMAIN ),
			'multiline' => true,
		) );

		echo '</td></tr>';
		echo '<tr><td>';

		OCS_Off_Canvas_Sidebars_Form::checkbox_option( array(
			'name' => 'nested',
			'label' => __( 'Nested shortcode', OCS_DOMAIN ) . '?',
			'value' => '',
			'description' => __( '[ocs_trigger text="Your text"] or [ocs_trigger]Your text[/ocs_trigger]', OCS_DOMAIN ),
		) );

		echo '</td></tr>';

		echo '</table></div></div>';
		?>
		<div id="section_shortcode_preview" class="stuffbox postbox postbox-third">
			<h3 class="hndle"><span><?php esc_html_e( 'Preview', OCS_DOMAIN ); ?>:</span></h3>
			<div class="inside">
				<div id="ocs_shortcode_preview"></div>
			</div>
			<h3 class="hndle"><span>HTML:</span></h3>
			<div class="inside">
				<textarea id="ocs_shortcode_html" class="widefat"></textarea>
			</div></div>
		<?php
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Tab_Shortcode
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
