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

$task_id = (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
$title = filter_input( INPUT_POST, 'title', FILTER_UNSAFE_RAW );
$description = filter_input( INPUT_POST, 'description', FILTER_UNSAFE_RAW );
$priority = filter_input( INPUT_POST, 'priority', FILTER_UNSAFE_RAW );
$user_id = filter_input( INPUT_POST, 'user_id', FILTER_VALIDATE_INT );
$project_id = filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );
$assigned_users = filter_input( INPUT_POST, 'user_id_collection', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
$file_attachments = filter_input( INPUT_POST, 'file_attachments', FILTER_UNSAFE_RAW );
$template = '';

$user_access = TaskBreakerCT::get_instance();
$task = TaskBreakerTasksController::get_instance();

// Clean $assigned_users var in case the users submits non array parameters.
if ( ! $assigned_users ) {
	$assigned_users = array();
}

$args = array(
	'title' => $title,
	'id' => $task_id,
	'description' => $description,
	'priority' => $priority,
	'user_id' => $user_id,
	'project_id' => $project_id,
	'assigned_users' => $assigned_users,
);

$json_response = array(
	'message' => 'success',
	'type' => 'valid',
	'debug' => $task_id,
	'html' => $template,
);

// Update file attachment
if ( ! empty ( $file_attachments ) ) {
	$taskbreaker_file_attachment = new TaskBreakerFileAttachment();
	$taskbreaker_file_attachment->task_attach_file( $file_attachments, $task_id );
}

$json_response = array_merge( $json_response, $args );

// Make sure the current user is able to update the task.
if ( $user_access->can_update_task( $project_id ) ) {

	if ( $task->updateTask( $task_id, $args ) ) {

		$json_response['message'] = 'success';

	} else {

		$json_response['type'] = 'required';

		if ( ! empty( $title ) && ! empty( $description ) ) {

			$json_response['type'] = 'no_changes';

		}

		$json_response['message'] = 'fail';

	}
} else {

	$json_response['type'] = 'unauthorized';

	$json_response['message'] = 'fail';

}

$this->task_breaker_api_message( $json_response );
