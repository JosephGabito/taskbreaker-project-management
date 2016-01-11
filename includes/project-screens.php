<?php
/**
 *
 * @TODO TODO TODO
 * [bp_projects_load_template_filter description]
 * @param  [type] $found_template [description]
 * @param  [type] $templates      [description]
 * @return [type]                 [description]
 */

if ( ! defined( 'ABSPATH' ) ) { die(); }

function bp_projects_load_template_filter( $found_template, $templates ) {

	// Only filter the template location when we're on the bp-plugin component pages.
	if ( ! bp_is_current_component( 'projects' ) ) {

		return $found_template;

	}

	if ( ! bp_projects_is_bp_default() ) {

		return $found_template;

	}

	foreach ( ( array ) $templates as $template ) {

		if ( file_exists( STYLESHEETPATH . '/' . $template ) ) {

			$filtered_templates[] = STYLESHEETPATH . '/' . $template;

		} else if ( file_exists( TEMPLATEPATH . '/' . $template ) ) {

			$filtered_templates[] = TEMPLATEPATH . '/' . $template;

		} else {

			$filtered_templates[] = thrive_template_dir() . '/' . $template;

		}
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'bp_projects_load_template_filter', $found_template );

}

add_filter( 'bp_located_template', 'bp_projects_load_template_filter', 10, 2 );

/**
 * [bp_projects_is_bp_default description]
 * @return [type] [description]
 */
function bp_projects_is_bp_default() {

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
 * [bp_projects_screen_index description]
 * @return void
 */
function bp_projects_screen_index() {

	// Check if on current project directory page.
	if ( ! bp_displayed_user_id() && bp_is_current_component( 'projects' ) && ! bp_current_action() ) {

		bp_update_is_directory( true, 'projects' );

		// ... before using bp_core_load_template to ask BuddyPress
		// to load the template bp-plugin (which is located in
		// BP_PLUGIN_DIR . '/templates/bp-plugin.php)
		bp_core_load_template( apply_filters( 'bp_projects_screen_index', 'project-loop' ) );

	}
}

add_action( 'bp_screens', 'bp_projects_screen_index' );


/**
 * [bp_projects_add_template_stack description]
 * @param  [type] $templates [description]
 * @return [type]            [description]
 */
function bp_projects_add_template_stack( $templates ) {

	// if we're on a page of our plugin and the theme is not BP Default, then we
	// add our path to the template path array
	if ( bp_is_current_component( 'projects' ) && ! bp_projects_is_bp_default() ) {

		$templates[] = thrive_template_dir();
	}
	return $templates;
}

add_filter( 'bp_get_template_stack', 'bp_projects_add_template_stack', 10, 1 );

/**
 * [bp_projects_locate_template description]
 * @param  boolean $template [description]
 * @return [type]            [description]
 */
function bp_projects_locate_template( $template = false ) {

	if ( empty( $template ) ) {
		return false;
	}

	if ( bp_projects_is_bp_default() ) {

		locate_template( array( $template . '.php' ), true );

	} else {

		bp_get_template_part( $template );

	}
}


function bp_projects_main_screen_function() {

	add_action( 'bp_template_title', 'bp_projects_title' );
	
	add_action( 'bp_template_content', 'bp_projects_content' );

	bp_core_load_template( apply_filters( 'bp_projects_main_screen_function', 'project-dashboard' ) );

	/*
	// if BP Default is not used, we filter bp_get_template_part
	if ( ! bp_projects_is_bp_default() ) {

		add_filter( 'bp_get_template_part', 'bp_projects_user_template_part', 10, 3 );

	}*/
}

function bp_projects_main_screen_function_new_project() {

	add_action( 'bp_template_title', 'bp_projects_add_new_title' );
	add_action( 'bp_template_content', 'bp_projects_add_new_content' );

	bp_core_load_template( apply_filters( 'bp_projects_main_screen_function_new_project', 'project-dashboard-new-project' ) );
	/*
	// if BP Default is not used, we filter bp_get_template_part
	if ( ! bp_projects_is_bp_default() ) {

		add_filter( 'bp_get_template_part', 'bp_projects_user_template_part', 10, 3 );

	}*/

}

function bp_projects_user_template_part( $templates, $slug, $name ) {

	if ( $slug != 'members/single/plugins' ) {

		return $templates;

	}

	return array( 'project-dashboard.php' );

}

function bp_projects_menu_header() {
	_e( 'Menu Header', 'thrive' );
}

function bp_projects_title() {
	_e( 'Projects', 'thrive' );
}


function bp_projects_content() {

	echo '<div id="thrive-intranet-projects">';

			$user_groups = thrive_get_displayed_user_groups();

			$current_user_groups = thrive_get_current_user_groups();

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
							'key'     => 'thrive_project_group_id',
							'value'   => $groups_collection,
							'compare' => 'IN',
						),
					),
				);

			thrive_project_loop( $args );

	echo '</div>';

	return;
}

function bp_projects_add_new_title() {
	_e( 'New Project', 'thrive' );
}

function bp_projects_add_new_content() {
	thrive_new_project_form();
}

/**
 * BP Projects Theme Compatability
 */
class BP_Projects_Theme_Compat {
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
		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => apply_filters( 'thrive_projects_dir_title', __( 'Projects Directory', 'thrive' ) ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'bp_projects',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed',
		) );
	}

	/**
	 * Filter the_content with bp-plugin index template part
	 */
	public function directory_content() {

		bp_buffer_template_part( 'project-loop' );

	}
}

new BP_Projects_Theme_Compat();
?>
