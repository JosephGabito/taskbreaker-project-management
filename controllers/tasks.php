<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskController
 */
if ( ! defined( 'ABSPATH') ) {
	return;
}
/**
 * Manually include the TaskBreakerTasksController dependency.
 */
require_once plugin_dir_path( __FILE__ ) . '../models/tasks.php';

/**
 * TaskBreakerTasksController class is a Singleton object
 * that handles the request coming from Transactions Controller
 * and delegates the task to the right methods. This class uses
 * TaskBreakerTask methods to add, edit, delete, and display tasks
 *
 * @since    1.0 Early Plugin Release
 * @author   Joseph G. [joseph@useissuestabinstead.com]
 * @version  1.0
 */
class TaskBreakerTasksController extends TaskBreakerTask {

	/**
	 * Class constructor.
	 *
	 * @return  object The instance of this class.
	 */
	public function __construct() {

		return $this;

	}

	/**
	 * Creates an instance for our TaskController Object.
	 *
	 * @return  object The instance of this class.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( null === $instance ) {

			$instance = new TaskBreakerTasksController();

		}

		return $instance;

	}

	/**
	 * Creates a new task.
	 *
	 * @param array $params The task properties. Includes 'title', 'description', 'priority', 'user_id', 'project_id', 'user_id_collection'.
	 */
	public function addTicket( $params = array() ) {

		do_action( 'taskbreaker_controller_before_add_task' );

		$args = apply_filters( 'taskbreaker_controller_task_args', array(
			'title' => '',
			'description' => '',
			'milestone_id' => 0,
			'project_id' => 0,
			'user_id' => 0,
			'priority' => 0,
			'user_id_collection' => array(),
		));

		foreach ( $params as $key => $value ) {

			if ( ! empty( $value ) ) {

				$args[ $key ] = $value;

			}
		}

		$this->setTitle( $args['title'] )
			 ->setDescription( $args['description'] )
			 ->setMilestoneId( $args['milestone_id'] )
			 ->setProjectId( $args['project_id'] )
			 ->setUser( $args['user_id'] )
			 ->setPriority( $args['priority'] )
			 ->setAssignUsers( $args['user_id_collection'] );

		if ( empty( $this->title ) || empty( $this->description ) ) {

			return false;

		}

		return $this->prepare()->save();

	}

	/**
	 * Deletes a task.
	 *
	 * @param  integer $id         The ID of the Task.
	 * @param  integer $project_id The ID of the Project where the Task belongs to.
	 * @return boolean             True on successful delete, otherwise, false.
	 */
	public function deleteTask( $id = 0, $project_id = 0 ) {

		$user_access = TaskBreakerCT::get_instance();

		// Return false if there is no id specified.
		if ( 0 === $id ) {
			return false;
		}

		if ( ! $user_access->can_delete_task( $project_id ) ) {
			return false;
		}

		return $this->setProjectId( $project_id )->setId( $id )->prepare()->delete();

	}

	/**
	 * Updates the Task.
	 *
	 * @param  integer $id   The ID of the Task.
	 * @param  array   $args The properties of the Task. Includes 'title', 'description', 'priority', 'user_id', 'project_id', 'assigned_users'.
	 * @return boolean	True on successful update. Otherwise, false.
	 */
	public function updateTask( $id = 0, $args = array() ) {

		$user_access = TaskBreakerCT::get_instance();

		// Make sure the current user is able to update the task.
		if ( ! $user_access->can_update_task( $args['project_id'] ) ) {

			return false;

		}

		$this->setTitle( $args['title'] );
		$this->setId( $id );
		$this->setDescription( $args['description'] );
		$this->setPriority( $args['priority'] );
		$this->setUser( $args['user_id'] );
		$this->setProjectId( $args['project_id'] );
		$this->setAssignUsers( $args['assigned_users'] );

		return $this->prepare()->save();

	}

	/**
	 * Renders the task. Populate 'id' argument to render single task.
	 *
	 * @param  array $args The arguments for fetching the task. Includes 'project_id', 'id', 'page', 'priority', 'search', 'orderby', 'order', 'show_completed', 'echo'.
	 * @return array  The tasks available that mathces the giver arguments.
	 */
	public function renderTasks( $args = array() ) {

		return $this->prepare()->fetch( $args );

	}

	/**
	 * Updates the task and mark it as 'Complete'.
	 *
	 * @param  integer $task_id the ID of the Task.
	 * @param  integer $user_id the ID of the User that completed the Task.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function completeTask( $task_id = 0, $user_id = 0 ) {

		parent::prepare();

		return parent::completeTask( $task_id, $user_id );

	}

	/**
	 * Reopens the task after it has been completed.
	 *
	 * @param  integer $task_id The ID of the task.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function renewTask( $task_id = 0 ) {

		parent::prepare();

		return parent::renewTask( absint( $task_id ) );

	}

	/**
	 * Fetches the priority of the given task.
	 *
	 * @param  integer $priority the priority index. Can be 1, 2, and 3.
	 * @return string The priority of the task.
	 */
	public function getPriority( $priority = 1 ) {

		return parent::getPriority( absint( $priority ) );

	}

	/**
	 * Assign the users to specific tasks.
	 *
	 * @param  array $user_id_collection The id of the users separated by comma.
	 * @return boolean True on success. Otherwise, false.
	 */
	public function setAssignUsers( $user_id_collection = array() ) {

		parent::prepare();

		return parent::assignUsersToTask( $user_id_collection );

	}

}
