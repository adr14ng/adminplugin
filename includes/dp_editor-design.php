<?php


/*****************************************************
 *
 *  Editing the dashboard
 *
 *****************************************************/

//Form action for reviewed status
if(isset($_POST['action']) && $_POST['action'] == "reviewed"){
	unset($_POST['action']);

	$field_key = 'field_52ab9ffcbb332';		//acf field key for review, may need to change

	//includes->dpadmin->plugs->wp-content->base
	$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
	require_once($base_url.'/wp-admin/admin.php');

	//Security check
	check_admin_referer( 'update_review_status');

	
	//Get all fields starting with reviewed- and their id
	foreach($_POST as $key => $value){
		$exp_key = explode('-', $key);
		if($exp_key[0] == 'reviewed'){
			 $update_terms[$exp_key[1]] = $value;
		}
	}
	
	//Update the values and send email when review is complete
	foreach($update_terms as $term_id => $value){
		if( $value === 'Review Complete')  //value based on form value
			update_field($field_key, 0, 'department_shortname_'.$term_id); //toggle to uncomplete
		else {
			update_field($field_key, 1, 'department_shortname_'.$term_id); //toggle to complete
			
			email_admin_review($term_id);	//notify admin
		}
	}
	
	//Redirect back to page
	if(isset($_POST['return']))
		wp_redirect( $_POST['return'] );
	else
		wp_redirect( admin_url() );
}

//Email admin to notify that a review is complete
function email_admin_review($term_id) {
	//Admin email from settings
	$admin_email = get_bloginfo('admin_email');
	
	//Cleaned up term description holding department name
	$dp_name = term_description( $term_id, 'department_shortname' );
	$dp_name = strip_tags($dp_name);		//remove p tags
	$dp_name = trim(preg_replace('/\s\s+/', ' ', $dp_name));	//remove newline character
	
	//Date in month, day, year format
	$time = date('m-d-Y');
	
	$subject = $dp_name.' Review Completed';
	$message = 'Hello Catalog Editor,
			
'.$dp_name.' has completed it\'s review of the catalog copy on'.$time.'. 

Please examine and approve any changes.
			
Thank you.';
	
	//Send the email
	wp_mail( $admin_email, $subject, $message);
}


//setting up dashboard widgets
function csun_dashboard_widgets() {
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
 	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	wp_add_dashboard_widget( 'csun_dashboard', 'Welcome', 'csun_custom_widget' );
}
add_action('wp_dashboard_setup', 'csun_dashboard_widgets');

//Widget that lists departments a user has access to edit
function csun_custom_widget() {?>
	<h2> Welcome to the CSUN Catalog </h2>
	<p>You can edit your department information by clicking the links below.</p>
	<p>Click on "Update" to save.</p>
	<p>When you are finished update your review status by clicking the button. </p>
	<p>Thank you. </p>
	
<?php
	$due = "2/15/2014";
	$user_id = get_current_user_id();
	$userCat = get_user_meta($user_id, 'user_cat');
	$userCat = $userCat[0];
	?>
	<table>
	<thead> <tr><th> Department </th><th>Status</th><th>Deadline</th></thead>
	<form name="review_status" action="<?php echo plugins_url().'/department-admin/includes/dp_editor-design.php'; ?>" method="post" id="review_status">
		<?php wp_nonce_field('update_review_status'); ?>
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
		<input type="hidden" name="return" value="<?php echo admin_url(); ?>" />
		<input type="hidden" name="action" value="reviewed" />
	<tbody>
	<?php
	foreach($userCat as $link) {
		$term_id = term_exists( $link );
		echo '<tr><td>';
		echo '<a href="'.admin_url().'admin.php?page=dp_page&department_shortname='.$link.'&action=edit">';//get link
		/*echo '<button type="button" class="btn btn-primary">';
		echo strtoupper($link);//cat name
		echo '</button>';
		*/
		echo term_description( $term_id, 'department_shortname' );
		echo '</a>';
		echo '</td><td>';
		echo '<input type="submit"';
		if (get_field( 'reviewed', 'department_shortname_'.$term_id ))
			echo ' value="Review Complete" name="reviewed-'.$term_id.'" class="btn btn-reviewed">';
		else
			echo ' value="Review Pending" name="reviewed-'.$term_id.'" class="btn btn-pending">';
			
		echo '<br />';
		echo '<td>'.$due.'</td></tr>';
	}

	echo '</tbody></form></table>';
}


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