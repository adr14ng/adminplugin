<?php
/*
Plugin Name: Department Admin
Description: Shows a simplified Admin View to department editors
Version: 0.5
*/
//Is this admin pages?
if ( is_admin() ) {
	$plug_in_dir = dirname(__FILE__);
    //Load the plugin
    require $plug_in_dir . '/includes/dp-admin-core.php';
	$dp_new_admin = new DP_Admin();
	//Activate plugin
	register_activation_hook( __FILE__, array( 'DP_Admin', 'activate'));
	//Delete plugin
	register_uninstall_hook( __FILE__, array( 'DP_Admin', 'uninstall'));
	//Add filter to allow departments to edit own files
	add_filter( 'map_meta_cap', array( 'DP_Admin','match_category_user'), 10, 4);
	//Add a basic style to the pages
	add_action('admin_enqueue_scripts', array( 'DP_Admin', 'add_base_style'));
	//Add menu for aggregate view
	require $plug_in_dir . '/includes/aggregate-edit-form.php';
	add_action( 'admin_menu', 'add_aggregate_menu' );
	
}//is_admin()

