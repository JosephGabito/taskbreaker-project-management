<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerWidgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! empty( $user_tasks ) ) { ?>
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

				<?php $dated_added = new DateTime( $task->date_added ); ?>
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
					$tsstack_length = count( $time_since_stack );
					for( $y = 0; $y < $tsstack_length; $y ++ ) {
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

		<?php if ( is_user_logged_in() ) { ?>
			<?php esc_html_e( 'There are no tasks assigned to you. Enjoy your day!', 'tas_breaker' ); ?>
		<?php } else { ?>
			<?php esc_html_e( 'Please use the login form to sign in and view your tasks.', 'tas_breaker' ); ?>
		<?php } ?>

	</div>

<?php } ?>
