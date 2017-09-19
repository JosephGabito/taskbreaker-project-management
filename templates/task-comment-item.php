<?php $user = get_userdata( intval( $args['user'] ) ); ?>

<?php if ( empty( $user ) ) { ?>

	<li class="task-lists-item comment">
		<p id="message" class="message error">
			<?php _e( 'Unable to update the task. Please make sure you have the right privilege.', 'task_breaker' ); ?>
		</p>
	</li>

<?php } else { ?>

	<li class="task-lists-item comment" id="task-update-<?php echo absint( $args['id'] );?>">

		<div class="task-item-update">

			<div class="task-update-owner">

				<?php echo get_avatar( $args['user'], 60 ); ?>

			</div>

			<div class="task-update-details">

				<div class="task-meta">

					<?php $progress_label = __( 'New Progress by', 'task_breaker' ); ?>

					<?php $task_progress = absint( $args['status'] ); ?>

					<?php if ( 1 === $task_progress ) { ?>

					<?php $progress_label = __( 'Completed by', 'task_breaker' );?>

					<?php } ?>

					<?php if ( 2 === $task_progress ) { ?>

					<?php $progress_label = __( 'Reopened by', 'task_breaker' );?>

					<?php } ?>

					<p class="<?php echo sanitize_title( $progress_label ); ?>">

						<span class="opened-by">

							<?php echo esc_html( $progress_label ); ?>

						</span>

						<?php echo $user->display_name; ?>

						<span class="added-on"> <?php echo date( sprintf( '%s / g:i:s a', get_option( 'date_format' ) ), strtotime( $args['date_added'] ) ); ?> </span>
					</p>
				</div>
				<div class="task-content">

		<?php echo wpautop( nl2br( $args['details'] ) ); ?>

		<?php $current_user_id = get_current_user_id(); ?>

		<?php // Check if current user can delete the comment ?>
		<?php if ( $current_user_id == $args['user'] || current_user_can( 'administrator' ) ) { ?>
		<?php // Delete link. ?>
			<a href="#" title="<?php _e( 'Delete comment', 'task_breaker' ); ?>" data-comment-id="<?php echo absint( $args['id'] ); ?>" class="task_breaker-delete-comment">
				<?php _e( 'Remove Comment', 'task_breaker' ); ?>
			</a>

		<?php } ?>
				</div>
			</div>
			<div class="clearfix"></div>
		</div><!--task-item-update-->
	</li>

<?php } ?>
