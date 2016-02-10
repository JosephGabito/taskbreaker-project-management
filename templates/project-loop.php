<?php
/**
 * Fires at the top of the members directory template file.
 *
 * @since Task Breaker 1.0
 */
do_action( 'task_breaker_before_projects_directory' ); ?>

<div id="buddypress">

	<div id="task_breaker-intranet-projects">

		<?php task_breaker_new_project_modal(); ?>

		<?php task_breaker_project_loop( array() ); ?>

	</div><!--#task_breaker-intranet-projects-->

</div><!-- #buddypress -->

<?php
do_action( 'task_breaker_after_projects_directory' );
