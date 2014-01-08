This plug-in is created to modify the admin experience of the CSUN catalog website.


To use with Advanced Custom Fields (3.0.0) the following files must be edited in the following way:

acf.php
	input.min.js -> input.js

/core/controllers/field_group.php
	in function validate_page()
		//validate page (Aggregate Edit)
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['department_shortname'] ) )
		{
			$return = true;
		}


/core/controllers/post.php 
	in function validate_page()
		//validate page (Aggregate Edit)
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['department_shortname'] ) )
		{
			$return = true;
		}

	under add_action('admin_head', 
		add_action('custom-fields', array($this,'admin_head'));


	in meta_box_input
	$post_ID=$post->ID; //get post ID

		replace js
		//Modified to make IDs unique
		<?php if(isset( $_GET['page'] ) && $_GET['page'] == "dp_page" && isset( $_GET['department_shortname'] ) ):?>
			document.getElementById('<?php echo $id; ?>').id = '<?php echo $id.'_'.$post_ID; ?>';
			
			$('#<?php echo $id.'_'.$post_ID ?>').addClass('<?php echo $class; ?>').removeClass('hide-if-js');
			$('#adv-settings label[for="<?php echo $id.'_'.$post_ID; ?>-hide"]').addClass('<?php echo $toggle_class; ?>');
		<?php else : ?>
			$('#<?php echo $id; ?>').addClass('<?php echo $class; ?>').removeClass('hide-if-js');
			$('#adv-settings label[for="<?php echo $id; ?>-hide"]').addClass('<?php echo $toggle_class; ?>');
		<?php endif; ?>

/js/input.js
	$(document).trigger('acf/setup_fields', [ $('.poststuff') ]);
 	$(document).trigger('acf/setup_fields', [ $('#poststuff') ]);