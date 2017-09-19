<?php
/**
 * This file contains the TaskBreakerProjectsComponent
 * which is responsible for our project component
 * structre and at the same time make it possible
 * to be used in buddypress profiles
 *
 * @since      1.0
 * @package    Thrive Intranet
 * @subpackage Projects
 * @author     dunhakdis
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
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
 * TaskBreakerProjectsComponent
 *
 * BP Projects Components extends the
 * BP Component object which is provided
 * as a starting point for building custom
 * buddypress module
 *
 * @since 1.0
 * @uses  BP_Component the boilerplate
 */
class TaskBreakerProjectsComponent extends BP_Component {

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

		$this->core = new TaskBreakerCore();

		$this->id = $this->core->get_component_id();

		$this->name = $this->core->get_component_name();

		parent::start(
			$this->id,
			$this->name,
			$this->core->get_include_directory()
		);

		$this->includes();
		$this->actions();

		return $this;
	}

	/**
	 * All actions and hooks that are related to
	 * TaskBreakerProjectsComponent are listed here
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

		// Define some slug here.
		if ( ! defined( 'BP_PROJECTS_SLUG' ) ) {
			define( 'BP_PROJECTS_SLUG', $this->id );
		}

		$globals = array(
			'slug' => BP_PROJECTS_SLUG,
			'root_slug' => isset( buddypress()->pages->{$this->id}->slug ) ? buddypress()->pages->{$this->id}->slug : BP_PROJECTS_SLUG,
		 	'has_directory' => true,
		 	'directory_title' => __( 'Projects', 'task_breaker' ),
		 	'search_string' => __( 'Search Projects...', 'task_breaker' ),
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

		$count = 0;

		if ( bp_is_user() ) {
			$core = new TaskBreakerCore();
			$projects = $core->get_displayed_user_groups_projects();
			$count = $projects['total'];
		}

		$main_nav = array(
			'name' => sprintf( esc_html( 'Projects %s', 'task_breaker' ), '<span class="count">' . absint( $count ) . '</span>' ),
			'slug' => $this->id,
			'position' => 80,
			'screen_function' => array( 'TaskBreakerProjectScreens', 'bp_projects_main_screen_function' ),
			'default_subnav_slug' => 'all',
		);

		// Add a few subnav items under the main tab.
		$bp_userdata = get_userdata( bp_displayed_user_id() );
		$displayed_user_name = '';

		if ( ! empty( $bp_userdata ) ) {
			$displayed_user_name = $bp_userdata->display_name;
			$displayed_user_name = $displayed_user_name . '\'' . ($displayed_user_name[ strlen( $displayed_user_name ) - 1 ] != 's' ? 's' : '');
		}

		$sub_nav[] = array(
			'name'            => sprintf( __( '%s Projects', 'task_breaker' ), $displayed_user_name ),
			'slug'            => 'all',
			'parent_url'      => bp_loggedin_user_domain() . $this->id . '/',
			'parent_slug'     => 'projects',
			'screen_function' => array( 'TaskBreakerProjectScreens', 'bp_projects_main_screen_function' ),
			'position'        => 10,
		);

		// Edit subnav.
		$sub_nav[] = array(
			'name'            => __( 'New Project', 'task_breaker' ),
			'slug'            => 'new',
			'parent_url'      => bp_loggedin_user_domain() . '' . $this->id . '/',
			'parent_slug'     => 'projects',
			'screen_function' => array( 'TaskBreakerProjectScreens', 'bp_projects_main_screen_function_new_project' ),
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
buddypress()->projects = new TaskBreakerProjectsComponent;
