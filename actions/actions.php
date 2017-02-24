<?php

final class TaskBreakerActions {

	/**
	 * Attach all WordPress action hooks to the following class methods listed in __construct.
	 *
	 * @return  void.
	 */
	public function __construct() {
		
		add_action( 'before_delete_post', array( $this, 'project_delete_garbage_collection') );

	}

	/**
	 * Deletes all the task attachments inside a specific project
	 * 
	 * @return void
	 */
	public function project_delete_garbage_collection( $project_id ) {
		
		if ( empty ( $project_id ) ) {
			return;
		}

		$set_upload_dir = false;

		$dbase = TaskBreaker::wpdb();

		$fs = new TaskBreakerFileAttachment( $set_upload_dir );
		
		$task_table = $dbase->prefix . 'task_breaker_tasks';

		$task_meta_table = $dbase->prefix . 'task_breaker_task_meta';

		$task_comments_table = $dbase->prefix . 'task_breaker_comments';

		$task_user_assignment_table = $dbase->prefix . 'task_breaker_tasks_user_assignment';
		
		$stmt = $dbase->prepare("SELECT * FROM {$task_table} WHERE project_id = %d", $project_id, OBJECT );

		$project_tasks = $dbase->get_results( $stmt );

		if ( ! empty ( $project_tasks ) ) {

			// Delete all the tasks under the project.
			if ( $dbase->delete( $task_table, array( 'project_id' => absint( $project_id ) ), array( '%d' ) ) ) {

				foreach ( $project_tasks as $task ) {

					// Delete all task attachments under the task inside a specific project.
					if ( $fs->delete_task_attachments( $task->id ) ) {

						// Delete attachments "meta" after succesfully removing all the tasks attachments in the directory.
						if ( FALSE !== $dbase->delete( $task_meta_table, array( 'task_id' => $task->id ), array( '%d' ) ) ) {

							// Delete all task comments as well.
							if ( FALSE !== $dbase->delete( $task_comments_table, array( 'ticket_id' => $task->id ), array( '%d' ) )  ) {

								// Delete all user assignments.
								if ( $dbase->delete( $task_user_assignment_table, array( 'task_id' => $task->id ), array( '%d' ) ) === FALSE  ) {
									
									TaskBreaker::stop('Unable to delete user assignments. There was an error in db query.');

								}

							} else {

								TaskBreaker::stop('Unable to delete task comments. There was an error in db query.');

							}

						// End task meta deletion.
						} else {

							TaskBreaker::stop('Unable to delete attachments meta. There was an error in db query.');

						}
					// End Delete all task attachments under the task inside a specific project.
					} else {

						TaskBreaker::stop('Unable to delete attachments. There was an error in db query.');

					}

				} // End foreach.

			// End Delete all the tasks under the project.
			} else {

				TaskBreaker::stop('Unable to delete tasks. There was an error in db query. ');

			}
		} // End not empty.

		return;

	} // End method.
}

new TaskBreakerActions();