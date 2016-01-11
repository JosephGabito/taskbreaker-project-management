<div class="form-wrap">
	<div id="task_breaker-edit-task-message" class="task_breaker-notifier"></div>

	<input type="hidden" id="task_breakerTaskId" />
	<div class="task_breaker-form-field">
		<input placeholder="Task Title" type="text" id="task_breakerTaskEditTitle" maxlength="160" name="title" class="widefat"/>
		<br><span class="description"><?php _e('Enter the title of this task. Max 160 characters', 'task_breaker'); ?></span>
	</div><br/>
	
	<div class="task_breaker-form-field">
		<?php 
			$args = array(
				'teeny' => true,
				'editor_height' => 100,
				'media_buttons' => false
			); 
		?>
		<?php echo wp_editor($content = null, $editor_id = "task_breakerTaskEditDescription", $args); ?>
		<br><span class="description"><?php _e('In few words, explain what this task is all about', 'task_breaker'); ?></span>
	</div><br/>

	<div class="task_breaker-form-field">
		<label for="task_breaker-task-priority-select">
			<strong><?php _e('Priority:', 'task_breaker'); ?> </strong>
			<?php echo task_breaker_task_priority_select($default = 1, $name = 'task_breaker-task-edit-priority', $id='task_breaker-task-edit-select-id'); ?>
		</label>
	</div>

	<div class="task_breaker-form-field">
		<button id="task_breaker-delete-btn" class="button button-primary button-large" style="float:right; margin-left: 10px;">
			<?php _e('Delete', 'dunhakdis'); ?>
		</button>

		<button id="task_breaker-edit-btn" class="button button-primary button-large" style="float:right">
			<?php _e('Update Task', 'dunhakdis'); ?>
		</button>
		
		<div style="clear:both"></div>
	</div>
</div>