<?php $projects = new WP_Query( $args ); ?>

<?php if ( $projects->have_posts() ) { ?>

<ul id="thrive-projects-lists">

	<?php while ( $projects->have_posts() ) { ?>

	<?php $projects->the_post(); ?>

	<li <?php post_class(); ?>>

		<?php the_post_thumbnail('thumbnail'); ?>
		
		<div class="thrive-project-title">
			<h3>
				<a href="<?php echo the_permalink(); ?>">
					<?php the_title(); ?>
				</a>
			</h3>
		</div>

		<div class="thrive-project-meta">

			<?php thrive_project_meta( get_the_ID() ); ?>

		</div>
		
		<div class="thrive-project-excerpt">

			<?php the_excerpt(); ?>

		</div>

	

		<div class="thrive-project-author">

			<?php thrive_project_user( get_the_author_meta( 'ID' ), get_the_ID() ); ?>

		</div>
	</li>

	<?php } // endwhile ?>

</ul> <!--#thrive-projects-lists-->

<div id="project-navigation">

	<?php thrive_project_nav( $projects ); ?>
	
</div>	

<?php } else {  ?>

	<div id="message" class="error">
		<?php _e( 'There are no projects found. Why not add one?', 'thrive' ); ?>
	</div>

<?php // No Project Found. ?>

<?php } // endif ?>



<?php 
			// Reset the post data.
wp_reset_postdata(); 
?>