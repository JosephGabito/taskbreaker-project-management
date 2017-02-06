<?php
/**
 * Task Breaker Conditional tags
 *
 * @since 0.0.1
 * @package TaskBreaker\TaskBreakerCT
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * TaskBreakerCT is a singleton class that contains several useful methods to check the capability
 * of the current logged in user before creating task, deleting task, creating new project, etc.
 *
 * @package TaskBreaker\TaskBreakerCT
 */
class TaskBreakerCT {

	/**
	 * This property will serve as $wpdb clone later.
	 *
	 * @var string
	 */
	var $dbase = '';

	/**
	 * Class constructors. Initiates $wpdb to $_db property.
	 *
	 * @return object TaskBreakerCT
	 */
	private function __construct() {
		$this->dbase = TaskBreaker::wpdb();
		return $this;
	}

	/**
	 * Singleton method that instantiate or return the current instance of TaskBreakerCT.
	 *
	 * @return object TaskBreakerCT
	 */
	public static function get_instance() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new TaskBreakerCT();
		}
		return $instance;
	}

	/**
	 * Check if current user is a member of a group
	 *
	 * @param Integer $group_id The ID of the Group.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function is_group_member( $group_id = 0 ) {

		$bp = buddypress();

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return false;
		}

		$stmt = $this->dbase->prepare(
			"SELECT id FROM {$bp->groups->table_name_members} WHERE user_id = %d 
			AND group_id = %d AND is_confirmed = 1 AND is_banned = 0", $user_id, $group_id
		);

		$results = $this->dbase->get_row( $stmt );

		if ( ! empty( $results ) ) {

			return true;

		}

		return false;

	}

	/**
	 * Check if current user can access projects. Only group members can access the project.
	 *
	 * @param  Integer $project_id The id of the project.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function can_view_project( $project_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( $this->is_group_member( $group_id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if current user can add project to group. Only admin and
	 * moderators can add project to a specific group.
	 *
	 * @param  integer $group_id The id of the group.
	 * @return boolean
	 */
	public function can_add_project_to_group( $group_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		if ( groups_is_user_admin( get_current_user_id(), $group_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current user can edit project. Only admin and moderators can edit
	 * the projects.
	 *
	 * @param  integer $project_id The id of the project.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function can_edit_project( $project_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		if ( groups_is_user_admin( get_current_user_id(), $group_id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if current user can delete the project. Only the project owner and admin
	 * can delete the project.
	 *
	 * @param  integer $project_id The id of the project.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function can_delete_project( $project_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$project_object = get_post( $project_id );

		if ( empty( $project_object ) ) {
			return false;
		}

		$current_user_id = intval( get_current_user_id() );

		$project_owner = intval( $project_object->post_author );

		// Return true if the current owner is the author of project post.
		if ( $project_owner === $current_user_id ) {
			return true;
		}

		// Return true if it's admin.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if current logged in user can see the task inside a project.
	 *
	 * @param  int $project_id The ID of the project.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function can_see_project_tasks( $project_id ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Only members of the group can the project tasks.
		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( $this->is_group_member( $group_id ) ) {

			return true;

		}

		return false;

	}

	/**
	 * Check if current user can add tasks. Only group admin and group mods can add tasks.
	 *
	 * @param  int $project_id The project ID.
	 * @return boolean Returns True on Success. Otherwise, False.
	 */
	public function can_add_task( $project_id ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		if ( groups_is_user_admin( get_current_user_id(), $group_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current logged in user can delete the task inside a specific project.
	 *
	 * @param  integer $project_id The Project ID.
	 * @return boolean             True on success. Otherwise, false.
	 */
	public function can_delete_task( $project_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( empty( $project_id ) ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		if ( groups_is_user_admin( get_current_user_id(), $group_id ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the current logged in user can update the task inside a specific Project.
	 *
	 * @param  integer $project_id The ID of the Project.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function can_update_task( $project_id = 0 ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( empty( $project_id ) ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		if ( groups_is_user_admin( get_current_user_id(), $group_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current user can add task comment.
	 *
	 * @param  integer $project_id The id of the project.
	 * @param  integer $task_id    The id of the task.
	 * @return boolean             True on success. Otherwise, false.
	 */
	public function can_add_task_comment( $project_id = 0, $task_id = 0 ) {

		$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

		$user_access = TaskBreakerCT::get_instance();

		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Return true if the current user is an administrator.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Return true if the current user is a moderator of the group.
		if ( groups_is_user_mod( get_current_user_id(), $group_id ) ) {
			return true;
		}

		// Only members of the group can add comment to project.
		if ( $this->is_group_member( $group_id ) ) {

			// Check to see if the current task has assigned members on it.
			if ( $this->has_members_assigned( $task_id ) ) {

				// If it has assign members on it, disallow un-assigned members to update the task.
				if ( ! $user_access->is_member_assigned_to_task( $task_id ) ) {
					return false;
				}
			}

			return true;
		}

		return false;

	}

	/**
	 * Check to see if the task has assign members in it
	 *
	 * @param  integer $task_id The ID of the task.
	 * @return boolean True if has members on it, otherwise false.
	 */
	public function has_members_assigned( $task_id = 0 ) {

		if ( 0 === $task_id ) {
			return false;
		}

		$stmt = $this->dbase->prepare(
			"SELECT assign_users FROM {$this->dbase->prefix}task_breaker_tasks
	        WHERE id = %d AND assign_users <> %s", absint( $task_id ), ''
		);

		$result = $this->dbase->get_row( $stmt );

		if ( ! empty( $result ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the current logged-in user is assigned to a specific task.
	 *
	 * @param  integer $task_id The ID of the task.
	 * @return boolean          False if there are no task assign to the current user, otherwise True.
	 */
	public function is_member_assigned_to_task( $task_id = 0 ) {

		$current_user_id = get_current_user_id();

		$stmt = $this->dbase->prepare(
			"SELECT task_id FROM {$this->dbase->prefix}task_breaker_tasks_user_assignment
	        WHERE task_id = %d AND member_id = %d", $task_id, $current_user_id
		);

		$result = $this->dbase->get_row( $stmt );

		if ( ! empty( $result ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the project's group is public.
	 *
	 * @param  integer $project_id The project ID.
	 * @return boolean             True on success. Otherwise, false.
	 */
	public function is_project_group_public( $project_id = 0 ) {

		$core = new TaskBreakerCore();
		
		$public_status = 'public';

		$group_id = $core->get_project_group_id( $project_id );

		$group = groups_get_group( $group_id );

		if ( $public_status === $group->status ) {

			return true;

		}

		return false;

	}

}

