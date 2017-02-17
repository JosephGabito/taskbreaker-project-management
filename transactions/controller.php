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

$taskbreaker_transactions = new TaskBreakerTransactions();

/**
 * Holds the methods for our Transactions. This class acts as our middleware between each post and request.
 *
 * @package TasksBreaker\TaskBreakerTransactions
 */
class TaskBreakerTransactions {

	/**
	 * Class constructor method.
	 *
	 * @return  void
	 */
	public function __construct() {

		add_action( 'wp_ajax_task_breaker_transactions_request', array( $this, 'transactions_callblack' ) );

		return;

	}

	/**
	 * Validates the http request.
	 *
	 * @return void
	 */
	public function prepare_request() {

		header( 'Content-type:application/json; charset=utf-8' );

		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

		// Try getting post request if $action is empty when getting request via 'get' method.
		if ( empty( $action ) ) {
			$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		}

		// Bail out if action request is not 'task_breaker_transactions_request'.
		if ( 'task_breaker_transactions_request' !== $action ) {
			return;
		}

		return;
	}

	/**
	 * Executes the method or function requested by the client
	 *
	 * @return void
	 */
	public function transactions_callblack() {

		$this->prepare_request();

		if ( TASK_BREAKER_PROFILER ) {
			if ( function_exists( 'getrusage' ) ) {
				$this->rustart = getrusage();
			}
		}

		require_once TASKBREAKER_DIRECTORY_PATH . 'controllers/tasks.php';

		// Always check for nonce before proceeding...
		$nonce = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

		// If INPUT_GET is empty try input post.
		if ( empty( $nonce ) ) {

			$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

		}

		if ( ! wp_verify_nonce( $nonce, 'task_breaker-transaction-request' ) ) {

			esc_html_e( 'Invalid Request. Your session has already expired (invalid nonce). Please go back and refresh your browser. Thanks!', 'task_breaker' );
			return;

		}

		$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_ENCODED );

		if ( empty( $method ) ) {

			// Try to get the action.
			$method = filter_input( INPUT_GET, 'method', FILTER_SANITIZE_ENCODED );
		}

		$allowed_callbacks = apply_filters( 'taskbreaker_allowed_callbacks', array(

			// Tickets/Tasks callbacks.
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

			// Task autosuggest.
			'task_breaker_transactions_user_suggest',

			// Task file attachment.
			'task_breaker_transaction_task_file_attachment',
			'task_breaker_transaction_delete_ticket_attachment'
		));

		// Check if the method is allowed.
		if ( in_array( $method, $allowed_callbacks, true ) ) {
			
			$needles = array(
					'task_breaker_transaction_',
					'task_breaker_transactions_',
				);
			
			// Load the method.
			$method = str_replace( '_', '-', str_replace( $needles, '', $method ) );

			$method_module = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'routes/' . sanitize_file_name( $method ) . '.php';

			if ( file_exists( $method_module ) ) {
				do_action( 'taskbreaker_before_route_call', $method );
				require_once $method_module;
			} else {
				$this->task_breaker_api_message
				(
					array(
						'message' => sprintf( __( 'Cannot find the route: %s', 'task_breaker' ), $method ),
					)
				);
			}
		} else {
			$this->task_breaker_api_message(
				array(
					'message' => 'method is not listed in the callback',
				)
			);
		}

		$this->task_breaker_api_message(
			array(
			'message' => 'transaction callback executed',
			)
		);

	}

	/**
	 * Returns the message to the client via wp_json_encode
	 *
	 * @param  array $args The parameters you wish to pass on the http message.
	 * @return void
	 */
	public function task_breaker_api_message( $args = array() ) {

		if ( TASK_BREAKER_PROFILER ) {
			if ( function_exists( 'getrusage' ) ) {
				$ru = getrusage();
				$args['profiler'] = array(
					'process_used_ms' => $this->task_breaker_profiler($ru, $this->rustart, "utime") . 'ms',
					'system_calls_ms' => $this->task_breaker_profiler($ru, $this->rustart, "stime") . 'ms',
					'queries' => get_num_queries()
				);
			}
		}

		echo wp_json_encode( $args );

		wp_die();

		return;

	}

	public function task_breaker_profiler( $ru, $rus, $index ) {
		return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000)) -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
	}
}
