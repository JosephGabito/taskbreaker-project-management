<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists('bp_is_active') ) {
	echo '<div id="message" class="info">';
		esc_html_e( 'Please install and activate BuddyPress to use this feature.', 'task_breaker' );
	echo '</div>';
	return;
}
?>

<?php if ( bp_is_active( 'groups' ) ) { ?>

<?php $user_access = TaskBreakerCT::get_instance(); ?>

<?php $__post = TaskBreaker::get_post(); ?>

<?php $core = new TaskBreakerCore(); ?>

	<div id="task_breaker-project">

		<?php if ( $user_access->can_view_project( $args->ID ) ) { ?>

			<?php include $core->get_template_directory() . '/project-heading.php'; ?>
			
			<div class="task_breaker-project-tabs">

				<ul id="task_breaker-project-tab-li">
					<li class="task_breaker-project-tab-li-item active">
						<a data-content="task_breaker-project-dashboard" class="task_breaker-project-tab-li-item-a" href="#tasks/dashboard">
							<?php esc_html_e( 'Dashboard', 'task-breaker' ); ?>
						</a>
					</li>
					<li class="task_breaker-project-tab-li-item">
						<a data-content="task_breaker-project-tasks" class="task_breaker-project-tab-li-item-a" href="#tasks">
							<?php esc_html_e( 'Tasks', 'task-breaker' ); ?>
						</a>
					</li>
					<li class="task_breaker-project-tab-li-item">
						<a data-content="task_breaker-project-add-new" id="task_breaker-project-add-new" class="task_breaker-project-tab-li-item-a" href="#tasks/add">
							<?php esc_html_e( 'Add New', 'task-breaker' ); ?>
						</a>
					</li>
					<li class="task_breaker-project-tab-li-item">
						<a data-content="task_breaker-project-edit" id="task_breaker-project-edit-tab" class="task_breaker-project-tab-li-item-a" href="#">
							<?php esc_html_e( 'Edit', 'task-breaker' ); ?>
						</a>
					</li>
					<?php if ( $user_access->can_edit_project( $__post->ID ) ) { ?>
						<li class="task_breaker-project-tab-li-item">
							<a data-content="task_breaker-project-settings" class="task_breaker-project-tab-li-item-a" href="#tasks/settings">
								<?php esc_html_e( 'Settings', 'task-breaker' ); ?>
							</a>
						</li>
					<?php } ?>
				</ul>

			</div><!--.task_breaker-project-tabs-->
			<div id="task_breaker-project-tab-content">
				<?php
					if ( $__post->post_type === 'project' ) {
						include $core->get_template_directory() . '/project.php';
					}
				?>
			</div>

		<?php } else { ?>

			<div id="task-breaker-access-project-not-allowed" class="row">
				<div class="col-xs-12">
					<div class="task-breaker-message info">
						<?php esc_attr_e( 'This project can only be accessed by group members. Use the button below join the group and receive an access to this project.', 'task-breaker' ); ?>
					</div>
				</div>
			</div>

			<?php $group_id = absint( get_post_meta( $args->ID, 'task_breaker_project_group_id', true ) ); ?>

			<?php $group = groups_get_group( array( 'link_class' => 'button', 'group_id' => $group_id ) ); ?>

			<?php $join_link = wp_nonce_url( bp_get_group_permalink( $group ) . 'join', 'groups_join_group' ); ?>

			<a class="button" href="<?php echo esc_url( $join_link ); ?>" title="<?php esc_attr_e( 'Join Group', 'task-breaker' ); ?>">
				<?php esc_attr_e( 'Join Group', 'task-breaker' ); ?>
			</a>

		<?php } ?>
	</div><!--#task_breaker-project-->
<?php } else { ?>
	<p id="message" class="info">
		<?php _e( 'Please enable BuddyPress Groups Components.', 'task-breaker' ); ?>
	</p>
<?php } ?>
