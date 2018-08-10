<?php
/**
 * Task Breaker CSS and Javascript Loader
 *
 * @since 0.0.1
 * @package TaskBreaker\TaskBreakerEnqueue
 */

if ( ! defined( 'ABSPATH' ) ) { 
	return; 
}

/**
 * Enqueues and register all TaskBreaker Javascript and CSS.
 *
 * @package TaskBreaker\TaskBreakerEnqueue
 */
final class TaskBreakerEnqueue {

	/**
	 * This variable holds the current version of TaskBreaker.
	 *
	 * @var float The TaskBreaker Version.
	 */
	private $version = 1.0;

	/**
	 * Load front scripts and register our project configuration
	 *
	 * @return void
	 */
	public function __construct() {

		$this->version = TASK_BREAKER_VERSION;

		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
		add_action( 'wp_footer', array( $this, 'register_config' ) );

		return;
	}

	/**
	 * Register front-end styling and front-end js.
	 *
	 * @return void
	 */
	public function front_scripts() {

		// Front-end stylesheet.
		wp_enqueue_style( 'taskbreaker-stylesheet', TASK_BREAKER_ASSET_URL . '/css/style.css', array(), $this->version );

		// Administrator JS.
		if ( is_admin() ) {
			wp_enqueue_script(
				'task_breaker-admin',
				TASK_BREAKER_ASSET_URL . '/js/admin.js',
				array( 'jquery', 'backbone' ),
				$this->version, true
			);
		}

		// Front-end JS.
		if ( is_singular( TASK_BREAKER_PROJECT_SLUG ) ) {

			wp_enqueue_script(
				'task_breaker-js',
				TASK_BREAKER_ASSET_URL . 'js/task-breaker.min.js',
				array( 'jquery', 'backbone' ),
				$this->version, true
			);

			// Localize TB js strings.
			$translation_array = array(
				'file_error' => __( 'There was an error uploading your file. File size exceeded the allowed number of bytes per request', 'taskbreaker-project-management' ),
				'file_attachment_error' => __('The application did not received any response from the server. Try uploading smaller files.', 'taskbreaker-project-management'),
				'tasks_not_found' => __('No tasks found. Try different keywords and filters.','taskbreaker-project-management'),
				'comment_confirm_delete' => __('Are you sure you want to delete this comment? This action is irreversible.', 'taskbreaker-project-management'),
				'comment_error' => __('Transaction Error: There was an error trying to delete this comment.', 'taskbreaker-project-management'),
				'project_confirm_delete' => __('Are you sure you want to delete this project? All the tickets under this project will be deleted as well. This action cannot be undone.', 'taskbreaker-project-management'),
				'project_error' => __('There was an error trying to delete this post. Try again later.', 'taskbreaker-project-management'),
				'project_label_btn_update' => __('Update Project', 'taskbreaker-project-management'),
				'project_authentication_error' => __('Only group administrators and moderators can update the project settings.', 'taskbreaker-project-management'),
				'project_all_fields_required' => __('There was an error saving the project. All fields are required.', 'taskbreaker-project-management'),
				'task_error_500' => __('Unexpected Error (500)', 'taskbreaker-project-management'),
				'task_unexpected_error' => __('Unexpected Error Encountered During Request', 'taskbreaker-project-management'),
				'task_confirm_delete' => __('Are you sure you want to delete this task? This action is irreversible', 'taskbreaker-project-management'),
				'task_updated' => __('Task successfully updated', 'taskbreaker-project-management'),
				'task_view' => __('View', 'taskbreaker-project-management'),
				'task_update_error' => __('There was an error updating the task. All fields are required.', 'taskbreaker-project-management'),
				'task_unauthorized_error' => __('You are not allowed to modify this task. Only group project administrators and group projects moderators are allowed.', 'taskbreaker-project-management'),
				'file_attachment_delete' => __('Are you sure you want to delete this file attachment? This process is not reversible.', 'taskbreaker-project-management')
			);

			wp_localize_script( 'task_breaker-js', 'taskbreaker_strings', $translation_array );

			wp_enqueue_script(
				'task_breaker-select2',
				TASK_BREAKER_ASSET_URL . 'js/plugins/select2.min.js',
				array( 'jquery', 'backbone' ),
				$this->version, true
			);
			
			// jQuery IU Date Picker.
			wp_enqueue_script('jquery-ui-slider', array('jquery'));
			wp_enqueue_script('jquery-ui-datepicker', array('jquery'));

			wp_enqueue_style(
				'jquery-ui-style',
				TASK_BREAKER_ASSET_URL . 'css/jquery-ui.css',
				false, 
				'1.9.0'
			);

			// AddOn Time Picker
			wp_enqueue_style( 'jquery-ui-timepicker-style', 
				TASK_BREAKER_ASSET_URL . 'css/jquery-ui-timepicker-addon.min.css', 
				array(), 
				$this->version 
			);
			
			wp_enqueue_script('jquery-ui-timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', 
				array('jquery', 'jquery-ui-datepicker'),
				$this->version, true);
			
		}

		// Project Archive JS.
		wp_enqueue_script(
			'task_breaker-archive-js',
			TASK_BREAKER_ASSET_URL . 'js/archive.js', array( 'jquery', 'backbone' ),
			1.0, true
		);

		return;

	}

	/**
	 * Register the project configuration.
	 *
	 * @return void
	 */
	public function register_config() {

		if ( is_singular( TASK_BREAKER_PROJECT_SLUG ) ) { ?>
			<script>
				var task_breakerAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
				var task_breakerTaskConfig = {
		  			currentProjectId: '<?php echo absint( get_queried_object_id() ); ?>',
		  			currentUserId: '<?php echo absint( get_current_user_id() ); ?>',
				}
			</script>
		<?php
		}

		return;

	}

}
$taskbreaker_enqueue = new TaskBreakerEnqueue();
?>
