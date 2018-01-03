<?php
/**
 * Off-Canvas Sidebars - Class Menu_Meta_Box
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars menu meta box
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1
 * @version 0.5
 *
 * Credits to the Polylang plugin.
 */
final class OCS_Off_Canvas_Sidebars_Menu_Meta_Box extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Menu_Meta_box
	 * @since  0.3
	 */
	protected static $_instance = null;

	protected $meta_key = '_off_canvas_control_menu_item';

	private $general_key = '';
	private $plugin_key = '';
	//private $plugin_tabs = array();
	private $settings = array();
	private $general_labels = array();
	private $link_classes = array(
		'-trigger',
		'-toggle',
		// -toggle-
	);

	/**
	 * Class constructor.
	 * @since  0.3  private constructor
	 * @access private
	 */
	private function __construct() {
		//add_action( 'admin_init', array( $this, 'load_settings' ) );
		add_action( 'init', array( $this, 'load_plugin_data' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 20 ); // after the customizer menus
	}

	/**
	 * Get plugin data.
	 * @since 0.1
	 */
	public function load_plugin_data() {
		$off_canvas_sidebars  = off_canvas_sidebars();
		$this->settings       = $off_canvas_sidebars->get_settings();
		$this->general_labels = $off_canvas_sidebars->get_general_labels();
		$this->general_key    = $off_canvas_sidebars->get_general_key();
		$this->plugin_key     = $off_canvas_sidebars->get_plugin_key();

		foreach ( $this->link_classes as $key => $class ) {
			$this->link_classes[ $key ] = $this->settings['css_prefix'] . $class;
		}
	}

	/**
	 * Add the meta box to the menu options.
	 * @since 0.1
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->plugin_key . '-meta-box',
			esc_html__( 'Off-Canvas Control', OCS_DOMAIN ),
			array( $this, 'meta_box' ),
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Meta box callback.
	 * @since 0.1
	 */
	function meta_box() {
		global $_nav_menu_placeholder; //, $nav_menu_selected_id;
		$off_canvas_sidebars = off_canvas_sidebars();
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		?>
		<div class="off-canvas-control-meta-box posttypediv" id="off-canvas-control-meta-box">
			<div id="tabs-panel-off-canvas-control" class="tabs-panel tabs-panel-active">
				<ul id="off-canvas-control" class="categorychecklist form-no-clear">
			<?php
				foreach ( $this->settings['sidebars'] as $sidebar => $sidebar_data ) {
					if ( $sidebar_data['enable'] ) {
			?>
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php echo $sidebar_data['label']; ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#off_canvas_control">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $sidebar_data['label']; ?>">
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="">
					</li>
			<?php
					}
				}
				if ( ! $off_canvas_sidebars->is_sidebar_enabled() ) {
					echo '<li>' . $this->general_labels['no_sidebars_available'] . '</li>';
				}
			?>
				</ul>
			</div>
			<?php if ( $off_canvas_sidebars->is_sidebar_enabled() ) { ?>
			<p class="button-controls">
				<span class="list-controls">
					<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#off-canvas-control-meta-box" class="select-all"><?php echo __( 'Select All' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php echo __( 'Add to Menu' ); ?>" name="add-off-canvas-control-menu-item" id="submit-off-canvas-control-meta-box">
					<span class="spinner"></span>
				</span>
			</p>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * prepares javascript to modify the off-canvas control menu item.
	 * @since 0.1
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'nav-menus' !== $screen->base )
			return;

		$suffix = ''; //defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : OCS_PLUGIN_VERSION;
		wp_enqueue_script( 'off_canvas_control_nav_menu', OCS_PLUGIN_URL . '/js/nav-menu' . $suffix . '.js', array( 'jquery' ), $version );

		$data['strings'] = array(
			'show_icon'             => __( 'Show icon', OCS_DOMAIN ),
			'icon'                  => __( 'Icon classes', OCS_DOMAIN ),
			'menu_item_type'        => __( 'Off-Canvas Control', OCS_DOMAIN ),
			'no_sidebars_available' => $this->general_labels['no_sidebars_available'],
		);
		$data['controls'] = array();
		foreach ( $this->settings['sidebars'] as $sidebar => $sidebar_data ) {
			if ( $sidebar_data['enable'] ) {
				$data['controls'][ $sidebar ] = $sidebar_data['label'];
			}
		}

		// Get all language switcher menu items.
		$items = get_posts( array(
			'numberposts' => -1,
			'nopaging'    => true,
			'post_type'   => 'nav_menu_item',
			'fields'      => 'ids',
			'meta_key'    => $this->meta_key,
		) );

		// The options values for the triggers.
		$data['val'] = array();
		foreach ( $items as $item )
			$data['val'][ $item ] = get_post_meta( $item, $this->meta_key, true );

		// Send all these data to javascript.
		wp_localize_script( 'off_canvas_control_nav_menu', 'ocsNavControl', $data );
	}

	/**
	 * save our menu item options.
	 *
	 * @since   0.1
	 * @param   int  $menu_id not used
	 * @param   int  $menu_item_db_id
	 */
	public function wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0 ) {
		$off_canvas_sidebars = off_canvas_sidebars();

		// @codingStandardsIgnoreLine
		if ( empty( $_POST['menu-item-url'][ $menu_item_db_id ] ) || '#off_canvas_control' !== $_POST['menu-item-url'][ $menu_item_db_id ] )
			return;

		// Security check since 'wp_update_nav_menu_item' can be called from outside WP admin.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		$available_controls = array();
		foreach ( $this->settings['sidebars'] as $sidebar => $sidebar_data ) {
			$available_controls[ $sidebar ] = $sidebar_data['label'];
		}

		// Auto select control when adding a new item.
		$default_control = '';
		if ( in_array( $_POST['menu-item-title'][ $menu_item_db_id ], $available_controls, true ) ) {
			$default_control = $off_canvas_sidebars->get_sidebar_key_by_label( $_POST['menu-item-title'][ $menu_item_db_id ] );
		}
		// Default values.
		$options = array( 'off-canvas-control' => $default_control );

		// Our jQuery form has not been displayed.
		if ( empty( $_POST['menu-item-off-canvas-control-detect'][ $menu_item_db_id ] ) ) {

			// Our options were never saved.
			if ( ! get_post_meta( $menu_item_db_id, $this->meta_key, true ) ) {
				update_post_meta( $menu_item_db_id, $this->meta_key, $options );
			}

		} else {

			$options['off-canvas-control'] = '';

			// If only one is available, always select it.
			if ( ! empty( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ] ) &&
				 array_key_exists( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ], $available_controls )
			) {
				$options['off-canvas-control'] = strip_tags( stripslashes( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ] ) );
			}

			// Allow us to easily identify our nav menu item.
			update_post_meta( $menu_item_db_id, $this->meta_key, $options );
		}
	}

	/**
	 * Splits the one item of backend in several items on frontend.
	 * Takes care to menu_order as it is used later in wp_nav_menu.
	 *
	 * @since   0.1
	 * @param   array  $items menu items
	 * @return  array  modified items
	 */
	public function wp_get_nav_menu_items( $items ) {

		if ( function_exists( 'doing_action' ) && doing_action( 'customize_register' ) || is_admin() ) {
			// Needed since WP 4.3, doing_action available since WP 3.9.
			return $items;
		}

		foreach ( $items as $key => $item ) {
			$options = get_post_meta( $item->ID, $this->meta_key, true );
			if ( ! $options ) {
				continue;
			}
			$item->url = '';
			if ( isset( $this->settings['sidebars'][ $options['off-canvas-control'] ] ) &&
				 $this->settings['sidebars'][ $options['off-canvas-control'] ]['enable']
			) {

				$link_classes = $this->link_classes;
				if ( ! is_array( $link_classes ) ) {
					$link_classes = explode( ' ', (string) $link_classes );
				}

				if ( ! empty( $options['off-canvas-control'] ) ) {
					$link_classes[] = $this->settings['css_prefix'] . '-toggle-' . $options['off-canvas-control'];
				}

				if ( ! is_array( $item->classes ) ) {
					$item->classes = explode( ' ', $item->classes );
				}

				$item->classes = array_merge( $item->classes, $link_classes );
			}
		}

		return $items;
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Menu_Meta_box
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // end class
