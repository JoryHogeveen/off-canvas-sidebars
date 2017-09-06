<?php
/**
 * Off-Canvas Sidebars plugin uninstall
 *
 * Uninstall
 * @author Jory Hogeveen <info@keraweb.nl>
 * @package off-canvas-sidebars
 * @version 0.4
 * @todo Uninstall for multi-networks aswell
 */

//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

if ( ! is_multisite() ) {
	ocs_uninstall();
} else {
	global $wp_version;
	if ( version_compare( $wp_version, '4.5.999', '<' ) ) {
		// @codingStandardsIgnoreLine - Sadly does not work for large networks -> return false
		$blogs = wp_get_sites();
	} else {
		$blogs = get_sites();
	}
	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $blog ) {
			$blog = (array) $blog;
			ocs_uninstall( intval( $blog['blog_id'] ) );
		}
		ocs_uninstall( 'site' );
	}
}

function ocs_uninstall( $blog_id = false ) {

	// Delete all options
	$option_keys = array( 'off_canvas_sidebars_options' );
	if ( $blog_id ) {
		if ( 'site' === $blog_id ) {
			foreach ( $option_keys as $option_key ) {
				delete_site_option( $option_key );
			}
		} else {
			foreach ( $option_keys as $option_key ) {
				delete_blog_option( $blog_id, $option_key );
			}
		}
	} else {
		foreach ( $option_keys as $option_key ) {
			delete_option( $option_key );
		}
	}

}
