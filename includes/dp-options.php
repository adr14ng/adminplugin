<?php
/** * * * * * * * * * * * * * * * * * * * *
 *
 *	Settings
 *	
 *	Custom settings including messages for
 *	the editors home page, the course page,
 *  files page and due date.
 *
 *	!The custom field for Review Status must
 *	exist and have it's setting updated
 *
 * 	CSUN Department of Undergraduate Studies
 * 	2013-2014
 *
 * * * * * * * * * * * * * * * * * * * * * */

class DPAdminSettings
{
    //Holds the values to be used in the fields callbacks
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_option_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_option_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Department Admin Settings', 
            'Department Admin Settings', 
            'manage_options', 
            'dp-admin-options', 
            array( $this, 'dpadmin_option_page' )
        );
    }

    /**
     * Options page callback
     */
    public function dpadmin_option_page()
    {
        // Set class property
        $this->options = get_option( 'main_dp_settings' );
        ?>
        <div class="wrap">
            <h2>Department Admin Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'dp-admin-group' );   
                do_settings_sections( 'dp-admin-options' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'dp-admin-group', // Option group
            'main_dp_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'dp-main-settings', // ID
            'Department Admin Settings', // Title
            array( $this, 'print_dp_settings_info' ), // Callback
            'dp-admin-options' // Page
        );  
		
		add_settings_field(
            'welcome_message', // ID
            'Welcome Message', // Title 
            array( $this, 'welcome_message_callback' ), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 

        add_settings_field(
            'username_text', // ID
            'Username Text', // Title 
            array( $this, 'username_text_callback' ), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 

		add_settings_field(
            'view_all_message', // ID
            'Edit All Message', // Title 
            array( $this, 'view_all_message_callback' ), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
		
		add_settings_field(
            'course_message', // ID
            'Courses Message', // Title 
            array( $this, 'course_message_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'file_message', // ID
            'Files Message', // Title 
            array( $this, 'file_message_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'review_deadline', // ID
            'Department Review Deadline', // Title 
            array( $this, 'review_deadline_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
	
		add_settings_field(
            'college_deadline', // ID
            'College Review Deadline', // Title 
            array( $this, 'college_deadline_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'tseng_description', // ID
            'Self Support Description', // Title 
            array( $this, 'tseng_description_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
		
		add_settings_field(
            'tseng_both', // ID
            'Both Supports Description', // Title 
            array( $this, 'tseng_both_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
		
		add_settings_field(
            'planning_year', // ID
            'Planning Year', // Title 
            array( $this, 'planning_year_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
		
		add_settings_field(
            'old_planning_year', // ID
            'Plan Message End Year', // Title 
            array( $this, 'old_planning_year_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'old_plan_message', // ID
            'Plan Message', // Title 
            array( $this, 'old_plan_message_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'course_semester', // ID
            'OMAR Course Semester', // Title 
            array( $this, 'course_semester_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
		
		add_settings_field(
            'course_semester2', // ID
            'OMAR Additional Course Semester', // Title 
            array( $this, 'course_semester2_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
	 *
	 * @return array Updated with new settings fields
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['welcome_message'] ) )
		{
			$welcome = $input['welcome_message'];
			$welcome = wp_check_invalid_utf8( $welcome, true );
			$welcome = stripslashes($welcome);
			$welcome = strip_tags($welcome, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$welcome = balanceTags($welcome);
            $new_input['welcome_message'] = $welcome;
		}
			
		if( isset( $input['username_text'] ) )
		{
            $new_input['username_text'] = sanitize_text_field( $input['username_text'] );
		}

        if( isset( $input['view_all_message'] ) )
		{
			$all = $input['view_all_message'];
			$all = wp_check_invalid_utf8( $all, true );
			$all = stripslashes($all);
			$all = strip_tags($all, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$all = balanceTags($all);
            $new_input['view_all_message'] = $all;
		}
			
		if( isset( $input['course_message'] ) )
		{
			$course = $input['course_message'];
			$course = wp_check_invalid_utf8( $course, true );
			$course = stripslashes($course);
			$course = strip_tags($course, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$course = balanceTags($course);
            $new_input['course_message'] = $course;
		}
			
		if( isset( $input['file_message'] ) )
		{
            $file = $input['file_message'];
			$file = wp_check_invalid_utf8( $file, true );
			$file = stripslashes($file);
			$file = strip_tags($file, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$file = balanceTags($file);
            $new_input['file_message'] = $file;
		}
		
		if( isset( $input['tseng_description'] ) )
		{
            $file = $input['tseng_description'];
			$file = wp_check_invalid_utf8( $file, true );
			$file = stripslashes($file);
			$file = strip_tags($file, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$file = balanceTags($file);
            $new_input['tseng_description'] = $file;
		}
		
		if( isset( $input['tseng_both'] ) )
		{
            $file = $input['tseng_both'];
			$file = wp_check_invalid_utf8( $file, true );
			$file = stripslashes($file);
			$file = strip_tags($file, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$file = balanceTags($file);
            $new_input['tseng_both'] = $file;
		}
			
		if( isset( $input['review_deadline'] ) )
		{
            $new_input['review_deadline'] = sanitize_text_field( $input['review_deadline'] );
		}
			
		if( isset( $input['college_deadline'] ) )
		{
            $new_input['college_deadline'] = sanitize_text_field( $input['college_deadline'] );
		}
			
		if( isset( $input['planning_year'] ) )
		{
            $new_input['planning_year'] = sanitize_title( $input['planning_year'] );
		}
		
		if( isset( $input['old_planning_year'] ) )
		{
            $new_input['old_planning_year'] = sanitize_title( $input['old_planning_year'] );
		}
		
		if( isset( $input['old_plan_message'] ) )
		{
			$course = $input['old_plan_message'];
			$course = wp_check_invalid_utf8( $course, true );
			$course = stripslashes($course);
			$course = strip_tags($course, '<a><p><h2><h3><em><strong><ul><li><ol>');
			$course = balanceTags($course);
            $new_input['old_plan_message'] = $course;
		}
			
		if( isset( $input['course_semester'] ) )
		{
            $new_input['course_semester'] = sanitize_title( $input['course_semester'] );
		}
		
		if( isset( $input['course_semester2'] ) )
		{
            $new_input['course_semester2'] = sanitize_title( $input['course_semester2'] );
		}

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_dp_settings_info()
    {
        print 'Enter your settings below:';
    }

   /** 
     * Get the settings option array and print each settings current value
     */
    function welcome_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="welcome_message" name="main_dp_settings[welcome_message]" class="large-text code">%s</textarea>',
            isset( $this->options['welcome_message'] ) ? esc_attr( $this->options['welcome_message']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function username_text_callback()
    {
        printf(
            '<input type="text" id="username_text" name="main_dp_settings[username_text]" value="%s" />',
            isset( $this->options['username_text'] ) ? esc_attr( $this->options['username_text']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function view_all_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="view_all_message" name="main_dp_settings[view_all_message]" class="large-text code">%s</textarea>',
            isset( $this->options['view_all_message'] ) ? esc_attr( $this->options['view_all_message']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function course_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="course_message" name="main_dp_settings[course_message]" class="large-text code">%s</textarea>',
            isset( $this->options['course_message'] ) ? esc_attr( $this->options['course_message']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function file_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="file_message" name="main_dp_settings[file_message]" class="large-text code">%s</textarea>',
            isset( $this->options['file_message'] ) ? esc_attr( $this->options['file_message']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
    function tseng_description_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="tseng_description" name="main_dp_settings[tseng_description]" class="large-text code">%s</textarea>',
            isset( $this->options['tseng_description'] ) ? esc_attr( $this->options['tseng_description']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
    function tseng_both_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="tseng_both" name="main_dp_settings[tseng_both]" class="large-text code">%s</textarea>',
            isset( $this->options['tseng_both'] ) ? esc_attr( $this->options['tseng_both']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function review_deadline_callback()
    {
        printf(
            '<input type="text" id="review_deadline" name="main_dp_settings[review_deadline]" value="%s" />',
            isset( $this->options['review_deadline'] ) ? esc_attr( $this->options['review_deadline']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function college_deadline_callback()
    {
        printf(
            '<input type="text" id="college_deadline" name="main_dp_settings[college_deadline]" value="%s" />',
            isset( $this->options['college_deadline'] ) ? esc_attr( $this->options['college_deadline']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function planning_year_callback()
    {
        printf(
            '<input type="text" id="planning_year" name="main_dp_settings[planning_year]" value="%s" />',
            isset( $this->options['planning_year'] ) ? esc_attr( $this->options['planning_year']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function old_planning_year_callback()
    {
        printf(
            '<input type="text" id="old_planning_year" name="main_dp_settings[old_planning_year]" value="%s" />',
            isset( $this->options['old_planning_year'] ) ? esc_attr( $this->options['old_planning_year']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
    function old_plan_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="old_plan_message" name="main_dp_settings[old_plan_message]" class="large-text code">%s</textarea>',
            isset( $this->options['old_plan_message'] ) ? esc_attr( $this->options['old_plan_message']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function course_semester_callback()
    {
        printf(
            '<input type="text" id="course_semester" name="main_dp_settings[course_semester]" value="%s" />',
            isset( $this->options['course_semester'] ) ? esc_attr( $this->options['course_semester']) : ''
        );
    }
	
	/** 
     * Get the settings option array and print each settings current value
     */
	function course_semester2_callback()
    {
        printf(
            '<input type="text" id="course_semester2" name="main_dp_settings[course_semester2]" value="%s" />',
            isset( $this->options['course_semester2'] ) ? esc_attr( $this->options['course_semester2']) : ''
        );
    }

}
