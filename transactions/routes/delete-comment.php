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

include_once TASKBREAKER_DIRECTORY_PATH . 'models/comments.php';

$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_VALIDATE_INT ) );

if ( 0 === $comment_id ) {
	task_breaker_api_message(
		array(
		'message'  => 'failure',
		'response' => 'Invalid Comment ID',
		)
	);
}

// Proceed.
$comment = new TaskBreakerTaskComment();

// Delete the comment and handle the result
if ( $comment->set_id( $comment_id )->set_user( get_current_user_id() )->delete() ) {
	$this->task_breaker_api_message(
		array(
		'message'  => 'success',
		)
	);
} else {
	// Otherwise, tell the client to throw an error
	$this->task_breaker_api_message(
		array(
		'message' => 'failure',
		)
	);
}
