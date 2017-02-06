<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph Gabito <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerTemplates
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

?>
<nav>

	<?php echo esc_html( apply_filters( 'task_breaker_projects_page_label', __( 'Page:', 'task_breaker' ) ) ); ?>
	
	<?php for ( $page = 1; $page <= $maximum_page; $page++ ) { ?>
		
		<?php $active = ''; ?>
		
		<?php if ( $page === $current_page ) { $active = 'active '; } ?> 

		<a class="<?php echo sanitize_html_class( $active );?>project-nav-link" 
			title="<?php echo sprintf( esc_attr__( 'Go to page %d &raquo;', 'task_breaker' ), absint( $page ) ); ?>" 
			href="?paged=<?php echo absint( $page ); ?>">

			<?php echo esc_html( $page ); ?>

		</a>

	<?php } ?>

</nav>
