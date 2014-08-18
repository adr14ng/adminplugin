<?php
/** * * * * * * * * * * * * * * * * * * * *
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
 
 /* * * * * * * * * * * * * * * * * * * * * *
 *
 *  Including the styles and js
 *
 * * * * * * * * * * * * * * * * * * * * * */
/**
 * Include custom styles for admissions
 * Hooks onto admin_enqueue_scripts action.
 */
 function add_ar_style() {
	$basedir = dirname(plugin_dir_url(__FILE__));
	wp_enqueue_style('ar-style', $basedir . '/css/admissions-style.css');
}
add_action('admin_enqueue_scripts', 'add_ar_style');

/* * * * * * * * * * * * * * * * * * * * * *
 *
 *  Editing the admin bar
 *
 * * * * * * * * * * * * * * * * * * * * * */
/**
 * Remove admin bar links
 * Hooks onto admin_bar_menu action.
 *
 * @param WP_Admin_Bar $wp_admin_bar Wordpress admin bar
 */
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

/* * * * * * * * * * * * * * * * * * * * * *
 *
 *  Editing the list tables
 *
 * * * * * * * * * * * * * * * * * * * * * */
/**
 * Remove extra collumns from list table
 * Hooks onto manage_${post_type}_posts_columns filter
 *
 * @param array $defaults Default collumn list
 *
 * @return array	Simplified collumn list
 */
function simplify_admissions_post_columns($defaults) {
  unset($defaults['comments']);
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['tags']);
  unset($defaults['level']);
  return $defaults;
}
add_filter('manage_${post_type}_posts_columns', 'simplify_admissions_post_columns');

/**
 * Remove extra collumns from program list table
 * Hooks onto manage_edit-programs_columns filter
 *
 * @param array $defaults Default program collumn list
 *
 * @return array	Simplified program collumn list
 */
function simplify_program_columns($defaults) {
  unset($defaults['cb']);
  return $defaults;
}
add_filter('manage_edit-programs_columns', 'simplify_program_columns');

/**
 * Remove extra collumns from plan and staract list tables
 * Hooks onto manage_edit-courses_columns filter, manage_edit-staract_columns filter
 *
 * @param array $defaults Default plan and staract collumn list
 *
 * @return array	Simplified plan and staract collumn list
 */
function simplify_plan_columns($defaults) {
  unset($defaults['cb']);
  return $defaults;
}
add_filter('manage_edit-plans_columns', 'simplify_plan_columns');
add_filter('manage_edit-staract_columns', 'simplify_plan_columns');

/**
 * Remove quick edit links
 * Hooks onto post_row_actions filter.
 *
 * @param array $actions	List of available actions
 *
 * @return array	Updated list of available actions
 */
function remove_quick_edit( $actions ) {
	unset($actions['inline hide-if-no-js']);
	return $actions;
}
add_filter('post_row_actions','remove_quick_edit',10,1);

/* * * * * * * * * * * * * * * * * * * * * *
 *
 *  Modifying the Program Link
 *
 * * * * * * * * * * * * * * * * * * * * * */
/**
 * Modifies program link to show only majors
 * Hooks onto admin_menu action.
 */
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