<?php
/**
 * Plugin Name: Task Breaker
 * Description: A WordPress plug-in that will help you break some task!
 * Version: 0.1
 * Author: Dunhakdis
 * Author URI: http://dunhakdis.me
 * Text Domain: thrive
 * License: GPL2
 *
 * PHP version 5
 * 
 * @since     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'THRIVE_PROJECT_LIMIT', 10 );
define( 'THRIVE_PROJECT_SLUG', 'project' );

// Setup the tables on activation.
register_activation_hook( __FILE__, 'thrive_install' );

// Plugin l10n.
add_action( 'plugins_loaded', 'thrive_localize_plugin' );
// Include thrive projects transactions.
add_action( 'init', 'thrive_register_transactions' );
// Include thrive projects component.
add_action( 'bp_loaded', 'thrive_register_projects_component' );

require_once plugin_dir_path( __FILE__ ) . 'core/enqueue.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/project-post-type.php';
require_once plugin_dir_path( __FILE__ ) . 'install/table.php';

/**
 * TaskBreaker l10n callback.
 * @return void
 */
function thrive_localize_plugin() {

	$rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	
    load_plugin_textdomain( 'thrive', FALSE, $rel_path );

    return;
}

/**
 * Register our transactions.
 * @return void
 */
function thrive_register_transactions() {

	include_once plugin_dir_path( __FILE__ ) . 'transactions/controller.php';

	return;
}

/**
 * Register our project components.
 * @return void
 */
function thrive_register_projects_component() {

	include_once plugin_dir_path( __FILE__ ) . '/includes/project-component.php';

	return;
}
?>