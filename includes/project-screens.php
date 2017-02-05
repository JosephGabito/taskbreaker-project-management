<?php
/**
 * [task_breaker_bp_projects_load_template_filter description]
 * @param  [type] $found_template [description]
 * @param  [type] $templates      [description]
 * @return [type]                 [description]
 */

if ( ! defined( 'ABSPATH' ) ) { 
	return;
}

function task_breaker_bp_projects_load_template_filter( $found_template, $templates ) {

	$core = new TaskBreakerCore();

	// Only filter the template location when we're on the bp-plugin component pages.
	if ( ! bp_is_current_component( 'projects' ) ) {

		return $found_template;

	}

	if ( ! task_breaker_bp_projects_is_bp_default() ) {

		return $found_template;

	}

	foreach ( ( array ) $templates as $template ) {

		if ( file_exists( STYLESHEETPATH . '/' . $template ) ) {

			$filtered_templates[] = STYLESHEETPATH . '/' . $template;

		} elseif ( file_exists( TEMPLATEPATH . '/' . $template ) ) {

			$filtered_templates[] = TEMPLATEPATH . '/' . $template;

		} else {

			$filtered_templates[] = $core->get_template_directory() . '/' . $template;

		}
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'task_breaker_bp_projects_load_template_filter', $found_template );

}

add_filter( 'bp_located_template', 'task_breaker_bp_projects_load_template_filter', 10, 2 );

/**
 * [task_breaker_bp_projects_is_bp_default description]
 *
 * @return [type] [description]
 */
function task_breaker_bp_projects_is_bp_default() {

	// if active theme is BP Default or a child theme, then we return true
	// If the Buddypress version  is < 1.7, then return true too
	if ( current_theme_supports( 'buddypress' ) || in_array( 'bp-default', array( get_stylesheet(), get_template() ) )  || ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7', '<' ) ) ) {

		return true;

	} else {

		return false;

	}

	return false;
}

/**
 * [task_breaker_bp_projects_screen_index description]
 *
 * @return void
 */
function task_breaker_bp_projects_screen_index() {

	// Check if on current project directory page.
	if ( ! bp_displayed_user_id() && bp_is_current_component( 'projects' ) && ! bp_current_action() ) {

		bp_update_is_directory( true, 'projects' );

		// ... before using bp_core_load_template to ask BuddyPress
		// to load the template bp-plugin (which is located in
		// BP_PLUGIN_DIR . '/templates/bp-plugin.php)
		bp_core_load_template( apply_filters( 'task_breaker_bp_projects_screen_index', 'project-loop' ) );

	}
}

add_action( 'bp_screens', 'task_breaker_bp_projects_screen_index' );


/**
 * [bp_projects_add_template_stack description]
 *
 * @param  [type] $templates [description]
 * @return [type]            [description]
 */
function bp_projects_add_template_stack( $templates ) {
	$core = new TaskBreakerCore();
	// if we're on a page of our plugin and the theme is not BP Default, then we
	// add our path to the template path array
	if ( bp_is_current_component( 'projects' ) && ! task_breaker_bp_projects_is_bp_default() ) {

		$templates[] = $core->get_template_directory();
	}
	return $templates;
}

add_filter( 'bp_get_template_stack', 'bp_projects_add_template_stack', 10, 1 );

/**
 * [task_breaker_bp_projects_locate_template description]
 *
 * @param  boolean $template [description]
 * @return [type]            [description]
 */
function task_breaker_bp_projects_locate_template( $template = false ) {

	if ( empty( $template ) ) {
		return false;
	}

	if ( task_breaker_bp_projects_is_bp_default() ) {

		locate_template( array( $template . '.php' ), true );

	} else {

		bp_get_template_part( $template );

	}
}


function task_breaker_bp_projects_main_screen_function() {

	add_action( 'bp_template_title', 'task_breaker_bp_projects_title' );

	add_action( 'bp_template_content', 'task_breaker_bp_projects_content' );

	bp_core_load_template( apply_filters( 'task_breaker_bp_projects_main_screen_function', 'project-dashboard' ) );

	return;
	
}

function task_breaker_bp_projects_main_screen_function_new_project() {

	add_action( 'bp_template_title', 'task_breaker_bp_projects_add_new_title' );

	add_action( 'bp_template_content', 'task_breaker_bp_projects_add_new_content' );

	bp_core_load_template( apply_filters( 'task_breaker_bp_projects_main_screen_function_new_project', 'project-dashboard-new-project' ) );

	return;

}

function task_breaker_bp_projects_user_template_part( $templates, $slug, $name ) {

	if ( $slug != 'members/single/plugins' ) {

		return $templates;

	}

	return array( 'project-dashboard.php' );

}

function task_breaker_bp_projects_menu_header() {
	_e( 'Menu Header', 'task_breaker' );
}

function task_breaker_bp_projects_title() {
	_e( 'Projects', 'task_breaker' );
}


function task_breaker_bp_projects_content() {

	echo '<div id="task_breaker-intranet-projects">';

	$user_groups = task_breaker_get_displayed_user_groups();

	$current_user_groups = task_breaker_get_current_user_owned_groups();

	$groups_collection = array();

	if ( ! empty( $user_groups ) ) {

		foreach ( $user_groups as $key => $group ) {

			$groups_collection[] = $group['group_id'];

		}
	}

	// If there are no groups found assign negative value
	// so that WP_Query will return empty result
	if ( empty( $groups_collection ) ) {

		$groups_collection = array( -1 );

	}

	$args = array(
		'meta_query' => array(
			array(
				'key'     => 'task_breaker_project_group_id',
				'value'   => $groups_collection,
				'compare' => 'IN',
			),
		),
	);
	
	task_breaker_project_loop( $args );

	echo '</div>';

	return;
}

function task_breaker_bp_projects_add_new_title() {
	_e( 'New Project', 'task_breaker' );
}

function task_breaker_bp_projects_add_new_content() {
	task_breaker_new_project_form();
}

/**
 * BP Projects Theme Compatability
 */
class Task_Breaker_Projects_Theme_Compat {

	/**
	 * Setup the bp plugin component theme compatibility
	 */
	public function __construct() {
		/* this is where we hook bp_setup_theme_compat !! */
		add_action( 'bp_setup_theme_compat', array( $this, 'is_bp_projects' ) );
	}

	/**
	 * Are we looking at something that needs theme compatability?
	 */
	public function is_bp_projects() {

		if ( ! bp_current_action() && ! bp_displayed_user_id() && bp_is_current_component( 'projects' ) ) {
			// first we reset the post
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			// then we filter 'the_content' thanks to bp_replace_the_content
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content' ) );
		}
	}

	/**
	 * Update the global $post with directory data
	 */
	public function directory_dummy_post() {
		bp_theme_compat_reset_post(
			array(
			'ID'             => 0,
			'post_title'     => apply_filters( 'task_breaker_projects_dir_title', __( 'Projects Directory', 'task_breaker' ) ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_projects',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed',
			)
		);
	}

	/**
	 * Filter the_content with bp-plugin index template part
	 */
	public function directory_content() {

		bp_buffer_template_part( 'project-loop' );

	}
}

new Task_Breaker_Projects_Theme_Compat();

