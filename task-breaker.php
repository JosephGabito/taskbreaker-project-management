<?php
/**
 * Plugin Name: TaskBreaker - Group Project Management
 * Description: A simple WordPress plugin for managing projects and tasks. Integrated into BuddyPress Groups for best collaborative experience.
 * Version: 1.3.5
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
	exit();
}

/**
 * Do not run TaskBreaker on PHP version 5.3.0-
 */
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	add_action( 'admin_notices', 'taskbreaker_admin_notice' );
	function taskbreaker_admin_notice() { ?>
		<div class="notice notice-error is-dismissible">
	        <p><strong><?php _e( 'Notice: TaskBreaker is only available for PHP Version 5.3.0 and above.', 'task_breaker' ); ?></strong></p>
	    </div>
	<?php } 
	return;
}

define( 'TASK_BREAKER_VERSION', '1.3.5' );

define( 'TASK_BREAKER_PROJECT_LIMIT', 10 );

define( 'TASK_BREAKER_PROJECT_SLUG', 'project' );

define( 'TASK_BREAKER_ASSET_URL', plugin_dir_url( __FILE__ ) . 'assets/' );

// Setup the tables on activation.
register_activation_hook( __FILE__, 'task_breaker_install' );

// Migration over the old version.
register_activation_hook( __FILE__, 'task_breaker_import_thrive_intranet_data' );

// Plugin l10n.
add_action( 'plugins_loaded', 'task_breaker_localize_plugin' );

// Include taskbreaker projects transactions.
add_action( 'init', 'task_breaker_register_transactions' );

// Include taskbreaker projects component.
add_action( 'bp_loaded', 'task_breaker_register_projects_component' );

// Included other taskbreaker components.
add_action( 'bp_loaded', 'task_breaker_load_components' );

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
 * Register Task Breaker Deactivation Scripts
 */
register_activation_hook( __FILE__, 'task_breaker_deactivate_thrive_intranet' );

/**
 * This is the legacy version of task breaker.
 *
 * @return void
 */
function task_breaker_deactivate_thrive_intranet() {

	// De-activate Thrive Intranet in case it is used to prevent conflict.
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	deactivate_plugins( '/thrive-intranet/thrive-intranet.php' );

	return;
}

/**
 * Enable Github Updater.
 */
add_action( 'init', 'task_breaker_plugin_updater_init' );

/**
 * The callback function that wraps 'WP_GitHub_Updater'
 * to init action of WordPress to check for our plugin updates.
 *
 * @return void
 */
function task_breaker_plugin_updater_init() {

	/**
	 * Do not trigger the updater script on ajax requests.
	 */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( is_admin() ) {

		include_once plugin_dir_path( __FILE__ ) . '/update-check.php';

		$repo_name = 'task-breaker';

		$config = array(
		 	'slug' => plugin_basename( __FILE__ ),
		 	'proper_folder_name' => 'task-breaker',
		 	'api_url' => sprintf( 'https://api.github.com/repos/codehaiku/%s', $repo_name ),
		 	'raw_url' => sprintf( 'https://raw.github.com/codehaiku/%s/master', $repo_name ),
		 	'github_url' => sprintf( 'https://github.com/codehaiku/%s', $repo_name ),
			'zip_url' => sprintf( 'https://github.com/codehaiku/%s/zipball/master', $repo_name ),
			'sslverify' => true,
			'requires' => '4.0',
			'tested' => '4.4.2',
			'readme' => 'README.md',
			'access_token' => '',
		);

		$github_updater = new WP_GitHub_Updater( $config );

	}

	return;
}
