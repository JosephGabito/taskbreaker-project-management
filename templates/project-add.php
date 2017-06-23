<?php if ( bp_is_active( 'groups' ) ) { ?>
<?php $core = new TaskBreakerCore(); ?>

	<div id="task_breaker-project-add-new-form">

		<form id="task_breaker-project-add-new-form-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post">

			<?php wp_nonce_field( 'task_breaker-transaction-request', 'nonce' ); ?>

			<input type="hidden" name="method"  value="task_breaker_transactions_update_project" />

			<input type="hidden" name="action"  value="task_breaker_transactions_request" />

			<input type="hidden" name="no_json" value="yes" />

			<div class="task_breaker-form-field hide" id="project-add-modal-js-message"></div>

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

				<textarea id="task_breaker-project-content" name="content" rows="5"
				placeholder="<?php esc_html_e( 'Describe what this project is all about. You can edit this later.', 'task_breaker' );?>" required ></textarea>

			</div>

			<?php $current_user_groups = $core->get_current_user_owned_groups(); ?>

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

										<?php if ( absint( $group_id ) === absint( $group->group_id ) ) { ?>

											<?php $selected = 'selected'; ?>

										<?php } ?>

									<?php } ?>

									<option <?php echo esc_attr_e( $selected );?> value="<?php echo esc_attr_e( absint( $group->group_id ) ); ?>">

										<?php echo esc_html( $group->group_name ); ?>

									</option>

						<?php } ?>

					</select>


			<?php } ?>
				<div class="field-description">
					<p class="task-breaker-message info">
						<?php
						esc_attr_e( 'You can only add projects into the group that you are either the administrator or one of the moderator.', 'task_breaker' );
						?>
					</p>
				</div>

			</div><!--.task_breaker-form-field-->

			<div class="task_breaker-form-field">

				<div class="alignright">

					<button id="task_breakerSaveProjectBtn" type="submit" class="button">

						<?php esc_attr_e( 'Save Project', 'task_breaker' ); ?>

					</button>

				</div>

				<div class="clearfix"></div>

			</div><!--.task_breaker-form-field-->

	<?php } else { ?>

			<p class="task-breaker-message info">
				<?php $groups_url = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ); ?>
				<?php echo sprintf(
					esc_html__(
						'Only a group moderator or a group administrator can create a group project.
						%1$s Create your group %2$s or %3$s join an existing one %4$s to take part in projects.',
						'task_breaker'
					),
					'<a target="__blank" href="'.esc_url( $groups_url . 'create' ).'" title="'.__('Create Group', 'task_breaker').'">',
					'</a>',
					'<a target="__blank" href="'.esc_url( $groups_url ).'" title="'.__('Visit Groups', 'task_breaker').'">',
					'</a>'
				); ?>
			</p><!--#message-->
	<?php } ?>
	</form><!--#task_breaker-project-add-new-form-form-->
</div><!--task_breaker-project-add-new-form-->

<?php } else { ?>
	<p id="message" class="info">
		<?php esc_html_e( 'Please enable BuddyPress Groups Component to create new Project', 'task_breaker' ); ?>
	</p>
<?php } ?>
