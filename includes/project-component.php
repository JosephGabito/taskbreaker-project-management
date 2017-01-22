<?php
/**
 * This file contains the Task_Breaker_Projects_Component
 * which is responsible for our project component
 * structre and at the same time make it possible
 * to be used in buddypress profiles
 *
 * @since      1.0
 * @package    Thrive Intranet
 * @subpackage Projects
 * @author     dunhakdis
 */

if ( ! defined( 'ABSPATH' ) ) { die();
}

/**
 * Include our core functions
 */
require_once plugin_dir_path( __FILE__ ) . '../core/functions.php';

/**
 * Include our own conditional tags.
 */
require_once plugin_dir_path( __FILE__ ) . '../core/conditional-tags.php';

/**
 * Task_Breaker_Projects_Component
 *
 * BP Projects Components extends the
 * BP Component object which is provided
 * as a starting point for building custom
 * buddypress module
 *
 * @since 1.0
 * @uses  BP_Component the boilerplate
 */


class Task_Breaker_Projects_Component extends BP_Component {


	/**
	 * Holds the ID of our 'Projects' component
	*
	 * @var The ID of the Project.
	 */
	var $id = '';

	/**
	 * Holds the name of our 'Projects' component
	*
	 * @var The name of the 'Project' component.
	 */
	var $name = '';
	/**
	 * Register our 'Projects' Component to BuddyPress Components
	 */
	function __construct() {

		$this->id = task_breaker_component_id();
		$this->name = task_breaker_component_name();

		parent::start(
			$this->id,
			$this->name,
			task_breaker_include_dir()
		);

		$this->includes();
		$this->actions();

		return $this;
	}

	/**
	 * All actions and hooks that are related to
	 * Task_Breaker_Projects_Component are listed here
	 *
	 * @uses   buddypress()
	 * @return void
	 */
	private function actions() {

		// Enable task_breaker projects component.
		buddypress()->active_components[ $this->id ] = '1';

		return;
	}

	/**
	 * Incudes all related screens and functions
	 * related to our 'Projects' component
	 *
	 * @param  array $includes The included templates files.
	 * @return void
	 */
	public function includes( $includes = array() ) {

		$includes = array(
		 'project-screens.php'
		);

		parent::includes( $includes );

		return;
	}

	/**
	 * All public objects that are accessible
	 * to anyclass are listed here
	 *
	 * @param  array $args The callback array.
	 * @return void
	 */
	public function setup_globals( $args = array() ) {

		global $bp;

		// Define some slug here.
		if ( ! defined( 'BP_PROJECTS_SLUG' ) ) {
			define( 'BP_PROJECTS_SLUG', $this->id );
		}

		$globals = array(
		 'slug' => BP_PROJECTS_SLUG,
		 'root_slug' => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : BP_PROJECTS_SLUG,
		 'has_directory' => true,
		 'directory_title' => __( 'Projects', 'component directory title', 'task_breaker' ),
		 'search_string' => __( 'Search Projects...', 'buddypress' ),
		);

		parent::setup_globals( $globals );

		return;
	}

	/**
	 * Set-up our buddypress navigation which
	 * are accesible in members and groups nav
	 *
	 * @param  array $main_nav The main navigation for groups.
	 * @param  array $sub_nav  The sub navigation of groups.
	 * @return void
	 */
	function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		$main_nav = array(
		 'name' => $this->name,
		 'slug' => $this->id,
		 'position' => 80,
		 /* main nav screen function callback */
		 'screen_function' => 'task_breaker_bp_projects_main_screen_function',
		 'default_subnav_slug' => 'all',
		);

		// Add a few subnav items under the main tab.
		$sub_nav[] = array(
		 'name'            => __( 'My Projects', 'task_breaker' ),
		 'slug'            => 'all',
		 'parent_url'      => bp_loggedin_user_domain() . '' . $this->id . '/',
		 'parent_slug'     => 'projects',
		 'screen_function' => 'task_breaker_bp_projects_main_screen_function',
		 'position'        => 10,
		);

		// Edit subnav.
		$sub_nav[] = array(
		 'name'            => __( 'New Project', 'task_breaker' ),
		 'slug'            => 'new',
		 'parent_url'      => bp_loggedin_user_domain() . '' . $this->id . '/',
		 'parent_slug'     => 'projects',
		 'screen_function' => 'task_breaker_bp_projects_main_screen_function_new_project',
		 'position'        => 10,
		);

		parent::setup_nav( $main_nav, $sub_nav );

		return;
	}
} // End Class.

/**
 * Set-ups the new Project Component.
 *
 * @return void
 */
buddypress()->projects = new Task_Breaker_Projects_Component;

