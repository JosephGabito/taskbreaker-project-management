<div id="task_breaker-project-add-new-form">
	
	<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">

		<input type="hidden" name="method"  value="task_breaker_transactions_update_project" />

		<input type="hidden" name="action"  value="task_breaker_transactions_request" />
		
		<input type="hidden" name="no_json" value="yes" />

		<?php wp_nonce_field( 'task_breaker-transaction-request', 'nonce' ); ?>

		<div class="task_breaker-form-field">

			<?php $placeholder = __( 'Enter the new title for this project', 'task_breaker' ); ?>
			
			<label for="task_breaker-project-name">

				<?php esc_html_e( 'Project Name', 'task_breaker' ); ?>
				
			</label>
			
			<input required placeholder="<?php esc_attr_e( $placeholder ); ?>" type="text" name="title" id="task_breaker-project-name" />

		</div>

		<div class="task_breaker-form-field">

			<label for="task_breaker-project-content">
				
				<?php esc_html_e( 'Project Details', 'task_breaker' ); ?>

			</label>

			<textarea id="task_breaker-project-content" name="content" rows="5" placeholder="<?php esc_html_e( 'Describe what this project is all about. You can edit this later.', 'task_breaker' );?>" required ></textarea>

		</div>

		<?php $current_user_groups = task_breaker_get_current_user_groups(); ?>
		
		<?php $group_id = 0; ?>
		
		<?php if ( bp_is_group_single() ) { ?>
			
			<?php $group_id = bp_get_group_id(); ?>

		<?php } ?>

		<?php if ( ! empty( $current_user_groups ) ) { ?>

			<div class="task_breaker-form-field">

				<label for="task_breaker-project-assigned-group">
					
					<?php esc_html_e( 'Assign to Group:', 'task_breaker' ); ?>

				</label>

				<?php if ( ! empty( $current_user_groups ) ) { ?>

					<select name="group_id" id="task_breaker-project-assigned-group">
						
						<?php foreach ( $current_user_groups as $group ) { ?>
						
							<?php $selected = ''; ?>

							<?php if ( ! empty( $group_id ) ) { ?>
									
								<?php if ( absint( $group_id ) === absint( $group['group_id'] ) ) { ?>
										
									<?php $selected = 'selected'; ?>

								<?php } ?>

							<?php } ?>

							<option <?php echo esc_attr_e( $selected );?> value="<?php echo esc_attr_e( absint( $group['group_id'] ) ); ?>">
								
								<?php echo esc_html( $group['name'] ); ?>

							</option>

						<?php } ?>
					</select>
					
				<?php } ?>

			</div>

			<div class="task_breaker-form-field">

				<div class="alignright">

					<button id="task_breakerSaveProjectBtn" type="submit" class="button">

						<?php esc_attr_e( 'Save Project', 'task_breaker' ); ?>

					</button>

				</div>

				<div class="clearfix"></div>

			</div>

		<?php } else { ?>

			<div id="message" class="error">

				<?php esc_attr_e( 'Looks like you don\'t have any groups yet. Please join or create new group to start a project.?', 'task_breaker' ); ?>
			
			</div>
			
			<div class="task_breaker-form-field">

				<div class="alignright">

					<button type="button" disabled class="button danger">

						<?php esc_attr_e( 'Save Project', 'task_breaker' ); ?>

					</button>

				</div>

				<div class="clearfix"></div>

			</div>
		<?php } ?>
		</form>	
	</div>
