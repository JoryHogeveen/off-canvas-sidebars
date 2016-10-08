<?php
/**
 * Off-Canvas Sidebars plugin API
 *
 * API
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.3
 */

! defined( 'ABSPATH' ) and die( 'You shall not pass!' );

/**
 * Echos one, multiple or all OCS sidebars
 *
 * @since  0.3
 * @param  string|array|bool  $sidebars  (Optional) The ID of this sidebar as configured in: Appearances > Off-Canvas Sidebars > Sidebars
 */
function the_ocs_off_canvas_sidebar( $sidebars = false ) {
	if ( $instance = Off_Canvas_Sidebars_Frontend() ) {
		if ( false === $sidebars ) {
			$instance->do_sidebars();
		} else {
			if ( is_array( $sidebars ) ) {
				foreach( $sidebars as $sidebar ) {
					$instance->do_sidebar( (string) $sidebar );
				}
			} else {
				$instance->do_sidebar( (string) $sidebars );
			}
		}
	}
}

/**
 * Main instance of Off-Canvas Sidebars Frontend.
 *
 * Returns the main instance of OCS_Off_Canvas_Sidebars_Frontend to prevent the need to use globals.
 *
 * @since   0.3
 * @return  OCS_Off_Canvas_Sidebars_Frontend|false
 */
function Off_Canvas_Sidebars_Frontend() {
	if ( class_exists( 'OCS_Off_Canvas_Sidebars_Frontend' ) ) {
		return OCS_Off_Canvas_Sidebars_Frontend::get_instance();
	}
	return false;
}
