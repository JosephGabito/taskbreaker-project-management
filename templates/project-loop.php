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
	
	<?php 

		$user_groups = task_breaker_get_current_user_owned_groups();
		
		$user_groups = BP_Groups_Member::get_group_ids( get_current_user_id() );

		$user_groups_id_collection = array();

		$groups_collection = array();

		if ( ! empty ( $user_groups['groups'] ) ) {

			$user_groups_id_collection = $user_groups['groups'];

		} else {

			$user_groups_id_collection = array(-1);
			
		}

		$config = array(
			'meta_query' => array(
				array(
					'key'     => 'task_breaker_project_group_id',
					'value'   => $user_groups_id_collection,
					'compare' => 'IN',
				),
			),
		);
		
	?>
	<?php task_breaker_project_loop( $config ); ?>

	<?php } else { ?>

			<p id="message" class="info">

				<?php _e( 'Please enable BuddyPress Groups Components to access the Projects.', 'task-breaker' ); ?>

			</p>

	<?php } ?>

	</div><!--#task_breaker-intranet-projects-->

</div><!-- #buddypress -->

<?php
do_action( 'task_breaker_after_projects_directory' );
