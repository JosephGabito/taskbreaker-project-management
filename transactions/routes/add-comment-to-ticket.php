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

require_once TASKBREAKER_DIRECTORY_PATH . 'models/comments.php';
require_once TASKBREAKER_DIRECTORY_PATH . 'models/tasks.php';

$task      = new TaskBreakerTask();
$comment   = new TaskBreakerTaskComment();
$template  = new TaskBreakerTemplate();

$details    = filter_input( INPUT_POST, 'details', FILTER_SANITIZE_STRING );
$ticket_id  = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );
$priority   = filter_input( INPUT_POST, 'priority', FILTER_VALIDATE_INT );
$completed  = filter_input( INPUT_POST, 'completed', FILTER_SANITIZE_STRING );
$project_id = filter_input( INPUT_POST, 'project_id', FILTER_SANITIZE_STRING );

$user_access = TaskBreakerCT::get_instance();

// Check if current user can add comment
if ( ! $user_access->can_add_task_comment( $project_id, $ticket_id ) ) {

	task_breaker_api_message(
		array(
		'message' => 'fail',
		'stats' => $task->getTaskStatistics( $project_id, $ticket_id ),
		'result' => $template->comments_template( $added_comment ),
		)
	);

}

// Get the current user that is logged in.
$user_id = get_current_user_id();

// Update the priority.
$task->update_priority( $ticket_id, $priority );

// Prepare the comment statuses.
$status = array(
	'no'     => 0,
	'yes'    => 1,
	'reopen' => 2,
);

// Update the task status
if ( $completed === 'yes' ) {
	$task->completeTask( $ticket_id, $user_id );
}

// Reopen task
if ( $completed === 'reopen' ) {
	$task->renewTask( $ticket_id );
}

if ( empty( $user_id ) ) {
	$this->task_breaker_api_message(
		array(
		'message' => 'fail',
		)
	);
}

$new_comment = $comment->set_details( $details )
	->set_user( $user_id )
	->set_status( $status[ $completed ] )
	->set_ticket_id( $ticket_id )
	->save();

if ( $new_comment ) {

	$added_comment = $comment->fetch( $new_comment );

	$this->task_breaker_api_message(
		array(
		'message' => 'success',
		'stats' => $task->getTaskStatistics( $project_id, $ticket_id ),
		'result' => $template->comments_template( $added_comment ),
		)
	);
}
