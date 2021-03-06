<?php
/** * * * * * * * * * * * * * * * * * * * *
 *
 *	Proposal Files View
 *	
 * 	Creates a page to display course 
 *	proposal files.
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */

//includes->dpadmin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

/**
 * Creates the menu link for the proposal page
 * Hooks onto admin_menu action.
 */
function add_proposal_menu()
{
	add_menu_page( 'Proposal Files', 'Proposal Files', 'edit_posts', 
				'proposals', 'proposal_page', '', 18 ); //need icon
}

/**
 * Generates the proposal files page
 */
function proposal_page() {	
	//if we already have the category page request
	if(isset($_REQUEST['department_shortname'])) 
	{
		edit_proposals();
	}
	else //list all pages
	{
		list_proposal();
	}
}

/**
 * Creates the page that lists all departments
 * Uses Proposal List Table to do so
 */
function list_proposal() {
	//need to use word presses list table and our custom one
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require( dirname(__FILE__) . '/class-proposal-list-table.php' );
	
	//header title?>
	<div class = "wrap">
		<h2>Proposals and Memos</h2>
		
		<?//Create the list table
		$prop_list_table = new Proposal_List_Table();
		$prop_list_table->prepare_items();
		
		//Search?>
		<form class="search-form prop-form" action method="get">
			<input type="hidden" name="page" value="proposals">
			<?php $prop_list_table->search_box( 'Search', 'aggr' ); ?>
		</form>
		
		<?php //display the list table
		$prop_list_table->display(); ?>
	</div><!-- /wrap -->
<?}	//end list aggregrate post


/**
 * Creates the page where all files per a department are viewed
 */
function edit_proposals(){

	/* * * * * * * * * * * * * * * * * * * * * *
	 * Get attachments with meta
	 * * * * * * * * * * * * * * * * * * * * * */
	 if($post_cat = $_REQUEST['department_shortname'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){	//if the term exists
			//get all the posts with that department code
			$args=array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_key' => 'user_cat',
				'meta_query' => array(	//check for the category in user cat field
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
		
	
		
	/* * * * * * * * * * * * * * * * * * * * * *
	 * Build Overall Page
	 * * * * * * * * * * * * * * * * * * * * * */
		
	$message = get_option( 'main_dp_settings');	//get message option
	$message = $message['file_message'];
	
	$term = get_term($term_id, 'department_shortname');
	$alternate = true;	//alternate striping
	?>
	<div class="wrap">
	<h2>Proposals and Memos : <?php echo $term->description; ?></h2> 
	<p> <?php echo $message; ?></p>
	
	<?php
	if( !$posts )	//if no posts were retrieved
		echo '<p>There is no record of any approved curriculum proposals.</p>';
	
	else{ ?>
	
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
			<?php echo $post->post_content; //display date information?> <br />
		</tr></td>
	<?php }
	
	
	echo '</tbody></table></div><!-- /wrap -->';
	}
	
}

?>