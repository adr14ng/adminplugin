<?php


/*****************************************************
 *
 *  Including the styles and js
 *
 ****************************************************/
function add_dp_style() {
	$basedir = dirname(plugin_dir_url(__FILE__));
	wp_enqueue_style('dp-editor-style', $basedir . '/css/dp-editor-style.css');
}
add_action('admin_enqueue_scripts', 'add_dp_style');


/*****************************************************
 *
 *  Editing the adminbar
 *
 *****************************************************/
function add_csun_admin_bar_links( $wp_admin_bar ) {
	$args = array(
			'id' => 'csun_dashboard_link',
			'title' => __( 'Dashboard'),
			'href' => admin_url(),
			);
	$wp_admin_bar->add_node( $args );
	
	$wp_admin_bar->remove_node( 'comments' );
	$wp_admin_bar->remove_node( 'new-content' );
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'site-name' );
	$wp_admin_bar->remove_node( 'edit-profile' );
	$wp_admin_bar->remove_node( 'user-info' );
}
add_action( 'admin_bar_menu', 'add_csun_admin_bar_links', 999 );

/*****************************************************
 *
 *  Editing the menu
 *
 *****************************************************/

//remove update notice
add_filter( 'pre_site_transient_update_core', function(){return null;} ); 
 
/*/Remove menu options
function remove_menu_items() {
	global $menu;
	$restricted = array(__('Links'), __('Comments'), __('Media'),
	__('Plugins'), __('Tools'), __('Users'), __('Settings'), __('Appearance'), __('Posts'));
	end ($menu);
	
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){
			unset($menu[key($menu)]);
		}
	}
}
add_action('admin_menu', 'remove_menu_items');*/


/*****************************************************
 *
 *  Editing the dashboard
 *
 *****************************************************/
 
//setting up dashboard widgets
function csun_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
 	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	add_meta_box('csun_dashboard_links', 'What to Edit', 'csun_links_widget', 'dashboard', 'side', 'high');
	add_meta_box('csun_dashboard_welcome', 'Welcome', 'csun_welcome_widget', 'dashboard', 'normal', 'high');
}
add_action('wp_dashboard_setup', 'csun_dashboard_widgets');

function csun_links_widget() {
	$user_id = get_current_user_id();
	$userCat = get_user_meta($user_id, 'user_cat');
	$userCat = $userCat[0];
	
	foreach($userCat as $link) {
		echo '<a href="'.admin_url().'/admin.php?page=dp_page&cat='.$link.'&action=edit">';//get link
		echo '<button type="button" class="btn btn-primary">';
		echo strtoupper($link);//cat name
		echo '</button>';
		echo '</a>';
	}
}

function csun_welcome_widget() { ?>
<h2> Welcome to the CSUN Catalog </h2>
<p>You can edit your department information by clicking the links on the right.</p>
<p>Click on "Update" to save.</p>
<p>Thank you. </p>
<?php }


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