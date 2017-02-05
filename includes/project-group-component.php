<?php
/**
 * Extends the BP_Group_Extension to create new 'Project' component.
 *
 * @since  1.0 Initial Release
 * @package TaskBreaker\TaskBreakerProjectsGroupExtension
 */

if ( ! class_exists( 'BP_Group_Extension' ) ) {
	return;
}

/**
 * Extends BuddyPress Group Extension
 *
 * @package TaskBreaker\TaskBreakerProjectGroupExtension
 */
class TaskBreakerProjectsGroupExtension extends BP_Group_Extension {

	/**
	 * Configures the Project Settings inside the Group.
	 *
	 * @return  void
	 */
	function __construct() {

		$args = array(
			'slug' => 'projects',
			'name' => __( 'Projects', 'task_breaker' ),
			'nav_item_position' => 105,
			'screens' => array(
			'edit' => array(
					'name' => 'Projects',
					// Changes the text of the Submit button.
					'submit_text' => 'Submit, submit',
				),
		  	'create' => array(
		   			'position' => 100,
				),
			),
		);

		parent::init( $args );

		return;

	}

	/**
	 * Displays the Projects under 'Projects' tab under group.
	 *
	 * @param  int $group_id The Group ID.
	 * @return void
	 */
	function display( $group_id = null ) {

		do_action( 'task_breaker_before_projects_archive' );

		$group_id = bp_get_group_id(); 

		$template = new TaskBreakerTemplate();
		?>

		<h3><?php esc_html_e( 'Projects', 'task_breaker' ); ?></h3>

		<div id="task_breaker-intranet-projects">

			<?php $template->display_new_project_modal( $group_id ); ?>

			<?php
				$args = array(
					'meta_key'   => 'task_breaker_project_group_id',
					'meta_value' => absint( $group_id ),
				);
			?>

			<?php $template->display_project_loop( $args ); ?>

		</div>

		<?php

		do_action( 'task_breaker_after_projects_archive' );

		return;
	}

}

bp_register_group_extension( 'TaskBreakerProjectsGroupExtension' );
