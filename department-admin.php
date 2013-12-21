<?php
/** 
* Plugin Name: Department Admin 
* Description: Shows a simplified Admin View to department editors 
* Version: 1.0 
* Author: CSUN Undergraduate Studies 
*/

//Login redirect to dashpage
function csun_login($redirect_to){
	//is there a user to check?
    global $user;
    if( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if( in_array( "dp_editor", $user->roles ) ) {
            // redirect them to the default place
            return admin_url('admin.php?page=review');;
        } else {
		return admin_url('index.php');
		}
	}
	else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'csun_login');

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
	
	//Add new toolbar 
	add_filter( 'acf/fields/wysiwyg/toolbars' , array( 'DP_Admin', 'my_toolbars'));
	
	//Add menu for aggregate view
	require $plug_in_dir . '/includes/aggregate-edit-form.php';
	add_action( 'admin_menu', 'add_aggregate_menu' );		
	
	//Add menu for proposal files
	require $plug_in_dir . '/includes/proposal-files.php';
	add_action( 'admin_menu', 'add_proposal_menu' );
	
	//Add settings page
	require $plug_in_dir . '/includes/dp-options.php';
	$dp_settings_page = new DPAdminSettings();

	//Add menu for review page
	require $plug_in_dir . '/includes/review.php';
	add_action( 'admin_menu', 'add_review_menu' );
	
	//Chage layout for department editors
	add_action('init', array( 'DP_Admin', 'change_layout'));	
	
	//Make editor posts save as pending
	add_filter( 'wp_insert_post_data', array( 'DP_Admin', 'make_pending_post'));
	
	//Add custom footer 
	function csun_footer_admin () 
	{	  
		echo 'Powered by the Office of Undergraduate Studies.';	
	}	
	add_filter('admin_footer_text', 'csun_footer_admin');
	
	//Add custom colors
	add_action( 'admin_init' , array( 'DP_Admin', 'add_csun_colors') );
}//is_admin()
