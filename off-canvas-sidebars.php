<?php
/**
 * @author  Jory Hogeveen <info@keraweb.nl>
 * @package Off_Canvas_Sidebars
 * @since   0.1.0
 * @version 0.5.5
 * @licence GPL-2.0+
 * @link    https://github.com/JoryHogeveen/off-canvas-sidebars
 *
 * @wordpress-plugin
 * Plugin Name:       Off-Canvas Sidebars & Menus (Slidebars)
 * Description:       Add off-canvas sidebars using the Slidebars jQuery plugin
 * Plugin URI:        https://wordpress.org/plugins/off-canvas-sidebars/
 * Version:           0.5.5
 * Author:            Jory Hogeveen
 * Author URI:        http://www.keraweb.nl
 * Text Domain:       off-canvas-sidebars
 * Domain Path:       /languages/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/JoryHogeveen/off-canvas-sidebars
 *
 * @copyright 2015-2019 Jory Hogeveen
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * ( at your option ) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'OCS_Off_Canvas_Sidebars' ) && ! function_exists( 'off_canvas_sidebars' ) ) {

	define( 'OCS_PLUGIN_VERSION', '0.5.5' );
	define( 'OCS_DOMAIN', 'off-canvas-sidebars' );
	define( 'OCS_FILE', __FILE__ );
	define( 'OCS_BASENAME', plugin_basename( OCS_FILE ) );
	define( 'OCS_PLUGIN_DIR', plugin_dir_path( OCS_FILE ) );
	define( 'OCS_PLUGIN_URL', plugin_dir_url( OCS_FILE ) );

	// Include main class file.
	require_once( OCS_PLUGIN_DIR . 'includes/class-ocs.php' );

	/**
	 * Main instance of Off-Canvas Sidebars.
	 *
	 * Returns the main instance of OCS_Off_Canvas_Sidebars to prevent the need to use globals.
	 *
	 * @since   0.1.2
	 * @return  OCS_Off_Canvas_Sidebars
	 */
	function off_canvas_sidebars() {
		return OCS_Off_Canvas_Sidebars::get_instance();
	}

	off_canvas_sidebars();

// end if class_exists.
} else {

	// @since  0.5.1  added notice on class name conflict.
	add_action( 'admin_notices', 'off_canvas_sidebars_conflict_admin_notice' );
	function off_canvas_sidebars_conflict_admin_notice() {
		echo '<div class="notice-error notice is-dismissible"><p><strong>' . esc_html__( 'Off-Canvas Sidebars', 'off-canvas-sidebars' ) . ':</strong> '
		     . esc_html__( 'Plugin not activated because of a conflict with an other plugin or theme', 'off-canvas-sidebars' )
		     // Translators: %s stands for the class name.
		     . ' <code>(' . sprintf( esc_html__( 'Class %s already exists', 'off-canvas-sidebars' ), 'OCS_Off_Canvas_Sidebars' ) . ')</code></p></div>';
	}
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( plugin_basename( __FILE__ ) );

} // End if().
