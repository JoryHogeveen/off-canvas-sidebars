<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$option_name = array('off_canvas_sidebars_options');

foreach ($option_name as $option) {
	delete_option( $option );
	// For site options in multisite
	delete_site_option( $option );  
}

// Meta is removed when the menu item is removes so no need for this
/*
$post_meta = array('_off_canvas_control_menu_item');
foreach ($post_meta as $meta) {
}
*/

//drop a custom db table
//global $wpdb;
//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mytable" );

//note in multisite looping through blogs to delete options on each blog does not scale. You'll just have to leave them.