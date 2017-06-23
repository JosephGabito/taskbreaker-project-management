<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerTemplate
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * TaskBreakerTemplate is a collection of methods that are used as a template tags.
 *
 * @package TaskBreaker\TaskBreakerTemplate
 */
class TaskBreakerTemplate {

	/**
	 * Includes the task add form.
	 *
	 * @return void
	 */
	function task_add_form() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-add.php';

	}

	/**
	 * Includes the task edit form.
	 *
	 * @return void
	 */
	function task_edit_form() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-edit.php';

	}

	/**
	 * Includes the task filters form.
	 *
	 * @return void
	 */
	function task_filters() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-filter.php';

	}

	/**
	 * Displays the project model markup
	 *
	 * @param  integer $group_id The group id.
	 * @return void
	 */
	function display_new_project_modal( $group_id = 0 ) {

		include plugin_dir_path( __FILE__ ) . '../templates/project-add-modal.php';

		return;

	}

	/**
	 * Renders a tasks 'view' based on the arguments given.
	 *
	 * @param array $args The argument that you want to pass into the methid. 'project_id', 'page', 'priority', 'search', 'orderby', 'order', 'show_completed', 'echo'
	 * @return void if $echo is set to true other wise returns the constructed markup for tasks
	 */
	function task_view( $args = array() ) {

		$core = new TaskBreakerCore();

		$config = array(
				'project_id' => 0,
				'page' => 1,
				'priority' => -1,
				'search' => '',
				'orderby' => 'date_added',
				'order' => 'desc',
				'show_completed' => 'no',
				'echo' => true,
			);

		foreach ( $config as $option => $value ) {

			if ( ! empty( $args[ $option ] ) ) {
				$$option = $args[ $option ];
			} else {
				$$option = $value;
			}
		}

		if ( $echo === 'no' ) { ob_start(); }

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$task_breaker_tasks = TaskBreakerTasksController::get_instance();

		$tasks = $task_breaker_tasks->renderTasks( $args );
		$stats = $tasks['stats'];
		$tasks = $tasks['results'];
		$current_user_id = get_current_user_id();

		echo '<div id="task_breaker-task-list-canvas">';

		$open_tasks_no     = $core->count_tasks( $project_id, 'open' );
		$completed_task_no = $core->count_tasks( $project_id, 'completed' );
		$all_tasks_no      = $core->count_tasks( $project_id, 'all' );

		if ( ! empty( $search ) ) {

			echo '<p id="task_breaker-view-info">' . sprintf( __( 'Search result for: "%s"', 'task_breaker' ), esc_html( $search ) ) . '</p>';

		} else {

			if ( 'no' === $show_completed ) {
				echo '<p id="task_breaker-view-info">' . sprintf( _n( 'Currently showing %d task ', 'Currently showing %d tasks ', $open_tasks_no, 'task_breaker' ), $open_tasks_no );
				echo sprintf( __( 'out of %d', 'task_breaker' ), $all_tasks_no ) . '</p>';
			}

			if ( $show_completed == 'yes' ) {
				echo '<p id="task_breaker-view-info">' . sprintf( _n( 'Currently showing %d completed task ', 'Currently showing %d completed tasks ', $completed_task_no, 'task_breaker' ), $completed_task_no );
				echo sprintf( __( 'out of %d', 'task_breaker' ), $all_tasks_no ) . '</p>';
			}
		}

		if ( empty( $tasks ) ) {

			echo '<p class="bp-template-notice error" id="task_breaker-message">';
				echo __( 'No results found. Try another filter or add new task.', 'task_breaker' );
			echo '</p>';

		} else {

			echo '<table class="wp-list-table widefat fixed striped pages" id="task_breaker-core-functions-render-task">';
			echo '<tr>';
				echo '<th width="70%">' . __( 'Title', 'task_breaker' ) . '</th>';
				echo '<th>' . __( 'Priority', 'task_breaker' ) . '</th>';
				echo '<th>' . __( 'Date', 'task_breaker' ) . '</th>';
			echo '</tr>';

			foreach ( (array) $tasks as $task ) {

				$priority_label = $task_breaker_tasks->getPriority( $task->priority );

				$completed = '';

				if ( $task->completed_by != 0 ) {

					$completed = 'completed';

				}

				$classes = implode( ' ', array( esc_attr( sanitize_title( $priority_label ) ), $completed ) );

				$row_actions = '<div class="row-actions">';
					$row_actions .= '<span class="edit"><a href="#tasks/edit/' . intval( $task->id ) . '">Edit</a> | </span>';
				if ( empty( $completed ) ) {
					$row_actions .= '<span data-user_id="' . intval( $current_user_id ) . '" data-task_id="' . intval( $task->id ) . '" class="task_breaker-complete-ticket"><a href="#">Complete</a> | </span>';
				} else {
					$row_actions .= '<span data-task_id="' . intval( $task->id ) . '" class="task_breaker-renew-task"><a href="#">Renew Task</a> | </span>';
				}
					$row_actions .= '<span class="trash"><a data-ticket-id="' . intval( $task->id ) . '" class="task_breaker-delete-ticket-btn" href="#">Delete</a> </span>';
				$row_actions .= '</div>';

				echo '<tr class="' . $classes . '">';

					echo '<td><strong><a class="row-title" href="#tasks/edit/' . intval( $task->id ) . '">' . stripslashes( esc_html( $task->title ) ) . '</a></strong>' . $row_actions . '</td>';
					echo '<td>' . esc_html( $priority_label ) . '</h3></td>';

				if ( '0000-00-00 00:00:00' !== $task->date_added ) {
					echo '<td>' . esc_html( date( 'M d, o @H:i', strtotime( $task->date_added ) ) ) . '</h3></td>';
				} else {
					echo '<td>' . __( 'N/A','task_breaker' ) . '</td>';
				}

				echo '</tr>';
			}
			echo '</table>';

			$total      = intval( $stats['total'] );
			$total_page = intval( $stats['total_page'] );
			$currpage   = intval( $stats['current_page'] );
			$min_page	= intval( $stats['min_page'] );
			$max_page   = intval( $stats['max_page'] );

			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo '<span class="displaying-num">' . sprintf( _n( '%s task', '%s tasks', $total, 'task_breaker' ),$total ) . '</span>';

			if ( $total_page >= 1 ) {
				echo '<span id="trive-task-paging" class="pagination-links">';
					echo '<a class="first-page disabled" title="' . __( 'Go to the first page', 'task_breaker' ) . '" href="#tasks/page/' . $min_page . '">«</a>';
					echo '<a class="prev-page disabled" title="' . __( 'Go to the previous page', 'task_breaker' ) . '" href="#">‹</a>';

							echo '<span class="paging-input"><label for="task_breaker-task-current-page-selector" class="screen-reader-text">' . __( 'Select Page', 'task_breaker' ) . '</label>';
							echo '<input readonly class="current-page" id="task_breaker-task-current-page-selector" type="text" maxlength="' . strlen( $total_page ) . '" size="' . strlen( $total_page ) . '"value="' . intval( $currpage ) . '">';
							echo ' of <span class="total-pages">' . $total_page . '</span></span>';

					echo '<a class="next-page" title="' . __( 'Go to the next page', 'task_breaker' ) . '" href="#">›</a>';
					echo '<a class="last-page" title="' . __( 'Go to the last page', 'task_breaker' ) . '" href="#tasks/page/' . absint( $max_page ) . '">»</a></span>';
				echo '</span>';
			}

			echo '</div></div><!--.tablenav--><!--.tablenav-pages-->';
		}

		echo '</div><!--#task_breaker-task-list-canvas-->';

		?>
		<script>
		var task_breakerProjectSettings = {
			project_id: '<?php echo absint( $post->ID );?>',
			nonce: '<?php echo wp_create_nonce( 'task_breaker-transaction-request' ); ?>',
			current_group_id: '<?php echo absint( get_post_meta( $post->ID, 'task_breaker_project_group_id', true ) ); ?>',
			max_file_size: '<?php echo absint( wp_max_upload_size() ); ?>'
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
	 *
	 * @param  array $args The post type configs
	 * @return void
	 */
	function render_tasks( $args ) {

		ob_start();

		require_once( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$config = array(
				'project_id' => 0,
				'page' => 1,
				'priority' => -1,
				'search' => '',
				'orderby' => 'date_added',
				'order' => 'desc',
				'show_completed' => 'no',
				'echo' => true,
			);

		foreach ( $config as $option => $value ) {

			if ( ! empty( $args[ $option ] ) ) {
				$$option = $args[ $option ];
			} else {
				$$option = $value;
			}
		}

		$task_breaker_tasks = TaskBreakerTasksController::get_instance();

		$tasks = $task_breaker_tasks->renderTasks( $args );

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
				'max_page' 		=> 0,
				),
			);
		}

		$tasks['project_id'] = $project_id;

		?>

		<div class="clearfix"></div>

		<div id="task_breaker-project-tasks">

			<?php $this->locate_template( 'task-loop', $tasks ); ?>

		</div><!--#task_breaker-project-tasks-->

		<?php

		return ob_get_clean();

	}

	/**
	 * Returns the markup for the single tasks.
	 *
	 * @param  array $task The argument you want to pass inside the single task template.
	 * @return string 	   The html markup.
	 */
	function single_task_index( $task ) {

		ob_start();

		$this->locate_template( 'task-single', $task );

		return ob_get_clean();

	}

	/**
	 * The markup for our comments
	 *
	 * @param  array $args The arguments required to show to comments template.
	 * @return string 		The comment template.
	 */
	function comments_template( $args = array() ) {

		ob_start();

		$this->locate_template( 'task-comment-item', $args );

		return ob_get_clean();
	}

	/**
	 * Loads the project settings tempalte.
	 *
	 * @return void
	 */
	function project_settings() {

		include plugin_dir_path( __FILE__ ) . '../templates/project-settings.php';

		return;

	}

	/**
	 * The pagination of tasks.
	 *
	 * @param  WP_Query $object an instance of WP_Query
	 * @return void.
	 */
	function the_project_navigation( WP_Query $object ) {

		// Maximum page.
		$maximum_page = absint( $object->max_num_pages );

		// Current page.
		$current_page = absint( $object->query_vars['paged'] );

		// Do no display pagination if there is only 1 project.
		if ( 1 === $maximum_page ) { return; }

		include TASKBREAKER_DIRECTORY_PATH . 'templates/project-navigation.php';

		return;
	}

	/**
	 * Renders the project add form inside a group
	 *
	 * @param  integer $group_id The group id.
	 * @return void
	 */
	function display_new_project_form( $group_id = 0 ) {

		if ( ! is_user_logged_in() ) { return; }

		include plugin_dir_path( __FILE__ ) . '../templates/project-add.php';

		return;
	}

	/**
	 * Renders the project task meta
	 *
	 * @param  integer $project_id The project id.
	 * @return void
	 */
	function the_project_meta( $project_id = 0 ) {

		$core = new TaskBreakerCore();

		if ( 0 === $project_id ) { return; }

		$tasks_total = absint( $core->count_tasks( $project_id, 'all' ) );
		$tasks_completed  = absint( $core->count_tasks( $project_id, 'completed' ) );
		$tasks_remaining = absint( $tasks_total - $tasks_completed );

		if ( 0 !== $tasks_total ) {

			$tasks_progress = ceil( ( $tasks_completed / $tasks_total ) * 100 );

			$args = array(
				'tasks_total' => $tasks_total,
				'tasks_completed' => $tasks_completed,
				'tasks_remaining' => $tasks_remaining,
				'tasks_progress' => $tasks_progress,
			);

			do_action( 'taskbreaker_template_before_project_meta' );

			$this->locate_template( 'task-meta', $args );

			do_action( 'taskbreaker_template_after_project_meta' );

		} // end if

		return;
	}

	/**
	 * Renders the project of the current user.
	 *
	 * @param  integer $user_id The user id.
	 * @param  integer $post_id The post id.
	 * @return void.
	 */
	function display_project_user( $user_id = 0, $post_id = 0 ) {

		if ( 0 === $post_id ) { return; }

		if ( 0 === $user_id ) { return; }

		$user_profile_url = get_author_posts_url( $user_id );

		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			$user_profile_url = bp_core_get_user_domain( $user_id );
		}

			esc_html_e( 'Started by ', 'task_breaker' ); ?>

			<a href="<?php echo esc_url( $user_profile_url ); ?>" title="<?php esc_attr_e( 'Visit User Profile', 'task_breaker' ); ?>">
				<?php echo get_avatar( $user_id, 32 ); ?>
				<?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?>
			</a>

		<?php
		$group_id = absint( get_post_meta( $post_id, 'task_breaker_project_group_id', true ) );

		if ( function_exists( 'groups_get_group' ) ) {

			$group = groups_get_group( array( 'group_id' => $group_id ) );

			if ( ! empty( $group->id ) ) {

				esc_html_e( 'under &raquo;', 'task_breaker' ); ?>

				<a href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>" title="<?php echo esc_attr( $group->name ); ?>">

					<?php echo bp_core_fetch_avatar( array( 'object' => 'group', 'item_id' => absint( $group_id ) ) ) ?>

					<?php echo esc_html( $group->name ); ?>

				</a>

			<?php }
		}
		return;
	}

	/**
	 * Includes the specific template file.
	 *
	 * @param  string $file_name The file name.
	 * @param  string $args      The arguments you wish to pass into the template.
	 * @return void
	 */
	function locate_template( $file_name = '', $args = '' ) {

		if ( empty( $file_name ) ) {
			return;
		}

		include plugin_dir_path( __FILE__ ) . '../templates/' . esc_attr( $file_name ) . '.php';

		return;
	}

	/**
	 * Renders the project loop template
	 *
	 * @param  array $args The arguments you wish to pass on the project loop content.
	 * @return void
	 */
	function display_project_loop( $args = array() ) {

		if ( ! is_array( $args ) ) {
			return;
		}

		$args['post_type'] = 'project';

		$args['paged'] = get_query_var( 'paged' );

		include plugin_dir_path( __FILE__ ) . '../templates/project-loop-content.php';

		return;

	}

	/**
	 * Displays the editor settings form of a project.
	 *
	 * @return string The WordPress wp_editor.
	 */
	function display_settings_editor() {

		$taskbreaker = new TaskBreaker();

		$tb_post = $taskbreaker->get_post();

		$content = $tb_post->post_content;

		$args = array(
			'teeny' => true,
			'editor_height' => 100,
			'media_buttons' => false,
		);

		$editor_id = 'task_breakerProjectContent';

		return wp_editor( $content, $editor_id, $args );

	}

	/**
	 * The button for our add new project.
	 *
	 * @return void
	 */
	function display_new_project_modal_button() {
		if ( is_user_logged_in() ) { ?>
			<a id="task_breaker-new-project-btn" class="<?php echo esc_attr( apply_filters( 'task_breaker_new_project_modal_button_class', 'button' ) ); ?>" href="#">
			    <?php esc_html_e( 'New Project', 'task_breaker' ); ?>
			</a>
		<?php
		}
	}

}
?>
