<?php
$task_id = (int) filter_input( INPUT_POST, 'task_id', FILTER_VALIDATE_INT );

$args = array(
	'message' => 'success',
	'task_id' => 0,
);

$task = TaskBreakerTasksController::get_instance();

$task_id = $task->renewTask( $task_id );

if ( $task_id ) {

	$args['message'] = 'success';
	$args['task_id'] = $task_id;

} else {

	$args['message'] = 'fail';

}

$this->task_breaker_api_message( $args );
