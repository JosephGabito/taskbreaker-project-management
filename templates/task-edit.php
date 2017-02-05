<?php global $post; ?>
<?php $user_access = TaskBreakerCT::get_instance(); ?>
<?php $core = new TaskBreakerCore(); ?>
<div id="task-breaker-task-edit-form" class="form-wrap">

	<?php if ( $user_access->can_update_task( $post->ID ) ) { ?>

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

		<!-- Task Controls -->
		<div class="task_breaker-form-field">

			<button id="task_breaker-delete-btn" class="button button-primary button-large" style="float:right; margin-left: 10px;">
				<?php esc_attr_e( 'Delete', 'task-breaker' ); ?>
			</button>

			<button id="task_breaker-edit-btn" class="button button-primary button-large" style="float:right">
				<?php esc_attr_e( 'Update Task', 'task-breaker' ); ?>
			</button>

			<div style="clear:both"></div>
		</div>

	<?php }  else { ?>
		<p class="task-breaker-message info">
			<?php echo sprintf( esc_html__('Ops! Looks like you are lost. %s', 'task_breaker'), '<a href="#tasks">'.__('Go back to safely tasks.', 'task_breaker').'</a>'); ?>
		</p>
	<?php } ?> 

</div><!--#task-breaker-task-edit-form-->