<?php
/*
Plugin Name: Department Admin
Description: Shows a simplified Admin View to department editors
Version: 0.1
*/

//Is this admin pages?
if ( is_admin() ) {	//does my new admin page count?
	$plug_in_dir = plugin_dir_url(__FILE__);

    //Load the plugin
    require dirname $plug_in_dir . '/includes/dp-admin-core.php';
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
	add_action( 'admin_menu', 'register_my_custom_menu_page' );
	
	//Add custom post types
	add_action('init', array( 'DP_Admin', 'csun_create_custom') );
	
}//is_admin()

