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

$task_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
$page = filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT );
$project_id = filter_input( INPUT_GET, 'project_id', FILTER_VALIDATE_INT );
$priority = filter_input( INPUT_GET, 'priority', FILTER_VALIDATE_INT );
$search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_URL );
$show_completed = filter_input( INPUT_GET, 'show_completed', FILTER_SANITIZE_STRING );
$callback_template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_STRING );

$html_template = 'render_tasks';
$task_user_access = TaskBreakerCT::get_instance();
$template = '';

if ( ! empty( $callback_template ) ) {
	$html_template = $callback_template;
}

if ( ! $task_user_access->can_see_project_tasks( $project_id ) ) {

	task_breaker_api_message(
		array(
		'message' => 'fail',
		'message_long' => __( 'Unable to access the task details. Only group members can access this page', 'task_breaker' ),
		'task'    => array(),
		'stats'   => array(),
		'debug'   => __( 'Unauthorized Access', 'task_breaker' ),
		'html'    => '',
		)
	);

	return;

}

$task = TaskBreakerTasksController::get_instance();

$args = array(
	'project_id' => $project_id,
	'id' => $task_id,
	'page' => $page,
	'priority' => $priority,
	'search' => $search,
	'show_completed' => $show_completed,
	'orderby' => 'priority',
	'order' => 'desc',
	'echo' => 'no',
);

$task_collection = $task->renderTasks( $args );

if ( empty ( $task_collection ) ) {
	$task_collection = array();
}

$taskbreaker_template = new TaskBreakerTemplate();

if ( 0 === $task_id ) {

	$task_id = null;

	$template = $taskbreaker_template->$html_template( $args );

} else {

	if ( ! empty( $callback_template ) ) {

		$template = $taskbreaker_template->$html_template( $task_collection );

	}
}

$stats = array();

if ( array_key_exists( 'stats', $task_collection ) ) {

	$stats = $task_collection['stats'];

}

$this->task_breaker_api_message(
	array(
		'message' => 'success',
		'task'    => $task_collection,
		'stats'   => $stats,
		'debug'   => $task_id,
		'html'    => $template,
	)
);
