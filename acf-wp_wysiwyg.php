<?php

/*
Plugin Name: Advanced Custom Fields: WP WYSIWYG
Plugin URI: https://github.com/elliotcondon/acf-wordpress-wysiwyg-field
Description: Adds a native WordPress WYSIWYG field to the Advanced Custom Fields plugin. Please note this field does not work as a sub field.
Version: 1.0.2
Author: Elliot Condon
Author URI: http://www.elliotcondon.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
//load_plugin_textdomain( 'acf-wp_wysiwyg', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );


// Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_wp_wysiwyg( $version ) {

	include_once('acf-wp_wysiwyg-v5.php');

}

add_action('acf/include_field_types', 'include_field_types_wp_wysiwyg');	


// Include field type for ACF4
function register_fields_wp_wysiwyg() {

	include_once('acf-wp_wysiwyg-v4.php');

}

add_action('acf/register_fields', 'register_fields_wp_wysiwyg');	


// Include field type for ACF3
function init_wp_wysiwyg() {
	
	if( function_exists('register_field') ) {
		
		register_field('acf_field_wp_wysiwyg', dirname(__File__) . '/acf-wp_wysiwyg-v3.php');
	
	}

}
	
add_action('init', 'init_wp_wysiwyg');	

function force_save_revision($check_for_changes, $revision, $post)
{
	if(!$check_for_changes)
		return false;
			
	global $wpdb;
	$results = $wpdb->get_results(
		"
		SELECT meta_value 
		FROM $wpdb->postmeta
		WHERE meta_value LIKE '%wp_wysiwyg%'
		"
	);
	
	foreach($results as $result)
	{
		preg_match('/s:3:"key";s:19:"(field_[A-Za-z0-9]{13})"/', $result->meta_value, $matches);
				
		if(!isset($_POST['fields'][$matches[1]]))	//this post doesn't have this field
			continue;
		
		$new = $_POST['fields'][$matches[1]];
		$new = str_replace('\"', '"', $new);
		$prev = get_field($matches[1], $post->ID, false);
		
		if ( normalize_whitespace($prev) != normalize_whitespace($new) ) 
		{
			return false;
		}
	}
	
	return true;
}
add_filter('wp_save_post_revision_check_for_changes', 'force_save_revision', 10, 3);

?>