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
			
	}//activate()
	
	/**
	 * Unistalling plugin clean up
	 */
	function uninstall() {
		remove_role( 'dp_editor' );
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
		if( $cap !== 'edit_posts' && $cap !== 'publish_posts' && $cap !== 'edit_post'&& $cap !== 'edit_others_posts'){
			return $caps;
		}
		
		//Allows viewing course list page
		if(isset($_REQUEST['post_type']))
			if('dp_course' === $_REQUEST['post_type'])
				return array();
		
		$userCat = get_user_meta($user_id, 'user_cat');		//get user categories
		$userCat = $userCat[0];
		
		$post_id = $args[1];
		$cats = get_the_terms($post_id, 'department_shortname');//get categories of a post
		
		//if we didn;t get it from the args
		if(!$cats && isset($_REQUEST['cat']))
			$cats = $_REQUEST['cat'];

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
		wp_enqueue_style('csun-colors', $basedir . '/css/colors-csun.css');
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
	

	
} //dp_admin
?>