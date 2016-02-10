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

	task_breaker_locate_template('project-single', $post);

	return;
}
?>
