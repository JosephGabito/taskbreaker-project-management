<?php global $post; ?>

<?php $template = new TaskBreakerTemplate(); ?>

<?php $core = new TaskBreakerCore(); ?>

<?php $projects = $core->get_user_groups_projects( get_current_user_id() ); ?>

<?php if ( bp_is_user() ) { ?>

	<?php $projects = $core->get_displayed_user_groups_projects(); ?>

<?php } ?>

<?php if ( bp_is_group() ) { ?>

	<?php $projects = $core->get_group_projects( bp_get_group_id() ); ?>

<?php } ?>

<p id="group-projects-explainer" class="mg-top-15 no-mg-bottom">
	<?php
		echo $projects['summary'];
	?>
</p>

<?php if ( ! empty( $projects['projects'] ) ) { ?>

	<ul id="task_breaker-projects-lists">

		<?php foreach ( $projects['projects'] as $post ) { ?>

		<?php setup_postdata( $post ); ?>

			<li <?php echo post_class( array( 'taskbreaker-project-item'), $post->ID ); ?>>

				<div class="taskbreaker-project-item-wrap">

					<div class="task_breaker-project-title">
						<h3>
							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" title="<?php echo esc_attr( the_title() ); ?>">
								<?php echo the_title(); ?>
							</a>
						</h3>
					</div>

					<div class="task_breaker-project-meta">
						<?php $template->the_project_meta( get_the_ID() ); ?>
					</div>

					<div class="task_breaker-project-excerpt">
						<?php the_excerpt(); ?>
					</div>

					<div class="task_breaker-project-author">
						<?php $template->display_project_user( get_the_author_meta( 'ID' ), get_the_ID() ); ?>
					</div>

				</div>

			</li>
		<?php } ?>
		<?php wp_reset_postdata(); ?>
	</ul>

<?php } else { ?>
	<p id="message" class="info">
		<?php esc_html_e( 'There are no Group Projects found at this time.', 'task_breaker' ); ?>
	</p>
<?php } ?>
<div id="taskbreaker-project-navigation">
	<?php
		echo paginate_links( array(
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $projects['total_pages'],
			'prev_text' => __('«'),
			'next_text'  => __('»'),
		) );
	?>
</div>
