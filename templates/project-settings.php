<?php global $post; ?>

<div id="thrive-project-settings">

	<input type="hidden" name="thrive-project-id" id="thrive-project-id" value="<?php echo absint($post->ID); ?>" />

	<div class="thrive-form-field">
		
		<?php $placeholder = __('Enter the new title for this project', 'thrive'); ?>
		
		<?php $title = $post->post_title; ?>
		
		<input value="<?php echo esc_attr($title); ?>" placeholder="<?php echo $placeholder; ?>" type="text" name="thrive-project-name" id="thrive-project-name" />
	
	</div>

	<div class="thrive-form-field">
		
		<?php thrive_settings_display_editor(); ?>

		<br>

		<span class="description">

			<?php _e('Explain what this project is all about', 'thrive'); ?>

		</span>

	</div>

	<div class="thrive-form-field">

		<label for="thrive-project-assigned-group">
			<?php _e('Assign to Group:', 'thrive'); ?>
		</label>
		
		<?php $current_user_groups = thrive_get_current_user_groups(); ?>
		<?php //print_r($current_user_groups); ?>
		<?php if ( !empty($current_user_groups) ) { ?>
			<select name="thrive-project-assigned-group" id="thrive-project-assigned-group">
				<?php foreach( $current_user_groups as $group ) { ?>
					<option value="<?php echo absint( $group['group_id'] ); ?>">
						<?php echo esc_html( $group['name'] ); ?>
					</option>
				<?php } ?>
			</select>
		<?php } ?>
	
	</div>

	<div class="thrive-form-field">
		<div class="alignright">
			
			<button id="thriveUpdateProjectBtn" type="button" class="button">
				<?php echo _e('Update Project', 'thrive'); ?>
			</button>

			<?php if ( current_user_can( 'delete_post', $post->ID ) || $post->post_author == get_current_user_id() ) { ?>
				<button id="thriveDeleteProjectBtn" type="button" class="button button-danger">
					<?php echo _e('Delete', 'thrive'); ?>
				</button>
			<?php } ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>