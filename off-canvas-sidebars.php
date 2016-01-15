<?php
/**
 * Plugin Name: Off-Canvas Sidebars
 * Description: Add off-canvas sidebars using the Slidebars jQuery plugin
 * Plugin URI:  https://wordpress.org/plugins/off-canvas-sidebars/
 * Version:     0.1
 * Author:      Jory Hogeveen
 * Author URI:  http://www.keraweb.nl
 * Text Domain: off-canvas-sidebars
 * Domain Path: /languages/
 * License: 	GPLv2
 */
 
! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

$off_canvas_sidebars = new OCS_Off_Canvas_Sidebars();

class OCS_Off_Canvas_Sidebars {
	
	/**
	 * Plugin version
	 *
	 * @var    String
	 * @since  0.1
	 */
	protected $version = '0.1';

	/**
	 * User ignore nag key
	 *
	 * @var    String
	 * @since  1.1
	 */
	protected $noticeKey = 'ocs_ignore_genesis_notice';
	
	/**
	 * Current user object
	 *
	 * @var    Object
	 * @since  1.1
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
	 * Init function to register plugin hook
	 *
	 * @return	void
	 * @since   0.1
	 */
	function __construct() {
		// Lets start!
		add_action( 'init', array( $this, 'init' ) );
		
		// Load translations
		$this->load_textdomain();
		
		include_once 'widgets/off-canvas-sidebars-widget.php';
		add_action( 'widgets_init', function() {
			register_widget( 'OCS_Off_Canvas_Sidebars_Control_Widget' );
		} );
		include_once 'includes/off-canvas-sidebars-menu-meta-box.class.php';
		new OCS_Off_Canvas_Sidebars_Menu_Meta_box();
	}
	
	/**
	 * Init function/action to check current user, load nessesary data and classes, register hooks
	 *
	 * @return	void
	 * @since   0.1
	 */
	function init() {
		if ( !defined( 'OCS_PLUGIN_VERSION' ) ) define( 'OCS_PLUGIN_VERSION', $this->version );
		if ( !defined( 'OCS_FILE' ) ) define( 'OCS_FILE', __FILE__ );
		if ( !defined( 'OCS_BASENAME' ) ) define( 'OCS_BASENAME', plugin_basename( __FILE__ ) );
		if ( !defined( 'OCS_PLUGIN_DIR' ) ) define( 'OCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		if ( !defined( 'OCS_PLUGIN_URL' ) ) define( 'OCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				
		// Get the current user
		$this->curUser = wp_get_current_user();
		$this->enable = true;
		
		if ($this->enable == true) {
			$this->general_settings = ( get_option( $this->general_key ) ) ? get_option( $this->general_key ) : array();
			$this->general_settings = $this->get_settings(); // Merge DB settings with default settings
			$this->general_labels = $this->get_general_labels();
			
			if ( get_template() == 'genesis' ) {
				$this->register_sidebars_genesis();
			} else {
				$this->register_sidebars();
			}
			
			if (is_admin()) {
				include_once 'includes/off-canvas-sidebars-settings.class.php';
				new OCS_Off_Canvas_Sidebars_Settings();
				
				add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
			} else {
				include_once 'includes/off-canvas-sidebars-frontend.class.php';
				new OCS_Off_Canvas_Sidebars_Frontend();
			}
			
		} else {
			add_action( 'admin_notices', array( $this, 'genesis_notice' ) ); 
			add_action( 'wp_ajax_'.$this->noticeKey, array( $this, 'ignore_genesis_notice' ) );
		}
	}
	
	/**
	 * Add notice when theme is not based on the Genesis Framework
	 * Checks for version in the notice ignore meta value. If the version is the same (user has clicked ignore), then hide it
	 *
	 * @return	void
	 * @since   0.1
	 */
	function genesis_notice() {
		if ( get_template() != 'genesis' ) {
			if ( get_user_meta( $this->curUser->ID, $this->noticeKey, true ) != $this->version ) {
				$class = 'error notice is-dismissible';
				$message = '<strong>Off-Canvas Sidebars:</strong> The <a href="http://my.studiopress.com/themes/genesis/" targer="_blank">Genesis framework</a> is recommended to ensure that Off-Canvas Sidebars will work properly';
				$ignore = '<a id="' . $this->noticeKey . '" href="?' . $this->noticeKey . '=1" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'off-canvas-sidebars' ) . '</span></a>';
				$script = '<script>(function($) { $(document).on("click", "#' . $this->noticeKey . '", function(e){e.preventDefault();$.post(ajaxurl, {\'action\': \'' . $this->noticeKey . '\'});}) })( jQuery );</script>';
				echo '<div id="' . $this->noticeKey . '" class="' . $class . '"> <p>' . $message . '</p> ' . $ignore . $script . '</div>';
			}
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
	function ignore_genesis_notice() {
		update_user_meta( $this->curUser->ID, $this->noticeKey, $this->version );
		wp_die();
	}
	
	/**
	 * Merge database plugin settings with default settings
	 *
	 * @return	void
	 * @since   0.1
	 */
	function get_settings() {
		$args = $this->general_settings;
		$defaults = array(
			'sidebars' => array(
				'left' => array(
					'enable' => 0,
					'width' => 'default',
					'width_input' => '',
					'width_input_type' => '%',
					'style' => 'push',
				),
				'right' => array(
					'enable' => 0,
					'width' => 'default',
					'width_input' => '',
					'width_input_type' => '%',
					'style' => 'push',
				),
			),
			'site_close' => 1,
			'disable_over' => '',
			'hide_control_classes' => 0,
			'scroll_lock' => 0,
		);
		// Add values that are missing
		$args = array_merge( $defaults, $args );
		/*foreach ( $defaults['sidebars'] as $key => $value ) {
			if ( ! isset( $args['sidebars'][$key] ) ) {
				$args['sidebars'][$key] = $defaults['sidebars'][$key];
			}
			foreach ($defaults['sidebars'][$key] as $key2 => $value2) {
				if ( ! isset( $args['sidebars'][$key][$key2] ) ) {
					$args['sidebars'][$key][$key2] = $defaults['sidebars'][$key][$key2];
				}
			}
		}*/
		// Remove values that should not exist
		foreach ( $args as $key => $value ) {
			if ( ! isset( $defaults[$key] ) ) {
				unset($args[$key]);
			}
			foreach ( $args['sidebars'] as $key2 => $value2 ) {
				if ( ! isset( $defaults['sidebars'][$key2] ) ) {
					unset($args['sidebars'][$key2]);
				}
				if ( isset( $args['sidebars'][$key2] ) ) {
					foreach ( $args['sidebars'][$key2] as $key3 => $value3 ) {
						if ( isset( $defaults['sidebars'][$key2] ) && ! isset( $defaults['sidebars'][$key2][$key3] ) ) {
							unset($args['sidebars'][$key2][$key3]);
						}
					}
				}
			}
		}
		return $args;
	}
	
	/**
	 * Returns the plugin key
	 *
	 * @return	String
	 * @since   0.1
	 */
	function get_plugin_key() {return $this->plugin_key;}
	
	/**
	 * Returns the general key (plugin settings)
	 *
	 * @return	String
	 * @since   0.1
	 */
	function get_general_key() {return $this->general_key;}
	
	/**
	 * Returns the general labels
	 *
	 * @return	Array
	 * @since   0.1
	 */
	function get_general_labels() {
		return array(
			'sidebars' => array(
				'left' => array(
					'label' => __( 'Left', 'off-canvas-sidebars' ),
					'sidebar_name' => __( 'Off Canvas Left', 'off-canvas-sidebars' ),
				),
				'right' => array(
					'label' => __( 'Right', 'off-canvas-sidebars' ),
					'sidebar_name' => __( 'Off Canvas Right', 'off-canvas-sidebars' ),
				),
			),
			'no_sidebars_available' => __( 'Please enable an off-canvas sidebar', 'off-canvas-sidebars' ), //themes.php?page=off-canvas-sidebars-settings
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
		foreach ( $this->general_labels['sidebars'] as $key => $value ) {
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
	 * Register slidebar sidebars for Genesis Framework
	 *
	 * @return	void
	 * @since   0.1
	 */
	function register_sidebars_genesis() {
		foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) {
			if ( $sidebar_data['enable'] == 1 ) {
				genesis_register_sidebar( array(
					'id'            => 'off-canvas-'.$sidebar,
					'class'			=> 'off-canvas-sidebar',
					'name'          => $this->general_labels['sidebars'][$sidebar]['sidebar_name'],
					'description'   => __( 'This is a widget area that is used for off-canvas widgets.', 'off-canvas-sidebars' ),
					//'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-wrap"><div class="inner">',
					//'after_widget' 	=> '</div></div></section>',
					//'before_title' 	=> '<div class="widget-title-wrapper widgettitlewrapper"><h3 class="widget-title widgettitle">',
					//'after_title' 	=> '</h3></div>',
				) );
			}
		}
	}

	/**
	 * Register slidebar sidebars
	 *
	 * @return	void
	 * @since   0.1
	 */
	function register_sidebars() {
		foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) {
			if ( $sidebar_data['enable'] == 1 ) {
				
				register_sidebar( array(
					'id'            => 'off-canvas-'.$sidebar,
					'class'			=> 'off-canvas-sidebar',
					'name'          => $this->general_labels['sidebars'][$sidebar]['sidebar_name'],
					'description'   => __( 'This is a widget area that is used for off-canvas widgets.', 'off-canvas-sidebars' ),
					//'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-wrap"><div class="inner">',
					//'after_widget' 	=> '</div></div></section>',
					//'before_title' 	=> '<div class="widget-title-wrapper widgettitlewrapper"><h3 class="widget-title widgettitle">',
					//'after_title' 	=> '</h3></div>',
				) );
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
