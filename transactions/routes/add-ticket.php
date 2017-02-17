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


$task = TaskBreakerTasksController::get_instance();

$user_access = TaskBreakerCT::get_instance();

$task_id = $task->addTicket( $_POST );

if ( ! $user_access->can_add_task( (int) $_POST['project_id'] ) ) {
	$this->task_breaker_api_message(
		array(
			'message' => 'fail',
			'response' => __( 'Unable to add tasks. Only a group administrator or a group moderator can add tasks.', ' task_breaker' ),
		)
	);
}

if ( $task_id ) {

	do_action( 'taskbreaker_task_saved' );
	// Attach the file into the task.
	if ( !empty( $_POST['file_attachments'] ) ) {
		$taskbreaker_file_attachment = new TaskBreakerFileAttachment();
		$taskbreaker_file_attachment->task_attach_file( $_POST['file_attachments'], $task_id );
	}

	$this->task_breaker_api_message(
		array(
			'message' => 'success',
			'response' => array(
					'id' => $task_id,
				),
			'stats' => $task->getTaskStatistics( (int) $_POST['project_id'] ),
		)
	);

} else {

	$this->task_breaker_api_message(
		array(
			'message' => 'fail',
			'response' => __(
				'There was an error trying to add this task. Title and Description fields are required or there was an unexpected error.', ' task_breaker'
			),
		)
	);
}
