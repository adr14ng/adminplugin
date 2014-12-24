<?php

/** * * * * * * * * * * * * * * * * * * * *
 *
 *	Review
 *	
 * 	Creates the home page for editors and a 
 *	page with all review statuses for admin.
 *
 *  Processes review status change forms.
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */
 

//includes->dpadmin->plugs->wp-content->wordpress
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

/**
 * Creates the menu link for the review page
 * Hooks onto admin_menu action.
 */
function add_review_menu()
{
	add_menu_page( 'Review Status', 'Review Status', 'read', 
				'review', 'review_page', '', 21 ); //need icon
}

/**
 * Directs user to the correct view per user role
 */
function review_page() {	
	global $current_user, $wpdb;
	$role = $wpdb->prefix . 'capabilities';
	$current_user->role = array_keys($current_user->$role);
	$role = $current_user->role[0];
		
	if ('dp_editor' == $role || 'dp_college' == $role || 'dp_reviewer' == $role ){
		editor_home_page();
	}
	elseif ('administrator' == $role){
		adminstrator_review_page();
	}
	else{
		wp_die(__( 'You do not have permission to view this page.' ));
	}
}

/**
 * Creates department editor home page (review page)
 */
function editor_home_page() {
	$option = get_option( 'main_dp_settings' );	//get our options (message & due dates)
	$message = $option['welcome_message'];
?>
	<div class="wrap">
	
	<?php echo $message; ?>
	
<?php
	
	$due = $option['review_deadline'];
	$college_due = $option['college_deadline'];
	$user_id = get_current_user_id();
	$userCat = get_user_meta($user_id, 'user_cat');
	$userCat = $userCat[0];		//the categories associated with the user determine which departments they can edit
	?>
	<table class="wp-list-table widefat" cellspacing="0">
		<thead> 
			<tr>
				<th scope="col" id="col_name" class="manage-column column-col_name" style=""> <span>Department</span> </th>
				<th scope="col" id="col_status" class="manage-column column-col_status" style="">
					<span>Department Deadline:</span><br /><strong><?php echo $due; ?></strong> </th>
				<th scope="col" id="col_date" class="manage-column column-col_date" style="">
					<span>College Deadline:</span><br /><strong><?php echo $college_due; ?></strong> </th>
			</tr>
		</thead>
	<tbody id="the-list">
	<?php
	$alt = false;
	
	foreach($userCat as $link) :
		$term = get_term_by( 'slug', $link, 'department_shortname' );
		
		//department name
		$dp_name = $term->description;
		//the department post associated with department_shortname
		$department_id = get_department($link)
		//Output row ?>

		<tr <?php if($alt) echo 'class="alternate"'; $alt = !$alt; ?>>
			<td class="col_name column-col_name">
				<a class="row-title" href="<?php echo admin_url().'post.php?action=edit&post='.$department_id.'&department_shortname='.$link;?>">
					<?php echo $dp_name; ?>
				</a>
			</td>
			<td>
			<?php if(review_submitted($link, 'false')) : ?>
				<button name="reviewed-<?php echo $term_id; ?>" class="btn btn-reviewed disabled">Review Complete</button>
			<?php else: ?>
				<a href="<?php echo site_url('complete/').'?department_shortname='.$link.'&dean=false'; ?>">
					<button name="reviewed-<?php echo $term_id; ?>" class="btn">Submit for Review</button>
				</a>
			<?php endif; ?>
			</td>
			<td>
			<?php if (review_submitted($link, 'true')) : ?>
				<button name="college-<?php echo $term_id; ?>" class="btn btn-reviewed disabled">Review Complete</button>
			<?php else: ?>
				<a href="<?php echo site_url('complete/').'?department_shortname='.$link.'&dean=true' ?>">
					<button name="college-<?php echo $term_id; ?>" class="btn">Submit for Review</button>
				</a>
			<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>

	</tbody></table></div>
<?}

/**
 * Creates administration view of review page 
 * Lists all departments and current review statuses
 */
function adminstrator_review_page() {
	$terms = get_terms( 'department_shortname', array('exclude_tree' => array(511) ) );	//get terms except the X-Don't use ones

	?>
	<div class="wrap">
	
	<h2> Review Status </h2>
	<table class="wp-list-table widefat" cellspacing="0">
		<thead> 
			<tr>
				<th scope="col" id="col_name" class="manage-column column-col_name" style=""> <span>Academic Org</span> </th>
				<th scope="col" id="col_department" class="manage-column column-col_status" style=""><span>Department</span></th>
				<th scope="col" id="col_college" class="manage-column column-col_status" style=""><span>College</span></th>
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
				<?php $entry = review_submitted($term->slug, "false");
				if($entry) : ?>
				<a href="<?php echo $entry; ?>">
					<button name="reviewed-<?php echo $term->term_id; ?>" class="btn btn-reviewed">Review Complete</button>
				</a>
				<?php else: ?>
				<button name="reviewed-<?php echo $term->term_id; ?>" class="btn disabled">Not Submitted</button>
				<?php endif; ?>
			</td>
			<td>
				<?php $entry = review_submitted($term->slug, "true");
				if($entry) : ?>
				<a href="<?php echo $entry; ?>">
					<button name="college-<?php echo $term->term_id; ?>" class="btn btn-reviewed">Review Complete</button>
				</a>
				<? else: ?>
				<button name="college-<?php echo $term->term_id; ?>" class="btn disabled">Not Submitted</button>
				<? endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>

	</tbody>
	</table>
	</div>
<? }

/**
 *	Checks if there is a current review submitted for this particular entry.
 *
 *	@param	string	$dept	The department shortcode
 *	@param	string	$dean	"true" if this is the college, "false" if department
 *
 *	@return	bool|string		False if not reviewed, link to form entry otherwise
 */
function review_submitted($dept, $dean)
{
	//only want active forms
	$search_criteria["status"] = "active";
	//field 1 of the review form is the department shortname
	$search_criteria["field_filters"][] = array("key" => "1", "value" => $dept);
	//field 3 of the review form is the college/department select
	$search_criteria["field_filters"][] = array("key" => "3", "value" => $dean);
	
	//Gravity forms API get entries
	//http://www.gravityhelp.com/documentation/page/API_Functions#get_entries
	//the review form is ID 3
	$entries = GFAPI::get_entries(3, $search_criteria);
	
	//if we have any entries get their url
	if(count($entries) > 0)
	{
		$url = admin_url('/admin.php?page=gf_entries&view=entry&id=3').'&lid='.$entries[0]['id'];
		return $url;
	}
	else
		return false;
}

?>