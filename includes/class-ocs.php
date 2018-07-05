<?php
/**
 * Off-Canvas Sidebars - Class Init (Main class)
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Plugin initializer class.
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.2
 */
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
	 * @since  0.1.0
	 */
	protected $version = OCS_PLUGIN_VERSION;

	/**
	 * Database version.
	 *
	 * @var    string
	 * @since  0.2.0
	 */
	protected $db_version = '0';

	/**
	 * User ignore nag key.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $noticeKey = 'ocs_ignore_theme_compatibility_notice';

	/**
	 * Current user object.
	 *
	 * @var    object
	 * @since  0.1.0
	 */
	protected $curUser = false;

	/**
	 * Enable functionalities?
	 *
	 * @var    bool
	 * @since  0.1.0
	 */
	protected $enable = false;

	/**
	 * Plugin key.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $plugin_key = 'off-canvas-sidebars-settings';

	/**
	 * Plugin general settings key, also used as option key.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $general_key = 'off_canvas_sidebars_options';

	/**
	 * Plugin settings.
	 *
	 * @var    array
	 * @since  0.1.0
	 */
	protected $general_labels = array();

	/**
	 * Class registry
	 *
	 * @since  0.5.2
	 * @var    array
	 */
	private $classes = array(
		'Base'             => 'includes/class-base.php',
		'Control_Trigger'  => 'includes/class-control-trigger.php',
		'Form'             => 'includes/class-form.php',
		'Frontend'         => 'includes/class-frontend.php',
		'Page'             => 'includes/class-page.php',
		'Settings'         => 'includes/class-settings.php',
		'Tab'              => 'includes/class-tab.php',
		'Tab_General'      => 'includes/class-tab-general.php',
		'Tab_Importexport' => 'includes/class-tab-importexport.php',
		'Tab_Shortcode'    => 'includes/class-tab-shortcode.php',
		'Tab_Sidebars'     => 'includes/class-tab-sidebars.php',
		'Menu_Meta_Box'    => 'includes/class-menu-meta-box.php',
		'Mce_Shortcode'    => 'tinymce/class-mce-shortcode.php',
		'Control_Widget'   => 'widgets/control-widget.php',
	);

	/**
	 * Init function to register plugin hook.
	 *
	 * @since   0.1.0
	 * @access  private
	 */
	private function __construct() {
		self::$_instance = $this;

		// DB version for the current plugin version (only major version tags).
		$this->db_version = substr( OCS_PLUGIN_VERSION, 0, 3 );

		$this->enable = true; // Added for possible use in future.
		if ( true === $this->enable ) {

			spl_autoload_register( array( $this, '_autoload' ) );

			// Lets start!
			add_action( 'init', array( $this, 'init' ) );

			// Load the OCS API.
			include_once OCS_PLUGIN_DIR . 'includes/api.php';

			off_canvas_sidebars_settings();

			$this->maybe_db_update();

			$this->general_labels = $this->get_general_labels();

			// Register the widget.
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );

			// Load menu-meta-box option.
			OCS_Off_Canvas_Sidebars_Menu_Meta_Box::get_instance();

		} else {

			// Added for possible use in future.
			add_action( 'admin_notices', array( $this, 'compatibility_notice' ) );
			add_action( 'wp_ajax_' . $this->noticeKey, array( $this, 'ignore_compatibility_notice' ) );
		}
	}

	/**
	 * Class autoloader if needed.
	 *
	 * @since   0.5.2
	 * @access  private
	 * @internal
	 * @param   string  $class  The class name.
	 */
	public function _autoload( $class ) {
		$prefix = 'OCS_Off_Canvas_Sidebars_';
		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}
		$ocs_class = str_replace( $prefix, '', $class );
		if ( isset( $this->classes[ $ocs_class ] ) ) {
			include_once OCS_PLUGIN_DIR . $this->classes[ $ocs_class ];
		}
	}

	/**
	 * Init function/action to check current user, load necessary data and classes, register hooks.
	 *
	 * @since   0.1.0
	 */
	public function init() {
		// Get the current user.
		//$this->curUser = wp_get_current_user();

		// Load translations.
		$this->load_textdomain();

		// Register the enabled sidebars.
		$this->register_sidebars();

		if ( is_admin() ) {

			// Load the settings page.
			OCS_Off_Canvas_Sidebars_Page::get_instance();

			// Load the WP Editor shortcode generator.
			OCS_Off_Canvas_Sidebars_Mce_Shortcode::get_instance();

			// Add settings link to plugins page.
			add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );
			add_action( 'plugin_row_meta', array( $this, 'action_plugin_row_meta' ), 10, 2 );

		} else {
			off_canvas_sidebars_frontend();
		}
	}

	/**
	 * Register widgets.
	 * @since  0.4.0
	 */
	public function widgets_init() {
		register_widget( 'OCS_Off_Canvas_Sidebars_Control_Widget' );
	}

	/**
	 * Add notice when theme is not compatible.
	 * Checks for version in the notice ignore meta value. If the version is the same (user has clicked ignore), then hide it.
	 *
	 * @since   0.1.0
	 */
	public function compatibility_notice() {
		if ( get_user_meta( $this->curUser->ID, $this->noticeKey, true ) !== $this->version ) {
			$class = 'error notice is-dismissible';
			$message = '<strong>' . __( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ) . ':</strong> ' . $this->general_labels['compatibility_notice_theme'];
			$ignore = '<a id="' . $this->noticeKey . '" href="?' . $this->noticeKey . '=1" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', OCS_DOMAIN ) . '</span></a>';
			$script = '<script>(function($) { $(document).on("click", "#' . $this->noticeKey . '", function(e) {e.preventDefault();$.post(ajaxurl, {\'action\': \'' . $this->noticeKey . '\'});}) })( jQuery );</script>';
			echo '<div id="' . $this->noticeKey . '" class="' . $class . '"> <p>' . $message . '</p> ' . $ignore . $script . '</div>';
		}
	}

	/**
	 * AJAX handler.
	 * Stores plugin version.
	 *
	 * Store format: `boolean`
	 *
	 * @since   0.1.0
	 */
	public function ignore_compatibility_notice() {
		update_user_meta( $this->curUser->ID, $this->noticeKey, $this->version );
		wp_die();
	}

	/**
	 * Get the plugin settings.
	 * @param   string  $key
	 * @return  mixed
	 */
	public function get_settings( $key = null ) {
		return OCS_Off_Canvas_Sidebars_Settings::get_instance()->get_settings( $key );
	}

	/**
	 * Get all sidebars or a single sidebar by id.
	 * @param   string  $id
	 * @return  array|null
	 */
	public function get_sidebars( $id = null ) {
		$sidebars = $this->get_settings( 'sidebars' );
		if ( $id ) {
			return ( isset( $sidebars[ $id ] ) ) ? $sidebars[ $id ] : null;
		}
		return ( isset( $sidebars ) ) ? $sidebars : null;
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since   0.1.2
	 * @return  string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since   0.1.2
	 * @return  string
	 */
	public function get_db_version() {
		return $this->db_version;
	}

	/**
	 * Returns the plugin key.
	 *
	 * @since   0.1.0
	 * @return  string
	 */
	public function get_plugin_key() {
		return $this->plugin_key;
	}

	/**
	 * Returns the general key (plugin settings).
	 *
	 * @since   0.1.0
	 * @return  string
	 */
	public function get_general_key() {
		return $this->general_key;
	}

	/**
	 * Returns the general labels.
	 *
	 * @since   0.1.0
	 * @since   0.5.0  `$key` parameter.
	 * @param   string  $key  (optional) The label key.
	 * @return  array|string
	 */
	public function get_general_labels( $key = null ) {
		static $labels;
		if ( ! $labels ) {
			$labels = array(
				'no_sidebars_available' => __( 'Please enable an off-canvas sidebar', OCS_DOMAIN ),
				// Translators: %s stands for the URL.
				'compatibility_notice_theme' => sprintf( __( 'If this plugin is not working as it should then your theme might not be compatible with this plugin, <a href="%s" target="_blank">please let me know!</a>', OCS_DOMAIN ), 'https://github.com/JoryHogeveen/off-canvas-sidebars/issues' ),
			);
		}
		if ( $key ) {
			return ( isset( $labels[ $key ] ) ) ? $labels[ $key ] : '';
		}
		return $labels;
	}

	/**
	 * Returns a sidebar key based on its label.
	 *
	 * @since   0.1.0
	 * @param   string  $label
	 * @return  string  $key
	 */
	public function get_sidebar_key_by_label( $label ) {
		foreach ( $this->get_sidebars() as $key => $value ) {
			if ( $label === $value['label'] ) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Checks if an off-canvas sidebar is enabled.
	 * If no $id parameter is passed it will check if any off-canvas sidebar is enabled.
	 *
	 * @since   0.1.0
	 * @since   0.5.0  Optional ID parameter.
	 * @param   string  $id  Sidebar ID.
	 * @return  bool
	 */
	public function is_sidebar_enabled( $id = null ) {
		$sidebars = $this->get_sidebars();
		if ( $id ) {
			return ( ! empty( $sidebars[ $id ]['enable'] ) );
		}
		foreach ( $this->get_sidebars() as $key => $value ) {
			if ( ! empty( $value['enable'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Register slidebar sidebars.
	 * Also checks if theme is based on the Genesis Framework.
	 *
	 * @since   0.1.0
	 */
	public function register_sidebars() {
		$sidebars = $this->get_sidebars();
		foreach ( $sidebars as $sidebar_id => $sidebar_data ) {

			if ( ! empty( $sidebar_data['enable'] ) ) {

				if ( 'sidebar' === $sidebar_data['content'] ) {

					$args = array(
						//'id'            => 'off-canvas-' . $sidebar_id,
						'class'         => 'off-canvas-sidebar',
						'name'          => __( 'Off Canvas', OCS_DOMAIN ) . ': ' . $sidebars[ $sidebar_id ]['label'],
						'description'   => __( 'This is a widget area that is used for off-canvas widgets.', OCS_DOMAIN ),
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
					 * @since 0.3.0
					 *
					 * @see  https://codex.wordpress.org/Function_Reference/register_sidebar
					 * @see  OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
					 *
					 * @param  array  $args          The register_sidebar() arguments.
					 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars.
					 * @param  array  $sidebar_data  The sidebar settings.
					 */
					$args = apply_filters( 'ocs_register_sidebar_args', $args, $sidebar_id, $sidebar_data );

					// Force our ID.
					$args['id'] = 'off-canvas-' . $sidebar_id;

					if ( function_exists( 'genesis_register_sidebar' ) ) {
						genesis_register_sidebar( $args );
					} else {
						register_sidebar( $args );
					}
				}

				elseif ( 'menu' === $sidebar_data['content'] ) {

					register_nav_menu(
						'off-canvas-' . $sidebar_id,
						__( 'Off Canvas', OCS_DOMAIN ) . ': ' . $sidebars[ $sidebar_id ]['label']
					);
				} // End if().
			} // End if().
		} // End foreach().
	}

	/**
	 * Add Settings link to plugin's entry on the Plugins page.
	 *
	 * @since   0.1.0
	 * @since   0.5.0   Renamed from `add_settings_link`.
	 * @param   array   $links
	 * @param   string  $file
	 * @return  array
	 */
	public function filter_plugin_action_links( $links, $file ) {
		if ( OCS_BASENAME === $file ) {
			$settings_link = '<a href="' . admin_url( 'themes.php?page=' . $this->plugin_key ) . '">' . esc_attr__( 'Settings', OCS_DOMAIN ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @since   0.5.0
	 * @see     \WP_Plugins_List_Table::single_row()
	 * @param   array[]  $links  The existing links.
	 * @param   string   $file   The plugin file.
	 * @return  array
	 */
	public function action_plugin_row_meta( $links, $file ) {
		if ( OCS_BASENAME === $file ) {
			$icon_attr = array(
				'style' => array(
					'font-size: inherit;',
					'line-height: inherit;',
					'display: inline;',
					'vertical-align: text-top;',
				),
			);
			foreach ( $this->get_links() as $id => $link ) {
				$icon_attr['class'] = 'dashicons ' . $link['icon'];
				$title = '<span ' . OCS_Off_Canvas_Sidebars_Base::parse_to_html_attr( $icon_attr ) . '></span> ' . esc_html( $link['title'] );
				$links[ $id ] = '<a href="' . esc_url( $link['url'] ) . '" target="_blank">' . $title . '</a>';
			}
		}
		return $links;
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since   0.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'off-canvas-sidebars', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Update settings.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.2.0
	 * @access  private
	 */
	private function db_update() {
		$settings = $this->get_settings();
		$db_version = strtolower( $settings['db_version'] );

		if ( $db_version ) {

			// Upgrade to 0.2.x
			if ( version_compare( $db_version, '0.2', '<' ) && isset( $settings['sidebars'] ) ) {
				foreach ( $settings['sidebars'] as $sidebar_id => $sidebar_data ) {
					if ( empty( $sidebar_data['label'] ) ) {
						// @codingStandardsIgnoreLine - Label is new
						$settings['sidebars'][ $sidebar_id ]['label'] = __( ucfirst( $sidebar_id ), OCS_DOMAIN );
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
		}

		$settings['db_version'] = $this->db_version;
		OCS_Off_Canvas_Sidebars_Settings::get_instance()->update_settings( $settings );
	}

	/**
	 * Check the correct DB version in the DB.
	 *
	 * @since   0.2.0
	 * @access  public
	 */
	public function maybe_db_update() {
		$db_version = strtolower( $this->get_settings( 'db_version' ) );
		if ( version_compare( $db_version, $this->db_version, '<' ) ) {
			$this->db_update();
		}
	}

	/**
	 * Plugin links.
	 *
	 * @since   0.5.0
	 * @return  array[]
	 */
	public function get_links() {
		static $links;
		if ( ! empty( $links ) ) {
			return $links;
		}

		$links = array(
			'support' => array(
				'title' => __( 'Support', OCS_DOMAIN ),
				'description' => __( 'Need support?', OCS_DOMAIN ),
				'icon'  => 'dashicons-sos',
				'url'   => 'https://wordpress.org/support/plugin/off-canvas-sidebars/',
			),
			'slack' => array(
				'title' => __( 'Slack', OCS_DOMAIN ),
				'description' => __( 'Quick help via Slack', OCS_DOMAIN ),
				'icon'  => 'dashicons-format-chat',
				'url'   => 'https://keraweb.slack.com/messages/plugin-ocs/',
			),
			'review' => array(
				'title' => __( 'Review', OCS_DOMAIN ),
				'description' => __( 'Give 5 stars on WordPress.org!', OCS_DOMAIN ),
				'icon'  => 'dashicons-star-filled',
				'url'   => 'https://wordpress.org/support/plugin/off-canvas-sidebars/reviews/',
			),
			'translate' => array(
				'title' => __( 'Translate', OCS_DOMAIN ),
				'description' => __( 'Help translating this plugin!', OCS_DOMAIN ),
				'icon'  => 'dashicons-translation',
				'url'   => 'https://translate.wordpress.org/projects/wp-plugins/off-canvas-sidebars',
			),
			'issue' => array(
				'title' => __( 'Report issue', OCS_DOMAIN ),
				'description' => __( 'Have ideas or a bug report?', OCS_DOMAIN ),
				'icon'  => 'dashicons-lightbulb',
				'url'   => 'https://github.com/JoryHogeveen/off-canvas-sidebars/issues',
			),
			'docs' => array(
				'title' => __( 'Documentation', OCS_DOMAIN ),
				'description' => __( 'Documentation', OCS_DOMAIN ),
				'icon'  => 'dashicons-book-alt',
				'url'   => 'https://github.com/JoryHogeveen/off-canvas-sidebars/wiki',
			),
			'github' => array(
				'title' => __( 'GitHub', OCS_DOMAIN ),
				'description' => __( 'Follow and/or contribute on GitHub', OCS_DOMAIN ),
				'icon'  => 'dashicons-editor-code',
				'url'   => 'https://github.com/JoryHogeveen/off-canvas-sidebars/tree/dev',
			),
			'donate' => array(
				'title' => __( 'Donate', OCS_DOMAIN ),
				'description' => __( 'Buy me a coffee!', OCS_DOMAIN ),
				'icon'  => 'dashicons-smiley',
				'url'   => 'https://www.keraweb.nl/donate.php?for=off-canvas-sidebars',
			),
			'plugins' => array(
				'title' => __( 'Plugins', OCS_DOMAIN ),
				'description' => __( 'Check out my other WordPress plugins', OCS_DOMAIN ),
				'icon'  => 'dashicons-admin-plugins',
				'url'   => 'https://profiles.wordpress.org/keraweb/#content-plugins',
			),
		);

		return $links;
	}

	/**
	 * Main Off-Canvas Sidebars Instance.
	 *
	 * Ensures only one instance of Off-Canvas Sidebars is loaded or can be loaded.
	 *
	 * @since   0.1.2
	 * @static
	 * @see     off_canvas_sidebars()
	 * @return  \OCS_Off_Canvas_Sidebars
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
	 * @since   0.3.0
	 * @access  public
	 * @return  string
	 */
	public function __toString() {
		return get_class( $this );
	}

	/**
	 * Magic method to keep the object from being cloned.
	 *
	 * @since   0.3.0
	 * @access  public
	 */
	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to be cloned', OCS_DOMAIN ),
			null
		);
	}

	/**
	 * Magic method to keep the object from being unserialized.
	 *
	 * @since   0.3.0
	 * @access  public
	 */
	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			get_class( $this ) . ': ' . esc_html__( 'This class does not want to wake up', OCS_DOMAIN ),
			null
		);
	}

	/**
	 * Magic method to prevent a fatal error when calling a method that doesn't exist.
	 *
	 * @since   0.3.0
	 * @access  public
	 * @param   string  $method
	 * @param   array   $args
	 * @return  null
	 */
	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong(
			get_class( $this ) . "::{$method}",
			esc_html__( 'Method does not exist.', OCS_DOMAIN ),
			null
		);
		unset( $method, $args );
		return null;
	}

} // End class().
