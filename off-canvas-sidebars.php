<?php
/**
 * Plugin Name: Off-Canvas Sidebars
 * Description: Add off-canvas sidebars using the Slidebars jQuery plugin
 * Plugin URI:  https://wordpress.org/plugins/off-canvas-sidebars/
 * Version:     0.2
 * Author:      Jory Hogeveen
 * Author URI:  http://www.keraweb.nl
 * Text Domain: off-canvas-sidebars
 * Domain Path: /languages/
 * License: 	GPLv2
 */
 
! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars {

	/**
	 * The single instance of the class.
	 *
	 * @var Off-Canvas Sidebars
	 * @since 0.1.2
	 */
	protected static $_instance = null;
	
	/**
	 * Plugin version
	 *
	 * @var    String
	 * @since  0.1
	 */
	protected $version = '0.2';

	/**
	 * User ignore nag key
	 *
	 * @var    String
	 * @since  0.1
	 */
	protected $noticeKey = 'ocs_ignore_theme_compatibility_notice';
	
	/**
	 * Current user object
	 *
	 * @var    Object
	 * @since  0.1
	 */	
	protected $curUser = false;

	/**
	 * Enable functionalities?
	 *
	 * @var    Boolean
	 * @since  0.1
	 */
	protected $enable = false;	

	/**
	 * Plugin key
	 *
	 * @var    Boolean
	 * @since  0.1
	 */
	protected $plugin_key = 'off-canvas-sidebars-settings';

	/**
	 * Plugin general settings key
	 *
	 * @var    Boolean
	 * @since  0.1
	 */
	protected $general_key = 'off_canvas_sidebars_options';

	/**
	 * Plugin settings
	 *
	 * @var    Boolean
	 * @since  0.1
	 */
	protected $general_settings = array();	

	/**
	 * Plugin settings
	 *
	 * @var    Boolean
	 * @since  0.1
	 */
	protected $general_labels = array();

	/**
	 * Default settings
	 *
	 * @var    Boolean
	 * @since  0.2
	 */
	protected $default_settings = array(
		'enable_frontend' => '1',
		'frontend_type' => 'action',
		'site_close' => 1,
		'disable_over' => '',
		'hide_control_classes' => 0,
		'scroll_lock' => 0,
		'background_color_type' => '',
		'background_color' => '',
		'website_before_hook' => 'website_before',
		'website_after_hook' => 'website_after',
		'compatibility_position_fixed' => 0,
		'sidebars' => array(),
	);

	/**
	 * Default sidebar settings
	 *
	 * @var    Boolean
	 * @since  0.2
	 */
	protected $default_sidebar_settings = array(
		'enable' => 0,
		'label' => '',
		'location' => '',
		'style' => 'push',
		'width' => 'default',
		'width_input' => '',
		'width_input_type' => '%',
		'background_color' => '',
		'background_color_type' => '',
	);
	
	/**
	 * Init function to register plugin hook
	 *
	 * @return	void
	 * @since   0.1
	 */
	function __construct() {
		self::$_instance = $this;
		
		if ( !defined( 'OCS_PLUGIN_VERSION' ) ) define( 'OCS_PLUGIN_VERSION', $this->version );
		if ( !defined( 'OCS_FILE' ) ) define( 'OCS_FILE', __FILE__ );
		if ( !defined( 'OCS_BASENAME' ) ) define( 'OCS_BASENAME', plugin_basename( __FILE__ ) );
		if ( !defined( 'OCS_PLUGIN_DIR' ) ) define( 'OCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		if ( !defined( 'OCS_PLUGIN_URL' ) ) define( 'OCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		
		$this->enable = true; // Added for possible use in future
		if ($this->enable == true) {
			// Lets start!
			add_action( 'init', array( $this, 'init' ) );
			
			// Load translations
			$this->load_textdomain();
			
			// Register the widget
			include_once 'widgets/off-canvas-sidebars-widget.php';
			add_action( 'widgets_init', function() {
				register_widget( 'OCS_Off_Canvas_Sidebars_Control_Widget' );
			} );
			// Load menu-meta-box option
			include_once 'includes/off-canvas-sidebars-menu-meta-box.class.php';
			new OCS_Off_Canvas_Sidebars_Menu_Meta_box();
		} else {
			// Added for possible use in future
			add_action( 'admin_notices', array( $this, 'compatibility_notice' ) ); 
			add_action( 'wp_ajax_'.$this->noticeKey, array( $this, 'ignore_compatibility_notice' ) );
		}
	}

	/**
	 * Main Off-Canvas Sidebars Instance.
	 *
	 * Ensures only one instance of Off-Canvas Sidebars is loaded or can be loaded.
	 *
	 * @since 0.1.2
	 * @static
	 * @see Off_Canvas_Sidebars()
	 * @return Off-Canvas Sidebars - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Init function/action to check current user, load nessesary data and classes, register hooks
	 *
	 * @return	void
	 * @since   0.1
	 */
	function init() {		
		// Get the current user
		//$this->curUser = wp_get_current_user();
		
		$this->general_settings = ( get_option( $this->general_key ) ) ? get_option( $this->general_key ) : array();
		$this->general_settings = $this->get_settings(); // Merge DB settings with default settings
		$this->general_labels = $this->get_general_labels();
		
		// Register the enabled sidebars
		$this->register_sidebars();
		
		if ( is_admin() ) {
			// Load the admin
			include_once 'includes/off-canvas-sidebars-settings.class.php';
			new OCS_Off_Canvas_Sidebars_Settings();
			
			// Add settings link to plugins page
			add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
		} else {
			// If a sidebar is enabled, load the front-end
			if ( $this->is_sidebar_enabled() ) {
				include_once 'includes/off-canvas-sidebars-frontend.class.php';
				new OCS_Off_Canvas_Sidebars_Frontend();
			}
		}
	}
	
	/**
	 * Add notice when theme is not compatible
	 * Checks for version in the notice ignore meta value. If the version is the same (user has clicked ignore), then hide it
	 *
	 * @return	void
	 * @since   0.1
	 */
	function compatibility_notice() {
		if ( get_user_meta( $this->curUser->ID, $this->noticeKey, true ) != $this->version ) {
			$class = 'error notice is-dismissible';
			$message = '<strong>Off-Canvas Sidebars:</strong> ' . $this->general_labels['compatibility_notice_theme'];
			$ignore = '<a id="' . $this->noticeKey . '" href="?' . $this->noticeKey . '=1" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'off-canvas-sidebars' ) . '</span></a>';
			$script = '<script>(function($) { $(document).on("click", "#' . $this->noticeKey . '", function(e){e.preventDefault();$.post(ajaxurl, {\'action\': \'' . $this->noticeKey . '\'});}) })( jQuery );</script>';
			echo '<div id="' . $this->noticeKey . '" class="' . $class . '"> <p>' . $message . '</p> ' . $ignore . $script . '</div>';
		}
	}
	
	/**
	 * AJAX handler
	 * Stores plugin version
	 *
	 * Store format: Boolean
	 *
	 * @return	String
	 * @since   0.1
	 */
	function ignore_compatibility_notice() {
		update_user_meta( $this->curUser->ID, $this->noticeKey, $this->version );
		wp_die();
	}
	
	/**
	 * Merge database plugin settings with default settings
	 *
	 * TODO: Make adding more sidebars dynamic (Slidebars will need to support more sidebars aswell)
	 *
	 * @return	void
	 * @since   0.1
	 */
	function get_settings() {
		$settings = $this->general_settings;

		// Validate global settings
		$settings = $this->validate_settings( $settings, $this->get_default_settings() );
		// Validate sidebar settings
		foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$settings['sidebars'][ $sidebar_id ] = $this->validate_settings( $sidebar_settings, $this->get_default_sidebar_settings() );
		}
		
		return $settings;
	}

	/**
	 * Validate setting keys
	 *
	 * @param   array
	 * @return	array
	 * @since   0.2
	 */
	function validate_settings( $settings, $defaults ) {
		// supports one level array
		$settings = array_merge( $defaults, $settings );
		// Remove unknown keys
		foreach ( $settings as $key => $value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $settings[ $key ] );
			}
		}
		return $settings;
	}
	
	/**
	 * Returns the default settings
	 *
	 * @return	String
	 * @since   0.2
	 */
	function get_default_settings() { return $this->default_settings; }
	
	/**
	 * Returns the default sidebar_settings
	 *
	 * @return	String
	 * @since   0.2
	 */
	function get_default_sidebar_settings() { return $this->default_sidebar_settings; }
	
	/**
	 * Returns the plugin version
	 *
	 * @return	String
	 * @since   0.1.2
	 */
	function get_version() { return $this->version; }
	
	/**
	 * Returns the plugin key
	 *
	 * @return	String
	 * @since   0.1
	 */
	function get_plugin_key() { return $this->plugin_key; }
	
	/**
	 * Returns the general key (plugin settings)
	 *
	 * @return	String
	 * @since   0.1
	 */
	function get_general_key() { return $this->general_key; }
	
	/**
	 * Returns the general labels
	 *
	 * @return	Array
	 * @since   0.1
	 */
	function get_general_labels() {
		return array(
			'no_sidebars_available' => __( 'Please enable an off-canvas sidebar', 'off-canvas-sidebars' ), //themes.php?page=off-canvas-sidebars-settings
			'compatibility_notice_theme' => sprintf( __('If this plugin is not working as it should then your theme might not be compatible with this plugin, <a href="%s" target="_blank">please let me know!</a>', 'off-canvas-sidebars' ), 'https://wordpress.org/support/plugin/off-canvas-sidebars' ),
		);
	}
	
	/**
	 * Returns a sidebar key based on its label
	 *
	 * @param 	String 	$label
	 * @return	String	$key
	 * @since   0.1
	 */
	function get_sidebar_key_by_label($label) {
		foreach ( $this->general_settings['sidebars'] as $key => $value ) {
			if ( $label == $value['label'] ) 
				return $key;
		}
	}
	
	/**
	 * Checks if a sidebar is enabled
	 *
	 * @return	Boolean
	 * @since   0.1
	 */
	function is_sidebar_enabled() {
		foreach ( $this->general_settings['sidebars'] as $key => $value ) {
			if ( $value['enable'] == 1 ) 
				return true;
		}
		return false;
	}
	
	/**
	 * Register slidebar sidebars
	 * Also checks if theme is based on the Genesis Framework.
	 *
	 * @return	void
	 * @since   0.1
	 */
	function register_sidebars() {
		foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) {
			if ( $sidebar_data['enable'] == 1 ) {
				$args = array(
					'id'            => 'off-canvas-' . $sidebar,
					'class'			=> 'off-canvas-sidebar',
					'name'          => __( 'Off Canvas', 'off-canvas-sidebars' ) . ': ' . $this->general_settings['sidebars'][ $sidebar ]['label'],
					'description'   => __( 'This is a widget area that is used for off-canvas widgets.', 'off-canvas-sidebars' ),
					//'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-wrap"><div class="inner">',
					//'after_widget' 	=> '</div></div></section>',
					//'before_title' 	=> '<div class="widget-title-wrapper widgettitlewrapper"><h3 class="widget-title widgettitle">',
					//'after_title' 	=> '</h3></div>',
				);
				if ( get_template() == 'genesis' ) {
					genesis_register_sidebar( $args );
				} else {
					register_sidebar( $args );
				}
			}
		}
	}
	
	/**
	 * Add Settings link to plugin's entry on the Plugins page
	 * 
	 * @param $links
	 * @param $file
	 * @return array
	 * @since 0.1
	 */
	function add_settings_link( $links, $file ) {
		static $this_plugin;
		if ( !$this_plugin ) $this_plugin = OCS_BASENAME;

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="'.admin_url( 'themes.php?page=off-canvas-sidebars-settings' ).'">'.esc_attr__( 'Settings', 'off-canvas-sidebars' ).'</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 0.1
	 * @return	void
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'off-canvas-sidebars', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	
} // end class

/**
 * Main instance of Off-Canvas Sidebars.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars to prevent the need to use globals.
 *
 * @since  0.1.2
 * @return OCS_Off_Canvas_Sidebars
 */
function Off_Canvas_Sidebars() {
	return OCS_Off_Canvas_Sidebars::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['off_canvas_sidebars'] = Off_Canvas_Sidebars();
