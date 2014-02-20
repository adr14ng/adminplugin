<?php
/* * * * * * * * * * * * * * * * * * * * * *
 *
 *	Department Editor Custom Creation
 *	
 *	1. Include Styles
 *	2. Admin Bar
 *	3. Advanced Edit Form
 *	4. Edit Table Lists
 *	5. Custom Course Message
 *	6. Tabs
 *	7. Helper Functions
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */


/*****************************************************
 *
 *  Including the styles and js
 *
 ****************************************************/
function add_dp_editor_style() {
	$basedir = dirname(plugin_dir_url(__FILE__));
	wp_enqueue_style('dp-editor-style', $basedir . '/css/dp-editor-style.css');
}
add_action('admin_enqueue_scripts', 'add_dp_editor_style');


/*****************************************************
 *
 *  Editing the adminbar
 *
 *****************************************************/
 //Remove admin bar links, add link to review page (editor home)
function add_csun_admin_bar_links( $wp_admin_bar ) {
	//add link to the department editor home page
	$args = array(
			'id' => 'csun_dashboard_link',
			'title' => '<span class="ab-icon"></span>
		<span id="ab-csun-dashboard" class="ab-label">Home</span>',
			'href' => admin_url('admin.php?page=review'),
			);
	$wp_admin_bar->add_node( $args );	//add dashboard link
	
	//remove all the other links
	$wp_admin_bar->remove_node( 'comments' );
	$wp_admin_bar->remove_node( 'new-content' );
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'site-name' );
	$wp_admin_bar->remove_node( 'edit-profile' );
	$wp_admin_bar->remove_node( 'user-info' );
}
add_action( 'admin_bar_menu', 'add_csun_admin_bar_links', 999 );

//Add secondary bar for navigation in the department
function add_csun_admin_bar() {
	//if the category is in the url, use it (files&course list)
	if(isset($_REQUEST['department_shortname'])){
		$cat = $_REQUEST['department_shortname'];
	}
	//otherwise, figure out category from post (courses, programs, departments)
	elseif(isset($_REQUEST['post'])){
		//get all departments relating to the post
		$terms =  wp_get_post_terms( $_REQUEST['post'], 'department_shortname' );
		
		foreach($terms as $term){
			//ge and top level terms can't be the category
			if($term->slug !== 'ge' && $term->parent != 0) {
				//save the slug of the category that works
				$cat = $term->slug;
			}
		}
	}

	//if we have a category, build the bar
	if(isset($cat)) : 
		$term_id = term_exists( $cat );	//get term id from slug

		//Cleaned up term description holding department name
		$dp_name = term_description( $term_id, 'department_shortname' );
		$dp_name = strip_tags($dp_name);		//remove p tags
		$dp_name = trim(preg_replace('/\s\s+/', ' ', $dp_name));	//remove newline character

		//make li active for current page
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;	//get uri
		$type = isset($_GET['post']) ? get_post_type( ($_GET['post'])) : NULL ;	//get post type

		if ($uri AND ($type === 'programs' || $type === 'departments')){
			$page = 'program';
		}
		else if ($uri AND (strpos($uri,'courses')||$type === 'courses')) {
			$page = 'course';
		}
		else if ($uri AND strpos($uri,'proposals')) {
			$page = 'file';
		}

		//figure out which post is active first for programs/departments (will link to that tab)
		$department_id = get_first_term_post($cat);
?>
	<div id="csun-bar" role="naviagation">
	<div class="quicklinks" id="csun-toolbar" role="navigation" aria-label="Second navigation toolbar." tabindex="0">
		<ul id="csun-dept-bar" class="ab-second-menu">
			<li id="department-name"><?php echo $dp_name.' : '; ?></li>
			<li id="csun-program-link" <?php if($page === 'program') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url().'post.php?action=edit&post='.$department_id.'&department_shortname='.$cat;?>">
					<span class="ab-icon"></span>
					<span id="ab-csun-programs" class="ab-label">Programs</span>
				</a>		
			</li>
			<li id="csun-course-link" <?php if($page === 'course') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url().'edit.php?post_type=courses&amp;department_shortname='.$cat.'&amp;orderby=title&amp;order=asc"';?>>
					<span class="ab-icon"></span>
					<span id="ab-csun-courses" class="ab-label">Courses</span>
				</a>		
			</li>
			<li id="csun-file-link" <?php if($page === 'file') echo 'class="active"'; ?>>
				<a class="ab-item" href="<?php echo admin_url().'admin.php?page=proposals&amp;department_shortname='.$cat; ?>">
					<span class="ab-icon"></span>
					<span id="ab-csun-files" class="ab-label">Files</span>
				</a>		
			</li>		
		</ul><!-- /csun-dept-bar-->		
	</div><!-- /quicklins-->
	</div><!-- /csun-bar-->
<?php
	endif; //end isset($cat)
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
	remove_meta_box('revisionsdiv', 'post', 'normal');
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

	//Only edit page they can get to is courses
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
	
	//They can edit multiple posts though
	if ($uri AND strpos($uri,'post.php'))
    {
		$post_id = $_GET['post'];
		$post_type = get_post_type( $post_id );
	
		if($post_type === 'programs')	//if its programs the basic box is overview
			$description = '<label for="basic-box">Overview</label>Description of the program.';
		elseif($post_type === 'departments')	//if its departments the basic box is misc
			$description = '<label for="basic-box">Misc</label>Department information that fits no where else (e.g. accreditation).';
		
		if($description) {
		?><script>
			jQuery(function($) {
				//both are after the high acf fields
				$('<p class="label">' + '<?php echo $description; ?>' + '</p>').insertAfter('#acf_after_title-sortables')
			});
			</script><?php
		}
	}
	
}

/**************************************************
 *
 * Fake Tabs
 *
 **************************************************/
function department_edit_tabs(){
	/******************************************
	 * Get posts for category
	 *****************************************/
	 $curr_post = $_GET['post'];
	 
	 $terms = wp_get_post_terms( $curr_post, 'department_shortname', $args );
	 
	 foreach($terms as $term) {
		if($term->parent != 0) {	//we only want the child term
			$post_cat = $term;
			break;
		}
	 }
	 
	 if( isset($post_cat)){
		$term_id = $post_cat->term_id;

		//get departments with that department code
		$args=array(
			'post_type' => 'departments',
			'department_shortname' => $post_cat->slug,
			'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ), 
			'numberposts' => 50,
		);
		$departments = get_posts( $args );

		//get programs with that department code
		$args['post_type'] = 'programs';
		$programs = get_posts( $args );
		
		$posts = array_merge($departments, $programs);
	}
		
	if( $posts ){
		
	/********************************************
	 * Build Tabs
	 ********************************************/

		$term = get_term($term_id, 'department_shortname');
		
		$message = get_option( 'main_dp_settings');	//get message option
		$message = $message['view_all_message'];

		echo '<br />';
		echo '<h1>'.$term->description.'</h1>';	//department name
		echo '<p>'.$message.'</p>';

		//Create top tabs to switch between posts
		echo '<ul id="edit-tabs" class="nav nav-tabs">';
		foreach($posts as $post) {
			$post_ID = $post->ID;
			$post_type = get_post_type( $post );
			$post_name = $post->post_title;
			
			if($post_type==='programs')
				$post_option=get_field('option_title', $post_ID);

			echo '<li class="';
			
			if($post_ID == $curr_post)
				echo 'active ';
				
			if(isset($post_option)&&$post_option!=='')
				echo 'option" >';
			else
				echo 'nonoption" >';
			
			echo '<a href="'.get_edit_post_link( $post_ID ).'">'.$post_name;
			if($post_type==='programs'){
				echo ', ';
				echo the_field('degree_type', $post_ID);
			}
			if(isset($post_option)&&$post_option!==''){
				echo '<br />';
				echo '<span class="option">'.$post_option.'</span>';
			}
			echo '</a></li>';
		}	
		echo'</ul>';
	
	}
}
//only show on program and department edit pages
if( isset($_GET['post']) && ( get_post_type( $_GET['post'] ) === 'programs' ||  get_post_type( $_GET['post'] ) === 'departments')){
	add_action( 'all_admin_notices' , 'department_edit_tabs');
}
	
/*******************************************
 *
 * Helper functions
 *
 *******************************************/
 
//Takes either slug or id of term and returns id of the first department/program
function get_first_term_post($term) {
	$args=array(
		'post_type' => 'departments',
		'department_shortname' => $term,
		'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ), 
		'numberposts' => 50,
	);
	$departments = get_posts( $args );

	if($departments)
		return $departments[0]->ID;
			
	$args['post_type'] = 'programs';
	$programs = get_posts( $args );
	
	if($programs)
		return $programs[0]->ID;
		
	return 0;		
}

//Creates the edit link with the department shortname intact
function department_edit_link($link, $post_ID, $context) {
	if(isset($_REQUEST['department_shortname'])){
		if ( 'display' == $context )
			$link = $link.'&amp;department_shortname='.$_REQUEST['department_shortname'];
		else
			$action = $link.'&department_shortname='.$_REQUEST['department_shortname'];
	}
		
	return $link;
}
add_filter('get_edit_post_link', 'department_edit_link', 10, 3);

?>