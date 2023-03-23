<?php
/**
 * Off-Canvas Sidebars - Class Frontend
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Off-Canvas Sidebars plugin front-end
 *
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.8
 * @uses    \OCS_Off_Canvas_Sidebars_Base Extends class
 */
final class OCS_Off_Canvas_Sidebars_Frontend extends OCS_Off_Canvas_Sidebars_Base
{
	/**
	 * The single instance of the class.
	 *
	 * @var    \OCS_Off_Canvas_Sidebars_Frontend
	 * @since  0.3.0
	 */
	protected static $_instance = null;

	/**
	 * Class constructor.
	 * @since  0.3.0  Private constructor.
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles_scripts' ) );
	}

	/**
	 * Initialize frontend.
	 * Add default actions.
	 *
	 * @since   0.1.0
	 * @since   0.5.8  Renamed from `default_actions` and made public.
	 */
	public function init() {
		/**
		 * Enable or disable frontend rendering.
		 *
		 * @since 0.5.8
		 *
		 * @param  bool  $enabled  Whether to enable frontend.
		 */
		if ( ! apply_filters( 'ocs_enable_frontend', (bool) $this->get_settings( 'enable_frontend' ) ) ) {
			return;
		}

		$before_hook = $this->get_website_before_hook();
		$after_hook  = $this->get_website_after_hook();

		$before_prio = $this->get_settings( 'website_before_hook_priority' );
		$after_prio  = $this->get_settings( 'website_after_hook_priority' );

		if ( ! is_numeric( $before_prio ) ) {
			$before_prio = 5; // Early addition.
		}
		if ( ! is_numeric( $after_prio ) ) {
			$after_prio = 50; // Late addition.
			if ( 'wp_footer' === $after_hook ) {
				$after_prio = -50; // Early addition (before scripts).
			}
		}

		$before_prio = apply_filters( 'ocs_website_before_hook_priority', $before_prio );
		$after_prio  = apply_filters( 'ocs_website_after_hook_priority', $after_prio );

		add_action( $before_hook, array( $this, 'before_site' ), $before_prio );
		add_action( $after_hook, array( $this, 'after_site' ), $after_prio );

		/* EXPERIMENTAL */
		//add_action( 'wp_footer', array( $this, 'after_site' ), 0 ); // enforce first addition.
		//add_action( 'wp_footer', array( $this, 'after_site_script' ), 99999 ); // enforce almost last addition.

		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
	}

	/**
	 * Add classes to the body
	 *
	 * @since   1.4.0
	 * @param   array  $classes
	 * @return  array
	 */
	public function filter_body_class( $classes ) {
		if ( 'legacy-css' === $this->get_settings( 'compatibility_position_fixed' ) ) {
			$classes[] = 'ocs-legacy';
		}
		return $classes;
	}

	/**
	 * Get the hook to open the website wrapper.
	 *
	 * @since  0.5.6
	 * @return string
	 */
	public function get_website_before_hook() {
		$before_hook = trim( $this->get_settings( 'website_before_hook' ) );

		if ( empty( $before_hook ) ) {
			if ( 'genesis' === get_template() ) {
				$before_hook = 'genesis_before';
			} else {
				$before_hook = 'website_before';
			}
		}

		return trim( apply_filters( 'ocs_website_before_hook', $before_hook ) );
	}

	/**
	 * Get the hook to close the website wrapper.
	 *
	 * @since  0.5.6
	 * @return string
	 */
	public function get_website_after_hook() {
		$after_hook = trim( $this->get_settings( 'website_after_hook' ) );

		if ( empty( $after_hook ) ) {
			if ( 'genesis' === get_template() ) {
				$after_hook = 'genesis_after';
			} else {
				$after_hook = 'website_after';
			}
		}

		return trim( apply_filters( 'ocs_website_after_hook', $after_hook ) );
	}

	/**
	 * before_site action hook
	 *
	 * @since   0.1.0
	 * @since   0.2.0  Add canvas attribute (Slidebars 2.0).
	 * @since   0.2.1  Add actions.
	 * @access  public
	 */
	public function before_site() {

		// Add content before the site container.
		do_action( 'ocs_container_before' );

		// Open site canvas container.
		echo '<div ' . $this->get_container_attributes() . '>';

		// Add content before other content in the site container.
		do_action( 'ocs_container_inner_before' );
	}

	/**
	 * after_site action hook
	 *
	 * @since   0.1
	 * @since   0.2.1  Add actions.
	 * @access  public
	 */
	public function after_site() {

		// Add content after other content in the site container.
		do_action( 'ocs_container_inner_after' );

		// Close site canvas container.
		if ( 'jquery' !== $this->get_settings( 'frontend_type' ) ) {
			echo '</div>';
		}

		// Add content after the site container.
		do_action( 'ocs_container_after' );

		// Add all the enabled sidebars.
		$this->do_sidebars();
	}

	/**
	 * EXPERIMENTAL: Not used in this version.
	 *
	 * after_site action hook for scripts.
	 *
	 * @since   0.1.0
	 * @access  public
	 */
	public function after_site_script() {
		if ( ! is_admin() ) {
			$prefix = esc_attr( $this->get_settings( 'css_prefix' ) );
			?>
<script type="text/javascript">
	(function($) {
		$('div.<?php echo $prefix; ?>-slidebar:first').prevAll().wrapAll('<div id="<?php echo $prefix; ?> -site" canvas="container"></div>');
	}) (jQuery);
</script>
			<?php
		}
	}

	/**
	 * Render all sidebars.
	 *
	 * @since   0.3.0
	 * @since   0.5.3  Add actions.
	 * @access  public
	 */
	public function do_sidebars() {

		// Add content before the off-canvas sidebars.
		do_action( 'ocs_sidebars_before' );

		$sidebars = $this->get_enabled_sidebars();
		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar_id => $sidebar_data ) {
				$this->do_sidebar( $sidebar_id );
			}
		}

		// Add content after the off-canvas sidebars.
		do_action( 'ocs_sidebars_after' );
	}

	/**
	 * Render a sidebar.
	 *
	 * @since   0.1.0
	 * @access  public
	 * @param   string  $sidebar_id
	 */
	public function do_sidebar( $sidebar_id ) {
		$sidebar_data = $this->get_sidebar_settings( $sidebar_id );
		if ( empty( $sidebar_data ) ) {
			return;
		}

		if ( ! $this->is_sidebar_enabled( $sidebar_id, $sidebar_data ) ) {
			return;
		}

		/**
		 * Change the sidebar element.
		 *
		 * @since 0.5.7
		 *
		 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
		 * @param  array   $sidebar_data  The sidebar settings.
		 */
		$element = esc_attr( apply_filters( 'ocs_sidebar_element', 'div', $sidebar_id, $sidebar_data ) );

		echo '<' . $element . ' ' . $this->get_sidebar_attributes( $sidebar_id, $sidebar_data ) . '>';

		/**
		 * Action to add content before the default sidebar content
		 *
		 * @since 0.3.0
		 *
		 * @see  \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings
		 *
		 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
		 * @param  array   $sidebar_data  The sidebar settings.
		 */
		do_action( 'ocs_custom_content_sidebar_before', $sidebar_id, $sidebar_data );

		switch ( $sidebar_data['content'] ) {

			case 'sidebar':
				/**
				 * Sidebar args are set when registering.
				 * @see  \OCS_Off_Canvas_Sidebars::register_sidebars()
				 */
				if ( 'genesis' === get_template() ) {
					genesis_widget_area( 'off-canvas-' . $sidebar_id );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
				} else {
					dynamic_sidebar( 'off-canvas-' . $sidebar_id );//, array('before'=>'<aside class="sidebar widget-area">', 'after'=>'</aside>'));
				}

				break;

			case 'menu':
				$args = array(
					'fallback_cb' => false,
					'container'   => 'nav', // HTML5 FTW!
				);

				/**
				 * Filter nav menu args.
				 *
				 * Please note that the `theme_location` and `echo` properties will be overwritten!
				 *
				 * @since 0.3.0
				 *
				 * @see https://developer.wordpress.org/reference/functions/wp_nav_menu/
				 * @see \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
				 *
				 * @param  array   $args          The wp_nav_menu() arguments.
				 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
				 * @param  array   $sidebar_data  The sidebar settings.
				 */
				$args = apply_filters( 'ocs_wp_nav_menu_args', $args, $sidebar_id, $sidebar_data );

				// Force the theme location.
				$args['theme_location'] = 'off-canvas-' . $sidebar_id;
				// Force echo.
				$args['echo'] = true;

				wp_nav_menu( $args );

				break;

			case 'action':
			default:
				/**
				 * Action to hook into the sidebar content.
				 *
				 * @since 0.3.0
				 *
				 * @see \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
				 *
				 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
				 * @param  array   $sidebar_data  The sidebar settings.
				 */
				do_action( 'ocs_custom_content_sidebar_' . $sidebar_id, $sidebar_id, $sidebar_data );

				break;
		}

		/**
		 * Action to add content after the default sidebar content.
		 *
		 * @since 0.3.0
		 *
		 * @see \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
		 *
		 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
		 * @param  array   $sidebar_data  The sidebar settings.
		 */
		do_action( 'ocs_custom_content_sidebar_after', $sidebar_id, $sidebar_data );

		echo '</' . $element . '>';
	}

	/**
	 * Get the enabled plugin sidebars.
	 *
	 * @since   0.5.3
	 * @since   0.5.6  Overwrite from global settings.
	 * @return  array
	 */
	public function get_enabled_sidebars() {
		$sidebars = off_canvas_sidebars_settings()->get_sidebars();
		foreach ( $sidebars as $sidebar_id => $sidebar_data ) {
			if ( ! $this->is_sidebar_enabled( $sidebar_id ) ) {
				unset( $sidebars[ $sidebar_id ] );
			} elseif ( empty( $sidebar_data['overwrite_global_settings'] ) ) {
				// Get global settings with the same key.
				$settings = off_canvas_sidebars_settings()->get_settings();
				$settings = array_intersect_key( $settings, $sidebar_data );
				// Overwrite sidebar settings with global settings.
				$sidebars[ $sidebar_id ] = array_merge( $sidebar_data, $settings );
			}
		}
		return $sidebars;
	}

	/**
	 * Get the plugin settings.
	 *
	 * @since   0.5.6
	 * @param   string  $sidebar_id  The sidebar ID.
	 * @param   string  $key         (optional) Get a single setting by key?
	 * @return  mixed
	 */
	public function get_sidebar_settings( $sidebar_id, $key = null ) {
		$sidebars = $this->get_enabled_sidebars();
		if ( empty( $sidebars[ $sidebar_id ] ) ) {
			return null;
		}
		$settings = $sidebars[ $sidebar_id ];

		/**
		 * Filter whether an off-canvas sidebar should be rendered.
		 * @since   0.5.6
		 * @param   array   $settings
		 * @param   string  $sidebar_id
		 * @return  array
		 */
		$settings = apply_filters( 'ocs_get_sidebar_settings', $settings, $sidebar_id );
		if ( $key ) {
			return ( isset( $settings[ $key ] ) ) ? $settings[ $key ] : null;
		}
		return $settings;
	}

	/**
	 * Check if an off-canvas sidebar should be shown.
	 * Difference with settings is that this method allows a filter for frontend.
	 *
	 * @since   0.5.2
	 * @param   string  $sidebar_id
	 * @param   array   $sidebar_data
	 * @return  bool
	 */
	public function is_sidebar_enabled( $sidebar_id, $sidebar_data = null ) {
		$enabled = off_canvas_sidebars_settings()->is_sidebar_enabled( $sidebar_id );

		/**
		 * Filter whether an off-canvas sidebar should be rendered.
		 * @since   0.5.2
		 * @param   bool    $enabled
		 * @param   string  $sidebar_id
		 * @param   array   $sidebar_data
		 * @return  bool
		 */
		return (bool) apply_filters( 'ocs_is_sidebar_enabled', $enabled, $sidebar_id, $sidebar_data );
	}

	/**
	 * Get container attributes
	 *
	 * @since   0.5.3
	 * @return  string
	 */
	public function get_container_attributes() {
		$atts = array(
			'id'                            => $this->get_settings( 'css_prefix' ) . '-site',
			'data-canvas'                   => 'container',
			'data-ocs-site_close'           => (bool) $this->get_settings( 'site_close' ),
			'data-ocs-disable_over'         => (int) $this->get_settings( 'disable_over' ),
			'data-ocs-hide_control_classes' => (bool) $this->get_settings( 'hide_control_classes' ),
			'data-ocs-scroll_lock'          => (bool) $this->get_settings( 'scroll_lock' ),
		);

		return self::parse_to_html_attr( $atts );
	}

	/**
	 * Get sidebar attributes
	 *
	 * @since   0.1.0
	 * @since   0.3.0  Overwrite global setting attributes.
	 * @since   0.5.1  Move `id` attr here.
	 * @param   string  $sidebar_id
	 * @param   array   $data
	 * @return  string
	 */
	public function get_sidebar_attributes( $sidebar_id, $data ) {
		$prefix = $this->get_settings( 'css_prefix' );
		$atts   = array();

		$atts['id'] = $prefix . '-' . $sidebar_id;

		$atts['class']   = array();
		$atts['class'][] = $prefix . '-slidebar';
		$atts['class'][] = $prefix . '-' . $sidebar_id;
		if ( 'ocs' !== $prefix ) {
			$atts['class'][] = 'ocs-slidebar';
			$atts['class'][] = 'ocs-' . $sidebar_id;
		}
		$atts['class'][] = 'ocs-size-' . $data['size'];
		$atts['class'][] = 'ocs-location-' . $data['location'];
		$atts['class'][] = 'ocs-style-' . $data['style'];

		/**
		 * Filter the classes for a sidebar.
		 *
		 * @since  0.3.0
		 *
		 * @see \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
		 *
		 * @param  array   $classes       Sidebar classes.
		 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
		 * @param  array   $sidebar_data  The sidebar settings.
		 */
		$atts['class'] = apply_filters( 'ocs_sidebar_classes', $atts['class'], $sidebar_id, $data );

		// Slidebars 2.0
		$atts['data-off-canvas'] = array(
			$prefix . '-' . $sidebar_id, // ID.
			$data['location'], // Location.
			$data['style'], // Animation style.
		);

		$atts['data-ocs-sidebar-id'] = $sidebar_id;

		// Overwrite global settings.
		if ( true === (bool) $data['overwrite_global_settings'] ) {
			$atts['data-ocs-overwrite_global_settings'] = (int) $data['overwrite_global_settings'];
			$atts['data-ocs-site_close']                = (int) $data['site_close'];
			$atts['data-ocs-disable_over']              = (int) $data['disable_over'];
			$atts['data-ocs-hide_control_classes']      = (int) $data['hide_control_classes'];
			$atts['data-ocs-scroll_lock']               = (int) $data['scroll_lock'];
		}

		/**
		 * Filter the attributes for a sidebar.
		 *
		 * @since  0.5.7
		 *
		 * @see \OCS_Off_Canvas_Sidebars_Settings::$default_sidebar_settings for the sidebar settings.
		 *
		 * @param  array   $atts          Sidebar attributes.
		 * @param  string  $sidebar_id    The ID of this sidebar as configured in: Appearance > Off-Canvas Sidebars > Sidebars.
		 * @param  array   $sidebar_data  The sidebar settings.
		 */
		$atts = apply_filters( 'ocs_sidebar_attributes', $atts, $sidebar_id, $data );

		return self::parse_to_html_attr( $atts );
	}

	/**
	 * Generate a trigger element.
	 *
	 * @see     OCS_Off_Canvas_Sidebars_Control_Trigger::render()
	 * @since   0.4.0
	 * @since   0.5.0  Add icon options.
	 * @param   string  $sidebar_id
	 * @param   array   $args        See API: the_ocs_control_trigger() for info.
	 * @return  string
	 */
	public function do_control_trigger( $sidebar_id, $args = array() ) {
		return OCS_Off_Canvas_Sidebars_Control_Trigger::render( $sidebar_id, $args );
	}

	/**
	 * Add necessary scripts and styles.
	 *
	 * @since   0.1.0
	 * @since   0.2.0  Add our own scripts and styles + localize them.
	 * @since   0.2.2  Add FastClick library.
	 */
	public function add_styles_scripts() {
		/**
		 * Enable or disable frontend assets.
		 *
		 * @since 0.5.8
		 *
		 * @param  bool  $enabled  Whether to enable frontend.
		 */
		if ( ! apply_filters( 'ocs_enable_assets', true ) ) {
			return;
		}

		// @todo Validate and use minified files
		$suffix  = '';//defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : OCS_PLUGIN_VERSION;

		// FastClick library https://github.com/ftlabs/fastclick
		if ( $this->get_settings( 'use_fastclick' ) ) {
			wp_enqueue_script( 'fastclick', OCS_PLUGIN_URL . 'js/fastclick' . $suffix . '.js', array(), '1.0.6', true );
		}

		if ( 'custom-js' === $this->get_settings( 'compatibility_position_fixed' ) ) {
			wp_enqueue_script( 'ocs-fixed-scrolltop', OCS_PLUGIN_URL . 'js/fixed-scrolltop' . $suffix . '.js', array( 'jquery' ), $version, true );
		}

		wp_enqueue_style( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars' . $suffix . '.css', array(), $version );
		wp_enqueue_script( 'slidebars', OCS_PLUGIN_URL . 'slidebars/slidebars' . $suffix . '.js', array( 'jquery' ), $version, true );

		wp_enqueue_style( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'css/off-canvas-sidebars' . $suffix . '.css', array(), $version );
		wp_enqueue_script( 'off-canvas-sidebars', OCS_PLUGIN_URL . 'js/off-canvas-sidebars' . $suffix . '.js', array( 'jquery', 'slidebars' ), $version, true );

		wp_localize_script(
			'off-canvas-sidebars',
			'ocsOffCanvasSidebars',
			array(
				'late_init'            => (bool) $this->get_settings( 'late_init' ),
				'site_close'           => (bool) $this->get_settings( 'site_close' ),
				'link_close'           => (bool) $this->get_settings( 'link_close' ),
				'disable_over'         => (int) $this->get_settings( 'disable_over' ),
				'hide_control_classes' => (bool) $this->get_settings( 'hide_control_classes' ),
				'scroll_lock'          => (bool) $this->get_settings( 'scroll_lock' ),
				'legacy_css'           => (bool) ( 'legacy-css' === $this->get_settings( 'compatibility_position_fixed' ) ),
				'css_prefix'           => $this->get_settings( 'css_prefix' ),
				'sidebars'             => $this->get_enabled_sidebars(),
				'_debug'               => (bool) ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			)
		);

		wp_add_inline_style( 'off-canvas-sidebars', $this->get_inline_styles() );
	}

	/**
	 * Get inline styles.
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @todo Refactor to enable above checks?
	 *
	 * @since   0.1.0  Added as output method `add_inline_styles` in `wp_head` action.
	 * @since   0.5.8  Refactored and renamed to a getter method.
	 * @return  string
	 */
	public function get_inline_styles() {
		$prefix = $this->get_settings( 'css_prefix' );
		$css    = '';

		$bg_color_type = $this->get_settings( 'background_color_type' );
		if ( '' !== $bg_color_type ) {
			$bg_color = '';
			if ( 'transparent' === $bg_color_type ) {
				$bg_color = 'transparent';
			}
			elseif ( 'color' === $bg_color_type ) {
				$bg_color = OCS_Off_Canvas_Sidebars_Settings::validate_color( $this->get_settings( 'background_color' ) );
			}
			if ( $bg_color ) {
				$css .= '#' . $prefix . '-site {background-color:' . $bg_color . ';}';
			}
		} // End if().

		foreach ( $this->get_enabled_sidebars() as $sidebar_id => $sidebar_data ) {
			$prop = array();
			if ( ! empty( $sidebar_data['background_color_type'] ) ) {
				if ( 'transparent' === $sidebar_data['background_color_type'] ) {
					$prop[] = 'background-color: transparent;';
				}
				elseif ( 'color' === $sidebar_data['background_color_type'] && '' !== $sidebar_data['background_color'] ) {
					$prop[] = 'background-color: ' . OCS_Off_Canvas_Sidebars_Settings::validate_color( $sidebar_data['background_color'] ) . ';';
				}
			}
			if ( 'custom' === $sidebar_data['size'] && ! empty( $sidebar_data['size_input'] ) ) {
				if ( in_array( $sidebar_data['location'], array( 'left', 'right' ), true ) ) {
					$prop[] = 'width: ' . (int) $sidebar_data['size_input'] . $sidebar_data['size_input_type'] . ';';
				}
				elseif ( in_array( $sidebar_data['location'], array( 'top', 'bottom' ), true ) ) {
					$prop[] = 'height: ' . (int) $sidebar_data['size_input'] . $sidebar_data['size_input_type'] . ';';
				}
			}
			if ( ! empty( $sidebar_data['animation_speed'] ) ) {
				// http://www.w3schools.com/cssref/css3_pr_transition-duration.asp
				$speed  = (int) $sidebar_data['animation_speed'];
				$prop[] = '-webkit-transition-duration: ' . $speed . 'ms;';
				$prop[] = '-moz-transition-duration: ' . $speed . 'ms;';
				$prop[] = '-o-transition-duration: ' . $speed . 'ms;';
				$prop[] = 'transition-duration: ' . $speed . 'ms;';
			}
			if ( ! empty( $sidebar_data['padding'] ) ) {
				$prop[] = 'padding: ' . (int) $sidebar_data['padding'] . 'px;';
			}

			if ( ! empty( $prop ) ) {
				$css .= '.ocs-slidebar.ocs-' . $sidebar_id . ' {' . implode( ' ', $prop ) . '}';
			}
		} // End foreach().

		/**
		 * Modify the inline styles.
		 *
		 * @since 0.5.8
		 *
		 * @param  string  $css     The rendered inline styles.
		 * @param  string  $prefix  The CSS prefix.
		 *
		 * @return string
		 */
		$css = apply_filters( 'ocs_inline_styles', $css, $prefix );

		return $css;
	}

	/**
	 * Class Instance.
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @since   0.3.0
	 * @static
	 * @return  \OCS_Off_Canvas_Sidebars_Frontend
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

} // End class().
