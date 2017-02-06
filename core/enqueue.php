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

			wp_enqueue_script(
				'task_breaker-select2',
				TASK_BREAKER_ASSET_URL . 'js/plugins/select2.min.js',
				array( 'jquery', 'backbone' ),
				$this->version, true
			);
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
