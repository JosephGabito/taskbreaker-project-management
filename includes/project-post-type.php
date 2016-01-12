<?php

add_action( 'init', 'task_breaker_projects_register_post_type' );

/**
 * Register 'Projects' component post type
 *
 * @return void
 */
function task_breaker_projects_register_post_type() {

	$labels = array(
		'name'               => __( 'Projects', 'post type general name', 'task_breaker' ),
		'singular_name'      => __( 'Project', 'post type singular name', 'task_breaker' ),
		'menu_name'          => __( 'Projects', 'admin menu', 'task_breaker' ),
		'name_admin_bar'     => __( 'Project', 'add new on admin bar', 'task_breaker' ),
		'add_new'            => __( 'Add New', 'project', 'task_breaker' ),
		'add_new_item'       => __( 'Add New Project', 'task_breaker' ),
		'new_item'           => __( 'New Project', 'task_breaker' ),
		'edit_item'          => __( 'Edit Project', 'task_breaker' ),
		'view_item'          => __( 'View Project', 'task_breaker' ),
		'all_items'          => __( 'All Projects', 'task_breaker' ),
		'search_items'       => __( 'Search Projects', 'task_breaker' ),
		'parent_item_colon'  => __( 'Parent Projects:', 'task_breaker' ),
		'not_found'          => __( 'No projects found.', 'task_breaker' ),
		'not_found_in_trash' => __( 'No projects found in Trash.', 'task_breaker' ),
	);

	$args = array(
		'menu_icon'			 => 'dashicons-analytics',
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'project' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'custom-fields' ),
	);

	register_post_type( 'project', $args );

	return;
}

add_action( 'wp', 'task_breaker_filter_single_project' );

function task_breaker_filter_single_project() {

	global $post;

	if ( is_singular( 'project' ) ) {

		add_filter( 'the_content', 'task_breaker_project_content_filter' );

	}

	return;
	
}

function task_breaker_project_content_filter( $content ) {

	global $post;

	require_once( plugin_dir_path( __FILE__ ) . '../core/functions.php' );

	$container = '<div id="task_breaker-project">';

	$container_end = '</div><!--#task_breaker-project-->';

		ob_start();
		
		include task_breaker_template_dir(). '/project-heading.php';
		
		$heading = ob_get_clean();

	    $project_tabs = '<div class="task_breaker-project-tabs">';
	    	$project_tabs .= '<ul id="task_breaker-project-tab-li">';
	    		$project_tabs .= '<li class="task_breaker-project-tab-li-item active"><a data-content="task_breaker-project-dashboard" class="task_breaker-project-tab-li-item-a" href="#tasks/dashboard">Dashboard</a></li>';
	    		$project_tabs .= '<li class="task_breaker-project-tab-li-item"><a data-content="task_breaker-project-tasks" class="task_breaker-project-tab-li-item-a" href="#tasks">Tasks</a></li>';
	    		$project_tabs .= '<li class="task_breaker-project-tab-li-item"><a data-content="task_breaker-project-add-new" id="task_breaker-project-add-new" class="task_breaker-project-tab-li-item-a" href="#tasks/add">Add New</a></li>';
	    		$project_tabs .= '<li class="task_breaker-project-tab-li-item"><a data-content="task_breaker-project-edit" id="task_breaker-project-edit-tab" class="task_breaker-project-tab-li-item-a" href="#">Edit</a></li>';
	    		$project_tabs .= '<li class="task_breaker-project-tab-li-item"><a data-content="task_breaker-project-settings" class="task_breaker-project-tab-li-item-a" href="#tasks/settings">Settings</a></li>';
	    	$project_tabs .= '</ul>';
	    $project_tabs .= '</div>';

	    $tab_content  = '<div id="task_breaker-project-tab-content">';

		ob_start();

		if ( $post->post_type == 'project' ) {
			include task_breaker_template_dir(). '/project.php';
		} 

    	$project_contents = ob_get_clean();

		$tab_content .= $project_contents;

	    $tab_content .= '</div><!--#task_breaker-project-tab-content-->';

	return  $container . $heading . $project_tabs . $tab_content .  $container_end;
}
?>
