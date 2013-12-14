<?php

/**
 * Creates a page to display course proposal files
 */
 
//need to enable url fopen
//includes->dpadmin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

function add_proposal_menu()
{
	add_menu_page( 'Proposal Files', 'Proposal Files', 'edit_posts', 
				'proposals', 'proposal_page', $icon, 18 ); //need icon
}

//function that generates the aggregate post page
function proposal_page() {	
	if(isset($_REQUEST['department_shortname'])) //if we already have the category page request
	{
		edit_proposals();
	}
	else //list all pages
	{
		list_proposal();
	}
}

//list department pages
function list_proposal() {
	//need to use word presses list table and our custom one
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require( dirname(__FILE__) . '/class-proposal-list-table.php' );
	
	//header title, plus links to make new programs/departments?>
	<div class = "wrap">
	<h2>Proposals and Memos</h2>
	
	<?//Createthe aggregate list table
	$prop_list_table = new Proposal_List_Table();
	$prop_list_table->prepare_items();
	
	//Search?>
	<form class="search-form prop-form" action method="get">
		<input type="hidden" name="page" value="proposals">
		<?php $prop_list_table->search_box( 'Search', 'aggr' ); ?>
	</form>
	
	<?php //display the aggregate list table
	$prop_list_table->display(); ?>
	</div>
<?}	//end list aggregrate post


//Creates the edit page where all posts are edited
function edit_proposals(){

	/******************************************
	 * Get attachments with meta
	 *****************************************/
	 if($post_cat = $_REQUEST['department_shortname'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){	//if the term exists
			//get all the posts with that department code
			$args=array(
				'post_type' => 'attachment',
				//'post__not_in' => $ids, // avoid duplicate posts
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_key' => 'user_cat',
				'meta_query' => array(
					array(
						'key' => 'user_cat',
						'value' => $post_cat,
						'compare' => 'LIKE'
					)
				),
			);
			
			$posts = get_posts( $args ); 
		}
		else{	//the term doesn't exist
			wp_die(__( 'Department does not exist' ));
		}
	}
	else	//we were given no category
		wp_die(__( 'Not enough information' ));
		
	if( !$posts )	//if no posts were retrieved
		wp_die(__( 'No files in this category' ));
		
	/********************************************
	 * Build Overall Page
	 ********************************************/
	
	$term = get_term($term_id, 'department_shortname');
	$alternate = true;
	?>
	<div class="wrap">
	<h2>Proposals and Memos : <?php echo $term->description; ?></h2> <br />
	
	<table class="wp-list-table widefat" cellspacing="0">
	
		<thead>
			<tr>
				<th scope="col" id="col_name" class="manage-column column-col_name" style=""><span>Files</span></th>
			</tr>
		</thead>
		
	<tbody id="the-list">
	<?php foreach($posts as $post) {
		$alternate = !$alternate;?>
		<tr class="<?php if($alternate) echo 'alternate'; ?>"><td class="col_name column-col_name">
			<a class="row-title" href=<?php echo $post->guid; ?> >
			<?php echo $post->post_title; ?> </a>
			<br />
			<?php echo $post->post_content; ?> <br />
		</tr></td>
	<?php }
	
	echo '</tbody></table></div>';
	
	}

?>