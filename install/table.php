<?php
/**
 *
 * This file implements our install script for projects
 * and task table. Runs on plugin activation.
 *
 * @package TaskBreaker
 * @since   1.0
 */


// @reference <https://codex.wordpress.org/Creating_Tables_with_Plugins>

define( 'TASK_BREAKER_TASKS_TABLE', 'task_breaker_tasks' );
define( 'TASK_BREAKER_COMMENTS_TABLE', 'task_breaker_comments' );

define( 'TASK_BREAKER_TASKS_TABLE_VERSION', '1.2.04082016v2' );
define( 'TASK_BREAKER_COMMENTS_TABLE_VERSION', '1.2.1122016v1' );

function task_breaker_install()
{

	// Setup our tasks table.
	task_breaker_tasks_setup_table();

	// Setup our tasks comments/discussion table.
	task_breaker_comments_setup_table();

	return;
}

function task_breaker_import_thrive_intranet_data() {

	// Import existing tasks.
	task_breaker_import_thrive_tasks();

	// Import existing comments.
	task_breaker_import_thrive_comments();

	return;
}

/**
 * Add tasks table to database
 *
 * @return void
 */
function task_breaker_tasks_setup_table()
{
	global $wpdb;

	$tasks_table_name = $wpdb->prefix . TASK_BREAKER_TASKS_TABLE;

	$charset_collate = $wpdb->get_charset_collate();

	$tasks_table_structure = "CREATE TABLE $tasks_table_name (
  		id int(10) NOT NULL AUTO_INCREMENT,
  		title varchar(255) NOT NULL,
  		description text NOT NULL,
  		user int(10) NOT NULL,
		assign_users text NOT NULL,
  		milestone_id int(10) NOT NULL,
  		project_id int(10) NOT NULL,
  		priority int(2) NOT NULL,
  		completed_by int(10) NOT NULL,
  		date_created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  		UNIQUE KEY (id)
	) $charset_collate ;";

	// Include the 'upgrade' script inside WP include dir.
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Run the dbDelta function with our table specification.
	dbDelta( $tasks_table_structure );

	add_option( 'task_breaker_tasks_table_version', TASK_BREAKER_TASKS_TABLE_VERSION );

	return;
}

/**
 * Add comments table to database
 *
 * @return void
 */
function task_breaker_comments_setup_table()
{
	global $wpdb;

	$comments_table_name = $wpdb->prefix . TASK_BREAKER_COMMENTS_TABLE;

	$charset_collate = $wpdb->get_charset_collate();

	$comments_table_structure = "CREATE TABLE $comments_table_name (
		  	id int(10) NOT NULL AUTO_INCREMENT,
		  	details text NOT NULL,
		  	user int(10) NOT NULL,
		  	ticket_id int(10) NOT NULL,
		  	status int(11) NOT NULL DEFAULT '0',
		  	date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  	UNIQUE KEY (id)
		) $charset_collate";

	// Include the 'upgrade' script inside WP include dir.
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Run the dbDelta function with our table specification.
	dbDelta( $comments_table_structure );

	add_option( 'task_breaker_comments_table_version', TASK_BREAKER_COMMENTS_TABLE_VERSION );

	return;
}

function task_breaker_import_thrive_tasks() {

	global $wpdb;

	$tasks_table_name = $wpdb->prefix . TASK_BREAKER_TASKS_TABLE;

	// Check if it has data in wp_thrive_tasks.
	// Copy the data if it has.
	$old_table = $wpdb->prefix . 'thrive_tasks';
	$has_table = $wpdb->get_results("SHOW TABLES LIKE '".$old_table."'");

	// Only run once
	if ( ! get_option( "task_breaker_tasks_imported" ) ) {

		if ( $has_table ) {

			$wpdb->query( "INSERT {$tasks_table_name} SELECT * FROM {$old_table};" );

			update_option( "task_breaker_tasks_imported", "yes" );

			// Update the meta
			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_key' => 'task_breaker_project_group_id',	// string
				),
				array( 'meta_key' => 'thrive_project_group_id' ),
				array(
					'%s',	// value1
				),
				array( '%s' )
			);
		}

	}

	return;
}

function task_breaker_import_thrive_comments() {

	global $wpdb;

	$comments_table_name = $wpdb->prefix . TASK_BREAKER_COMMENTS_TABLE;

	// Check if it has data in wp_thrive_comments.
	// Copy the data if it has.
	$old_table = $wpdb->prefix . 'thrive_comments';
	$has_table = $wpdb->get_results("SHOW TABLES LIKE '".$old_table."'");

	if ( ! get_option( "task_breaker_comments_imported" ) ) {
		if ( $has_table ) {
			$wpdb->query( "INSERT {$comments_table_name} SELECT * FROM {$old_table};" );
			update_option( "task_breaker_comments_imported", "yes" );
		}
	}

	return;
}
?>
