<?php
/**
 * Enqueue
 */
add_action( 'admin_head',               'task_breaker_admin_stylesheet' );
add_action( 'admin_print_scripts',   'task_breaker_admin_scripts' );
add_action( 'wp_enqueue_scripts',    'task_breaker_register_scripts' );
add_action( 'wp_footer',              'task_breaker_register_config' );

// Disable login modals introduced in WordPress 3.6
remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

function task_breaker_admin_stylesheet() {

	global $post;

	if ( empty( $post ) ) {
		return;
	}

	if ( 'project' !== $post->post_type ) {
		return;
	}

	wp_enqueue_style( 'task-breaker-admin-style', TASK_BREAKER_ASSET_URL . 'css/admin.css' );

	return;
}

function task_breaker_admin_scripts() {

	global $post;

	if ( empty( $post ) ) {
		return;
	}

	if ( 'project' === $post->post_type ) {

		wp_enqueue_script( 'backbone' );
		wp_enqueue_script( 'task_breaker-admin', TASK_BREAKER_ASSET_URL . 'js/admin.js', array( 'jquery', 'backbone' ), $ver = 1.0, $in_footer = true );
		// Deregister the culprit.
		wp_deregister_script( 'vc_accordion_script' );

	}

	return;

}

function task_breaker_register_scripts() {

	// Front-end stylesheet.
	wp_enqueue_style( 'task_breaker-stylesheet', TASK_BREAKER_ASSET_URL . '/css/style.css', array(), 1.0 );

	// Administrator JS.
	if ( is_admin() ) {
		wp_enqueue_script(
			'task_breaker-admin',  TASK_BREAKER_ASSET_URL . '/js/admin.js', array( 'jquery', 'backbone' ),  // Dependencies.
			1.0, true
		);
	}

	// Front-end JS.
	if ( is_singular( TASK_BREAKER_PROJECT_SLUG ) ) {
		wp_enqueue_script(
			'task_breaker-js',
			TASK_BREAKER_ASSET_URL . 'js/task-breaker.min.js', array( 'jquery', 'backbone' ),
			1.0, true
		);

		wp_enqueue_script(
			'task_breaker-select2',
			TASK_BREAKER_ASSET_URL . 'js/plugins/select2.min.js', array( 'jquery', 'backbone' ),
			1.0, true
		);
	}

	// Project Archive JS.
	wp_enqueue_script(
		'task_breaker-archive-js',
		TASK_BREAKER_ASSET_URL . 'js/archive.js', array( 'jquery', 'backbone' ),
		1.0, true
	);

	return;
}

function task_breaker_register_config() {

	if ( is_singular( TASK_BREAKER_PROJECT_SLUG ) ) { ?>
		<script>
			<?php global $post; ?>
		 var task_breakerAjaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		 var task_breakerTaskConfig = {
		  currentProjectId: '<?php echo $post->ID; ?>',
		  currentUserId: '<?php echo get_current_user_id(); ?>',
		 }
		</script>
		<?php
	}

	return;
}
?>
