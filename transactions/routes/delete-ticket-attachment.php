<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph G. <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerTransactions\Routes
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$ticket_id = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );

$fileAttachment = new TaskBreakerFileAttachment();

if ( $fileAttachment->delete_task_attachments( $ticket_id ) ) {
	$this->task_breaker_api_message(
		array(
			'message' => 'success',
			'response' => __( 'File attachment successfully deleted.', 'task_breaker' ),
		)
	);	
} else {
	$this->task_breaker_api_message(
		array(
			'message' => 'fail',
			'response' => __( 'Fail to delete attachment', 'task_breaker' ),
		)
	);	
}