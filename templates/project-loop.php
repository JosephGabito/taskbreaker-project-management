<?php
/**
 * Fires at the top of the members directory template file.
 *
 * @since ThriveIntranet 1.0
 */
do_action( 'thrive_before_projects_directory' ); ?>

<div id="buddypress">
	
	<div id="thrive-intranet-projects">
		
		<?php thrive_new_project_modal(); ?>

		<?php thrive_project_loop( array() ); ?>
		
	</div><!--#thrive-intranet-projects-->

</div><!-- #buddypress -->

<?php
do_action( 'thrive_after_projects_directory' );
