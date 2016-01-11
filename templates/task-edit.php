<div class="form-wrap">
	<div id="thrive-edit-task-message" class="thrive-notifier"></div>

	<input type="hidden" id="thriveTaskId" />
	<div class="thrive-form-field">
		<input placeholder="Task Title" type="text" id="thriveTaskEditTitle" maxlength="160" name="title" class="widefat"/>
		<br><span class="description"><?php _e('Enter the title of this task. Max 160 characters', 'thrive'); ?></span>
	</div><br/>
	
	<div class="thrive-form-field">
		<?php 
			$args = array(
				'teeny' => true,
				'editor_height' => 100,
				'media_buttons' => false
			); 
		?>
		<?php echo wp_editor($content = null, $editor_id = "thriveTaskEditDescription", $args); ?>
		<br><span class="description"><?php _e('In few words, explain what this task is all about', 'thrive'); ?></span>
	</div><br/>

	<div class="thrive-form-field">
		<label for="thrive-task-priority-select">
			<strong><?php _e('Priority:', 'thrive'); ?> </strong>
			<?php echo thrive_task_priority_select($default = 1, $name = 'thrive-task-edit-priority', $id='thrive-task-edit-select-id'); ?>
		</label>
	</div>

	<div class="thrive-form-field">
		<button id="thrive-delete-btn" class="button button-primary button-large" style="float:right; margin-left: 10px;">
			<?php _e('Delete', 'dunhakdis'); ?>
		</button>

		<button id="thrive-edit-btn" class="button button-primary button-large" style="float:right">
			<?php _e('Update Task', 'dunhakdis'); ?>
		</button>
		
		<div style="clear:both"></div>
	</div>
</div>