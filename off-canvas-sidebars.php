<?php
/**
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1
 * @version 0.4
 * @licence GPL-2.0+
 * @link    https://github.com/JoryHogeveen/off-canvas-sidebars
 *
 * @wordpress-plugin
 * Plugin Name:       Off-Canvas Sidebars
 * Description:       Add off-canvas sidebars using the Slidebars jQuery plugin
 * Plugin URI:        https://wordpress.org/plugins/off-canvas-sidebars/
 * Version:           0.4
 * Author:            Jory Hogeveen
 * Author URI:        http://www.keraweb.nl
 * Text Domain:       off-canvas-sidebars
 * Domain Path:       /languages/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/JoryHogeveen/off-canvas-sidebars
 *
 * @copyright 2015-2017 Jory Hogeveen
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * ( at your option ) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'OCS_PLUGIN_VERSION', '0.4' );
define( 'OCS_FILE', __FILE__ );
define( 'OCS_BASENAME', plugin_basename( OCS_FILE ) );
define( 'OCS_PLUGIN_DIR', plugin_dir_path( OCS_FILE ) );
define( 'OCS_PLUGIN_URL', plugin_dir_url( OCS_FILE ) );

final class OCS_Off_Canvas_Sidebars
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars
	 * @since  0.1.2
	 */
	protected static $_instance = null;

	/**
	 * Plugin version.
	 *
	 * @var    string
	 * @since  0.1
	 */
	protected $version = OCS_PLUGIN_VERSION;

	/**
	 * Database version.
	 *
	 * @var    string
	 * @since  0.2
	 */
	protected $db_version = '0';

	/**
	 * User ignore nag key.
	 *
	 * @var    string
	 * @since  0.1
	 */
	protected $noticeKey = 'ocs_ignore_theme_compatibility_notice';

	/**
	 * Current user object.
	 *
	 * @var    object
	 * @since  0.1
	 */
	protected $curUser = false;

	/**
	 * Enable functionalities?
	 *
	 * @var    bool
	 * @since  0.1
	 */
	protected $enable = false;

	/**
	 * Plugin key.
	 *
	 * @var    string
	 * @since  0.1
	 */
	protected $plugin_key = 'off-canvas-sidebars-settings';

	/**
	 * Plugin general settings key, also used as option key.
	 *
	 * @var    string
	 * @since  0.1
	 */
	protected $general_key = 'off_canvas_sidebars_options';

	/**
	 * Plugin settings.
	 *
	 * @var    array
	 * @since  0.1
	 */
	protected $settings = array();

	/**
	 * Plugin settings.
	 *
	 * @var    array
	 * @since  0.1
	 */
	protected $general_labels = array();

	/**
	 * Default settings.
	 *
	 * @var    array
	 * @since  0.2
	 */
	protected $default_settings = array(
		'db_version'                    => '0',
		'enable_frontend'               => 1,
		'frontend_type'                 => 'action',
		'site_close'                    => 1,
		'disable_over'                  => '',
		'hide_control_classes'          => 0,
		'scroll_lock'                   => 0,
		'background_color_type'         => '',
		'background_color'              => '',
		'website_before_hook'           => 'website_before',
		'website_after_hook'            => 'website_after',
		'use_fastclick'                 => 0,
		'compatibility_position_fixed'  => 'none',
		'wp_editor_shortcode_rendering' => 0,
		'css_prefix'                    => 'ocs',
		'sidebars'                      => array(),
	);

	/**
	 * Default sidebar settings.
	 *
	 * @var    array
	 * @since  0.2
	 */
	protected $default_sidebar_settings = array(
		'enable'                    => 0,
		'label'                     => '',
		'content'                   => 'sidebar',
		'location'                  => 'left',
		'style'                     => 'push',
		'size'                      => 'default',
		'size_input'                => '',
		'size_input_type'           => '%',
		'animation_speed'           => '',
		'padding'                   => '',
		'background_color'          => '',
		'background_color_type'     => '',
		// Global overwrites.
		'overwrite_global_settings' => 0,
		'site_close'                => 1,
		'disable_over'              => '',
		'hide_control_classes'      => 0,
		'scroll_lock'               => 0,
	);

	/**
	 * Init function to register plugin hook.
	 *
	 * @since   0.1
	 * @access  private
	 */
	private function __construct() {
		self::$_instance = $this;

		// DB version for the current plugin version (only major version tags).
		$this->db_version = substr( OCS_PLUGIN_VERSION, 0, 3 );

		$this->enable = true; // Added for possible use in future.
		if ( true === $this->enable ) {

			// Lets start!
			add_action( 'init', array( $this, 'init' ) );

			// Load the OCS API.
			include_once( OCS_PLUGIN_DIR . 'includes/api.php' );

			$this->settings = (array) get_option( $this->general_key, array() );
			$this->maybe_db_update();

			// Merge DB settings with default settings.
			$this->settings = $this->get_settings();
			$this->general_labels = $this->get_general_labels();

			// Register the widget.
			include_once( OCS_PLUGIN_DIR . 'widgets/control-widget.php' );
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );

			// Load menu-meta-box option.
			include_once( OCS_PLUGIN_DIR . 'includes/class-menu-meta-box.php' );
			OCS_Off_Canvas_Sidebars_Menu_Meta_Box::get_instance();

		} else {

			// Added for possible use in future
			add_action( 'admin_notices', array( $this, 'compatibility_notice' ) );
			add_action( 'wp_ajax_' . $this->noticeKey, array( $this, 'ignore_compatibility_notice' ) );
		}
	}

	/**
	 * Init function/action to check current user, load nessesary data and classes, register hooks.
	 *
	 * @return  void
	 * @since   0.1
	 */
	function init() {
		// Get the current user.
		//$this->curUser = wp_get_current_user();

		// Load translations.
		$this->load_textdomain();

		// Register the enabled sidebars.
		$this->register_sidebars();

		if ( is_admin() ) {

			// Load the settings page.
			include_once( OCS_PLUGIN_DIR . 'includes/class-form.php' );
			include_once( OCS_PLUGIN_DIR . 'includes/class-settings.php' );
			OCS_Off_Canvas_Sidebars_Settings::get_instance();

			// Load the WP Editor shortcode generator.
			include_once( OCS_PLUGIN_DIR . 'includes/class-editor-shortcode-generator.php' );
			OCS_Off_Canvas_Sidebars_Editor_Shortcode_Generator::get_instance();

			// Add settings link to plugins page.
			add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );

		} else {

			// If a sidebar is enabled, load the front-end.
			include_once( OCS_PLUGIN_DIR . 'includes/class-frontend.php' );
			OCS_Off_Canvas_Sidebars_Frontend::get_instance();
		}
	}

	/**
	 * Register widgets.
	 * @since  0.4
	 */
	function widgets_init() {
		register_widget( 'OCS_Off_Canvas_Sidebars_Control_Widget' );
	}

	/**
	 * Add notice when theme is not compatible.
	 * Checks for version in the notice ignore meta value. If the version is the same (user has clicked ignore), then hide it.
	 *
	 * @return  void
	 * @since   0.1
	 */
	function compatibility_notice() {
		if ( get_user_meta( $this->curUser->ID, $this->noticeKey, true ) !== $this->version ) {
			$class = 'error notice is-dismissible';
			$message = '<strong>' . __( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ) . ':</strong> ' . $this->general_labels['compatibility_notice_theme'];
			$ignore = '<a id="' . $this->noticeKey . '" href="?' . $this->noticeKey . '=1" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'off-canvas-sidebars' ) . '</span></a>';
			$script = '<script>(function($) { $(document).on("click", "#' . $this->noticeKey . '", function(e){e.preventDefault();$.post(ajaxurl, {\'action\': \'' . $this->noticeKey . '\'});}) })( jQuery );</script>';
			echo '<div id="' . $this->noticeKey . '" class="' . $class . '"> <p>' . $message . '</p> ' . $ignore . $script . '</div>';
		}
	}

	/**
	 * AJAX handler.
	 * Stores plugin version.
	 *
	 * Store format: `boolean`
	 *
	 * @since   0.1
	 */
	function ignore_compatibility_notice() {
		update_user_meta( $this->curUser->ID, $this->noticeKey, $this->version );
		wp_die();
	}

	/**
	 * Merge database plugin settings with default settings.
	 *
	 * @since   0.1
	 * @return  array
	 */
	function get_settings() {
		$settings = $this->settings;

		// Validate global settings.
		$settings = $this->validate_settings( $settings, $this->get_default_settings() );
		// Validate sidebar settings.
		foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_settings ) {
			$settings['sidebars'][ $sidebar_id ] = $this->validate_settings( $sidebar_settings, $this->get_default_sidebar_settings() );
		}

		return $settings;
	}

	/**
	 * Validate setting keys.
	 *
	 * @since   0.2
	 * @param   array  $settings
	 * @param   array  $defaults
	 * @return  array
	 */
	function validate_settings( $settings, $defaults ) {
		// supports one level array
		$settings = array_merge( $defaults, $settings );
		// Remove unknown keys
		foreach ( $settings as $key => $value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $settings[ $key ] );
			} else {
				// Validate types
				settype( $settings[ $key ], gettype( $defaults[ $key ] ) );
			}
		}
		return $settings;
	}

	/**
	 * Returns the default settings.
	 *
	 * @since   0.2
	 * @return  array
	 */
	function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Returns the default sidebar_settings.
	 *
	 * @since   0.2
	 * @return  array
	 */
	function get_default_sidebar_settings() {
		return $this->default_sidebar_settings;
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since   0.1.2
	 * @return  string
	 */
	function get_version() {
		return $this->version;
	}

	/**
	 * Returns the plugin key.
	 *
	 * @since   0.1
	 * @return  string
	 */
	function get_plugin_key() {
		return $this->plugin_key;
	}

	/**
	 * Returns the general key (plugin settings).
	 *
	 * @since   0.1
	 * @return  string
	 */
	function get_general_key() {
		return $this->general_key;
	}

	/**
	 * Returns the general labels.
	 *
	 * @since   0.1
	 * @return  array
	 */
	function get_general_labels() {
		return array(
			'no_sidebars_available' => __( 'Please enable an off-canvas sidebar', 'off-canvas-sidebars' ),
			// Translators: %s stands for the URL.
			'compatibility_notice_theme' => sprintf( __( 'If this plugin is not working as it should then your theme might not be compatible with this plugin, <a href="%s" target="_blank">please let me know!</a>', 'off-canvas-sidebars' ), 'https://github.com/JoryHogeveen/off-canvas-sidebars/issues' ),
		);
	}

	/**
	 * Returns a sidebar key based on its label.
	 *
	 * @since   0.1
	 * @param   string  $label
	 * @return  string  $key
	 */
	function get_sidebar_key_by_label( $label ) {
		foreach ( $this->settings['sidebars'] as $key => $value ) {
			if ( $label === $value['label'] )
				return $key;
		}
		return false;
	}

	/**
	 * Checks if a sidebar is enabled.
	 *
	 * @since   0.1
	 * @return  bool
	 */
	function is_sidebar_enabled() {
		foreach ( $this->settings['sidebars'] as $key => $value ) {
			if ( 1 === (int) $value['enable'] )
				return true;
		}
		return false;
	}

	/**
	 * Register slidebar sidebars.
	 * Also checks if theme is based on the Genesis Framework.
	 *
	 * @since   0.1
	 * @return  void
	 */
	function register_sidebars() {
		foreach ( $this->settings['sidebars'] as $sidebar_id => $sidebar_data ) {

			if ( ! empty( $sidebar_data['enable'] ) ) {

				if ( 'sidebar' === $sidebar_data['content'] ) {

					$args = array(
						//'id'            => 'off-canvas-' . $sidebar_id,
						'class'         => 'off-canvas-sidebar',
						'name'          => __( 'Off Canvas', 'off-canvas-sidebars' ) . ': ' . $this->settings['sidebars'][ $sidebar_id ]['label'],
						'description'   => __( 'This is a widget area that is used for off-canvas widgets.', 'off-canvas-sidebars' ),
						//'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-wrap"><div class="inner">',
						//'after_widget'  => '</div></div></section>',
						//'before_title'  => '<div class="widget-title-wrapper widgettitlewrapper"><h3 class="widget-title widgettitle">',
						//'after_title'   => '</h3></div>',
					);

					/**
					 * Filter the register_sidebar arguments.
					 *
					 * Please note that the ID will be overwritten!
					 *
					 * @since 0.3
					 *
					 * @see https://codex.wordpress.org/Function_Reference/register_sidebar
					 * @see $default_sidebar_settings for the sidebar settings.
					 *
					 * @param  array  $args          The register_sidebar() arguments
					 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
					 * @param  array  $sidebar_data  The sidebar settings
					 */
					$args = apply_filters( 'ocs_register_sidebar_args', $args, $sidebar_id, $sidebar_data );

					// Force our ID
					$args['id'] = 'off-canvas-' . $sidebar_id;

					if ( get_template() === 'genesis' ) {
						genesis_register_sidebar( $args );
					} else {
						register_sidebar( $args );
					}
				}

				elseif ( 'menu' === $sidebar_data['content'] ) {

					register_nav_menu(
						'off-canvas-' . $sidebar_id,
						__( 'Off Canvas', 'off-canvas-sidebars' ) . ': ' . $this->settings['sidebars'][ $sidebar_id ]['label']
					);
				} // End if().
			} // End if().
		} // End foreach().
	}

	/**
	 * Add Settings link to plugin's entry on the Plugins page.
	 *
	 * @since   0.1
	 * @param   array   $links
	 * @param   string  $file
	 * @return  array
	 */
	function add_settings_link( $links, $file ) {
		if ( OCS_BASENAME === $file ) {
			$settings_link = '<a href="' . admin_url( 'themes.php?page=' . $this->plugin_key ) . '">' . esc_attr__( 'Settings', 'off-canvas-sidebars' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since   0.1
	 * @return  void
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'off-canvas-sidebars', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Update settings.
	 *
	 * @since   0.2
	 * @access  private
	 * @return  void
	 */
	private function db_update() {
		$settings = $this->settings;
		$db_version = strtolower( $settings['db_version'] );

		// Upgrade to 0.2.x
		if ( version_compare( $db_version, '0.2', '<' ) && isset( $settings['sidebars'] ) ) {
			foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_data ) {
				if ( empty( $sidebar_data['label'] ) ) {
					// @codingStandardsIgnoreLine - Label is new
					$settings['sidebars'][ $sidebar_id ]['label'] = __( ucfirst( $sidebar_id ), 'off-canvas-sidebars' );
					// Location is new. In older versions the location was the sidebar_id (left or right)
					$settings['sidebars'][ $sidebar_id ]['location'] = $sidebar_id;
					if ( isset( $sidebar_data['width'] ) ) {
						if ( 'thin' === $sidebar_data['width'] ) {
							$sidebar_data['width'] = 'small';
						} elseif ( 'wide' === $sidebar_data['width'] ) {
							$sidebar_data['width'] = 'large';
						}
						$settings['sidebars'][ $sidebar_id ]['size'] = $sidebar_data['width'];
					}
					if ( isset( $sidebar_data['width_input'] ) ) {
						$settings['sidebars'][ $sidebar_id ]['size_input'] = $sidebar_data['width_input'];
					}
					if ( isset( $sidebar_data['width_input_type'] ) ) {
						$settings['sidebars'][ $sidebar_id ]['size_input_type'] = $sidebar_data['width_input_type'];
					}
				}
			}
		}

		// Upgrade to 0.3.x
		if ( version_compare( $db_version, '0.3', '<' ) && version_compare( $db_version, '0', '>' ) ) {
			// Old Slidebars classes prefix.
			$settings['css_prefix'] = 'sb';
		}

		// Upgrade to 0.4
		if ( version_compare( $db_version, '0.4', '<' ) ) {
			if ( ! empty( $settings['compatibility_position_fixed'] ) ) {
				// This was the only option before 0.4.
				$settings['compatibility_position_fixed'] = 'custom-js';
			}
		}

		$settings['db_version'] = $this->db_version;
		update_option( $this->general_key, $settings );
		$this->settings = $settings;
	}

	/**
	 * Check the correct DB version in the DB.
	 *
	 * @since   0.2
	 * @access  public
	 * @return  void
	 */
	public function maybe_db_update() {
		if ( ! isset( $this->settings['db_version'] ) ) {
			$this->settings['db_version'] = 0;
			$this->db_update();
		}
		$db_version = strtolower( $this->settings['db_version'] );
		if ( version_compare( $db_version, $this->db_version, '<' ) ) {
			$this->db_update();
		}
	}

	/**
	 * Main Off-Canvas Sidebars Instance.
	 *
	 * Ensures only one instance of Off-Canvas Sidebars is loaded or can be loaded.
	 *
	 * @since   0.1.2
	 * @static
	 * @see     off_canvas_sidebars()
	 * @return  OCS_Off_Canvas_Sidebars
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Magic method to output a string if trying to use the object as a string.
	 *
	 * @since   0.3
	 * @access  public
	 * @return  string
	 */
	public function __toString() {
		return get_class( $this );
	}

	/**
	 * Magic method to keep the object from being cloned.
	 *
	 * @since   0.3
	 * @access  public
	 * @return  void
	 */
	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to be cloned', 'view-admin-as' ),
			null
		);
	}

	/**
	 * Magic method to keep the object from being unserialized.
	 *
	 * @since   0.3
	 * @access  public
	 * @return  void
	 */
	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to wake up', 'view-admin-as' ),
			null
		);
	}

	/**
	 * Magic method to prevent a fatal error when calling a method that doesn't exist.
	 *
	 * @since   0.3
	 * @access  public
	 * @param   string  $method
	 * @param   array   $args
	 * @return  null
	 */
	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong(
			get_class( $this ) . "::{$method}",
			esc_html__( 'Method does not exist.', 'off-canvas-sidebars' ),
			null
		);
		unset( $method, $args );
		return null;
	}

} // End class().

/**
 * Main instance of Off-Canvas Sidebars.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars to prevent the need to use globals.
 *
 * @since   0.1.2
 * @return  OCS_Off_Canvas_Sidebars
 */
function off_canvas_sidebars() {
	return OCS_Off_Canvas_Sidebars::get_instance();
}
off_canvas_sidebars();
