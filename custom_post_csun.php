<?php
/*
Plugin Name: CSUN Custom Post Types
Description: Adds custom post types and taxonomy for catalog
Version: 0.1
*/


	function activate() {
		if( !current_user_can('activate_plugins') )
			return;
			
		csun_create_post_type();
		flush_rewrite_rules();
	}//activate()
	register_activation_hook( __FILE__, 'activate');
	
	/**
	 * Unistalling plugin clean up
	 */
	function uninstall() {
		flush_rewrite_rules();
	}
	register_uninstall_hook( __FILE__, 'uninstall');
	
	
	/**
	 * Function to add custom post types
	 */
	function csun_create_post_type() {
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
						'title',
						'editor',
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
						'title',
						'editor',
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
						'title',
						'editor',
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
						'title',
						'editor',
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
		
		//Policy Types (Fees, Conduct)
		register_taxonomy( 'policy_type', 'dp_policy', 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Policy Types' ),
							'singular_name'	=> __( 'Policy Type' )
							),
				'public'			=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true
			)
		);
		
		//Policy Types (Fees, Conduct)
		register_taxonomy( 'policy_keywords', 'dp_policy', 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Policy Keywords' ),
							'singular_name'	=> __( 'Policy Keyword' )
							),
				'public'			=> true,
				'show_tagcloud'		=> true,
				'hierarchical'		=> false
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
		register_taxonomy_for_object_type( 'policy_keywords', 'dp_policy' );
	} //csun create post type
	
	//Add custom post types
	add_action('init', 'csun_create_post_type' );