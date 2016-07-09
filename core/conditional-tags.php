<?php
/**
 * Task Breaker Conditional tags
 *
 * @since 0.0.1
 */
 if ( ! defined ( 'ABSPATH' ) ) exit;

/**
 * Check if current user is a member of a group
 *
 * @return boolean
 */
function task_breaker_current_user_is_member_of_group( $group_id = 0 ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    global $wpdb;

    $user_id = get_current_user_id();

    if ( empty( $user_id ) ) {
        return false;
    }

    $bp = buddypress();

    $stmt = $wpdb->prepare( "SELECT id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND group_id = %d AND is_confirmed = 1 AND is_banned = 0", $user_id, $group_id );

    $results = $wpdb->get_row( $stmt );

    if ( ! empty ( $results ) ) {
        return true;
    }

    return false;

 }

 /**
  * Check if current user can access projects. Only group members can access
  * the project
  *
  * @return boolean
  */
 function task_breaker_can_view_project( $project_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( task_breaker_current_user_is_member_of_group ( $group_id ) ) {
         return true;
    }

    return false;
 }

 /**
  * Check if current user can add project to group. Only admin and
  * moderators can add project to group.
  *
  * @return boolean
  */
 function task_breaker_can_add_project_to_group( $group_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    if ( groups_is_user_mod ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    if ( groups_is_user_admin ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    return false;
 }

 /**
  * Check if current user can edit project. Only admin and moderators can edit
  * the projects.
  *
  * @return boolean
  */
function task_breaker_can_edit_project( $project_id = 0 ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( groups_is_user_mod ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    if ( groups_is_user_admin ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    return false;
}

/**
 * Check if current user can delete the project. Only the project owner and admin
 * can delete the project.
 *
 * @return boolean
 */
function task_breaker_can_delete_project( $project_id = 0 ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    $project_object = get_post( $project_id );

    if ( empty ( $project_object ) ) {
        return false;
    }

    $current_user_id = intval( get_current_user_id() );

    $project_owner = intval( $project_object->post_author );

    // Return true if the current owner is the author of project post.
    if ( $project_owner === $current_user_id ) {
        return true;
    }

    // Return true if it's admin
    if ( current_user_can( 'manage_options' ) ) {
        return true;
    }

    return false;

}

 // Tasks
 // Check if current user can see tasks inside the project
function task_breaker_can_see_project_tasks( $project_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    // Only members of the group can the project tasks
    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( task_breaker_current_user_is_member_of_group ( $group_id ) ) {

        return true;

    }

    return false;

}
 /* Check if current user can add tasks
  * - Only group admin and group mods can add tasks
  */
 function task_breaker_can_add_task( $project_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( groups_is_user_mod ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    if ( groups_is_user_admin ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    return false;
 }

 // Check if current user can delete tasks
 // Check if current user can edit tasks

 // Task Comments
 // Check if current user can add comment
 function task_breaker_can_add_task_comment( $project_id, $task_id = 0 ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    // Return true if the current user is an administrator.
    if ( current_user_can( 'manage_options') ) {
        return true;
    }

    // Only members of the group can add comment to project
    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( task_breaker_current_user_is_member_of_group ( $group_id ) ) {
        // Check to see if the current task has assigned members on it.
        if ( task_has_members_assigned( $task_id ) ) {
            // If it has assign members on it, disallow un-assigned members to update the task.
            if ( ! task_current_member_is_assign_to( $task_id ) ) {
                return false;
            }
        }

        return true;

    }

    return false;
 }


/**
 * Check to see if the task has assign members in it
 * @param $task_id integer The ID of the task.
 * @return boolean True if has members on it, otherwise false.
 */
function task_has_members_assigned( $task_id = 0 ) {

    global $wpdb;

    if ( $task_id === 0 ) {
        return false;
    }

    $stmt = $wpdb->prepare("SELECT assign_users FROM {$wpdb->prefix}task_breaker_tasks
        WHERE id = %d AND assign_users <> %s", absint( $task_id ), '');

    $result = $wpdb->get_row( $stmt );

    if ( ! empty( $result ) )
    {
        return true;
    }

    return false;
}

/**
 * Check if the current logged-in user is assigned to a specific task.
 * @param  integer $task_id The ID of the task.
 * @return boolean          False if there are no task assign to the current user, otherwise True.
 */
function task_current_member_is_assign_to( $task_id = 0 ){

    global $wpdb;

    $current_user_id = get_current_user_id();

    $stmt = $wpdb->prepare( "SELECT task_id FROM {$wpdb->prefix}task_breaker_tasks_user_assignment
        WHERE task_id = %d AND member_id = %d", $task_id, $current_user_id );

    $result = $wpdb->get_row( $stmt );

    if ( ! empty( $result ) )
    {
        return true;
    }

    return false;
}
?>
