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
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-dashboard" class="ab-label">Home</span>',
			'href' => admin_url(),
			);
	$wp_admin_bar->add_node( $args );	//add dashboard link
	
	if(isset($_REQUEST['department_shortname'])){
		$cat = $_REQUEST['department_shortname'];
		
		$wp_admin_bar->add_node(array(
			'id' => 'csun_progam_link',
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-programs" class="ab-label">Programs</span>',
			'href' => admin_url().'admin.php?page=dp_page&department_shortname='.$cat.'&action=edit',
			));
		
		$wp_admin_bar->add_node(array(
			'id' => 'csun_course_link',
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-courses" class="ab-label">Courses</span>',
			'href' => admin_url().'edit.php?post_type=courses&department_shortname='.$cat.'&orderby=title&order=asc',
			));
			
		$wp_admin_bar->add_node(array(
			'id' => 'csun_file_link',
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-files" class="ab-label">Files</span>',
			'href' => admin_url().'admin.php?page=proposals&department_shortname='.$cat,
			));
	}
	
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
 *  Editing the dashboard
 *
 *****************************************************/
 
//setting up dashboard widgets
function csun_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
 	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	add_meta_box('csun_dashboard_links', 'What to Edit', 'csun_links_widget', 'dashboard', 'side', 'high');
	add_meta_box('csun_dashboard_welcome', 'Welcome', 'csun_welcome_widget', 'dashboard', 'normal', 'high');
}
add_action('wp_dashboard_setup', 'csun_dashboard_widgets');

//Widget that lists departments a user has access to edit
function csun_links_widget() {
	$user_id = get_current_user_id();
	$userCat = get_user_meta($user_id, 'user_cat');
	$userCat = $userCat[0];
	
	foreach($userCat as $link) {
		echo '<a href="'.admin_url().'admin.php?page=dp_page&department_shortname='.$link.'&action=edit">';//get link
		echo '<button type="button" class="btn btn-primary">';
		echo strtoupper($link);//cat name
		echo '</button>';
		echo '</a>';
	}
}

//Widget that displays helpful text to a department editor
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
	remove_meta_box('department_shortnamediv', 'courses', 'side');
	remove_meta_box('general_educationdiv', 'courses', 'side');
	
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
  unset($defaults['department']);
  unset($defaults['ge']);
  return $defaults;
}
add_filter('manage_${post_type}_posts_columns', 'simplify_post_columns');

function simplify_course_columns($defaults) {
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['date']);
  unset($defaults['department']);
  unset($defaults['ge']);
  return $defaults;
}
add_filter('manage_edit-courses_columns', 'simplify_course_columns');

//remove quick edit
function remove_quick_edit( $actions ) {
	unset($actions['inline hide-if-no-js']);
	return $actions;
}
add_filter('post_row_actions','remove_quick_edit',10,1);

/*****************************************************
 *
 *	Custom Messages
 *
 ****************************************************/
 
 add_action('admin_footer', 'editor_admin_footer');
function editor_admin_footer()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

    $message = NULL;

    if ($uri AND strpos($uri,'edit.php'))
    {
		//$message = 'Course Message';
        $message = 'Select a course to update. Click on the links above to navigate to home, edit the programs, or view the approved circulum proposals of this department.';
    }

    if ($message)
    {
        ?><script>
            jQuery(function($)
            {
                $('<div id="course_message"><p></p></div>').text('<?php echo $message; ?>').insertAfter('#wpbody-content .wrap h2:eq(0)');
            });
        </script><?php
    }
}

?>