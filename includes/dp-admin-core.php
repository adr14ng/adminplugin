<?php

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
	}
 
 
	/**
	 * Adds a filter to map meta cap
	 * If user has the same meta cat as one of the
	 * categories of the post, they are able to edit
	 * otherwise change nothing
	 */	
	function match_category_user($caps, $cap, $user_id, $args) {
		global $post;
		//if we're not trying to edit or publish edits of a post, return
		if( $cap !== 'edit_posts' /*&& $cap !== 'publish_posts'*/ && $cap !== 'edit_post'&& $cap !== 'edit_others_posts' &&
			$cap !== 'edit_programs' /*&& $cap !== 'publish_programs'*/ && $cap !== 'edit_program'&& $cap !== 'edit_others_programs'){
			return $caps;
		}
		
		//Allows viewing course list page
		if(isset($_REQUEST['post_type']))
			if('courses' === $_REQUEST['post_type'])
				return array();
		
		$userCat = get_user_meta($user_id, 'user_cat');		//get user categories
		$userCat = $userCat[0];
		
		$post_id = $args[1];
		$cats = get_the_terms($post_id, 'department_shortname');//get categories of a post
		
		//if we didn;t get it from the args
		if(!$cats && isset($_REQUEST['department_shortname']))
			$cats = $_REQUEST['department_shortname'];

		if(is_array($userCat)){foreach ($userCat as $user){
			$user = strtolower($user);

			//can just be a string
			if($cats)
				$cats = (array) $cats;

			if (is_array($cats)) {foreach($cats as $cat){
				if(is_object($cat))
					$catName = strtolower($cat->slug);
				else
					$catName = strtolower($cat);
				//strict comparison
				if($user === $catName) {		//if user and post have same cat
					return array();	//no cap required
				}
			}}
		}}

		return $caps;	//is not linked to category name
	} //match_category_user()


	/**
	 *  Including the styles and js
	 */
	function add_base_style() {
		$basedir = dirname(plugin_dir_url(__FILE__));
		wp_enqueue_style('base-style', $basedir . '/css/base-admin-style.css');
		//wp_enqueue_style('csun-colors', $basedir . '/css/colors-csun.css');
		wp_enqueue_style('dp-bootstrap-style', $basedir . '/css/bootstrap.min.css');
		wp_enqueue_script('dp-bootstrap-script', $basedir . '/js/bootstrap.js');
		wp_enqueue_script('dp-script', $basedir . '/js/jquery.form.min.js');
	}

		
	/**
	 * Calls different layout files per user role
	 */
	function change_layout() {
		$user_ID = get_current_user_id();
		$user = get_userdata( $user_ID );
	 
		if ( empty( $user ) )
			return false;
		elseif( in_array( 'dp_editor', (array) $user->roles ))
			include dirname(__FILE__) . '/dp_editor-design.php';
	}//change layout
	
	//Add custom toolbar for CSUN
	function my_toolbars( $toolbars )
	{
		// CSUN Custom
		$toolbars['CSUN' ] = array();
		$toolbars['CSUN' ][1] = array('formatselect', 'bullist', 'numlist', 'bold', 'italic', 'undo', 'redo');
	 
		// return $toolbars - IMPORTANT!
		return $toolbars;
	}
	
	/**
	 * Register color schemes.
	 */
	function add_csun_colors() {
		$suffix = is_rtl() ? '-rtl' : '';
		$basedir = dirname(plugin_dir_url(__FILE__));

		wp_admin_css_color( 
			'csun', __( 'CSUN' ), 
			$basedir . '/css/colors'.$suffix.'.css',
			array( '#000000', '#666666', '#d99f5f', '#990000' ), 
			array( 'base' => '#000', 'focus' => '#fff', 'current' => '#fff' )
		);
		
		wp_admin_css_color( 
			'csun-default', __( 'CSUN Default' ), 
			$basedir . '/css/default-colors'.$suffix.'.css',
			array( '#000000', '#222222', '#0074a2', '#d54e21' ), 
			array( 'base' => '#222', 'focus' => '#fff', 'current' => '#fff' )
		);
	}
	
	function make_pending_post($data) {
	
		global $current_user, $wpdb;
		$role = $wpdb->prefix . 'capabilities';
		$current_user->role = array_keys($current_user->$role);
		$role = $current_user->role[0];
		
		if ('dp_editor' == $role ){

			if(isset( $data['post_status'])) {
				$data['post_status']= 'pending';	//set published status to pending review
			}
		}
		
		return $data;
	}

	
} //dp_admin
?>