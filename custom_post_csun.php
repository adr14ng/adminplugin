<?php
/*
Plugin Name: CSUN Custom Post Types
Description: Adds custom post types and taxonomy for catalog
Version: 0.1
*/


	function csun_custom_activate() {
		if( !current_user_can('activate_plugins') )
			return;
			
		csun_create_post_type();
		flush_rewrite_rules();
	}//activate()
	register_activation_hook( __FILE__, 'csun_custom_activate');
	
	/**
	 * Unistalling plugin clean up
	 */
	function csun_custom_uninstall() {
		flush_rewrite_rules();
	}
	register_uninstall_hook( __FILE__, 'csun_custom_uninstall');
	
	
	/**
	 * Function to add custom post types
	 */
	function csun_create_post_type() {
		register_post_type( 'courses',
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
				),
			'rewrite' => array('slug' => 'courses'),
			'delete_with_user' => false,
			'map_meta_cap'  => true,
			)
		);
		
		register_post_type( 'programs',
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
				),
			'rewrite'       => array('slug' => 'programs'),
			'delete_with_user' => false,
			//'map_meta_cap'  => true,
			'capability_type' => 'program',
			'capabilities' => array(
				'read_post' => 'read_progam',
				'publish_posts' => 'publish_progams',
				'edit_posts' => 'edit_progams',
				'edit_others_posts' => 'edit_others_progams',
				'delete_posts' => 'delete_progams',
				'delete_others_posts' => 'delete_others_progams',
				'read_private_posts' => 'read_private_progams',
				'edit_post' => 'edit_progam',
				'delete_post' => 'delete_progam',
			),
			)
		);
		
		register_post_type( 'faculty',
			array(
			'label' 		=> 'Faculty',
			'public' 		=> true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title',
						'editor',
						'revisions'
				),
			'rewrite' => array('slug' => 'faculty'),
			'delete_with_user' => false,
			//'map_meta_cap'  => true,
			'capability_type' => 'faculty',
			'capabilities' => array(
				'read_post' => 'read_faculty',
				'publish_posts' => 'publish_facultys',
				'edit_posts' => 'edit_facultys',
				'edit_others_posts' => 'edit_others_facultys',
				'delete_posts' => 'delete_facultys',
				'delete_others_posts' => 'delete_others_facultys',
				'read_private_posts' => 'read_private_facultys',
				'edit_post' => 'edit_faculty',
				'delete_post' => 'delete_faculty',
			),
			)
		);
		
		register_post_type( 'departments',
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
			'rewrite' => array('slug' => 'departments'),
			'delete_with_user' => false,
			'map_meta_cap'  => true,
			)
		);
		
		register_post_type( 'policies',
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
				),
			'rewrite' => array('slug' => 'policies'),
			'delete_with_user' => false,
			'map_meta_cap'  => true,
			)
		);
		
		register_post_type('staract', 
			array(  
			//'label' => 'Star-act',
			'labels' => array (
			    'name' => 'Star-act',
			    'singular_name' => '',
			    'menu_name' => 'Star-act',
			    'add_new' => 'Add Star-act',
			    'add_new_item' => 'Add New Star-act',
			    'edit' => 'Edit',
			    'edit_item' => 'Edit Star-act',
			    'new_item' => 'New Star-act',
			    'view' => 'View Star-act',
			    'view_item' => 'View Star-act',
			    'search_items' => 'Search Star-act',
			    'not_found' => 'No Star-act Found',
			    'not_found_in_trash' => 'No Star-act Found in Trash',
			    'parent' => 'Parent Star-act',),
			'public' => true,
			'has_archive'	=> true,
			'menu_position'	=> 5,
			'supports' 		=> array(
						'title',
						'editor',
						'revisions'
				),
			'rewrite' => array('slug' => 'star-act'),
			'delete_with_user' => false,
			//'map_meta_cap'  => true,
			'capability_type' => 'plan',
			)	
		);
		
		register_post_type('plans', 
			array(	
				//'label' => 'Plans',
				'labels' => array (
					'name' => 'Plans',
					'singular_name' => 'plan',
					'menu_name' => 'Plans',
					'add_new' => 'Add plan',
					'add_new_item' => 'Add New plan',
					'edit' => 'Edit',
					'edit_item' => 'Edit plan',
					'new_item' => 'New plan',
					'view' => 'View plan',
					'view_item' => 'View plan',
					'search_items' => 'Search Plans',
					'not_found' => 'No Plans Found',
					'not_found_in_trash' => 'No Plans Found in Trash',
					'parent' => 'Parent plan',),
				'public' => true,
				'has_archive'	=> true,
				'menu_position'	=> 5,
				'supports' => array('title','editor','revisions',),
				'hierarchical' => false,
				'rewrite' => array('slug' => 'guides'),
				'delete_with_user' => false,
				//'map_meta_cap'  => true,
				'capability_type' => 'plan',
				'capabilities' => array(
					'read_post' => 'read_plan',
					'publish_posts' => 'publish_plans',
					'edit_posts' => 'edit_plans',
					'edit_others_posts' => 'edit_others_plans',
					'delete_posts' => 'delete_plans',
					'delete_others_posts' => 'delete_others_plans',
					'read_private_posts' => 'read_private_plans',
					'edit_post' => 'edit_plan',
					'delete_post' => 'delete_plan',
				),
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
				'hierarchical'		=> true,
				'capabilities' => array(
					'assign_terms' => 'edit_faculty'
				),
			)
		);
	
		//General Education categories
		register_taxonomy( 'general_education', 'courses', 
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
		
		//Program degree levels (Minor, Major, Masters etc)
		register_taxonomy( 'degree_level', 'programs', 
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
		register_taxonomy( 'policy_categories', 'policies', 
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
		
		//Policy Keywords (money, cheating)
		register_taxonomy( 'policy_keywords', 'policies', 
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
		
		//Year for star act and plans
		register_taxonomy( 'year', null, 
			array(
				'labels'	=> array(
							'name' 			=> __( 'Years' ),
							'singular_name'	=> __( 'Year' )
							),
				'public'	=> true,
				'show_tagcloud'		=> false,
				'hierarchical'		=> true,
				'capabilities' => array(
					'assign_terms' => 'edit_plan'
				),
			)
		);
	
	//Assign taxonomies for custom post types
		register_taxonomy_for_object_type( 'department_shortname', 'courses' );
		register_taxonomy_for_object_type( 'department_shortname', 'programs' );
		register_taxonomy_for_object_type( 'department_shortname', 'faculty' );
		register_taxonomy_for_object_type( 'department_shortname', 'departments' );
		register_taxonomy_for_object_type( 'department_shortname', 'plans' );
		register_taxonomy_for_object_type( 'department_shortname', 'staract' );
		register_taxonomy_for_object_type( 'general_education', 'courses' );
		register_taxonomy_for_object_type( 'degree_level', 'programs' );
		register_taxonomy_for_object_type( 'policy_categories', 'policies' );
		register_taxonomy_for_object_type( 'policy_keywords', 'policies' );
		register_taxonomy_for_object_type( 'year', 'plans' );
		register_taxonomy_for_object_type( 'year', 'staract' );
	} //csun create post type
	
	//Add custom post types
	add_action('init', 'csun_create_post_type' );
	
	
	//Register collumns for the custom taxonomy and types
	
	add_action( 'manage_posts_custom_column' , 'custom_columns', 10, 2 );

	function custom_columns( $column, $post_id ) {
		switch ( $column ) {
		case 'department' :
			$terms = get_the_term_list( $post_id , 'department_shortname' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;

		case 'year' :
			$terms = get_the_term_list( $post_id , 'year' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;
		
		case 'ge' :
			$terms = get_the_term_list( $post_id , 'general_education' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;
			
		case 'pol_cat' :
			$terms = get_the_term_list( $post_id , 'policy_categories' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;
		
		case 'pol_key' :
			$terms = get_the_term_list( $post_id , 'policy_keywords' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;
			
		case 'level' :
			$terms = get_the_term_list( $post_id , 'degree_level' , '' , ', ' , '' );
				if ( is_string( $terms ) )
				echo $terms;
			else
				_e( '-', 'your_text_domain' );
			break;
		}
	}
	
	add_filter('manage_edit-plans_columns', 'plan_columns');
	add_filter('manage_edit-staract_columns', 'plan_columns');
	function plan_columns($columns) {
		$columns['year'] = 'Year';
		$columns['department'] = 'Department';
		return $columns;
	}
	
	add_filter('manage_edit-faculty_columns', 'dept_columns');
	add_filter('manage_edit-departments_columns', 'dept_columns');
	function dept_columns($columns) {
		$columns['department'] = 'Department';
		return $columns;
	}
	
	add_filter('manage_edit-programs_columns', 'prog_columns');
	function prog_columns($columns) {
		$columns['department'] = 'Department';
		$columns['level'] = 'Degree';
		return $columns;
	}
	
	add_filter('manage_edit-courses_columns', 'course_columns');
	function course_columns($columns) {
		$columns['department'] = 'Department';
		$columns['ge'] = 'Gen Ed';
		return $columns;
	}
	
	add_filter('manage_edit-policies_columns', 'policy_columns');
	function policy_columns($columns) {
		$columns['pol_cat'] = 'Category';
		$columns['pol_key'] = 'Keywords';
		return $columns;
	}
		