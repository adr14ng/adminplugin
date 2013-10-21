<?php
/**
 * Edit pages for Aggregate Editing
 * Includes list page and edit page
 */
 
//need to enable url fopen
//$admin_url = admin_url('/wp-admin');
//includes->dpadmin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
//require_once($base_url.'/wp-admin/admin.php');

function add_aggregate_menu()
{
	add_menu_page( 'Edit Department Page', 'View All', 'edit_posts', 
				'dp_page', 'aggregate_post', $icon, 19 ); //need icon
}

//function that generates the aggregate post page
function aggregate_post() {
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$userCat = get_user_meta($user_id, 'user_cat');
	
	if(isset($_REQUEST['cat'])) //if we already have the category page request
	{
		edit_aggregate_post();
	}
	/*else if(count($userCat == 1)){ //if there is only one possible category page
		$userCat = $userCat[0];
		//redirect to the proper cat page
		wp_redirect(get_aggregate_edit_link($userCat, ''));
		exit;
	}*/
	else //if there are multiple pages, list them
	{
		list_aggregate_post();
	}
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

//Creates the edit page where all posts are edited
function edit_aggregate_post(){
    global  $post, $pagenow, $typenow;
    
    $pagenow = 'post.php'; //mimicking post page
	/******************************************
	 * Get posts for category
	 *****************************************/
	 if($post_cat = $_REQUEST['cat'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){
			$args=array(
				'post_type' => array('dp_department', 'dp_program'),
				'post__not_in' => $ids, // avoid duplicate posts
				'department_shortname' => $post_cat,
			);
			
			$posts = get_posts( $args ); 
		}
		else{
			wp_die(__( 'Department does not exist' ));
		}
	}
	else
		wp_die(__( 'Not enough information' ));
		
	if( !$posts )
		wp_die(__( 'No posts in this category' ));
		
	/********************************************
	 * Build Overall Page
	 ********************************************/
	$action ='edit';
	wp_enqueue_script('post');
	
	if ( wp_is_mobile() )
		wp_enqueue_script( 'jquery-touch-punch' );

	$isFirst = true;
	echo '<ul id="edit-tabs" class="nav nav-tabs">';
	foreach($posts as $post) {
		$post_ID = $post->ID;
		$post_name = $post->post_title;
		if($isFirst){
			$isFirst = false;
			echo '<li class="active">';
		}
		else
			echo '<li>';
		
		echo '<a href="#custom-edit-'.$post_ID.'" data-toggle="tab">'.$post_name.'</a></li>';
	}	
	echo'</ul><div class="tab-content"> ';
	
	/*********************************************
	 * Build Form for each post
	 ********************************************/
	$isFirst = true;
	foreach ($posts as $post) {
		$post_ID = $post->ID;
		$typenow = $post_type = $post->post_type;
		
		?>
		<div id="custom-edit-<?php echo $post_ID?>" class="csun-edit-form tab-pane fade<?php if($isFirst){ echo ' in active'; $isFirst = false;}?>">
		<?php
		include('edit-form.php');
		?>
		</div>
		<?php
	}
	
	echo '</div>';
}

//Returns a link to the aggregate edit page
//Used for building the table and redirects
function get_aggregate_edit_link($cat, $context='') {
	$sformat = 'admin.php?page=dp_page&cat=%s';
	
	if( 'display' == $context)
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat));
}

//Makes the default edit link this one
function filter_aggregate_edit_link($url, $post, $context)
{
	$cat =  wp_get_post_terms( $post, 'department_shortname');
	$post_type = get_post_type( $post );

	if($cat && ($post_type == 'dp_department' || $post_type == 'dp_program')){
		$cat = $cat[0];
		$cat_name = $cat->slug;
		
		$url = get_aggregate_edit_link($cat_name, $context);
	}
	return $url;
}
add_filter( 'get_edit_post_link', 'filter_aggregate_edit_link', '99', 3 );


//Fixes the name change of content field
//Does not apply filters
function dp_edit_post($data, $postarr) {
	$contentName= 'content'.$postarr['post_ID'];
	
	if(isset( $postarr[$contentName])) {
		 $data['post_content']= $postarr[$contentName];
		unset( $post_data[$contentName]);
	}
	
	return $data;
}
add_filter( 'wp_insert_post_data', 'dp_edit_post', '99' , 2);

?>