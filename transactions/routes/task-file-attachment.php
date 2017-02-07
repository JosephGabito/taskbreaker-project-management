<?php
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

$fileAttachment = new TaskBreakerFileAttachment();

$file = $fileAttachment->process_http_file();

$file_name = basename( $file['file'] );

$this->task_breaker_api_message(
	array(
		'message' => 'success',
		'response' => __( 'File upload success' ),
		'file' => $file_name
	)
);
