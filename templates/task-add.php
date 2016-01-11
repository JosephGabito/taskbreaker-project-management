<?php
/**
 * Adding tasks form template.
 *
 * @since 1.0
 */
?>
<div class="form-wrap">
	
	<div id="task_breaker-add-task-message" class="task_breaker-notifier"></div>

	<div class="task_breaker-form-field">
		<input placeholder="Task Title" type="text" id="task_breakerTaskTitle" maxlength="160" name="title" class="widefat"/>
		<br><span class="description"><?php _e('Enter the title of this task. Max 160 characters', 'task_breaker'); ?></span>
	</div><br/>
	
	<div class="task_breaker-form-field">
		<?php $args = array(
			'teeny' => true,
			'editor_height' => 100,
			'media_buttons' => false,
		); ?>
		
		<?php echo wp_editor($content = null, $editor_id = "task_breakerTaskDescription", $args); ?>

		<br><span class="description"><?php _e('In few words, explain what this task is all about', 'task_breaker'); ?></span>
	</div><br />

	<div class="task_breaker-form-field">
		<label for="task_breaker-task-priority-select">
			<strong><?php _e('Priority:', 'task_breaker'); ?> </strong>
			<?php echo task_breaker_task_priority_select(); ?>
		</label>
	</div>

	<div class="task_breaker-form-field">
		<button id="task_breaker-submit-btn" class="button button-primary button-large" style="float:right">
			<?php _e('Save Task', 'dunhakdis'); ?>
		</button>
		<div style="clear:both"></div>
	</div>
</div>