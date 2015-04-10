<?php
/** * * * * * * * * * * * * * * * * *
 * 
 *
 *
 *
 * * * * * * * * * * * * * * * * * * */
 
class PlansTool
{
	
	/**
	 * Start Up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_tool_page' ) );
	}
	
	/**
	 *
	 */
	public function add_tool_page()
	{
		//This page will be under "Tools"
		add_management_page(
			'Plan Management',
			'Plan Management',
			'manage_options',
			'plan-management',
			array($this, 'planning_page')
		);
	}
	
	/**
	 *
	 */
	public function planning_page()
	{
		if(isset($_POST['action']))
		{
			if($_POST['action'] === 'copy')
			{
				$this->copy_plans();
				$this->copy_plans_page();
			}
			else if($_POST['action'] === 'link')
			{
				$this->link_plans();
			}
		}
		
		if(isset($_GET['subpage']) && $_GET['subpage'] === 'import')
		{
			$this->import_plans_page();
		}
		else if(isset($_GET['subpage']) && $_GET['subpage'] === 'link')
		{
			$this->link_plans_page();
		}
		else
		{
			$this->copy_plans_page();
		}
	}
	
	public function import_plans_page()
	{
		$page = admin_url('tools.php?page=plan-management');
		$form_action = plugins_url().'/department-admin/includes/dp-export-plans.php'
	?>
		<div class="wrap clearfix">
			<h1>Plan Management</h1>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo $page; ?>" class="nav-tab">Copy Plans</a>
				<a href="<?php echo $page.'&subpage=import'; ?>" class="nav-tab nav-tab-active">Import/Export Plans</a>
				<a href="<?php echo $form_action.'&subpage=link'; ?>" class="nav-tab">Link Plan Courses</a>
			</h2>
			<div id="export">
				<h3>Export Plans</h3>
				<p>This will export all Plans and/or STAR Acts of a select year to be imported at another version of this site.</p>
				<form name="export_plans" action="<?php echo $form_action; ?>" method="post" class="plan-management">
					<?php wp_nonce_field('export_plans'); ?>
					<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
					<input type="hidden" name="return" value="<?php echo $page.'&subpage=import'; ?>" />
					<input type="hidden" name="action" value="export" />
					<fieldset>
						<legend> Post Type: </legend>
						<div>
							<label for="plan_type">
								<input id="plan_type" type="checkbox" name="type[]" value="plans" />
								<span>Plans</span>
							</label>
							<label for="star_type">
								<input id="star_type" type="checkbox" name="type[]" value="staract" />
								<span>STAR Act</span>
							</label>
						</div>
					</fieldset>
					<label for="aca_year"> 
						<span>Years: </span>
						<select multiple name="aca_year[]" id="aca_year">
							<?php echo $this->year_drop_down(true); ?>
						</select>
					</label>
					<fieldset>
						<legend> Fields: </legend>
						<div>
							<label for="field_title">
								<input id="field_title" type="checkbox" name="field[]" value="title" />
								<span>Title</span>
							</label>
							<label for="field_content">
								<input id="field_content" type="checkbox" name="field[]" value="content" />
								<span>Content</span>
							</label>
						</div>
					</fieldset>
					<input type="submit" value="Submit" />
				</form>
			</div>
			<div id="import">
				<h3>Import Plans</h3>
				<p>Select the file exported from another site to update existing Plans and STAR Acts.</p>
				<form name="import_plans" action="<?php echo $form_action; ?>" method="post" class="plan-management" enctype="multipart/form-data">
					<?php wp_nonce_field('import_plans'); ?>
					<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
					<input type="hidden" name="return" value="<?php echo $page.'&subpage=import'; ?>" />
					<input type="hidden" name="action" value="import" />
					<label for="field_title">
						<span>File : </span>
						<input id="import-file" type="file" name="import" />
					</label>
					<input type="submit" value="Submit" />
				</form>
			</div>
		</div>	
	<?php
	}
	
	public function link_plans_page()
	{
		$form_action = admin_url('tools.php?page=plan-management');
	?>
		<div class="wrap clearfix">
			<h1>Plan Management</h1>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo $form_action; ?>" class="nav-tab">Copy Plans</a>
				<a href="<?php echo $form_action.'&subpage=import'; ?>" class="nav-tab">Import/Export Plans</a>
				<a href="<?php echo $form_action.'&subpage=link'; ?>" class="nav-tab nav-tab-active">Link Plan Courses</a>
			</h2>
			<h3>Link Plan Courses</h3>
			<p>This will link all the courses in a subset of the plans.</p>
			<form name="copy_plans" action="<?php echo $form_action; ?>&subpage=link" method="post" class="plan-management">
				<?php wp_nonce_field('link_plans'); ?>
				<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
				<input type="hidden" name="return" value="<?php echo $form_action; ?>&subpage=link" />
				<input type="hidden" name="action" value="link" />
				<fieldset>
					<legend> Post Type: </legend>
					<div>
						<label for="plan_type">
							<input id="plan_type" type="checkbox" name="type[]" value="plans" />
							<span>Plans</span>
						</label>
						<label for="star_type">
							<input id="star_type" type="checkbox" name="type[]" value="staract" />
							<span>STAR Act</span>
						</label>
					</div>
				</fieldset>
				<label for="aca_year"> 
					<span>Year: </span>
					<select name="aca_year" id="aca_year">
						<?php echo $this->year_drop_down(); ?>
					</select>
				</label>
				<input type="submit" value="Submit" />
			</form>
		</div>	
	<?php
	}
	
	public function copy_plans_page()
	{
		$form_action = admin_url('tools.php?page=plan-management');
	?>
		<div class="wrap clearfix">
			<h1>Plan Management</h1>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo $form_action; ?>" class="nav-tab nav-tab-active">Copy Plans</a>
				<a href="<?php echo $form_action; ?>&subpage=import" class="nav-tab">Import/Export Plans</a>
				<a href="<?php echo $form_action; ?>&subpage=link" class="nav-tab">Link Plan Courses</a>
			</h2>
			<h3>Copy Plans</h3>
			<p>This will take all the Plans and/or STAR Act Plans from the original year, create new posts which are duplicates of the original but with the New Year and save them as drafts to be editted.</p>
			<form name="copy_plans" action="<?php echo $form_action; ?>" method="post" class="plan-management">
				<?php wp_nonce_field('copy_plans'); ?>
				<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>" />
				<input type="hidden" name="return" value="<?php echo $form_action; ?>" />
				<input type="hidden" name="action" value="copy" />
				<fieldset>
					<legend> Post Type: </legend>
					<div>
						<label for="plan_type">
							<input id="plan_type" type="checkbox" name="type[]" value="plans" />
							<span>Plans</span>
						</label>
						<label for="star_type">
							<input id="star_type" type="checkbox" name="type[]" value="staract" />
							<span>STAR Act</span>
						</label>
					</div>
				</fieldset>
				<label for="aca_year_original"> 
					<span>Original Year: </span>
					<select name="aca_year_original" id="aca_year_original">
						<?php echo $this->year_drop_down(); ?>
					</select>
				</label>
				<label for="aca_year_new"> 
					<span>New Year: </span>
					<select name="aca_year_new" id="aca_year_new">
						<?php echo $this->year_drop_down(); ?>
					</select>
				</label>
				<input type="submit" value="Submit" />
			</form>
		</div>	
	<?php
	}
	
	public function year_drop_down($tax_id = false)
	{
		$drop_down = '';
		$years = get_terms('aca_year', array('orderby' => 'name', 'order' => 'DESC', 'hide_empty' => false));
		foreach($years as $year)
		{
			$drop_down .= '<option value="'.($tax_id ? $year->term_taxonomy_id : $year->term_id).'">'.$year->name.'</option>';
		}
		
		return $drop_down;
	}
	
	public function copy_plans()
	{
		check_admin_referer('copy_plans');
		
		//build query
		$args = array(
			'post_type' => $_POST['type'],
			'tax_query' => array(
				array(
					'taxonomy' => 'aca_year',
					'fields' => 'id',
					'terms' => $_POST['aca_year_original'],
				),
			),
			'posts_per_page' => -1,		//5 for testing, -1 for production
		);
		
		//get plans
		$plans = get_posts($args);
		
		//build insert requests
		$insert = array();
		foreach($plans as $plan)
		{
			$plan_info['post_title'] = $plan->post_title; //for testing
			$plan_info['post_status'] = 'draft';
			$plan_info['post_type'] = $plan->post_type;
			$plan_info['tax_input']['aca_year'] = $_POST['aca_year_new'];
			$plan_info['tax_input']['department_shortname'] = wp_get_post_terms( $plan->ID, 'department_shortname', array('fields' => 'ids') );
			$plan_info['fields']['field_548f81952e786'] = get_field( 'field_548f81952e786', $plan->ID, false );
			$plan_info['post_content'] = "";
			print_r($plan_info);
			
			$plan_info['post_content'] = $plan->post_content;
			
			$insert[] = $plan_info;
		}
		
		//insert plans
		foreach($insert as $new)
		{
			$id = wp_insert_post($new, true);
			if( is_wp_error( $id ) ) {
				echo "<br \>Unsuccessful operation<br />";
				echo $id->get_error_message()."<br \>";
			}
			else
			{
				foreach($new['fields'] as $field=>$value)
				{
					update_field( $field, $value, $id );
				}
			}
		}
	}
	
	public function link_plans()
	{
		check_admin_referer('link_plans');
		
		//build query
		$args = array(
			'post_type' => $_POST['type'],
			'tax_query' => array(
				array(
					'taxonomy' => 'aca_year',
					'fields' => 'id',
					'terms' => $_POST['aca_year'],
				),
			),
			'posts_per_page' => -1,		//5 for testing, -1 for production
		);

		$posts = get_posts($args);
		foreach($posts as $post)
		{
			//$post = get_post(46053);
			//echo '<h2>'.$post->post_title."</h2>\n";
			$content = $this->find_course_links($post->post_content, $post->ID);
			if($content)
			{
				wp_update_post(array('ID' => $post->ID, 'post_content' => $content));
			}
		}
	}
	
	public function find_course_links($content, $id=false)
	{
		//echo '<div id="debug-'.$id.'">';
		$content = htmlentities($content, null, 'utf-8');
		$content = str_replace("&nbsp;", " ", $content);
		$content = html_entity_decode($content, null, 'utf-8');
		//print_r($content); echo "<br />\n";
		$regex = "/([A-Z]{2,4}) ".			//prefix is 2-4 uppercase letters then a space
				 "([0-9]{3})".				//course number is 3 numbers
				 "([A-Z-]*)\/*([A-Z]*)/";			//suffix with optional slash
				 
		$dom = new DOMDocument();
		$dom->loadHtml(mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8"));
		
		$xpath = new DOMXPath($dom);
		$updated = false;
		
		
		foreach($xpath->query('//text()[not(ancestor::a)]') as $node)
		{
			$node_update = false;
			//echo "Orginal Node: "; print_r($node->wholeText); echo "<br />\n";
			$replaced = preg_replace_callback( 
				   $regex,
				   function ($matches) {
						$slug = $matches[1].'-'.$matches[2].$matches[3].$matches[4];
						$slug = strtolower($slug);
						$course = get_posts(array(
						          'name' => $slug,
								  'posts_per_page' => 1,
								  'post_type' => 'courses', 
								  'post_status' => 'publish',
								));
						
						if($course)
						{
							$link = '<a href="'.get_permalink( $course[0]->ID ).'" title="'.$course[0]->post_title.'">'.$matches[0].'</a>';
							$node_update = true;
							return $link;
						}
						else
						{
							return $matches[0];
						}
				   },
				   $node->wholeText
				);
			//echo "Updated Node: "; print_r($replaced); echo "<br />\n";
			if($node_update)
			{
				$updated = true;
				$newNode  = $dom->createDocumentFragment();
				$newNode->appendXML($replaced);
				$node->parentNode->replaceChild($newNode, $node);
			}
		}
		//print_r(mb_substr($dom->saveXML($xpath->query('//body')->item(0)), 6, -7, "UTF-8"));
		//echo '</div>';
		// get only the body tag with its contents, then trim the body tag itself to get only the original content
		if($updated)
		{
			return mb_substr($dom->saveXML($xpath->query('//body')->item(0)), 6, -7, "UTF-8");
		}
		else
		{
			return false;
		}
	}
 
}