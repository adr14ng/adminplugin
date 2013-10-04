<?php
/*
Plugin Name: Department Admin
Description: Shows a simplified Admin View to department editors
Version: 0.1
*/

//Is this admin pages?
if ( is_admin() ) {

    //Load the plugin
    require dirname(__FILE__) . '/includes/dp-admin-core.php';
	$dp_new_admin = new DP_Admin();
	
	//Activate plugin
	register_activation_hook( __FILE__, array( 'DP_Admin', 'activate'));
	
	//Delete plugin
	register_uninstall_hook( __FILE__, array( 'DP_Admin', 'uninstall'));
	
	//Add filter to allow departments to edit own files
	add_filter( 'map_meta_cap', array( 'DP_Admin','match_category_user'), 10, 4);
	
	//Add a basic style to the pages
	add_action('admin_enqueue_scripts', array( 'DP_Admin', 'add_base_style'));
	
	//Add custom post types
	add_action('init', array( 'DP_Admin', 'add_base_style') );
	
}//is_admin()

