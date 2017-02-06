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

$user_access = TaskBreakerCT::get_instance();

$project = new TaskBreakerProject();

$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

$project->set_id( absint( $project_id ) );

$redirect = home_url();

if ( ! $user_access->can_delete_project( $project_id ) ) {

	$this->task_breaker_api_message(
		array(
			'message' => 'fail',
			'response' => __( 'Permission Denied. Unauthorized.' ),
		)
	);

	return;
}

if ( $project->delete() ) {

	// Get the projects page permalink
	$bp_options_pages = get_option( 'bp-pages' );

	if ( ! empty( $bp_options_pages ) && is_array( $bp_options_pages ) ) {

		$project_page_id = $bp_options_pages['projects'];

		if ( ! empty( $project_page_id ) ) {
			$redirect = get_permalink( $project_page_id );
		}
	}

	$this->task_breaker_api_message(
		array(
			'message' => 'success',
			'redirect' => $redirect,
		)
	);

} else {

	$this->task_breaker_api_message(
		array(
		'message' => 'failure',
		)
	);
}
