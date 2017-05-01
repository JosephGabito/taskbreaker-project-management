<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerProjectPostType
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Register our Project Post Type.
 *
 * @package TaskBreaker\TaskBreakerProjectScreens
 */
final class TaskBreakerProjectPostType {

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_post_type' ) );

		add_action( 'wp', array( $this, 'single_project_filter' ) );

		// Fixing Yoast Issue.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		return;

	}

	/**
	 * Register 'Projects' component post type
	 *
	 * @return void
	 */
	function register_post_type() {

		$labels = array(
			'name'               => __( 'Projects', 'task_breaker' ),
			'singular_name'      => __( 'Project', 'task_breaker' ),
			'menu_name'          => __( 'Projects', 'task_breaker' ),
			'name_admin_bar'     => __( 'Project', 'task_breaker' ),
			'add_new'            => __( 'Add New', 'task_breaker' ),
			'add_new_item'       => __( 'Add New Project', 'task_breaker' ),
			'new_item'           => __( 'New Project', 'task_breaker' ),
			'edit_item'          => __( 'Edit Project', 'task_breaker' ),
			'view_item'          => __( 'View Project', 'task_breaker' ),
			'all_items'          => __( 'All Projects', 'task_breaker' ),
			'search_items'       => __( 'Search Projects', 'task_breaker' ),
			'parent_item_colon'  => __( 'Parent Projects:', 'task_breaker' ),
			'not_found'          => __( 'No projects found.', 'task_breaker' ),
			'not_found_in_trash' => __( 'No projects found in Trash.', 'task_breaker' ),
		);

		$args = array(
			'menu_icon'           => 'dashicons-analytics',
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => apply_filters( 'task_breaker_project_post_type_show_ui', '__return_false' ),
			'show_in_menu'       => apply_filters( 'task_breaker_project_post_type_show_ui', '__return_false' ),
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'project' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'custom-fields' ),
		);

		if ( current_user_can( 'manage_options' ) ) {
			$args['show_ui'] = true;
			$args['show_in_menu'] = true;
		}

		register_post_type( 'project', $args );

		return;
	}

	/**
	 * Filters the content of project single page.
	 *
	 * @return void
	 */
	public function single_project_filter() {

		if ( is_singular( 'project' ) ) {
			add_filter( 'the_content', array( $this, 'project_content_filter' ) );
		}

		return;
	}

	/**
	 * Displays the project single template.
	 *
	 * @param  mixed $content The callback argument for the_content filter.
	 * @return mixed The html output of the project.
	 */
	public function project_content_filter( $content ) {

		ob_start();

		$template = new TaskBreakerTemplate();

		$taskbreaker = new TaskBreaker();

		$taskbreaker_post = $taskbreaker->get_post();

		include_once plugin_dir_path( __FILE__ ) . '../core/functions.php';
		
		$template->locate_template( 'project-single', $taskbreaker_post );

		return ob_get_clean();

	}

	/**
	 * Manually add the tinymce editor style.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		// Only load this fix on 'project' post type.
		if ( is_singular( 'project' ) ) {

			// Disable script enqueue when Yoast is not active
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			
			if ( ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			  	return;
			}

			$__post = TaskBreaker::get_post();

			if ( $__post->post_type  ) {

				$loaded_styles = array(
					'tb-dashicons' => array( 'src' => includes_url('css/dashicons.min.css'),'handle' => 'taskbreaker-tinymce-dashicons' ),
					'editor-buttons' => array( 'src' => includes_url('css/editor.min.css'),'handle' => 'taskbreaker-tinymce-editor-buttons' ),
					'buttons' => array( 'src' => includes_url('css/buttons.min.css'),'handle' => 'taskbreaker-tinymce-buttons' ),
				);

				foreach( $loaded_styles as $style_key => $style ) {

					if ( ! wp_style_is( $style_key ) ) {
						wp_enqueue_style( $style['handle'], $style['src'], array(), false );
					}

				}

			}
			
		}
	
		return;
	}
}

$taskbreaker_project_post_type = new TaskBreakerProjectPostType();

