<?php
add_filter( 'bp_notifications_get_registered_components', 'tb_add_new_notification_component' );
add_filter( 'bp_notifications_get_notifications_for_user', 'tb_new_task_notification_text', 10, 5 );
add_action( 'task_breaker_new_task', 'bp_custom_add_notification', 99, 2 );

/**
 * Create callback function for 'bp_notifications_get_registered_components' filter in-order for us to register a new shortcode.
 *
 * @param array $component_names the existing components inside BuddyPress
 */
function tb_add_new_notification_component( $component_names = array() ) {

	// Return safely if the parameter $component_names data type is not array.
	if ( ! is_array( $component_names ) ) {
		return;
	}

	// Push the 'task_breaker_ua_notifications_name' to components collection.
	array_push( $component_names, 'task_breaker_ua_notifications_name' );

	// Return the new $component_names collection with 'task_breaker_ua_notifications_name' already pushed inside the BuddyPress components collection.
	return $component_names;
}

function tb_new_task_notification_text( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	// New task_breaker_ua_notifications_name notifications
	if ( 'task_breaker_ua_action' === $action ) {

		$task = task_breaker_get_task( $item_id );

		$project = get_post( $task->project_id );

		$secondary_item_user = get_user_by( 'id', absint( $secondary_item_id ) );

		$secondary_item_user_name = $secondary_item_user->display_name;

		$project_id = absint( $task->project_id );

		// Customized notification title for new task.
		$notification_text = sprintf(
			'%s %s %s',
			esc_attr( $secondary_item_user_name ),
			__( ' assigned a new task for you &mdash; ', 'task-breaker' ),
			esc_html( $task->title )
		);

		// Customized notification text for new task.
		$notification_title = sprintf(
			'%s %s %s',
			__( ' You have a new task waiting for you under ', 'task-breaker' ),
			get_the_title( $project_id ),
			__( ' project', 'task-breaker' )
		);

		// The link of the task.
		$notification_link  = trailingslashit( get_permalink( $project_id ) ) . '#tasks/view/' . $item_id;

		// WordPress Toolbar.
		if ( 'string' === $format ) {
			$out = apply_filters( 'custom_filter', '<a href="' . esc_url( $notification_link ) . '" title="' . esc_attr( $notification_title ) . '">' . esc_html( $notification_text ) . '</a>', $notification_text, $notification_link );

			// Deprecated BuddyBar.
		} else {
			$out = apply_filters(
				'custom_filter', array(
				'text' => $notification_text,
				'link' => $notification_link,
				), $notification_link, (int) $total_items, $notification_text, $notification_title
			);
		}

		return $out;

	}

}

