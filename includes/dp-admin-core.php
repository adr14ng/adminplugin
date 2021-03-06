<?php
/** * * * * * * * * * * * * * * * * * * * * *
 *
 *	Department Admin Custom Experience
 *	
 *	1. Activation
 *	2. Capabilities
 *	3. Styles
 *		-Enque Scripts
 *		-Color Schemes
 *		-Role Dependent Styles
 *	4. TinyMCE Toolbars
 *	5. Pending Post
 *	6. Publish => Save
 *
 * 	@author CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */


class DP_Admin {
	
	/**
	 * Any action that needs to be taken upon activation
	 * Which includes registering new roles and capabilities
	 * Hooks onto activation action.
	 */ 
	function activate() {
		if( !current_user_can('activate_plugins') )
			return;
			
		add_role( 'dp_intern', 'Intern', array(
			'delete_others_pages' => true,
			'delete_others_posts' => true,
			'delete_pages' => true,
			'delete_posts' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages' => true,
			'edit_others_posts' => true,
			'edit_pages' => true,
			'edit_posts' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_published_pages' => true,
			'edit_published_posts' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'publish_pages' => true,
			'publish_posts' => true,
			'read' => true,
			'read_private_pages' => true,
			'read_private_posts' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'edit_faculty' => true,
			'edit_facultys' => true,
			'edit_others_facultys' => true,
			'publish_facultys' => true, 
			'read_faculty' => true, 
			'read_private_facultys' => true,
			'delete_faculty' => true,
			'delete_facultys' => true,
			'delete_others_facultys' => true,
			'assign_terms' => true,
			'edit_plan' => true,
			'edit_plans' => true,
			'edit_others_plans' => true,
			'publish_plans' => true, 
			'read_plan' => true, 
			'read_private_plans' => true,
			'delete_plan' => true,
			'delete_plans' => true,
			'delete_others_plans' => true,
			'edit_policy' => true,
			'edit_policies' => true,
			'edit_others_policies' => true,
			'publish_policies' => true, 
			'read_policy' => true, 
			'read_private_policies' => true,
			'delete_policy' => true,
			'delete_policies' => true,
			'delete_others_policies' => true,
		));
			
		//Create a department editor role
		//Restrict access to things not in the category of its name
		//Restrict editing to Department information
		add_role( 'dp_editor', 'Department Editor', array(
			'read' => true,
			'delete_posts' => false
		));
		
		//Create a college editor role
		//Restrict access to things not in the category of its name
		add_role( 'dp_college', 'College Editor', array(
			'read' => true,
			'delete_posts' => false
		));
			
		//Create a college editor role
		//Restrict access to things not in the category of its name
		//Do not allow editing
		add_role( 'dp_reviewer', 'Reviewer', array(
			'read' => true,
			'delete_posts' => false
		));
			
		//Create a faculty editor role
		//Restrict access to faculty
		add_role( 'dp_faculty', 'Faculty Editor', array(
			'read' => true,
			'edit_posts' => true,
			'edit_faculty' => true,
			'edit_facultys' => true,
			'edit_others_facultys' => true,
			'publish_facultys' => true, 
			'read_faculty' => true, 
			'read_private_facultys' => true,
			'delete_faculty' => true,
			'delete_facultys' => true,
			'delete_others_facultys' => true,
			'assign_terms' => true,
		));
			
		//Create a faculty editor role
		//Restrict access to programs, 4 year plans and star acts
		add_role( 'dp_ar', 'Admissions and Records', array(
			'read' => true,
			'edit_posts' => true,
			'edit_plan' => true,
			'edit_plans' => true,
			'edit_others_plans' => true,
			'publish_plans' => true, 
			'read_plan' => true, 
			'read_private_plans' => true,
			'delete_plan' => true,
			'delete_plans' => true,
			'delete_others_plans' => true,
			'assign_terms' => true,
		));
		
		//Create a special groups role
		add_role( 'dp_group', 'Special Groups', array(
			'read' => true,
			'edit_posts' => true,
			'edit_group' => true,
			'edit_groups' => true,
			'publish_groups' => true, 
			'read_group' => true, 
			'read_private_groups' => true
		));
			
		//Create a policy editor role
		//Restrict access to policies and pages
		add_role( 'dp_policy', 'Policy Editor', array(
			'read' => true,
			'edit_posts' => true,
			'edit_policy' => true,
			'edit_policies' => true,
			'edit_others_policies' => true,
			'publish_policies' => true, 
			'read_policy' => true, 
			'read_private_policies' => true,
			'delete_policy' => true,
			'delete_policies' => true,
			'delete_others_policies' => true,
			'assign_terms' => true,
			'read_page' => true, 
			'read_private_pages' => true,
			'edit_page' => true,
			'edit_pages' => true,
			'edit_private_pages' => true,
			'edit_published_pages' => true,
			'edit_others_pages' => true,
			'publish_pages' => true, 
			'delete_page' => true,
			'delete_pages' => true,
			'delete_others_pages' => true,
			'delete_published_pages' => true,
			'assign_terms' => true,
		));
		
		//Create a page editor role
		//Restrict access to pages
		add_role( 'dp_pages', 'Page Editor', array(
			'read' => true,
			'edit_posts' => true,
			'read_page' => true, 
			'read_private_pages' => true,
			'edit_page' => true,
			'edit_pages' => true,
			'edit_private_pages' => true,
			'edit_published_pages' => true,
		));

	}//activate()
	
	/**
	 * Unistalling plugin clean up
	 * Hooks onto uninstall action.
	 */
	function uninstall() {
		remove_role( 'dp_editor' );
		remove_role( 'dp_college' );
		remove_role( 'dp_reviewer' );
		remove_role( 'dp_faculty' );
		remove_role( 'dp_ar' );
		remove_role( 'dp_policy' );
		remove_role( 'dp_pages' );
		remove_role( 'dp_intern' );
		remove_role( 'dp_group' );
		
		delete_option( 'main_dp_settings' );
		unregister_setting( 'dp-admin-group', 'main_dp_settings');
	}//uninstall
 
 
	/**
	 * Adds a filter to map meta cap
	 * If user has the same meta cat as one of the categories of the post, they are able to edit
	 * otherwise change nothing
	 * Hooks onto map_meta_cap filter.
	 *
	 * @param array  $caps    Returns the user's actual capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id The user ID.
	 * @param array  $args    Adds the context to the cap. Typically the object ID.
	 *
	 * @return array	Caps required to complete the task
	 */	
	function match_category_user($caps, $cap, $user_id, $args) {
		global $post;
		$user = get_userdata( $user_id );
		
		if(isset( $_GET['revision'] )){
			return;
		}
		
		//if we're not trying to edit or publish edits of a post, return
		if( $cap !== 'edit_posts' && $cap !== 'publish_posts' && $cap !== 'edit_post'&& $cap !== 'edit_others_posts' && 
			$cap !== 'edit_private_posts' && $cap !== 'read_private_posts' && $cap !== 'edit_departments'  
			&& $cap !== 'publish_departments' && $cap !== 'edit_department'&& $cap !== 'edit_others_departments'&& $cap !== 'read_department'
			&& $cap !== 'edit_programs'  && $cap !== 'publish_programs' && $cap !== 'edit_program'&& $cap !== 'edit_others_programs'&& $cap !== 'read_program'
			&& $cap !== 'edit_courses'  && $cap !== 'publish_courses' && $cap !== 'edit_course'&& $cap !== 'edit_others_courses'&& $cap !== 'read_course'){

			//print_r($cap);
			
			return $caps;
		}

		
		//Truncate Department Editor Permissions
		if(in_array( 'dp_editor', (array) $user->roles )){
			//no publishing programs
			if($cap == 'publish_programs' || $cap == 'edit_others_programs'){
				return $caps;
			}
			
			//No publishing courses
			if($cap == 'publish_courses' || $cap == 'edit_others_courses'){
				return $caps;
			}
		}
		
		//Truncate Reviewer Permissions
		if(in_array( 'dp_reviewer', (array) $user->roles )){
			//no editing posts
			if($cap == 'publish_departments' || $cap == 'edit_others_departments'){
				return $caps;
			}
			
			//no publishing programs
			if($cap == 'publish_programs' || $cap == 'edit_others_programs'){
				return $caps;
			}
			
			//No publishing courses
			if($cap == 'publish_courses' || $cap == 'edit_others_courses'){
				return $caps;
			}
		}
		
		//Allows viewing course list page (all courses though)
		if(isset($_REQUEST['post_type']))
			if('courses' === $_REQUEST['post_type'])
				return array();	//no caps required
		
		$userCat = get_user_meta($user_id, 'user_cat');		//get user categories
		$userCat = $userCat[0];
		
		//get current post id
		if(isset( $_GET['post'] ))	//if we have the post id
			$post_id = $_GET['post'];
		elseif(isset( $_GET['revision'] )){ //revisions page post id is 0
			if(isset($args[0])) $post_id = $args[0];}
		elseif(isset( $_REQUEST['post_ID'] ))
			$post_id = $_REQUEST['post_ID'];
		elseif(isset( $_REQUEST['p'] ))	//preview post
			$post_id = $_REQUEST['p'];
		elseif(isset($args[1]) )	//post id might be 1 otherwise
			$post_id = $args[1];
		
		//get terms of that post
		if(isset($post_id) )
			$cats = get_the_terms($post_id, 'department_shortname');//get categories of a post
		else
			$cats=false;
		
		//if we didn't get it from the args (courses list and proposal files)
		if(!$cats && isset($_REQUEST['department_shortname']))
			$cats = $_REQUEST['department_shortname'];

		/*echo "<br />Post: ";
		print_r($cats);
		echo "<br />User: ";
		print_r($userCat);*/
		
		//compare each post category to each user category until you find a match
		if(is_array($userCat)){foreach ($userCat as $user){	//each users category
			$user = strtolower($user);	//just in case there are capitals

			//can just be a string if only 1, need an array
			if($cats)
				$cats = (array) $cats;

			if (is_array($cats)) {foreach($cats as $cat){	//each post category
				if(is_object($cat))	
					$catName = strtolower($cat->slug);	//just in case of capitals
				else
					$catName = strtolower($cat);
					
				if($user === $catName) {		//if user and post have same cat
					return array();	//no cap required
				}
			}}
		}}

		return $caps;	//is not linked to category name, whatever standard caps
	} //match_category_user()


	/**
	 *  Including the styles and js
	 *  Hooks onto admin_enqueue_scripts action.
	 */
	function add_base_style() {
		$basedir = dirname(plugin_dir_url(__FILE__));
		wp_enqueue_style('base-style', $basedir . '/css/base-admin-style.css');
	}//add base style

	
	/**
	 * Register color schemes.
	 * Hooks onto admin_init action.
	 */
	function add_csun_colors() {
		$basedir = dirname(plugin_dir_url(__FILE__));

		//Red and black color scheme
		wp_admin_css_color( 
			'csun', __( 'CSUN' ), //name
			$basedir . '/css/colors.css',
			array( '#000000', '#666666', '#d99f5f', '#990000' ), 
			array( 'base' => '#000', 'focus' => '#fff', 'current' => '#fff' )
		);
		
		//Blue and black color scheme
		wp_admin_css_color( 
			'csun-default', __( 'CSUN Default' ), //name
			$basedir . '/css/default-colors.css',
			array( '#000000', '#222222', '#0074a2', '#d54e21' ), 
			array( 'base' => '#222', 'focus' => '#fff', 'current' => '#fff' )
		);
	}//add csun colors
	
	
	/**
	 * Calls different layout files per user role
	 * Hooks onto init action.
	 */
	function change_layout() {
		//determine users role
		$user_ID = get_current_user_id();
		$user = get_userdata( $user_ID );
		
		$basedir = dirname(plugin_dir_url(__FILE__));
	 
		if ( empty( $user ) )
			return false;
		elseif( in_array( 'dp_editor', (array) $user->roles ) || in_array( 'dp_college', (array) $user->roles ) || in_array( 'dp_reviewer', (array) $user->roles ))
			include dirname(__FILE__) . '/dp_editor-design.php';
		elseif( in_array( 'dp_ar', (array) $user->roles ))
			include dirname(__FILE__) . '/dp_ar-design.php';
		elseif( in_array( 'dp_faculty', (array) $user->roles ))
			wp_enqueue_style('faculty-style', $basedir . '/css/faculty-style.css');
		elseif( in_array( 'dp_policy', (array) $user->roles ))
			wp_enqueue_style('policy-style', $basedir . '/css/policy-style.css');
		elseif( in_array( 'dp_pages', (array) $user->roles ))
			wp_enqueue_style('pages-style', $basedir . '/css/page-style.css');
		elseif( in_array( 'dp_group', (array) $user->roles ))
			wp_enqueue_style('group-style', $basedir . '/css/group-style.css');
	}//change layout
	
	
	/**
	 * Add custom toolbar for CSUN
	 * Hooks onto acf/fields/wysiwyg/toolbars filter
	 *
	 * @param array $toolbars	The current list of toolbars used by Advanced Custom Fields
	 *
	 * @return array	The updated list of toolbars for ACF
	 */
	function my_toolbars( $toolbars )
	{
		// CSUN Custom
		$toolbars['CSUN' ] = array();
		//1 row tool bar
		$toolbars['CSUN' ][1] = array('formatselect','styleselect', 'bullist', 'numlist', 'bold', 'italic', 'link', 'unlink', 'table', 'undo', 'redo', 'removeformat');
	 
		return $toolbars;
	}//my_toolbars
	
	/**
	 * Customize first row of toolbar
	 * Hooks onto mce_buttons filter.
	 *
	 * @param array $buttons	The default wordpress toolbar
	 *
	 * @return array			The updated wordpress toolbar
	 */
	function csunFormatTinyMCEButtons($buttons)
	{	
		$buttons=array('formatselect','styleselect','bullist','numlist',
			'bold','italic','subscript', 'superscript','outdent','indent',
			'link','unlink','table','undo','redo','removeformat');
		
		return $buttons;
	}
	
	/**
	 * Customize second row of toolbar
	 * Hooks onto mce_buttons_2 filter.
	 *
	 * @param array $buttons	The default wordpress toolbar
	 *
	 * @return array			The updated wordpress toolbar
	 */
	function csunFormatTinyMCEButtons2($buttons)
	{
		$buttons=array();
		
		return $buttons;
	}
	
	/**
	 * Add custom tinyMCE plugins (must have js already)
	 * Hooks onto mce_external_plugins filter.
	 *
	 * @return	array	The added plugins
	 */
	function custom_tinyMCE_plugins () {
		$basedir = dirname(plugin_dir_url(__FILE__));
	
		$plugins_array[ 'table' ] = $basedir . '/js/table-plugin.min.js';
		$plugins_array[ 'advlist' ] = $basedir . '/js/advlist-plugin.min.js';
		
		return $plugins_array;
	}
	
	/**
	 * If not an administrator, all posts must be reviewed, so change them to pending
	 * Hooks onto post_updated action.
	 *
	 * @param int 	$post_id		The id of the post being saved
	 * @param array	$data			The post data to be saved
	 * @param array	$post_before	The post data before this update
	 */
	function make_pending_post($post_id, $data, $post_before) {
		global $current_user, $wpdb;
		
		//Determine users role
		$role = $wpdb->prefix . 'capabilities';
		$current_user->role = array_keys($current_user->$role);
		$role = $current_user->role[0];
		
		if ('administrator' !== $role && 'dp_faculty' !== $role && 'dp_ar' !== $role){	//if not an administrator/faculty editor

			//only change to pending if not already (assume all edited posts are published)
			//avoids infinite loop of updating
			if(isset( $data->post_status) && $data->post_status === 'publish') {
				$data->post_status= 'pending';	//set published status to pending review
				wp_update_post($data);	//update to pending
			}
		}
	} //make pending post
	
	/**
	 * Change publish, update, etc to Save
	 * Hooks onto gettext filter.
	 *
	 * @param string $translation	Translated text.
	 * @param string $text			Text to translate.
	 *
	 * @return string	Updated text
	 */
	function change_publish_button( $translation, $text ) {
		//Typical words on 'Publish' button
		if ( $text == 'Publish' )
			return 'Save';
			
		if ( $text == 'Update' )
			return 'Save';
			
		if ( $text == 'Submit for Review' )
			return 'Save';

		return $translation;
	}//change publish button
	
	/**
	 * Changes footer version text.
	 * Hooks onto update_footer filter.
	 *
	 * @return string	Updated text
	 */
	function replace_footer_version(){
		return 'California State University, Northridge';
	}
	
	/**
	 * Changes footer text
	 * Hooks onto admin_footer_text filter.
	 *
	 * @return string	Updated text
	 */
	function csun_footer_admin () 
	{	  
		return 'Powered by the Office of Undergraduate Studies.';	
	}
	
	/**
	 * Changes the program name in the relationship field for acf.
	 * Allows differentiation between programs with same base name.
	 * Hooks onto acf/fields/relationship/result/name=degree_planning_guides filter.
	 *
	 * @param	string	$title	The base program title
	 * @param	WP_Post	$object	The program post object
	 *
	 * @return	string			The full program title
	 */
	function acf_modify_prog_name($title, $object)
	{
		//B.A., B.S., B.M., etc.
		$degree = get_field('degree_type', $object->ID);

		if ($degree === 'honors' || $degree === 'Honors' ){
			$title = $title;
		}
		else {
			$title = $title.', '.$degree;
		}
		
		$post_option = get_field('option_title', $object->ID);
		if( isset($post_option) && $post_option !== '')
		{
			$title = $title.' - '.$post_option.' Option';
		}
		
		return $title;
	}

	
} //dp_admin
?>