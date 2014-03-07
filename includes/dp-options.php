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
    /**
     * Holds the values to be used in the fields callbacks
     */
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
            'Review Deadline', // Title 
            array( $this, 'review_deadline_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        );
	
		add_settings_field(
            'review_field_key', // ID
            'ACF Review Field Key', // Title 
            array( $this, 'review_field_key_callback'), // Callback
            'dp-admin-options', // Page
            'dp-main-settings' // Section           
        ); 
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['welcome_message'] ) )
            $new_input['welcome_message'] = sanitize_text_field( $input['welcome_message'] );
			
		if( isset( $input['username_text'] ) )
            $new_input['username_text'] = sanitize_text_field( $input['username_text'] );

        if( isset( $input['view_all_message'] ) )
            $new_input['view_all_message'] = sanitize_text_field( $input['view_all_message'] );
			
		if( isset( $input['course_message'] ) )
            $new_input['course_message'] = sanitize_text_field( $input['course_message'] );
			
		if( isset( $input['file_message'] ) )
            $new_input['file_message'] = sanitize_text_field( $input['file_message'] );
			
		if( isset( $input['review_deadline'] ) )
            $new_input['review_deadline'] = sanitize_text_field( $input['review_deadline'] );
		
		if( isset( $input['review_field_key'] ) )
            $new_input['review_field_key'] = sanitize_key( $input['review_field_key'] );

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
	
	function username_text_callback()
    {
        printf(
            '<input type="text" id="username_text" name="main_dp_settings[username_text]" value="%s" />',
            isset( $this->options['username_text'] ) ? esc_attr( $this->options['username_text']) : ''
        );
    }
	
	function view_all_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="view_all_message" name="main_dp_settings[view_all_message]" class="large-text code">%s</textarea>',
            isset( $this->options['view_all_message'] ) ? esc_attr( $this->options['view_all_message']) : ''
        );
    }
	
	function course_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="course_message" name="main_dp_settings[course_message]" class="large-text code">%s</textarea>',
            isset( $this->options['course_message'] ) ? esc_attr( $this->options['course_message']) : ''
        );
    }
	
	function file_message_callback()
    {
        printf(
            '<textarea rows="4" cols="50" id="file_message" name="main_dp_settings[file_message]" class="large-text code">%s</textarea>',
            isset( $this->options['file_message'] ) ? esc_attr( $this->options['file_message']) : ''
        );
    }
	
	function review_deadline_callback()
    {
        printf(
            '<input type="text" id="review_deadline" name="main_dp_settings[review_deadline]" value="%s" />',
            isset( $this->options['review_deadline'] ) ? esc_attr( $this->options['review_deadline']) : ''
        );
    }
	
	function review_field_key_callback()
    {
        printf(
            '<input type="text" id="review_field_key" name="main_dp_settings[review_field_key]" value="%s" />',
            isset( $this->options['review_field_key'] ) ? esc_attr( $this->options['review_field_key']) : ''
        );
    }
}
