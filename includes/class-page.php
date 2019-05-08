<?php
/**
 * Off-Canvas Sidebars - Class Page
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin page
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.5.0  Refactored from single settings class.
 * @version 0.5.4
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Page extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Page
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	protected $general_key    = '';
	protected $plugin_key     = '';
	protected $general_labels = array();
	protected $capability     = 'edit_theme_options';
	protected $request_tab    = '';
	protected $tab            = 'ocs-settings';
	protected $tabs           = array();

	/**
	 * @since   0.1.0
	 * @since   0.3.0  Private constructor.
	 * @access  private
	 */
	private function __construct() {
		// @codingStandardsIgnoreStart
		if ( isset( $_POST['ocs_tab'] ) ) {
			$this->set_request_tab( $_POST['ocs_tab'] );
		}
		if ( isset( $_GET['tab'] ) ) {
			$this->set_current_tab( $_GET['tab'] );
		}
		// @codingStandardsIgnoreEnd
		$this->plugin_key = off_canvas_sidebars()->get_plugin_key();
		$this->register_tabs();

		add_action( 'admin_init', array( $this, 'load_plugin_data' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ), 15 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
	}

	/**
	 * Get plugin data.
	 * @since   0.1.0
	 */
	public function load_plugin_data() {
		$off_canvas_sidebars  = off_canvas_sidebars();
		$this->general_labels = $off_canvas_sidebars->get_general_labels();
		$this->general_key    = $off_canvas_sidebars->get_general_key();

		/**
		 * Change the capability for the OCS settings.
		 * @since  0.4.0
		 * @param  string
		 * @return string
		 */
		$this->capability = apply_filters( 'ocs_settings_capability', $this->capability );
	}

	/**
	 * Enqueue our styles and scripts only when it's our page.
	 * @since   0.1.0
	 * @param   string  $hook
	 */
	public function enqueue_styles_scripts( $hook ) {
		if ( 'appearance_page_' . $this->plugin_key !== $hook ) {
			return;
		}
		// @todo Minified versions.

		wp_enqueue_style(
			'off-canvas-sidebars-admin',
			OCS_PLUGIN_URL . 'css/off-canvas-sidebars-admin.css',
			array( 'wp-color-picker' ),
			OCS_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'off-canvas-sidebars-settings',
			OCS_PLUGIN_URL . 'js/off-canvas-sidebars-settings.js',
			array( 'jquery', 'postbox', 'wp-color-picker' ),
			OCS_PLUGIN_VERSION,
			true // load in footer.
		);

		wp_localize_script(
			'off-canvas-sidebars-settings',
			'ocsOffCanvasSidebarsSettings',
			array(
				'general_key'               => $this->general_key,
				'plugin_key'                => $this->plugin_key,
				'css_prefix'                => $this->get_settings( 'css_prefix' ),
				'__required_fields_not_set' => __( 'Some required fields are not set!', OCS_DOMAIN ),
			)
		);

	}

	/**
	 * Create admin page under the appearance menu.
	 * @since   0.1.0
	 */
	public function add_admin_menus() {
		add_theme_page(
			esc_attr__( 'Off-Canvas Sidebars', OCS_DOMAIN ),
			esc_attr__( 'Off-Canvas Sidebars', OCS_DOMAIN ),
			$this->capability,
			$this->plugin_key,
			array( $this, 'options_page' )
		);
	}

	/**
	 * Register the page tabs.
	 * @since   0.5.0
	 */
	private function register_tabs() {

		include_once OCS_PLUGIN_DIR . 'includes/class-tab.php';
		include_once OCS_PLUGIN_DIR . 'includes/class-tab-general.php';
		include_once OCS_PLUGIN_DIR . 'includes/class-tab-sidebars.php';
		include_once OCS_PLUGIN_DIR . 'includes/class-tab-shortcode.php';
		include_once OCS_PLUGIN_DIR . 'includes/class-tab-importexport.php';
		OCS_Off_Canvas_Sidebars_Tab_General::get_instance();
		OCS_Off_Canvas_Sidebars_Tab_Sidebars::get_instance();
		OCS_Off_Canvas_Sidebars_Tab_Shortcode::get_instance();
		OCS_Off_Canvas_Sidebars_Tab_Importexport::get_instance();

		/**
		 * Register the tabs.
		 * @since   0.5.0
		 * @param   array  $tabs  Tab instances.
		 * @return  array  Array of tab instanced. Array key needs to be the tab ID.
		 */
		$this->tabs = apply_filters( 'ocs_page_register_tabs', $this->tabs );
	}

	/**
	 * Get a tab instance.
	 * @since   0.5.1
	 * @param   string  $tab
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_tab( $tab ) {
		return ( isset( $this->tabs[ $tab ] ) ) ? $this->tabs[ $tab ] : null;
	}

	/**
	 * Get the current tab instance.
	 * @since   0.5.0
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_current_tab() {
		return $this->get_tab( $this->tab );
	}

	/**
	 * Get the tab instance for the form request handler.
	 * @since   0.5.0
	 * @return  \OCS_Off_Canvas_Sidebars_Tab
	 */
	public function get_request_tab() {
		return $this->get_tab( $this->request_tab );
	}

	/**
	 * Set the current tab.
	 * @since   0.5.0
	 * @param   string  $tab
	 */
	public function set_current_tab( $tab ) {
		$this->tab = (string) $tab;
	}

	/**
	 * Set the tab for the form request handler.
	 * @since   0.5.0
	 * @param   string  $tab
	 */
	public function set_request_tab( $tab ) {
		$this->request_tab = (string) $tab;
	}

	/**
	 * Register our settings.
	 * @since   0.1.0
	 */
	public function register_settings() {

		$tab = $this->get_current_tab();
		if ( $tab ) {
			// @todo Enhance this...
			register_setting( $this->request_tab, $this->general_key, array( $tab, 'validate_form' ) );
		}

		foreach ( $this->tabs as $tab_key => $tab ) {
			$tab->register_settings();
		}

		do_action( 'off_canvas_sidebar_settings' );

		if ( $this->get_current_tab() ) {
			$this->get_current_tab()->init();
		}
	}

	/**
	 * Plugin Options page rendering goes here, checks for active tab and replaces key with the related settings key.
	 * Uses the plugin_options_tabs() method to render the tabs.
	 * @since   0.1.0
	 */
	public function options_page() {
		$do_submit = ( apply_filters( 'ocs_page_form_do_submit', true ) ) ? true : false;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Off-Canvas Sidebars', OCS_DOMAIN ); ?></h1>
		<?php $this->plugin_options_tabs(); ?>
		<div class="<?php echo $this->plugin_key; ?> container">

			<?php $form_action = apply_filters( 'ocs_page_form_action', 'options.php' ); ?>
			<form id="<?php echo $this->general_key; ?>" method="post" action="<?php echo $form_action; ?>" enctype="multipart/form-data">

				<?php settings_errors(); ?>

				<?php if ( $do_submit ) { ?>
				<p class="alignright"><?php submit_button( null, 'primary', 'submit', false ); ?></p>
				<?php } ?>

				<input id="ocs_tab" type="hidden" name="ocs_tab" value="<?php echo $this->tab; ?>" />

				<?php do_action( 'ocs_page_form_before' ); ?>

				<div class="metabox-holder">
				<div class="postbox-container">
				<div id="main-sortables" class="meta-box-sortables ui-sortable">

				<?php
				if ( apply_filters( 'ocs_page_form_do_settings_fields', true ) ) {
					settings_fields( $this->tab );
				}
				if ( apply_filters( 'ocs_page_form_do_sections', true ) ) {
					$this->do_settings_sections( $this->tab );
				}
				do_action( 'ocs_page_form' );
				?>

				</div>
				</div>
				</div>

				<?php do_action( 'ocs_page_form_after' ); ?>

				<?php if ( $do_submit ) submit_button(); ?>

			</form>

			<?php $this->do_page_sidebar(); ?>

		</div>
	</div>
	<?php
		//add_action( 'in_admin_footer', array( 'OCS_Lib', 'admin_footer' ) );
	}

	/**
	 * Render the OCS sidebar.
	 * @since   0.5.0
	 */
	protected function do_page_sidebar() {
		?>
	<div class="ocs-sidebar">
		<div class="ocs-credits stuffbox">
			<h3 class="hndle"><?php echo esc_html__( 'Off-Canvas Sidebars', OCS_DOMAIN ) . ' ' . OCS_PLUGIN_VERSION; ?></h3>
			<div class="inside">
				<h4 class="inner"><?php esc_html_e( 'Need support?', OCS_DOMAIN ); ?></h4>
				<p class="inner">
					<?php
					echo sprintf(
						// Translators: %1$s and %2$s stands for a URL.
						__( 'If you are having problems with this plugin, checkout plugin <a href="%1$s" target="_blank">Documentation</a> or talk about them in the <a href="%2$s" target="_blank">Support forum</a>', OCS_DOMAIN ),
						off_canvas_sidebars()->get_links( 'docs', 'url' ),
						off_canvas_sidebars()->get_links( 'support', 'url' )
					);
					?>
				</p>
				<hr />
				<h4 class="inner"><?php esc_html_e( 'Do you like this plugin?', OCS_DOMAIN ); ?></h4>
				<a class="inner" href="<?php echo off_canvas_sidebars()->get_links( 'donate', 'url' ); ?>" target="_blank">
					<img alt="PayPal - The safer, easier way to pay online!" border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif">
				</a>
				<p class="inner">
					<a href="<?php echo off_canvas_sidebars()->get_links( 'review', 'url' ); ?>" target="_blank"><span class="icon dashicons dashicons-star-filled"></span><?php esc_html_e( 'Give 5 stars on WordPress.org!', OCS_DOMAIN ); ?></a><br />
					<a href="https://wordpress.org/plugins/off-canvas-sidebars/" target="_blank"><span class="icon dashicons dashicons-testimonial"></span><?php esc_html_e( 'Blog about it & link to the plugin page', OCS_DOMAIN ); ?></a><br />
					<a href="<?php echo off_canvas_sidebars()->get_links( 'plugins', 'url' ); ?>" target="_blank"><span class="icon dashicons dashicons-admin-plugins"></span><?php esc_html_e( 'Check out my other WordPress plugins', OCS_DOMAIN ); ?></a><br />
				</p>
				<hr />
				<h4 class="inner"><?php esc_html_e( 'Want to help?', OCS_DOMAIN ); ?></h4>
				<p class="inner">
					<a href="<?php echo off_canvas_sidebars()->get_links( 'github', 'url' ); ?>" target="_blank"><span class="icon dashicons dashicons-editor-code"></span><?php esc_html_e( 'Follow and/or contribute on GitHub', OCS_DOMAIN ); ?></a><br />
					<a href="<?php echo off_canvas_sidebars()->get_links( 'translate', 'url' ); ?>" target="_blank"><span class="icon dashicons dashicons-translation"></span><?php esc_html_e( 'Help translating this plugin!', OCS_DOMAIN ); ?></a>
				</p>
				<hr />
				<p class="ocs-link inner"><?php esc_html_e( 'Created by', OCS_DOMAIN ); ?>: <a href="https://profiles.wordpress.org/keraweb/" target="_blank" title="Keraweb - Jory Hogeveen"><!--<img src="' . plugins_url( '../images/logo-keraweb.png', __FILE__ ) . '" title="Keraweb - Jory Hogeveen" alt="Keraweb - Jory Hogeveen" />-->Keraweb (Jory Hogeveen)</a></p>
			</div>
		</div>
	</div>
		<?php
	}

	/**
	 * This function is similar to the function in the Settings API, only the output HTML is changed.
	 * Print out the settings fields for a particular settings section.
	 *
	 * @since   0.1.0
	 *
	 * @global  array  $wp_settings_sections  Array of settings sections.
	 * @global  array  $wp_settings_fields    Array of settings fields and their pages/sections.
	 *
	 * @param   string  $page     Slug title of the admin page who's settings fields you want to show.
	 * param    string  $section  Slug title of the settings section who's fields you want to show.
	 */
	protected function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			$box_classes = apply_filters( 'ocs_page_form_section_box_classes', 'postbox ' . $section['id'], $section, $page );

			echo '<div id="' . esc_attr( $section['id'] ) . '" class="' . esc_attr( $box_classes ) . '">';
			echo '<button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">'
				 . esc_html__( 'Toggle panel', OCS_DOMAIN ) . '</span><span class="toggle-indicator" aria-hidden="true"></span></button>';

			if ( $section['title'] ) {
				echo '<h3 class="hndle"><span>' . $section['title'] . '</span></h3>';
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			echo '<div class="inside">';

			do_action( 'ocs_page_form_section_before', $section, $page );

			if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				echo '<table class="form-table">';
				do_action( 'ocs_page_form_section_table_before', $section, $page );
				do_settings_fields( $page, $section['id'] );
				do_action( 'ocs_page_form_section_table_after', $section, $page );
				echo '</table>';
			}

			do_action( 'ocs_page_form_section_after', $section, $page );

			echo '</div></div>';
		}
	}

	/**
	 * Renders our tabs in the plugin options page, walks through the object's tabs array and prints them one by one.
	 * Provides the heading for the options_page() method.
	 * @since   0.1.0
	 */
	public function plugin_options_tabs() {
		echo '<h1 class="nav-tab-wrapper">';
		foreach ( $this->tabs as $tab_key => $tab ) {
			$active = $this->tab === $tab_key ? ' nav-tab-active' : '';
			echo '<a class="nav-tab' . esc_attr( $active ) . '" href="?page=' . esc_attr( $this->plugin_key ) . '&amp;tab=' . esc_attr( $tab_key ) . '">' . esc_html( $tab->name ) . '</a>';
		}
		echo '</h1>';
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Page
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
