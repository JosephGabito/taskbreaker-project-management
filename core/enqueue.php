<?php

add_action( 'admin_head',      		 'thrive_admin_stylesheet' );
add_action( 'admin_print_scripts',   'thrive_admin_scripts' );
add_action( 'wp_enqueue_scripts',    'thrive_register_scripts' );
add_action( 'wp_footer', 			 'thrive_register_config' );
// Disable login modals introduced in WordPress 3.6
remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

function thrive_admin_stylesheet() {

	global $post;

	if ( empty( $post ) ) {
		return;
	}

	if ( 'project' !== $post->post_type ) {
		return;
	}

	wp_enqueue_style( 'thrive-admin-style', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css' );

	return;
}

function thrive_admin_scripts() {

	global $post;

	if ( empty( $post) ) {
		return;
	}
	
	if ( "project" === $post->post_type ) {

		wp_enqueue_script( 'backbone' );
		wp_enqueue_script( 'thrive-admin', plugin_dir_url( __FILE__ ) . '../assets/js/admin.js', array( 'jquery', 'backbone' ), $ver = 1.0, $in_footer = true );
		// Deregister the culprit.
		wp_deregister_script( 'vc_accordion_script' );
		
	}

	return;

}

function thrive_register_scripts() {

	// Front-end stylesheet.
	wp_enqueue_style( 'thrive-stylesheet', plugin_dir_url( __FILE__ ) . '../assets/css/style.css', array(), 1.0 );

	// Administrator JS.
	if ( is_admin() ) {
		wp_enqueue_script(
			'thrive-admin',  plugin_dir_url( __FILE__ ) . '../assets/js/admin.js', array( 'jquery', 'backbone' ),  // Dependencies.
			1.0, true
		);
	}

	// Front-end JS.
	if ( is_singular( THRIVE_PROJECT_SLUG ) ) {
		wp_enqueue_script(
			'thrive-js', plugin_dir_url( __FILE__ ) . '../assets/js/thrive.min.js', array( 'jquery', 'backbone' ),
			1.0, true
		);
	}

	// Project Archive JS.
	wp_enqueue_script(
		'thrive-archive-js', plugin_dir_url( __FILE__ ) . '../assets/js/archive.js', array( 'jquery', 'backbone' ),
		1.0, true
	);

	return;
}

function thrive_register_config() {

	if ( is_singular( THRIVE_PROJECT_SLUG ) ) { ?>
		<script>
			<?php global $post; ?>
			var thriveAjaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			var thriveTaskConfig = {
				currentProjectId: '<?php echo $post->ID; ?>',
				currentUserId: '<?php echo get_current_user_id(); ?>',
			}
		</script>
		<?php
	}

	return;
}
?>
