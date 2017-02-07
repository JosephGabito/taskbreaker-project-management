<?php
/**
 * This file implements our install script for projects
 * and task table. Runs on plugin activation.
 *
 * @package TaskBreaker
 * @since   1.0
 */

// @reference <https://codex.wordpress.org/Creating_Tables_with_Plugins>
define( 'TASKBREAKER_DB_VERSION', '1.3.1231' );
define( 'TASK_BREAKER_TASKS_TABLE', 'task_breaker_tasks' );
define( 'TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE', 'task_breaker_tasks_user_assignment' );
define( 'TASK_BREAKER_COMMENTS_TABLE', 'task_breaker_comments' );
define( 'TASK_BREAKER_META_TABLE', 'task_breaker_task_meta' );

define( 'TASK_BREAKER_TASKS_TABLE_VERSION', TASKBREAKER_DB_VERSION );
define( 'TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE_VERSION', TASKBREAKER_DB_VERSION );
define( 'TASK_BREAKER_COMMENTS_TABLE_VERSION', TASKBREAKER_DB_VERSION );
define( 'TASK_BREAKER_META_TABLE_VERSION', TASKBREAKER_DB_VERSION );

function task_breaker_install() {

	// Setup our tasks table.
	task_breaker_tasks_setup_table();

	// Setup our tasks comments/discussion table.
	task_breaker_comments_setup_table();

	// Setup our task comments user assignment.
	task_breaker_tasks_user_assignment_setup_table();

	// Setup task meta table
	task_breaker_setup_task_meta_table();

	return;
}
/**
 * Add tasks table to database
 *
 * @return void
 */
function task_breaker_tasks_setup_table() {

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
  		date_added datetime DEFAULT NULL,
  		UNIQUE KEY id (id)
	) $charset_collate ;";

	// Include the 'upgrade' script inside WP include dir.
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

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
function task_breaker_comments_setup_table() {

	global $wpdb;

	$comments_table_name = $wpdb->prefix . TASK_BREAKER_COMMENTS_TABLE;

	$charset_collate = $wpdb->get_charset_collate();

	$comments_table_structure = "CREATE TABLE $comments_table_name (
	  	id int(10) NOT NULL AUTO_INCREMENT,
	  	details text NOT NULL,
	  	user int(10) NOT NULL,
	  	ticket_id int(10) NOT NULL,
	  	status int(11) NOT NULL DEFAULT '0',
	  	date_added datetime DEFAULT NULL,
	  	UNIQUE KEY id (id)
	) $charset_collate";

	// Include the 'upgrade' script inside WP include dir.
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Run the dbDelta function with our table specification.
	dbDelta( $comments_table_structure );

	add_option( 'task_breaker_comments_table_version', TASK_BREAKER_COMMENTS_TABLE_VERSION );

	return;
}

function task_breaker_tasks_user_assignment_setup_table() {

	global $wpdb;

	$task_user_assignment_table_name = $wpdb->prefix . TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE;

	$charset_collate = $wpdb->get_charset_collate();

	$task_user_assignment_table_structure = "CREATE TABLE $task_user_assignment_table_name (
	  	id int(10) NOT NULL AUTO_INCREMENT,
	  	task_id int(10) NOT NULL,
	  	member_id int(10) NOT NULL,
	  	date_added datetime DEFAULT NULL,
	  	UNIQUE KEY id (id)
	) $charset_collate";

	// Include the 'upgrade' script inside WP include dir.
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Run the dbDelta function with our table specification.
	dbDelta( $task_user_assignment_table_structure );

	add_option( 'task_breaker_comments_table_version', TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE_VERSION );

	return;
}

function task_breaker_setup_task_meta_table() {

	global $wpdb;

	//TASK_BREAKER_META_TABLE_VERSION
	$task_meta_table = $wpdb->prefix . TASK_BREAKER_META_TABLE;

	$charset_collate = $wpdb->get_charset_collate();

	$task_meta_table_struct = "CREATE TABLE $task_meta_table (
	  	id int(10) NOT NULL AUTO_INCREMENT,
	  	task_id int(10) NOT NULL,
	  	meta_key varchar(255) NOT NULL,
	  	meta_value text NOT NULL,
	  	UNIQUE KEY id (id)
	) $charset_collate";

	// Include the 'upgrade' script inside WP include dir.
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Run the dbDelta function with our table specification.
	dbDelta( $task_meta_table_struct );

	add_option( 'task_breaker_comments_table_version', TASK_BREAKER_TASKS_USER_ASSIGNMENT_TABLE_VERSION );

	return;
}
