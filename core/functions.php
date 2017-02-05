<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
/**
 * TaskBreakerCore is a collection of methods that are useful
 * across the whole application
 *
 * @package TaskBreaker\TaskBreakerCore
 */
class TaskBreakerCore {

	/**
	 * Returns the task_breaker component id or slug
	 *
	 * @return string the task_breaker component id or slug
	 */
	function get_component_id() {
		return apply_filters( 'task_breaker_component_id', 'projects' );
	}

	/**
	 * Returns the Project component name.
	 *
	 * @return string The Project Name.
	 */
	function get_component_name() {
		return apply_filters( 'task_breaker_component_name', __( 'Projects', 'task_breaker' ) );
	}

	/**
	 * Get the template directory absolute path.
	 *
	 * @return stirng The absolute path of the template directory.
	 */
	function get_template_directory() {
		return plugin_dir_path( __FILE__ ) . '../templates';
	}

	/**
	 * Get the includes directory absolute path.
	 *
	 * @return string the directory absolute path.
	 */
	function get_include_directory() {
		return plugin_dir_path( __FILE__ ) . '../includes';
	}

	/**
	 * Display a select field with list of available priorities.
	 *
	 * @param  integer $default     the default priority.
	 * @param  string  $select_name the name of the select field.
	 * @param  string  $select_id   the id of the select field.
	 * @return void
	 */
	function task_priority_select( $default = 1, $select_name = 'task_breaker_task_priority', $select_id = 'task_breaker-task-priority-select' ) {

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$task_breaker_tasks = TaskBreakerTasksController::get_instance();

		$priorities = $task_breaker_tasks->getPriorityCollection();

		echo '<select name="' . esc_attr( $select_name ) . '" id="' . esc_attr( $select_id ) . '" class="task_breaker-task-select">';

		foreach ( $priorities as $priority_id => $priority_label ) {

			$selected = (intval( $priority_id ) === $default) ? 'selected': '';

			echo '<option ' . esc_html( $selected ) . ' value="' . esc_attr( $priority_id ) . '">' . esc_html( $priority_label ) . '</option>';
		}

		echo '</select>';

		return;
	}

	/**
	 * Counts the number of task for the given project. Can be filter by 'all', 'completed'
	 *
	 * @param  integer $project_id The project ID.
	 * @param  string  $type       The type of the task tasks that you want to fetch.
	 * @return integer             The number of task under the specified project.
	 */
	function count_tasks( $project_id = 0, $type = 'all' ) {

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$task_breaker_tasks = new TaskBreakerTask();

		return $task_breaker_tasks->getCount( $project_id, $type );

	}

	/**
	 * Gets a single task.
	 *
	 * @param  integer $task_id The task ID.
	 * @return object  The collection of task.
	 */
	function get_task( $task_id = 0 ) {

		$taskbreaker = new TaskBreaker();

		$dbase = $taskbreaker->wpdb();

		$stmt = $dbase->prepare( "SELECT * FROM {$dbase->prefix}task_breaker_tasks WHERE id = %d", $task_id );

		$result = $dbase->get_row( $stmt, OBJECT );

		return $result;

	}

	/**
	 * Gets the comments for specific tasks.
	 *
	 * @param  integer $ticket_id The ticket ID of the comment.
	 * @return array              The assosiative array containing the comments.
	 */
	function get_tasks_comments( $ticket_id = 0 ) {

		$taskbreaker = new TaskBreaker();

		$dbase = $taskbreaker->wpdb();

		$query = $dbase->prepare( "SELECT * FROM {$dbase->prefix}task_breaker_comments WHERE ticket_id = %d", absint( $ticket_id ) );

		$results = $dbase->get_results( $query, 'ARRAY_A' );

		return $results;

	}

	/**
	 * Returns the current logged in users groups.
	 *
	 * @return array The current logged-in user groups.
	 */
	function get_current_user_owned_groups() {

		return $this->get_user_group_admin_mod();

	}

	/**
	 * Get current user groups where he/she is the group admin or
	 * one of the moderators.
	 *
	 * @return array The groups.
	 */
	function get_user_group_admin_mod() {

		$taskbreaker = new TaskBreaker();
		$dbase = $taskbreaker->wpdb();

		if ( ! function_exists( 'buddypress' ) ) {
			return array();
		}

		if ( ! bp_is_active( 'groups' ) ) {
			return array();
		}

		$groups = array();

		$bp_table = buddypress()->groups->table_name_members;

		$user_id = get_current_user_id();

		$group_results_stmt = "SELECT
	            groups.id as group_id,
	            group_member.user_id as user_id,
	            groups.name as group_name,
	            group_member.is_mod,
	            group_member.is_admin
	            FROM
	            {$dbase->prefix}bp_groups_members as group_member
	            INNER JOIN
	            {$dbase->prefix}bp_groups as groups
	            WHERE
	            group_member.group_id = groups.id
	            AND
	            ( group_member.is_mod = 1 OR group_member.is_admin = 1 )
	            AND
	            group_member.user_id = %d GROUP BY groups.id;";

		$group_results = $dbase->get_results(
		$dbase->prepare( $group_results_stmt, $user_id ), OBJECT );

		if ( ! empty( $group_results ) ) {
			return $group_results;
		}

		return $groups;

	}

	/**
	 * Get displayed user group.
	 *
	 * @return array    The current displayed user group.
	 */
	function get_displayed_user_groups() {

		$taskbreaker = new TaskBreaker();
		$dbase = $taskbreaker->wpdb();

		if ( ! function_exists( 'bp_displayed_user_id' ) ) {
			return;
		}

		$current_user_id = intval( bp_displayed_user_id() );

		if ( 0 === $current_user_id ) {
			return array();
		}

		$bp_groups = $dbase->prefix . 'bp_groups';

		$bp_group_members = $dbase->prefix . 'bp_groups_members';

		$stmt = sprintf( "SELECT {$bp_group_members}.group_id, {$bp_groups}.name
				FROM {$bp_group_members }
				INNER JOIN {$bp_groups}
				ON {$bp_groups}.id = {$bp_group_members}.group_id
				WHERE user_id = %d
				ORDER BY {$bp_groups}.name asc", $current_user_id );

		$results = $dbase->get_results( $stmt, 'ARRAY_A' );

		if ( $results ) {
			return $results;
		}

		return array();

	}

	/**
	 * Parses the assigned users and convert them into array.
	 *
	 * @param  string $user_id_collection The collection of user id. Separated by comma.
	 * @return  array The uses collection.
	 */
	function parse_assigned_users( $user_id_collection = '' ) {

		$taskbreaker = new TaskBreaker();
		$dbase = $taskbreaker->wpdb();

		$users = new stdclass;

		if ( ! empty( $user_id_collection ) ) {

			$stmt = esc_sql( "SELECT ID, display_name FROM {$dbase->prefix}users WHERE ID IN({$user_id_collection})" );

			$users = $dbase->get_results( $stmt );

		}

		return $users;

	}

	/**
	 * Get the given group id of any given project.
	 *
	 * @param  integer $project_id The id of the project.
	 * @return integer The group id.
	 */
	function get_project_group_id( $project_id = 0 ) {

		$group_id = 0;

		if ( 0 === $project_id ) {
			return 0;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( empty( $group_id ) ) {
			return 0;
		}

		return $group_id;
	}

	/**
	 * Returns the current logged-in user tasks.
	 *
	 * @param  array $args The arguments you wish to pass on the query.
	 * @return object       The latest tasks of the current logged-in user.
	 */
	function get_current_user_tasks( $args = array() ) {

		$taskbreaker = new TaskBreaker();
		$dbase = $taskbreaker->wpdb();
		$user_id = get_current_user_id();

		$user_assignment_tbl = "{$dbase->prefix}task_breaker_tasks_user_assignment as user_task_assignment";
		$task_tbl = "{$dbase->prefix}task_breaker_tasks as task_table";
		$limit = ( ! empty( $args['task_number'] ) ) ? absint( $args['task_number'] ): 5;

		$stmt = $dbase->prepare( "SELECT * FROM {$user_assignment_tbl} INNER JOIN {$task_tbl} ON task_table.id = user_task_assignment.task_id WHERE user_task_assignment.member_id = %d ORDER BY task_table.id DESC LIMIT %d", $user_id, $limit );

		return $dbase->get_results( $stmt, OBJECT );
		;

	}

}


