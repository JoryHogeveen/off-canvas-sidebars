<?php
/**
 * Off-Canvas Sidebars - Class Tab_Shortcode
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab shortcode
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.0
 * @version 0.5.4
 * @uses    \OCS_Off_Canvas_Sidebars_Tab Extends class
 */
final class OCS_Off_Canvas_Sidebars_Tab_Shortcode extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Tab_Shortcode
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	/**
	 * Class constructor.
	 * @since   0.1.0
	 * @since   0.3.0  Private constructor.
	 * @since   0.5.0  Protected constructor. Refactor into separate tab classes and methods.
	 * @access  protected
	 */
	protected function __construct() {
		$this->tab  = 'ocs-shortcode';
		$this->name = esc_attr__( 'Shortcode', OCS_DOMAIN );
		parent::__construct();
	}

	/**
	 * Initialize this tab.
	 * @since   0.5.0
	 */
	public function init() {
		add_filter( 'ocs_page_form_do_submit', '__return_false' );
		add_filter( 'ocs_page_form_do_settings_fields', '__return_false' );
		add_filter( 'ocs_page_form_do_sections', '__return_false' );
		add_action( 'ocs_page_form', array( $this, 'tab_content' ) );
	}

	/**
	 * Register settings.
	 * @since   0.1.0
	 * @since   0.5.0  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		//parent::register_settings();

		do_action( 'off_canvas_sidebar_settings_' . $this->filter );
	}

	/**
	 * Tab content.
	 *
	 * @since   0.5.0
	 */
	public function tab_content() {

		?>
		<div id="section_shortcode" class="postbox">
			<h3 class="hndle"><span><?php esc_html_e( 'Shortcode', OCS_DOMAIN ); ?>:</span></h3>
			<div class="inside">
				<textarea id="ocs_shortcode" class="widefat">[ocs_trigger id=""]</textarea>
			</div></div>
		<?php

		echo '<div id="section_shortcode_options" class="postbox postbox postbox-third first">';

		echo '<h3 class="hndle"><span>' . esc_html__( 'Basic options', OCS_DOMAIN ) . ':</span></h3>';

		echo '<div class="inside"><table class="form-table">';

		$fields = OCS_Off_Canvas_Sidebars_Control_Trigger::get_fields_by_group( 'basic' );
		$this->render_table_fields( $fields );

		echo '</table></div></div>';

		echo '<div id="section_shortcode_optionaloptions" class="postbox postbox postbox-third">';

		echo '<h3 class="hndle"><span>' . esc_html__( 'Advanced options', OCS_DOMAIN ) . ':</span></h3>';

		echo '<div class="inside"><table class="form-table">';

		$fields = OCS_Off_Canvas_Sidebars_Control_Trigger::get_fields_by_group( 'advanced' );

		if ( isset( $fields['element']['options'] ) ) {
			// Add select option to the `element` field.
			array_unshift( $fields['element']['options'], array(
				'value' => '',
				'label' => ' - ' . __( 'Select', OCS_DOMAIN ) . ' - ',
			) );
		}

		$this->render_table_fields( $fields );

		echo '</table></div></div>';
		?>
		<div id="section_shortcode_preview" class="postbox postbox-third">
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
	 * Render table fields.
	 *
	 * @since  0.5.1
	 * @param  array  $fields
	 */
	public function render_table_fields( $fields ) {
		foreach ( $fields as $field ) {
			echo '<tr><td>';
			switch ( $field['type'] ) {
				case 'text':
					$field['value'] = '';
					$field['class'] = 'widefat';
					OCS_Off_Canvas_Sidebars_Form::text_option( $field );
					break;
				case 'select':
					OCS_Off_Canvas_Sidebars_Form::select_option( $field );
					break;
				case 'radio':
					OCS_Off_Canvas_Sidebars_Form::radio_option( $field );
					break;
				case 'checkbox':
					$field['value'] = '';
					OCS_Off_Canvas_Sidebars_Form::checkbox_option( $field );
					break;
				case 'number':
					$field['value'] = '';
					$field['class'] = 'widefat';
					OCS_Off_Canvas_Sidebars_Form::number_option( $field );
					break;
				case 'color':
					$field['value'] = '';
					$field['class'] = 'widefat';
					OCS_Off_Canvas_Sidebars_Form::color_option( $field );
					break;
			}
			echo '</td></tr>';
		}
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Tab_Shortcode
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
