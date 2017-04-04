<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerProjectScreens
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * The BuddyPress integration of Projects. This class defines the sceens and template of taskbreaker Project component.
 *
 * @package TaskBreaker\TaskBreakerProjectScreens
 */
final class TaskBreakerProjectScreens {

	/**
	 * CLass constructor.
	 *
	 * @return  void
	 */
	public function __construct() {

		add_filter( 'bp_located_template', array( $this, 'load_template_filter' ), 10, 2 );
		add_filter( 'bp_get_template_stack', array( $this, 'bp_projects_add_template_stack' ), 10, 1 );
		add_action( 'bp_screens', array( $this, 'bp_projects_screen_index' ) );

	}
	/**
	 * Overwrites the template for projects component
	 *
	 * @param  string $found_template The found template, if there is one.
	 * @param  string $templates      The templates.
	 * @return string                    The path of the template (filtered)
	 */
	public function load_template_filter( $found_template, $templates  ) {

		$core = new TaskBreakerCore();

		// Only filter the template location when we're on the bp-plugin component pages.
		if ( ! bp_is_current_component( 'projects' ) ) {
			return $found_template;
		}

		if ( ! $this->projects_is_bp_default() ) {
			return $found_template;
		}

		foreach ( (array) $templates as $template ) {

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

	/**
	 * Check if current theme supports buddypress.
	 *
	 * @return boolean True on success. Otherwise, false.
	 */
	public function projects_is_bp_default() {

		// If active theme is BP Default or a child theme, then we return true.
		// If the Buddypress version  is < 1.7, then return true too.
		if ( current_theme_supports( 'buddypress' ) ||
			in_array( 'bp-default', array( get_stylesheet(), get_template() ), true )  ||
			( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7', '<' ) ) ) {

			return true;

		}

		return false;
	}


	/**
	 * The projects template stacks.
	 *
	 * @param  mixed $templates The template stacks collection.
	 * @return mixed The template.
	 */
	public function bp_projects_add_template_stack( $templates ) {

		$core = new TaskBreakerCore();

		// if we're on a page of our plugin and the theme is not BP Default, then we add our path to the template path array.
		if ( bp_is_current_component( 'projects' ) && ! $this->projects_is_bp_default() ) {
			$templates[] = $core->get_template_directory();
		}

		return $templates;

	}

	/**
	 * Gets the template for our project loop container
	 *
	 * @param  boolean $template The template file.
	 * @return mixed   false if template is null, otherwise void.
	 */
	public function bp_projects_locate_template( $template = false ) {

		if ( empty( $template ) ) {
			return false;
		}

		if ( $this->projects_is_bp_default() ) {

			locate_template( array( $template . '.php' ), true );

		} else {

			bp_get_template_part( $template );

		}

		return;

	}

	/**
	 * The callback function for displaying the project loop inside screen index.
	 *
	 * @return void.
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

		return;

	}

	/**
	 * The main screen function for our projects component
	 *
	 * @return void
	 */
	public static function bp_projects_main_screen_function() {

		add_action( 'bp_template_title', array( 'TaskBreakerProjectScreens', 'bp_projects_title' ) );

		add_action( 'bp_template_content', array( 'TaskBreakerProjectScreens', 'bp_projects_content' ) );

		bp_core_load_template( apply_filters( 'task_breaker_bp_projects_main_screen_function', 'project-dashboard' ) );

		return;

	}

	/**
	 * The handler for user profile new project.
	 *
	 * @return void
	 */
	public static function bp_projects_main_screen_function_new_project() {

		add_action( 'bp_template_title', array( 'TaskBreakerProjectScreens', 'bp_projects_add_new_title' ) );

		add_action( 'bp_template_content', array( 'TaskBreakerProjectScreens', 'bp_projects_add_new_content' ) );

		bp_core_load_template( apply_filters( 'task_breaker_bp_projects_main_screen_function_new_project', 'project-dashboard-new-project' ) );

		return;

	}

	/**
	 * Stray function task_breaker_bp_projects_user_template_part
	 *
	 * @param  string $templates The template name.
	 * @param  string $slug      The template slug name.
	 * @return array             The templates.
	 */
	public function task_breaker_bp_projects_user_template_part( $templates, $slug ) {

		if ( 'members/single/plugins' !== $slug ) {

			return $templates;

		}

		return array( 'project-dashboard.php' );

	}

	/**
	 * The projects mene header.
	 *
	 * @return void
	 */
	public function bp_projects_menu_header() {

		esc_html_e( 'Menu Header', 'task_breaker' );

		return;
	}

	/**
	 * The projects title.
	 *
	 * @return void
	 */
	public static function bp_projects_title() {

		esc_html_e( 'Public Group Projects', 'task_breaker' );

		return;
	}

	/**
	 * The projects body.
	 *
	 * @return void
	 */
	public static function bp_projects_content() {

		$core = new TaskBreakerCore();

		$template = new TaskBreakerTemplate();

		echo '<div id="task_breaker-intranet-projects">';

		$user_groups = $core->get_displayed_user_groups();

		$groups_collection = array();

		if ( ! empty( $user_groups ) ) {

			foreach ( $user_groups as $key => $group ) {

				$groups_collection[] = $group['group_id'];

			}
		}

		// If there are no groups found assign negative value so that WP_Query will return empty result.
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

		$template->display_project_loop( $args );

		echo '</div>';

		return;

	}

	/**
	 * Projects 'Add New' title.
	 *
	 * @return void
	 */
	public static function bp_projects_add_new_title() {

		esc_html_e( 'New Project', 'task_breaker' );

		return;
	}

	/**
	 * Projects 'Add New' Content
	 *
	 * @return void
	 */
	public static function bp_projects_add_new_content() {

		$template = new TaskBreakerTemplate();

		$template->display_new_project_form();

		return;

	}
}

$taskbreaker_project_screens = new TaskBreakerProjectScreens();

// Include and start the theme compatibility.
require_once TASKBREAKER_DIRECTORY_PATH . 'includes/project-theme-compat.php';

$taskbreaker_theme_compat = new TaskBreakerThemeCompatibility();
