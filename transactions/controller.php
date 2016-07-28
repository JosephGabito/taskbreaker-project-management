<?php
/**
 * This file acts as an api for our admin-ajax requests.
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

if ( 'task_breaker_transactions_request' !== $action ) {
	return;
}


// Format our page header when Unit Testing
if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {

	header('Content-Type: application/json; charset=utf-8');

} else {

	// Hide warnings when running tests
	@header('Content-type:application/json; charset=utf-8');
	
}

add_action( 'wp_ajax_task_breaker_transactions_request', 'task_breaker_transactions_callblack' );

require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

/**
 * Executes the method or function requested by the client
 * @return void
 */
function task_breaker_transactions_callblack() {

	// Always check for nonce before proceeding...
	$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

	// If INPUT_GET is empty try input post
	if ( empty( $nonce ) ) {

		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

	}

	if ( ! wp_verify_nonce( $nonce, 'task_breaker-transaction-request' ) )
	{

		die(
			__( 'Invalid Request. Your session has already expired (invalid nonce).
				Please go back and refresh your browser. Thanks!', 'task_breaker' )
		);

	}

	$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_ENCODED );

	if ( empty( $method ) ) {
		// try get action
		$method = filter_input( INPUT_GET, 'method', FILTER_SANITIZE_ENCODED );
	}

	$allowed_callbacks = array(

		// Tickets/Tasks callbacks
		'task_breaker_transaction_add_ticket',
		'task_breaker_transaction_delete_ticket',
		'task_breaker_transaction_fetch_task',
		'task_breaker_transaction_edit_ticket',
		'task_breaker_transaction_complete_task',
		'task_breaker_transaction_renew_task',

		// Comments callback functions.
		'task_breaker_transaction_add_comment_to_ticket',
		'task_breaker_transaction_delete_comment',

		// Project callback functions.
		'task_breaker_transactions_update_project',
		'task_breaker_transactions_delete_project',

		// Task autosuggest
		'task_breaker_transactions_user_suggest'
	);

	if ( function_exists( $method ) ) {
		if ( in_array( $method, $allowed_callbacks ) ) {
			// execute the callback
			$method();
		} else {
			task_breaker_api_message(array(
				'message' => 'method is not listed in the callback',
			));
		}
	} else {
		task_breaker_api_message(array(
			'message' => 'method not allowed or method does not exists',
		));
	}

	task_breaker_api_message(array(
			'message' => 'transaction callback executed',
		));
}

function task_breaker_api_message($args = array()) {
	// Added @ to server php 7
	@header("Content-type: application/json");
	echo json_encode($args);
	die();
}

function task_breaker_transaction_add_ticket() {

	$task = new ThriveProjectTasksController();

	$task_id = $task->addTicket( $_POST );

	if ( ! task_breaker_can_add_task( (int) $_POST['project_id'] ) ) {
		task_breaker_api_message( array(
			'message' => 'fail',
			'response' => __('Unable to add tasks. Only a group administrator or a group moderator can add tasks.',' task_breaker'),
		) );
	}

	if ( $task_id ) {

		task_breaker_api_message( array(
			'message' => 'success',
			'response' => array(
					'id' => $task_id,
				),
			'stats' => $task->getTaskStatistics( (int) $_POST['project_id'] )
		));

	} else {
		task_breaker_api_message( array(
			'message' => 'fail',
			'response' => __('There was an error trying to add this task.
				Title and Description fields are required or there was
				an unexpected error.',' task_breaker'),
		) );
	}

	return;
}

function task_breaker_transaction_delete_ticket() {

	$ticket_id = (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	$project_id = (int) filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );;

	$task = new ThriveProjectTasksController();

	$deleteTicket = $task->deleteTicket( $ticket_id );

	if ( $deleteTicket ) {

		task_breaker_api_message(
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

function task_breaker_transaction_fetch_task() {

	$task_id = (int) filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
	$page = (int) filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT );
	$project_id = (int) filter_input( INPUT_GET, 'project_id', FILTER_VALIDATE_INT );
	$priority = (int) filter_input( INPUT_GET, 'priority', FILTER_VALIDATE_INT );
	$search = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_URL );
	$show_completed = filter_input( INPUT_GET, 'show_completed', FILTER_SANITIZE_STRING );
	$callback_template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_STRING );
	$html_template = 'task_breaker_render_task';
	$template = '';

	if ( ! empty( $callback_template ) && function_exists( $callback_template ) ) {
		$html_template = $callback_template;
	}


	if ( ! task_breaker_can_see_project_tasks( $project_id ) ) {

		task_breaker_api_message(array(
			'message' => 'fail',
			'message_long' => __('Unable to access the task details. Only group members can access this page', 'task-breaker'),
			'task'    => array(),
			'stats'   => array(),
			'debug'   => __("Unauthorized Access", "task-breaker"),
			'html'    => "",
		));

		return;

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

	// Push the ticket ID in the task_collection stack.
	$task_collection->task_id = absint( $task_id );

	if ( 0 === $task_id ) {

		$task_id = null;

		$template = $html_template( $args );

	} else {

		if ( ! empty( $callback_template ) ) {

			$template = $html_template( $task_collection );

		}
	}

	$stats = array();

	if ( array_key_exists( 'stats', $task_collection ) ) {

		$stats = $task_collection['stats'];

	}

	task_breaker_api_message(array(
		'message' => 'success',
		'task'    => $task_collection,
		'stats'   => $stats,
		'debug'   => $task_id,
		'html'    => $template,
	));

	return;

}

function task_breaker_transaction_edit_ticket() {

	$task_id = (int) filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
	$title = filter_input( INPUT_POST, 'title', FILTER_UNSAFE_RAW );
	$description = filter_input( INPUT_POST, 'description', FILTER_UNSAFE_RAW );
	$priority = filter_input( INPUT_POST, 'priority', FILTER_UNSAFE_RAW );
	$user_id = filter_input( INPUT_POST, 'user_id', FILTER_VALIDATE_INT );
	$project_id = filter_input( INPUT_POST, 'project_id', FILTER_VALIDATE_INT );
	$assigned_users = filter_input( INPUT_POST, 'user_id_collection', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	$template = '';

	$task = new ThriveProjectTasksController();

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

	$json_response = array_merge( $json_response, $args );

	if ( $task->updateTicket( $task_id, $args ) ) {

		$json_response['message'] = 'success';

	} else {

		$json_response['type'] = 'required';

		if ( ! empty( $title ) && ! empty( $description ) ) {

			$json_response['type'] = 'no_changes';

		}

		$json_response['message'] = 'fail';

	}

	task_breaker_api_message( $json_response );

	return;
}

function task_breaker_transaction_complete_task() {

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

	task_breaker_api_message( $args );

	return;
}

function task_breaker_transaction_renew_task () {

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

	task_breaker_api_message( $args );
}

function task_breaker_transaction_add_comment_to_ticket() {

	require_once plugin_dir_path( __FILE__ ) . '../models/comments.php';
	require_once plugin_dir_path( __FILE__ ) . '../models/tasks.php';

	$comment   = new ThriveComments();
	$task      = new ThriveProjectTasksModel();

	$details    = filter_input( INPUT_POST, 'details', FILTER_SANITIZE_STRING );
	$ticket_id  = filter_input( INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT );
	$priority   = filter_input( INPUT_POST, 'priority', FILTER_VALIDATE_INT );
	$completed  = filter_input( INPUT_POST, 'completed', FILTER_SANITIZE_STRING );
	$project_id = filter_input( INPUT_POST, 'project_id', FILTER_SANITIZE_STRING );;

	// Check if current user can add comment
	if ( ! task_breaker_can_add_task_comment( $project_id, $ticket_id ) ) {

		task_breaker_api_message(array(
			'message' => 'fail',
			'stats' => $task->getTaskStatistics( $project_id, $ticket_id ),
			'result' => task_breaker_comments_template( $added_comment ),
		));

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
		task_breaker_api_message(array(
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

		task_breaker_api_message(array(
				'message' => 'success',
				'stats' => $task->getTaskStatistics( $project_id, $ticket_id ),
				'result' => task_breaker_comments_template( $added_comment ),
			));
	}

	return;
}

function task_breaker_transaction_delete_comment() {

	require_once plugin_dir_path( __FILE__ ) . '../models/comments.php';

	$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_VALIDATE_INT ) );

	if ( 0 === $comment_id ) {
		task_breaker_api_message(array(
			'message'  => 'failure',
			'response' => 'Invalid Comment ID',
		));
	}

	// Proceed.
	$comment = new ThriveComments();

	// Delete the comment and handle the result
	if ( $comment->set_id( $comment_id )->set_user( get_current_user_id() )->delete() ) {
		task_breaker_api_message(array(
			'message'  => 'success',
		));
	} else {
		// Otherwise, tell the client to throw an error
		task_breaker_api_message(array(
			'message' => 'failure',
		));
	}

	return;
}

function task_breaker_transactions_update_project() {

	require_once plugin_dir_path( __FILE__ ) . '../models/project.php';

	$project = new ThriveProject();

	// The project id. Leave blank if creating new project.
	$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	// The title of the project.
	$project_title = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );

	// The content of the project.
	$project_content = filter_input( INPUT_POST, 'content', FILTER_SANITIZE_STRING );

	// The group id of the buddypress group where this project is under.
	$project_group_id = filter_input( INPUT_POST, 'group_id', FILTER_VALIDATE_INT );

	// The callback format.
	$no_json = filter_input( INPUT_POST, 'no_json', FILTER_SANITIZE_STRING );

	// Check if current user can add project to group
	if ( ! task_breaker_can_add_project_to_group( $project_group_id ) ) {
		task_breaker_api_message( array(
				'message' => 'failure',
				'project_id' => 0,
				'type' => 'authentication_error'
			));
	}

	if ( ! empty( $project_id ) ) {
		$project->set_id( $project_id );
	}

	// Only users who can edit project can access this transaction
	if ( ! empty( $project_id ) ) {
		if ( ! task_breaker_can_edit_project( $project_id ) ) {
			task_breaker_api_message( array(
					'message' => 'failure',
					'project_id' => 0,
					'type' => 'authentication_error'
				));
		}
	}

	$project->set_title( $project_title );
	$project->set_content( $project_content );
	$project->set_group_id( $project_group_id );

	if ( $project->save() ) {

		if ( $no_json === 'yes' ) {

			wp_safe_redirect( get_permalink( $project->get_id() ) );

		}

		task_breaker_api_message( array(
				'message' => 'success',
				'project_id' => $project->get_id(),
				'project_permalink' => get_permalink( $project->get_id() )
			));

	} else {

		task_breaker_api_message( array(
				'message' => 'failure',
				'project_id' => 0,
				'type' => 'error_generic',
			));
	}
}

function task_breaker_transactions_delete_project() {

	require_once plugin_dir_path( __FILE__ ) . '../models/project.php';

	$project = new ThriveProject();

	$project_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

	$project->set_id( absint( $project_id ) );

	$redirect = home_url();

	if ( ! task_breaker_can_delete_project( $project_id ) ) {

		task_breaker_api_message( array(
				'message' => 'fail',
				'response' => __("Permission Denied. Unauthorized.")
			));

		return;
	}

	if ( $project->delete() ) {

		// Get the projects page permalink
		$bp_options_pages = get_option( 'bp-pages' );

		if ( ! empty( $bp_options_pages ) && is_array( $bp_options_pages ) ) {

			$project_page_id = $bp_options_pages[ 'projects' ];

			if ( ! empty( $project_page_id ) ) {
				$redirect = get_permalink( $project_page_id );
			}
		}

		task_breaker_api_message( array(
				'message' => 'success',
				'redirect' => $redirect,
			));

	} else {

		task_breaker_api_message( array(
				'message' => 'failure',
			));
	}

	return;
}

/**
 * task_breaker_transactions_user_suggest
 *
 * This function returns the list of user inside the group ($group_id)
 * and filters it by name (like)
 *
 * @return void
 */
function task_breaker_transactions_user_suggest() {

	global $wpdb;

	$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );
	$group_id = filter_input( INPUT_GET, 'group_id', FILTER_SANITIZE_NUMBER_INT );
	$prefix = $wpdb->prefix;

	$stmt = $wpdb->prepare("SELECT {$prefix}bp_groups_members.user_id as id, {$prefix}users.display_name as text FROM {$prefix}bp_groups_members INNER JOIN {$prefix}users
	ON {$prefix}bp_groups_members.user_id = {$prefix}users.ID
	WHERE {$prefix}bp_groups_members.group_id = %d AND {$prefix}users.display_name LIKE %s ORDER BY {$prefix}users.display_name ASC LIMIT 10",
	$group_id, "%".$wpdb->esc_like( $term )."%");

	$results = $wpdb->get_results( $stmt, ARRAY_A );

	$formatted_results = array();

	if ( ! empty( $results ) ) {

		foreach( $results as $result ) {

		    if ( ! empty ( $result ['text'] ) ) {

		        $image_tag = get_avatar( absint( $result['id'] ) );

				preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $image_tag, $image_src);

		        $formatted_results[] = array(
		            'id' => $result['id'],
		            'text' => $result['text'],
		            'avatar' => $image_src[1]
		        );

		    }

		}

	}

	task_breaker_api_message(
		array(
			'results' => $formatted_results,
		)
	);

	return;
}
?>
