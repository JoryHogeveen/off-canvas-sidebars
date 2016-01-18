<?php
/**
 * Off-Canvas Sidebars plugin front-end
 *
 * Front-end
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-slidebars
 * @version 0.1
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars_Frontend {
	
	private $general_settings = array();

	function __construct() {
		$this->load_plugin_data();
		
		if ( $this->general_settings['enable_frontend'] == true ) { 
			if ( get_template() == 'genesis' ) {
				$this->genesis_actions();
			} else {
				$this->default_actions();
			}
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
		global $off_canvas_sidebars;
		$this->general_settings = $off_canvas_sidebars->get_settings();
	}

	/**
	 * Add default actions
	 *
	 * @since   0.1
	 * @return	void
	 */
	function default_actions() {
		add_action( 'website_before', array( $this, 'before_site' ), 0.000000001 ); // enforce first addition
		add_action( 'website_after', array( $this, 'after_site' ), 999999999 ); // enforce last addition
		
		/* EXPERIMENTAL */
		//add_action( 'wp_footer', array( $this, 'after_site' ), 0.000000001 ); // enforce first addition
		//add_action( 'wp_footer', array( $this, 'after_site_script' ), 99999 ); // enforce almnost last addition
	}

	/**
	 * Add genesis actions
	 *
	 * @since   0.1
	 * @return	void
	 */
	function genesis_actions() {
		add_action( 'genesis_before', array( $this, 'before_site' ), 0.000000001 ); // enforce first addition
		add_action( 'genesis_after', array( $this, 'after_site' ), 999999999 ); // enforce last addition
	}
	
	/**
	 * before_site action hook
	 *
	 * @since   0.1
	 * @return	void
	 */
	function before_site() {
		echo '<div id="sb-site">';
	}
	
	/**
	 * after_site action hook
	 *
	 * @since   0.1
	 * @return	void
	 */
	function after_site() {
		if ( get_template() == 'genesis' ) {
			echo '</div>';
		} // close #sb-site
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
	 * @return	void
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
	 * @return	void
	 */
	function add_slidebar($sidebar) {
		$prefix = $this->general_settings['sidebars'][$sidebar];
		$classes = 'sb-slidebar sb-' . $sidebar;
		$attributes = '';		
		echo '<div class="' . $classes.$this->get_sidebar_attributes( $prefix, 'class') . '" ' . $attributes.$this->get_sidebar_attributes( $prefix, 'other') . '>';
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
	 * @return	void
	 */
	function get_sidebar_attributes( $prefix, $attr ) {
		$return = '';
		switch( $attr ) {
			case 'class':
				switch ( $prefix['width'] ) {
					case 'thin': $return .= ' sb-width-thin';
					break;
					case 'wide': $return .= ' sb-width-wide';
					break;
					case 'custom': $return .= ' sb-width-custom';
					break;
				}
				switch ( $prefix['style'] ) {
					case 'push': $return .= ' sb-style-push';
					break;
					case 'overlay': $return .= ' sb-style-overlay';
					break;
				}
			break;
			case 'other':
				if ($prefix['width'] == 'custom') { $return .= ' data-sb-width="'.$prefix['width_input'].$prefix['width_input_type'].'"'; }
			break;
		}
		return $return;
	}

	/**
	 * Add nessesary scripts and styles
	 *
	 * @since   0.1
	 * @return	void
	 */
	function add_styles_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.css', array(), '0.10.3' );
		wp_enqueue_script( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.js', array( 'jquery' ), '0.10.3' );
		//wp_enqueue_style( 'off_canvas_slidebars_style', plugin_dir_url( __FILE__ ) . 'style.css', array(), $this->version );
		//wp_enqueue_script( 'off_canvas_slidebars_script', plugin_dir_url( __FILE__ ) . 'script.js', array( 'jquery' ), $this->version );
	}
	
	/**
	 * Add nessesary inline scripts
	 *
	 * @since   0.1
	 * @return	void
	 */
	function add_inline_scripts() {
		if ( ! is_admin() ) {
			?>
<script type="text/javascript">
	(function($) {
		if ($('#sb-site').length > 0 && (typeof $.slidebars == 'function')) {
			$.slidebars({
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
	 * @return	void
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