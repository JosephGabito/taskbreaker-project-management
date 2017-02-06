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

$__post = TaskBreaker::get_post();

$core = new TaskBreakerCore();

// Total tasks.
$total = intval( $core->count_tasks( $__post->ID ) );

// Completed tasks.
$completed = intval( $core->count_tasks( $__post->ID, $type = 'completed' ) );

// Remaining Tasks.
$remaining = absint( $total - $completed );

$user_access = TaskBreakerCT::get_instance();
?>
	<div id="task_breaker-tasks-filter">
		<div class="clearfix">
			<div class="task_breaker-tabs-tabs">
				<ul>
					<li id="task_breaker-task-list-tab" class="task_breaker-task-tabs active">
						<a href="#tasks" title="<?php _e( 'Tasks', 'task_breaker' ); ?>">
							<span class="dashicons dashicons-list-view"></span>
								<?php _e( 'Tasks', 'task_breaker' ); ?>
							<span class="task_breaker-remaining-tasks-count task_breaker-task-count">
								<?php echo esc_html( $remaining ); ?>
							</span>
						</a>
					</li>
					<li id="task_breaker-task-completed-tab" class="task_breaker-task-tabs">
						<a href="#tasks/completed" title="<?php _e( 'Tasks', 'tasl_breaker' ); ?>">
							<span class="dashicons dashicons-yes"></span>
								<?php _e( 'Completed', 'task_breaker' ); ?>
							<span class="task-progress-completed task_breaker-task-count">
								<?php echo esc_html( $completed ); ?>
							</span>
						</a>
					</li>
					<?php if ( $user_access->can_update_task( $__post->ID ) ) { ?>
					<li id="task_breaker-task-add-tab" class="task_breaker-task-tabs"><a href="#tasks/add">
						<span class="dashicons dashicons-plus"></span>
							<?php esc_html_e('Create New Task', 'task_breaker'); ?>
						</a>
					</li>
					<?php } ?>
					<li id="task_breaker-task-edit-tab" class="task_breaker-task-tabs hidden" style="display: none;">
						<a href="#task_breaker-edit-task">
							<?php esc_html_e('Edit Task', 'task_breaker'); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="clearfix">
			<div class="alignleft">
				<select name="task_breaker-task-filter-select-action" id="task_breaker-task-filter-select">
					<option value="-1" selected="selected"><?php _e( 'Show All', 'task_breaker' ); ?></option>
					<option value="1"><?php _e( 'Normal Priority', 'task_breaker' ); ?></option>
					<option value="2"><?php _e( 'High Priority', 'task_breaker' ); ?></option>
					<option value="3"><?php _e( 'Critical Priority', 'task_breaker' ); ?></option>
				</select>
			</div><!--.alignleft actions bulkactions-->

			<div class="alignright">
				<p class="task_breaker-search-box screen-reader-text">
					<label class="screen-reader-text">
						<?php _e( 'Search Tasks:', 'task_breaker' ); ?>
					</label>
					<form action="" method="get" id="task-breaker-search-task-form">
						<input maxlength="160" placeholder="<?php _e( 'Search Task', 'task_breaker' ); ?>" type="search" id="task_breaker-task-search-field" name="task_breaker-task-search" value="">
						<input type="submit" id="task_breaker-task-search-submit" class="button screen-reader-text sr-only" value="<?php _e( 'Apply', 'task_breaker' ); ?>">
					</form>
				</p><!--.search box-->
			</div>
		</div>
	</div><!--#task_breaker-task-filter-->
