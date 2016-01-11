<div id="thrive-project-add-new-form">
	
	<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">

		<input type="hidden" name="method"  value="thrive_transactions_update_project" />

		<input type="hidden" name="action"  value="thrive_transactions_request" />
		
		<input type="hidden" name="no_json" value="yes" />

		<?php wp_nonce_field( 'thrive-transaction-request', 'nonce' ); ?>

		<div class="thrive-form-field">

			<?php $placeholder = __( 'Enter the new title for this project', 'thrive' ); ?>
			
			<label for="thrive-project-name">

				<?php esc_html_e( 'Project Name', 'thrive' ); ?>
				
			</label>
			
			<input required placeholder="<?php esc_attr_e( $placeholder ); ?>" type="text" name="title" id="thrive-project-name" />

		</div>

		<div class="thrive-form-field">

			<label for="thrive-project-content">
				
				<?php esc_html_e( 'Project Details', 'thrive' ); ?>

			</label>

			<textarea id="thrive-project-content" name="content" rows="5" placeholder="<?php esc_html_e( 'Describe what this project is all about. You can edit this later.', 'thrive' );?>" required ></textarea>

		</div>

		<?php $current_user_groups = thrive_get_current_user_groups(); ?>
		
		<?php $group_id = 0; ?>
		
		<?php if ( bp_is_group_single() ) { ?>
			
			<?php $group_id = bp_get_group_id(); ?>

		<?php } ?>

		<?php if ( ! empty( $current_user_groups ) ) { ?>

			<div class="thrive-form-field">

				<label for="thrive-project-assigned-group">
					
					<?php esc_html_e( 'Assign to Group:', 'thrive' ); ?>

				</label>

				<?php if ( ! empty( $current_user_groups ) ) { ?>

					<select name="group_id" id="thrive-project-assigned-group">
						
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

			<div class="thrive-form-field">

				<div class="alignright">

					<button id="thriveSaveProjectBtn" type="submit" class="button">

						<?php esc_attr_e( 'Save Project', 'thrive' ); ?>

					</button>

				</div>

				<div class="clearfix"></div>

			</div>

		<?php } else { ?>

			<div id="message" class="error">

				<?php esc_attr_e( 'Looks like you don\'t have any groups yet. Please join or create new group to start a project.?', 'thrive' ); ?>
			
			</div>
			
			<div class="thrive-form-field">

				<div class="alignright">

					<button type="button" disabled class="button danger">

						<?php esc_attr_e( 'Save Project', 'thrive' ); ?>

					</button>

				</div>

				<div class="clearfix"></div>

			</div>
		<?php } ?>
		</form>	
	</div>
