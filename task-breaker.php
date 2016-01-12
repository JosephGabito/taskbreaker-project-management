<?php
/**
 * Plugin Name: Task Breaker
 * Description: A WordPress plug-in that will help you break some task!
 * Version: 0.1
 * Author: Dunhakdis
 * Author URI: http://dunhakdis.me
 * Text Domain: task_breaker
 * License: GPL2
 *
 * PHP version 5
 * 
 * @since     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'TASK_BREAKER_PROJECT_LIMIT', 10 );
define( 'TASK_BREAKER_PROJECT_SLUG', 'project' );
define( 'TASK_BREAKER_ASSET_URL', plugin_dir_url(__FILE__) . 'assets/' );

// Setup the tables on activation.
register_activation_hook( __FILE__, 'task_breaker_install' );
// Migration over the old version
register_activation_hook( __FILE__, 'task_breaker_import_thrive_intranet_data' );

// Plugin l10n.
add_action( 'plugins_loaded', 'task_breaker_localize_plugin' );
// Include task_breaker projects transactions.
add_action( 'init', 'task_breaker_register_transactions' );
// Include task_breaker projects component.
add_action( 'bp_loaded', 'task_breaker_register_projects_component' );

require_once plugin_dir_path( __FILE__ ) . 'core/enqueue.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/project-post-type.php';
require_once plugin_dir_path( __FILE__ ) . 'install/table.php';

/**
 * TaskBreaker l10n callback.
 * @return void
 */
function task_breaker_localize_plugin() {

	$rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	
    load_plugin_textdomain( 'task_breaker', FALSE, $rel_path );

    return;
}

/**
 * Register our transactions.
 * @return void
 */
function task_breaker_register_transactions() {

	include_once plugin_dir_path( __FILE__ ) . 'transactions/controller.php';

	return;
}

/**
 * Register our project components.
 * @return void
 */
function task_breaker_register_projects_component() {

	include_once plugin_dir_path( __FILE__ ) . '/includes/project-component.php';

	return;
}
?>