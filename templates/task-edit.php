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
?>

<?php $user_access = TaskBreakerCT::get_instance(); ?>
<?php $__post = TaskBreaker::get_post(); ?>
<?php $core = new TaskBreakerCore(); ?>
<div id="task-breaker-task-edit-form" class="form-wrap">

	<?php if ( $user_access->can_update_task( $__post->ID ) ) { ?>

		<div id="task_breaker-edit-task-message" class="task_breaker-notifier"></div>

		<input type="hidden" id="task_breakerTaskId" />

		<!-- Task Title -->
		<div class="task_breaker-form-field">
			<input placeholder="<?php esc_attr_e( 'Task Summary', 'task_breaker' ); ?>" type="text" id="task_breakerTaskEditTitle" maxlength="160" name="title" class="widefat"/>
		</div>

		<!-- Task User Assigned -->
		<div class="task_breaker-form-field">
			<select multiple id="task-user-assigned-edit" class="task-breaker-select2"></select>
		</div>

		<!-- Task Description -->
		<div class="task_breaker-form-field">
			<?php
				$args = array(
					'teeny' => true,
					'editor_height' => 100,
					'media_buttons' => false,
					'quicktags' => false,
				);
			?>
			<?php echo wp_editor( $content = null, $editor_id = 'task_breakerTaskEditDescription', $args ); ?>
		</div>

		<!-- Task Priority -->
		<div class="task_breaker-form-field">
			<label for="task_breaker-task-priority-select">
				<strong>
					<?php _e( 'Priority:', 'task_breaker' ); ?>
				</strong>
				<?php
					$core->task_priority_select( 1, 'task_breaker-task-edit-priority', 'task_breaker-task-edit-select-id' );
				?>
			</label>
		</div>

		<!--file attachments-->
		<div class="task_breaker-form-field" id="taskbreaker-file-attachment-edit">
			<div class="taskbreaker-task-file-attachment">
				<div class="task-breaker-form-file-attachment">
					<input disabled type="file" name="file" id="task-breaker-form-file-attachment-edit-field" />
					<label for="task-breaker-form-file-attachment-edit-field">
						<strong class="tasbreaker-file-attached">
							<?php esc_html_e( 'Loading Attached Files...', 'task_breaker' ); ?>
						</strong>
						<div class="taskbreaker-task-attached-file"></div>
						<?php esc_html_e('Click to update file attachment', 'task_breaker'); ?>
						<?php echo sprintf( __('(maximum file size: %d MB)', 'task_breaker'), absint( $core->get_wp_max_upload_size() ) ); ?>
					</label>

				</div>
				<div class="tb-file-attachment-progress-wrap">
					<div class="tb-file-attachment-progress-text">
						<?php esc_html_e('Uploading', 'task_breaker'); ?>&hellip;<span class="taskbreaker-upload-progress-value">(0%)</span>
						<span class="taskbreaker-upload-success-text-helper">
							<?php esc_html_e('. File attached successfully.', 'task_breaker'); ?>
						</span>
						<span class="taskbreaker-upload-error-text-helper">
							<?php esc_html_e('. Upload successfully initiated, but the server was unable to process it. See message below.', 'task_breaker'); ?>
						</span>
					</div>
					<div class="tb-file-attachment-progress">
						<div class="tb-file-attachment-progress-movable"></div>
					</div>

				</div>
				<div id="taskbreaker-unlink-file-btn" role="button"></div>
			</div>

		</div>

		<!--[if lte IE 9]>
			<div class="task_breaker-form-field ie-fallback ie-10">
				<label for="task_breaker-task-priority-select">
					<?php esc_html_e('File attachment is disabled for this browser. Please update to latest version', 'task_breaker'); ?>
				</label>
			</div>
		<![endif]-->
		<!-- end file attachments -->

		<!-- Task Controls -->
		<div class="task_breaker-form-field">

			<button id="task_breaker-delete-btn" class="button button-primary button-large" style="float:right; margin-left: 10px;">
				<?php esc_attr_e( 'Delete', 'task_breaker' ); ?>
			</button>

			<button id="task_breaker-edit-btn" class="button button-primary button-large" style="float:right">
				<?php esc_attr_e( 'Update Task', 'task_breaker' ); ?>
			</button>

			<div style="clear:both"></div>
		</div>

	<?php }  else { ?>
		<p class="task-breaker-message info">
			<?php echo sprintf( esc_html__('Ops! Looks like you are lost. %s', 'task_breaker'), '<a href="#tasks">'.__('Go back to tasks.', 'task_breaker').'</a>'); ?>
		</p>
	<?php } ?>

</div><!--#task-breaker-task-edit-form-->
