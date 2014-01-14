<?php

/*****************************************************
 *
 *  Including the styles and js
 *
 ****************************************************/
function add_dp_style() {
	$basedir = dirname(plugin_dir_url(__FILE__));
	wp_enqueue_style('dp-editor-style', $basedir . '/css/dp-editor-style.css');
}
add_action('admin_enqueue_scripts', 'add_dp_style');


/*****************************************************
 *
 *  Editing the adminbar
 *
 *****************************************************/
function add_csun_admin_bar_links( $wp_admin_bar ) {
	$args = array(
			'id' => 'csun_dashboard_link',
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-dashboard" class="ab-label">Home</span>',
			'href' => admin_url('admin.php?page=review'),
			);
	$wp_admin_bar->add_node( $args );	//add dashboard link
	
	
	$wp_admin_bar->remove_node( 'comments' );
	$wp_admin_bar->remove_node( 'new-content' );
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'site-name' );
	$wp_admin_bar->remove_node( 'edit-profile' );
	$wp_admin_bar->remove_node( 'user-info' );
}
add_action( 'admin_bar_menu', 'add_csun_admin_bar_links', 999 );

function add_csun_admin_bar() {
	if(isset($_REQUEST['department_shortname'])){
	
		$cat = $_REQUEST['department_shortname'];
	}
	elseif(isset($_REQUEST['post'])){
		$terms =  wp_get_post_terms( $_REQUEST['post'], 'department_shortname' );
		
		foreach($terms as $term){
			if($term->slug !== 'ge') {
				$cat = $term->slug;
			}
		}
	}
	
	if(isset($cat)) : 
		$term_id = term_exists( $cat );
		
		//Cleaned up term description holding department name
		$dp_name = term_description( $term_id, 'department_shortname' );
		$dp_name = strip_tags($dp_name);		//remove p tags
		$dp_name = trim(preg_replace('/\s\s+/', ' ', $dp_name));	//remove newline character
		
		//make li active for current page
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

		if ($uri AND strpos($uri,'dp_page')){
			$page = 'program';
		}
		else if ($uri AND (strpos($uri,'courses')||strpos($uri,'post.php'))) {
			$page = 'course';
		}
		else if ($uri AND strpos($uri,'proposals')) {
			$page = 'file';
		}
?>
	<div id="csun-bar" role="naviagation">
	<div class="quicklinks" id="csun-toolbar" role="navigation" aria-label="Second navigation toolbar." tabindex="0">
		<ul id="csun-dept-bar" class="ab-second-menu">
			<li id="department-name"><?php echo $dp_name.' : '; ?></li>
			<li id="csun-progam-link" <?php if($page === 'program') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url(); ?>admin.php?page=dp_page&amp;department_shortname=<?php echo $cat; ?>&amp;action=edit">
					<span class="ab-icon"></span>
					<span id="ab-csun-programs" class="ab-label">Programs</span>
				</a>		
			</li>
			<li id="csun-course-link" <?php if($page === 'course') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url(); ?>edit.php?post_type=courses&amp;department_shortname=<?php echo $cat; ?>&amp;orderby=title&amp;order=asc">
					<span class="ab-icon"></span>
					<span id="ab-csun-courses" class="ab-label">Courses</span>
				</a>		
			</li>
			<li id="csun-file-link" <?php if($page === 'file') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url(); ?>admin.php?page=proposals&amp;department_shortname=<?php echo $cat; ?>">
					<span class="ab-icon"></span>
					<span id="ab-csun-files" class="ab-label">Files</span>
				</a>		
			</li>		
		</ul>			
	</div>
	</div>
<?php
	endif;
}

add_action( 'in_admin_header', 'add_csun_admin_bar');

/*****************************************************
 *
 *  Editing the advanced edit form
 *
 *****************************************************/
 
//Remove meta-boxes
function remove_meta_boxes() {
	remove_meta_box('formatdiv', 'post', 'normal');
	remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
	remove_meta_box('postimagediv', 'post', 'normal');
	remove_meta_box('department_shortnamediv', 'courses', 'side');
	remove_meta_box('general_educationdiv', 'courses', 'side');
	
}
add_action('admin_init', 'remove_meta_boxes');


/*****************************************************
 *
 *  Editing the edit list
 *
 *****************************************************/
 
//remove extra info in list
function simplify_post_columns($defaults) {
  unset($defaults['comments']);
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['date']);
  unset($defaults['tags']);
  unset($defaults['department']);
  unset($defaults['ge']);
  return $defaults;
}
add_filter('manage_${post_type}_posts_columns', 'simplify_post_columns');

function simplify_course_columns($defaults) {
  unset($defaults['cb']);
  unset($defaults['author']);
  unset($defaults['date']);
  unset($defaults['department']);
  unset($defaults['ge']);
  return $defaults;
}
add_filter('manage_edit-courses_columns', 'simplify_course_columns');

//remove quick edit
function remove_quick_edit( $actions ) {
	unset($actions['inline hide-if-no-js']);
	return $actions;
}
add_filter('post_row_actions','remove_quick_edit',10,1);

/*****************************************************
 *
 *	Custom Messages
 *
 ****************************************************/
 
 add_action('admin_footer', 'editor_admin_footer');
function editor_admin_footer()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

    $message = NULL;

    if ($uri AND strpos($uri,'edit.php'))
    {
        $message = get_option( 'main_dp_settings');	//get message option
		$message = $message['course_message'];
	}

    if ($message)
    {
        ?><script>
            jQuery(function($)
            {
                $('<div id="course_message"><p></p></div><br />').text('<?php echo $message; ?>').insertAfter('#wpbody-content .wrap h2:eq(0)');
            });
        </script><?php
    }
}

/**************************************************
 *
 * Fake Tabs
 *
 **************************************************/
 //Creates the edit page where all posts are edited
function edit_aggregate_post(){
    global  $post, $pagenow, $typenow;
    
    $pagenow = 'post.php'; //mimicking post page
	/******************************************
	 * Get posts for category
	 *****************************************/
	 if($post_cat = $_REQUEST['department_shortname'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){	//if the term exists
			//get departments with that department code
			$args=array(
				'post_type' => 'departments',
				'post__not_in' => $ids, // avoid duplicate posts
				'department_shortname' => $post_cat,
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ), 
				'numberposts' => 50,
			);
			$departments = get_posts( $args );
			
			//get programs with that department code
			$args=array(
				'post_type' => 'programs',
				'post__not_in' => $ids, // avoid duplicate posts
				'department_shortname' => $post_cat,
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ), 
				'numberposts' => 50,
			);
			$programs = get_posts( $args );
			
			$posts = array_merge($departments, $programs);
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
	 * Build Tabs
	 ********************************************/

	$term = get_term($term_id, 'department_shortname');
	
	$message = get_option( 'main_dp_settings');	//get message option
	$message = $message['view_all_message'];

	echo '<br />';
	echo '<h1>'.$term->description.'</h1>';
	echo '<p>'.$message.'</p>';

	//Create top tabs to switch between posts
	$isFirst = true; //to make active tab
	echo '<ul id="edit-tabs" class="nav nav-tabs">';
	foreach($posts as $post) {
		$post_ID = $post->ID;
		$post_type = get_post_type( $post );
		$post_name = $post->post_title;
		if($isFirst){
			$isFirst = false;
			echo '<li class="active">';
		}
		else
			echo '<li>';
		
		echo '<a href="#custom-edit-'.$post_ID.'" data-toggle="tab">'.$post_name;
		if($post_type==='programs'){
			echo ', ';
			echo the_field('degree_type');
		}
		echo '</a></li>';
	}	
	echo'</ul><div class="tab-content"> ';
}

?>