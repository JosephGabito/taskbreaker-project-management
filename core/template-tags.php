<?php
class TaskBreakerTemplate {

	function task_add_form() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-add.php';

	}

	function task_edit_form() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-edit.php';

	}

	function task_filters() {

		include plugin_dir_path( __FILE__ ) . '../templates/task-filter.php';

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
				'orderby' => 'date_created',
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

		$open_tasks_no     = $core->count_tasks( $project_id, $type = 'open' );
		$completed_task_no = $core->count_tasks( $project_id, $type = 'completed' );
		$all_tasks_no      = $core->count_tasks( $project_id, $type = 'all' );

		if ( ! empty( $search ) ) {

			echo '<p id="task_breaker-view-info">' . sprintf( __( 'Search result for: "%s"', 'task-breaker' ), esc_html( $search ) ) . '</p>';

		} else {

			if ( $show_completed == 'no' ) {
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

				if ( '0000-00-00 00:00:00' !== $task->date_created ) {
					echo '<td>' . esc_html( date( 'M d, o @H:i', strtotime( $task->date_created ) ) ) . '</h3></td>';
				} else {
					echo '<td>' . __( 'N/A','task_breaker' ) . '</td>';
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
			echo '<span class="displaying-num">' . sprintf( _n( '%s task', '%s tasks', $total, 'task_breaker' ),$total ) . '</span>';

			if ( $total_page >= 1 ) {
				echo '<span id="trive-task-paging" class="pagination-links">';
					echo '<a class="first-page disabled" title="' . __( 'Go to the first page', 'task_breaker' ) . '" href="#tasks/page/' . $min_page . '">«</a>';
					echo '<a class="prev-page disabled" title="' . __( 'Go to the previous page', 'task_breaker' ) . '" href="#">‹</a>';

							echo '<span class="paging-input"><label for="task_breaker-task-current-page-selector" class="screen-reader-text">' . __( 'Select Page', 'task_breaker' ) . '</label>';
							echo '<input readonly class="current-page" id="task_breaker-task-current-page-selector" type="text" maxlength="' . strlen( $total_page ) . '" size="' . strlen( $total_page ) . '"value="' . intval( $currpage ) . '">';
							echo ' of <span class="total-pages">' . $total_page . '</span></span>';

					echo '<a class="next-page" title="' . __( 'Go to the next page', 'task_breaker' ) . '" href="#">›</a>';
					echo '<a class="last-page" title="' . __( 'Go to the last page', 'trive' ) . '" href="#tasks/page/' . $max_page . '">»</a></span>';
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
			current_group_id: '<?php echo absint( get_post_meta( $post->ID, 'task_breaker_project_group_id', true ) ); ?>'
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

		require_once ( plugin_dir_path( __FILE__ ) . '../controllers/tasks.php' );

		$config = array(
				'project_id' => 0,
				'page' => 1,
				'priority' => -1,
				'search' => '',
				'orderby' => 'date_created',
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

			<?php task_breaker_locate_template( 'task-loop', $tasks ); ?>

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

		task_breaker_locate_template( 'task-single', $task );

		return ob_get_clean();

	}

}
?>