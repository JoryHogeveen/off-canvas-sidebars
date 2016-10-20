<?php
/**
 * Off-Canvas Sidebars plugin front-end
 *
 * Front-end
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3.1
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

final class OCS_Off_Canvas_Sidebars_Frontend
{
	/**
	 * The single instance of the class.
	 *
	 * @var    OCS_Off_Canvas_Sidebars_Frontend
	 * @since  0.3
	 */
	protected static $_instance = null;

	private $settings = array();

	/**
	 * @since  0.3  private constructor
	 * @access private
	 */
	private function __construct() {
		$this->load_plugin_data();

		if ( $this->settings['enable_frontend'] == true ) {
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
	 * @since   0.1
	 */
	private function load_plugin_data() {
		$this->settings = Off_Canvas_Sidebars()->get_settings();
	}

	/**
	 * Add default actions
	 *
	 * @since   0.1
	 * @return  void
	 */
	private function default_actions() {

		$before_hook = trim( $this->settings['website_before_hook'] );
		$after_hook  = trim( $this->settings['website_after_hook'] );

		if ( get_template() == 'genesis' ) {
			$before_hook = 'genesis_before';
			$after_hook  = 'genesis_after';
		}
		if ( empty( $before_hook ) ) {
			$before_hook = 'website_before';
		}
		if ( empty( $after_hook ) ) {
			$after_hook  = 'website_after';
		}

		$before_hook = trim( apply_filters( 'ocs_website_before_hook', $before_hook ) );
		$after_hook  = trim( apply_filters( 'ocs_website_after_hook', $after_hook ) );

		add_action( $before_hook, array( $this, 'before_site' ), 5 ); // enforce early addition
		add_action( $after_hook,  array( $this, 'after_site' ), 999999999 ); // enforce last addition
		add_action( $after_hook,  array( $this, 'do_sidebars' ), 999999999 ); // enforce last addition

		/* EXPERIMENTAL */
		//add_action( 'wp_footer', array( $this, 'after_site' ), 0 ); // enforce first addition
		//add_action( 'wp_footer', array( $this, 'after_site_script' ), 99999 ); // enforce almnost last addition
	}

	/**
	 * before_site action hook
	 *
	 * @since   0.1
	 * @since   0.2    Add canvas attribute (Slidebars 2.0)
	 * @since   0.2.1  Add actions
	 * @return  void
	 */
	function before_site() {

		// Add content before the site container
		do_action( 'ocs_container_before' );

		$atts = array(
			'ocs-site_close'           => ( $this->settings['site_close'] ) ? true : false,
			'ocs-disable_over'         => ( $this->settings['disable_over'] ) ? (int) $this->settings['disable_over'] : false,
			'ocs-hide_control_classes' => ( $this->settings['hide_control_classes'] ) ? true : false,
			'ocs-scroll_lock'          => ( $this->settings['scroll_lock'] ) ? true : false,
		);

		foreach ( $atts as $name => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}
			$atts[ $name ] = $name . '="' . $value . '"';
		}

		echo '<div id="' . $this->settings['css_prefix'] . '-site" canvas="container" ' . implode( ' ', $atts ) . '>';

		// Add content before other content in the site container
		do_action( 'ocs_container_inner_before' );
	}

	/**
	 * after_site action hook
	 *
	 * @since   0.1
	 * @since   0.2.1  Add actions
	 * @return  void
	 */
	function after_site() {

		// Add content after other content in the site container
		do_action( 'ocs_container_inner_after' );

		if ( $this->settings['frontend_type'] != 'jquery' ) {
			echo '</div>'; // close #ocs-site
		}
		// Add content after the site container
		do_action( 'ocs_container_after' );
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
		$('div.<?php echo $this->settings['css_prefix']; ?>-slidebar:first').prevAll().wrapAll('<div id="<?php echo $this->settings['css_prefix']; ?> -site" canvas="container"></div>');
	}) (jQuery);
</script>
			<?php
		}
	}

	/**
	 * Echo all sidebars
	 *
	 * @since   0.3
	 * @return  void
	 * @access  public
	 */
	function do_sidebars() {
		if ( ! empty( $this->settings['sidebars'] ) ) {
			foreach ( $this->settings['sidebars'] as $sidebar_id => $sidebar_data ) {
				$this->do_sidebar( $sidebar_id );
			}
		}
	}

	/**
	 * Echos a sidebar
	 *
	 * @since   0.1
	 * @param   string  $sidebar_id
	 * @return  void
	 * @access  public
	 */
	function do_sidebar( $sidebar_id ) {
		if ( ! empty( $this->settings['sidebars'][ $sidebar_id ] ) ) {

			$sidebar_data = $this->settings['sidebars'][ $sidebar_id ];

			if ( empty( $sidebar_data['enable'] ) ) {
				return;
			}

			echo '<div id="' . $this->settings['css_prefix'] . '-' . esc_attr( $sidebar_id ) . '" ' . $this->get_sidebar_attributes( $sidebar_id, $sidebar_data ) . '>';

			/**
			 * Action to add content before the default sidebar content
			 *
			 * @since 0.3
			 *
			 * @see OCS_Off_Canvas_Sidebars->default_sidebar_settings for the sidebar settings
			 *
			 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
			 * @param  array  $sidebar_data  The sidebar settings
			 */
			do_action( 'ocs_custom_content_sidebar_before', $sidebar_id, $sidebar_data );

			if ( 'sidebar' == $sidebar_data['content'] ) {

				if ( get_template() == 'genesis' ) {
					genesis_widget_area( 'off-canvas-' . $sidebar_id );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
				} else {
					dynamic_sidebar( 'off-canvas-' . $sidebar_id );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
				}
			}

			elseif( 'menu' == $sidebar_data['content'] ) {

				$args = array(
					'fallback_cb' => false,
					'container' => 'nav' // HTML5 FTW!
				);

				/**
				 * Filter nav menu args
				 *
				 * Please note that the ID will be overwritten!
				 *
				 * @since 0.3
				 *
				 * @see https://developer.wordpress.org/reference/functions/wp_nav_menu/
				 * @see OCS_Off_Canvas_Sidebars->default_sidebar_settings for the sidebar settings
				 *
				 * @param  array  $args          The wp_nav_menu() arguments
				 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
				 * @param  array  $sidebar_data  The sidebar settings
				 */
				apply_filters( 'ocs_wp_nav_menu_args', $args, $sidebar_id, $sidebar_data );

				// Force our ID
				$args['menu'] = 'off-canvas-' . $sidebar_id;
				// Force echo
				$args['echo'] = true;

				wp_nav_menu( $args );
			}

			elseif( 'action' == $sidebar_data['content'] ) {

				/**
				 * Action to hook into the sidebar content
				 *
				 * @since 0.3
				 *
				 * @see OCS_Off_Canvas_Sidebars->default_sidebar_settings for the sidebar settings
				 *
				 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
				 * @param  array  $sidebar_data  The sidebar settings
				 */
				do_action( 'ocs_custom_content_sidebar_' . $sidebar_id, $sidebar_id, $sidebar_data );
			}

			/**
			 * Action to add content after the default sidebar content
			 *
			 * @since 0.3
			 *
			 * @see OCS_Off_Canvas_Sidebars->default_sidebar_settings for the sidebar settings
			 *
			 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
			 * @param  array  $sidebar_data  The sidebar settings
			 */
			do_action( 'ocs_custom_content_sidebar_after', $sidebar_id, $sidebar_data );

			echo '</div>';
		}
	}

	/**
	 * Get sidebar attributes
	 *
	 * @since   0.1
	 * @since   0.3  Overwrite global setting attributes
	 * @param   string  $sidebar_id
	 * @param   array   $data
	 * @return  string
	 */
	function get_sidebar_attributes( $sidebar_id, $data ) {
		$prefix = $this->settings['css_prefix'];
		$atts = array();

		$atts['class'] = array();
		$atts['class'][] = $prefix . '-slidebar';
		$atts['class'][] = $prefix . '-' . esc_attr( $sidebar_id );
		$atts['class'][] = 'ocs-slidebar';
		$atts['class'][] = 'ocs-' . esc_attr( $sidebar_id );
		$atts['class'][] = 'ocs-size-' . esc_attr( $data['size'] );
		$atts['class'][] = 'ocs-location-' . esc_attr( $data['location'] );
		$atts['class'][] = 'ocs-style-' . esc_attr( $data['style'] );

		/**
		 * Filter the classes for a sidebar
		 *
		 * @since  0.3
		 *
		 * @see OCS_Off_Canvas_Sidebars->default_sidebar_settings for the sidebar settings
		 *
		 * @param  array  $classes       Classes
		 * @param  string $sidebar_id    The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
		 * @param  array  $sidebar_data  The sidebar settings
		 */
		$atts['class'] = apply_filters( 'ocs_sidebar_classes', $atts['class'], $sidebar_id, $data );

		// Slidebars 2.0
		$atts['off-canvas'] = array(
			$prefix . '-' . esc_attr( $sidebar_id ), // ID
			esc_attr( $data['location'] ), // Location
			esc_attr( $data['style'] )     // Animation style
		);
		$atts['ocs-sidebar-id'] = esc_attr( $sidebar_id );

		// Overwrite global settings
		if ( true === (bool) $data['overwrite_global_settings'] ) {
			$atts['ocs-overwrite_global_settings'] = esc_attr( (int) $data['overwrite_global_settings'] );
			$atts['ocs-site_close']                = esc_attr( (int) $data['site_close'] );
			$atts['ocs-disable_over']              = esc_attr( (int) $data['disable_over'] );
			$atts['ocs-hide_control_classes']      = esc_attr( (int) $data['hide_control_classes'] );
			$atts['ocs-scroll_lock']               = esc_attr( (int) $data['scroll_lock'] );
		}

		foreach ( $atts as $name => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$atts[ $name ] = $name . '="' . $value . '"';
		}
		$return = implode( ' ', $atts );

		return $return;
	}

	/**
	 * Generate a trigger element
	 *
	 * @since  0.4
	 * @param  string $sidebar_id
	 * @param  array  $args  see API: the_ocs_control_trigger() for info
	 * @return string
	 */
	public function do_control_trigger( $sidebar_id, $args = array() ) {

		$sidebar_id = (string) $sidebar_id;

		$defaults = array(
			'text'    => '', // Text to show
			'action'  => 'toggle', // toggle|open|close
			'element' => 'button', // button|span|i|b|a|etc.
			'class'   => array(), // Extra classes (space separated), also accepts an array of classes
			'attr'    => array() // An array of attribute keys and their values
		);
		$args = array_merge( $defaults, $args );

		if ( in_array( $args['element'], array( 'base', 'body', 'html', 'link', 'meta', 'noscript', 'style', 'script', 'title' ) ) ) {
			return '<span class="error">' . __( 'This element is not supported for use as a button', 'off-canvas-sidebars' ) . '</span>';
		}

		$singleton = false;

		// Is it a singleton element? Add the text to the attributes
		if ( in_array( $args['element'], array( 'br', 'hr', 'img', 'input' ) ) ) {
			$singleton = true;
			if ( 'img' == $args['element'] && empty( $args['attr']['alt'] ) ) {
				$args['attr']['alt'] = $args['text'];
			}
			if ( 'input' == $args['element'] && empty( $args['attr']['value'] ) ) {
				$args['attr']['value'] = $args['text'];
			}
		}

		$attr = '';
		if ( empty( $args['attr']['class'] ) ) {
			$args['attr']['class'] = array();
		}
		foreach ( $args['attr'] as $key => $value ) {
			if ( 'class' == $key ) {
				// Add our own classes
				$ocs_classes = array(
					$this->settings['css_prefix'] . '-trigger',
					$this->settings['css_prefix'] . '-' . $args['action'],
					$this->settings['css_prefix'] . '-' . $args['action'] . '-' . $sidebar_id
				);
				if ( ! is_array( $value ) ) {
					$value = explode( ' ', $value );
				}
				foreach ( $ocs_classes as $class ) {
					if ( ! in_array( $class, $value ) ) {
						$value[] = $class;
					}
				}

				// Optionally add extra classes
				if ( ! empty( $args['class'] ) ) {
					$classes_extra = explode( ' ', $args['class'] );
					foreach ( $classes_extra as $c ) {
						$c = esc_attr( $c );
						if ( ! in_array( $c, $value ) ) {
							$value[] = $c;
						}
					}
				}
			}
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}
			$attr .= ' ' . esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
		}

		$return = '<' . $args['element'] . $attr;
		if ( $singleton ) {
			$return .= ' />';
		} else {
			$return .= '>' . $args['text'] . '</' . $args['element'] . '>';
		}

		return $return;
	}

	/**
	 * Add necessary scripts and styles
	 *
	 * @since   0.1
	 * @since   0.2    Add our own scripts and styles + localize them
	 * @since   0.2.2  Add FastClick library
	 * @return  void
	 */
	function add_styles_scripts() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$version = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : OCS_PLUGIN_VERSION;
		wp_enqueue_style( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.css', array(), '2.0.2' );
		wp_enqueue_script( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars'.$suffix.'.js', array( 'jquery' ), '2.0.2', true );

		wp_enqueue_style( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'css/off-canvas-sidebars.css', array(), $version ); // @todo: '.$suffix.'
		wp_enqueue_script( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'js/off-canvas-sidebars.js', array( 'jquery', 'slidebars' ), $version, true ); // @todo: '.$suffix.'

		$sidebars = array();
		foreach ( $this->settings['sidebars'] as $sidebar_id => $sidebar_data ) {
			if ( ! empty( $sidebar_data['enable'] ) ) {
				$sidebars[ $sidebar_id ] = $sidebar_data;
			}
		}
		wp_localize_script( 'off-canvas-sidebars', 'ocsOffCanvasSidebars', array(
			'site_close'           => ( $this->settings['site_close'] ) ? true : false,
			'disable_over'         => ( $this->settings['disable_over'] ) ? (int) $this->settings['disable_over'] : false,
			'hide_control_classes' => ( $this->settings['hide_control_classes'] ) ? true : false,
			'scroll_lock'          => ( $this->settings['scroll_lock'] ) ? true : false,
			'legacy_css'           => ( 'legacy-css' == $this->settings['compatibility_position_fixed'] ) ? true : false,
			'css_prefix'           => $this->settings['css_prefix'],
			'sidebars'             => $sidebars
		) );

		if ( 'custom-js' == $this->settings['compatibility_position_fixed'] ) {
			wp_enqueue_script( 'ocs-fixed-scrolltop', OCS_PLUGIN_URL . 'js/fixed-scrolltop.js', array( 'jquery' ), $version, true );
		}

		// FastClick library https://github.com/ftlabs/fastclick
		if ( true === (bool) $this->settings['use_fastclick'] ) {
			wp_enqueue_script( 'fastclick', OCS_PLUGIN_URL . 'js/fastclick.js', array(), false, true );
		}
	}

	/**
	 * Add necessary inline scripts
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
			$prefix = $this->settings['css_prefix'];
			?>
<style type="text/css">
<?php
if ( $this->settings['background_color_type'] != '' ) {
	$bgcolor = '';
	if ( $this->settings['background_color_type'] == 'transparent' ) {
		$bgcolor = 'transparent';
	}
	elseif ( $this->settings['background_color_type'] == 'color' && $this->settings['background_color'] != '' ) {
		$bgcolor = $this->settings['background_color'];
	}
?>
	#<?php echo $prefix; ?>-site {background-color: <?php echo $bgcolor; ?>;}
<?php
} // endif
foreach ( $this->settings['sidebars'] as $sidebar_id => $sidebar_data ) {
	if ( true === (bool) $sidebar_data['enable'] ) {
		$prop = array();
		if ( ! empty( $sidebar_data['background_color_type'] ) ) {
			if ( $sidebar_data['background_color_type'] == 'transparent' ) {
				$prop[] = 'background-color: transparent;';
			}
			elseif ( $sidebar_data['background_color_type'] == 'color' && $sidebar_data['background_color'] != '' ) {
				$prop[] = 'background-color: ' . $sidebar_data['background_color'] . ';';
			}
		}
		if ( $sidebar_data['size'] == 'custom' && ! empty( $sidebar_data['size_input'] ) ) {
			if ( in_array( $sidebar_data['location'], array( 'left', 'right' ) ) ) {
				$prop[] = 'width: ' . (int) $sidebar_data['size_input'] . $sidebar_data['size_input_type'] . ';';
			}
			elseif ( in_array( $sidebar_data['location'], array( 'top', 'bottom' ) ) ) {
				$prop[] = 'height: ' . (int) $sidebar_data['size_input'] . $sidebar_data['size_input_type'] . ';';
			}
		}
		if ( ! empty( $sidebar_data['animation_speed'] ) ) {
			// http://www.w3schools.com/cssref/css3_pr_transition-duration.asp
			$speed = (int) $sidebar_data['animation_speed'];
			$prop[] = '-webkit-transition-duration: ' . $speed . 'ms;';
			$prop[] = '-moz-transition-duration: ' . $speed . 'ms;';
			$prop[] = '-o-transition-duration: ' . $speed . 'ms;';
			$prop[] = 'transition-duration: ' . $speed . 'ms;';
		}
		if ( ! empty( $sidebar_data['padding'] ) ) {
			$prop[] = 'padding: ' . (int) $sidebar_data['padding'] . 'px;';
		}

		if ( ! empty( $prop ) ) {
?>
	.ocs-slidebar.ocs-<?php echo $sidebar_id; ?> {<?php echo implode( ' ', $prop ); ?>}
<?php
		} //endif
	} // endif
} //endforeach
?>
	.<?php echo $prefix; ?>-trigger {cursor: pointer;}
</style>
			<?php
		}
	}

	/**
	 * Main Off-Canvas Sidebars Frontend Instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3
	 * @static
	 * @return  OCS_Off_Canvas_Sidebars_Frontend
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // end class
