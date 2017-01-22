<?php
/**
 * Controller for tasks
 */
require_once plugin_dir_path( __FILE__ ) . '../models/tasks.php';

class ThriveProjectTasksController extends ThriveProjectTasksModel {


	public function __construct() {

		return $this;

	}

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

	public function deleteTicket( $id = 0 ) {

		// delete the ticket
		if ( 0 === $id ) {
			echo __( 'Invalid ticket id', 'task-breaker' );
			die();
		}

		return $this->setId( $id )->prepare()->delete();

	}

	public function updateTicket( $id = 0, $args = array() ) {

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

	public function renderTicketsByMilestone( $milestone_id = 0 ) {

		return array();
	}

	public function renderTicketsByUser( $user_id = 0 ) {

		return array();

	}


	public function completeTask( $task_id = 0, $user_id = 0 ) {

		parent::prepare();

		return parent::completeTask( $task_id, $user_id );

	}

	public function renewTask( $task_id = 0 ) {

		parent::prepare();

		return parent::renewTask( $task_id );
	}

	public function getPriority( $priority = 1 ) {

		return parent::getPriority( $priority );

	}

	public function setAssignUsers( $user_id_collection = array() ) {

		parent::prepare();

		return parent::assignUsersToTask( $user_id_collection );

	}

}

