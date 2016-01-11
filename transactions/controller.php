<?php
/**
 * This file act as a middleware for every transaction
 *
 * @since  1.0
 * @author  dunhakdis
 */

// check if access directly
if ( ! defined( 'ABSPATH' ) ) {   die(); }

$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

// Try getting post request if $action is empty when getting request via 'get' method.
if ( empty( $action ) ) {
	
	$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

}

if ( 'thrive_transactions_request' !== $action ) {
	
	return;

}

// Format our page header when Unit Testing
if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {

	header( 'Content-Type: application/json' );

} else {

	// Hide warnings when running tests
	@header( 'Content-Type: application/json' );

}

add_action( 'wp_ajax_thrive_transactions_request', 'thrive_transactions_callblack' );

require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

/**
 * Executes the method or function requested by the client
 * @return void
 */
function thrive_transactions_callblack() {

	// Always check for nonce before proceeding...
	$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

	// If INPUT_GET is empty try input post
	if ( empty( $nonce ) ) {

		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

	}

	if ( ! wp_verify_nonce( $nonce, 'thrive-transaction-request' ) ) 
	{

		die( 
			__( 'Invalid Request. Your session has already expired (invalid nonce). 
				Please go back and refresh your browser. Thanks!', 'thrive' ) 
		);

	}

	$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_ENCODED );

	if ( empty( $method ) ) {
		// try get action
		$method = filter_input( INPUT_GET, 'method', FILTER_SANITIZE_ENCODED );
	}

	$allowed_callbacks = array(

		// Tickets/Tasks callbacks
		'thrive_transaction_add_ticket',
		'thrive_transaction_delete_ticket',
		'thrive_transaction_fetch_task',
		'thrive_transaction_edit_ticket',
		'thrive_transaction_complete_task',
		'thrive_transaction_renew_task',

		// Comments callback functions.
		'thrive_transaction_add_comment_to_ticket',
		'thrive_transaction_delete_comment',

		// Project callback functions.
		'thrive_transactions_update_project',
		'thrive_transactions_delete_project',
	);

	if ( function_exists( $method ) ) {
		if ( in_array( $method, $allowed_callbacks ) ) {
			// execute the callback
			$method();
		} else {
			thrive_api_message(array(
				'message' => 'method is not listed in the callback',
			));
		}
	} else {
		thrive_api_message(array(
			'message' => 'method not allowed or method does not exists',
		));
	}

	thrive_api_message(array(
			'message' => 'transaction callback executed',
		));
}

function thrive_api_message($args = array()) {
	echo json_encode( $args );
	die();
}

function thrive_transaction_add_ticket() {

	$task = new ThriveProjectTasksController();

	$task_id = $task->addTicket( $_POST );

	if ( $task_id ) {
		
		thrive_api_message( array(
			'message' => 'success',
			'response' => array(
					'id' => $task_id,
				),
			'stats' => $task->getTaskStatistics( (int) $_POST['project_id'] )
		));

	} else {
		thrive_api_message( array(
			'message' => 'fail',
			'response' => __('There was an error trying to add this task. 
				Title and Description fields are required or there was 
				an unexpected error.',' thrive'),
		) );
	}

	return;
}

function thrive_transaction_delete_ticket() {

	$ticket_id = (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	$project_id = (int) filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );;

	$task = new ThriveProjectTasksController();

	$deleteTicket = $task->deleteTicket( $ticket_id );

	if ( $deleteTicket ) {

		thrive_api_message(
				array(
						'message' => 'success',
						'response' => array(
								'id' => $ticket_id
							),
						'stats' => $task->getTaskStatistics( $project_id )
					)
			);
	}

	return;
}

function thrive_transaction_fetch_task() {

	$task_id = (int) filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
	$page = (int) filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT );
	$project_id = (int) filter_input( INPUT_GET, 'project_id', FILTER_VALIDATE_INT );
	$priority = (int) filter_input( INPUT_GET, 'priority', FILTER_VALIDATE_INT );
	$search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_URL );
	$show_completed = filter_input( INPUT_GET, 'show_completed', FILTER_SANITIZE_STRING );
	$callback_template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_STRING );
	$html_template = 'thrive_render_task';
	$template = '';

	if ( ! empty( $callback_template ) && function_exists( $callback_template ) ) {
		$html_template = $callback_template;
	}

	$task = new ThriveProjectTasksController();

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

	if ( 0 === $task_id ) {

		$task_id = null;

		$template = $html_template($args);

	} else {

		if ( ! empty( $callback_template ) ) {

			$template = $html_template($task_collection);

		}
	}

	$stats = array();

	if ( array_key_exists( 'stats', $task_collection ) ) {

		$stats = $task_collection['stats'];

	}

	thrive_api_message(array(
		'message' => 'success',
		'task'    => $task_collection,
		'stats'   => $stats,
		'debug'   => $task_id,
		'html'    => $template,
	));

	return;

}

function thrive_transaction_edit_ticket() {

	$task_id = (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
	$title = filter_input( INPUT_POST, 'title', FILTER_UNSAFE_RAW );
	$description = filter_input( INPUT_POST, 'description', FILTER_UNSAFE_RAW );
	$priority = filter_input( INPUT_POST, 'priority', FILTER_UNSAFE_RAW );
	$user_id = filter_input( INPUT_POST, 'user_id', FILTER_VALIDATE_INT );
	$project_id = filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );
	$template = '';

	$task = new ThriveProjectTasksController();

	$args = array(
		'title' => $title,
		'id' => $task_id,
		'description' => $description,
		'priority' => $priority,
		'user_id' => $user_id,
		'project_id' => $project_id,
	);

	$json_response = array(
		'message' => 'success',
		'type' => 'valid',
		'debug' => $task_id,
		'html' => $template,
	);

	$json_response = array_merge( $json_response, $args );

	if ( $task->updateTicket( $task_id, $args ) ) {
		
		$json_response['message'] = 'success';
	
	} else {

		$json_response['type'] = 'required';

		if ( !empty( $title ) && !empty( $description ) ) {

			$json_response['type'] = 'no_changes';

		}

		$json_response['message'] = 'fail';

	}

	thrive_api_message( $json_response );

	return;
}

function thrive_transaction_complete_task() {

	$task_id = (int) filter_input( INPUT_POST, 'task_id', FILTER_VALIDATE_INT );
	$user_id = (int) filter_input( INPUT_POST, 'user_id', FILTER_VALIDATE_INT );

	$args = array(
			'message' => 'success',
			'task_id' => 0,
		);

	$task = new ThriveProjectTasksController();

	$task_id = $task->completeTask( $task_id, $user_id );

	if ( $task_id ) {
		$args['message'] = 'success';
		$args['task_id'] = $task_id;
	} else {
		$args['message'] = 'fail';
	}

	thrive_api_message( $args );

	return;
}

function thrive_transaction_renew_task () {

	$task_id = (int) filter_input( INPUT_POST, 'task_id', FILTER_VALIDATE_INT );

	$args = array(
			'message' => 'success',
			'task_id' => 0,
		);

	$task = new ThriveProjectTasksController();

	$task_id = $task->renewTask( $task_id );

	if ( $task_id ) {
		$args['message'] = 'success';
		$args['task_id'] = $task_id;
	} else {
		$args['message'] = 'fail';
	}

	thrive_api_message( $args );
}

function thrive_transaction_add_comment_to_ticket() {

	require_once plugin_dir_path( __FILE__ ) . '../models/comments.php';
	require_once plugin_dir_path( __FILE__ ) . '../models/tasks.php';

	$comment   = new ThriveComments();
	$task      = new ThriveProjectTasksModel();

	$details    = filter_input( INPUT_POST, 'details', FILTER_SANITIZE_STRING );
	$ticket_id  = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );
	$priority   = filter_input( INPUT_POST, 'priority', FILTER_VALIDATE_INT );
	$completed  = filter_input( INPUT_POST, 'completed', FILTER_SANITIZE_STRING );
	$project_id = filter_input( INPUT_POST, 'project_id', FILTER_SANITIZE_STRING );;

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
		thrive_api_message(array(
				'message' => 'fail',
			));
	}

	$new_comment = $comment->set_details( $details )
	        			   ->set_user( $user_id )
	        			   ->set_status( $status[$completed] )
	        			   ->set_ticket_id( $ticket_id )
	                       ->save();

	if ( $new_comment ) {

		$added_comment = $comment->fetch( $new_comment );

		thrive_api_message(array(
				'message' => 'success',
				'stats' => $task->getTaskStatistics( $project_id ),
				'result' => thrive_comments_template( $added_comment ),
			));
	}

	return;
}

function thrive_transaction_delete_comment() {

	require_once plugin_dir_path( __FILE__ ) . '../models/comments.php';

	$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_VALIDATE_INT ) );

	if ( 0 === $comment_id ) {
		thrive_api_message(array(
			'message'  => 'failure',
			'response' => 'Invalid Comment ID',
		));
	}

	// Proceed.
	$comment = new ThriveComments();

	// Delete the comment and handle the result
	if ( $comment->set_id( $comment_id )->set_user( get_current_user_id() )->delete() ) {
		thrive_api_message(array(
			'message'  => 'success',
		));
	} else {
		// Otherwise, tell the client to throw an error
		thrive_api_message(array(
			'message' => 'failure',
		));
	}

	return;
}

function thrive_transactions_update_project() {

	require_once plugin_dir_path( __FILE__ ) . '../models/project.php';

	$project = new ThriveProject();

	$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
	$project_title = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );
	$project_content = filter_input( INPUT_POST, 'content', FILTER_SANITIZE_STRING );
	$project_group_id = filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT );
	$no_json = filter_input( INPUT_POST, 'no_json', FILTER_SANITIZE_STRING );

	if ( ! empty( $project_id ) ) {
		$project->set_id( $project_id );
	}

	$project->set_title( $project_title );
	$project->set_content( $project_content );
	$project->set_group_id( $project_group_id );

	if ( $project->save() ) {

		if ( $no_json === 'yes' ) {

			wp_safe_redirect( get_permalink( $project->get_id() ) );

		}

		thrive_api_message( array(
				'message' => 'success',
				'project_id' => $project->get_id(),
			));
	} else {
		thrive_api_message( array(
				'message' => 'failure',
				'project_id' => 0,
			));
	}
}

function thrive_transactions_delete_project() {

	require_once plugin_dir_path( __FILE__ ) . '../models/project.php';

	$project = new ThriveProject();

	$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	$project->set_id( absint( $project_id ) );

	$redirect = home_url();

	if ( $project->delete() ) {

		// Get the projects page permalink
		$bp_options_pages = get_option( 'bp-pages' );

		if ( ! empty( $bp_options_pages ) && is_array( $bp_options_pages ) ) {

			$project_page_id = $bp_options_pages[ 'projects' ];

			if ( ! empty( $project_page_id ) ) {
				$redirect = get_permalink( $project_page_id );
			}
		}

		thrive_api_message( array(
				'message' => 'success',
				'redirect' => $redirect,
			));

	} else {

		thrive_api_message( array(
				'message' => 'failure',
			));
	}

	return;
}
?>
