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
	else //list all pages
	{
		list_aggregate_post();
	}
}

//list department pages
function list_aggregate_post() {
	//need to use word presses list table and our custom one
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require( dirname(__FILE__) . '/class-dp-aggregate-list-table.php' );
	
	//header title, plus links to make new programs/departments?>
	<div class = "wrap">
	<h2>Departments and Programs
		<a href="<?php echo admin_url('post-new.php?post_type=departments')?>" class="add-new-h2">Add New Department</a>
		<a href="<?php echo admin_url('post-new.php?post_type=programs')?>" class="add-new-h2">Add New Program</a>
	</h2>
	
	<?//Createthe aggregate list table
	$aggr_list_table = new Aggregate_List_Table();
	$aggr_list_table->prepare_items();
	
	//Search?>
	<form class="search-form agg-form" action method="get">
		<input type="hidden" name="page" value="dp_page">
		<?php $aggr_list_table->search_box( 'Search', 'aggr' ); ?>
	</form>
	
	<?php //display the aggregate list table
	$aggr_list_table->display(); ?>
	</div>
<?}	//end list aggregrate post

//Creates the edit page where all posts are edited
function edit_aggregate_post(){
    global  $post, $pagenow, $typenow;
    
    $pagenow = 'post.php'; //mimicking post page
	/******************************************
	 * Get posts for category
	 *****************************************/
	 if($post_cat = $_REQUEST['cat'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){	//if the term exists
			//get all the posts with that department code
			$args=array(
				'post_type' => array('programs', 'departments'),
				'post__not_in' => $ids, // avoid duplicate posts
				'department_shortname' => $post_cat,
				'numberposts' => 50,
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
		wp_die(__( 'No posts in this category' ));
		
	/********************************************
	 * Build Overall Page
	 ********************************************/
	$action ='edit';

	$posts = array_reverse ($posts); //reverse order to show department first
	//this depends on the order in which they were created (so make departments first for now
?>
	<br />

<?php
	//Create top tabs to switch between posts
	$isFirst = true; //to make active tab
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
	$isFirst = true; //to make active tab
	foreach ($posts as $post) {
		$post_ID = $post->ID;
		$typenow = $post_type = $post->post_type;
		
		?>
		<div id="custom-edit-<?php echo $post_ID?>" class="csun-edit-form tab-pane<?php if($isFirst){ echo ' active'; $isFirst = false;}?>">
		<?php
		include('edit-form.php');
		?>
		</div>
		<?php
	}?>
	</div>
	
	<script type="text/javascript">
		(function($) {
			//Pop up all divs and hide after editors have their height set
			//prevents compressed WYSIWYG boxes
			$( window ).one( "click scroll", function () {
				$( ".tab-pane" ).addClass('inactive');
				$( ".active" ).removeClass('inactive');
			});
			
			//Initialize forms as ajaxForm
			$(document).on( "ready", function () {
					$('.dp-editform').ajaxForm();  
				});
			
			//Submit the forms
			$( ".submitall" ).on( "click", function () {
				$('.dp-editform').each(function () {
					var options = {context: this}  
					tinyMCE.triggerSave();		//make sure to update the text boxes from the WYSIWYG boxes
					$(this).ajaxSubmit(options);
				})
				
				showmessage();
			});
			
			//Show update message (fade in and out if pressed multiple times)
			function showmessage() {
				$(".updated").fadeOut( 300 ).fadeIn( 300 );

				$( ".updated" ).removeClass('invisible');
			}
		})(window.jQuery);
	</script>

<?php }

//Returns a link to the aggregate edit page
//Used for building the table and redirects
function get_aggregate_edit_link($cat, $context='') {
	$sformat = 'admin.php?page=dp_page';		//format of the url
	
	//wordpresses context display
	if( 'display' == $context)
		$action = '&amp;cat=%s&amp;action=edit';
	else
		$action = '&cat=%s&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat));
}

//Makes the edit link this one if on our custom page
function filter_aggregate_edit_link($url, $post, $context)
{
	$cat =  wp_get_post_terms( $post, 'department_shortname');
	$post_type = get_post_type( $post );

	//if the post shows up on an aggregate edit page and it comes from an aggregate edit page
	if($cat && ($post_type == 'departments' || $post_type == 'programs')
			&& (strpos($_REQUEST[_wp_http_referer], 'page=dp_page')!== false)){
		$cat = $cat[0];
		$cat_name = $cat->slug;
		
		$url = get_aggregate_edit_link($cat_name, $context);	//get the edit link
	}
	return $url;
}
add_filter( 'get_edit_post_link', 'filter_aggregate_edit_link', '99', 3 );


//Fixes the name change of content field, allows saving on aggregate edit form
function dp_edit_post($data, $postarr) {
	$contentName= 'content'.$postarr['post_ID'];	//our custom field name
	
	if(isset( $postarr[$contentName])) {
		$data['post_content']= $postarr[$contentName];	//what the field name should be
		$postarr['post_content']= $postarr[$contentName];
		unset( $data[$contentName]);
		unset( $postarr[$contentName]);
	}
	
	return $data;
}
add_filter( 'wp_insert_post_data', 'dp_edit_post', '99' , 2);

?>