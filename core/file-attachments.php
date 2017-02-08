<?php
class TaskBreakerFileAttachment {

	public function __construct() {
		add_filter( 'upload_dir', array( $this, 'set_upload_dir') );
	}

	public function set_upload_dir( $dirs ) {

		$user_id = get_current_user_id();
		$upload_dir = apply_filters('taskbreaker_task_file_attachment_upload_dir', sprintf( 'taskbreaker/%d/tmp', $user_id ) );

	    $dirs['subdir'] = $upload_dir;
	    $dirs['path'] = trailingslashit( $dirs['basedir'] ) . $upload_dir;
	    $dirs['url'] = trailingslashit( $dirs['baseurl'] ) . $upload_dir;

	    return $dirs;

	}

	public function process_http_file() {

		$uploaded_file = $_FILES[0];

		$upload_overwrites = array( 'test_form' => false );

		$movefile = wp_handle_upload( $uploaded_file, $upload_overwrites);

		return $movefile;

	}

	public function task_attach_file( $name, $task_id ) {

		$dbase = TaskBreaker::wpdb();

		$data = array( 'task_id' => $task_id,'meta_key'=>'file_attachment', 'meta_value'=> $name );

		$format = array('%s', '%s');

		$dbase->insert( "{$dbase->prefix}task_breaker_task_meta", $data, $format );

		$this->transport_file( $name, $task_id );

		return true;

	}

	protected function transport_file( $file_name, $task_id ) {

		$path = wp_upload_dir();
		$tmp_directory = $path['basedir'] . '/taskbreaker/'. get_current_user_id() .'/tmp/' . $file_name;
		$destination_directory = $path['basedir'] . '/taskbreaker/'. get_current_user_id() . '/tasks/' . $task_id . '/';
		$final_destination = $destination_directory . $file_name;

		if ( wp_mkdir_p( $destination_directory ) ) {
			if ( ! rename( $tmp_directory, $final_destination ) ) {
			    echo "failed to copy $file...\n";
			}
		}
		
	}

	public static function task_get_attached_files( $task_id = 0, $user_id = 0 ) {
		
		$dbase = TaskBreaker::wpdb();

		$stmt = $dbase->prepare( "SELECT * FROM {$dbase->prefix}task_breaker_task_meta WHERE task_id = %d", $task_id );

		$results = $dbase->get_results( $stmt, OBJECT );

		$files = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$files[] = array(
					'name' => $result->meta_value,
					'url' => esc_url( content_url( 'uploads/taskbreaker/'. $user_id . '/tasks/' . $task_id . '/' . $result->meta_value ) )
				);

			}
		}

		return $files;

	}

	public function __destruct() {
		remove_filter( 'upload_dir', array( $this, 'set_upload_dir' ) );
	}
}