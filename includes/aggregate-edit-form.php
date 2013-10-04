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
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0)
			$posts = get_objects_in_term( $term_id, 'department_shortname' );
		else
			wp_die(__( 'Department does not exist' ));					
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

//Returns a link to the aggregate edit page
//Used for building the table and redirects
function get_aggregate_edit_link($cat, $context) {
	$sformat = 'dp_page.php?cat=%s';
	
	if( 'display' == $context)
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat))
	
}

//Builds the display table for all departments
//Heavily based on the terms list table because we are essentially listing
//taxonomy terms and linking them to an edit page
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
			'col_aggr_posts' => __('Posts'),
			'col_aggr_date' => __('Last Updated'),
		);
	}
	
	//Sortable collumns
	function get_sortable_columns() {
		return $sortable = array('col_aggr_name' => 'name',
								 'col_aggr_posts' => 'count');
	}

	
	//Prepare the data table
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$tags_per_page = 20; //eventually make customizable?
		$screen = get_current_screen();
		
		$args = array( 'page' => $this-get_pagenum(),
					   'number' => $tags_per_page);
		
		//Get any ordering
		if ( !empty( $_REQUEST['orderby'] ) )
			$args['orderby'] = trim( wp_unslash( $_REQUEST['orderby'] ) );

		if ( !empty( $_REQUEST['order'] ) )
			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
			
		$this->callback_args = $args;
		
		$this->set_pagination_args( array(
			'total_items' => wp_count_terms( 'department_shortname', compact( 'search' ) ),
			'per_page' => $tags_per_page,
		) );
	}
	
	function display_row_or_placeholder() {
		$taxonomy = 'department_shortname';
		//Merges set arguments with default ones
		$args = wp_parse_args( $this->callback_args, array('page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 0
			) );
		
		extract( $args, EXTR_SKIP );
		$args['offset'] = $offset = ( $page - 1 ) * $number;
		
		//Convert to table rows
		$count = 0;
		
		if( !isset( $orderby ) ) {
			$args['number'] = $args['offset'] = 0;
		}
		
		$terms = get_terms( $taxonomy, $args );
		
		//Empty table view
		if ( empty( $terms ) ) {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}
		
		if( !isset( $orderby ) ) {
			$children = _get_term_hierarchy( $taxonomy );

			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within
			$this->_rows( $taxonomy, $terms, $children, $offset, $number, $count );
		} else {
			$terms = get_terms( $taxonomy, $args );
			foreach ( $terms as $term )
				$this->single_row( $term );
				
			$count = $number; // Only displaying a single page.
		}
	}
	
	//Fix pages and hierarchy
	function _rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ) {

		$end = $start + $per_page;

		foreach ( $terms as $key => $term ) {

			if ( $count >= $end )
				break;

			if ( $term->parent != $parent && empty( $_REQUEST['s'] ) )
				continue;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $term->parent > 0 && empty( $_REQUEST['s'] ) ) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;
				while ( $p ) {
					$my_parent = get_term( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					if ( in_array( $p, $parent_ids ) ) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset( $parent_ids );

				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					echo "\t";
					$this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start ) {
				echo "\t";
				$this->single_row( $term, $level );
			}

			++$count;

			unset( $terms[$key] );

			if ( isset( $children[$term->term_id] ) && empty( $_REQUEST['s'] ) )
				$this->_rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
		}
	}
	
	//single_row
	
}

?>