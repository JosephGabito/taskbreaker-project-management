<?php
class TaskBreaker extends WP_Widget {

	var $task_number = 5;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		
		$widget_ops = array( 
			'classname' => 'taskbreaker_user_recent_tasks',
			'description' => __('Displays the current logged in user\'s latest tasks.', 'task_breaker'),
		);

		parent::__construct( 'taskbreaker_user_recent_tasks', __('(TaskBreaker) My Recent Task', 'task_breaker'), $widget_ops );

	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		// outputs the content of the widget.
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		$task_number = ( ! empty ( $instance['task_number'] ) ) ? absint ( $instance['task_number'] ): $this->task_number;

		$user_tasks = task_breaker_get_current_user_tasks( array(
				'task_number' => absint( $task_number )
			));

		?>

		<?php if ( ! empty( $user_tasks ) ) { ?>
			<ul>
				<?php 
					$priority = array(
						'1' => 'Normal', 
						'2' => 'High', 
						'3' => 'Critical'
					); 
				?>
				<?php foreach ( $user_tasks as $task ) { ?>
				
					<li class="task-breaker-recent-item">
						<h5>
							<span class="task-priority <?php echo sanitize_html_class( sanitize_title( $priority[$task->priority] ) ); ?>"></span>
							<a href="<?php echo get_permalink( $task->project_id ); ?>#tasks/view/<?php echo $task->task_id; ?>">
								<?php echo esc_html( $task->title ); ?>
							</a>
						</h5>
						<div class="date">
			
							<?php $dated_added = new DateTime( $task->date_created ); ?>
							<?php $time_since  = $dated_added->diff( new DateTime( date( "Y-m-d H:i:s", current_time( 'timestamp' ) ) ) );  ?>
							<?php 
								$time_since_stack = array(
									array( 'type' => 'year', 'value' => $time_since->y ),
									array( 'type' => 'month', 'value' => $time_since->m ),
									array( 'type' => 'day', 'value' => $time_since->d ),
									array( 'type' => 'hour', 'value' => $time_since->h ),
									array( 'type' => 'minute', 'value' => $time_since->i ),
									array( 'type' => 'second', 'value' => $time_since->s )
								);
								echo 'added ';
								for( $y = 0; $y < count( $time_since_stack ); $y ++ ) {
									if ( $time_since_stack[$y]['value'] > 0 ) {
										echo $time_since_stack[$y]['value'] . ' ' . $time_since_stack[$y]['type'];
										if ( $time_since_stack[$y]['value'] >1 ) {
											echo 's';
										}
										if ( isset($time_since_stack[$y+1])) {
											if ( $time_since_stack[$y+1]['value'] !== 0 ) {
												echo ', ' . $time_since_stack[$y+1]['value'] . ' ';
												echo $time_since_stack[$y+1]['type'];
												if ( $time_since_stack[$y+1]['value'] >1 ) {
													echo 's';
												}
											}
										}
										echo ' ago';
									break;

									}
								}

							?>
							
							
						</div>
					</li>
				<?php } ?>
			</ul>

		<?php } else { ?>
			
			<div class="task-breaker-widget-no-task-assigned">

				<?php esc_html_e( 'There are no tasks assigned to you. Enjoy your day!', 'tas_breaker' ); ?>
				
			</div>

		<?php } ?>
		
		<?php 
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'My Recent Tasks', 'task_breaker' ); 
		$task_number = ! empty( $instance['task_number'] ) ? $instance['task_number'] : absint( $this->task_number ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'task_breaker' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'task_number' ) ); ?>">
				<?php esc_attr_e( 'Number of Tasks:', 'task_breaker' ); ?>
			</label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'task_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'task_number' ) ); ?>" type="text" value="<?php echo esc_attr( $task_number ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['task_number'] = ( ! empty( $new_instance['task_number'] ) ) ? absint( $new_instance['task_number'] ) : $this->task_number;

		return $instance;
	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget( "TaskBreaker" );') );
?>