<?php global $post; ?>

<div class="form-wrap">

	<?php if ( task_breaker_can_add_task( $post->ID ) ) { ?>

		<div id="task_breaker-add-task-message" class="task_breaker-notifier"></div>

		<!-- Task Title -->
		<div class="task_breaker-form-field">

			<input placeholder="<?php esc_attr_e( 'Task Summary', 'task_breaker' ); ?>" type="text" id="task_breakerTaskTitle" maxlength="160" name="title" class="widefat"/>

		</div>

		<!-- Task User Assigned -->
		<div class="task_breaker-form-field">
			<select multiple id="task-user-assigned" class="task-breaker-select2"></select>
		</div>

		<!-- Task Description -->
		<div class="task_breaker-form-field">

	<?php $args = array(
				'teeny' => true,
				'editor_height' => 100,
				'media_buttons' => false,
				'quicktags' => false,
			); ?>

	<?php echo wp_editor( $content = null, $editor_id = 'task_breakerTaskDescription', $args ); ?>
		</div>

		<div class="task_breaker-form-field">
			<label for="task_breaker-task-priority-select">
				<strong><?php _e( 'Priority:', 'task_breaker' ); ?> </strong>
				<?php echo task_breaker_task_priority_select(); ?>
			</label>
		</div>

		<div class="task_breaker-form-field">
			<button id="task_breaker-submit-btn" class="button button-primary button-large" style="float:right">
				<?php _e( 'Save Task', 'dunhakdis' ); ?>
			</button>
			<div style="clear:both"></div>
		</div>
	<?php } else { ?>
		<p id="message" class="info">
	<?php _e( 'Ops! Only group administrator or group moderators can add tasks.', 'task-breaker' ); ?>
		</p>
	<?php } ?>
</div>
