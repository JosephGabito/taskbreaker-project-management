<?php

add_action( 'init', 'thrive_projects_register_post_type' );

/**
 * Register 'Projects' component post type
 *
 * @return void
 */
function thrive_projects_register_post_type() {

	$labels = array(
		'name'               => __( 'Projects', 'post type general name', 'thrive' ),
		'singular_name'      => __( 'Project', 'post type singular name', 'thrive' ),
		'menu_name'          => __( 'Projects', 'admin menu', 'thrive' ),
		'name_admin_bar'     => __( 'Project', 'add new on admin bar', 'thrive' ),
		'add_new'            => __( 'Add New', 'project', 'thrive' ),
		'add_new_item'       => __( 'Add New Project', 'thrive' ),
		'new_item'           => __( 'New Project', 'thrive' ),
		'edit_item'          => __( 'Edit Project', 'thrive' ),
		'view_item'          => __( 'View Project', 'thrive' ),
		'all_items'          => __( 'All Projects', 'thrive' ),
		'search_items'       => __( 'Search Projects', 'thrive' ),
		'parent_item_colon'  => __( 'Parent Projects:', 'thrive' ),
		'not_found'          => __( 'No projects found.', 'thrive' ),
		'not_found_in_trash' => __( 'No projects found in Trash.', 'thrive' ),
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
		'register_meta_box_cb'      => 'thrive_project_meta_box',
		'supports'           => array( 'title', 'editor', 'custom-fields' ),
	);

	register_post_type( 'project', $args );

	return;
}


add_action( 'add_meta_boxes_post' ,'thrive_project_meta_box' );

function thrive_project_meta_box() {
	
	wp_enqueue_script( 'jquery-ui-datepicker' );

	add_meta_box(
		'thrive_tasks_metabox',
		__( 'Tasks', 'thrive' ),
		'thrive_tasks_metabox_content',
		'project',
		'advanced',
		'high'
	);

}

function thrive_tasks_metabox_content() {
	?>
	<div id="thrive-tasks" class="thrive-tabs">
		<div id="thrive-action-preloader" class="active">
			<span><?php _e( 'Loading', 'thrive' ); ?> &hellip;</span>
		</div> 
		<div class="thrive-tabs-tabs">
			<ul>
			    <li id="thrive-task-list-tab" class="thrive-task-tabs ui-state-active"><a href="#tasks"><span class="dashicons dashicons-list-view"></span> Tasks List</a></li>
			    <li id="thrive-task-completed-tab" class="thrive-task-tabs"><a href="#tasks/completed"><span class="dashicons dashicons-yes"></span> Completed</a></li>
			    <li id="thrive-task-add-tab" class="thrive-task-tabs"><a href="#tasks/add"><span class="dashicons dashicons-plus"></span> New Task</a></li>
			    <li id="thrive-task-edit-tab" class="thrive-task-tabs hidden" id="thrive-edit-task-list"><a href="#thrive-edit-task">Edit Task</a></li>
			</ul>
		</div>
		<div class="thrive-tabs-content">
			<div id="thrive-task-list" class="thrive-tab-item-content active">
				<?php if ( function_exists( 'thrive_render_task' ) ) {?>
					<?php thrive_render_task(); ?>
				<?php } ?>
			</div>

			<div id="thrive-add-task" class="thrive-tab-item-content">
				<?php thrive_add_task_form(); ?>
			</div><!--.#thrive-add-task-->

			<div id="thrive-edit-task" class="thrive-tab-item-content">
				<?php thrive_edit_task_form(); ?>
			</div><!--.#thrive-edit-task-->
			
		</div>
	</div>
	<script>
		<?php global $post; ?>
		var thriveAjaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		var thriveTaskConfig = {
			currentProjectId: '<?php echo $post->ID; ?>',
			currentUserId: '<?php echo get_current_user_id(); ?>',
		}
	</script>
	<?php
}


add_action( 'wp', 'thrive_filter_single_project' );

function thrive_filter_single_project() {

	global $post;

	if ( is_singular( 'project' ) ) {

		add_filter( 'the_content', 'thrive_project_content_filter' );

	}

	return;
	
}

function thrive_project_content_filter( $content ) {

	global $post;

	require_once( plugin_dir_path( __FILE__ ) . '../core/functions.php' );

	$container = '<div id="thrive-project">';

	$container_end = '</div><!--#thrive-project-->';

		ob_start();
		
		include thrive_template_dir(). '/project-heading.php';
		
		$heading = ob_get_clean();

	    $project_tabs = '<div class="thrive-project-tabs">';
	    	$project_tabs .= '<ul id="thrive-project-tab-li">';
	    		$project_tabs .= '<li class="thrive-project-tab-li-item active"><a data-content="thrive-project-dashboard" class="thrive-project-tab-li-item-a" href="#tasks/dashboard">Dashboard</a></li>';
	    		$project_tabs .= '<li class="thrive-project-tab-li-item"><a data-content="thrive-project-tasks" class="thrive-project-tab-li-item-a" href="#tasks">Tasks</a></li>';
	    		$project_tabs .= '<li class="thrive-project-tab-li-item"><a data-content="thrive-project-add-new" id="thrive-project-add-new" class="thrive-project-tab-li-item-a" href="#tasks/add">Add New</a></li>';
	    		$project_tabs .= '<li class="thrive-project-tab-li-item"><a data-content="thrive-project-edit" id="thrive-project-edit-tab" class="thrive-project-tab-li-item-a" href="#">Edit</a></li>';
	    		$project_tabs .= '<li class="thrive-project-tab-li-item"><a data-content="thrive-project-settings" class="thrive-project-tab-li-item-a" href="#tasks/settings">Settings</a></li>';
	    	$project_tabs .= '</ul>';
	    $project_tabs .= '</div>';

	    $tab_content  = '<div id="thrive-project-tab-content">';

		ob_start();

		if ( $post->post_type == 'project' ) {
			include thrive_template_dir(). '/project.php';
		} 

    	$project_contents = ob_get_clean();

		$tab_content .= $project_contents;

	    $tab_content .= '</div><!--#thrive-project-tab-content-->';

	return  $container . $heading . $project_tabs . $tab_content .  $container_end;
}
?>
