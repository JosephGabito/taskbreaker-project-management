<?php $projects = new WP_Query( $args ); ?>

<?php if ( $projects->have_posts() ) { ?>

<ul id="task_breaker-projects-lists">

	<?php while ( $projects->have_posts() ) { ?>

	<?php $projects->the_post(); ?>

	<li <?php post_class(); ?>>

		<?php the_post_thumbnail('thumbnail'); ?>
		
		<div class="task_breaker-project-title">
			<h3>
				<a href="<?php echo the_permalink(); ?>">
					<?php the_title(); ?>
				</a>
			</h3>
		</div>

		<div class="task_breaker-project-meta">

			<?php task_breaker_project_meta( get_the_ID() ); ?>

		</div>
		
		<div class="task_breaker-project-excerpt">

			<?php the_excerpt(); ?>

		</div>

	

		<div class="task_breaker-project-author">

			<?php task_breaker_project_user( get_the_author_meta( 'ID' ), get_the_ID() ); ?>

		</div>
	</li>

	<?php } // endwhile ?>

</ul> <!--#task_breaker-projects-lists-->

<div id="project-navigation">

	<?php task_breaker_project_nav( $projects ); ?>
	
</div>	

<?php } else {  ?>

	<div id="message" class="error">
		<?php _e( 'There are no projects found. Why not add one?', 'task_breaker' ); ?>
	</div>

<?php // No Project Found. ?>

<?php } // endif ?>



<?php 
			// Reset the post data.
wp_reset_postdata(); 
?>