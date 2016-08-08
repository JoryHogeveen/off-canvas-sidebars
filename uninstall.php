<?php
/**
 * Off-Canvas Sidebars plugin uninstall
 *
 * Uninstall
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.2
 */

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();


if ( ! is_multisite() ) {
	ocs_uninstall();
} else {
	global $wp_version;
	if ( version_compare( $wp_version, '4.5.999', '<' ) ) {
		// Sadly does not work for large networks -> return false
		$blogs = wp_get_sites();
	} else {
		$blogs = get_sites();
	}
	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $blog ) {
			$blog = (array) $blog;
			switch_to_blog( intval( $blog['blog_id'] ) );
			ocs_uninstall();
		}
		restore_current_blog();
	}
}

function ocs_uninstall() {
	
	// Delete all options
	$option_keys = array( 'off_canvas_sidebars_options' );
	foreach ( $option_keys as $option_key ) {
		delete_option( $option_key );
	}
		
}
