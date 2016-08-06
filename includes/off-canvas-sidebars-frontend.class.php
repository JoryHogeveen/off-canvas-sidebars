<?php
/**
 * Off-Canvas Sidebars plugin front-end
 *
 * Front-end
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2.0
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars_Frontend {
	
	private $general_settings = array();
	private $version = false;
	
	function __construct() {
		$this->load_plugin_data();
		
		if ( $this->general_settings['enable_frontend'] == true ) { 
			$this->default_actions();
		}
				
		// Dúh..
		//add_action( 'admin_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
		add_action( 'wp_footer', array( $this, 'add_inline_scripts' ), 999999999 ); // enforce last addition
		add_action( 'wp_head', array( $this, 'add_inline_styles' ) );
	}
	
	/**
	 * Get plugin defaults
	 */
	function load_plugin_data() {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->general_settings = $off_canvas_sidebars->get_settings();
		$this->version = $off_canvas_sidebars->get_version();
	}

	/**
	 * Add default actions
	 *
	 * @since   0.1
	 * @return  void
	 */
	function default_actions() {
		$before_hook = str_replace( array(' '), '', $this->general_settings['website_before_hook'] );
		$after_hook = str_replace( array(' '), '', $this->general_settings['website_after_hook'] );
		if ( get_template() == 'genesis' ) {
			$before_hook = 'genesis_before';
			$after_hook = 'genesis_after';
		}
		if ( empty( $before_hook ) || empty( $after_hook ) ) {
			$before_hook = 'website_before';
			$after_hook = 'website_after';
		}
		add_action( $before_hook, array( $this, 'before_site' ), 0.000000001 ); // enforce first addition
		add_action( $after_hook, array( $this, 'after_site' ), 999999999 ); // enforce last addition
		
		/* EXPERIMENTAL */
		//add_action( 'wp_footer', array( $this, 'after_site' ), 0.000000001 ); // enforce first addition
		//add_action( 'wp_footer', array( $this, 'after_site_script' ), 99999 ); // enforce almnost last addition
	}

	/**
	 * before_site action hook
	 *
	 * @since   0.1
	 * @since   0.2  Add canvas attribute (Slidebars 2.0)
	 * @return  void
	 */
	function before_site() {
		echo '<div id="sb-site" canvas="container">';
	}
	
	/**
	 * after_site action hook
	 *
	 * @since   0.1
	 * @return  void
	 */
	function after_site() {
		if ( $this->general_settings['frontend_type'] != 'jquery' ) {
			echo '</div>'; // close #sb-site
		}
		foreach ($this->general_settings['sidebars'] as $sidebar => $sidebar_data) {
			if ($sidebar_data['enable'] == 1) {
				$this->add_slidebar($sidebar);
			}
		}
	}
	
	/**
	 * EXPERIMENTAL: Not used in this version
	 * 
	 * after_site action hook for scripts
	 *
	 * @since   0.1
	 * @return  void
	 */
	function after_site_script() {
		if ( ! is_admin() ) {
			?>
<script type="text/javascript">
	(function($) {
		$('div.sb-slidebar:first').prevAll().wrapAll('<div id="sb-site"></div>');       
	}) (jQuery);
</script>
			<?php
		}
	}
	
	/**
	 * Add the slidebar action hook
	 *
	 * @since   0.1
	 * @return  void
	 */
	function add_slidebar( $sidebar ) {
		$prefix = $this->general_settings['sidebars'][ $sidebar ];
		$classes = 'sb-slidebar sb-' . esc_attr( $sidebar );
		$attributes = '';
		echo '<div class="' . $classes . $this->get_sidebar_attributes( $sidebar, $prefix, 'class') . '" ' . $attributes . $this->get_sidebar_attributes( $sidebar, $prefix, 'other') . '>';
		if ( get_template() == 'genesis' ) {
			genesis_widget_area( 'off-canvas-'.$sidebar );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
		} else {
			dynamic_sidebar( 'off-canvas-'.$sidebar );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
		}
		echo '</div>';
	}

	/**
	 * Get sidebar attributes
	 *
	 * @since   0.1
	 * @return  void
	 */
	function get_sidebar_attributes( $sidebar, $prefix, $attr ) {
		$return = '';
		switch( $attr ) {
			case 'class':
				$return .= ' sb-width-' . esc_attr( $prefix['width'] );
				$return .= ' sb-style-' . esc_attr( $prefix['style'] );
			break;
			case 'other':
				if ( $prefix['width'] == 'custom' ) { $return .= ' data-sb-width="' . $prefix['width_input'] . $prefix['width_input_type'] . '"'; }

				// Slidebars 2.0
				$return .= ' off-canvas="sb-' . esc_attr( $sidebar ) . ' ' . esc_attr( $prefix['location'] ) . ' ' . esc_attr( $prefix['width'] ) . ' ' . esc_attr( $prefix['style'] ) . '"';
			break;
		}
		return $return;
	}

	/**
	 * Add nessesary scripts and styles
	 *
	 * @since   0.1
	 * @return  void
	 */
	function add_styles_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.css', array(), '2.0.2' );
		wp_enqueue_script( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.js', array( 'jquery' ), '2.0.2' );
		
		wp_enqueue_style( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'css/off-canvas-sidebars.css', array(), $this->version ); //'.$suffix.'

		if ( $this->general_settings['compatibility_position_fixed'] == true ) { 
			wp_enqueue_script( 'ocs-fixed-scrolltop', OCS_PLUGIN_URL . 'js/fixed-scrolltop.js', array( 'jquery' ), $this->version );
		}
	}
	
	/**
	 * Add nessesary inline scripts
	 *
	 * @since   0.1
	 * @return  void
	 */
	function add_inline_scripts() {
		if ( ! is_admin() ) {
			?>
<script type="text/javascript">
	(function($) {
		if ($('#sb-site').length > 0 && (typeof $.slidebars == 'function')) {
			var slidebars_controller = new slidebars();
			slidebars_controller.init({
				siteClose: <?php echo ($this->general_settings['site_close'])?'true':'false'; ?>,
				hideControlClasses: <?php echo ($this->general_settings['hide_control_classes'])?'true':'false'; ?>,
				scrollLock: <?php echo ($this->general_settings['scroll_lock'])?'true':'false'; ?>,
				disableOver: <?php echo ($this->general_settings['disable_over'])?$this->general_settings['disable_over']:'false'; ?>,
			});
		}
	}) (jQuery);
</script>
			<?php
		}
	}

	/**
	 * Add inline styles
	 *
	 * @since   0.1
	 * @return  void
	 */
	function add_inline_styles() {
		if ( ! is_admin() ) {
			?>
<style type="text/css">
<?php 
if ( $this->general_settings['background_color_type'] != '' ) {
	$bgcolor = '';
	if ( $this->general_settings['background_color_type'] == 'transparent' ) {
		$bgcolor = 'transparent';
	} else if ( $this->general_settings['background_color_type'] == 'color' && $this->general_settings['background_color'] != '' ) {
		$bgcolor = $this->general_settings['background_color'];
	}
?>
	#sb-site {background-color: <?php echo $bgcolor; ?>;}
<?php } ?>
<?php 
foreach ($this->general_settings['sidebars'] as $sidebar => $sidebar_data) {
	if ($sidebar_data['enable'] == 1 && $sidebar_data['background_color_type'] != '') {
		$bgcolor = '';
		if ( $sidebar_data['background_color_type'] == 'transparent' ) {
			$bgcolor = 'transparent';
		} else if ( $sidebar_data['background_color_type'] == 'color' && $sidebar_data['background_color'] != '' ) {
			$bgcolor = $sidebar_data['background_color'];
		}
?>
	.sb-slidebar.sb-<?php echo $sidebar; ?> {background-color: <?php echo $bgcolor; ?>;}
<?php }} ?>
</style>
			<?php
		}
	}

} // end class