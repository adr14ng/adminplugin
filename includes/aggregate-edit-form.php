<?php
/**
 * Edit pages for Aggregate Editing
 * Includes list page and edit page
 */
 
//need to enable url fopen
$admin_url = admin_url('/wp-admin');

function add_aggregate_menu()
{
	add_menu_page( 'Edit Department Page', 'View All', 'edit_posts', 
				'dp_page', 'aggregate_post', $icon, 19 ); //need icon
}

//function that generates the aggregate post page
function aggregate_post() {
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$userCat = get_user_meta($user_id, 'user_cat');
	
	if(isset($_REQUEST['cat'])) //if we already have the category page request
	{
		edit_aggregate_post();
	}
	/*else if(count($userCat == 1)){ //if there is only one possible category page
		$userCat = $userCat[0];
		//redirect to the proper cat page
		wp_redirect(get_aggregate_edit_link($userCat, ''));
		exit;
	}*/
	else //if there are multiple pages, list them
	{
		list_aggregate_post();
	}
}

//if user has more than one category, list department pages
function list_aggregate_post() {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require( dirname(__FILE__) . '/class-dp-aggregate-list-table.php' );
	
	//Do I need to set up something else?
	
	//Create and display the aggregate list table
	$aggr_list_table = new Aggregate_List_Table();
	$aggr_list_table->prepare_items();
	$aggr_list_table->display();
}

function edit_aggregate_post(){
	/******************************************
	 * Get posts for category
	 *****************************************/
	 if($post_cat = $_REQUEST['cat'] ){
		$term_id = term_exists( $post_cat );
		
		if($term_id != 0){
			$args=array(
				'post_type' => array('dp_department', 'dp_program'),
				'post__not_in' => $ids, // avoid duplicate posts
				'department_shortname' => $post_cat,
			);
			
			$posts = get_posts( $args ); 
		}
		else{
			wp_die(__( 'Department does not exist' ));
		}
	}
	else
		wp_die(__( 'Not enough information' ));
		
	if( !$posts )
		wp_die(__( 'No posts in this category' ));
		
	/********************************************
	 * Build Overall Page
	 ********************************************/
	$action ='edit';

	/*********************************************
	 * Build Form for each post
	 ********************************************/
		//need to wrap in a div to hide/show
		//need to edit form name HOOK: do_action('post_edit_form_tag', $post);
	foreach ($posts as $post) {
		$post_ID = $post->ID;
		$post_type = $post->post_type;
		
		include('edit-form.php');

	}
}

//Returns a link to the aggregate edit page
//Used for building the table and redirects
function get_aggregate_edit_link($cat, $context='') {
	$sformat = 'admin.php?page=dp_page&cat=%s';
	
	if( 'display' == $context)
		$action = '&amp;action=edit';
	else
		$action = '&action=edit';
	
	return admin_url(sprintf($sformat . $action, $cat));
}


//Update Posts
if($action=isset($_POST['action']) {
	if($action==='editpost'){
			check_admin_referer('update-post_' . $post_id);
	
	if ( empty($post_data) )
		$post_data = &$_POST;

	// Clear out any data in internal vars.
	unset( $post_data['filter'] );

	$post_ID = (int) $post_data['post_ID'];
	$post = get_post( $post_ID );
	$post_data['post_type'] = $post->post_type;
	$post_data['post_mime_type'] = $post->post_mime_type;

	$ptype = get_post_type_object($post_data['post_type']);
	if ( !current_user_can( 'edit_post', $post_ID ) ) {
		if ( 'page' == $post_data['post_type'] )
			wp_die( __('You are not allowed to edit this page.' ));
		else
			wp_die( __('You are not allowed to edit this post.' ));
	}

	$post_data = _wp_translate_postdata( true, $post_data );
	if ( is_wp_error($post_data) )
		wp_die( $post_data->get_error_message() );
	if ( ( empty( $post_data['action'] ) || 'autosave' != $post_data['action'] ) && 'auto-draft' == $post_data['post_status'] ) {
		$post_data['post_status'] = 'draft';
	}

	if ( isset($post_data['visibility']) ) {
		switch ( $post_data['visibility'] ) {
			case 'public' :
				$post_data['post_password'] = '';
				break;
			case 'password' :
				unset( $post_data['sticky'] );
				break;
			case 'private' :
				$post_data['post_status'] = 'private';
				$post_data['post_password'] = '';
				unset( $post_data['sticky'] );
				break;
		}
	}

	// Post Formats
	if ( isset( $post_data['post_format'] ) )
		set_post_format( $post_ID, $post_data['post_format'] );

	$format_meta_urls = array( 'url', 'link_url', 'quote_source_url' );
	foreach ( $format_meta_urls as $format_meta_url ) {
		$keyed = '_format_' . $format_meta_url;
		if ( isset( $post_data[ $keyed ] ) )
			update_post_meta( $post_ID, $keyed, wp_slash( esc_url_raw( wp_unslash( $post_data[ $keyed ] ) ) ) );
	}

	$format_keys = array( 'quote', 'quote_source_name', 'image', 'gallery', 'audio_embed', 'video_embed' );

	foreach ( $format_keys as $key ) {
		$keyed = '_format_' . $key;
		if ( isset( $post_data[ $keyed ] ) ) {
			if ( current_user_can( 'unfiltered_html' ) )
				update_post_meta( $post_ID, $keyed, $post_data[ $keyed ] );
			else
				update_post_meta( $post_ID, $keyed, wp_filter_post_kses( $post_data[ $keyed ] ) );
		}
	}

	// Meta Stuff
	if ( isset($post_data['meta']) && $post_data['meta'] ) {
		foreach ( $post_data['meta'] as $key => $value ) {
			if ( !$meta = get_post_meta_by_id( $key ) )
				continue;
			if ( $meta->post_id != $post_ID )
				continue;
			if ( is_protected_meta( $value['key'], 'post' ) || ! current_user_can( 'edit_post_meta', $post_ID, $value['key'] ) )
				continue;
			update_meta( $key, $value['key'], $value['value'] );
		}
	}

	if ( isset($post_data['deletemeta']) && $post_data['deletemeta'] ) {
		foreach ( $post_data['deletemeta'] as $key => $value ) {
			if ( !$meta = get_post_meta_by_id( $key ) )
				continue;
			if ( $meta->post_id != $post_ID )
				continue;
			if ( is_protected_meta( $meta->meta_key, 'post' ) || ! current_user_can( 'delete_post_meta', $post_ID, $meta->meta_key ) )
				continue;
			delete_meta( $key );
		}
	}

	// Attachment stuff
	if ( 'attachment' == $post_data['post_type'] ) {
		if ( isset( $post_data[ '_wp_attachment_image_alt' ] ) ) {
			$image_alt = wp_unslash( $post_data['_wp_attachment_image_alt'] );
			if ( $image_alt != get_post_meta( $post_ID, '_wp_attachment_image_alt', true ) ) {
				$image_alt = wp_strip_all_tags( $image_alt, true );
				// update_meta expects slashed
				update_post_meta( $post_ID, '_wp_attachment_image_alt', wp_slash( $image_alt ) );
			}
		}

		$attachment_data = isset( $post_data['attachments'][ $post_ID ] ) ? $post_data['attachments'][ $post_ID ] : array();
		$post_data = apply_filters( 'attachment_fields_to_save', $post_data, $attachment_data );
	}
	
	$contentName= 'content'.$post_ID;
	if(isset($post_data[$contentName])) {
		$post_data['post_content']=$post_data[$contentName];
		unset($post_data[$contentName]);
	}

	add_meta( $post_ID );

	update_post_meta( $post_ID, '_edit_last', $GLOBALS['current_user']->ID );

	wp_update_post( $post_data );

	// Now that we have an ID we can fix any attachment anchor hrefs
	_fix_attachment_links( $post_ID );

	wp_set_post_lock( $post_ID );

	if ( current_user_can( $ptype->cap->edit_others_posts ) ) {
		if ( ! empty( $post_data['sticky'] ) )
			stick_post( $post_ID );
		else
			unstick_post( $post_ID );
	}

	// Session cookie flag that the post was saved
	if ( isset( $_COOKIE['wp-saving-post-' . $post_id] ) )
		setcookie( 'wp-saving-post-' . $post_id, 'saved' );

	redirect_post($post_id); // Send user on their way while we keep working

	exit();
	}

}

?>