<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();


if ( ! is_multisite() ) {
	ocs_uninstall();
} else {
    $blogs = wp_get_sites(); // Sadly does not work for large networks -> return false
	if ($blogs) {
		foreach ( $blogs as $blog ) {
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
