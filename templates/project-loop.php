<?php
/**
 * Fires at the top of the members directory template file.
 *
 * @since Task Breaker 1.0
 */
do_action( 'task_breaker_before_projects_directory' ); ?>

<div id="buddypress">

	<div id="task_breaker-intranet-projects">

		<?php if ( bp_is_active( 'groups' ) ) { ?>

			<?php task_breaker_new_project_modal(); ?>

			<?php task_breaker_project_loop( array() ); ?>

		<?php } else { ?>

			<p id="message" class="info">
	        	<?php _e('Please enable BuddyPress Groups Components to access the Projects.', 'task_breaker'); ?>
	    	</p>

		<?php } ?>

	</div><!--#task_breaker-intranet-projects-->

</div><!-- #buddypress -->

<?php
do_action( 'task_breaker_after_projects_directory' );
