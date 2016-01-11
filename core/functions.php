<?php
/**
 * This file contains core functions that are use
 * as help logic in thrive projects component
 *
 * @since  1.0
 * @package Thrive Intranet
 * @subpackage Projects
 */
if ( ! defined( 'ABSPATH' ) ) { die(); }

/**
 * Returns the thrive component id or slug
 * @return string the thrive component id or slug
 */
function thrive_component_id() {
	return apply_filters( 'thrive_component_id', 'projects' );
}

function thrive_component_name() {
	return apply_filters( 'thrive_component_name', __( 'Projects', 'thrive' ) );
}

function thrive_template_dir() {
	return plugin_dir_path( __FILE__ ) . '../templates';
}

function thrive_include_dir() {
	return plugin_dir_path( __FILE__ ) . '../includes';
}

/**
 * Display a select field with list of available priorities
 * @param  integer $default     the default priority
 * @param  string  $select_name the name of the select field
 * @param  string  $select_id   the id of the select field
 * @return void
 */
function thrive_task_priority_select($default = 1, $select_name = 'thrive_task_priority', $select_id = 'thrive-task-priority-select') {

	require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

	$thrive_tasks = new ThriveProjectTasksController();

	$priorities = $thrive_tasks->getPriorityCollection();

	echo '<select name="'.esc_attr( $select_name ).'" id="'.esc_attr( $select_id ).'" class="thrive-task-select">';

	foreach ( $priorities as $priority_id => $priority_label ) {

		$selected = (intval( $priority_id ) === $default) ? 'selected': '';

		echo '<option '.esc_html( $selected ).' value="'.esc_attr( $priority_id ).'">'.esc_html( $priority_label ).'</option>';
	}

	echo '</select>';

	return;
}

function thrive_count_tasks($project_id, $type = 'all') {

	require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

	$thrive_tasks = new ThriveProjectTasksModel();

	return $thrive_tasks->getCount( $project_id, $type );

}

function thrive_add_task_form() {

	include plugin_dir_path( __FILE__ ) . '../templates/task-add.php';

}

function thrive_edit_task_form() {

	include plugin_dir_path( __FILE__ ) . '../templates/task-edit.php';

}

function thrive_task_filters() {

	include plugin_dir_path( __FILE__ ) . '../templates/task-filter.php';

}
/**
 * thrive_render_task($echo = true, $page = 1, $limit = 10)
 *
 * Renders a table that enables admin to manage
 * tickets under a project. Only use this function
 * when calling inside the administration area
 *
 * @param  boolean $echo  option to show or store the task inside the variable
 * @param  integer $page  sets the current page of tasks
 * @param  integer $limit limits the number of task displayed
 * @return void if $echo is set to true other wise returns the constructed markup for tasks
 */
function thrive_render_task($args = array()) {

	$defaults = array(
			'project_id' => 0,
			'page' => 1,
			'priority' => -1,
			'search' => '',
			'orderby' => 'date_created',
			'order' => 'desc',
			'show_completed' => 'no',
			'echo' => true,
		);

	foreach ( $defaults as $option => $value ) {

		if ( ! empty( $args[$option] ) ) {
			$$option = $args[$option];
		} else {
			$$option = $value;
		}
	}
	// todo convert thrive_render_task params to array
	if ( $echo === 'no' ) { ob_start(); }

	require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

	$thrive_tasks = new ThriveProjectTasksController();
	$tasks = $thrive_tasks->renderTasks( $args );
	$stats = $tasks['stats'];
	$tasks = $tasks['results'];
	$current_user_id = get_current_user_id();

	echo '<div id="thrive-task-list-canvas">';

	$open_tasks_no     = thrive_count_tasks( $project_id, $type = 'open' );
	$completed_task_no = thrive_count_tasks( $project_id, $type = 'completed' );
	$all_tasks_no      = thrive_count_tasks( $project_id, $type = 'all' );

	if ( ! empty( $search ) ) {
		echo '<p id="thrive-view-info">'.sprintf( __( 'Search result for: "%s"' ), esc_html( $search ) ).'</p>';
	} else {
		if ( $show_completed == 'no' ) {
			echo '<p id="thrive-view-info">'.sprintf( _n( 'Currently showing %d task ', 'Currently showing %d tasks ', $open_tasks_no, 'thrive' ), $open_tasks_no );
			echo sprintf( __( 'out of %d', 'thrive' ), $all_tasks_no ) . '</p>';
		}

		if ( $show_completed == 'yes' ) {
			echo '<p id="thrive-view-info">'.sprintf( _n( 'Currently showing %d completed task ', 'Currently showing %d completed tasks ', $completed_task_no, 'thrive' ), $completed_task_no );
			echo sprintf( __( 'out of %d', 'thrive' ), $all_tasks_no ) . '</p>';
		}
	}

	if ( empty( $tasks ) ) {

		echo '<p class="bp-template-notice error" id="thrive-message">';
			echo __( 'No results found. Try another filter or add new task.', 'thrive' );
		echo '</p>';

	} else {

		echo '<table class="wp-list-table widefat fixed striped pages" id="thrive-core-functions-render-task">';
		echo '<tr>';
			echo '<th width="70%">'.__( 'Title', 'thrive' ).'</th>';
			echo '<th>'.__( 'Priority', 'thrive' ).'</th>';
			echo '<th>'.__( 'Date', 'thrive' ).'</th>';
		echo '</tr>';

		foreach ( (array) $tasks as $task ) {

			$priority_label = $thrive_tasks->getPriority( $task->priority );

			$completed = '';
			if ( $task->completed_by != 0 ) {
				$completed = 'completed';
			}

			$classes = implode( ' ', array( esc_attr( sanitize_title( $priority_label ) ), $completed ) );

			$row_actions = '<div class="row-actions">';
				$row_actions .= '<span class="edit"><a href="#tasks/edit/'.intval( $task->id ).'">Edit</a> | </span>';
			if ( empty( $completed ) ) {
				$row_actions .= '<span data-user_id="'.intval( $current_user_id ).'" data-task_id="'.intval( $task->id ).'" class="thrive-complete-ticket"><a href="#">Complete</a> | </span>';
			} else {
				$row_actions .= '<span data-task_id="'.intval( $task->id ).'" class="thrive-renew-task"><a href="#">Renew Task</a> | </span>';
			}
				$row_actions .= '<span class="trash"><a data-ticket-id="'.intval( $task->id ).'" class="thrive-delete-ticket-btn" href="#">Delete</a> </span>';
			$row_actions .= '</div>';

			echo '<tr class="'.$classes.'">';
				
				echo '<td><strong><a class="row-title" href="#tasks/edit/'.intval( $task->id ).'">'. stripslashes( esc_html( $task->title ) ).'</a></strong>'.$row_actions.'</td>';
				echo '<td>'.esc_html( $priority_label ).'</h3></td>';

				if ( "0000-00-00 00:00:00" !== $task->date_created ) {
					echo '<td>'.esc_html( date( 'M d, o @H:i', strtotime( $task->date_created ) ) ).'</h3></td>';
				} else {
					echo '<td>'.__('N/A','thrive').'</td>';
				}

			echo '</tr>';
		}
		echo '</table>';

		$total      = intval( $stats['total'] );
		$perpage    = intval( $stats['perpage'] );
		$total_page = intval( $stats['total_page'] );
		$currpage   = intval( $stats['current_page'] );
		$min_page	= intval( $stats['min_page'] );
		$max_page   = intval( $stats['max_page'] );

		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo '<span class="displaying-num">'.sprintf( _n( '%s task', '%s tasks', $total, 'thrive' ),$total ).'</span>';

		if ( $total_page >= 1 ) {
			echo '<span id="trive-task-paging" class="pagination-links">';
				echo '<a class="first-page disabled" title="'.__( 'Go to the first page', 'thrive' ).'" href="#tasks/page/'.$min_page.'">«</a>';
				echo '<a class="prev-page disabled" title="'.__( 'Go to the previous page', 'thrive' ).'" href="#">‹</a>';

						echo '<span class="paging-input"><label for="thrive-task-current-page-selector" class="screen-reader-text">'.__( 'Select Page', 'thrive' ).'</label>';
						echo '<input readonly class="current-page" id="thrive-task-current-page-selector" type="text" maxlength="'.strlen( $total_page ).'" size="'.strlen( $total_page ).'"value="'.intval( $currpage ).'">';
						echo ' of <span class="total-pages">'.$total_page.'</span></span>';

				echo '<a class="next-page" title="'.__( 'Go to the next page', 'thrive' ).'" href="#">›</a>';
				echo '<a class="last-page" title="'.__( 'Go to the last page', 'trive' ).'" href="#tasks/page/'.$max_page.'">»</a></span>';
			echo '</span>';
		}

		echo '</div></div><!--.tablenav--><!--.tablenav-pages-->';
	}

	echo '</div><!--#thrive-task-list-canvas-->';

	?>
	<script>
	var thriveProjectSettings = {
		project_id: '<?php echo absint($post->ID);?>',
		nonce: '<?php echo wp_create_nonce( "thrive-transaction-request" ); ?>'
	};
	</script>
	<?php

	if ( $echo === 'no' ) {
		return ob_get_clean();
	} else {
		return;
	}
}

/**
 * Renders the tasks
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function thrive_the_tasks($args) {

	ob_start();

	require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

	$defaults = array(
			'project_id' => 0,
			'page' => 1,
			'priority' => -1,
			'search' => '',
			'orderby' => 'date_created',
			'order' => 'desc',
			'show_completed' => 'no',
			'echo' => true,
		);

	foreach ( $defaults as $option => $value ) {

		if ( ! empty( $args[$option] ) ) {
			$$option = $args[$option];
		} else {
			$$option = $value;
		}
	}

	$thrive_tasks = new ThriveProjectTasksController();

	$tasks = $thrive_tasks->renderTasks( $args );

	// Fallback to default values when there are no tasks.
	if ( empty( $tasks ) ) {

		// Default parameters.
		$tasks = array(
			'stats' => array(
				'total' 		=> 0,
				'perpage' 		=> 5,
				'current_page' 	=> 1,
				'total_page' 	=> 1,
				'min_page' 		=> 0,
				'max_page' 		=> 0
			)
		);
	}
?>

<div class="clearfix"></div>

<div id="thrive-project-tasks">
	<?php if ( ! empty( $tasks['results'] ) ) { ?>
		<ul>
		<?php foreach ( $tasks['results'] as $task ) { ?>
			<?php
			$priority_label = $thrive_tasks->getPriority( $task->priority );
			$completed = '';
			if ( $task->completed_by != 0 ) {
				$completed = 'completed';
			}
			$classes = implode( ' ', array( esc_attr( sanitize_title( $priority_label ) ), $completed ) );
			?>
			<li class="thrive-task-item <?php echo esc_attr( $classes ); ?>">
				<ul class="thrive-task-item-details">
					<li class="priority">
						<span>
							<?php $priority_collection = $thrive_tasks->getPriorityCollection(); ?>
							<?php echo $priority_collection[$task->priority]; ?>
						</span>
					</li>
					<li class="details">
						<h3>
							<a href="#tasks/view/<?php echo intval( $task->id ); ?>">
								<span class="task-id">#<?php echo intval( $task->id );?></span> - 
								<?php echo esc_html( stripslashes( $task->title ) ); ?>
								
							</a>
						</h3>
					</li>
					<li class="last-user-update">
						<div class="task-user">
							<?php echo get_avatar( intval( $task->user ), 32 ); ?>
							<?php $user = get_userdata( $task->user ); ?>
							<div class="task-user-name">
								<small>
									<?php echo esc_html( $user->display_name ); ?>
								</small>
							</div>
						</div>
					</li>
				</ul>
				
			</li>
		<?php } ?>
		</ul>
	<?php } else { ?>
		<div class="error" id="message">
			<p>
				<?php _e( 'No tasks found. If you\'re trying to find a task, kindly try different keywords and/or filters.', 'thrive' ); ?>
			</p>
		</div>
	<?php } ?>

<?php

$stats = $tasks['stats'];
$total = intval( $stats['total'] );
$perpage    = intval( $stats['perpage'] );
$total_page = intval( $stats['total_page'] );
$currpage   = intval( $stats['current_page'] );
$min_page	= intval( $stats['min_page'] );
$max_page   = intval( $stats['max_page'] );

if ( 0 !== $total ) {

	echo '<div class="tablenav"><div class="tablenav-pages">';
	echo '<span class="displaying-num">'.sprintf( _n( '%s task', '%s tasks', $total, 'thrive' ),$total ).'</span>';

	if ( $total_page >= 1 ) {
		echo '<span id="thrive-task-paging" class="pagination-links">';
			echo '<a class="first-page disabled" title="'.__( 'Go to the first page', 'thrive' ).'" href="#tasks/page/'.$min_page.'">«</a>';
			echo '<a class="prev-page disabled" title="'.__( 'Go to the previous page', 'thrive' ).'" href="#">‹</a>';
				echo '<span class="paging-input"><label for="thrive-task-current-page-selector" class="screen-reader-text">'.__( 'Select Page', 'thrive' ).'</label>';
				echo '<input readonly class="current-page" id="thrive-task-current-page-selector" type="text" maxlength="'.strlen( $total_page ).'" size="'.strlen( $total_page ).'"value="'.intval( $currpage ).'">';
				echo ' of <span class="total-pages">'.$total_page.'</span></span>';

				echo '<a class="next-page" title="'.__( 'Go to the next page', 'thrive' ).'" href="#">›</a>';
				echo '<a class="last-page" title="'.__( 'Go to the last page', 'trive' ).'" href="#tasks/page/'.$max_page.'">»</a></span>';
			echo '</span>';
	}

	echo '</div><!--.tablenav--></div><!--.tablenav-pages -->';

	?>
<?php } // End if ( 0 !== $total ). ?>
</div><!--#thrive-project-tasks-->
<?php
return ob_get_clean();
}

function thrive_ticket_single($task) {
	ob_start(); ?>
	<div id="thrive-single-task">
		
		<div id="thrive-single-task-details">
			<?php
				$priority_label = array(
					'1' => __( 'Normal', 'thrive' ),
					'2' => __( 'High', 'thrive' ),
					'3' => __( 'Critical', 'thrive' ),
				);
			?>
			<div class="task-priority <?php echo sanitize_title( $priority_label[$task->priority] ); ?>">
				<?php echo esc_html( $priority_label[$task->priority] ); ?>
			</div>
			<h2>
				<?php echo esc_html( $task->title ); ?>
				<span class="clearfix"></span>
			</h2>

			<div class="task-content">
				<?php echo do_shortcode( $task->description ); ?>
			</div>

			<div class="task-content-meta">
				<div class="alignright">
					<a href="#tasks" title="<?php _e( 'Tasks List', 'thrive' ); ?>" class="button">
						<?php _e( '&larr; Tasks List', 'thrive' ); ?>
					</a>
					<a href="#tasks/edit/<?php echo intval( $task->id ); ?>" class="button">
						<?php _e( 'Edit', 'thrive' ); ?>
					</a>
				</div>
				<div class="clearfix"></div>
			</div>
		</div><!--#thrive-single-task-details-->

		<ul id="task-lists">
			<li class="thrive-task-discussion">
				<h3>
					<?php _e( 'Discussion', 'thrive' ); ?>
				</h3>
			</li>
			<?php $comments = thrive_get_tasks_comments( $task->id ); ?>
			<?php if ( ! empty( $comments ) ) { ?>
				<?php foreach ( $comments as $comment ) { ?>
					<?php echo thrive_comments_template( $comment, (array) $task ); ?>
				<?php } ?>
			<?php } ?>

		</ul><!--#task-lists-->

		<div id="task-editor">
			<div id="task-editor_update-status">
				<?php
				$completed = 'no';
				if ( absint( $task->completed_by ) !== 0 ) {
					$completed = 'yes';
				}
				?>
					<div id="comment-completed-radio">
						<?php if ( $completed === 'no' ) { ?>
						<div class="alignleft">
							<label for="ticketStatusInProgress"> 
								<input <?php echo $completed === 'no' ?  'checked': ''; ?> id="ticketStatusInProgress" type="radio" value="no" name="task_commment_completed">
								<small><?php _e( 'In Progress', 'thrive' ); ?></small>
							</label>
						</div>
						<?php } ?>
						<div class="alignleft">
							<label for="ticketStatusComplete">
								<input <?php echo $completed === 'yes' ? 'checked': ''; ?> id="ticketStatusComplete" type="radio" value="yes" name="task_commment_completed">
								<small><?php _e( 'Completed', 'thrive' ); ?></small>
							</label>
						</div>
						<?php if ( $completed === 'yes' ) { ?>
						<div class="alignleft">
							<label for="ticketStatusReOpen">
								<input id="ticketStatusReOpen" type="radio" value="reopen" name="task_commment_completed">
								<small><?php _e( 'Reopen Task', 'thrive' ); ?></small>
							</label>
						</div>
						<?php } ?>
					</div>
					<!--On Complete -->
					<div id="thrive-comment-completed-radio" class="hide">
						<div class="alignleft">
							<label for="ticketStatusCompleteUpdate">
								<input disabled id="ticketStatusCompleteUpdate" type="radio" value="yes" name="task_commment_completed">
								<small><?php _e( 'Completed', 'thrive' ); ?></small>
							</label>
						</div>
						<div class="alignleft">
							<label for="ticketStatusReOpenUpdate">
								<input disabled id="ticketStatusReOpenUpdate" type="radio" value="reopen" name="task_commment_completed">
								<small><?php _e( 'Reopen Task', 'thrive' ); ?></small>
							</label>
						</div>						
					</div>

					<!-- On ReOpen -->
					<div id="thrive-comment-reopen-radio" class="hide">
						<div class="alignleft">
							<label disabled for="ticketStatusReOpenInProgress">
								<input id="ticketStatusReOpenInProgress" type="radio" value="yes" name="task_commment_completed">
								<small><?php _e( 'In Progress', 'thrive' ); ?></small>
							</label>
						</div>
						<div class="alignleft">
							<label disabled for="ticketStatusReOpenComplete">
								<input disabled id="ticketStatusReOpenComplete" type="radio" value="reopen" name="task_commment_completed">
								<small><?php _e( 'Complete', 'thrive' ); ?></small>
							</label>
						</div>						
					</div>

				<div class="clearfix"></div>	
			</div>
			
			<div id="task-editor_update-content">
				<textarea id="task-comment-content" rows="5" width="100"></textarea>
			</div>

			<div id="task-editor_update-priority">
				<label for="thrive-task-priority-select">
					<?php _e( 'Update Priority:', 'thrive' ); ?>
					<?php thrive_task_priority_select( $select = absint( $task->priority ), $name = 'thrive-task-priority-update-select', $id = 'thrive-task-priority-update-select' );?>
				</label>
			</div>
			
			<div id="task-editor_update-submit">
				<button type="button" id="updateTaskBtn" class="button">
					<?php _e( 'Update Task', 'thrive' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function thrive_comments_template($args = array(), $task = array()) {
	ob_start(); ?>
<?php $user = get_userdata( intval( $args['user'] ) ); ?>
<li class="task-lists-item comment" id="task-update-{$args['id']}">
	<div class="task-item-update">
		<div class="task-update-owner">
			<?php echo get_avatar( $args['user'], 60 ); ?>
		</div>
		<div class="task-update-details">
			<div class="task-meta">
				
				<?php $progress_label = __( 'New Progress by', 'thrive' ); ?>
				<?php $task_progress = absint( $args['status'] ); ?>
				
				<?php if ( 1 === $task_progress ) { ?>
					<?php $progress_label = __( 'Completed by', 'thrive' );?>
				<?php } ?>
				
				<?php if ( 2 === $task_progress ) { ?>
					<?php $progress_label = __( 'Reopened by', 'thrive' );?>
				<?php } ?>
				
				<p class="<?php echo sanitize_title( $progress_label ); ?>">
					<span class="opened-by">
						<?php echo esc_html( $progress_label ); ?>
					</span> 
						<?php echo $user->display_name; ?>    
					
					<span class="added-on"> <?php echo date( sprintf( '%s / g:i:s a', get_option( 'date_format' ) ), strtotime( $args['date_added'] ) ); ?> </span>
				</p>
			</div>
			<div class="task-content">
				<?php echo wpautop( nl2br( $args['details'] ) ); ?>
				
				<?php $current_user_id = get_current_user_id(); ?>

				<?php // Check if current user can delete the comment ?>
				<?php if ( $current_user_id == $args['user'] or current_user_can( 'administrator' ) ) { ?>
					<?php // Delete link. ?>
					<a href="#" title="<?php _e( 'Delete comment', 'thrive' ); ?>" data-comment-id="<?php echo absint( $args['id'] ); ?>" class="thrive-delete-comment">
						<?php _e( 'Delete', 'thrive' ); ?>
					</a>

				<?php } ?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div><!--task-item-update-->
</li>
<?php
return ob_get_clean();
}

function thrive_get_tasks_comments($ticket_id = 0) {

	global $wpdb;

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}thrive_comments WHERE ticket_id = $ticket_id", 'ARRAY_A' );

	return $results;
}

function thrive_project_settings() {

	include plugin_dir_path( __FILE__ ) . '../templates/project-settings.php';

	return;
}

function thrive_get_config_base_prefix() {
	
	global $wpdb;

	if ( is_multisite() ) 
	{
		return	$wpdb->base_prefix;
	}

	return $wpdb->prefix;
}

function thrive_get_current_user_groups() {

	global $wpdb;

	$prefix = thrive_get_config_base_prefix();

	$current_user_id = intval( get_current_user_id() );

	if ( 0 === $current_user_id ) {
		return array();
	}

	$bp_groups = $prefix . 'bp_groups';
	$bp_group_members = $prefix . 'bp_groups_members';

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

function thrive_get_displayed_user_groups() {

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

function thrive_project_nav( WP_Query $object ) {
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
		<?php echo esc_html( apply_filters( 'thrive_projects_page_label', __( 'Page:', 'thrive' ) ) ); ?>
		<?php for ( $page = 1; $page <= $maximum_page; $page++ ) { ?>
			<?php $active = ''; ?>
			<?php if ( $page === $current_page ) { ?>
				<?php $active = 'active '; ?>
			<?php } ?>
		<a class="<?php echo $active;?>project-nav-link" title="<?php echo sprintf( __( 'Go to page %d &raquo;', 'thrive' ), $page ); ?>" href="?paged=<?php echo $page; ?>">
			<?php echo $page; ?>
		</a>
		<?php } ?>
	</nav>
	<?php
	return;
}

function thrive_new_project_form( $group_id = 0 ) {

	include plugin_dir_path( __FILE__ ) . '../templates/project-add.php';

	return;
}

function thrive_project_meta( $project_id = 0 ) {
	?>

	<?php if ( 0 === $project_id ) { return; } ?>

	<?php $tasks_total = absint( thrive_count_tasks( $project_id, $type = 'all' ) ); ?>
	<?php $tasks_completed  = absint( thrive_count_tasks( $project_id, $type = 'completed' ) ); ?>
	<?php $tasks_remaining = absint( $tasks_total - $tasks_completed ); ?>

	<?php if ( 0 !== $tasks_total ) { ?>

	<?php $tasks_progress = ceil( ( $tasks_completed / $tasks_total ) * 100 ); ?>


	<div class="task-progress">
		
		<div class="task-progress-bar">
			<div class="task-progress-percentage" style="width:<?php echo absint( $tasks_progress ); ?>%;">
				<div class="task-progress-task-count-wrap">
					<div class="task-progress-task-count">
						<?php 
							printf( _n( '%s Task', '%s Tasks', $tasks_total, 'thrive' ), '<span class="thrive-total-tasks">'. $tasks_total .'</span>' ); 
						?>
					</div>
				</div>
				<div class="task-progress-percentage-label">
					<span>
						<?php echo absint( $tasks_progress ); ?>% 
						<?php _e( 'Completed', 'thrive' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>

<?php } // end if  ?>
<?php return; ?>
<?php }

function thrive_project_user( $user_id = 0, $post_id = 0 ) {
	?>
	
	<?php if ( $post_id === 0 ) { return; } ?>
	
	<?php if ( $user_id === 0 ) { return; } ?>

	<?php // Project User ?>
	<?php $user_profile_url = get_author_posts_url( $user_id ); ?>

	<?php // Use BuddyPress profile if possible ?>

	<?php if ( function_exists( 'bp_core_get_user_domain' ) ) { ?>
		<?php $user_profile_url = bp_core_get_user_domain( $user_id ); ?>
	<?php } ?>

	<?php _e( 'Started by ', 'thrive' ); ?>

	<a href="<?php echo esc_url( $user_profile_url ); ?>" title="<?php _e( 'Visit User Profile', 'thrive' ); ?>">
		<?php echo get_avatar( $user_id, 32 ); ?> 
		<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
	</a>

	<?php _e( 'under &raquo;' ); ?>

	<?php $group_id = absint( get_post_meta( $post_id, 'thrive_project_group_id', true ) ); ?>
	
	<?php if ( function_exists( 'groups_get_group ') ) { ?>
	
		<?php $group = groups_get_group( array( 'group_id' => $group_id ) ); ?>
	
	<?php } ?>
	
	<a href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>" title="<?php echo esc_attr( $group->name ); ?>">

		<?php echo bp_core_fetch_avatar( array( 'object' => 'group', 'item_id' => $group_id ) ) ?>

		<?php echo esc_html( $group->name ); ?>

	</a>	
	<?php // End Project User ?>

<?php
return;
}

function thrive_project_loop( $args = array() ) {

	if ( ! is_array( $args ) ) {
		return;
	}

	$args['post_type'] = 'project';

	$args['paged'] = get_query_var( 'paged' );

	include plugin_dir_path( __FILE__ ) . '../templates/project-loop-content.php';

	return;

}

function thrive_new_project_modal( $group_id = 0 ) {
	?>
	<a id="thrive-new-project-btn" class="button" href="#">

		<?php _e( 'New Project', 'thrive' ); ?>

	</a>

	<div class="clearfix"></div>


	<div id="thrive-new-project-modal">

		<div id="thrive-modal-content">

			<div id="thrive-modal-heading">
				
				<h5 class="alignleft">

					<?php _e( 'Add New Project', 'thrive' ); ?>

				</h5>

				<span id="thrive-modal-close" class="alignright">
					&times;
				</span>

				<div class="clearfix"></div>

			</div>

			<div id="thrive-modal-body">

				<?php thrive_new_project_form( $group_id ); ?>

			</div>

			<div id="thrive-modal-footer">

				<small>

					<?php _e( "Tip: Press the <em>'escape'</em> key in your keyboard to hide this form", 'thrive' ); ?>

				</small>

			</div>

		</div>

	</div>

	<?php
}


function thrive_settings_display_editor() {

	global $post;

	$content = $post->post_content;
	
	$args = array(
		'teeny' => true,
		'editor_height' => 100,
		'media_buttons' => false,
	); 
		
	return wp_editor( $content, $editor_id = "thriveProjectContent", $args );

}

function thrive_pre( $mixed ) {

	echo '<pre>';
		print_r( $mixed );
	echo '</pre>';

}
?>
