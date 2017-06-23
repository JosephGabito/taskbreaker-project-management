<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph G. <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerCore\FileAttachments
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * TaskBrekerFileAttachment contains several useful methods for attaching,
 * deleting, and updating task file attachments.
 */
class TaskBreakerFileAttachment {

	/**
	 * Do we alter the upload dir path?
	 *
	 * @var boolean
	 */
	public $set_upload_dir = true;

	/**
	 * Our class constructor;
	 *
	 * @param  boolean $set_upload_dir Set true to alter 'upload_dir' hook.
	 * @return void.
	 */
	public function __construct( $set_upload_dir = true ) {

		$this->set_upload_dir = $set_upload_dir;

		if ( $this->set_upload_dir ) {
			add_filter( 'upload_dir', array( $this, 'set_upload_dir' ) );
		}

	}

	/**
	 * Sets the upload directory to our custom file path.
	 *
	 * @param mixed $dirs The collection of directory properties.
	 * @return  array The directory.
	 */
	public function set_upload_dir( $dirs ) {

		$user_id = get_current_user_id();

		$upload_dir = apply_filters( 'taskbreaker_task_file_attachment_upload_dir',
		sprintf( 'taskbreaker/%d/tmp', $user_id ) );

	    $dirs['subdir'] = $upload_dir;
	    $dirs['path'] = trailingslashit( $dirs['basedir'] ) . $upload_dir;
	    $dirs['url'] = trailingslashit( $dirs['baseurl'] ) . $upload_dir;

	    return $dirs;

	}

	/**
	 * Catch any file attachments and let wp_handle_upload do the uploading.
	 *
	 * @return array The http message.
	 */
	public function process_http_file() {

		if ( ! is_user_logged_in() ) {
			return array( 'error' => __( 'Authentication issues. Terminating...', 'task_breaker' ) );
		}

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		}

		$fs = new WP_Filesystem_Direct( array() );

		$file = '';

		if ( isset( $_FILES[0] ) && ! empty( $_FILES[0]['name'] ) ) {
			$file = wp_unslash( $_FILES[0] );
		}

		if ( empty( $file ) ) {
			return array( 'error' => __( 'Did not received any http file. Terminating...', 'task_breaker' ) );
		}

		if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
			return array( 'error' => __( 'An error occured. Please check maximum size and maximum header size.', 'task_breaker' ) );
		}

		$upload_overwrites = array( 'test_form' => false );

		// First delete everything in tmp directory.
		$path = wp_upload_dir();

		$tmp_dir = $path['basedir'] . sprintf( '/taskbreaker/%d/tmp', get_current_user_id() );

		if ( $fs->delete( $tmp_dir, true ) ) {

			// Re-create the directory.
			if ( $fs->mkdir( $tmp_dir ) ) {
				// Then, move the file.
				return wp_handle_upload( $file, $upload_overwrites );

			} else {

				return array( 'error' => __( 'Unable to create temporary directory. Permission error.', 'task_breaker' ) );
			}

		} else {
			return array( 'error' => __( 'Unable to clear temporary directory. Permission error.', 'task_breaker' ) );
		}

		return array( 'error' => __( 'Unable to handle file upload.', 'task_breaker' ) );

	}

	/**
	 * Attach an existing file inside the temporary directory (tmp) to taskbreaker path.
	 *
	 * @param  string  $name    The filename.
	 * @param  integer $task_id The id of the task.
	 * @return boolean          True on success. Otherwise, false.
	 */
	public function task_attach_file( $name = '', $task_id = 0 ) {

		if ( empty( $name ) ) {
			return false;
		}

		if ( empty( $task_id ) ) {
			return false;
		}

		$dbase = TaskBreaker::wpdb();

		$stmt = $dbase->prepare( "SELECT * FROM {$dbase->prefix}task_breaker_task_meta
			WHERE task_id = %d", $task_id );

		$files_attached = $dbase->get_row( $stmt, OBJECT );

		if ( ! empty( $files_attached ) ) {

			$data = array( 'meta_value' => $name );
			$format = array( '%s' );
			$where = array( 'task_id' => $task_id );
			$where_format = array( '%d' );

			$dbase->update( "{$dbase->prefix}task_breaker_task_meta", $data, $where, $format, $where_format );

		} else {

			$data = array( 'task_id' => $task_id,'meta_key' => 'file_attachment', 'meta_value' => $name );
			$format = array( '%s', '%s' );
			$dbase->insert( "{$dbase->prefix}task_breaker_task_meta", $data, $format );

		}

		return $this->transport_file( $name, $task_id );

	}

	/**
	 * Delete the file under a specific task.
	 *
	 * @param  integer $task_id   The id of the task.
	 * @param  string  $file_name The file name of the attached file.
	 * @return boolean             True on success. Otherwise, false.
	 */
	public function delete_file( $task_id = 0, $file_name = '' ) {

		if ( empty( $task_id ) ) {
			return false;
		}

		if ( empty( $file_name ) ) {
			return false;
		}

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		}

		$fs = new WP_Filesystem_Direct( $args );

		if ( empty( $task_id ) ) {
			return false;
		}
		if ( empty( $file_name ) ) {
			return false;
		}

		// Start deleting the file.
		$fs->delete( $this->get_current_user_file_path( $task_id, $file_name ) );

	}

	/**
	 * Delete all files under a specific task.
	 *
	 * @param  integer $task_id The task ID.
	 * @return boolean           True on success. Otherwise, false.
	 */
	public function delete_task_attachments( $task_id = 0 ) {

		if ( empty( $task_id ) ) {
			return false;
		}

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		}

		$fs = new WP_Filesystem_Direct( array() );

		$dbase = TaskBreaker::wpdb();

		$path = wp_upload_dir();

		$task_dir = $path['basedir'] . sprintf( '/taskbreaker/%d/tasks/%d', get_current_user_id(), $task_id );

		if ( $fs->delete( $task_dir, true ) ) {
			$dbase->delete( "{$dbase->prefix}task_breaker_task_meta", array( 'task_id' => absint( $task_id ) ), array( '%d' ) );
			return true;
		}

		return false;

	}

	/**
	 * Returns the current logged-in user file path.
	 *
	 * @param  string $task_id The task ID.
	 * @param  string $name    The task file name.
	 * @return boolean          The current directory path of the current logged-in user.
	 */
	public function get_current_user_file_path( $task_id = '', $name = '' ) {

		if ( ! empty( $task_id ) ) {
			return false;
		}
		if ( ! empty( $name ) ) {
			return false;
		}

		$path = wp_upload_dir();

		$upload_dir = $path['basedir'] . '/taskbreaker/';

		$file = sprintf( '%1$s/%2$d/tasks/%3$d/%4$d', $upload_dir, absint( get_current_user_id() ), absint( $task_id ), sanitize_file_name( $name ) );

		return $file;

	}

	/**
	 * Transport file method 'moves' the file located inside 'tmp' directory to the task directory.
	 *
	 * @param  string  $file_name The filename of the task.
	 * @param  integer $task_id   The task ID.
	 * @return boolean             True on success. Otherwise, false.
	 */
	protected function transport_file( $file_name = '', $task_id = 0 ) {

		if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		    require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
		}

		$args = array();

		$fs = new WP_Filesystem_Direct( $args );

		$path = wp_upload_dir();
		$tmp_directory = $path['basedir'] . '/taskbreaker/' . get_current_user_id() . '/tmp/' . $file_name;
		$destination_directory = $path['basedir'] . '/taskbreaker/' . get_current_user_id() . '/tasks/' . $task_id . '/';
		$final_destination = $destination_directory . $file_name;

		if ( wp_mkdir_p( $destination_directory ) ) {
			if ( ! $fs->move( $tmp_directory, $final_destination ) ) {
				return false;
			}
		} else {
			return false;
		}

	}

	/**
	 * Get all attached files under a specific task.
	 *
	 * @param  integer $task_id The task ID.
	 * @param  integer $user_id The owner of the task. Pass the user id.
	 * @return array           The recorded files inside a specific task.
	 */
	public static function task_get_attached_files( $task_id = 0, $user_id = 0 ) {

		if ( empty( $task_id ) ) {
			return array();
		}

		if ( empty( $user_id ) ) {
			return array();
		}

		$dbase = TaskBreaker::wpdb();

		$stmt = $dbase->prepare( "SELECT * FROM {$dbase->prefix}task_breaker_task_meta
			WHERE task_id = %d", $task_id );

		$results = $dbase->get_results( $stmt, OBJECT );

		$files = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$files[] = array(
					'name' => $result->meta_value,
					'url' => esc_url( content_url( 'uploads/taskbreaker/' . $user_id . '/tasks/' .
					$task_id . '/' . $result->meta_value ) ),
				);
			}
		}

		return $files;

	}

	/**
	 * Our class destruct mechanism. Since we set the directory path on object creation. We need to revert it back
	 * to default WordPress directory path to prevent any bugs.
	 *
	 * @return void.
	 */
	public function __destruct() {
		if ( $this->set_upload_dir ) {
			remove_filter( 'upload_dir', array( $this, 'set_upload_dir' ) );
		}
	}
}
