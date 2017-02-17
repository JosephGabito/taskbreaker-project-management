<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph G. <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerTransactions
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! is_user_logged_in() ) {
	return;
}

$ticket_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

$project_id = filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );

$task = TaskBreakerTasksController::get_instance();

$delete_task = $task->deleteTask( $ticket_id, $project_id );

if ( $delete_task ) {

	$this->task_breaker_api_message(
		array(
			'message' => 'success',
			'response' => array(
				'id' => absint( $ticket_id ),
			),
			'stats' => $task->getTaskStatistics( absint( $project_id ) ),
		)
	);

} else {

	$this->task_breaker_api_message(
		array(
			'message' => 'fail',
			'type'    => 'unauthorized',
			'response' => array(
				'id' => absint( $ticket_id ),
			),
			'message_text' => esc_html__( 'You are not allowed to delete this task. Only group administrators or group moderators are allowed.', 'task_breaker' ),
			'stats' => $task->getTaskStatistics( absint( $project_id ) ),
		)
	);

}
