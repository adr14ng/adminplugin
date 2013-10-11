<?php
/**
 * Builds the display table for all departments
 * Heavily based on the terms list table because we are essentially listing
 * taxonomy terms and linking them to an edit page
*/
class Aggregate_List_Table extends WP_List_Table {
	
	var $callback_args;
	
	function __construct() {
		global $taxonomy, $tax;
		
		parent::__construct( array(
			'singular'=> 'wp_list_aggregate',
			'plural'=>'wp_list_aggregates',
			'ajax' => false
			));
			
		$taxonomy = 'department_shortname';
		$tax = get_taxonomy( $taxonomy );
	}
	
	//Adding navigation to the top and bottom of the table
	function extra_tablenav( $which ) {
		if( $which == 'top' ) {
			echo '<h3> Table Top </h3>';
		}
		elseif ( $which == 'bottom' ) {
			echo '<h3> Table Bottom </h3>';
		}
	}
	
	//Add collumns
	function get_columns() {
		return $columns= array(
			'col_name'=>__('Name'),
			'col_descrip'=>__('Description'),
			'col_posts'=>__('Items')
		);
	}
	
	//Sortable columns
	function get_sortable_columns() {
		return $sortable = array(
			'col_name' => 'name',
			'col_posts' => 'count'
		);
	}
		
	//Prepare the data table
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		$taxonomy = 'department_shortname';
		
		$args = array(); 
		
		if( !empty( $_REQUEST['s'] ) )
			$args['search'] = trim( wp_unslash( $_REQUEST['s'] ) );
		
		//Get any ordering
		if ( !empty( $_REQUEST['orderby'] ) )
			$args['orderby'] = trim( wp_unslash( $_REQUEST['orderby'] ) );

		if ( !empty( $_REQUEST['order'] ) )
			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
			
		$terms = get_terms( $taxonomy, $args );
		
		
		//Pagination Set up
		$totalitems = count($terms);
		$perpage = 20; //eventually make customizable?
		$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
		if(empty($paged) || !is_numeric($paged) || $paged<=0 )
			$paged=1;
		$totalpages = ceil($totalitems/$perpage);
		if(!empty($paged) && !empty($perpage)){
		    $args['offset']=($paged-1)*$perpage;
    		$args['number'] = $perpage;
	    }
		
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page" => $perpage,
		) );
		
		// Register the Columns
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		//Get the data
		$this->items = get_terms( $taxonomy, $args );
	}

	function display_rows() {
		$records = $this->items;
	
		list( $columns, $hidden ) = $this->get_column_info();

		//Loop for each record
		if(!empty($records)){foreach($records as $rec){

			//Open the line
			echo '<tr id="aggr-'.$rec->slug.'">';
			foreach ( $columns as $column_name => $column_display_name ) {

				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				//edit link
				$editlink  = get_aggregate_edit_link($rec->slug);

				//Display the cell
				switch ( $column_name ) {
					case "col_name":	echo '<td '.$attributes.'><a href="'.$editlink.'">'.$rec->name.'</a></td>';	break;
					case "col_descrip": echo '<td '.$attributes.'>'.$rec->description.'</td>'; break;
					case "col_posts": echo '<td '.$attributes.'>'.$rec->count.'</td>'; break;
				}
			}

			//Close the line
			echo'</tr>';
		}}
	}
}

?>