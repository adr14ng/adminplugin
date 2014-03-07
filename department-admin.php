<?php
/** 
* Plugin Name: Department Admin 
* Description: Shows a simplified Admin View to department editors 
* Version: 1.0 
* Author: CSUN Undergraduate Studies 
*/

/**
* Change the login page. 
* Hooks onto login_head action.
*/

function new_custom_login_logo() {
    echo '<style type="text/css">
        h1 a { background-image:url(http://csuncatalog.com/wp-content/uploads/2013/09/logo3.png) !important; height:85px !important; width: 225px !important; background-size: auto auto !important;} 
		body.login {background-image: url(http://csuncatalog.com/wp-content/uploads/2013/09/bg.png) !important; background-size: 100%; background-repeat:no-repeat;}
		#nav, #backtoblog {display:none}	
		#loginform {opacity:0.90;}
		#loginform label {font-weight:bold;color:black}
		body.login div#login form#loginform input:focus#user_login {border-color:#990000}
		body.login div#login form#loginform input:focus#user_pass {border-color:#990000}
		</style>';
}
add_action('login_head', 'new_custom_login_logo');

/**
* Login redirect to dashpage based on user role
* Hooks onto login_redirect filter.
*
* @param string $redirect_to	URI sent after login.
*
* @return string
*/
function csun_login($redirect_to){
	//is there a user to check?
    global $user;
    if( isset( $user->roles ) && is_array( $user->roles ) ) {
        //check for admins
        if( in_array( "dp_editor", $user->roles ) || in_array( "dp_college", $user->roles )|| in_array( "dp_reviewer", $user->roles )) {
            // redirect them to the default place
            return admin_url('admin.php?page=review');
        } 
		else if( in_array( "dp_faculty", $user->roles ) ) {
            // redirect them to the default place
            return admin_url('edit.php?post_type=faculty');
        }
		else if( in_array( "dp_ar", $user->roles ) ) {
            // redirect them to the default place
            return admin_url('edit.php?post_type=plans');
        }
		else if( in_array( "dp_policy", $user->roles ) ) {
            // redirect them to the default place
            return admin_url('edit.php?post_type=policies');
        }
		else if( in_array( "dp_pages", $user->roles ) ) {
            // redirect them to the default place
            return admin_url('edit.php?post_type=page');
        }
		else {
			return admin_url('index.php');
		}
	}
	else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'csun_login');

/**
* Change the Howdy,
* Hooks onto gettext filter.
*
* @param string $translation	Translated text.
* @param string $text			Text to translate.
* @param string $domain			Translation domain (multiple languages allowed).
*
* @return string
*/
function change_howdy($translated, $text, $domain) {
	$message = get_option( 'main_dp_settings');	//get message option
	$message = $message['username_text'];

    if ('default' != $domain)
        return $translated;

    if (false !== strpos($translated, 'Howdy'))
        return str_replace('Howdy,', $message, $translated);

    return $translated;
}
 add_filter('gettext', 'change_howdy', 10, 3);

//Is this admin pages?
if ( is_admin() ) {
	$plug_in_dir = dirname(__FILE__);
	
    //Load the plugin
    require $plug_in_dir . '/includes/dp-admin-core.php';
	$dp_new_admin = new DP_Admin();
	
	//Activate plugin
	register_activation_hook( __FILE__, array( 'DP_Admin', 'activate'));	
	
	//Delete plugin
	register_uninstall_hook( __FILE__, array( 'DP_Admin', 'uninstall'));	
	
	//Add filter to allow departments to edit own files
	add_filter( 'map_meta_cap', array( 'DP_Admin','match_category_user'), 10, 4);	
	
	//Add a basic style to the pages
	add_action('admin_enqueue_scripts', array( 'DP_Admin', 'add_base_style'));	
	
	//Add new toolbar 
	add_filter( 'acf/fields/wysiwyg/toolbars' , array( 'DP_Admin', 'my_toolbars'));	

	//Change wordpress toolbar
	add_filter('tiny_mce_before_init', array( 'DP_Admin', 'csunFormatTinyMCE') );
	
	//Add menu for proposal files
	require $plug_in_dir . '/includes/proposal-files.php';
	add_action( 'admin_menu', 'add_proposal_menu' );
	
	//Add settings page
	require $plug_in_dir . '/includes/dp-options.php';
	$dp_settings_page = new DPAdminSettings();

	//Add menu for review page
	require $plug_in_dir . '/includes/review.php';
	add_action( 'admin_menu', 'add_review_menu' );
	
	//Chage layout for department editors
	add_action('init', array( 'DP_Admin', 'change_layout'));	
	
	//Make editor posts save as pending
	add_action( 'post_updated', array( 'DP_Admin', 'make_pending_post'), 10, 3 );
	
	//Add custom footer 	
	add_filter('admin_footer_text', array( 'DP_Admin', 'csun_footer_admin') );
	add_filter( 'update_footer', array( 'DP_Admin', 'replace_footer_version'), 11);
	
	//Add custom colors
	add_action( 'admin_init' , array( 'DP_Admin', 'add_csun_colors'));
	
	//Change update to save
	add_filter( 'gettext', array( 'DP_Admin', 'change_publish_button'), 10, 2 );
}//is_admin()
