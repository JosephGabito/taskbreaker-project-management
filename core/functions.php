<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerCore
 */
if ( ! defined( 'ABSPATH' ) ) { 
	return; 
}
/**
 * TaskBreakerCore is a collection of methods that are useful
 * across the whole application
 *
 * @package TaskBreaker\TaskBreakerCore
 */
class TaskBreakerCore {

	/**
	 * Returns the task_breaker component id or slug
	 *
	 * @return string the task_breaker component id or slug
	 */
	function get_component_id() {
		return apply_filters( 'task_breaker_component_id', 'projects' );
	}

	/**
	 * Returns the Project component name.
	 * 
	 * @return string The Project Name.
	 */
	function get_component_name() {
		return apply_filters( 'task_breaker_component_name', __( 'Projects', 'task_breaker' ) );
	}

	/**
	 * Get the template directory absolute path.
	 * 
	 * @return stirng The absolute path of the template directory.
	 */
	function get_template_directory() {
		return plugin_dir_path( __FILE__ ) . '../templates';
	}

	/**
	 * Get the includes directory absolute path.
	 * 
	 * @return string the directory absolute path.
	 */
	function get_include_directory() {
		return plugin_dir_path( __FILE__ ) . '../includes';
	}

	/**
	 * Display a select field with list of available priorities
	 *
	 * @param  integer $default     the default priority
	 * @param  string  $select_name the name of the select field
	 * @param  string  $select_id   the id of the select field
	 * @return void
	 */
	function task_priority_select( $default = 1, $select_name = 'task_breaker_task_priority', $select_id = 'task_breaker-task-priority-select' ) {

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$task_breaker_tasks = TaskBreakerTasksController::get_instance();

		$priorities = $task_breaker_tasks->getPriorityCollection();

		echo '<select name="' . esc_attr( $select_name ) . '" id="' . esc_attr( $select_id ) . '" class="task_breaker-task-select">';

			foreach ( $priorities as $priority_id => $priority_label ) {

				$selected = (intval( $priority_id ) === $default) ? 'selected': '';

				echo '<option ' . esc_html( $selected ) . ' value="' . esc_attr( $priority_id ) . '">' . esc_html( $priority_label ) . '</option>';
			}

		echo '</select>';

		return;
	}

	/**
	 * Counts the number of task for the given project. Can be filter by 'all', 'completed'
	 * 
	 * @param  integer $project_id The project ID.
	 * @param  string  $type       The type of the task tasks that you want to fetch.
	 * @return integer             The number of task under the specified project.
	 */
	function count_tasks( $project_id = 0, $type = 'all' ) {

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$task_breaker_tasks = new TaskBreakerTask();

		return $task_breaker_tasks->getCount( $project_id, $type );

	}

}

function task_breaker_comments_template( $args = array(), $task = array() ) {

	ob_start();

	task_breaker_locate_template( 'task-comment-item', $args );

	return ob_get_clean();
}

function task_breaker_get_task( $task_id = 0 ) {

	global $wpdb;

	$stmt = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}task_breaker_tasks WHERE id = %d", $task_id );

	$result = $wpdb->get_row( $stmt, OBJECT );

	return $result;
}

function task_breaker_get_tasks_comments( $ticket_id = 0 ) {

	global $wpdb;

	$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}task_breaker_comments WHERE ticket_id = %d", absint( $ticket_id ) );

	$results = $wpdb->get_results( $query, 'ARRAY_A' );

	return $results;

}

function task_breaker_project_settings() {

	include plugin_dir_path( __FILE__ ) . '../templates/project-settings.php';

	return;
}

function task_breaker_get_config_base_prefix() {

	global $wpdb;

	if ( is_multisite() ) {
		return	$wpdb->base_prefix;
	}

	return $wpdb->prefix;
}

function task_breaker_get_current_user_owned_groups() {

	return task_breaker_get_user_group_admin_mod();

}

function task_breaker_get_displayed_user_groups() {

	global $wpdb;

	if ( ! function_exists( 'bp_displayed_user_id' ) ) {
		return;
	}

	$current_user_id = intval( bp_displayed_user_id() );

	if ( 0 === $current_user_id ) {
		return array();
	}

	$bp_groups = $wpdb->prefix . 'bp_groups';
	$bp_group_members = $wpdb->prefix . 'bp_groups_members';

	$stmt = sprintf( "SELECT {$bp_group_members}.group_id, {$bp_groups}.name
			FROM {$bp_group_members }
			INNER JOIN {$bp_groups}
			ON {$bp_groups}.id = {$bp_group_members}.group_id
			WHERE user_id = %d
			ORDER BY {$bp_groups}.name asc", $current_user_id );

	$results = $wpdb->get_results( $stmt, 'ARRAY_A' );

	if ( $results ) {
		return $results;
	}

	return array();

}

function task_breaker_project_nav( WP_Query $object ) {
	// Maximum page.
	$maximum_page = absint( $object->max_num_pages );
	// Current page.
	$current_page = absint( $object->query_vars['paged'] );
	// Do no display pagination if there is only 1 project
	if ( $maximum_page === 1 ) {
		return;
	}
	?>
	<nav>
		<?php echo esc_html( apply_filters( 'task_breaker_projects_page_label', __( 'Page:', 'task_breaker' ) ) ); ?>
		<?php for ( $page = 1; $page <= $maximum_page; $page++ ) { ?>
			<?php $active = ''; ?>
			<?php if ( $page === $current_page ) { ?>
				<?php $active = 'active '; ?>
			<?php } ?>
		<a class="<?php echo $active;?>project-nav-link" title="<?php echo sprintf( __( 'Go to page %d &raquo;', 'task_breaker' ), $page ); ?>" href="?paged=<?php echo $page; ?>">
			<?php echo $page; ?>
		</a>
		<?php } ?>
	</nav>
	<?php
	return;
}

function task_breaker_new_project_form( $group_id = 0 ) {

	if ( ! is_user_logged_in() ) { return; }

	include plugin_dir_path( __FILE__ ) . '../templates/project-add.php';

	return;
}

function task_breaker_project_meta( $project_id = 0 ) {

	$core = new TaskBreakerCore();

	if ( 0 === $project_id ) { return; }

	$tasks_total = absint( $core->count_tasks( $project_id, $type = 'all' ) );
	$tasks_completed  = absint( $core->count_tasks( $project_id, $type = 'completed' ) );
	$tasks_remaining = absint( $tasks_total - $tasks_completed );

	if ( 0 !== $tasks_total ) {

		$tasks_progress = ceil( ( $tasks_completed / $tasks_total ) * 100 );

		$args = array(
			'tasks_total' => $tasks_total,
			'tasks_completed' => $tasks_completed,
			'tasks_remaining' => $tasks_remaining,
			'tasks_progress' => $tasks_progress,
		);

		task_breaker_locate_template( 'task-meta', $args );

	} // end if

	return;
}

function task_breaker_project_user( $user_id = 0, $post_id = 0 ) {
	?>

	<?php if ( $post_id === 0 ) { return; } ?>

	<?php if ( $user_id === 0 ) { return; } ?>

	<?php // Project user. ?>
	<?php $user_profile_url = get_author_posts_url( $user_id ); ?>

	<?php // Use bp profile if possible. ?>

	<?php if ( function_exists( 'bp_core_get_user_domain' ) ) { ?>
		<?php $user_profile_url = bp_core_get_user_domain( $user_id ); ?>
	<?php } ?>

	<?php esc_html_e( 'Started by ', 'task_breaker' ); ?>

	<a href="<?php echo esc_url( $user_profile_url ); ?>" title="<?php _e( 'Visit User Profile', 'task_breaker' ); ?>">
		<?php echo get_avatar( $user_id, 32 ); ?>
		<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
	</a>

	<?php $group_id = absint( get_post_meta( $post_id, 'task_breaker_project_group_id', true ) ); ?>

	<?php if ( function_exists( 'groups_get_group' ) ) { ?>

		<?php $group = groups_get_group( array( 'group_id' => $group_id ) ); ?>

		<?php if ( ! empty( $group->id ) ) { ?>

			<?php _e( 'under &raquo;' ); ?>

			<a href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>" title="<?php echo esc_attr( $group->name ); ?>">

				<?php echo bp_core_fetch_avatar( array( 'object' => 'group', 'item_id' => $group_id ) ) ?>

				<?php echo esc_html( $group->name ); ?>

			</a>

		<?php } ?>

	<?php } ?>

	<?php // End Project User. ?>

<?php
return;
}

function task_breaker_locate_template( $file_name = '', $args = '' ) {

	if ( empty( $file_name ) ) {
		return;
	}

	include plugin_dir_path( __FILE__ ) . '../templates/' . esc_attr( $file_name ) . '.php';

	return;
}

function task_breaker_project_loop( $args = array() ) {

	if ( ! is_array( $args ) ) {
		return;
	}

	$args['post_type'] = 'project';

	$args['paged'] = get_query_var( 'paged' );

	include plugin_dir_path( __FILE__ ) . '../templates/project-loop-content.php';

	return;

}

function task_breaker_new_project_modal( $group_id = 0 ) {
	include plugin_dir_path( __FILE__ ) . '../templates/project-add-modal.php';
}


function task_breaker_settings_display_editor() {

	global $post;

	$content = $post->post_content;

	$args = array(
		'teeny' => true,
		'editor_height' => 100,
		'media_buttons' => false,
	);

	return wp_editor( $content, $editor_id = 'task_breakerProjectContent', $args );

}

/**
 * Get current user groups where he/she is the group admin or
 * one of the moderators.
 *
 * @return void
 */
function task_breaker_get_user_group_admin_mod() {

	global $bp;
	global $wpdb;

	if ( ! function_exists( 'buddypress' ) ) {
		return array();
	}

	if ( ! bp_is_active( 'groups' ) ) {
		return array();
	}

	$groups = array();

	$bp_table = $bp->groups->table_name_members;

	$user_id = get_current_user_id();

	$group_results_stmt = "SELECT
            groups.id as group_id,
            group_member.user_id as user_id,
            groups.name as group_name,
            group_member.is_mod,
            group_member.is_admin
            FROM
            {$wpdb->prefix}bp_groups_members as group_member
            INNER JOIN
            {$wpdb->prefix}bp_groups as groups
            WHERE
            group_member.group_id = groups.id
            AND
            ( group_member.is_mod = 1 OR group_member.is_admin = 1 )
            AND
            group_member.user_id = %d GROUP BY groups.id;";

	$group_results = $wpdb->get_results(
		$wpdb->prepare( $group_results_stmt, $user_id ),
	OBJECT );

	if ( ! empty( $group_results ) ) {
		return $group_results;
	}

	return $groups;

}

/**
 * The button for our add new project.
 *
 * @return void
 */
function task_breaker_new_project_modal_button() {
	if ( is_user_logged_in() ) { ?>
		<a id="task_breaker-new-project-btn" class="<?php echo esc_attr( apply_filters( 'task_breaker_new_project_modal_button_class', 'button' ) ); ?>" href="#">
		    <?php esc_html_e( 'New Project', 'task_breaker' ); ?>
		</a>
	<?php
	}
}


function task_breaker_parse_assigned_users( $user_id_collection = '' ) {

	global $wpdb;

	$users = new stdclass;

	if ( ! empty( $user_id_collection ) ) {
		$stmt = esc_sql( "SELECT ID, display_name FROM {$wpdb->prefix}users WHERE ID IN({$user_id_collection})" );

		$users = $wpdb->get_results( $stmt );

	}

	return $users;

}

/**
 * Get Project's group id by project ID.
 */
function task_breaker_get_project_group_id( $project_id = 0 ) {

	$group_id = 0;

	if ( 0 === $project_id ) {
		return 0;
	}

	$group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

	if ( empty( $group_id ) ) {
		return 0;
	}

	return $group_id;
}

function task_breaker_get_current_user_tasks( $args = array() ) {
	
	global $wpdb;

	$user_id = get_current_user_id();

	$user_assignment_tbl = "{$wpdb->prefix}task_breaker_tasks_user_assignment as user_task_assignment";
	$task_tbl = "{$wpdb->prefix}task_breaker_tasks as task_table";
	$limit = ( ! empty( $args['task_number'] ) ) ? absint( $args['task_number'] ): 5;

	$stmt = $wpdb->prepare("SELECT * FROM {$user_assignment_tbl} INNER JOIN {$task_tbl} ON task_table.id = user_task_assignment.task_id WHERE user_task_assignment.member_id = %d ORDER BY task_table.id DESC LIMIT %d", $user_id, $limit );

	return $wpdb->get_results( $stmt, OBJECT );;

}
?>
