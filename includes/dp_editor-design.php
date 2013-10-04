<?php

$basedir = dirname(dirname(__FILE__));	//base directory to get to design files

/*****************************************************
 *
 *  Including the styles and js
 *
 ****************************************************/
function add_dp_style() {
	wp_enqueue_style('dp-editor-style', $basedir . '/css/dp-editor-style.css');
}
add_action('admin_enqueue_scripts', 'add_dp_style');


/*****************************************************
 *
 *  Editing the menu
 *
 *****************************************************/

//remove update notice
add_filter( 'pre_site_transient_update_core', function(){return null;} ); 
 
//Remove menu options
function remove_menu_items() {
	global $menu;
	$restricted = array(__('Links'), __('Comments'), __('Media'),
	__('Plugins'), __('Tools'), __('Users'), __('Settings')), __('Appearance');
	end ($menu);
	
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){
			unset($menu[key($menu)]);}
		}
	}
}
add_action('admin_menu', 'remove_menu_items');


/*****************************************************
 *
 *  Editing the dashboard
 *
 *****************************************************/
 
//Remove extraneous dashboard widgets
function remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
 	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');



/*****************************************************
 *
 *  Editing the advanced edit form
 *
 *****************************************************/
 
//Remove meta-boxes
function remove_meta_boxes() {
	remove_meta_box('formatdiv', 'post', 'normal');
	remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
	remove_meta_box('postimagediv', 'post', 'normal');
	
}
add_action('admin_init', 'remove_meta_boxes');


/*****************************************************
 *
 *  Editing the edit list
 *
 *****************************************************/
 
//remove extra info in list
function simplify_post_columns($defaults) {
  unset($defaults['comments']);
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['date']);
  unset($defaults['tags']);
  return $defaults;
}
add_filter('manage_${post_type}_posts_columns', 'simplify_post_columns');

?>