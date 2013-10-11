<?php

//need to enable url fopen
//includes->dpadmin->plugins->wp-content->root
$admin_url = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-admin';
require_once $admin_url . '/admin.php'; 

function add_aggregate_menu()
{
	add_menu_page( 'Edit Department Page', 'Department Pages', 'edit_posts', 
				'dp_page', 'aggregate_post', $icon, 20 ); //need icon
}

//function that generates the aggregate post page
function aggregate_post() {
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$userCat = get_user_meta($user_id, 'user_cat');
	
	list_aggregate_post();

}

//if user has more than one category, list department pages
function list_aggregate_post() {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require( dirname(__FILE__) . '/class-dp-aggregate-list-table.php' );
	
	//Do I need to set up something else?
	
	//Create and display the aggregate list table
	$aggr_list_table = new Aggregate_List_Table();
	$aggr_list_table->prepare_items();
	$aggr_list_table->display();
}

//Returns a link to the aggregate edit page
//Used for building the table and redirects
function get_aggregate_edit_link($cat, $context='') {
	$sformat = 'dp_page.php?cat=%s';
	
	if( 'display' == $context)
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat));
	
}

?>