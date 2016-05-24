<?php
/**
 * Adding tasks form template.
 *
 * @since 1.0
 */
?>

<?php global $post; ?>

<div class="form-wrap">

	<?php if ( task_breaker_can_add_task( $post->ID ) ) { ?>

		<div id="task_breaker-add-task-message" class="task_breaker-notifier"></div>

		<div class="task_breaker-form-field">
			<input placeholder="Task Title" type="text" id="task_breakerTaskTitle" maxlength="160" name="title" class="widefat"/>
			<span class="description"><?php _e('Enter the title of this task. Max 160 characters', 'task_breaker'); ?></span>
		</div>

		<div class="task_breaker-form-field">
			<?php $args = array(
				'teeny' => true,
				'editor_height' => 100,
				'media_buttons' => false,
			); ?>

			<?php echo wp_editor($content = null, $editor_id = "task_breakerTaskDescription", $args); ?>
			<span class="description">
				<br>
				<?php _e('In few words, explain what this task is all about', 'task_breaker'); ?>
			</span>
		</div>

		<div class="task_breaker-form-field">
			<label for="task_breaker-task-priority-select">
				<strong><?php _e('Priority:', 'task_breaker'); ?> </strong>
				<?php echo task_breaker_task_priority_select(); ?>
			</label>
		</div>

		<div class="task_breaker-form-field">
			<button id="task_breaker-submit-btn" class="button button-primary button-large" style="float:right">
				<?php _e('Save Task', 'task_breaker'); ?>
			</button>
			<div style="clear:both"></div>
		</div>
	<?php } else { ?>
		<p id="message" class="info">
			<?php _e('Ops! Only group administrator or group moderators can add tasks.', 'task_breaker'); ?>
		</p>
	<?php } ?>
</div>
