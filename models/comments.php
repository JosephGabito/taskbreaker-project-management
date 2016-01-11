<?php
/**
 * Thrive Comments
 *
 * @package  ThriveIntranet
 */

/**
 * Thrive Comments Object
 *
 * The structure of our comments object
 */
class ThriveComments {

	/**
	 * Holds the commend id
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * Holds the comment details
	 * @var string
	 */
	protected $details = '';

	/**
	 * Holds the comment user id
	 * @var integer
	 */
	protected $user = 0;

	/**
	 * Holds the "date_added"
	 * @var string
	 */
	protected $date_added = '';

	/**
	 * Holds the ticket id where the comment is belong
	 * @var integer
	 */
	protected $ticket_id = 0;

	/**
	 * Holds the status of the comment
	 * 0 for 'In Progress', 1 for 'Complete', 2 'reOpen'
	 * @var integer
	 */
	protected $status = 0;

	/**
	 * Reference to custom table use for comments
	 * @var string
	 */
	private $model = '';

	// Set allowed status.
	// 0 for 'In Progress'
	// 1 for 'Completed'
	// 2 for 'ReOpen'
	private $allowed_status = array( 0, 1, 2 );

	/**
	 * Prepare the object properties before using
	 *
	 * @return  object self
	 */
	public function __construct() {
		global $wpdb;
		$this->model = $wpdb->prefix . 'thrive_comments';
		$this->date_added = date( 'Y-m-d g:i:s' );

	}

	/**
	 * Set the ID of the comment
	 * @param integer $id The id of the comment.
	 * @throws Exception ID must not be empty.
	 */
	public function set_id($id = 0) {

		$id = absint( $id );

		if ( 0 === $id ) {
			throw new Exception( 'Model/Comments/:ID must not be empty' );
		}

		$this->id = absint( $id );

		return $this;
	}

	/**
	 * Set the comment details
	 * @param string $details The details of the comments.
	 * @throws Exception Details must not be empty.
	 */
	public function set_details($details = '') {

		if ( empty( $details ) ) {
			throw new Exception( 'Model/Comments/:details must not be empty' );
		}

		$this->details = $details;

		return $this;
	}

	/**
	 * Assign a user the the comment
	 *
	 * @param integer $user_id the user's ID.
	 * @throws Exception The current user must be logged in.
	 * @throws Exception The current user must exists in wp_users table.
	 * @throws Exception The $user_id must not be empty.
	 */
	public function set_user($user_id = 0) {

		$user_id = absint( $user_id );

		if ( 0 === $user_id ) {
			throw new Exception( "Model/Comments/::user_id must not be equal to 0 'zero'" );
		}

		if ( ! is_user_logged_in() ) {
			throw new Exception( 'Model/Comments/setUser - User must be logged-in to proceed' );
		}

		if ( ! get_userdata( $user_id ) ) {
			throw new Exception( 'Model/Comments/setUser - User is not found' );
		}

		$this->user = $user_id;

		return $this;
	}

	/**
	 * Assign the comment to a ticket.
	 *
	 * @param integer $ticket_id The ID of the ticket.
	 * @throws Exception The $ticket_id must not be empty.
	 */
	public function set_ticket_id($ticket_id = 0) {

		$ticket_id = absint( $ticket_id );

		if ( 0 === $ticket_id ) {
			throw new Exception( 'Models/Comments/::ticket_id must not be empty' );
		}

		$this->ticket_id = $ticket_id;

		return $this;

	}

	/**
	 * Save the ticket comment
	 * @return boolean true if insertion of record succeed, otherwise false
	 */
	public function save() {

		if ( empty( $this->details ) ) { return false; }
		if ( empty( $this->user ) ) { return false; }
		if ( empty( $this->ticket_id ) ) { return false; }

		global $wpdb;

		$table = $this->model;

		$data = array(
				'details' => $this->details,
				'user' => $this->user,
				'ticket_id' => $this->ticket_id,
				'status' => $this->get_status(),
			);

		$formats = array(
				'%s', // The format for details.,
				'%d', // The format for user.
				'%d', // The format for ticket_id.
				'%d', // The format for status.
			);

		$insert_comments = $wpdb->insert( $table, $data, $formats ); // Db call ok.

		if ( $insert_comments ) {

			$last_insert_id = $wpdb->insert_id;

			// Add new activity. Check if buddypress is active first
			if ( function_exists( 'bp_activity_add' ) ) {

				$bp_user_link = '';

			 	if ( function_exists( 'bp_core_get_userlink' ) ) {
			 		$bp_user_link = bp_core_get_userlink( $this->user );
			 	}

			 	$status_label = array(
			 			__( 'posted new updated in', 'thrive' ),
			 			__( 'completed', 'thrive' ),
			 			__( 'reopened', 'theive' ),
			 		);

			 	$type = $status_label[$this->get_status()];

			 	$action = sprintf( __( '%s %s the task: %s - ', 'thrive' ), $bp_user_link, $type, '#' . $this->ticket_id );

			 	bp_activity_add(
			 		array(
						'user_id' => $this->user,
						'action' => apply_filters( 'thrive_update_task_activity_action', $action, $this->user ),
						'component' => 'project',
						'content' => $this->details,
						'type' => sanitize_title( 'thrive-'.$type ),
					)
				);
			} // End function_exists ( 'bp_activity_add' ).

			return $last_insert_id;

		} else {
			return false;
		}

		return false;
	}

	public function set_status( $status = 0 ) {

		$this->status = 0;

		if ( $this->validate_status( $status ) ) {
			$this->status = $status;
		}

		return $this;
	}

	public function validate_status( $status ) {

		if ( in_array( $status, $this->allowed_status ) ) {
			return true;
		}

		return false;
	}

	public function get_status() {

		if ( in_array( $this->status, $this->allowed_status ) ) {
			return $this->status;
		}

		return false;

	}

	/**
	 * Fetches the comment
	 * @param  integer $comment_id The id of the comment.
	 * @param  integer $task_id    The id of the task.
	 * @return array               Returns single result if ID is present,
	 *                             otherwise return all comments under a
	 *                             specific task.
	 */
	public function fetch($comment_id = 0, $task_id = 0) {

		global $wpdb;

		// Make sure $comment_id and $task_id are integer and non-negative value.
		$comment_id = absint( $comment_id );
		$task_id = absint( $task_id );

		$results = array();

		if ( $comment_id === 0 ) {
			$stmt = sprintf( "SELECT * FROM $this->model WHERE task_id = %d ORDER BY dated_added DESC;", $task_id );
			$results = $wpdb->get_results( $stmt, 'ARRAY_A' );
		}

		if ( $comment_id !== 0 ) {
			$stmt = sprintf( "SELECT * FROM $this->model WHERE id = %d;", $comment_id );
			$results = $wpdb->get_row( $stmt, 'ARRAY_A' );
		}

		if ( ! empty( $results ) ) {
			return $results;
		}

		// No conditions met? Return empty array.
		return array();
	}

	/**
	 * Removes the comment from the table
	 *
	 * @return boolean Returns true if removing of comment is successful, otherwise, false
	 */
	public function delete() {

		global $wpdb;

		if ( empty( $this->id ) ) {
			return false;
		}

		// Check if current user can delete the requested comment.
		if ( $this->current_user_can_delete() ) {

			$_delete_comment = $wpdb->delete( $this->model, array( 'id' => $this->id ), array( '%d' ) );

		 	return $_delete_comment;

		} else {

			return false;

		}

		return false;
	}

	/**
	 * Test if current logged-in user can delete the comment
	 *
	 * Conditions
	 *
	 * is_admin ? true
	 * current_user_id === comment->user ? true
	 *
	 * @return boolean
	 */
	public function current_user_can_delete() {

		global $wpdb;

		$comment_id = absint( $this->id );

		// If comment id is empty return false.
		if ( 0 === $comment_id ) {
			return false;
		}

		// Allow admins to delete the comment.
		if ( current_user_can( 'administrator' ) ) {
			return true;
		}

		// Only allow the same user to delete his own comment.
		$current_user_id = get_current_user_id();
		$comment_user = $wpdb->get_var( "SELECT user FROM $this->model WHERE id = $comment_id" ); // Db call ok; no-cache pass.
		$comment_user = absint( $comment_user );

		if ( ! empty( $comment_user ) ) {
			if ( $current_user_id === $comment_user ) {
				return true;
			}
		} else {
			return false;
		}

		// No conditions met? return false.
		return false;
	}
}
?>
