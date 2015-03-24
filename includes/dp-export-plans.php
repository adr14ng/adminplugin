<?php

//includes->dp_admin->plugs->wp-content->base
$base_url = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require_once($base_url.'/wp-admin/admin.php');

if(!isset($_POST['action']))
{
	wp_die("No action set");
}

if($_POST['action'] === 'export')
{
	export_plans();
}
elseif($_POST['action'] === 'import')
{
	import_plans();
}
else
{
	wp_die("Invalid Action.");
}

function export_plans()
{
	check_admin_referer('export_plans');

	global $wpdb;

	if(isset($_POST['type']))
		$args['type'] = $_POST['type'];
	if(isset($_POST['aca_year']))
		$args['aca_year'] = $_POST['aca_year'];
	if(isset($_POST['field']))
		$args['field'] = $_POST['field'];

	$defaults = array( 
		'type' => array('plans', 'staract'), 
		'aca_year' => false, 
		'field' => array('title', 'content')
	);

	$args = wp_parse_args( $args, $defaults );

	$sitename = sanitize_key( get_bloginfo( 'name' ) );
	if ( ! empty($sitename) ) $sitename .= '.';
	$filename = $sitename . 'plan-management.' . date( 'Y-m-d' ) . '.xml';

	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

	$esses = array_fill( 0, count($args['type']), '%s' );
	$where = $wpdb->prepare( "{$wpdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $args['type'] );

	$where .= " AND {$wpdb->posts}.post_status NOT IN ('auto-draft', 'trash')";

	if($args['aca_year'])
	{
		$join = "INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
		$dees = array_fill( 0, count($args['aca_year']), '%d' );
		$where .= $wpdb->prepare( " AND {$wpdb->term_relationships}.term_taxonomy_id IN (" . implode( ',', $dees ) . ')', $args['aca_year'] );
	}

	$fields = "ID";

	if(in_array('title', $args['field']))
	{
		$title = true;
		$fields .= ", post_title";
	}

	if(in_array('content', $args['field']))
	{
		$content = true;
		$fields .= ", post_content";
	}

	// Grab a snapshot of post IDs, just in case it changes during the export.
	$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} $join WHERE $where" );

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 * @return string
	 */
	function wxr_cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );

		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

	echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";

	/*
	echo "<!--";
	print_r($args);
	echo "<br />\nSELECT ID FROM  $join WHERE $where<br />\n";
	echo "<br />\n$fields<br />\n";
	print_r($post_ids);
	echo "-->";
	*/

	?>
	<!-- This file contains information about the Degree Planning Guides and STAR Act Guides -->
	<!-- You may use this file to update that content from one site to another. -->

	<posts>
	<?php if ( $post_ids ) :
		global $wp_query;

		// Fake being in the loop.
		$wp_query->in_the_loop = true;

		// Fetch 20 posts at a time rather than loading the entire table into memory.
		while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) :
		$where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
		$posts = $wpdb->get_results( "SELECT $fields FROM {$wpdb->posts} $where", "ARRAY_A" );

		// Begin Loop.
		foreach ( $posts as $post ) :
			//setup_postdata( $post );
	?>
		<item>
	<?php if($title) : ?>
			<title><?php echo $post['post_title']; ?></title>
	<?php endif; if($content) : ?>
			<content:encoded><?php echo wxr_cdata( $post['post_content']  ); ?></content:encoded>
	<?php endif; ?>
			<wp:post_id><?php echo $post['ID']; ?></wp:post_id>
		</item>
	<?php
		endforeach;
		endwhile;
	endif; ?>
	</posts>
	<?php

	exit();
}

function import_plans()
{
	/** Display verbose errors */
	define( 'IMPORT_DEBUG', false );

	// Load Importer API
	require_once ABSPATH . 'wp-admin/includes/import.php';

	if ( ! class_exists( 'WP_Importer' ) ) {
		$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		if ( file_exists( $class_wp_importer ) )
			require $class_wp_importer;
	}
	
	check_admin_referer( 'import-plans' );
	
	$importer = new Plan_Import();
	$importer->import();
	
	if(isset($_POST['return']))
		wp_safe_redirect( $_POST['return'] );
	else
		wp_redirect( admin_url() );
}

class Plan_Import {
	function WP_Import() { /* nothing */ }

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing
	 */
	function import() {
		set_time_limit(0);
		add_filter( 'http_request_timeout', array( &$this, 'bump_request_timeout' ) );

		$this->handle_upload();
		$this->process_posts();
		wp_import_cleanup( $this->id );
		wp_cache_flush();
	}

	/**
	 * Handles the WXR upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>Sorry, there has been an error.</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>Sorry, there has been an error.</strong><br />';
			printf( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$this->id = (int) $file['id'];
		$parser = new Plan_Parser_XML();
		$import_data $parser->parse( $file );
		
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>Sorry, there has been an error.</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			return false;
		}

		$this->posts = $import_data;
		return true;
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_posts() {
		print_r($this->posts);
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 60
	 */
	function bump_request_timeout() {
		return 60;
	}
}

class Plan_Parser_XML {
	function parse( $file ) {
		$this->posts = array();
		$this->in_post = $this->in_tag = false;

		$xml = xml_parser_create( 'UTF-8' );
		xml_parser_set_option( $xml, XML_OPTION_SKIP_WHITE, 1 );
		xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_object( $xml, $this );
		xml_set_character_data_handler( $xml, 'cdata' );
		xml_set_element_handler( $xml, 'tag_open', 'tag_close' );

		if ( ! xml_parse( $xml, file_get_contents( $file ), true ) ) {
			$current_line = xml_get_current_line_number( $xml );
			$current_column = xml_get_current_column_number( $xml );
			$error_code = xml_get_error_code( $xml );
			$error_string = xml_error_string( $error_code );
			return new WP_Error( 'XML_parse_error', 'There was an error when reading this WXR file', array( $current_line, $current_column, $error_string ) );
		}
		xml_parser_free( $xml );
		
		return $this->posts
	}

	function tag_open( $parse, $tag, $attr ) {
		switch ( $tag ) {
			case 'item': $this->in_post = true;
			case 'title': $this->in_tag = 'post_title'; break;
			case 'content:encoded': $this->in_tag = 'post_content'; break;
			case 'wp:post_id': $this->in_tag = 'post_id'; break;
		}
	}

	function cdata( $parser, $cdata ) {
		if ( ! trim( $cdata ) )
			return;

		$this->cdata .= trim( $cdata );
	}

	function tag_close( $parser, $tag ) {
		switch ( $tag ) {
			case 'item':
				$this->posts[] = $this->data;
				$this->data = false;
				break;
			default:
				$this->data[$this->in_tag] = ! empty( $this->cdata ) ? $this->cdata : '';
				$this->in_tag = false;
		}

		$this->cdata = false;
	}
}
