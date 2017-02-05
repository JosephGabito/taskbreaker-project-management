<?php global $post; ?>
<?php $template = new TaskBreakerTemplate(); ?>
<div id="task_breaker-single-project-group-details">
	<div id="task_breaker-intranet-projects">
		<ul id="task_breaker-projects-lists">
			<li class="type-project">
				<?php $template->display_project_user( $post->post_author, $post->ID ); ?>
				<div class="task_breaker-project-meta">
					<?php $template->the_project_meta( get_the_ID() ); ?>
				</div>
			</li>
		</ul>
	</div>
</div>
