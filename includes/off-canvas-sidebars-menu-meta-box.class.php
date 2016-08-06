<?php
/**
 * Off-Canvas Sidebars menu meta box
 *
 * Menu Meta Box
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2.0
 *
 * Credits to the Polylang plugin
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars_Menu_Meta_box {
	
	private $general_key = '';
	private $plugin_key = '';
	private $plugin_tabs = array();
	private $general_settings = array();
	private $general_labels = array();
	private $link_classes = 'sb-button sb-toggle'; // sb-toggle-

	function __construct() {
		//add_action( 'admin_init', array( $this, 'load_settings' ) );
		add_action( 'init', array( $this, 'load_plugin_data' ) );
		add_action( 'admin_init', array( $this, 'add_meta_box' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 20 ); // after the customizer menus
	}

	/**
	 * Get plugin defaults
	 */
	function load_plugin_data() {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->general_settings = $off_canvas_sidebars->get_settings();
		$this->general_labels = $off_canvas_sidebars->get_general_labels();
		$this->general_key = $off_canvas_sidebars->get_general_key();
		$this->plugin_key = $off_canvas_sidebars->get_plugin_key();
	}
	
	function add_meta_box() {
		add_meta_box( $this->plugin_key.'-meta-box', __('Off-Canvas Control', 'off-canvas-sidebars'), array( $this, 'meta_box' ), 'nav-menus', 'side', 'low' );
	}
	
	function meta_box() {
		global $_nav_menu_placeholder, $nav_menu_selected_id, $off_canvas_sidebars;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		?>
		<div class="off-canvas-control-meta-box posttypediv" id="off-canvas-control-meta-box">
            <div id="tabs-panel-off-canvas-control" class="tabs-panel tabs-panel-active">
                <ul id="off-canvas-control" class="categorychecklist form-no-clear">
                <?php foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) { 
						if ($sidebar_data['enable'] == 1) {
				?>
                    <li>
                        <label class="menu-item-title">
                            <input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1"> <?php echo $this->general_labels['sidebars'][ $sidebar ]['label']; ?>
                        </label>
                        <input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
                        <input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="#off_canvas_control">
                        <input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->general_labels['sidebars'][ $sidebar ]['label']; ?>">
                        <input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="">
                    </li>
                <?php } } ?>
                <?php 
				if ( ! $off_canvas_sidebars->is_sidebar_enabled() ) {
					?>
                    <li><?php echo $this->general_labels['no_sidebars_available']; ?></li>
                    <?php
				}
				?>
                </ul>
            </div>
            <?php if ( $off_canvas_sidebars->is_sidebar_enabled() ) { ?>
            <p class="button-controls">
                <span class="list-controls">
                    <a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#off-canvas-control-meta-box" class="select-all"><?php echo __('Select All'); ?></a>
                </span>
                <span class="add-to-menu">
                    <input type="submit" class="button-secondary submit-add-to-menu right" value="<?php echo __('Add to Menu'); ?>" name="add-off-canvas-control-menu-item" id="submit-off-canvas-control-meta-box">
                    <span class="spinner"></span>
                </span>
            </p>
            <?php } ?>
		</div>
		<?php
	}

	/*
	 * prepares javascript to modify the off-canvas control menu item
	 *
	 * @since 0.1
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'nav-menus' != $screen->base )
			return;

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'off_canvas_control_nav_menu', OCS_PLUGIN_URL .'/js/nav-menu'.$suffix.'.js', array('jquery'), OCS_PLUGIN_VERSION );
		
		$data['strings'] = array(
			'show_icon' => __( 'Show icon', 'off-canvas-sidebars' ),
			'icon' => __( 'Icon classes', 'off-canvas-sidebars' ),
			'menu_item_type' => __( 'Off-Canvas Control', 'off-canvas-sidebars' ),
			'no_sidebars_available' => $this->general_labels['no_sidebars_available'],
		);
		$data['controls'] = array();
		foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) {
			if ( $sidebar_data['enable'] == 1 ) {
				$data['controls'][$sidebar] = $sidebar_data['label'];
			}
		}

		// get all language switcher menu items
		$items = get_posts(array(
			'numberposts' => -1,
			'nopaging'    => true,
			'post_type'   => 'nav_menu_item',
			'fields'      => 'ids',
			'meta_key'    => '_off_canvas_control_menu_item'
		));

		// the options values for the language switcher
		$data['val'] = array();
		foreach ($items as $item)
			$data['val'][$item] = get_post_meta( $item, '_off_canvas_control_menu_item', true );
		
		// send all these data to javascript
		wp_localize_script( 'off_canvas_control_nav_menu', 'off_canvas_control_data', $data );
	}

	/*
	 * save our menu item options
	 *
	 * @since 0.1
	 *
	 * @param 	int 	$menu_id not used
	 * @param 	int 	$menu_item_db_id
	 */
	public function wp_update_nav_menu_item( $menu_id = 0, $menu_item_db_id = 0 ) {
		global $off_canvas_sidebars;
		if (empty($_POST['menu-item-url'][$menu_item_db_id]) || $_POST['menu-item-url'][$menu_item_db_id] != '#off_canvas_control')
			return;
		
		// security check
		// as 'wp_update_nav_menu_item' can be called from outside WP admin
		if (current_user_can('edit_theme_options')) {
			check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );
			
			$available_controls = array();
			foreach ( $this->general_settings['sidebars'] as $sidebar => $sidebar_data ) {
				$available_controls[$sidebar] = $sidebar_data['label'];
			}
			// Autoselect control when adding a new item
			$default_control = '';
			if ( in_array( $_POST['menu-item-title'][$menu_item_db_id], $available_controls ) ) {
				$default_control = $off_canvas_sidebars->get_sidebar_key_by_label( $_POST['menu-item-title'][$menu_item_db_id] );
			}
			$options = array('off-canvas-control' => $default_control); // default values
			// our jQuery form has not been displayed
			if (empty($_POST['menu-item-off-canvas-control-detect'][$menu_item_db_id])) {
				if (!get_post_meta($menu_item_db_id, '_off_canvas_control_menu_item', true)) // our options were never saved
					update_post_meta($menu_item_db_id, '_off_canvas_control_menu_item', $options);
			}
			else {
				$options['off-canvas-control'] = '';
				// Of only one is available, allways select it	
				if ( ! empty($_POST['menu-item-off-canvas-control'][$menu_item_db_id]) && array_key_exists( $_POST['menu-item-off-canvas-control'][$menu_item_db_id], $available_controls ) ) {
					$options['off-canvas-control'] = strip_tags( stripslashes( $_POST['menu-item-off-canvas-control'][$menu_item_db_id] ) );
				}
				// Of only one sidebar is available, allways select its control
				/*if (count($available_controls) == 1) {
					$options['off-canvas-control'] = $off_canvas_sidebars->get_sidebar_key_by_label( $available_controls[0] );
				}*/
				update_post_meta($menu_item_db_id, '_off_canvas_control_menu_item', $options); // allow us to easily identify our nav menu item
			}
		}
	}

	/*
	 * splits the one item of backend in several items on frontend
	 * take care to menu_order as it is used later in wp_nav_menu
	 *
	 * @since 0.1
	 *
	 * @param 	array 	$items menu items
	 * @return 	array 	modified items
	 */
	public function wp_get_nav_menu_items($items) {
		if (doing_action('customize_register') || is_admin()) { // needed since WP 4.3, doing_action available since WP 3.9
			return $items;
		}
		foreach ($items as $key => $item) {
			if ($options = get_post_meta($item->ID, '_off_canvas_control_menu_item', true)) {
				$item->url = '';
				if (isset($this->general_settings['sidebars'][$options['off-canvas-control']]) && $this->general_settings['sidebars'][$options['off-canvas-control']]['enable'] == 1) {
					//$item->title = $options['show_flags'] && $options['show_names'] ? $lang['flag'].'&nbsp;'.esc_html($lang['name']) : ($options['show_flags'] ? $lang['flag'] : esc_html($lang['name']));
					$link_classes = $this->link_classes;
					if ($options['off-canvas-control'] != '') {
						$link_classes .= ' sb-toggle-'.$options['off-canvas-control'];
					}
					if (!is_array($item->classes)){
						$item->classes = explode(' ', $item->classes);
					}
					$item->classes = array_merge($item->classes, explode(' ', $link_classes));
				}
			}
		}
		return $items;
	}

} // end class