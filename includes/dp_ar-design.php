<?php
/* * * * * * * * * * * * * * * * * * * * * *
 *
 *	Admissions and Records Custom Creation
 *
 *	1. Style
 *	2. Admin Bar
 *	3. List Tables
 *	4. Programs Link 
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */
 
 /*****************************************************
 *
 *  Including the styles and js
 *
 ****************************************************/
function add_ar_style() {
	$basedir = dirname(plugin_dir_url(__FILE__));
	wp_enqueue_style('ar-style', $basedir . '/css/admissions-style.css');
}
add_action('admin_enqueue_scripts', 'add_ar_style');

/*****************************************************
 *
 *  Editing the admin bar
 *
 *****************************************************/
 //Remove admin bar links
function remove_admissions_admin_bar_links( $wp_admin_bar ) {
	//remove all the other links
	$wp_admin_bar->remove_node( 'comments' );
	$wp_admin_bar->remove_node( 'new-content' );
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'site-name' );
	$wp_admin_bar->remove_node( 'edit-profile' );
	$wp_admin_bar->remove_node( 'user-info' );
}
add_action( 'admin_bar_menu', 'remove_admissions_admin_bar_links', 999 );

/*****************************************************
 *
 *  Editing the list tables
 *
 *****************************************************/
//remove extra info in list
function simplify_admissions_post_columns($defaults) {
  unset($defaults['comments']);
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['tags']);
  unset($defaults['department']);
  unset($defaults['level']);
  return $defaults;
}
add_filter('manage_${post_type}_posts_columns', 'simplify_admissions_post_columns');

function simplify_program_columns($defaults) {
  unset($defaults['cb']);
  unset($defaults['department']);
  unset($defaults['level']);
  return $defaults;
}
add_filter('manage_edit-programs_columns', 'simplify_program_columns');

function simplify_plan_columns($defaults) {
  unset($defaults['cb']);
  unset($defaults['department']);
  return $defaults;
}
add_filter('manage_edit-plans_columns', 'simplify_plan_columns');
add_filter('manage_edit-staract_columns', 'simplify_plan_columns');

//remove quick edit
function remove_quick_edit( $actions ) {
	unset($actions['inline hide-if-no-js']);
	return $actions;
}
add_filter('post_row_actions','remove_quick_edit',10,1);

/*****************************************************
 *
 *  Modifying the Program Link
 *
 *****************************************************/
function change_program_link() {
	global $menu;
	
	foreach($menu as $key => $link) {
		//if menu entry name is programs
		if($link[0] === "Programs"){
			//change the link to only show majors
			$menu[$key][2] = $link[2]."&amp;degree_level=major";
		}
	}
	
}
add_action('admin_menu', 'change_program_link', 2);