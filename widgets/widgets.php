<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskController
 */
if ( ! defined( 'ABSPATH') ) {
	return;
}
/**
 * TaskBreakerWidgets extends WP_Widget to implement custom Widgets for TaskBreaker
 *
 * @package TaskBreaker\TaskBreakerWidgets
 */
class TaskBreakerWidgets extends WP_Widget {

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
		$this->register_sidebar();

	}

	/**
	 * Register the recent task widget.
	 *
	 * @return void
	 */
	public static function register_widget() {

		register_widget( 'TaskBreakerWidgets' );

		return;
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$core = new TaskBreakerCore();
		// outputs the content of the widget.
		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$task_number = ( ! empty ( $instance['task_number'] ) ) ? absint ( $instance['task_number'] ): $this->task_number;

		$user_tasks = $core->get_current_user_tasks( array(
				'task_number' => absint( $task_number )
			));


		include TASKBREAKER_DIRECTORY_PATH . '/templates/widget-recent-tasks.php';

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

	public function register_sidebar() {
	    register_sidebar(
	    	array(
		        'name' => __( 'Projects', 'task_breaker' ),
		        'id' => 'taskbreaker-projects',
		        'description' => __( 'Use this sidebar area in your theme to display all the Widgets related to Projects.', 'task_breaker' ),
		        'before_widget' => '<aside id="%1$s" class="sidebar-widgets widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3><div class="widget-clear"></div>',
	    	)
	    );
	}
}

add_action( 'widgets_init', array('TaskBreakerWidgets', 'register_widget'), 20 );
