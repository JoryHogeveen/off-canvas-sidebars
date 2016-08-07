<?php
/**
 * Off-Canvas Sidebars plugin front-end
 *
 * Front-end
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

class OCS_Off_Canvas_Sidebars_Frontend {
	
	private $general_settings = array();
	
	function __construct() {
		$this->load_plugin_data();
		
		if ( $this->general_settings['enable_frontend'] == true ) { 
			$this->default_actions();
		}
				
		// Dúh..
		//add_action( 'admin_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
		//add_action( 'wp_footer', array( $this, 'add_inline_scripts' ), 999999999 ); // enforce last addition
		add_action( 'wp_head', array( $this, 'add_inline_styles' ) );
	}
	
	/**
	 * Get plugin defaults
	 */
	function load_plugin_data() {
		$off_canvas_sidebars = Off_Canvas_Sidebars();
		$this->general_settings = $off_canvas_sidebars->get_settings();
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
		add_action( $before_hook, array( $this, 'before_site' ), 0 ); // enforce first addition
		add_action( $after_hook, array( $this, 'after_site' ), 999999999 ); // enforce last addition
		
		/* EXPERIMENTAL */
		//add_action( 'wp_footer', array( $this, 'after_site' ), 0 ); // enforce first addition
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
			if ( $sidebar_data['enable'] == 1 ) {
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
		$('div.sb-slidebar:first').prevAll().wrapAll('<div id="sb-site" canvas="container"></div>');       
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
		$data = $this->general_settings['sidebars'][ $sidebar ];
		$classes = 'sb-slidebar sb-' . esc_attr( $sidebar );
		$attributes = '';
		echo '<div id="sb-' . esc_attr( $sidebar ) . '" class="' . $classes . $this->get_sidebar_attributes( $sidebar, $data, 'class') . '" ' . $attributes . $this->get_sidebar_attributes( $sidebar, $data, 'other') . '>';
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
	function get_sidebar_attributes( $sidebar, $data, $attr ) {
		$return = '';
		switch( $attr ) {
			case 'class':
				$return .= ' sb-width-' . esc_attr( $data['width'] );
				$return .= ' sb-location-' . esc_attr( $data['location'] );
				$return .= ' sb-style-' . esc_attr( $data['style'] );
			break;
			case 'other':
				if ( $data['width'] == 'custom' ) { $return .= ' data-sb-width="' . $data['width_input'] . $data['width_input_type'] . '"'; }

				// Slidebars 2.0
				$return .= ' off-canvas="sb-' . esc_attr( $sidebar ) . ' ' . esc_attr( $data['location'] ) . ' ' . esc_attr( $data['style'] ) . '"';
				$return .= ' off-canvas-sidebar-id="' . esc_attr( $sidebar ) . '"';
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
		wp_enqueue_script( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.js', array( 'jquery' ), '2.0.2', true );
		
		wp_enqueue_style( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'css/off-canvas-sidebars.css', array(), OCS_PLUGIN_VERSION ); //'.$suffix.'
		wp_enqueue_script( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'js/off-canvas-sidebars.js', array( 'jquery', 'slidebars' ), OCS_PLUGIN_VERSION, true ); //'.$suffix.'
		wp_localize_script( 'off-canvas-sidebars', 'OCS_OFF_CANVAS_SIDEBARS', array(
			'site_close'           => ( $this->general_settings['site_close'] ) ? true : false,
			'disable_over'         => ( $this->general_settings['disable_over'] ) ? (int) $this->general_settings['disable_over'] : false,
			'hide_control_classes' => ( $this->general_settings['hide_control_classes'] ) ? true : false,
			'scroll_lock'          => ( $this->general_settings['scroll_lock'] ) ? true : false,
			'sidebars'             => $this->general_settings['sidebars']
		) );

		if ( $this->general_settings['compatibility_position_fixed'] == true ) { 
			wp_enqueue_script( 'ocs-fixed-scrolltop', OCS_PLUGIN_URL . 'js/fixed-scrolltop.js', array( 'jquery' ), OCS_PLUGIN_VERSION, true );
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
foreach ($this->general_settings['sidebars'] as $sidebar_id => $sidebar_data) {
	if ( $sidebar_data['enable'] == 1 ) {
		$atts = '';
		if ( $sidebar_data['background_color_type'] != '' ) {
			if ( $sidebar_data['background_color_type'] == 'transparent' ) {
				$atts .= 'background-color: transparent;';
			} else if ( $sidebar_data['background_color_type'] == 'color' && $sidebar_data['background_color'] != '' ) {
				$atts .= 'background-color: ' . $sidebar_data['background_color'] . ';';
			}
		}
		if ( $sidebar_data['width'] == 'custom' && $sidebar_data['width_input'] != '' ) {
			if ( in_array( $sidebar_data['location'], array( 'left', 'right' ) ) ) {
				$atts .= 'width: ' . (int) $sidebar_data['width_input'] . $sidebar_data['width_input_type'] . ';';
			}
			elseif ( in_array( $sidebar_data['location'], array( 'top', 'bottom' ) ) ) {
				$atts .= 'height: ' . (int) $sidebar_data['width_input'] . $sidebar_data['width_input_type'] . ';';
			}
		}
?>
	.sb-slidebar.sb-<?php echo $sidebar_id; ?> {<?php echo $atts; ?>}
<?php }} ?>
</style>
			<?php
		}
	}

} // end class