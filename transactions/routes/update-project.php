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

include_once TASKBREAKER_DIRECTORY_PATH . 'models/project.php';

$project = new TaskBreakerProject();

$user_access = TaskBreakerCT::get_instance();

// The project id. Leave blank if creating new project.
$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

// The title of the project.
$project_title = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );

// The content of the project.
$project_content = filter_input( INPUT_POST, 'content', FILTER_DEFAULT );

// The group id of the buddypress group where this project is under.
$project_group_id = filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT );

// The callback format.
$no_json = filter_input( INPUT_POST, 'no_json', FILTER_SANITIZE_STRING );

// Check if current user can add project to group
if ( ! $user_access->can_add_project_to_group( $project_group_id ) ) {

	$this->task_breaker_api_message(
		array(
		'message' => 'failure',
		'project_id' => 0,
		'type' => 'authentication_error',
		)
	);

}

if ( ! empty( $project_id ) ) {

	$project->set_id( $project_id );

}

// Only users who can edit project can access this transaction.
if ( ! empty( $project_id ) ) {
	if ( ! $user_access->can_edit_project( $project_id ) ) {
		$this->task_breaker_api_message(
			array(
				'message' => 'failure',
				'project_id' => 0,
				'type' => 'authentication_error',
			)
		);
	}
}

$project->set_title( $project_title );
$project->set_content( $project_content );
$project->set_group_id( $project_group_id );

if ( $project->save() ) {

	if ( $no_json === 'yes' ) {

		wp_safe_redirect( get_permalink( $project->get_id() ) );

	}

	$this->task_breaker_api_message(
		array(
			'message' => 'success',
			'project_id' => $project->get_id(),
			'project_permalink' => get_permalink( $project->get_id() ),
		)
	);

} else {

	$this->task_breaker_api_message(
		array(
			'message' => 'failure',
			'project_id' => 0,
			'type' => 'error_generic',
		)
	);
}
