<?php
/* * * * * * * * * * * * * * * * * * * * * *
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
 *	6. Publish -> Save
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */


class DP_Admin {
	
	/**
	 * Any action that needs to be taken upon activation
	 * Which includes registering new roles and capabilities
	 */ 
	function activate() {
		if( !current_user_can('activate_plugins') )
			return;
			
		//Create a department editor role
		//Restrict access to things not in the category of its name
		add_role( 'dp_editor', 'Department Editor', array(
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
			'edit_progam' => true,
			'edit_progams' => true,
			'edit_others_progams' => true, 
			'publish_progams' => true, 
			'read_progam' => true, 
			'read_private_progams' => true,
			'assign_terms' => true,
			));
			
	}//activate()
	
	/**
	 * Unistalling plugin clean up
	 */
	function uninstall() {
		remove_role( 'dp_editor' );
		remove_role( 'dp_faculty' );
		remove_role( 'dp_ar' );
	}//uninstall
 
 
	/**
	 * Adds a filter to map meta cap
	 * If user has the same meta cat as one of the
	 * categories of the post, they are able to edit
	 * otherwise change nothing
	 */	
	function match_category_user($caps, $cap, $user_id, $args) {
		global $post;
		
		//if we're not trying to edit or publish edits of a post, return
		if( $cap !== 'edit_posts' && $cap !== 'publish_posts' && $cap !== 'edit_post'&& $cap !== 'edit_others_posts' && 
			$cap !== 'edit_private_posts' && $cap !== 'read_private_posts' &&
			$cap !== 'edit_progams' /*&& $cap !== 'publish_programs'*/ && $cap !== 'edit_progam'&& $cap !== 'edit_others_progams'){
			return $caps;
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
		elseif(isset( $_GET['revision'] )) //revisions page post id is 0
			$post_id = $args[0];
		else 		//post id might be 1 otherwise
			$post_id = $args[1];
		//get terms of that post
		$cats = get_the_terms($post_id, 'department_shortname');//get categories of a post
		
		//if we didn't get it from the args (courses list and proposal files)
		if(!$cats && isset($_REQUEST['department_shortname']))
			$cats = $_REQUEST['department_shortname'];

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
	 */
	function add_base_style() {
		$basedir = dirname(plugin_dir_url(__FILE__));
		wp_enqueue_style('base-style', $basedir . '/css/base-admin-style.css');
	}//add base style

	
	/**
	 * Register color schemes.
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
	 */
	function change_layout() {
		//determine users role
		$user_ID = get_current_user_id();
		$user = get_userdata( $user_ID );
		
		$basedir = dirname(plugin_dir_url(__FILE__));
	 
		if ( empty( $user ) )
			return false;
		elseif( in_array( 'dp_editor', (array) $user->roles ))
			include dirname(__FILE__) . '/dp_editor-design.php';
		elseif( in_array( 'dp_ar', (array) $user->roles ))
			include dirname(__FILE__) . '/dp_ar-design.php';
		elseif( in_array( 'dp_faculty', (array) $user->roles ))
			wp_enqueue_style('faculty-style', $basedir . '/css/faculty-style.css');
	}//change layout
	
	
	/**
	 * Add custom toolbar for CSUN
	 */
	function my_toolbars( $toolbars )
	{
		// CSUN Custom
		$toolbars['CSUN' ] = array();
		//1 row tool bar
		$toolbars['CSUN' ][1] = array('formatselect', 'bullist', 'numlist', 'bold', 'italic', 'link', 'unlink', 'undo', 'redo');
	 
		// return $toolbars - IMPORTANT!
		return $toolbars;
	}//my_toolbars
	
	function csunFormatTinyMCE($in)
	{	
		$in['theme_advanced_buttons1']='formatselect,bullist,numlist,bold,italic,link,unlink,undo,redo';
		$in['theme_advanced_buttons2']='';
		$in['theme_advanced_buttons3']='';
		$in['theme_advanced_buttons4']='';
		
		return $in;
	}
	
	/**
	 * If a dp editor, all posts must be reviewed, so change them to pending
	 * Hooks after saving changes to database
	 */
	function make_pending_post($post_id, $data, $post_before) {
		global $current_user, $wpdb;
		
		//Determine users role
		$role = $wpdb->prefix . 'capabilities';
		$current_user->role = array_keys($current_user->$role);
		$role = $current_user->role[0];
		
		if ('dp_editor' == $role ){	//if a dp editor

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

	
} //dp_admin
?>