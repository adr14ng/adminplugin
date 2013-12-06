<?php
/**
 * Edit form customized for Aggregate Edit Pages
 */

/* NOTE: ACF
 * In order for advanced custom fields to validate our page the following
 * acf.php
	input.min.js -> input.js

 * field_group.php
	in function validate_page()
		//validate page (Aggregate Edit)
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['cat'] ) )
		{
			$return = true;
		}


 * post.php 
 * 	in function validate_page()
		//validate page (Aggregate Edit)
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['cat'] ) )
		{
			$return = true;
		}

 * 	under add_action('admin_head', 
		add_action('custom-fields', array($this,'admin_head'));


 * 	in meta_box_input
	$post_ID=$post->ID; //get post ID

 * 		replace js
		//Modified to make IDs unique
		<?php if(isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['cat'] ) ):?>
			document.getElementById('<?php echo $id; ?>').id = '<?php echo $id.'_'.$post_ID; ?>';
			
			$('#<?php echo $id.'_'.$post_ID ?>').addClass('<?php echo $class; ?>').removeClass('hide-if-js');
			$('#adv-settings label[for="<?php echo $id.'_'.$post_ID; ?>-hide"]').addClass('<?php echo $toggle_class; ?>');
		<?php else : ?>
			$('#<?php echo $id; ?>').addClass('<?php echo $class; ?>').removeClass('hide-if-js');
			$('#adv-settings label[for="<?php echo $id; ?>-hide"]').addClass('<?php echo $toggle_class; ?>');
		<?php endif; ?>

 * input.js
	$(document).trigger('acf/setup_fields', [ $('.poststuff') ]);
 	$(document).trigger('acf/setup_fields', [ $('#poststuff') ]);
	
 * Alternatively you can modify the js, minify it and replace the original minified version with your own. 
 * If you do this, do not modify acf.php
 */
 
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
	
if ( wp_is_mobile() )
	wp_enqueue_script( 'jquery-touch-punch' );
/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';

//Fix screen variables
$screen = get_current_screen();
$screen->base = 'post';
$screen->post_type = $post_type;
$screen->parent_base = 'edit';
$screen->parent_file = 'edit.php?post_type='.$post_type;
$screen->id = $post_type;


do_action('custom-fields');
do_action('admin_head');

if ( post_type_supports($post_type, 'editor') || post_type_supports($post_type, 'thumbnail') ) {
	add_thickbox();
	wp_enqueue_media( array( 'post' => $post_ID ) );
}

// Add the local autosave notice HTML
add_action( 'admin_footer', '_local_storage_notice' );

$messages = array();
$messages['post'] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => sprintf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 2 => __('Custom field updated.'),
	 3 => __('Custom field deleted.'),
	 4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
	 7 => __('Post saved.'),
	 8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	 9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
);

$messages = apply_filters( 'post_updated_messages', $messages );

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) )
		$message = $messages[$post_type][$_GET['message']];
	elseif ( !isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]) )
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ( 'auto-draft' == $post->post_status ) {
	if ( 'edit' == $action )
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave( $post_ID );
}

$form_action = 'editpost';
$nonce_action = 'update-post_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post_ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ( $autosave && mysql2date( 'U', $autosave->post_modified_gmt, false ) > mysql2date( 'U', $post->post_modified_gmt, false ) ) {
	foreach ( _wp_post_revision_fields() as $autosave_field => $_autosave_field ) {
		if ( normalize_whitespace( $autosave->$autosave_field ) != normalize_whitespace( $post->$autosave_field ) ) {
			$notice = sprintf( __( 'There is an autosave of this post that is more recent than the version below. <a href="%s">View the autosave</a>' ), get_edit_post_link( $autosave->ID ) );
			break;
		}
	}
	// If this autosave isn't different from the current post, begone.
	if ( ! $notice )
		wp_delete_post_revision( $autosave->ID );
	unset($autosave_field, $_autosave_field);
}

$post_type_object = get_post_type_object($post_type);

// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once(ABSPATH.'wp-admin/includes/meta-boxes.php');

$publish_callback_args = null;
if ( post_type_supports($post_type, 'revisions') && 'auto-draft' != $post->post_status ) {
	$revisions = wp_get_post_revisions( $post_ID );
	
	// Check if the revisions have been upgraded
	if ( ! empty( $revisions ) && _wp_get_post_revision_version( end( $revisions ) ) < 1 )
		_wp_upgrade_revisions_of_post( $post, $revisions );
	// We should aim to show the revisions metabox only when there are revisions.
	if ( count( $revisions ) > 1 ) {
		reset( $revisions ); // Reset pointer for key()
		$publish_callback_args = array( 'revisions_count' => count( $revisions ), 'revision_id' => key( $revisions ) );
		add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', null, 'normal', 'core');
	}
}
		

add_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'side', 'core', $publish_callback_args );


if ( post_type_supports($post_type, 'page-attributes') )
	add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', null, 'side', 'core');

if ( post_type_supports($post_type, 'custom-fields') )
	add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', null, 'normal', 'core');

do_action('dbx_post_advanced', $post);

do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'side', $post);
do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);


add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );

require_once(ABSPATH.'wp-admin/admin-header.php');

?>

<div class="wrap">
<?php if ( $notice ) : ?>
<div id="notice<?php echo '-'.$post_ID; ?>" class="error"><p id="has-newer-autosave<?php echo '-'.$post_ID; ?>"><?php echo $notice ?></p></div>
<?php endif; ?>

<div id="message<?php echo '-'.$post_ID; ?>" class="updated <?php if($message) echo 'visible'; else echo 'invisible';?>"><p>
<?php if($message) echo $message; else echo "Posts Updated";?>
</p></div>

<div id="lost-connection-notice<?php echo '-'.$post_ID; ?>" class="error hidden">
	<p><span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
	<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
	</p>
</div>

<form name="post<?php echo '-'.$post_ID; ?>" action="<?php echo admin_url(post);?>.php" method="post" id="post<?php echo '-'.$post_ID; ?>" <?php do_action('post_edit_form_tag', $post); ?> class="dp-editform">
<?php wp_nonce_field($nonce_action); ?>
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ) ?>" />
<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ) ?>" />
<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post_type ) ?>" />
<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status) ?>" />
<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
<?php if ( ! empty( $active_post_lock ) ) { ?>
<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" />
<?php
}
if ( 'draft' != get_post_status( $post ) )
	wp_original_referer_field(true, 'previous');

echo $form_extra;

wp_nonce_field( 'autosave', 'autosavenonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>

<div id="poststuff" class="poststuff">
<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
<div id="post-body-content">

<?php if ( post_type_supports($post_type, 'title') ) { ?>
<div id="titlediv">
<div id="titlewrap">
	<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo apply_filters( 'enter_title_here', __( 'Enter title here' ), $post ); ?></label>
	<input type="text" name="post_title" size="30" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title-<?php echo $post_ID; ?>" autocomplete="off" />
</div>
<div class="inside">
<?php
$sample_permalink_html = $post_type_object->public ? aggr_sample_permalink_html($post->ID) : '';
$shortlink = wp_get_shortlink($post->ID, 'post');
if ( !empty($shortlink) )
    $sample_permalink_html .= '<input id="shortlink-'.$post_ID.'" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink-'.$post_ID.'\').val()); return false;">' . __('Get Shortlink') . '</a>';

if ( $post_type_object->public && ! ( 'pending' == get_post_status( $post ) && !current_user_can( $post_type_object->cap->publish_posts ) ) ) {
	$has_sample_permalink = $sample_permalink_html && 'auto-draft' != $post->post_status;
?>
	<div id="edit-slug-box-<?php echo $post_ID; ?>" class="hide-if-no-js">
	<?php
		if ( $has_sample_permalink )
			echo $sample_permalink_html;
	?>
	</div>
<?php
}
?>
</div>
<?php
wp_nonce_field( 'samplepermalink', 'samplepermalinknonce-'.$post_ID, false );
?>
</div><!-- /titlediv -->
<?php
}

do_action( 'edit_form_after_title', $post );

if ( post_type_supports($post_type, 'editor') ) {
?>
<div id="postdivrich" class="postarea edit-form-section">

<?php wp_editor( $post->post_content, 'content'.$post_ID, array(
	'media_buttons' => false,
	//'dfw' => true,
	'tabfocus_elements' => 'insert-media-button,save-post',
	'editor_class' => 'dp_editor',
	'editor_height' => 360,
	//'tinymce' => array ('height' => "321px",),
	//'editor_height' => 360,
) ); ?>
<table id="post-status-info" cellspacing="0"><tbody><tr>
	<td id="wp-word-count"><?php printf( __( 'Word count: %s' ), '<span class="word-count">0</span>' ); ?></td>
	<td class="autosave-info">
	<span class="autosave-message">&nbsp;</span>
<?php
	if ( 'auto-draft' != $post->post_status ) {
		echo '<span id="last-edit">';
		if ( $last_id = get_post_meta($post_ID, '_edit_last', true) ) {
			$last_user = get_userdata($last_id);
			printf(__('Last edited by %1$s on %2$s at %3$s'), esc_html( $last_user->display_name ), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		} else {
			printf(__('Last edited on %1$s at %2$s'), mysql2date(get_option('date_format'), $post->post_modified), mysql2date(get_option('time_format'), $post->post_modified));
		}
		echo '</span>';
	} ?>
	</td>
</tr></tbody></table>

</div>
<?php }

do_action( 'edit_form_after_editor', $post );

?>
</div><!-- /post-body-content -->

<div id="postbox-container-1" class="postbox-container">
<?php

do_action('submitpost_box', $post);

//do_meta_boxes(null, 'side', $post);

do_meta_boxes($post_type, 'side', $post);
?>

<div id="submitalldiv" class="postbox ">
<h3><span>Update Content</span></h3>
<div class="inside">
<div class="submitbox" id="submitall">
<button type="button" class="btn btn-primary submitall">Update</button>
</div>
</div>
</div>

<div id="coursesdiv" class="postbox ">
	<h3><span>Courses</span></h3>
	<div class="inside">
		<div class="coursebox" id="courses">
			<p>Click below to edit this department's courses.</p>
			<p>This will navigate away from this page.
			<em>Remember to save before proceding</em></p>
			<a href="<?php echo admin_url('edit.php?post_type=courses&department_shortname='.$post_cat); ?>" title="courses">
				<button id="course" type="button" class="btn btn-success">Courses</button>
			</a>
		</div>
	</div>
</div>
</div>

<div id="postbox-container-2" class="postbox-container">
<?php

do_meta_boxes(null, 'normal', $post);

do_action('edit_form_advanced', $post);

do_meta_boxes(null, 'advanced', $post);

?>
</div>
<?php

do_action('dbx_post_sidebar', $post);

?>
</div><!-- /post-body -->
<br class="clear" />
</div><!-- /poststuff -->
</form>
</div>

<?php
if ( post_type_supports( $post_type, 'comments' ) )
	wp_comment_reply();
?>

<?php if ( (isset($post->post_title) && '' == $post->post_title) || (isset($_GET['message']) && 2 > $_GET['message']) ) : ?>
<script type="text/javascript">
try{document.post.title.focus();}catch(e){}
</script>
<?php endif; ?>
