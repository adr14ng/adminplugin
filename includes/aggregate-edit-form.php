<?php

require_once(admin_url( '/admin.php')); 

function add_aggregate_menu()
{
	add_menu_page( 'Edit Department Page', 'Department Pages', 'edit_posts', 
				'dp_page', 'aggregate_post', $icon, 20 ); //need icon
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
	else if(count($userCat == 1)){ //if there is only one possible category page
		$userCat = $userCat[0];
		//redirect to the proper cat page
		wp_redirect(get_aggregate_edit_link($userCat, ''));
		exit;
	}
	else //if there are multiple pages, list them
	{
		list_aggregate_post();
	}

}

//if user has more than one category, list department pages
function list_aggregate_post() {
	if(!class_exists('WP_List_Table')){
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	
	//Do I need to set up something else?
	
	//Create and display the aggregate list table
	$aggr_list_table = new Aggregate_List_Table();
	$aggr_list_table->prepare_items();
	$aggr_list_table->display();
}

//?cat='dpt_short_name'?action=edit
function edit_aggregate_post(){
	/******************************************
	 * Get posts for category
	 *****************************************/
	 if($post_cat = $_REQUEST['cat'] ){
		$posts = get_posts( array('category' => $post_cat,
								  'orderby' => 'meta_value',
								  'meta_key' => 'dep_order'));	//need to create that
								
		}
	else
		wp_die(__( 'Not enough information' ));
		
	if( !$posts )
		wp_die(__( 'No posts in this category' ));
		
	/********************************************
	 * Build Overall Page
	 ********************************************/
	$action ='edit';

	 
	 
	/*********************************************
	 * Build Form for each post
	 ********************************************/
		//need to wrap in a div to hide/show
		//need to edit form name HOOK: do_action('post_edit_form_tag', $post);
	foreach $posts as $post {
		$post_ID = $post->ID;
		$post_type = $post->post_type;
		
		include(admin_url('/edit-form-advanced.php'));

	}
}

function get_aggregate_edit_link($cat, $context) {
	$sformat = 'dp_page.php?cat=%s';
	
	if( 'display' == $context)
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat))
	
}


class Aggregate_List_Table extends WP_List_Table {
	
	function __construct() {
		parent::__construct( array(
			'singular'=> 'wp_list_aggregate',
			'plural'=>'wp_list_aggregates',
			'ajax' => false
			));
	}
	
	//Adding navigation to the top and bottom of the table
	function extra_tablenav( $which ) {
		if( $which == 'top' ) {
			//code before table goes here
		}
		elseif ( $which == 'bottom' ) {
			//code after table goes here
		}
	}
	
	//The collumns across the top and bottom
	function get_collumns() {
		return $collumns = array(
			'col_aggr_name' => __('Name'),
			'col_aggr_descrip' => __('Description'),
			'col_aggr_pages' => __('Pages')
		);
	}
	
	//Sortable collumns
	function get_sortable_columns() {
		return $sortable = array('col_aggr_name' => 'category');
	}

	
	//Prepare the data table
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		
		$categories = get_categories( array ( 
				'type' 		=> 'post',
				'child_of'	=> 5,	//Need to edit based on department category id
				'hide_empty' => 1)	//Not sure this is allowed
				);
	}
	
}

?>