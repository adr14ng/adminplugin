<?php

/* * * * * * * * * * * * * * * * * * * * * *
 *
 *	Review
 *	
 * 	Creates the home page for editors and a 
 *	page with all review statuses for admin.
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */
 
//need to enable url fopen
//includes->dpadmin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

function add_review_menu()
{
	add_menu_page( 'Review Status', 'Review Status', 'read', 
				'review', 'review_page', '', 21 ); //need icon
}

//function that directs you to the correct view
function review_page() {	
	global $current_user, $wpdb;
		$role = $wpdb->prefix . 'capabilities';
		$current_user->role = array_keys($current_user->$role);
		$role = $current_user->role[0];
		
		if ('dp_editor' == $role || 'dp_college' == $role ){
			editor_home_page();
		}
		elseif ('administrator' == $role){
			adminstrator_review_page();
		}
		else{
			wp_die(__( 'You do not have permission to view this page.' ));
		}
}

//If you are a department editor, this creates the home page
function editor_home_page() {
	$option = get_option( 'main_dp_settings' );	//get our options (message & due date)
	$message = $option['welcome_message'];
?>
	<div class="wrap">
	
	<h2> Welcome to the 2014-2015 CSUN Catalog </h2>
	<p><?php echo $message; ?> <a href="http://csuncatalog.com/2014/guide/" target="_blank"> View training guide.</a></p> 
	
<?php
	
	$due = $option['review_deadline'];
	$user_id = get_current_user_id();
	$userCat = get_user_meta($user_id, 'user_cat');
	$userCat = $userCat[0];
	?>
	<table class="wp-list-table widefat" cellspacing="0">
		<thead> 
			<tr>
				<th scope="col" id="col_name" class="manage-column column-col_name" style=""> <span>Department</span> </th>
				<th scope="col" id="col_status" class="manage-column column-col_status" style=""><span>Status</span></th>
				<th scope="col" id="col_date" class="manage-column column-col_date" style=""><span>Deadline</span></th>
			</tr>
		</thead>
	<form name="review_status" action="<?php echo plugins_url().'/department-admin/includes/review.php'; ?>" method="post" id="review_status">
		<?php wp_nonce_field('update_review_status'); ?>
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
		<input type="hidden" name="return" value="<?php echo admin_url('admin.php?page=review'); ?>" />
		<input type="hidden" name="action" value="reviewed" />
	<tbody id="the-list">
	<?php
	$alt = false;
	
	foreach($userCat as $link) :
		$term_id = term_exists( $link );
		
		//Cleaned up term description holding department name
		$dp_name = term_description( $term_id, 'department_shortname' );
		$dp_name = strip_tags($dp_name);		//remove p tags
		$dp_name = trim(preg_replace('/\s\s+/', ' ', $dp_name));	//remove newline character
		
		$department_id = get_first_term_post($link)		
		//Output row ?>

		<tr <?php if($alt) echo 'class="alternate"'; $alt = !$alt; ?>>
			<td class="col_name column-col_name">
				<a class="row-title" href="<?php echo admin_url().'post.php?action=edit&post='.$department_id.'&department_shortname='.$link;?>">
					<?php echo $dp_name; ?>
				</a>
			</td>
			<td>
				<input type="submit" name="reviewed-<?php echo $term_id; ?>" <?php 
					if (get_field( 'reviewed', 'department_shortname_'.$term_id ))
						echo ' value="Review Complete" class="btn btn-reviewed">';
					else
						echo ' value="Submit for Review" class="btn">';
					?>
			<td>
				<?php echo $due; ?>
			</td>
		</tr>
	<?php endforeach; ?>

	</tbody></form></table></div>
<?}

function adminstrator_review_page() {
	$terms = get_terms( 'department_shortname', $args );

	?>
	<div class="wrap">
	
	<h2> Review Status </h2>
	<form name="review_status" action="<?php echo plugins_url().'/department-admin/includes/review.php'; ?>" method="post" id="review_status">
		<?php wp_nonce_field('update_review_status'); ?>
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
		<input type="hidden" name="return" value="<?php echo admin_url('admin.php?page=review'); ?>" />
		<input type="hidden" name="action" value="admin-review" />
	<input type="submit" name="clear-all" value="Clear All" class="btn btn-clear">
	<table class="wp-list-table widefat" cellspacing="0">
		<thead> 
			<tr>
				<th scope="col" id="col_name" class="manage-column column-col_name" style=""> <span>Academic Org</span> </th>
				<th scope="col" id="col_status" class="manage-column column-col_status" style=""><span>Status</span></th>
			</tr>
		</thead>
	<tbody id="the-list">
	<?php
	$alt = false;
	foreach($terms as $term) :

		//Output row ?>
		<tr <?php if($alt) echo 'class="alternate"'; $alt = !$alt; ?>>
			<td class="col_name column-col_name">
				<a class="row-title" href="<?php echo admin_url(); ?>admin.php?page=dp_page&department_shortname=<?php echo $link; ?>&action=edit">
					<?php echo $term->description; ?>
				</a>
			</td>
			<td>
				<input type="hidden" name="post-<?php echo $term->term_id; ?>" value="0" />
				<input type="submit" name="reviewed-<?php echo $term->term_id; ?>" <?php 
					if (get_field( 'reviewed', 'department_shortname_'.$term->term_id))
						echo ' value="Review Complete" class="btn btn-reviewed">';
					else
						echo ' value="Submit for Review" class="btn">';
					?>
		</tr>
	<?php endforeach; ?>

	</tbody>
	</table>
		<input type="submit" name="clear-all" value="Clear All" class="btn btn-clear">
	</form>
	</div>
<? }

//includes->dpadmin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

//Form action for reviewed status
if(isset($_POST['action']) && $_POST['action'] == "reviewed"){
	unset($_POST['action']);

	require_once($base_url.'/wp-admin/admin.php');

	$field_key = get_option( 'main_dp_settings');	//acf field key for review, may need to change
	$field_key = $field_key['review_field_key'];

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
//Form action for admin review (allows clear all)
else if(isset($_POST['action']) && $_POST['action'] == "admin-review"){
	unset($_POST['action']);

	require_once($base_url.'/wp-admin/admin.php');

	//acf field key for review, may need to change
	$field_key = get_option( 'main_dp_settings');	
	$field_key = $field_key['review_field_key'];

	//Security check
	check_admin_referer( 'update_review_status');

	//Clear All
	if(isset($_POST['clear-all'])) {
		foreach($_POST as $key => $value){
			$exp_key = explode('-', $key);
			if($exp_key[0] == 'post'){
				 $update_terms[$exp_key[1]] = $value;
			}
		}
		
		//Set all values to 0
		foreach($update_terms as $term_id => $value){
			update_field($field_key, $value, 'department_shortname_'.$term_id); //toggle to uncomplete
		}
	}
	//Toggle 1
	else{
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

			}
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
			
'.$dp_name.' has completed it\'s review of the catalog copy on '.$time.'. 

Please examine and approve any changes.
			
Thank you.';
	
	//Send the email
	wp_mail( $admin_email, $subject, $message);
}

?>