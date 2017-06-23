<?php
/**
 * Plugin Name: TaskBreaker - Group Project Management
 * Description: A simple WordPress plugin for managing projects and tasks. Integrated into BuddyPress Groups for best collaborative experience.
 * Version: 1.4.11
 * Author: Dunhakdis
 * Author URI: http://dunhakdis.com
 * Text Domain: task_breaker
 * License: GPL2
 *
 * PHP version 5.4+
 *
 * @category Loaders
 * @package  TaskBreaker
 * @author   DUNHAKDIS <info@dunhakdis.com>
 * @license  https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GPL2
 * @link     <http://dunhakdis.com>
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

define( 'TASK_BREAKER_PROFILER', false );

define( 'TASK_BREAKER_VERSION', '1.4.10' );

define( 'TASK_BREAKER_PROJECT_LIMIT', 10 );

define( 'TASK_BREAKER_PROJECT_SLUG', 'project' );

define( 'TASK_BREAKER_ASSET_URL', plugin_dir_url( __FILE__ ) . 'assets/' );

define( 'TASKBREAKER_DIRECTORY_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

// Setup the tables on activation.
register_activation_hook( __FILE__, 'task_breaker_install' );

// Plugin l10n.
add_action( 'plugins_loaded', 'task_breaker_localize_plugin' );

// Include taskbreaker projects transactions.
add_action( 'init', 'task_breaker_register_transactions' );

// Include taskbreaker projects component.
add_action( 'bp_loaded', 'task_breaker_register_projects_component' );

// Included other taskbreaker components.
add_action( 'bp_loaded', 'task_breaker_load_components' );

// Check if group is active
add_action( 'bp_loaded', 'taskbreaker_is_group_active' );

/**
 * Do not run TaskBreaker on PHP version 5.3.0-
 */
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	add_action( 'admin_notices', 'taskbreaker_admin_notice' );
	function taskbreaker_admin_notice() {
	?>
		<div class="notice notice-error is-dismissible">
	        <p><strong><?php _e( 'Notice: TaskBreaker is only available for PHP Version 5.3.0 and above.', 'task_breaker' ); ?></strong></p>
	    </div>
	<?php }
}

function taskbreaker_is_group_active() {
	if ( function_exists( 'bp_is_active' ) ) {
		if ( ! bp_is_active( 'groups' ) ) {
			add_action( 'admin_notices', 'taskbreaker_admin_notice_group_required' );
		}
	}
}

function taskbreaker_admin_notice_group_required() {
	?>
	<div class="notice notice-warning is-dismissible">
		<p><strong><?php _e( 'Notice: TaskBreaker requires BuddyPress Groups Component to be enabled.', 'task_breaker' ); ?></strong></p>
	</div>
<?php }

// Require the assets needed.
require_once plugin_dir_path( __FILE__ ) . 'core/enqueue.php';

// Require the script that registers our 'Project' post type.
require_once plugin_dir_path( __FILE__ ) . 'includes/project-post-type.php';

// Require install script.
require_once plugin_dir_path( __FILE__ ) . 'install/table.php';

// Require notification file.
require_once plugin_dir_path( __FILE__ ) . 'includes/project-notifications.php';

// Require widgets file.
require_once plugin_dir_path( __FILE__ ) . 'widgets/widgets.php';

// Require file attachments class.
require_once plugin_dir_path( __FILE__ ) . 'core/file-attachments.php';

// Require the template tags.
include_once plugin_dir_path( __FILE__ ) . 'core/template-tags.php';

// Register all the action hooks
include_once plugin_dir_path( __FILE__ ) . 'actions/actions.php';


/**
 * TaskBreaker l10n callback.
 *
 * @return void
 */
function task_breaker_localize_plugin() {

	$rel_path = basename( dirname( __FILE__ ) ) . '/languages';

	load_plugin_textdomain( 'task_breaker', false, $rel_path );

	return;
}

/**
 * Register our middle man API transactions.
 *
 * @return void
 */
function task_breaker_register_transactions() {

	include_once plugin_dir_path( __FILE__ ) . 'transactions/controller.php';

	return;
}

/**
 * Register our project components.
 *
 * @return void
 */
function task_breaker_register_projects_component() {

	// Include Task Breaker Project Component.
	include_once plugin_dir_path( __FILE__ ) . '/includes/project-component.php';

	// Include Task Breaker Project Group Component.
	include_once plugin_dir_path( __FILE__ ) . '/includes/project-group-component.php';

	return;
}


/**
 * Load TaskBreaker email templates and callbacks for BuddyPress Email API.
 *
 * @return void
 */
function task_breaker_load_components() {

	// Require our email handler class.
	include_once plugin_dir_path( __FILE__ ) . 'emails/class-buddypress-mail-register.php';

	return;
}

/**
 * A flat class to prevent calls to WordPress globals
 *
 * @version  1.3.6
 */
class TaskBreaker {

	/**
	 * PHP Die Wrapper.
	 *
	 * @return void
	 */
	public static function stop( $message ) {
		die( $message );
	}

	public static function wpdb() {

		global $wpdb;

		return $wpdb;

	}

	public static function get_post() {

		global $post;

		return $post;

	}

	public static function wpfilesystem() {

		global $wp_filesystem;

		return $wp_filesystem;

	}
}
