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

    global $wpdb;

    $user_id = get_current_user_id();

    if ( empty( $user_id ) ) {
        return false;
    }

    $bp = buddypress();

    $stmt = $wpdb->prepare( "SELECT id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND group_id = %d AND is_confirmed = 1 AND is_banned = 0", $user_id, $group_id );

    if ( ! empty ( $wpdb->get_row( $stmt ) ) ) {
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

    if ( groups_is_user_mod ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    if ( groups_is_user_admin ( get_current_user_id(), $group_id ) ) {
        return true;
    }

    return;
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
 function task_breaker_can_add_task_comment( $project_id ) {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    // Only members of the group can add comment to project
    $group_id = absint( get_post_meta( $project_id, 'task_breaker_project_group_id', true ) );

    if ( task_breaker_current_user_is_member_of_group ( $group_id ) ) {

        return true;

    }

    return false;
 }
 // Check if current user can delete comment

?>
