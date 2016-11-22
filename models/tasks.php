<?php
/**
 * ThriveProjectTasksModel
 *
 * This model contains the object used for TaskBreaker
 *
 * @author dunhakdis
 * @since 1.0
 * @version 1.1
 */
class ThriveProjectTasksModel {

	/**
	 * Contains the name of task
	 * @var string
	 */
	var $model = '';

	/**
	 * The ID of the task
	 * @var integer
	 */
	var $id = 0;

	/**
	 * The title of the task
	 * @var string
	 */
	var $title = '';

	/**
	 * The description of the task
	 * @var string
	 */
	var $description = '';

	/**
	 * The user id
	 * @var integer
	 */
	var $user_id = 1;

	/**
	 * The date pertaining time the task is inserted into the table
	 * @var string
	 */
	var $date = '';

	/**
	 * The milestone ID (Coming Soon)
	 * @var integer
	 */
	var $milestone_id = 0;

	/**
	 * The priority of the task
	 * @var integer
	 */
	var $priority = 0;

	/**
	 * The project id of task
	 * @var integer
	 */
	var $project_id = 0;

	/**
	 * The users that are assigned into this task
	 */
	var $group_members_assigned = '';

	/**
	 * Update the table name on initiate
	 */
	public function __construct() {

		global $wpdb;

		$this->model = sprintf( '%stask_breaker_tasks', $wpdb->prefix );
	}

	/**
	 * Method that calls the __construct function
	 * in case you don't need to instantitate the same
	 * object again and again
	 *
	 * @return object self
	 */
	public function prepare() {

		self::__construct();

		return $this;
	}

	/**
	 * Sets the id of our task
	 * @param integer $id
	 */
	public function setId($id = 0) {

		$this->id = $id;

		return $this;

	}

	public function setTitle($title = '') {

		$this->title = $title;

		return $this;
	}

	public function setDescription($description = '') {

		$this->description = $description;

		return $this;
	}

	public function setUser($user_id = 1) {
		$this->user_id = $user_id;

		return $this;
	}

	public function setDate($date = '') {
		$this->date = $date;

		return $this;
	}

	public function setMilestoneId($id = 0) {
		$this->milestone_id = $id;

		return $this;
	}

	public function setPriority($priority = 1) {

		$priority = intval( $priority );

		if ( $priority < 1 ) {$priority = 1;}
		if ( $priority > 3 ) {$priority = 3;}

		$this->priority = $priority;

		return $this;
	}

	public function setAssignUsers( $user_id_collection = array() ) {

		$this->group_members_assigned = implode( ",", $user_id_collection );

		return $this;
	}

	public function getPriority($priority = 1) {

		$priority = abs( $priority );

		if ( $priority > 3 || $priority === 0 ) {
			$priority = 3;
		}

		$priority_collection = $this->getPriorityCollection();

		return $priority_collection["{$priority}"];
	}

	public function getPriorityCollection() {
		return array(
			'1' => apply_filters( 'task_breaker_task_priority_1_label', 'Normal' ),
			'2' => apply_filters( 'task_breaker_task_priority_2_label', 'High' ),
			'3' => apply_filters( 'task_breaker_task_priority_3_label', 'Critical' ),
		);
	}

	public function setProjectId($project_id = 0) {

		$this->project_id = $project_id;

		return $this;

	}

	public function completeTask($task_id = 0, $user_id = 0) {

		if ( empty( $task_id ) || empty( $user_id ) ) {

			return false;

		} else {

			global $wpdb;

			$task = array(
					'completed_by' => $user_id,
				);

			$task_format = array( '%d' );

			$updated_task = array(
					'id' => $task_id,
				);

			$updated_task_format = array( '%d' );

			$updated_task_query = $wpdb->update( $this->model, $task, $updated_task, $task_format, $updated_task_format );

			if ( $updated_task_query === 1 ) {
				return $task_id;
			} else {
				return false;
			}
		}

		return false;
	}

	public function renewTask($task_id = 0) {

		$user_unassigned = 0;

		if ( empty( $task_id ) ) {
			return false;
		} else {
			global $wpdb;
			$task = array(
					'completed_by' => $user_unassigned,
				);
			$task_format  = array( '%d' );
			$updated_task = array( 'id' => $task_id );
			$updated_task_format = array( '%d' );
			$updated_task_query = $wpdb->update( $this->model, $task, $updated_task, $task_format, $updated_task_format );
			if ( $updated_task_query === 1 ) {
				return $task_id;
			} else {
				return false;
			}
		}
		return false;
	}

	public function showError() {
		$this->show_errors();
		$this->print_error();
		echo 'last query:' . $this->last_query;
	}

	public function fetch($args = array()) {

		// fetch all tickets if there is no id specified
		global $wpdb;

		$defaults = array(
			'project_id' => 0,
			'id' => 0,
			'page' => 1,
			'priority' => -1,
			'search' => '',
			'orderby' => 'date_created',
			'order' => 'asc',
			'show_completed' => 'no',
			'echo' => true,
		);

		// Assign default values and sanitize everything!
		foreach ( $defaults as $option => $value )
		{
			if ( ! empty( $args[$option] ) )
			{
				$$option = $args[$option];
			} else
			{
				$$option = $value;
			}
		}

		// project id should be specified
		// when not editing
		if ( $id === 0 ) {
			if ( $project_id === 0 ) {
				return array();
			}
		}

		if ( $id === 0 ) {

			$funnels = array();

			// where claused
			$filters = '';
			$allowed_priority = array( '1','2','3' );

			if ( $priority != -1 && in_array( $priority, $allowed_priority ) ) {
				$funnels[] = array(
						'column'  => 'priority',
						'operand' => '=',
						'value'   => absint( $priority ),
						'format'  => 'raw',
					);
			}
			// search
			if ( ! empty( $search ) ) {
				$funnels[] = array(
						'column'  => 'title',
						'operand' => 'like',
						'value'   =>  '%' . $wpdb->_real_escape( $search ). '%',
						'format'  => 'string',
					);
			}

			// show only tasks that are not completed
			if ( 'no' === $show_completed ) {
				$funnels[] = array(
					'column'  => 'completed_by',
					'operand' => '=',
					'value'   => '0',
					'format'  => 'raw',
				);
			} else {
				$funnels[] = array(
					'column'  => 'completed_by',
					'operand' => '<>',
					'value'   => '0',
					'format'  => 'raw',
				);
			}

			// always specify the project id
			if ( $project_id !== 0 ) {
				$funnels[] = array(
					'column' => 'project_id',
					'operand' => '=',
					'value' => absint( $project_id ),
					'format' => 'raw',
				);
			}

			if ( ! empty( $funnels ) ) {
				$filters .= 'WHERE ';
			}

			$count = 0;

			foreach ( $funnels as $funnel ) {

				$count++;

				if ( $funnel['format'] == 'string' ) {
					$funnel['value'] = "'".$funnel['value']."'";
				}

				$filters .= "{$funnel['column']} {$funnel['operand']} {$funnel['value']} AND ";

			}
			// echo $filters;
			$filters = substr( $filters, 0, strlen( $filters ) - 4 );

			// limit claused
			$limit = TASK_BREAKER_PROJECT_LIMIT;

			// total number of task per page
			$perpage = ceil( $limit );

			// set the current page to 1
			$currpage = ceil( $page );
			if ( $currpage <= 0 ) {$currpage = 1;}

			// initiate the row offset to zero
			$offset  = 0;

			// get total number of rows in the table
			$row_count_stmt = "SELECT COUNT(*) as count from {$this->model} {$filters}";
				$row = $wpdb->get_row( $row_count_stmt, OBJECT );
					$row_count = intval( $row->count );

			// control the offset
			if ( $currpage !== 0 ) {
			    $offset = $perpage * ($currpage -1);
			}

			// controls the maximum number of page
			// if user throws a page more than
			// the result has, set it to the highest
			// number of page
			if ( $offset >= $row_count ) {
				$offset = $row_count - $perpage;
			}

			if ( $offset < 0 ) {
				$offset = 0;
			}

			// minimum page is always equal to 1
			$min_page = 1;

			// maximum page is the total number of page, hence ceil(total/perpage)
			$max_page = ceil( $row_count / $limit );

			$stmt = "SELECT * FROM {$this->model} {$filters} ORDER BY {$orderby} {$order}, id desc LIMIT {$perpage} OFFSET {$offset}";

			$results = $wpdb->get_results( $stmt, OBJECT );

			if ( ! empty( $results ) ) {

				$stats = array();

					$total     = $stats['total'] 		= $row_count;
					$perpage   = $stats['perpage'] 		= $perpage;
					$totalpage = $stats['total_page'] 	= ceil( $total / $perpage );
					$currpage  = $stats['current_page'] = $currpage;
					$min_page  = $stats['min_page'] 	= $min_page;
					$max_page  = $stats['max_page'] 	= $max_page;

				return array(
						'stats' => $stats,
						'results' => (object) $results,
					);
			}
		}

		if ( $id !== 0 ) {

			$stmt = sprintf( "SELECT * FROM {$this->model} WHERE id = {$id} order by priority desc, date_created desc" );

			$result = $wpdb->get_row( $stmt );

			if ( ! empty( $result ) ) {

				$allowed_html = array(
					    'a' => array(
					        'href' => array(),
					        'title' => array(),
					    ),
					    'br' => array(),
					    'em' => array(),
					    'strong' => array(),
					    'del' => array(),
					    'ul' => array(),
					    'ol' => array(),
					    'li' => array(),
					    'code' => array(),
					    'img' => array(),
					    'ins' => array(),
					    'blockquote' => array(),
					    'hr' => array(),
					    'p' => array(
					    		'style' => array(),
					    	),
					);

				// Sanitize the title to prevent XSS.
				$result->title = stripslashes( $result->title );

				// Sanitize the description to prevent XSS.
				$result->description = stripslashes( wp_kses( $result->description, $allowed_html ) );

				// Let's assign custom meta for assigned users so that we can fetch the result easier later on.
				$members_stack = array();

				// Assign users meta data.
				$result->assign_users_meta = array(
					'members_stack' => $members_stack,
					'count' => 0
				);

				$assign_users = explode( ",", $result->assign_users );

				if ( ! empty( $assign_users ) && is_array( $assign_users ) ) {

					$get_assigned_users_record = new WP_User_Query( array( 'include' => $assign_users ) );

					if ( ! empty ( $get_assigned_users_record->results ) ) {

						foreach ( $get_assigned_users_record->results as $ua_result ) {

							$members_stack[] = array(
								'ID' => absint(  $ua_result->data->ID ),
								'display_name' => wp_kses( $ua_result->data->display_name, $allowed_html )
							);
						}

						$result->assign_users_meta = array(
							'members_stack' => $members_stack,
							'count' => absint( count( $members_stack ) )
						);
					}
				}

				// Group ID
				$result->group_id = get_post_meta( $result->project_id, 'task_breaker_project_group_id', true );

			}

			return $result;
		}

		return array();
	}

	public function save( $args = array() ) {

		global $wpdb;

		$args = array(
			'title' => $this->title,
			'description' => $this->description,
			'user' => $this->user_id,
			'milestone_id' => $this->milestone_id,
			'project_id' => $this->project_id,
			'priority' => $this->priority,
			'date_created' => date("Y-m-d H:i:s"),
			'assign_users' => $this->group_members_assigned
		);

		$trimmed_title = trim( $this->title );

		if ( empty( $trimmed_title ) ) {

			return false;

		}

		$trimmed_description = trim( $this->description );

		if ( empty( $trimmed_description ) ) {

			return false;

		}

		$format = array(
				'%s', // Title.
				'%s', // Description.
				'%d', // User.
				'%d', // Milestone Id.
				'%d', // Project Id.
				'%d', // Priority.
				'%s', // Date Created.
				'%s'  // Assign Users.
			);

		if ( ! empty( $this->id ) ) {

			// Assign members to the task.
			$this->assign_members( $this->id, $this->group_members_assigned );

			return ( $wpdb->update( $this->model, $args, array( 'id' => $this->id ), $format, array( '%d' ) ) === 0 );

		} else {

			if ( $wpdb->insert( $this->model, $args, $format ) ) {

			 	$last_insert_id = $wpdb->insert_id;

				// Assign members to the task.
				$this->assign_members( $last_insert_id, $this->group_members_assigned );

			 	// Add new activity. Check if buddypress is active first
			 	if ( function_exists( 'bp_activity_add' ) ) {

			 		$bp_user_link = '';

			 		if ( function_exists( 'bp_core_get_userlink' ) ) {

			 			$bp_user_link = bp_core_get_userlink( $this->user_id );

			 		}

			 		$task_breaker_project_post = get_post( $this->project_id, OBJECT );

			 		$task_breaker_project_name = '';

			 		$permalink = get_permalink( $this->project_id );

			 		if ( ! empty( $task_breaker_project_post ) ) {

			 			$task_breaker_project_name = sprintf( '<a href="%s" title="%s">%s<a/>', $permalink, $task_breaker_project_post->post_title, $task_breaker_project_post->post_title );

			 		}

			 		$action = sprintf( __( '%s added new task under %s', 'task_breaker' ), $bp_user_link, $task_breaker_project_name );

					if ( task_breaker_is_project_group_public( $this->project_id ) ) {

				 		$new_activity_id = bp_activity_add(
				 			array(
								'user_id' => $this->user_id,
								'action' => apply_filters( 'task_breaker_new_task_activity_action', $action, $this->user_id ),
								'component' => 'project',
								'content' => apply_filters( 'task_breaker_new_task_activity_descriptioin', sprintf( '<a href="%s" title="%s">#%d - %s</a>', $permalink . '#tasks/view/' . $last_insert_id, $this->title, $last_insert_id, $this->title ) ),
								'type' => 'task_breaker_new_task',
							)
						);

					}

					// Send a notification to the assigned member
					$exploded_members = explode( ",", $this->group_members_assigned );

					foreach ( (array) $this->group_members_assigned as $ua_id ) {
						bp_notifications_add_notification(
							array(
						        'user_id'           => $ua_id,
								'item_id'           => $last_insert_id,
						        'secondary_item_id' => $this->user_id,
						        'component_name'    => 'task_breaker_ua_notifications_name',
						        'component_action'  => 'task_breaker_ua_action',
						        'date_notified'     => bp_core_current_time(),
						        'is_new'            => 1,
					    	)
						);
					}

			 	}

			 	return $last_insert_id;

			} else {

			 	return false;

			}
		}
	}

	public function assign_members( $task_id = 0, $members_assign = "" ) {

		global $wpdb;

		if ( 0 === $task_id ) {
			return $this;
		}

		if ( empty( $members_assign ) ) {
			return $this;
		}

		$table = $wpdb->prefix . TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE;

		// Clear any existing records.
		$wpdb->delete( $table,
			array( 'task_id' => $task_id ), // Entry
			array( '%d' ) // Format.
		);

		$exp_members_assigned = explode( ',', $members_assign );

		if ( ! empty( $exp_members_assigned ) ) {

			foreach ( $exp_members_assigned as $task_member_id ) {
				$wpdb->insert(
					$table,
					array( 'task_id' => intval( $task_id ), 'member_id' => intval( $task_member_id ) ), // Entry.
					array( '%d', '%d' ) ); // Format.
				}
		}

		return $this;
	}

	public function getCount($project_id = 0, $type = 'all') {

		global $wpdb;

		if ( $project_id === 0 ) {
			return 0;
		}

		$where = sprintf( 'WHERE project_id = %d', $project_id );

		if ( $type == 'completed' ) {
			$where .= ' AND completed_by <> 0';
		}

		if ( $type == 'open' ) {
			$where .= ' AND completed_by = 0';
		}

		$this->prepare();

		$row_count = 0;

		$row_count_stmt = "SELECT COUNT(*) as count from {$this->model} {$where}";
			$row = $wpdb->get_row( $row_count_stmt, OBJECT );
				$row_count = intval( $row->count );

		return $row_count;
	}

	public function update_priority($task_id = 0, $new_priority = 1) {

		global $wpdb;

		if ( $task_id === 0 ) {
			return false;
		}

		$this->setPriority( $new_priority );

		$wpdb->update(
			$this->model,
			array( 'priority' => $this->priority ), // integer (number)
			array( 'id' => $task_id ),
			array( '%d' ), // Integer Format for priority.
			array( '%d' )  // Integer Format for ID.
		);

		return false;

	}

	/**
	 * Deletes the task
	 *
	 * @php todo should return the task id if successfal otherwise return false
	 * @return
	 */
	public function delete() {

		global $wpdb;

		if ( 0 === $this->id ) {
			echo 'Model Error: ticket ID is ' . $this->id;
		} else {
			$wpdb->delete( $this->model, array( 'id' => $this->id ), array( '%d' ) );
		}

		return $this;
	}

	public function getTaskStatistics( $project_id = 0, $task_id = 0 ) {

		if ( 0 === $project_id ) {

			return array();

		}

		$task_total = $this->getCount( $project_id, 'all' );

		$task_total_completed = $this->getCount( $project_id, 'completed' );

		$task_total_open = $this->getCount( $project_id, 'open' );

		$task_progress = ceil( ( $task_total_completed / $task_total ) * 100 ) . "%";

		$stats =  array(
				'total' 	=> $task_total,
				'completed' => $task_total_completed,
				'remaining' => $task_total_open,
				'status'    => null,
				'progress'  => sprintf( __('%s Completed', 'task_breaker'), $task_progress )
			);


		// If there is a task id, fetch the task using its ID
		if ( $task_id > 0 ) {

			$the_task = $this->fetch( array( 'id' => intval( $task_id ) ) );

			if ( $the_task ) {

				$priority = $this->getPriority( $the_task->priority );

				$completed_by = $the_task->completed_by;

				$task_status = __('Open', 'task_breaker');

				if ( $completed_by >= 1 ) {

					$task_status = __('Completed', 'task_breaker');

				}

				$stats['status'] = array(
						'task_id' => intval( $task_id ),
						'task_status' => $task_status,
						'priority' => $priority
					);
			}
		}

		return $stats;
	}

	public function assignUsersToTask( $user_id_collection = array() ) {

		$this->group_members_assigned = implode( ',', $user_id_collection );

		return $this;

	}
}
