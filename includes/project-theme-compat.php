<?php
/**
 * BP Projects Theme Compatability.
 */
class TaskBreakerThemeCompatibility {

	/**
	 * Setup the bp plugin component theme compatibility
	 */
	public function __construct() {
		/* this is where we hook bp_setup_theme_compat !! */
		add_action( 'bp_setup_theme_compat', array( $this, 'is_bp_projects' ) );
	}

	/**
	 * Are we looking at something that needs theme compatability?
	 */
	public function is_bp_projects() {

		if ( ! bp_current_action() && ! bp_displayed_user_id() && bp_is_current_component( 'projects' ) ) {

			// first we reset the post.
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );

			// then we filter 'the_content' thanks to bp_replace_the_content.
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content' ) );

		}
	}

	/**
	 * Update the with directory data
	 */
	public function directory_dummy_post() {
		bp_theme_compat_reset_post(
			array(
				'ID'             => 0,
				'post_title'     => apply_filters( 'task_breaker_projects_dir_title', __( 'Projects Directory', 'task_breaker' ) ),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => '',
				'post_type'      => 'project',
				'post_status'    => 'publish',
				'is_archive'     => true,
				'comment_status' => 'closed',
			)
		);
	}

	/**
	 * Filter the_content with bp-plugin index template part
	 */
	public function directory_content() {

		bp_buffer_template_part( 'project-loop' );

	}
}
