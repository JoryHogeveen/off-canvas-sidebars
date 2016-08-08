<?php
/**
 * Off-Canvas Sidebars plugin settings
 *
 * Settings
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars_Settings {
	
	private $general_key = '';
	private $settings_tab = 'ocs-settings';
	private $sidebars_tab = 'ocs-sidebars';
	private $importexport_tab = 'ocs-importexport';
	private $plugin_key = '';
	private $plugin_tabs = array();
	private $general_settings = array();
	private $general_labels = array();

	function __construct() {
		$this->plugin_key = Off_Canvas_Sidebars()->get_plugin_key();
		add_action( 'admin_init', array( $this, 'load_plugin_data' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'register_importexport_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ), 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
	}

	/**
	 * Get plugin defaults
	 */
	function load_plugin_data() {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->general_settings = $off_canvas_sidebars->get_settings();
		$this->general_labels = $off_canvas_sidebars->get_general_labels();
		$this->general_key = $off_canvas_sidebars->get_general_key();
	}
	
	function enqueue_styles_scripts( $hook ) {
		if ( $hook != 'appearance_page_' . $this->plugin_key ) {
			return;
		}

		// Add the color picker css and script file
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'postbox' );

		// Add our own scripts
		wp_enqueue_style( 'off-canvas-sidebars-admin', OCS_PLUGIN_URL . '/css/off-canvas-sidebars-admin.css', array(), OCS_PLUGIN_VERSION );
		wp_enqueue_script( 'off-canvas-sidebars-settings', OCS_PLUGIN_URL . '/js/off-canvas-sidebars-settings.js', array( 'jquery' ), OCS_PLUGIN_VERSION, true );
		wp_localize_script( 'off-canvas-sidebars-settings', 'OCS_OFF_CANVAS_SIDEBARS_SETTINGS', array(
			'general_key' => $this->general_key,
			'plugin_key' => $this->plugin_key,
			'__required_fields_not_set' => __( 'Some required fields are not set!', 'off-canvas-sidebars' ),
		) );

	}

	function register_settings() {
		$this->plugin_tabs[ $this->settings_tab ] = esc_attr__( 'Off-Canvas Sidebars Settings', 'off-canvas-sidebars' );
		$this->plugin_tabs[ $this->sidebars_tab ] = esc_attr__( 'Sidebars', 'off-canvas-sidebars' );
		
		register_setting( $this->settings_tab, $this->general_key, array( $this, 'validate_input' ) );
		register_setting( $this->sidebars_tab, $this->general_key, array( $this, 'validate_input' ) );
		
		add_settings_section( 
			'section_general', 
			esc_attr__( 'Off-Canvas Sidebars Settings', 'off-canvas-sidebars' ), 
			array( $this, 'register_general_settings' ), 
			$this->settings_tab 
		);
		
		// Register sidebar settings
		foreach ($this->general_settings['sidebars'] as $sidebar => $sidebar_data) {
			add_settings_section( 
				'section_sidebar_'.$sidebar, 
				__( 'Off-Canvas Sidebar - <code class="js-dynamic-id">'.$this->general_settings['sidebars'][ $sidebar ]['label'] . '</code>', 'off-canvas-sidebars' ), 
				array( $this, 'register_general_settings' ), 
				$this->sidebars_tab 
			);
			$this->register_sidebar_settings( $sidebar );
		}
		
		do_action( 'off_canvas_sidebar_settings' );
	}

	function register_general_settings() {
		add_settings_field( 
			'enable_frontend', 
			esc_attr__( 'Enable front-end', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->settings_tab, 
			'section_general' ,
			array( 
				'name' => 'enable_frontend', 
				'label' => __( 'Let this plugin add the necessary elements on the front-end.', 'off-canvas-sidebars' ),
				'description' => sprintf( __( '<a href="%s" target="_blank">Read this to setup your theme for support!</a> (Themes based on the Genesis Framework are supported by default)', 'off-canvas-sidebars' ), 'https://wordpress.org/plugins/off-canvas-sidebars/installation/' )
			) 
		);
		/*add_settings_field( 
			'frontend_type', 
			esc_attr__( 'Front-end type', 'off-canvas-sidebars' ), 
			array( $this, 'frontend_type_option' ), 
			$this->settings_tab, 
			'section_general' 
		);*/
		add_settings_field( 
			'enabled_sidebars', 
			esc_attr__( 'Enable Sidebars', 'off-canvas-sidebars' ), 
			array( $this, 'enabled_sidebars_option' ), 
			$this->settings_tab, 
			'section_general' 
		);
		add_settings_field( 
			'site_close', 
			esc_attr__( 'Close sidebar when clicking on the site', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'site_close', 'label' => __( 'Enables closing of a off-canvas sidebar by clicking on the site. Default: true.', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'disable_over', 
			esc_attr__( 'Disable over', 'off-canvas-sidebars' ), 
			array( $this, 'number_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'disable_over', 'description' => __( 'Disable off-canvas sidebars over specified screen width. Leave blank to disable.', 'off-canvas-sidebars' ), 'input_after' => '<code>px</code>' ) 
		);
		add_settings_field( 
			'hide_control_classes', 
			esc_attr__( 'Auto-hide control classes', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'hide_control_classes', 'label' => __( 'Hide off-canvas sidebar control classes over width specified in <strong>"Disable over"</strong>. Default: false.', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'scroll_lock', 
			esc_attr__( 'Scroll lock', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'scroll_lock', 'label' => __( 'Prevent site content scrolling whilst a off-canvas sidebar is open. Default: false.', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'background_color', 
			esc_attr__( 'Background color', 'off-canvas-sidebars' ), 
			array( $this, 'color_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'background_color', 'description' => __( 'Choose a background color for the site container. Default: <code>#ffffff</code>.', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'website_before_hook', 
			esc_attr__( '"website_before" hook name', 'off-canvas-sidebars' ), 
			array( $this, 'text_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'website_before_hook', 'placeholder' => 'website_before' ) 
		);
		add_settings_field( 
			'website_after_hook', 
			esc_attr__( '"website_after" hook name', 'off-canvas-sidebars' ), 
			array( $this, 'text_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'website_after_hook', 'placeholder' => 'website_after' ) 
		);
		add_settings_field( 
			'compatibility_position_fixed', 
			esc_attr__( 'Compatibility for fixed elements', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->settings_tab, 
			'section_general', 
			array( 'name' => 'compatibility_position_fixed', 'label' => '('.__( 'Experimental', 'off-canvas-sidebars' ).')' ) 
		);
	}
	
	function register_sidebar_settings( $sidebar_id ) {

		add_settings_field( 
			'sidebar_enable', 
			esc_attr__( 'Enable', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'name' => 'enable' ) 
		);
		add_settings_field( 
			'sidebar_id', 
			esc_attr__( 'ID', 'off-canvas-sidebars' ) . ' <span class="required">*</span>', 
			array( $this, 'text_option' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'name' => 'id', 'value' => $sidebar_id, 'required' => true, 'description' => __( 'IMPORTANT: Must be unique!', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'sidebar_label', 
			esc_attr__( 'Name', 'off-canvas-sidebars' ), 
			array( $this, 'text_option' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'name' => 'label' ) 
		);
		add_settings_field( 
			'sidebar_location', 
			esc_attr__( 'Location', 'off-canvas-sidebars' ) . ' <span class="required">*</span>', 
			array( $this, 'sidebar_location' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'required' => true ) 
		);
		add_settings_field( 
			'sidebar_width', 
			esc_attr__( 'Width', 'off-canvas-sidebars' ), 
			array( $this, 'sidebar_width' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'description' => __( 'You can overwrite this with CSS', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'sidebar_style', 
			esc_attr__( 'Style', 'off-canvas-sidebars' ) . ' <span class="required">*</span>', 
			array( $this, 'sidebar_style' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'required' => true ) 
		);
		add_settings_field( 
			'background_color', 
			esc_attr__( 'Background color', 'off-canvas-sidebars' ), 
			array( $this, 'color_option' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'name' => 'background_color', 'description' => __( 'Choose a background color for this sidebar. Default: <code>#222222</code>.', 'off-canvas-sidebars' ) . '<br>' . __( 'You can overwrite this with CSS', 'off-canvas-sidebars' ) ) 
		);
		add_settings_field( 
			'sidebar_delete', 
			esc_attr__( 'Delete sidebar', 'off-canvas-sidebars' ), 
			array( $this, 'checkbox_option' ), 
			$this->sidebars_tab, 
			'section_sidebar_' . $sidebar_id, 
			array( 'sidebar' => $sidebar_id, 'name' => 'delete', 'value' => 0 ) 
		);
	}
	
	/* 
	 * Specific fields
	 */
	function frontend_type_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefixName.'[frontend_type]'; ?>" id="<?php echo $prefixId.'_style_action'; ?>" value="action" <?php checked( $prefixValue['frontend_type'], 'action' ); ?> /> <?php _e( 'Actions', 'off-canvas-sidebars' ); echo ' (' . __( 'default', 'off-canvas-sidebars' ) . ')'; ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[frontend_type]'; ?>" id="<?php echo $prefixId.'_style_jquery'; ?>" value="jquery" <?php checked( $prefixValue['frontend_type'], 'jquery' ); ?> /> <?php _e( 'jQuery', 'off-canvas-sidebars' ); echo ' (' . __( 'experimental', 'off-canvas-sidebars' ) . ')' ?></label>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
	}
	
	function enabled_sidebars_option() {
		$prefixName = esc_attr( $this->general_key ).'[sidebars]';
		$prefixValue = $this->general_settings['sidebars'];
		$prefixId = $this->general_key.'_sidebars';
		$prefixClasses = array( $prefixId );
		foreach ($prefixValue as $sidebar => $sidebar_data) {
			$classes = $this->get_option_classes( $prefixClasses, 'enable' );
		?><fieldset>
		<label><input type="checkbox" name="<?php echo $prefixName.'['.$sidebar.'][enable]'; ?>" id="<?php echo $prefixId.'_enable_'.$sidebar; ?>" value="1" <?php checked( $prefixValue[$sidebar]['enable'], 1 ); ?> /> <?php echo $this->general_settings['sidebars'][ $sidebar ]['label']; ?></label><br />
	<?php }
		?></fieldset><?php
	}
	
	function sidebar_location( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefixClasses, 'location' );
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefixName.'[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_location_left'; ?>" value="left" <?php checked( $prefixValue['location'], 'left' ); ?> /> <?php _e( 'Left', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_location_right'; ?>" value="right" <?php checked( $prefixValue['location'], 'right' ); ?> /> <?php _e( 'Right', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_location_top'; ?>" value="top" <?php checked( $prefixValue['location'], 'top' ); ?> /> <?php _e( 'Top', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[location]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_location_bottom'; ?>" value="bottom" <?php checked( $prefixValue['location'], 'bottom' ); ?> /> <?php _e( 'Bottom', 'off-canvas-sidebars' ); ?></label>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}
	
	function sidebar_width( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefixClasses, 'width' );
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefixName.'[width]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_width_default'; ?>" value="default" <?php checked( $prefixValue['width'], 'default' ); ?> /> <?php _e( 'Default', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[width]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_width_thin'; ?>" value="thin" <?php checked( $prefixValue['width'], 'thin' ); ?> /> <?php _e( 'Thin', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[width]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_width_wide'; ?>" value="wide" <?php checked( $prefixValue['width'], 'wide' ); ?> /> <?php _e( 'Wide', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[width]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_width_custom'; ?>" value="custom" <?php checked( $prefixValue['width'], 'custom' ); ?> /> <?php _e( 'Custom', 'off-canvas-sidebars' ); ?></label>: 
			<div style="display: inline-block; vertical-align:top">
				<input type="number" name="<?php echo $prefixName.'[width_input]'; ?>" class="<?php echo $this->get_option_classes( $prefixClasses, 'width_input' ); ?>" min="1" max="" step="1" value="<?php echo $prefixValue['width_input'] ?>" /> 
				<select name="<?php echo $prefixName.'[width_input_type]'; ?>" class="<?php echo $this->get_option_classes( $prefixClasses, 'width_input_type' ); ?>">
					<option value="%" <?php selected( $prefixValue['width_input_type'], '%' ); ?>>%</option>
					<option value="px" <?php selected( $prefixValue['width_input_type'], 'px' ); ?>>px</option>
				</select>
			</div>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}
	
	function sidebar_style( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['sidebar'] ) ) {
			$classes = $this->get_option_classes( $prefixClasses, 'style' );
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefixName.'[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_style_push'; ?>" value="push" <?php checked( $prefixValue['style'], 'push' ); ?> /> <?php _e( 'Sidebar pushes the site across when opened.', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_style_overlay'; ?>" value="overlay" <?php checked( $prefixValue['style'], 'overlay' ); ?> /> <?php _e( 'Sidebar overlays the site when opened.', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_style_reveal'; ?>" value="reveal" <?php checked( $prefixValue['style'], 'reveal' ); ?> /> <?php _e( 'Sidebar reveals when opened.', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'[style]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_style_shift'; ?>" value="shift" <?php checked( $prefixValue['style'], 'shift' ); ?> /> <?php _e( 'Sidebar shifts when opened.', 'off-canvas-sidebars' ); ?></label>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}

	/* 
	 * General fields
	 */
	function text_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		$placeholder = '';
		if ( isset( $args['placeholder'] ) ) {
			$placeholder = ' placeholder="'.$args['placeholder'].'"';
		}
		if ( isset( $args['name'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefixValue[ $args['name'] ] = $args['value'];
			}
			$classes = $this->get_option_classes( $prefixClasses, $args['name'] );
		?><fieldset>
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<input type="text" name="<?php echo $prefixName.'['.$args['name'].']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_'.$args['name']; ?>" value="<?php echo $prefixValue[ $args['name'] ]; ?>"<?php echo $placeholder ?>/> 
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><?php } ?>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}

	function checkbox_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			if ( isset( $args['value'] ) ) {
				$prefixValue[ $args['name'] ] = $args['value'];
			}
			$classes = $this->get_option_classes( $prefixClasses, $args['name'] );
		?><fieldset>
			<?php if ( isset( $args['label'] ) ) { ?><label><?php } ?>
			<input type="checkbox" name="<?php echo $prefixName.'['.$args['name'].']'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_'.$args['name']; ?>" value="1" <?php checked( $prefixValue[$args['name']], 1 ); ?> /> 
			<?php if ( isset( $args['label'] ) ) { echo $args['label'] ?></label><?php } ?>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}

	function number_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			$classes = $this->get_option_classes( $prefixClasses, $args['name'] );
		?><fieldset>
			<input type="number" id="<?php echo $prefixId.'_'.$args['name']; ?>" class="<?php echo $classes; ?>" name="<?php echo $prefixName.'['.$args['name'].']'; ?>" value="<?php echo $prefixValue[$args['name']] ?>" min="1" max="" step="1" /> <?php echo ( ! empty( $args['input_after'] ) ) ? $args['input_after'] : ''; ?>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}

	function color_option( $args ) {
		$prefixes = $this->get_option_prefixes( $args );
		$prefixName = $prefixes['prefixName'];
		$prefixValue = $prefixes['prefixValue'];
		$prefixId = $prefixes['prefixId'];
		$prefixClasses = $prefixes['prefixClasses'];
		if ( isset( $args['name'] ) ) {
			$classes = $this->get_option_classes( $prefixClasses, $args['name'] . '_type' );
		?><fieldset>
			<label><input type="radio" name="<?php echo $prefixName.'['.$args['name'].'_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_background_color_type_theme'; ?>" value="" <?php checked( $prefixValue[$args['name'].'_type'], '' ); ?> /> <?php _e( 'Default', 'off-canvas-sidebars' ); ?></label> <span class="description">(<?php _e( 'Overwritable with CSS', 'off-canvas-sidebars' ); ?>)</span><br />
			<label><input type="radio" name="<?php echo $prefixName.'['.$args['name'].'_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_background_color_type_transparent'; ?>" value="transparent" <?php checked( $prefixValue[$args['name'].'_type'], 'transparent' ); ?> /> <?php _e( 'Transparent', 'off-canvas-sidebars' ); ?></label><br />
			<label><input type="radio" name="<?php echo $prefixName.'['.$args['name'].'_type]'; ?>" class="<?php echo $classes; ?>" id="<?php echo $prefixId.'_background_color_type_color'; ?>" value="color" <?php checked( $prefixValue[$args['name'].'_type'], 'color' ); ?> /> <?php _e( 'Color', 'off-canvas-sidebars' ); ?></label><br />
			<div class="<?php echo $prefixId.'_'.$args['name'].'_wrapper'; ?>">
			<input type="text" class="color-picker <?php echo $this->get_option_classes( $prefixClasses, $args['name'] ) ?>" id="<?php echo $prefixId.'_'.$args['name']; ?>" name="<?php echo $prefixName.'['.$args['name'].']'; ?>" value="<?php echo $prefixValue[$args['name']] ?>" />
			</div>
			<?php if ( isset( $args['description'] ) ) { ?>
			<p class="description"><?php echo $args['description'] ?></p>
			<?php } ?>
		</fieldset><?php
		}
	}
	
	/**
	 * Returns attribute prefixes for general settings and sidebar settings
	 *
	 * @since 0.1
	 *
	 * @param array $args Arguments from the settings field
	 * @return array $prefixes Prefixes for name, value and id attributes
	 */
	function get_option_prefixes( $args ) {
		if ( isset( $args['sidebar'] ) ) {
			$prefixName = esc_attr( $this->general_key ).'[sidebars]['.$args['sidebar'].']';
			$prefixValue = $this->general_settings['sidebars'][$args['sidebar']];
			$prefixId = $this->general_key.'_sidebars_'.$args['sidebar'];
			$prefixClasses = array(
				$this->general_key.'_sidebars_'.$args['sidebar'],
				$this->general_key.'_sidebars'
			);
		} else {
			$prefixName = esc_attr( $this->general_key );
			$prefixValue = $this->general_settings;
			$prefixId = $this->general_key;
			$prefixClasses = array(
				$this->general_key
			);
		}
		if ( ! empty( $args['required'] ) ) {
			$prefixClasses[] = 'required';
		}
		return array( 'prefixName' => $prefixName, 'prefixValue' => $prefixValue, 'prefixId' => $prefixId, 'prefixClasses' => $prefixClasses );
	}

	function get_option_classes( $classes, $append ) {
		if ( $append ) {
			foreach ( $classes as $key => $class ) {
				if ( ! in_array( $class, array( 'required' ) ) )
				$classes[ $key ] = $class . '_' . $append;
			}
		}
		return implode( ' ', $classes );
	}
	
	/**
	 * Validates post values
	 *
	 * @since 0.1
	 *
	 * @param array $input
	 * @return array $output
	 */
	function validate_input( $input ) {
		$output = array();
		// First set current values
		$output = $this->general_settings;

		if ( $_POST['ocs_tab'] == $this->settings_tab ) {
			// Make sure unchecked checkboxes are 0 on save
			$input['enable_frontend']              = ( isset( $input['enable_frontend'] ) ) ? $this->validate_checkbox( $input['enable_frontend'] ) : 0;
			$input['site_close']                   = ( isset( $input['site_close'] ) ) ? $this->validate_checkbox( $input['site_close'] ) : 0;
			$input['hide_control_classes']         = ( isset( $input['hide_control_classes'] ) ) ? $this->validate_checkbox( $input['hide_control_classes'] ) : 0;
			$input['scroll_lock']                  = ( isset( $input['scroll_lock'] ) ) ? $this->validate_checkbox( $input['scroll_lock'] ) : 0;
			$input['compatibility_position_fixed'] = ( isset( $input['compatibility_position_fixed'] ) ) ? $this->validate_checkbox( $input['compatibility_position_fixed'] ) : 0;
		}

		// Add new sidebar
		if ( ! empty( $input['sidebars']['ocs_add_new'] ) ) {
			$new_sidebar_id = $this->validate_id( $input['sidebars']['ocs_add_new'] );
			if ( empty( $input['sidebars'][ $new_sidebar_id ] ) && empty( $output['sidebars'][ $new_sidebar_id ] ) ) {
				$input['sidebars'][ $this->validate_id( $input['sidebars']['ocs_add_new'] ) ] = array(
					'enable' => 1,
					'label' => strip_tags( stripslashes( $input['sidebars']['ocs_add_new'] ) ),
				);
			} else {
				add_settings_error( $new_sidebar_id . '_duplicate_id', esc_attr( 'ocs_duplicate_id' ), sprintf( __( 'The ID %s already exists! Sidebar not added.', 'off-canvas-sidebars' ), '<code>' . $new_sidebar_id . '</code>' ) );
			}
		}
		unset( $input['sidebars']['ocs_add_new'] );

		// Handle existing sidebars
		if ( isset( $input['sidebars'] ) ) {
			foreach ( $output['sidebars'] as $sidebar_id => $sidebar_data ) {

				if ( ! isset( $input['sidebars'][ $sidebar_id ] ) ) {
					$input['sidebars'][ $sidebar_id ] = $output['sidebars'][ $sidebar_id ];
				}

				// Global settings page
				if ( count( $input['sidebars'][ $sidebar_id ] ) < 2 ) {
					$output['sidebars'][ $sidebar_id ]['enable'] = $input['sidebars'][ $sidebar_id ]['enable'];
					$input['sidebars'][ $sidebar_id ] = $output['sidebars'][ $sidebar_id ];
				}

				// Default label is sidebar ID
				if ( empty( $input['sidebars'][ $sidebar_id ]['label'] ) ) {
					$input['sidebars'][ $sidebar_id ]['label'] = $sidebar_id;
				}

				// Change sidebar ID
				if ( ! empty( $input['sidebars'][ $sidebar_id ]['id'] ) && $sidebar_id != $input['sidebars'][ $sidebar_id ]['id'] ) {

					$new_sidebar_id = $this->validate_id( $input['sidebars'][ $sidebar_id ]['id'] );

					if ( $sidebar_id != $new_sidebar_id ) {
						if ( empty( $input['sidebars'][ $new_sidebar_id ] ) ) {
							$input['sidebars'][ $new_sidebar_id ] = $input['sidebars'][ $sidebar_id ];
							$input['sidebars'][ $new_sidebar_id ]['id'] = $new_sidebar_id;
							unset( $input['sidebars'][ $sidebar_id ] );
						} else {
							add_settings_error( $sidebar_id . '_duplicate_id', esc_attr( 'ocs_duplicate_id' ), sprintf( __( 'The ID %s already exists! The ID is not changed.', 'off-canvas-sidebars' ), '<code>' . $new_sidebar_id . '</code>' ) );
						}
					}
				}
			}
		}

		// Overwrite non existing values with current values
		foreach ( $output as $key => $value ) {
			if ( ! isset( $input[ $key ] ) ) {
				$input[ $key ] = $value;
			}
		}

		// Overwrite the old settings
		$output = $input;

		foreach ( $output['sidebars'] as $sidebar_id => $sidebar_data ) {

			// Delete sidebar
			if ( isset( $input['sidebars'][ $sidebar_id ]['delete'] ) && $input['sidebars'][ $sidebar_id ]['delete'] == '1' ) {
				unset( $input['sidebars'][ $sidebar_id ] );
				unset( $output['sidebars'][ $sidebar_id ] );
			} else {

				// Make sure unchecked checkboxes are 0 on save
				$output['sidebars'][ $sidebar_id ]['enable'] = ( ! empty( $output['sidebars'][ $sidebar_id ]['enable'] ) ) ? strip_tags( $output['sidebars'][ $sidebar_id ]['enable'] ) : 0;

				$new_sidebar_id = $this->validate_id( $sidebar_id );
				if ( $sidebar_id != $new_sidebar_id ) {
					$output['sidebars'][ $new_sidebar_id ] = $output['sidebars'][ $sidebar_id ];
					$output['sidebars'][ $new_sidebar_id ]['id'] = $new_sidebar_id;

					unset( $output['sidebars'][ $sidebar_id ] );
				}
			}
		}

		// Validate global settings with defaults
		$output = Off_Canvas_Sidebars()->validate_settings( $output, Off_Canvas_Sidebars()->get_default_settings() );
		// Validate sidebar settings with defaults
		foreach ( $output['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$output['sidebars'][ $sidebar_id ] = Off_Canvas_Sidebars()->validate_settings( $sidebar_settings, Off_Canvas_Sidebars()->get_default_sidebar_settings() );
		}

		if ( isset( $output['ocs_tab'] ) ) {
			unset( $output['ocs_tab'] );
		}

		return $output;
	}
	
	/**
	 * Validates checkbox values, used by validate_input
	 *
	 * @since 0.1.2
	 *
	 * @param string $value
	 * @return string $value
	 */
	function validate_checkbox($value) {
		return ( ! empty( $value ) && $value == 1 ) ? (int) strip_tags( $value ) : 0;
	}

	/**
	 * Validates id values, used by validate_input
	 *
	 * @since 0.2
	 *
	 * @param string $value
	 * @return string $value
	 */
	function validate_id( $value ) {
		return preg_replace('/[^a-z0-9_-]+/i', '', $value);
	}

	/**
	 * Create admin menu page
	 * @since 0.1
	 */
	function add_admin_menus() {
		add_submenu_page( 'themes.php', esc_attr__( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ), esc_attr__( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ), 'edit_theme_options', $this->plugin_key, array( $this, 'plugin_options_page' ) );
	}

	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the plugin_options_tabs method
	 * to render the tabs.
	 *
	 * @since 0.1
	 */
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_tab;
		?>
	<div class="wrap">
		<?php $this->plugin_options_tabs(); ?>
		<div class="<?php echo $this->plugin_key ?> container">

			<form id="<?php echo $this->general_key ?>" method="post" action="options.php" enctype="multipart/form-data">

				<?php settings_errors(); ?>
				<?php if ( $tab != $this->importexport_tab ) { ?>
				<p class="alignright"><?php submit_button( null, 'primary', 'submit', false ); ?></p>
				<?php } ?>
				<input id="ocs_tab" type="hidden" name="ocs_tab" value="<?php echo $tab ?>" />

				<?php if ( $tab == $this->settings_tab ) { ?>
				<p><?php echo sprintf( __('You can add the control buttons with a widget, menu item or with custom code, <a href="%s" target="_blank">click here for documentation.</a>', 'off-canvas-sidebars' ), 'https://www.adchsm.com/slidebars/help/usage/' ); ?></p>
				<p><?php echo $this->general_labels['compatibility_notice_theme']; ?></p>
				<?php } elseif ( $tab == $this->sidebars_tab ) { ?>
				<p>
					Add a new sidebar <input name="<?php echo esc_attr( $this->general_key ).'[sidebars][ocs_add_new]'; ?>" value="" type="text" placeholder="<?php _e( 'Name', 'off-canvas-sidebars' ) ?>" /> 
					<?php submit_button( __( 'Add sidebar', 'off-canvas-sidebars'), 'primary', 'submit', false ); ?>
				</p>
				<?php } ?>

				<div class="metabox-holder">
				<div class="postbox-container">
				<div id="main-sortables" class="meta-box-sortables ui-sortable">
				<?php settings_fields( $tab ); ?>
				<?php $this->do_settings_sections( $tab ); ?>
				</div>
				</div>
				</div>

				<?php if ( $tab == $this->importexport_tab ) $this->importexport_fields(); ?>
				<?php if ( $tab != $this->importexport_tab ) submit_button(); ?>

			</form>
			
			<div class="ocs-sidebar">
				<div class="ocs-credits">
					<h3 class="hndle"><?php echo __( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ) . ' ' . OCS_PLUGIN_VERSION ?></h3>
					<div class="inside">
						<h4 class="inner"><?php _e( 'Need support?', 'off-canvas-sidebars' ) ?></h4>
						<p class="inner">
							<?php echo sprintf( __( 'If you are having problems with this plugin, checkout plugin <a href="%s" target="_blank">Documentation</a> or talk about them in the <a href="%s" target="_blank">Support forum</a>', 'off-canvas-sidebars' ), 'https://wordpress.org/plugins/off-canvas-sidebars/installation/', 'https://wordpress.org/support/plugin/off-canvas-sidebars' ) ?>
						</p>
						<hr />
						<h4 class="inner"><?php _e( 'Do you like this plugin?', 'off-canvas-sidebars' ) ?></h4>
						<a class="inner" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YGPLMLU7XQ9E8&lc=NL&item_name=Off%2dCanvas%20Sidebars&item_number=JWPP%2dOCS&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">
							<img alt="PayPal - The safer, easier way to pay online!" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif">
						</a>
						<p class="inner">
						<a href="http://wordpress.org/support/view/plugin-reviews/off-canvas-sidebars" target="_blank"><?php _e( 'Rate it 5 on WordPress.org', 'off-canvas-sidebars' ) ?></a><br />
						<a href="https://wordpress.org/plugins/off-canvas-sidebars/" target="_blank"> <?php _e( 'Blog about it & link to the plugin page', 'off-canvas-sidebars' ) ?></a><br />
						<a href="https://profiles.wordpress.org/keraweb/#content-plugins" target="_blank"> <?php _e( 'Check out my other WordPress plugins', 'off-canvas-sidebars' ) ?></a>
						</p>
						<hr />
						<p class="ocs-link inner"><?php _e( 'Created by', 'off-canvas-sidebars' ) ?> <a href="https://profiles.wordpress.org/keraweb/" target="_blank" title="Keraweb - Jory Hogeveen"><!--<img src="' . plugins_url( '../images/logo-keraweb.png', __FILE__ ) . '" title="Keraweb - Jory Hogeveen" alt="Keraweb - Jory Hogeveen" />-->Keraweb (Jory Hogeveen)</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
		//add_action( 'in_admin_footer', array( 'OCS_Lib', 'admin_footer' ) );
	}
	
	/**
	 * This function is similar to the function in the Settings API, only the output HTML is changed.
	 * Print out the settings fields for a particular settings section
	 *
	 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
	 *
	 * @since 0.1
	 *
	 * @param string $page Slug title of the admin page who's settings fields you want to show.
	 * @param string $section Slug title of the settings section who's fields you want to show.
	 */
	function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
	 
		if ( ! isset( $wp_settings_sections[$page] ) )
			return;
	 
		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			$box_classes = 'stuffbox postbox '.$section['id'].'';
			if ( $page == $this->sidebars_tab ) {
				$box_classes .= ' if-js-closed';
			}
			echo '<div id="'.$section['id'].'" class="'.$box_classes.'">';
			echo '<button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">' . __('Toggle panel', 'off-canvas-sidebars') . '</span><span class="toggle-indicator" aria-hidden="true"></span></button>';
			if ( $section['title'] )
				echo "<h3 class=\"hndle\"><span>{$section['title']}</span></h3>\n";
	 
			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );
	 
			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
				continue;
			echo '<div class="inside"><table class="form-table">';

			if ( $page == $this->sidebars_tab ) {
				echo '<tr class="sidebar_classes" style="display: none;"><th>'.__('ID & Classes', 'off-canvas-sidebars').'</th><td>'.__('Sidebar ID', 'off-canvas-sidebars').': <code>#sb-<span class="js-dynamic-id"></span></code> &nbsp; '.__('Sidebar Toggle Class', 'off-canvas-sidebars').': <code>.sb-toggle-<span class="js-dynamic-id"></span></code></td></tr>';
			}
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
			if ( $page == $this->sidebars_tab ) {
				submit_button( null, 'primary', 'submit', false );
			}
			echo '</div>';
			echo '</div>';
		}
	}

	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * plugin_options_page method.
	 *
	 * @since 0.1
	 */
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->settings_tab;

		echo '<h1 class="nav-tab-wrapper">';
		foreach ( $this->plugin_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab '.esc_attr( $active ).'" href="?page='.esc_attr( $this->plugin_key ).'&amp;tab='.esc_attr( $tab_key ).'">'.esc_html( $tab_caption ).'</a>';
		}
		echo '</h1>';
	}
	
	function importexport_fields() {
		?>
	<h3><?php _e( 'Import/Export Settings', 'off-canvas-sidebars' ); ?></h3>

	<p><a class="submit button" href="?<?php echo $this->plugin_key ?>-export"><?php esc_attr_e( 'Export Settings', 'off-canvas-sidebars' ); ?></a></p>

	<p>
		<input type="hidden" name="<?php echo $this->plugin_key ?>-import" id="<?php echo $this->plugin_key ?>-import" value="true" />
		<?php submit_button( esc_attr__( 'Import Settings', 'off-canvas-sidebars' ), 'button', $this->plugin_key . '-submit', false ); ?>
		<input type="file" name="<?php echo $this->plugin_key ?>-import-file" id="<?php echo $this->plugin_key ?>-import-file" />
	</p>

	<?php
	}

	function register_importexport_settings() {
		$this->plugin_tabs[ $this->importexport_tab ] = esc_attr__( 'Import/Export', 'off-canvas-sidebars' );

		if ( isset( $_GET['gocs_message'] ) ) {
			switch ( $_GET['gocs_message'] ) {
				case 1:
					$gocs_message_class = 'updated';
					$gocs_message = esc_attr__( 'Settings Imported', 'off-canvas-sidebars' );
					break;
				case 2:
					$gocs_message_class = 'error';
					$gocs_message = esc_attr__( 'Invalid Settings File', 'off-canvas-sidebars' );
					break;
				case 3:
					$gocs_message_class = 'error';
					$gocs_message = esc_attr__( 'No Settings File Selected', 'off-canvas-sidebars' );
					break;
				default:
					$gocs_message_class = '';
					$gocs_message = '';
					break;
			}
		}

		if ( isset( $gocs_message ) && $gocs_message != '' ) {
			echo '<div class="' . $gocs_message_class . '"><p>'.esc_html( $gocs_message ).'</p></div>';
		}

		// export settings
		if ( isset( $_GET[ $this->plugin_key . '-export'] ) ) {
			header( "Content-Disposition: attachment; filename=" . $this->plugin_key . ".txt" );
			header( 'Content-Type: text/plain; charset=utf-8' );
			$general = $this->general_settings;

			echo "[START=OCS SETTINGS]\n";
			foreach ( $general as $id => $text )
				echo "$id\t".json_encode( $text )."\n";
			echo "[STOP=OCS SETTINGS]";
			exit;
		}

		// import settings
		if ( isset( $_POST[ $this->plugin_key . '-import'] ) ) {
			$gocs_message = '';
			if ( $_FILES[ $this->plugin_key . '-import-file']['tmp_name'] ) {
				$import = explode( "\n", file_get_contents( $_FILES[ $this->plugin_key . '-import-file']['tmp_name'] ) );
				if ( array_shift( $import ) == "[START=OCS SETTINGS]" && array_pop( $import ) == "[STOP=OCS SETTINGS]" ) {
					$settings = array();
					foreach ( $import as $import_option ) {
						list( $key, $value ) = explode( "\t", $import_option );
						$settings[$key] = json_decode( sanitize_text_field( $value ), true );
					}
					// Validate global settings
					$settings = Off_Canvas_Sidebars()->validate_settings( $settings, Off_Canvas_Sidebars()->get_default_settings() );
					// Validate sidebar settings
					if ( ! empty( $settings['sidebars'] ) ) {
						foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_settings ) {
							$settings['sidebars'][ $sidebar_id ] = Off_Canvas_Sidebars()->validate_settings( $sidebar_settings, Off_Canvas_Sidebars()->get_default_sidebar_settings() );
						}					
					}
					update_option( $this->general_key, $settings );
					$gocs_message = 1;
				} else {
					$gocs_message = 2;
				}
			} else {
				$gocs_message = 3;
			}

			wp_redirect( admin_url( '/themes.php?page=' . $this->plugin_key . '&tab=' . $this->importexport_tab . '&gocs_message='.esc_attr( $gocs_message ) ) );
			exit;
		}
	}

} // end class