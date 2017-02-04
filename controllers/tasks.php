<?php
/*
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Manually include the ThriveProjectTasksController dependency.
 */
require_once plugin_dir_path( __FILE__ ) . '../models/tasks.php';

/**
 * ThriveProjectTasksController class is a Singleton object
 * that handles the request coming from Transactions Controller
 * and delegates the task to the right methods. This class uses
 * ThriveProjectTasksModel methods to add, edit, delete, and display tasks
 *
 * @since 	1.0 Early Plugin Release
 * @author 	Joseph G. [joseph@useissuestabinstead.com]
 * @version 1.0
 * @package TaskBreaker\TaskController
 */
class ThriveProjectTasksController extends ThriveProjectTasksModel {

	public function __construct() {

		return $this;

	}

	/**
	 * Creates new task.
	 *
	 * @param array $params The task properties.
	 */
	public function addTicket( $params = array() ) {

		$args = array(
			'title' => '',
			'description' => '',
			'milestone_id' => 0,
			'project_id' => 0,
			'user_id' => 0,
			'priority' => 0,
			'user_id_collection' => array(),
		 );

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

	public function deleteTask( $id = 0, $project_id = 0 ) {

		// delete the ticket
		if ( 0 === $id ) {
			return false;
		}

		if ( ! task_breaker_can_delete_task( $project_id ) ) {
			return false;
		}

		return $this->setProjectId( $project_id )->setId( $id )->prepare()->delete();

	}

	public function updateTask( $id = 0, $args = array() ) {

		// Make sure the current user is able to update the task.
		if ( ! task_breaker_can_update_task( $args['project_id'] ) ) {

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

	public function renderTasks( $args = array() ) {

		return $this->prepare()->fetch( $args );

	}

	public function completeTask( $task_id = 0, $user_id = 0 ) {

		parent::prepare();

		return parent::completeTask( $task_id, $user_id );

	}

	public function renewTask( $task_id = 0 ) {

		parent::prepare();

		return parent::renewTask( absint( $task_id ) );
	}

	public function getPriority( $priority = 1 ) {

		return parent::getPriority( absint( $priority ) );

	}

	public function setAssignUsers( $user_id_collection = array() ) {

		parent::prepare();

		return parent::assignUsersToTask( $user_id_collection );

	}

}

