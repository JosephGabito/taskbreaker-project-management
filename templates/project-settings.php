<?php global $post; ?>

<?php if ( task_breaker_can_edit_project( $post->ID ) ) { ?>

	<div id="task_breaker-project-settings">

		<input type="hidden" name="task_breaker-project-id" id="task_breaker-project-id" value="<?php echo absint($post->ID); ?>" />

		<div class="task_breaker-form-field">

			<?php $placeholder = __('Enter the new title for this project', 'task_breaker'); ?>

			<?php $title = $post->post_title; ?>

			<input value="<?php echo esc_attr($title); ?>" placeholder="<?php echo $placeholder; ?>" type="text" name="task_breaker-project-name" id="task_breaker-project-name" />

		</div>

		<div class="task_breaker-form-field">

			<?php task_breaker_settings_display_editor(); ?>

			<span class="description">

				<?php _e('Explain what this project is all about', 'task_breaker'); ?>

			</span>

		</div>

		<div class="task_breaker-form-field">

			<label for="task_breaker-project-assigned-group">

				<?php _e('Assign to Group:', 'task_breaker'); ?>

			</label>

			<?php $current_user_groups = task_breaker_get_current_user_groups(); ?>

			<?php $current_project_group = intval( get_post_meta( $post->ID, 'task_breaker_project_group_id', true ) ); ?>

			<?php if ( !empty($current_user_groups) ) { ?>

				<select name="task_breaker-project-assigned-group" id="task_breaker-project-assigned-group">

					<?php foreach( $current_user_groups as $group ) { ?>

						<?php $selected = absint( $group->group_id ) == $current_project_group ? 'selected' : '';?>

						<option <?php echo $selected; ?> value="<?php echo absint( $group->group_id ); ?>">

							<?php echo esc_html( $group->name ); ?>

						</option>

					<?php } ?>

				</select>
			<?php } ?>

		</div>

		<div class="task_breaker-form-field">

			<div class="alignright">

				<button id="task_breakerUpdateProjectBtn" type="button" class="button">
					<?php echo _e('Update Project', 'task_breaker'); ?>
				</button>

				<?php if ( task_breaker_can_delete_project( $post->ID ) ) { ?>
					<button id="task_breakerDeleteProjectBtn" type="button" class="button button-danger">
						<?php echo _e('Delete', 'task_breaker'); ?>
					</button>
				<?php } ?>
			</div>

			<div class="clearfix"></div>

		</div>
	</div>
<?php } else { ?>
	<p id="message" class="info">
		<?php _e('Cannot access project settings. Only administrators and moderators are allow to edit this project.', 'task-breaker'); ?>
	</p>
<?php } ?>
