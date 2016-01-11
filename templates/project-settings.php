<?php global $post; ?>

<div id="task_breaker-project-settings">

	<input type="hidden" name="task_breaker-project-id" id="task_breaker-project-id" value="<?php echo absint($post->ID); ?>" />

	<div class="task_breaker-form-field">
		
		<?php $placeholder = __('Enter the new title for this project', 'task_breaker'); ?>
		
		<?php $title = $post->post_title; ?>
		
		<input value="<?php echo esc_attr($title); ?>" placeholder="<?php echo $placeholder; ?>" type="text" name="task_breaker-project-name" id="task_breaker-project-name" />
	
	</div>

	<div class="task_breaker-form-field">
		
		<?php task_breaker_settings_display_editor(); ?>

		<br>

		<span class="description">

			<?php _e('Explain what this project is all about', 'task_breaker'); ?>

		</span>

	</div>

	<div class="task_breaker-form-field">

		<label for="task_breaker-project-assigned-group">
			<?php _e('Assign to Group:', 'task_breaker'); ?>
		</label>
		
		<?php $current_user_groups = task_breaker_get_current_user_groups(); ?>
		<?php //print_r($current_user_groups); ?>
		<?php if ( !empty($current_user_groups) ) { ?>
			<select name="task_breaker-project-assigned-group" id="task_breaker-project-assigned-group">
				<?php foreach( $current_user_groups as $group ) { ?>
					<option value="<?php echo absint( $group['group_id'] ); ?>">
						<?php echo esc_html( $group['name'] ); ?>
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

			<?php if ( current_user_can( 'delete_post', $post->ID ) || $post->post_author == get_current_user_id() ) { ?>
				<button id="task_breakerDeleteProjectBtn" type="button" class="button button-danger">
					<?php echo _e('Delete', 'task_breaker'); ?>
				</button>
			<?php } ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>