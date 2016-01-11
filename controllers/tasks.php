<?php
/**
 * Controller for tasks
 */
require_once( plugin_dir_path( __FILE__ ) . '../models/tasks.php' );

class ThriveProjectTasksController extends ThriveProjectTasksModel{

	public function __construct() {
		return $this;
	}

	public function addTicket($params = array()) {

		$args = array(
				'title' => '',
				'description' => '',
				'milestone_id' => 0,
				'project_id' => 0,
				'user_id' => 0,
				'priority' => 0
			);

		foreach ( $params as $key => $value ) {

			if ( ! empty ( $value ) ) {

				$args[ $key ] = $value;

			}

		}

		$this->setTitle( $args['title'] )
			 ->setDescription( $args['description'] )
			 ->setMilestoneId( $args['milestone_id'] )
			 ->setProjectId( $args['project_id'] )
			 ->setUser( $args['user_id'] )
			 ->setPriority( $args['priority'] );

		if ( empty( $this->title ) || empty( $this->description ) ) {
			return false;
		}

		return $this->prepare()->save();

	}

	public function deleteTicket($id = 0) {

		// delete the ticket
		if ( 0 === $id ) {
			echo 'INVALID [ID] PROVIDED #controllers/thrive-project-tasks@line:34';
		}

		return $this->setId( $id )->prepare()->delete();

	}

	public function updateTicket($id = 0, $args = array()) {

		$this->setTitle( $args['title'] );
		$this->setId( $id );
		$this->setDescription( $args['description'] );
		$this->setPriority( $args['priority'] );
		$this->setUser( $args['user_id'] );
		$this->setProjectId( $args['project_id'] );
		
		return $this->prepare()->save();

	}

	public function renderTasks($args = array()) {

		return $this->prepare()->fetch( $args );

	}

	public function renderTicketsByMilestone($milestone_id = 0) {

		return array();
	}

	public function renderTicketsByUser($user_id = 0) {
		return array();
	}


	public function completeTask($task_id = 0, $user_id = 0) {

		parent::prepare();

		return parent::completeTask( $task_id, $user_id );

	}

	public function renewTask($task_id = 0) {

		parent::prepare();

		return parent::renewTask( $task_id );
	}

	public function getPriority($priority = 1) {
		return parent::getPriority( $priority );
	}

}
?>
