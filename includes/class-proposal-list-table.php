<?php
/** * * * * * * * * * * * * * * * * * * * *
 *
 *	Proposal List Table
 *	
 * 	Builds the display table for all 
 *	departments. Heavily based on the terms 
 *	list table because we are essentially 
 *	listing taxonomy terms and linking them 
 *	to a list of files
 *
 * 	@author CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */

class Proposal_List_Table extends WP_List_Table {
	
	var $callback_args;
	
	function __construct() {
		global $taxonomy, $tax;
		
		parent::__construct( array(
			'singular'=> 'wp_list_proposal',
			'plural'=>'wp_list_proposals',
			'ajax' => false
			));
			
		$taxonomy = 'department_shortname';
		$tax = get_taxonomy( $taxonomy );
	}
	
	/**
	 * Provides list of collumns for the table
	 *
	 * @return array Collumns included in table
	 */
	function get_columns() {
		return $columns= array(
			'col_name'=>__('Name'),
			'col_descrip'=>__('Description')
		);
	}
	
	/**
	 * Provides list of sortable collumns for the table
	 *
	 * @return array Sortable collumns included in table
	 */
	function get_sortable_columns() {
		return $sortable = array(
			'col_name' => 'name'
		);
	}
		
	/**
	 * Prepare the data table with pagination arguments, search
	 * terms, ordering and collumn registration
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;
		$screen = get_current_screen();
		$taxonomy = 'department_shortname';
		
		$tags_per_page = 20; //eventually make customizable?	 	
		$search = !empty( $_REQUEST['s'] ) ? trim( wp_unslash( $_REQUEST['s'] ) ) : '';
		
		$args = array(
			'search' => $search,
			'page' => $this->get_pagenum(),
			'number' => $tags_per_page,
		);
		
		//Get any ordering
		if ( !empty( $_REQUEST['orderby'] ) )
			$args['orderby'] = trim( wp_unslash( $_REQUEST['orderby'] ) );

		if ( !empty( $_REQUEST['order'] ) )
			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
			
		$this->callback_args = $args;
		
		//hide empty terms (excludes parents with items in them)
		$hide_empty = 1;
		
		//Pagination Set up
		$this->set_pagination_args( array(
			'total_items' => wp_count_terms( $taxonomy, compact( 'search', 'hide_empty' ) ),
			'per_page' => $tags_per_page,
		) );
		
		// Register the Columns
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

	/**
	 * Required, copied from WP
	 *
	 * @return bool True
	 */
	function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}
	
	/**
	 * Creates the rows including handling the term
	 * hierarchy and an empty table
	 */
	function display_rows_or_placeholder() {
		$taxonomy = 'department_shortname';

		$args = wp_parse_args( $this->callback_args, array(
			'page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 1
		) );

		extract( $args, EXTR_SKIP );

		$args['offset'] = $offset = ( $page - 1 ) * $number;

		// convert it to table rows
		$count = 0;

		$terms = array();

		if ( !isset( $orderby ) ) {
			// We'll need the full set of terms then.
			$args['number'] = $args['offset'] = 0;
		}
		$terms = get_terms( $taxonomy, $args );

		if ( empty( $terms ) ) {
			list( $columns, $hidden ) = $this->get_column_info();
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if ( !isset( $orderby ) ) {
			if ( !empty( $search ) ) // Ignore children on searches.
				$children = array();
			else
				$children = _get_term_hierarchy( $taxonomy );

			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within, Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows( $taxonomy, $terms, $children, $offset, $number, $count );
		} else {
			$terms = get_terms( $taxonomy, $args );
			foreach ( $terms as $term )
				$this->single_row( $term );
			$count = $number; // Only displaying a single page.
		}
	}
	
	/**
	 * Populates rows with regard to hierarchy
	 */
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
	
	/**
	 * Creates an individual row
	 *
	 * @param object $tag	Term we are currently listing
	 * @param int $level	If parent, 0, then child depth
	 */
	function single_row( $tag, $level = 0 ) {
		static $row_class = '';

		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		$this->level = $level;

		echo '<tr id="tag-' . $tag->term_id . '"' . $row_class . '>';
		$this->single_row_columns( $tag );
		echo '</tr>';
	}
	
	/**
	 * Generates the name collumn for a specific term with link
	 *
	 * @param object $tag	Term we are getting the name of
	 *
	 * @return string Mark-up and content that goes in name collumn
	 */
	function column_col_name( $tag ) {
		$taxonomy = 'department_shortname';
		$tax = get_taxonomy( $taxonomy );

		$default_term = get_option( 'default_' . $taxonomy );

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );
		$name = $tag->name;
		$qe_data = get_term( $tag->term_id, $taxonomy, OBJECT, 'edit' );
		$edit_link = admin_url().'admin.php?page=proposals&department_shortname='.$tag->slug;
		
		$out = '<strong><a class="row-title" href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $name ) ) . '">' .$pad. $name . '</a></strong><br />';

		$out .= $this->row_actions( $actions );
		$out .= '<div class="hidden" id="inline_' . $qe_data->term_id . '">';
		$out .= '<div class="name">' . $qe_data->name . '</div>';
		$out .= '<div class="slug">' . apply_filters( 'editable_slug', $qe_data->slug ) . '</div>';
		$out .= '<div class="parent">' . $qe_data->parent . '</div></div>';

		return $out;
	}
	
	/**
	 * Generates the description collumn for a specific term
	 *
	 * @param object $tag	Term we are getting the description of
	 *
	 * @return string Term discription
	 */
	function column_col_descrip( $tag ) {
		return $tag->description;
	}
	
	/**
	 * Directs calls to proper function to generate collumn
	 *
	 * @param object $tag			Term we are listing
	 * @param string $column_name	Name of collumn we need to populate
	 *
	 * @return string The function for the collumn
	 */
	function column_default( $tag, $column_name ) {
		return apply_filters( "manage_department_shortname_custom_column", '', $column_name, $tag->term_id );
	}
}

?>