<?php
class TaskBreakerProject {
	
	protected $id = 0;
	protected $title = '';
	protected $content = '';
	protected $group_id = '';

	public function __construct() {

		return $this;

	}

	public function set_id( $id = 0 ) {

		$this->id = $id;

		return $this;

	}

	public function set_title( $title = '' ) {

		$this->title = $title;

		return $this;

	}

	public function set_content( $content = '' ) {

		$this->content = $content;

		return $this;

	}

	public function set_group_id( $id = 0 ) {

		$this->group_id = $id;

		return $this;

	}

	public function get_id() {

		return absint( $this->id );

	}

	public function get_title() {

		return $this->title;

	}

	public function get_content() {

		return $this->content;

	}

	public function get_group_id() {

		return absint( $this->group_id );

	}

	public function save() {

		$title = $this->get_title();

		$content = $this->get_content();

		// Return false if title and descriptions are empty.
		if ( empty( $title ) || empty( $content ) ) {
			return false;
		}

		// Return false if group id = 0
		if ( 0 === $this->get_group_id() ) {
			return false;
		}

		// Set-up our project title and description.
		$project_config = array(
		 'post_title'   => $this->get_title(),
		 'post_content' => $this->get_content(),
		 'post_type'    => TASK_BREAKER_PROJECT_SLUG,
		 'post_status'  => 'publish',
		);

		// If there is an ID set, insert the ID into $project_config array
		// and let wp_insert_post handle the update later.
		if ( 0 !== $this->get_id() ) {
			$project_config['ID'] = $this->get_id();
		}

		// Update existing post or insert new post of project is not set
		// $is_returned_ok holds the value of post id
		$is_returned_ok = wp_insert_post( $project_config );

		if ( $is_returned_ok ) {
			// If succesfully saved the details into database
			$this->set_id( $is_returned_ok );
			// update the task_breaker_project_group_id custom field
			update_post_meta( $is_returned_ok, 'task_breaker_project_group_id', $this->get_group_id() );

			return true;

		} else {

			return false;

		}
	}

	/**
	 * Deletes the project
	 *
	 * @uses   wp_delete_posts ...
	 * @return boolean true if successfully deleted, otherwise false
	 */
	public function delete() {

		if ( 0 === $this->get_id() ) {
			return false;
		}

		// check if current user can delete this post
		$is_returned_ok = false;

		$post = get_post( $this->get_id() );

		if ( empty( $post ) ) {
			return false;
		}

		if ( current_user_can( 'delete_post', $this->get_id() ) || $post->post_author == get_current_user_id() ) {

			$force_delete = true;

			$is_returned_ok = wp_delete_post( $this->get_id(), $force_delete );

		}

		if ( $is_returned_ok ) {

			$this->set_id( $is_returned_ok->ID );

			return true;
		}

		return false;
	}

}

