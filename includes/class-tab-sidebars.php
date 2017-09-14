<?php
/**
 * Off-Canvas Sidebars plugin tab sidebars
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @since   0.5
 * @version 0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

final class OCS_Off_Canvas_Sidebars_Tab_Sidebars extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Tab_Sidebars
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
		$this->tab = 'ocs-sidebars';
		$this->name = esc_attr__( 'Sidebars', OCS_DOMAIN );
		parent::__construct();
	}

	/**
	 * Initialize this tab.
	 * @since   0.5
	 */
	public function init() {
		add_action( 'ocs_page_form_before', array( $this, 'ocs_page_form_before' ) );
		add_action( 'ocs_page_form_section_before', array( $this, 'ocs_page_form_section_before' ) );
		add_action( 'ocs_page_form_section_after', array( $this, 'ocs_page_form_section_after' ) );
		add_filter( 'ocs_page_form_section_box_classes', array( $this, 'ocs_page_form_section_box_classes' ) );
	}

	/**
	 * Before form fields.
	 * @since   0.5
	 */
	public function ocs_page_form_before() {
		?>
	<p>
	<?php esc_html_e( 'Add a new sidebar', OCS_DOMAIN ); ?> <input name="<?php echo esc_attr( $this->key ) . '[sidebars][ocs_add_new]'; ?>" value="" type="text" placeholder="<?php esc_html_e( 'Name', OCS_DOMAIN ); ?>" />
	<?php submit_button( __( 'Add sidebar', OCS_DOMAIN ), 'primary', 'submit', false ); ?>
	</p>
		<?php
	}

	/**
	 * Before sections (in table).
	 * @since   0.5
	 */
	public function ocs_page_form_section_before() {
		$css_prefix = off_canvas_sidebars()->get_settings( 'css_prefix' );
		echo '<tr class="sidebar_classes" style="display: none;"><th>' . esc_html__( 'ID & Classes', OCS_DOMAIN ) . '</th><td>';
		echo  esc_html__( 'Sidebar ID', OCS_DOMAIN ) . ': <code>#' . $css_prefix . '-<span class="js-dynamic-id"></span></code> &nbsp; '
		      . esc_html__( 'Trigger Classes', OCS_DOMAIN ) . ': <code>.' . $css_prefix . '-toggle-<span class="js-dynamic-id"></span></code> <code>.' . $css_prefix . '-open-<span class="js-dynamic-id"></span></code> <code>.' . $css_prefix . '-close-<span class="js-dynamic-id"></span></code>';
		echo '</td></tr>';
	}

	/**
	 * After sections.
	 * @since   0.5
	 */
	public function ocs_page_form_section_after() {
		submit_button( null, 'primary', 'submit', false );
	}

	/**
	 * Section postbox classes.
	 * @since   0.5
	 */
	public function ocs_page_form_section_box_classes( $classes ) {
		$classes .= ' section-sidebar if-js-closed';
		return $classes;
	}

	/**
	 * Register settings.
	 * @since   0.1
	 * @since   0.5  Refactor into separate tab classes and methods
	 */
	public function register_settings() {
		parent::register_settings();
		$sidebars = off_canvas_sidebars()->get_sidebars();

		// Register sidebar settings.
		foreach ( $sidebars as $sidebar => $sidebar_data ) {
			add_settings_section(
				'section_sidebar_' . $sidebar,
				__( 'Off-Canvas Sidebar', OCS_DOMAIN ) . ' - <code class="js-dynamic-id">' . $sidebars[ $sidebar ]['label'] . '</code>',
				array( $this, 'section_callback' ),
				$this->tab
			);
		}

		do_action( 'off_canvas_sidebar_settings_sidebars' );
	}

	/**
	 * Callback function to create sidebar sections.
	 * @since   0.5
	 * @param   array  $args  Callback params.
	 */
	public function section_callback( $args ) {
		$sidebar_id = str_replace( 'section_sidebar_', '', $args['id'] );
		$this->register_sidebar_settings( $sidebar_id );
	}

	/**
	 * Sidebar settings.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.1
	 * @param   string  $sidebar_id
	 */
	public function register_sidebar_settings( $sidebar_id ) {

		add_settings_field(
			'sidebar_enable',
			esc_attr__( 'Enable', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'enable',
			)
		);
		add_settings_field(
			'sidebar_id',
			esc_attr__( 'ID', OCS_DOMAIN ) . ' <span class="required">*</span>',
			array( 'OCS_Off_Canvas_Sidebars_Form', 'text_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'id',
				'value' => $sidebar_id,
				'required' => true,
				'description' => __( 'IMPORTANT: Must be unique!', OCS_DOMAIN ),
			)
		);
		add_settings_field(
			'sidebar_label',
			esc_attr__( 'Name', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'text_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'label',
			)
		);
		add_settings_field(
			'sidebar_content',
			esc_attr__( 'Content', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'radio_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'content',
				'default' => 'sidebar',
				'options' => array(
					'sidebar' => array(
						'name' => 'sidebar',
						'label' => __( 'Sidebar', OCS_DOMAIN ) . ' &nbsp (' . __( 'Default', OCS_DOMAIN ) . ')',
						'value' => 'sidebar',
					),
					'menu' => array(
						'name' => 'menu',
						'label' => __( 'Menu', OCS_DOMAIN ),
						'value' => 'menu',
					),
					'action' => array(
						'name' => 'action',
						'label' => __( 'Custom', OCS_DOMAIN ) . ' &nbsp; (<a href="https://developer.wordpress.org/reference/functions/add_action/" target="_blank">' . __( 'Action hook', OCS_DOMAIN ) . '</a>: <code>ocs_custom_content_sidebar_<span class="js-dynamic-id"></span></code> )',
						'value' => 'action',
					),
				),
				'description' => __( 'Keep in mind that WordPress has menu and text widgets by default, the "sidebar" object is your best option in most cases.', OCS_DOMAIN ),
			)
		);
		add_settings_field(
			'sidebar_location',
			esc_attr__( 'Location', OCS_DOMAIN ) . ' <span class="required">*</span>',
			array( 'OCS_Off_Canvas_Sidebars_Form', 'sidebar_location' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'required' => true,
			)
		);
		add_settings_field(
			'sidebar_size',
			esc_attr__( 'Size', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'sidebar_size' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'description' => __( 'You can overwrite this with CSS', OCS_DOMAIN ),
			)
		);
		add_settings_field(
			'sidebar_style',
			esc_attr__( 'Style', OCS_DOMAIN ) . ' <span class="required">*</span>',
			array( 'OCS_Off_Canvas_Sidebars_Form', 'sidebar_style' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'required' => true,
			)
		);
		add_settings_field(
			'animation_speed',
			esc_attr__( 'Animation speed', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'number_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'animation_speed',
				'description' =>
					__( 'Set the animation speed for showing and hiding this sidebar. Default: 300ms', OCS_DOMAIN ) . '<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
				'input_after' => '<code>ms</code>',
			)
		);
		add_settings_field(
			'padding',
			esc_attr__( 'Padding', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'number_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'padding',
				'description' =>
					__( 'Add CSS padding (in pixels) to this sidebar. Default: none', OCS_DOMAIN ) . '<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
				'input_after' => '<code>px</code>',
			)
		);
		add_settings_field(
			'background_color',
			esc_attr__( 'Background color', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'color_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'background_color',
				'description' =>
					__( 'Choose a background color for this sidebar. Default: <code>#222222</code>.', OCS_DOMAIN ) . '<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
			)
		);

		add_settings_field(
			'overwrite_global_settings',
			esc_attr__( 'Overwrite global settings', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'overwrite_global_settings',
			)
		);
		add_settings_field(
			'site_close',
			esc_attr__( 'Close sidebar when clicking on the site', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'site_close',
				'label' => __( 'Enables closing of a off-canvas sidebar by clicking on the site. Default: true.', OCS_DOMAIN ),
			)
		);
		add_settings_field(
			'disable_over',
			esc_attr__( 'Disable over', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'number_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'disable_over',
				'description' => __( 'Disable off-canvas sidebars over specified screen width. Leave blank to disable.', OCS_DOMAIN ),
				'input_after' => '<code>px</code>',
			)
		);
		add_settings_field(
			'hide_control_classes',
			esc_attr__( 'Auto-hide control classes', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'hide_control_classes',
				'label' => __( 'Hide off-canvas sidebar control classes over width specified in <strong>"Disable over"</strong>. Default: false.', OCS_DOMAIN ),
			)
		);
		add_settings_field(
			'scroll_lock',
			esc_attr__( 'Scroll lock', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'scroll_lock',
				'label' => __( 'Prevent site content scrolling whilst a off-canvas sidebar is open. Default: false.', OCS_DOMAIN ),
			)
		);

		add_settings_field(
			'sidebar_delete',
			esc_attr__( 'Delete sidebar', OCS_DOMAIN ),
			array( 'OCS_Off_Canvas_Sidebars_Form', 'checkbox_option' ),
			$this->tab,
			'section_sidebar_' . $sidebar_id,
			array(
				'sidebar' => $sidebar_id,
				'name' => 'delete',
				'value' => 0,
			)
		);

	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Tab_Sidebars
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
