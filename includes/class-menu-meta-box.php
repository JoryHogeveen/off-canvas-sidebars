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
 * @since   0.1.0
 * @version 0.5.5
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 *
 * Credits to the Polylang plugin.
 */
final class OCS_Off_Canvas_Sidebars_Menu_Meta_Box extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Menu_Meta_box
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	/**
	 * @var string
	 */
	protected $meta_key = '_off_canvas_control_menu_item';

	/**
	 * Class constructor.
	 * @since  0.3.0  Private constructor.
	 * @access private
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'add_meta_box' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 20 ); // after the customizer menus
	}

	/**
	 * Add the meta box to the menu options.
	 * @since  0.1.0
	 */
	public function add_meta_box() {
		add_meta_box(
			off_canvas_sidebars()->get_plugin_key() . '-meta-box',
			esc_html__( 'Off-Canvas Trigger', OCS_DOMAIN ),
			array( $this, 'meta_box' ),
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Meta box callback.
	 * @since  0.1.0
	 */
	public function meta_box() {
		global $_nav_menu_placeholder; //, $nav_menu_selected_id;
		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? $_nav_menu_placeholder - 1 : -1;

		$sidebars = off_canvas_sidebars_settings()->get_enabled_sidebars();
		?>
		<div class="off-canvas-control-meta-box posttypediv" id="off-canvas-control-meta-box">
			<div id="tabs-panel-off-canvas-control" class="tabs-panel tabs-panel-active">
				<ul id="off-canvas-control" class="categorychecklist form-no-clear">
			<?php
				if ( $sidebars ) {
					foreach ( $sidebars as $sidebar => $sidebar_data ) {
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
				else {
					echo '<li>' . off_canvas_sidebars()->get_general_labels( 'no_sidebars_available' ) . '</li>';
				}
			?>
				</ul>
			</div>
			<?php
			if ( $sidebars ) {

				$select_all = add_query_arg( array(
					//'ocs-tab'  => 'all',
					'selectall' => '1',
				) );
				?>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo esc_attr( $select_all ); ?>#off-canvas-control-meta-box" class="select-all"><?php esc_html_e( 'Select All' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-off-canvas-control-menu-item" id="submit-off-canvas-control-meta-box">
					<span class="spinner"></span>
				</span>
			</p>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * prepares javascript to modify the off-canvas control menu item.
	 * @since  0.1.0
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'nav-menus' !== $screen->base ) {
			return;
		}

		$suffix  = ''; //defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : OCS_PLUGIN_VERSION;
		wp_enqueue_script( 'off_canvas_control_nav_menu', OCS_PLUGIN_URL . '/js/nav-menu' . $suffix . '.js', array( 'jquery' ), $version, true );

		$data['controls'] = array();
		$data['strings']  = array(
			'show_icon'             => __( 'Show icon', OCS_DOMAIN ),
			'icon'                  => __( 'Icon classes', OCS_DOMAIN ),
			'menu_item_type'        => __( 'Off-Canvas Trigger', OCS_DOMAIN ),
			'no_sidebars_available' => off_canvas_sidebars()->get_general_labels( 'no_sidebars_available' ),
		);
		foreach ( off_canvas_sidebars_settings()->get_enabled_sidebars() as $sidebar => $sidebar_data ) {
			$data['controls'][ $sidebar ] = $sidebar_data['label'];
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
		foreach ( $items as $item ) {
			$data['val'][ $item ] = get_post_meta( $item, $this->meta_key, true );
		}

		// Send all these data to javascript.
		wp_localize_script( 'off_canvas_control_nav_menu', 'ocsNavControl', $data );
	}

	/**
	 * save our menu item options.
	 *
	 * @since   0.1.0
	 * @param   int  $menu_id          (not used)
	 * @param   int  $menu_item_db_id
	 */
	public function wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0 ) {
		$ocs = off_canvas_sidebars();

		// @codingStandardsIgnoreLine
		if ( empty( $_POST['menu-item-url'][ $menu_item_db_id ] ) || '#off_canvas_control' !== $_POST['menu-item-url'][ $menu_item_db_id ] ) {
			return;
		}

		// Security check since 'wp_update_nav_menu_item' can be called from outside WP admin.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		$available_controls = array();
		foreach ( $ocs->get_sidebars() as $sidebar => $sidebar_data ) {
			$available_controls[ $sidebar ] = $sidebar_data['label'];
		}

		// Auto select control when adding a new item.
		$default_control = '';
		if ( in_array( $_POST['menu-item-title'][ $menu_item_db_id ], $available_controls, true ) ) {
			$default_control = $ocs->get_sidebar_key_by_label( $_POST['menu-item-title'][ $menu_item_db_id ] );
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
			if (
				! empty( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ] )
				&& array_key_exists( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ], $available_controls )
			) {
				$options['off-canvas-control'] = wp_strip_all_tags( stripslashes( $_POST['menu-item-off-canvas-control'][ $menu_item_db_id ] ) );
			}

			// Allow us to easily identify our nav menu item.
			update_post_meta( $menu_item_db_id, $this->meta_key, $options );
		}
	}

	/**
	 * Splits the one item of backend in several items on frontend.
	 * Takes care to menu_order as it is used later in wp_nav_menu.
	 *
	 * @since   0.1.0
	 * @param   array  $items  Menu items.
	 * @return  array  Modified items.
	 */
	public function wp_get_nav_menu_items( $items ) {

		if ( is_admin() || doing_action( 'customize_register' ) ) {
			// Needed since WP 4.3, doing_action available since WP 3.9.
			return $items;
		}

		foreach ( $items as $key => $item ) {
			$options = get_post_meta( $item->ID, $this->meta_key, true );
			if ( ! $options || empty( $options['off-canvas-control'] ) ) {
				continue;
			}
			$sidebar_id = $options['off-canvas-control'];

			if ( off_canvas_sidebars_frontend()->is_sidebar_enabled( $sidebar_id ) ) {
				$item->url = '';

				$link_classes = OCS_Off_Canvas_Sidebars_Control_Trigger::get_trigger_classes( $sidebar_id );

				if ( ! is_array( $item->classes ) ) {
					$item->classes = explode( ' ', $item->classes );
				}

				$item->classes = array_merge( $item->classes, $link_classes );
			}
			elseif ( off_canvas_sidebars_settings()->get_sidebar_settings( $sidebar_id, 'hide_control_classes' ) ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Menu_Meta_box
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
