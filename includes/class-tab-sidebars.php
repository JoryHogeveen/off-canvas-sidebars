<?php
/**
 * Off-Canvas Sidebars - Class Tab_Sidebars
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin tab sidebars
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.0
 * @version 0.5.2
 * @uses    \OCS_Off_Canvas_Sidebars_Tab Extends class
 */
final class OCS_Off_Canvas_Sidebars_Tab_Sidebars extends OCS_Off_Canvas_Sidebars_Tab
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Tab_Sidebars
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
		$this->tab  = 'ocs-sidebars';
		$this->name = esc_attr__( 'Sidebars', OCS_DOMAIN );
		parent::__construct();

		add_filter( 'ocs_settings_parse_input', array( $this, 'parse_input' ), 11, 2 );
		add_filter( 'ocs_settings_validate_input', array( $this, 'validate_input' ), 11, 2 );
	}

	/**
	 * Initialize this tab.
	 * @since   0.5.0
	 */
	public function init() {
		add_action( 'ocs_page_form_before', array( $this, 'ocs_page_form_before' ) );
		add_action( 'ocs_page_form_section_table_before', array( $this, 'ocs_page_form_section_table_before' ) );
		add_action( 'ocs_page_form_section_after', array( $this, 'ocs_page_form_section_after' ) );
		add_filter( 'ocs_page_form_section_box_classes', array( $this, 'ocs_page_form_section_box_classes' ) );
	}

	/**
	 * Before form fields.
	 * @since   0.5.0
	 */
	public function ocs_page_form_before() {
		?>
	<p>
	<?php esc_html_e( 'Add a new sidebar', OCS_DOMAIN ); ?> <input name="<?php echo esc_attr( $this->key ) . '[sidebars][_ocs_add_new]'; ?>" value="" type="text" placeholder="<?php esc_html_e( 'Name', OCS_DOMAIN ); ?>" />
	<?php submit_button( __( 'Add sidebar', OCS_DOMAIN ), 'primary', 'submit', false ); ?>
	</p>
		<?php
	}

	/**
	 * Before sections (in table).
	 * @since   0.5.0
	 */
	public function ocs_page_form_section_table_before() {
		$css_prefix = $this->get_settings( 'css_prefix' );
		echo '<tr class="sidebar_classes" style="display: none;"><th>' . esc_html__( 'ID & Classes', OCS_DOMAIN ) . '</th><td>';
		echo  esc_html__( 'Sidebar ID', OCS_DOMAIN ) . ': <code>#' . $css_prefix . '-<span class="js-dynamic-id"></span></code> &nbsp; '
		      . esc_html__( 'Trigger Classes', OCS_DOMAIN ) . ': <code>.' . $css_prefix . '-toggle-<span class="js-dynamic-id"></span></code> <code>.' . $css_prefix . '-open-<span class="js-dynamic-id"></span></code> <code>.' . $css_prefix . '-close-<span class="js-dynamic-id"></span></code>';
		echo '</td></tr>';
	}

	/**
	 * After sections.
	 * @since   0.5.0
	 */
	public function ocs_page_form_section_after() {
		submit_button( null, 'primary', 'submit', false );
	}

	/**
	 * Section postbox classes.
	 * @since   0.5.0
	 * @param   string  $classes  Existing classes.
	 * @return  string
	 */
	public function ocs_page_form_section_box_classes( $classes ) {
		$classes .= ' section-sidebar if-js-closed';
		return $classes;
	}

	/**
	 * Register settings.
	 *
	 * @since   0.1.0
	 * @since   0.5.0  Refactor into separate tab classes and methods.
	 */
	public function register_settings() {
		parent::register_settings();
		$sidebars = off_canvas_sidebars()->get_sidebars();

		// Register sidebar settings.
		foreach ( $sidebars as $sidebar => $sidebar_data ) {
			$label = $sidebars[ $sidebar ]['label'];
			$sep   = ' &nbsp; | &nbsp; ';
			add_settings_section(
				'section_sidebar_' . $sidebar,
				$label . $sep . '<code class="js-dynamic-id">' . $sidebar . '</code>',
				array( $this, 'register_sidebar_fields' ),
				$this->tab
			);
		}

		foreach ( $this->get_tab_fields() as $key => $field ) {
			$this->add_settings_field( $key, $field );
		}

		do_action( 'off_canvas_sidebar_settings_' . $this->filter );
	}

	/**
	 * Sidebar settings.
	 *
	 * @since   0.5.0
	 * @param   array  $args {
	 *     @type  string        $id
	 *     @type  string        $title
	 *     @type  array|string  $callback
	 * }
	 */
	public function register_sidebar_fields( $args ) {
		$sidebar_id = str_replace( 'section_sidebar_', '', $args['id'] );

		$fields  = $this->get_settings_fields();
		$section = 'section_sidebar_' . $sidebar_id;

		foreach ( $fields as $id => $args ) {

			if ( ! empty( $args['hidden'] ) ) {
				continue;
			}

			$title = $args['title'];
			unset( $args['title'] );

			$callback = $args['callback'];
			unset( $args['callback'] );
			if ( is_string( $callback ) ) {
				$callback = array( 'OCS_Off_Canvas_Sidebars_Form', $callback );
			}

			$args['sidebar'] = $sidebar_id;

			if ( 'id' === $id ) {
				$args['value'] = $sidebar_id;
			}

			add_settings_field( $id, $title, $callback, $this->tab, $section, $args );
		}
	}

	/**
	 * Parses sidebar post values, checks all values with the current existing data.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.4.0
	 * @since   0.5.0  Moved to this class.
	 * @param   array  $input
	 * @param   array  $current
	 * @return  array
	 */
	public function parse_input( $input, $current ) {
		if ( empty( $input['sidebars'] ) ) {
			// Somehow the sidebars were removed on submit. Sidebars can only be removed with the delete option.
			if ( ! empty( $current['sidebars'] ) ) {
				$input['sidebars'] = $current['sidebars'];
			}
			return $input;
		}

		$is_request_tab = $this->is_request_tab();

		// Add new sidebar.
		if ( ! empty( $input['sidebars']['_ocs_add_new'] ) ) {
			$new_sidebar_id = OCS_Off_Canvas_Sidebars_Settings::validate_id( $input['sidebars']['_ocs_add_new'] );
			if ( $new_sidebar_id && empty( $input['sidebars'][ $new_sidebar_id ] ) && empty( $current['sidebars'][ $new_sidebar_id ] ) ) {
				$input['sidebars'][ $new_sidebar_id ] = array_merge(
					off_canvas_sidebars_settings()->get_default_sidebar_settings(),
					array(
						'enable' => 1,
						'id'     => $new_sidebar_id,
						'label'  => wp_strip_all_tags( stripslashes( $input['sidebars']['_ocs_add_new'] ) ),
					)
				);
			} else {
				add_settings_error(
					$new_sidebar_id . '_duplicate_id',
					esc_attr( 'ocs_duplicate_id' ),
					// Translators: %s stands for a sidebar ID.
					sprintf( __( 'The ID %s already exists! Sidebar not added.', OCS_DOMAIN ), '<code>' . $new_sidebar_id . '</code>' )
				);
			}
		}
		unset( $input['sidebars']['_ocs_add_new'] );

		if ( empty( $current['sidebars'] ) ) {
			return $input;
		}

		$current  = (array) $current['sidebars'];
		$sidebars = (array) $input['sidebars'];

		foreach ( $current as $sidebar_id => $sidebar_data ) {

			if ( ! isset( $sidebars[ $sidebar_id ] ) ) {
				$sidebars[ $sidebar_id ] = $current[ $sidebar_id ];
				// Sidebars are set but this sidebar isn't checked as active.
				$sidebars[ $sidebar_id ]['enable'] = 0;
				continue;
			}

			// Not the current tab, only update `enable`.
			if ( ! $is_request_tab ) {
				$current[ $sidebar_id ]['enable'] = OCS_Off_Canvas_Sidebars_Settings::validate_checkbox( $sidebars[ $sidebar_id ]['enable'] );
				$sidebars[ $sidebar_id ]          = $current[ $sidebar_id ];
				continue;
			}

			// Default label is sidebar ID.
			if ( empty( $sidebars[ $sidebar_id ]['label'] ) ) {
				$sidebars[ $sidebar_id ]['label'] = $sidebar_id;
			}

			// Check checkboxes or they will be overwritten with the current settings.
			foreach ( $this->get_settings_fields_by_type( 'checkbox', true ) as $key ) {
				$sidebars[ $sidebar_id ][ $key ] = OCS_Off_Canvas_Sidebars_Settings::validate_checkbox( $sidebars[ $sidebar_id ], $key );
			}

			// Change sidebar ID.
			if ( ! empty( $sidebars[ $sidebar_id ]['id'] ) && $sidebar_id !== $sidebars[ $sidebar_id ]['id'] ) {

				$new_sidebar_id = OCS_Off_Canvas_Sidebars_Settings::validate_id( $sidebars[ $sidebar_id ]['id'] );

				if ( $sidebar_id !== $new_sidebar_id ) {

					if ( empty( $sidebars[ $new_sidebar_id ] ) ) {

						$sidebars[ $new_sidebar_id ]       = $sidebars[ $sidebar_id ];
						$sidebars[ $new_sidebar_id ]['id'] = $new_sidebar_id;

						unset( $sidebars[ $sidebar_id ] );

						// Migrate existing widgets to the new sidebar.
						OCS_Off_Canvas_Sidebars_Settings::migrate_sidebars_widgets( $sidebar_id, $new_sidebar_id );

					} else {
						add_settings_error(
							$sidebar_id . '_duplicate_id',
							esc_attr( 'ocs_duplicate_id' ),
							sprintf(
								// Translators: %s stands for a sidebar ID.
								__( 'The ID %s already exists! The ID is not changed.', OCS_DOMAIN ),
								'<code>' . $new_sidebar_id . '</code>'
							)
						);
					}
				}
			}
		} // End foreach().

		// Keep order on other pages.
		if ( ! $is_request_tab ) {
			$sidebars = array_merge( $current, $sidebars );
		}

		$input['sidebars'] = $sidebars;
		return $input;
	}

	/**
	 * @since   0.5.0
	 * @param   array  $data
	 * @param   array  $input
	 * @return  array
	 */
	public function validate_input( $data, $input ) {
		if ( ! $this->is_request_tab() ) {
			return $data;
		}

		foreach ( $data['sidebars'] as $sidebar_id => $sidebar_data ) {

			// Delete sidebar. Checks for original (non-parsed) input data.
			if ( ! empty( $input['sidebars'][ $sidebar_id ]['delete'] ) ) {
				unset( $input['sidebars'][ $sidebar_id ] );
				unset( $data['sidebars'][ $sidebar_id ] );
				continue;
			}

			$sidebar = $data['sidebars'][ $sidebar_id ];

			$sidebar = array_merge(
				off_canvas_sidebars_settings()->get_default_sidebar_settings(),
				$sidebar
			);

			$sidebar = OCS_Off_Canvas_Sidebars_Settings::validate_fields( $sidebar, $this->get_settings_fields() );

			$data['sidebars'][ $sidebar_id ] = $sidebar;

			$new_sidebar_id = OCS_Off_Canvas_Sidebars_Settings::validate_id( $sidebar_id );
			if ( $sidebar_id !== $new_sidebar_id ) {
				$data['sidebars'][ $new_sidebar_id ]       = $data['sidebars'][ $sidebar_id ];
				$data['sidebars'][ $new_sidebar_id ]['id'] = $new_sidebar_id;

				unset( $data['sidebars'][ $sidebar_id ] );

				OCS_Off_Canvas_Sidebars_Settings::migrate_sidebars_widgets( $sidebar_id, $new_sidebar_id );
			}
		} // End foreach().

		return $data;
	}

	/**
	 * Register tab fields.
	 * Note that section handling is not done with these fields as they are auto-added for each sidebar as a section.
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.5.0
	 */
	protected function get_tab_fields() {

		// Register sidebar fields.
		return array(
			'enable' => array(
				'name'     => 'enable',
				'title'    => esc_attr__( 'Enable', OCS_DOMAIN ),
				'callback' => 'checkbox_option',
				'type'     => 'checkbox',
			),
			'id' => array(
				'name'        => 'id',
				'title'       => esc_attr__( 'ID', OCS_DOMAIN ) . ' <span class="required">*</span>',
				'callback'    => 'text_option',
				'type'        => 'text',
				'required'    => true,
				'description' => __( 'IMPORTANT: Must be unique!', OCS_DOMAIN ),
			),
			'label' => array(
				'name'     => 'label',
				'title'    => esc_attr__( 'Name', OCS_DOMAIN ),
				'callback' => 'text_option',
				'type'     => 'text',
			),
			'content' => array(
				'name'     => 'content',
				'title'    => esc_attr__( 'Content', OCS_DOMAIN ),
				'callback' => 'radio_option',
				'type'     => 'radio',
				'default'  => 'sidebar',
				'options'  => array(
					'sidebar' => array(
						'name'  => 'sidebar',
						'label' => __( 'Sidebar', OCS_DOMAIN ) . ' &nbsp (' . __( 'Default', OCS_DOMAIN ) . ')',
						'value' => 'sidebar',
					),
					'menu'    => array(
						'name'  => 'menu',
						'label' => __( 'Menu', OCS_DOMAIN ),
						'value' => 'menu',
					),
					'action'  => array(
						'name'  => 'action',
						'label' => __( 'Custom', OCS_DOMAIN ) . ' &nbsp; (<a href="https://developer.wordpress.org/reference/functions/add_action/" target="_blank">' . __( 'Action hook', OCS_DOMAIN ) . '</a>: <code>ocs_custom_content_sidebar_<span class="js-dynamic-id"></span></code> )',
						'value' => 'action',
					),
				),
				'description' => __( 'Keep in mind that WordPress has menu and text widgets by default, the "sidebar" object is your best option in most cases.', OCS_DOMAIN ),
			),
			'location' => array(
				'name'     => 'location',
				'title'    => esc_attr__( 'Location', OCS_DOMAIN ) . ' <span class="required">*</span>',
				'callback' => 'radio_option',
				'type'     => 'radio',
				'required' => true,
				'default'  => 'left',
				'options'  => array(
					'left'   => array(
						'name'  => 'left',
						'label' => esc_html__( 'Left', OCS_DOMAIN ),
						'value' => 'left',
					),
					'right'  => array(
						'name'  => 'right',
						'label' => esc_html__( 'Right', OCS_DOMAIN ),
						'value' => 'right',
					),
					'top'    => array(
						'name'  => 'top',
						'label' => esc_html__( 'Top', OCS_DOMAIN ),
						'value' => 'top',
					),
					'bottom' => array(
						'name'  => 'bottom',
						'label' => esc_html__( 'Bottom', OCS_DOMAIN ),
						'value' => 'bottom',
					),
				),
			),

			// @todo Auto handler for radio options with a custom field.
			'size' => array(
				'name'        => 'size',
				'title'       => esc_attr__( 'Size', OCS_DOMAIN ),
				'callback'    => 'sidebar_size',
				'type'        => 'radio',
				'description' => __( 'You can overwrite this with CSS', OCS_DOMAIN ),
				'default'     => 'default',
				'options'     => array(
					'default' => array(
						'name'  => 'default',
						'label' => esc_html__( 'Default', OCS_DOMAIN ),
						'value' => 'default',
					),
					'small'   => array(
						'name'  => 'small',
						'label' => esc_html__( 'Small', OCS_DOMAIN ),
						'value' => 'small',
					),
					'large'   => array(
						'name'  => 'large',
						'label' => esc_html__( 'Large', OCS_DOMAIN ),
						'value' => 'large',
					),
					'custom'  => array(
						'name'  => 'custom',
						'label' => esc_html__( 'Custom', OCS_DOMAIN ),
						'value' => 'custom',
					),
				),
			),
			// @fixme See above. This only makes sure the fields gets recognized.
			'size_input' => array(
				'name'    => 'size_input',
				'hidden'  => true,
				'default' => '',
				'type'    => 'number',
			),
			// @fixme See above. This only makes sure the fields gets recognized.
			'size_input_type' => array(
				'name'        => 'size_input_type',
				'hidden'      => true,
				'default'     => '%',
				'type'        => 'radio',
				'options'     => array(
					'px' => array(
						'name'  => 'px',
						'label' => 'px',
						'value' => 'px',
					),
					'%'  => array(
						'name'  => '%',
						'label' => '%',
						'value' => '%',
					),
				),
			),
			'sidebar_style' => array(
				'name'     => 'style',
				'title'    => esc_attr__( 'Style', OCS_DOMAIN ) . ' (' . esc_attr__( 'Animation', OCS_DOMAIN ) . ') <span class="required">*</span>',
				'callback' => 'radio_option',
				'type'     => 'radio',
				'required' => true,
				'default'  => 'push',
				'options'  => array(
					'push'    => array(
						'name'  => 'push',
						'label' => esc_html__( 'Sidebar slides and pushes the site across when opened.', OCS_DOMAIN ),
						'value' => 'push',
					),
					'reveal'  => array(
						'name'  => 'reveal',
						'label' => esc_html__( 'Sidebar reveals and pushes the site across when opened.', OCS_DOMAIN ),
						'value' => 'reveal',
					),
					'shift'   => array(
						'name'  => 'shift',
						'label' => esc_html__( 'Sidebar shifts and pushes the site across when opened.', OCS_DOMAIN ),
						'value' => 'shift',
					),
					'overlay' => array(
						'name'  => 'overlay',
						'label' => esc_html__( 'Sidebar overlays the site when opened.', OCS_DOMAIN ),
						'value' => 'overlay',
					),
				),
			),
			'animation_speed' => array(
				'name'        => 'animation_speed',
				'title'       => esc_attr__( 'Animation speed', OCS_DOMAIN ),
				'callback'    => 'number_option',
				'type'        => 'number',
				'description' =>
					__( 'Set the animation speed for showing and hiding this sidebar.', OCS_DOMAIN )
					. '<br>' . __( 'Default', OCS_DOMAIN ) . ': <code>300ms</code>.<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
				'input_after' => '<code>ms</code>',
			),
			'padding' => array(
				'name'        => 'padding',
				'title'       => esc_attr__( 'Padding', OCS_DOMAIN ),
				'callback'    => 'number_option',
				'type'        => 'number',
				'description' =>
					__( 'Add CSS padding (in pixels) to this sidebar.', OCS_DOMAIN )
					. '<br>' . __( 'Default', OCS_DOMAIN ) . ': ' . __( 'none', OCS_DOMAIN ) . '.<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
				'input_after' => '<code>px</code>',
			),
			// @todo Auto handler for radio options with a custom field
			'background_color' => array(
				'name'        => 'background_color',
				'title'       => esc_attr__( 'Background color', OCS_DOMAIN ),
				'callback'    => 'color_option',
				'type'        => 'color',
				'description' =>
					__( 'Choose a background color for this sidebar.', OCS_DOMAIN )
					. '<br>' . __( 'Default', OCS_DOMAIN ) . ': <code>#000000</code>.<br>' .
					__( 'You can overwrite this with CSS', OCS_DOMAIN ),
			),
			// @fixme See above. This only makes sure the fields gets recognized.
			'background_color_type' => array(
				'name'    => 'background_color_type',
				'hidden'  => true,
				'type'    => 'radio',
				'default' => '',
				'options' => array(
					'default'     => array(
						'name'  => 'default',
						'label' => esc_html__( 'Default', OCS_DOMAIN ) . ': <code>#000000</code>',
						'value' => '',
					),
					'transparent' => array(
						'name'  => 'transparent',
						'label' => esc_html__( 'Transparent', OCS_DOMAIN ),
						'value' => 'transparent',
					),
					'color'       => array(
						'name'  => 'color',
						'label' => esc_html__( 'Color', OCS_DOMAIN ),
						'value' => 'color',
					),
				),
			),
			'overwrite_global_settings' => array(
				'name'     => 'overwrite_global_settings',
				'title'    => esc_attr__( 'Overwrite global settings', OCS_DOMAIN ),
				'callback' => 'checkbox_option',
				'type'     => 'checkbox',
			),
			'site_close' => array(
				'name'        => 'site_close',
				'title'       => esc_attr__( 'Close sidebar when clicking on the site', OCS_DOMAIN ),
				'callback'    => 'checkbox_option',
				'type'        => 'checkbox',
				'label'       => __( 'Enable', OCS_DOMAIN ) . '.',
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
			),
			'link_close' => array(
				'name'        => 'link_close',
				'title'       => esc_attr__( 'Close sidebar when clicking on a link', OCS_DOMAIN ),
				'callback'    => 'checkbox_option',
				'type'        => 'checkbox',
				'label'       => __( 'Enable', OCS_DOMAIN ) . '.',
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'enabled', OCS_DOMAIN ) . '.',
			),
			'disable_over' => array(
				'name'        => 'disable_over',
				'title'       => esc_attr__( 'Disable over', OCS_DOMAIN ),
				'callback'    => 'number_option',
				'type'        => 'number',
				'label'       => __( 'Disable off-canvas sidebars over specified screen width.', OCS_DOMAIN ),
				'description' => __( 'Leave blank to disable.', OCS_DOMAIN ),
				'input_after' => '<code>px</code>',
			),
			'hide_control_classes' => array(
				'name'        => 'hide_control_classes',
				'title'       => esc_attr__( 'Auto-hide control classes', OCS_DOMAIN ),
				'callback'    => 'checkbox_option',
				'type'        => 'checkbox',
				'label'       => __( 'Hide off-canvas sidebar control classes over width specified in <strong>"Disable over"</strong>.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
			),
			'scroll_lock' => array(
				'name'        => 'scroll_lock',
				'title'       => esc_attr__( 'Scroll lock', OCS_DOMAIN ),
				'callback'    => 'checkbox_option',
				'type'        => 'checkbox',
				'label'       => __( 'Prevent site content scrolling whilst a off-canvas sidebar is open.', OCS_DOMAIN ),
				'description' => __( 'Default', OCS_DOMAIN ) . ': ' . __( 'disabled', OCS_DOMAIN ) . '.',
			),
			'sidebar_delete' => array(
				'name'     => 'delete',
				'title'    => esc_attr__( 'Delete sidebar', OCS_DOMAIN ),
				'callback' => 'checkbox_option',
				'type'     => 'checkbox',
				'value'    => 0,
			),
		);
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Tab_Sidebars
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
