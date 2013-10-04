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
			
		csun_create_post_type();
		flush_rewrite_rules();
	}//activate()
	
	/**
	 * Unistalling plugin clean up
	 */
	function uninstall() {
		remove_role( 'dp_editor' );
		flush_rewrite_rules();
	}
	
	
	/**
	 * Checks if current user has a role. 
	 * Returns true if a match was found.
	 *
	 * @param string $role Role name.
	 * @return bool
	 */
	function check_user_role($role) {

		$user = wp_get_current_user();
	 
		if ( empty( $user ) )
			return false;
	 
		return in_array( $role, (array) $user->roles );
	}  //check_users_role()
	 
	 
	/**
	 * Adds a filter to map meta cap
	 * If user has the same meta cat as one of the
	 * categories of the post, they are able to edit
	 * otherwise change nothing
	 */	
	function match_category_user($caps, $cap, $user_id, $args) {
		//if we're not trying to edit or publish edits of a post, return
		if( $cap !== 'edit_post' || $cap !== 'publish_post')
			return $caps;

		$userCat = get_user_meta($user_id, 'user_cat');		//get user categories
		
		$post_id = $args[1];
		$post_term = wp_get_object_terms($post_id, 'department_shortname');		//get categories of a post

		foreach ($userCat as $use){
			foreach($post_terms as $post_term){
				$termName = $post_term->slug;
				//strict comparison
				if($use === $termName)		//if user and post have same cat
					return array();	//no cap required
			}
		}
			
		return $caps;	//is not linked to category name
	} //match_category_user()
	
	
	/**
	 *  Including the styles and js
	 */
	function add_base_style() {
		$basedir = dirname(plugin_dir_url(__FILE__));
		wp_enqueue_style('dp-editor-style', $basedir . '/css/base-admin-style.css');
	}

		
	/**
	 * Calls different layout files per user role
	 */
	function change_layout() {
		if ( check_user_role('dp_editor') )
		{
			include dirname(__FILE__) . '/dp_editor-design.php';
		}
	}//change layout
	
	/**
	 * Function to add custom post types and taxonomies
	 */
	function csun_create_custom() {
	
	//Custom post types
		register_post_type( 'dp_course',
			array(
			'labels' 		=> array(
						'name' 			=> __( 'Courses' ),
						'singular_name' => __( 'Course' )
				),
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title' ,
						'editor' ,
						'revisions'
				)
			)
		);
		
		register_post_type( 'dp_program',
			array(
			'labels' 		=> array(
						'name' 			=> __( 'Programs' ),
						'singular_name' => __( 'Program' )
				),
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title' ,
						'editor' ,
						'revisions'
				)
			)
		);
		
		register_post_type( 'dp_faculty',
			array(
			'label' 		=> 'Faculty',
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title' ,
						'editor' ,
						'revisions'
				)
			)
		);
		
		register_post_type( 'dp_department',
			array(
			'labels' 		=> array(
						'name' 			=> __( 'Departments' ),
						'singular_name' => __( 'Department' )
				),
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title',
						'editor',
						'revisions'
				),
			)
		);
		
		register_post_type( 'dp_policy',
			array(
			'labels' 		=> array(
						'name' 			=> __( 'Policies' ),
						'singular_name' => __( 'Policy' )
					),
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title' ,
						'editor' ,
						'revisions'
				)
			)
		);
	
	//Custom taxonomies
		//Department short codes, which will include colleges
		register_taxonomy( 'department_shortname', null, 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Departments' ),
							'singular_name'	=> __( 'Department' )
							),
				'public'	=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true
			)
		);
	
		//General Education categories
		register_taxonomy( 'general_education', 'dp_course', 
			array(
				'labels'	=> array(
							'name' 			=> __( 'GEs' ),
							'singular_name'	=> __( 'GE' )
							),
				'public'	=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true
			)
		);
		
		//Program degree levels (Minor, MA, BS etc)
		register_taxonomy( 'degree_level', 'dp_program', 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Degree Levels' ),
							'singular_name'	=> __( 'Degree Level' )
							),
				'public'	=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true
			)
		);
		
		//Program degree levels (Minor, MA, BS etc)
		register_taxonomy( 'policy_type', 'dp_policy', 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Policy Types' ),
							'singular_name'	=> __( 'Policy Type' )
							),
				'public'	=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true
			)
		);
	
	//Assign taxonomies for custom post types
		register_taxonomy_for_object_type( 'department_shortname', 'dp_course' );
		register_taxonomy_for_object_type( 'department_shortname', 'dp_program' );
		register_taxonomy_for_object_type( 'department_shortname', 'dp_faculty' );
		register_taxonomy_for_object_type( 'department_shortname', 'dp_department' );
		register_taxonomy_for_object_type( 'general_education', 'dp_course' );
		register_taxonomy_for_object_type( 'degree_level', 'dp_program' );
		register_taxonomy_for_object_type( 'policy_type', 'dp_policy' );
	} //csun create post type
	
} //dp_admin
?>